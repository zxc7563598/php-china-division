# hejunjie/china-division

English ｜ [简体中文](./README.zh-CN.md)

Regularly updated dataset of China's administrative divisions with ID-card address parsing. Distributed via Composer and versioned for use in forms, validation, and address-related scenarios.

**This project has been parsed by Zread. [View project overview](https://zread.ai/zxc7563598/php-china-division)**

## Features

- 📦 Nationwide administrative division data, synced regularly from public sources like the National Bureau of Statistics
- 🆔 ID-card parsing: address lookup, gender, date of birth, and checksum validation
- 📅 Historical data and change records — trace revoked, merged, or renamed regions
- 🔗 Built-in cascader data conversion, ready for frontend province-city-district linkage components
- 🏷 City tier classification (level_1 / level_2 / level_3)
- 🚀 Composer install, zero dependencies

## Requirements

- PHP >= 7.4

## Installation

```bash
composer require hejunjie/china-division
```

## Included Data Files

| File           | Description                                                   |
| -------------- | ------------------------------------------------------------- |
| `data.json`    | Current valid administrative division data                    |
| `history.json` | Historical region data (revoked, merged, or renamed regions)  |
| `diff.json`    | Administrative division change records (region code mappings) |

You can also work with the JSON files directly without the Division helper class.

## Usage

### Retrieving Data

```php
<?php
use Hejunjie\ChinaDivision\Division;

// Current division data
$data = Division::getData();

// Historical division data
$history = Division::getHistory();

// Change records
$diff = Division::getDiff();
```

### ID-card Parsing

```php
<?php
use Hejunjie\ChinaDivision\Division;

// Get province, city, and district from an ID-card number
$info = Division::getAddressInfo('11010119960124993X');
// ['province' => '北京市', 'city' => '市辖区', 'area' => '东城区']

// Get gender
$sex = Division::getGenderFromIdCard('11010119960124993X'); // '男'

// Get date of birth
$birthday = Division::getBirthdayFromIdCard('11010119960124993X');
// ['year' => '1996', 'month' => '01', 'day' => '24']

// Validate an ID-card number
$isValid = Division::isValidIdCard('11010119960124993X'); // true
```

### Cascader Data & City Tiers

```php
<?php
use Hejunjie\ChinaDivision\Division;

// Convert to cascader format (for frontend province-city-district linkage components)
$cascaderData = Division::convertToCascaderData();

// Get city tier data (level_1 / level_2 / level_3)
$levels = Division::getCityLevels();
```

> [!NOTE]
> The methods above provide basic usability. For performance-critical scenarios, consider caching the data in Redis and implementing your own query logic.

## Online Tool

Don't want to deploy? Use the online version: [china-division online tool](https://hejunjie.life/composer/china-division), with batch query support.

## Updates

Data is sourced from public channels such as the National Bureau of Statistics and updated periodically. PRs and issues for new data changes are always welcome.

---

This library will continue to be updated. Suggestions and feedback are always welcome.
