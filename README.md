# hejunjie/china-division

这是我自己维护的一个全国省市区划分数据仓库，包含了最新的行政区划数据，也保留了历史记录和地址变更情况。主要是为了方便项目里需要用到这些数据的时候不用每次都去手动找、整理。

数据每隔一段时间会同步更新，支持通过 Composer 安装，也可以直接引用 JSON 文件做处理。

如果你不想要部署，只是想要进行使用，可以 👉 [点击此处进行使用](https://tools.hejunjie.life/#/external/china-division)

支持批量查询

## 安装方式

```bash
composer require hejunjie/china-division
```

## 包含的数据文件

`data.json`：现行有效的全国省市区数据

`history.json`：历史地区数据（包括曾经存在但已撤销、合并、改名的）

`diff.json`：行政区划的变更记录（比如从哪里改成了什么）

都可以直接引入 JSON 文件来处理，结构比较清晰，拿来用就行。

## 使用方式

我写了一个简单的辅助类 Division 来方便获取数据和常用处理：

```php
<?php
use Hejunjie\ChinaDivision\Division;

// 获取当前数据
$data = Division::getData();

// 获取历史数据
$history = Division::getHistory();

// 获取变更记录
$diff = Division::getDiff();
```

另外也提供了一些常用的方法，直接可以拿来用，但如果你对性能有要求，强烈建议对数据进行缓存后自行实现

```php
<?php
use Hejunjie\ChinaDivision\Division;

// 根据身份证获取对应省市区的信息
$info = Division::getAddressInfo('11010119960124993X'); 

// 转成级联选择器可用的数据格式（适用于前端省市区联动组件）
$cascaderData = Division::convertToCascaderData();

// 根据身份证获取性别信息
$sex = Division::getGenderFromIdCard('11010119960124993X');

// 校验身份证信息
$isValid = Division::isValidIdCard('11010119960124993X');

// 根据身份证号获取出生日期（年、月、日）
$birthday = Division::getBirthdayFromIdCard('11010119960124993X');

// 获取分级的城市数据（level_1、level_2、level_3）
$level = Division::getCityLevels();
```

## 更新说明

数据源来自国家统计局等公开渠道，会不定期进行更新，有新数据也欢迎提 PR 或 issue 告诉我。

## 用途 & 初衷

这套数据主要是我自己项目用的，很多时候要处理地址匹配、省市区级联、历史数据比对之类的事情。找了一圈，要么数据不全，要么更新慢，就干脆自己做一份，顺便版本管理一下，方便引用。

如果你刚好也在做类似的事情，希望这个仓库能帮上忙 🙌。

## 🔧 更多工具包（可独立使用，也可统一安装）

本项目最初是从 [hejunjie/tools](https://github.com/zxc7563598/php-tools) 拆分而来，如果你想一次性安装所有功能组件，也可以使用统一包：

```bash
composer require hejunjie/tools
```

当然你也可以按需选择安装以下功能模块：

[hejunjie/cache](https://github.com/zxc7563598/php-cache) - 多层缓存系统，基于装饰器模式。

[hejunjie/error-log](https://github.com/zxc7563598/php-error-log) - 责任链日志上报系统。

[hejunjie/mobile-locator](https://github.com/zxc7563598/php-mobile-locator) - 国内手机号归属地 & 运营商识别。

[hejunjie/utils](https://github.com/zxc7563598/php-utils) - 常用工具方法集合。

[hejunjie/address-parser](https://github.com/zxc7563598/php-address-parser) - 收货地址智能解析工具，支持从非结构化文本中提取用户/地址信息。

[hejunjie/url-signer](https://github.com/zxc7563598/php-url-signer) - URL 签名工具，支持对 URL 进行签名和验证。

[hejunjie/google-authenticator](https://github.com/zxc7563598/php-google-authenticator) - Google Authenticator 及类似应用的密钥生成、二维码创建和 OTP 验证。

[hejunjie/simple-rule-engine](https://github.com/zxc7563598/php-simple-rule-engine) - 一个轻量、易用的 PHP 规则引擎，支持多条件组合、动态规则执行。

👀 所有包都遵循「轻量实用、解放双手」的原则，能单独用，也能组合用，自由度高，欢迎 star 🌟 或提 issue。

---

该库后续将持续更新，添加更多实用功能。欢迎大家提供建议和反馈，我会根据大家的意见实现新的功能，共同提升开发效率。








