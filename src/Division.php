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
     * 获取完整历史地区数据
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
     * 
     * @return array
     */
    public static function getAddressInfo(string $idNumber, bool $returnUnitsDirectlyUnder = true, string $returnUnknown = '未知'): array
    {
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
                    return self::getAddressInfo($diffAddresses[0], $returnUnitsDirectlyUnder, $returnUnknown);
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
     * 内部加载历史地区 JSON 数据
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
