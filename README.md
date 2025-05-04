# hejunjie/china-division

<div align="center">
  <a href="./README.md">English</a>｜<a href="./README.zh-CN.md">简体中文</a>
  <hr width="50%"/>
</div>

Regularly updated dataset of China's administrative divisions with ID-card address parsing. Distributed via Composer and versioned for use in forms, validation, and address-related features

---

This is a nationwide provincial, city, and district division data repository that I maintain, containing the latest administrative division data, as well as historical records and address changes. It's mainly to make it easier to access this data in projects without having to manually search and organize it every time.

The data is synchronized and updated periodically, supports installation via Composer, and can also be processed by directly referencing the JSON file.

If you don't want to deploy it and just want to use it, you can 👉 [click here to use it](https://tools.hejunjie.life/#/external/china-division).

It also supports bulk queries.

## Installation

Install via Composer:

```bash
composer require hejunjie/china-division
```

## Included Data Files

`data.json`: The currently valid nationwide provincial, city, and district data.

`history.json`: Historical regional data (including areas that have been revoked, merged, or renamed).

`diff.json`: Records of administrative division changes (such as what changed from and to what).

You can directly import the JSON files for processing. The structure is clear and ready to use.

## Usage

I wrote a simple helper class called `Division` to facilitate data retrieval and common processing:

```php
<?php
use Hejunjie\ChinaDivision\Division;

// Retrieve current data
$data = Division::getData();

// Retrieve historical data
$history = Division::getHistory();

// Retrieve change records
$diff = Division::getDiff();
```

It also provides some commonly used methods that you can directly use. However, if you have performance requirements, I strongly recommend caching the data and implementing it yourself.

```php
<?php
use Hejunjie\ChinaDivision\Division;

// Retrieve province, city, and district information based on the ID card number
$info = Division::getAddressInfo('11010119960124993X');

// Convert to a data format suitable for cascading selectors (for front-end province-city-district linkage components)
$cascaderData = Division::convertToCascaderData();

// Retrieve gender information based on the ID card number
$sex = Division::getGenderFromIdCard('11010119960124993X');

// Validate ID card information
$isValid = Division::isValidIdCard('11010119960124993X');

// Retrieve the birth date (year, month, day) based on the ID card number
$birthday = Division::getBirthdayFromIdCard('11010119960124993X');

// Retrieve city data by levels (level_1, level_2, level_3)
$level = Division::getCityLevels();
```

## Update Notes

The data source comes from public channels such as the National Bureau of Statistics and will be updated periodically. If there is new data, feel free to submit a PR or issue to let me know.

## Purpose & Motivation

This set of data is mainly for my own project. Often, I need to handle tasks like address matching, province-city-district linkage, and historical data comparison. After searching around, I found that either the data was incomplete or the updates were slow, so I decided to create my own dataset, and also manage versions for easier reference.

If you happen to be working on something similar, I hope this repository can be of help to you 🙌.

## 🔧 Additional Toolkits (Can be used independently or installed together)

This project was originally extracted from [hejunjie/tools](https://github.com/zxc7563598/php-tools).
To install all features in one go, feel free to use the all-in-one package:

```bash
composer require hejunjie/tools
```

Alternatively, feel free to install only the modules you need：

[hejunjie/utils](https://github.com/zxc7563598/php-utils) - A lightweight and practical PHP utility library that offers a collection of commonly used helper functions for files, strings, arrays, and HTTP requests—designed to streamline development and support everyday PHP projects.

[hejunjie/cache](https://github.com/zxc7563598/php-cache) - A layered caching system built with the decorator pattern. Supports combining memory, file, local, and remote caches to improve hit rates and simplify cache logic.

[hejunjie/china-division](https://github.com/zxc7563598/php-china-division) - Regularly updated dataset of China's administrative divisions with ID-card address parsing. Distributed via Composer and versioned for use in forms, validation, and address-related features

[hejunjie/error-log](https://github.com/zxc7563598/php-error-log) - An error logging component using the Chain of Responsibility pattern. Supports multiple output channels like local files, remote APIs, and console logs—ideal for flexible and scalable logging strategies.

[hejunjie/mobile-locator](https://github.com/zxc7563598/php-mobile-locator) - A mobile number lookup library based on Chinese carrier rules. Identifies carriers and regions, suitable for registration checks, user profiling, and data archiving.

[hejunjie/address-parser](https://github.com/zxc7563598/php-address-parser) - An intelligent address parser that extracts name, phone number, ID number, region, and detailed address from unstructured text—perfect for e-commerce, logistics, and CRM systems.

[hejunjie/url-signer](https://github.com/zxc7563598/php-url-signer) - A PHP library for generating URLs with encryption and signature protection—useful for secure resource access and tamper-proof links.

[hejunjie/google-authenticator](https://github.com/zxc7563598/php-google-authenticator) - A PHP library for generating and verifying Time-Based One-Time Passwords (TOTP). Compatible with Google Authenticator and similar apps, with features like secret generation, QR code creation, and OTP verification.

[hejunjie/simple-rule-engine](https://github.com/zxc7563598/php-simple-rule-engine) - A lightweight and flexible PHP rule engine supporting complex conditions and dynamic rule execution—ideal for business logic evaluation and data validation.

👀 All packages follow the principles of being lightweight and practical — designed to save you time and effort. They can be used individually or combined flexibly. Feel free to ⭐ star the project or open an issue anytime!

---

This library will continue to be updated with more practical features. Suggestions and feedback are always welcome — I’ll prioritize new functionality based on community input to help improve development efficiency together.
