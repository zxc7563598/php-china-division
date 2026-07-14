# hejunjie/china-division

[English](./README.md) ｜ 简体中文

全国最新省市区行政区划数据，支持身份证号码解析地址。通过 Composer 分发、带版本管理，适用于表单选项、数据校验、地址解析等场景。

**本项目已由 Zread 完成解析，可点击快速了解：[查看项目概览](https://zread.ai/zxc7563598/php-china-division)**

## 特性

- 📦 全国省市区行政区划数据，定期从国家统计局等公开渠道同步更新
- 🆔 身份证号码解析：地址归属、性别、出生日期、校验位验证
- 📅 历史数据与变更记录追溯，已撤销/合并/改名的地区均可查询
- 🔗 内置级联选择器数据格式转换，直接适配前端省市区联动组件
- 🏷 城市分级数据（level_1 / level_2 / level_3）
- 🚀 Composer 安装，即装即用，无额外依赖

## 环境要求

- PHP >= 7.4

## 安装

```bash
composer require hejunjie/china-division
```

## 包含的数据文件

| 文件           | 说明                                     |
| -------------- | ---------------------------------------- |
| `data.json`    | 现行有效的全国省市区数据                 |
| `history.json` | 历史地区数据（已撤销、合并、改名的地区） |
| `diff.json`    | 行政区划变更记录（地区代码变更映射）     |

也可以直接引入 JSON 文件自行处理，不依赖 Division 辅助类。

## 使用方式

### 获取数据

```php
<?php
use Hejunjie\ChinaDivision\Division;

// 现行地区数据
$data = Division::getData();

// 历史地区数据
$history = Division::getHistory();

// 变更记录
$diff = Division::getDiff();
```

### 身份证解析

```php
<?php
use Hejunjie\ChinaDivision\Division;

// 根据身份证号获取省市区信息
$info = Division::getAddressInfo('11010119960124993X');
// ['province' => '北京市', 'city' => '市辖区', 'area' => '东城区']

// 获取性别
$sex = Division::getGenderFromIdCard('11010119960124993X'); // '男'

// 获取出生日期
$birthday = Division::getBirthdayFromIdCard('11010119960124993X');
// ['year' => '1996', 'month' => '01', 'day' => '24']

// 校验身份证号是否合法
$isValid = Division::isValidIdCard('11010119960124993X'); // true
```

### 级联数据与城市分级

```php
<?php
use Hejunjie\ChinaDivision\Division;

// 转换为级联选择器格式（适用于前端省市区联动组件）
$cascaderData = Division::convertToCascaderData();

// 获取城市分级数据（level_1 / level_2 / level_3）
$levels = Division::getCityLevels();
```

> [!NOTE]
> 以上方法保障了基本可用性。如果你对性能有较高要求，建议将数据缓存至 Redis 后自行实现查询逻辑。

## 在线工具

不想部署？可以直接使用在线版本：[china-division 在线工具](https://hejunjie.life/composer/china-division)，支持批量查询。

## 更新说明

数据源来自国家统计局等公开渠道，不定期更新。欢迎通过 PR 或 Issue 提交新的数据变更。

---

该库将持续更新，欢迎提供建议和反馈。
