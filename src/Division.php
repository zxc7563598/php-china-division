<?php

namespace Hejunjie\ChinaDivision;

use RuntimeException;

class Division
{
    protected static array $data = [];
    protected static array $history = [];
    protected static array $diff = [];

    /**
     * 获取完整现行地区数据
     * 
     * @return array 
     */
    public static function getData(): array
    {
        if (empty(self::$data)) {
            self::loadData();
        }
        return self::$data;
    }

    /**
     * 获取完整历史地区数据
     * 
     * @return array 
     */
    public static function getHistory(): array
    {
        if (empty(self::$history)) {
            self::loadHistory();
        }
        return self::$history;
    }

    /**
     * 获取完整地区变更数据
     * 
     * @return array 
     */
    public static function getDiff(): array
    {
        if (empty(self::$diff)) {
            self::loadDiff();
        }
        return self::$diff;
    }

    /**
     * 获取身份证号码对应的地址信息
     * 
     * 该方法仅保障了最低可用性，如果需要提升响应速度与效率，建议调用 getAll() 获取全部数据存储 Redis 自行处理
     * 
     * @param string $idNumber 身份证号码
     * @param bool $returnUnitsDirectlyUnder 是否返回直属行政单位
     * @param string $returnUnknown 未知信息返回内容
     * @param bool $skip_validation 当递归调用时设置为True以避免重复验证
     * 
     * @return array
     */
    public static function getAddressInfo(string $idNumber, bool $returnUnitsDirectlyUnder = true, string $returnUnknown = '未知', bool $skip_validation = false): array
    {
        // 基础格式校验
        if (!$skip_validation) {
            if (!preg_match('/^\d{17}[\dXx]$/', $idNumber)) {
                return [
                    'province' => $returnUnknown,
                    'city' => $returnUnknown,
                    'area' => $returnUnknown,
                ];
            }
        }
        // 获取地址信息
        $code = substr($idNumber, 0, 6);
        if (strlen($code) === 6 && substr($code, 4, 2) !== '00') {
            if (empty(self::$data)) {
                self::loadData();
            }
            if (isset(self::$data[$code])) {
                return self::getAddressFromData($code, $returnUnitsDirectlyUnder, $returnUnknown);
            }
            // 检查差异数据，如果找到差异数据，尝试递归调用
            if (empty(self::$diff)) {
                self::loadDiff();
            }
            if (isset(self::$diff[$code])) {
                $diffAddresses = self::$diff[$code];
                if (count($diffAddresses) === 1) {
                    return self::getAddressInfo($diffAddresses[0], $returnUnitsDirectlyUnder, $returnUnknown, true);
                }
            }
            // 如果无差异数据，寻找历史数据有对应信息，尝试返回
            if (empty(self::$history)) {
                self::loadHistory();
            }
            if (isset(self::$history[$code])) {
                return self::getAddressFromData($code, $returnUnitsDirectlyUnder, $returnUnknown, self::$history);
            }
        }
        // 默认返回未知地址
        return [
            'province' => $returnUnknown,
            'city' => $returnUnknown,
            'area' => $returnUnknown,
        ];
    }

    /**
     * 根据身份证获取性别信息
     * 
     * @param string $idNumber 身份证
     * 
     * @return string 性别
     */
    public static function getGenderFromIdCard(string $idNumber): string
    {
        // 基础格式校验
        if (!preg_match('/^\d{17}[\dXx]$/', $idNumber)) {
            return '未知';
        }
        return (int)$idNumber[16] % 2 === 1 ? '男' : '女';
    }

    /**
     * 校验身份证信息
     * 
     * @param string $idNumber 身份证
     * 
     * @return bool 是否有效
     */
    public static function isValidIdCard(string $idNumber): bool
    {
        // 确保身份证格式
        $idNumber = strtoupper($idNumber);
        // 基础格式校验
        if (!preg_match('/^\d{17}[\dXx]$/', $idNumber)) {
            return false;
        }
        // 校验位校验
        $weights = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $checksumMap = ['1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'];
        // 计算校验和
        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $sum += (int) $idNumber[$i] * $weights[$i];
        }
        // 比较校验位
        return $idNumber[17] === $checksumMap[$sum % 11];
    }

    /**
     * 根据身份证号获取出生日期（年、月、日）
     * 
     * @param string $idNumber 身份证号
     * 
     * @return array
     */
    public static function getBirthdayFromIdCard(string $idNumber): array
    {
        // 基础格式校验
        if (!preg_match('/^\d{17}[\dXx]$/', $idNumber)) {
            return [
                'year' => '',
                'month' => '',
                'day' => ''
            ];
        }
        // 从身份证号中提取出生年份、月份、日期
        $year = substr($idNumber, 6, 4);
        $month = substr($idNumber, 10, 2);
        $day = substr($idNumber, 12, 2);
        // 返回数据
        return [
            'year' => (string) $year,
            'month' => (string) $month,
            'day' => (string) $day
        ];
    }

    /**
     * 获取分级的城市数据
     * 
     * @return array 返回包含3级城市数据的数组
     */
    public static function getCityLevels(): array
    {
        if (empty(self::$data)) {
            self::loadData();
        }
        $levels = [
            'level_1' => [],  // 1级城市
            'level_2' => [],  // 2级城市
            'level_3' => [],  // 3级城市
        ];
        // 遍历原始数据
        foreach (self::$data as $key => $name) {
            $level = null;
            $pid = null;
            // 判断并分类
            if (substr($key, -4) === '0000') {  // 1级城市
                $level = 'level_1';
                $pid = '0';
                $key = substr($key, 0, 2);
            } elseif (substr($key, -2) === '00') {  // 2级城市
                $level = 'level_2';
                $pid = substr($key, 0, 2);  // 2级城市的pid为前4位
                $key = substr($key, 0, 4);
            } else {  // 3级城市
                $level = 'level_3';
                $pid = substr($key, 0, 4);  // 3级城市的pid为前6位
            }
            // 添加到对应级别的城市数据中
            if ($level) {
                $levels[$level][$key] = [
                    'name' => $name,
                    'pid'  => $pid
                ];
            }
        }
        return $levels;
    }

    /**
     * 转换为级联选择器（cascader）格式，常用于前端组件
     * 
     * 该方法仅保障了最低可用性，如果需要提升响应速度与效率，建议调用 getAll() 获取全部数据存储 Redis 自行处理
     * 
     * @return array 
     */
    public static function convertToCascaderData(): array
    {
        if (empty(self::$data)) {
            self::loadData();
        }
        $tree = [];
        foreach (self::$data as $code => $name) {
            $provinceCode = substr($code, 0, 2);
            $cityCode = substr($code, 2, 2);
            if (str_ends_with($code, '0000')) {
                $tree[$provinceCode] = [
                    'code' => $provinceCode,
                    'name' => $name,
                    'children' => []
                ];
            } elseif (str_ends_with($code, '00')) {
                if (!isset($tree[$provinceCode])) {
                    $tree[$provinceCode] = [
                        'code' => $provinceCode,
                        'name' => null,
                        'children' => []
                    ];
                }
                $tree[$provinceCode]['children'][$cityCode] = [
                    'code' => $cityCode,
                    'name' => $name,
                    'children' => []
                ];
            } else {
                if (!isset($tree[$provinceCode])) {
                    $tree[$provinceCode] = [
                        'code' => $provinceCode,
                        'name' => null,
                        'children' => []
                    ];
                }
                if (!isset($tree[$provinceCode]['children'][$cityCode])) {
                    $tree[$provinceCode]['children'][$cityCode] = [
                        'code' => $cityCode,
                        'name' => '直辖市',
                        'children' => []
                    ];
                }
                $tree[$provinceCode]['children'][$cityCode]['children'][] = [
                    'code' => (string)$code,
                    'name' => $name,
                    'children' => []
                ];
            }
        }
        foreach ($tree as &$province) {
            $province['children'] = array_map(function ($city) {
                $city['children'] = array_values($city['children']);
                return $city;
            }, array_values($province['children']));
        }
        return array_values($tree);
    }

    /**
     * 内部加载现行地区 JSON 数据
     */
    protected static function loadData(): void
    {
        $file = __DIR__ . '/data.json';
        if (!is_file($file)) {
            throw new \RuntimeException("China division data file not found.");
        }
        $json = file_get_contents($file);
        self::$data = json_decode($json, true) ?? [];
    }

    /**
     * 内部加载历史地区 JSON 数据
     */
    protected static function loadHistory(): void
    {
        $file = __DIR__ . '/history.json';
        if (!is_file($file)) {
            throw new \RuntimeException("China division data file not found.");
        }
        $json = file_get_contents($file);
        self::$history = json_decode($json, true) ?? [];
    }

    /**
     * 内部加载地区变更 JSON 数据
     */
    protected static function loadDiff(): void
    {
        $file = __DIR__ . '/diff.json';
        if (!is_file($file)) {
            throw new \RuntimeException("China division data file not found.");
        }
        $json = file_get_contents($file);
        self::$diff = json_decode($json, true) ?? [];
    }

    /**
     * 从指定的数据源获取地址信息
     */
    protected static function getAddressFromData(string $code, bool $returnUnitsDirectlyUnder, string $returnUnknown, ?array $source = null): ?array
    {
        $source = $source ?? self::$data;

        $province = $source[substr($code, 0, 2) . '0000'] ?? $returnUnknown;
        $city = $source[substr($code, 0, 4) . '00'] ?? ($returnUnitsDirectlyUnder ? '市辖区' : '');
        $area = $source[$code] ?? $returnUnknown;

        return [
            'province' => $province,
            'city' => $city,
            'area' => $area,
        ];
    }
}
