<br/>
<p align="center">
    <picture>
      <source media="(prefers-color-scheme: dark)" srcset="https://i.imgur.com/k8sIUQI.png">
      <source media="(prefers-color-scheme: light)" srcset="https://i.imgur.com/RMUALSz.png">
      <img alt="IconCaptcha Logo" src="https://i.imgur.com/RMUALSz.png">
    </picture>
</p>

<p align="center">
    <strong>A self-hosted, customizable, easy-to-implement and user-friendly captcha.</strong>
</p>

<p align="center">
    <a href="https://github.com/fabianwennink/IconCaptcha-PHP/releases"><img src="https://img.shields.io/badge/version-4.0.3-orange.svg?style=flat-square" alt="Version" /></a>
    <a href="https://packagist.org/packages/fabianwennink/iconcaptcha"><img src="https://img.shields.io/packagist/v/fabianwennink/iconcaptcha.svg?style=flat-square" alt="Latest Version on Packagist" /></a>
    <a href="https://fabianwennink.nl/projects/IconCaptcha/license"><img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="License" /></a>
    <a href="https://paypal.me/nlgamevideosnl"><img src="https://img.shields.io/badge/support-PayPal-lightblue.svg?style=flat-square" alt="Support via PayPal" /></a>
    <a href="https://www.buymeacoffee.com/fabianwennink"><img src="https://img.shields.io/badge/support-Buy_Me_A_Coffee-lightblue.svg?style=flat-square" alt="Buy me a coffee" /></a>
</p>

<p align="center">
    <a href="https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-PHP"><img src="https://img.shields.io/sonar/alert_status/fabianwennink_IconCaptcha-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" alt="Sonar Quality" /></a>
    <a href="https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-PHP"><img src="https://img.shields.io/sonar/security_rating/fabianwennink_IconCaptcha-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud&color=%234c1" alt="Sonar Security" /></a>
    <a href="https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-PHP"><img src="https://img.shields.io/sonar/bugs/fabianwennink_IconCaptcha-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" alt="Sonar Bugs" /></a>
    <a href="https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-PHP"><img src="https://img.shields.io/sonar/vulnerabilities/fabianwennink_IconCaptcha-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" alt="Sonar Vulnerabilities" /></a>
</p>

___

Introducing IconCaptcha, a self-hosted captcha solution that's designed to be fast, user-friendly, and highly customizable. Unlike other captchas, IconCaptcha spares users the need of deciphering hard-to-read text images, solving complex math problems, or engaging with perplexing puzzle games. Instead, with IconCaptcha it's as straightforward as comparing up to 8 icons and selecting the least common one.

IconCaptcha doesn't just prioritize users; it's also developer-friendly. In just a few steps, you can have IconCaptcha integrated into your website. Even if you're new to PHP and JavaScript, installing IconCaptcha is a straightforward process. The included demo pages in this repository provide all the necessary code to get IconCaptcha up and running. For more in-depth insights, take a moment to explore the information provided on this page and the wiki.

___

### [▶ Try the live demo here!](https://www.fabianwennink.nl/projects/IconCaptcha/#!demonstration)

<img src="https://i.imgur.com/WsWdBRL.png" title="IconCaptcha widget examples" alt="IconCaptcha light and dark theme widget examples." />

**[Using ASP.NET instead? Try IconCaptcha for ASP.NET](https://github.com/fabianwennink/IconCaptcha-ASP.NET)** _(not currently maintained - will continue in Q4 of 2023)_
___

## Installation

### Composer
```bash
composer require fabianwennink/iconcaptcha
```
Once the package has been installed, continue with the remaining [installation instructions](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Getting-Started#installation), followed by the [setup instructions](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Implementation).

### Manual Installation
It is recommended to use Composer. However, if you are unable to, follow the [manual installation instructions](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Getting-Started#manual-installation) and [setup instructions](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Implementation).

## Features
* 🏠 __Self-Hosted:__ As a self-hosted solution, IconCaptcha eliminates reliance on third-party services, keeping things under your control.
* 🚫 __No Data Sharing:__ Unlike other captchas, IconCaptcha's self-hosted nature ensures no user data is shared with third parties.
* 🔐 __Server-Side Validation:__ All validation is carried out server-side, boosting security by eliminating exposure of sensitive processes on the client side.
* 😊 __User-Friendly:__ Replace complex captchas for easily understandable images, delivering a smoother experience for your users.
* 💾 __Database Support:__ Store and manage challenge data using various popular databases like MySQL, SQL Server, PostgreSQL, and SQLite.
* 🌐 __Cross-Domain Integration:__ With CORS support, IconCaptcha effortlessly integrates into applications spanning different domains.
* 🎣 __Events and Hooks:__ Events are triggered throughout the code, allowing you to inject custom code and fine-tune the experience to your needs.
* 🎨 __Contemporary Design:__  IconCaptcha's modern design seamlessly integrates with a diverse range of styles.
* 🖌️ __Themes:__  Choose from existing themes, or craft your own unique theme using the provided SASS file.
* 🔌 __jQuery Integration:__ While written in plain JavaScript, IconCaptcha smoothly integrates with jQuery.

## What's New in IconCaptcha 4
Version 4 of IconCaptcha introduces significant enhancements to the architecture and feature set, and offers greater flexibility and customization, alongside improved security. Here are the key updates in this release:

* 📦 **Composer Compatibility:** IconCaptcha is now available on Packagist and can be installed using Composer.
* 🏗️ **Restructured Code Base:** The entire code base has been restructured, moving away from the previous one-file-does-everything approach. This restructuring makes it easier to maintain the code in the future.
* 📃 **Improved Configuration:** IconCaptcha 4 introduces a more comprehensive and polished configuration file, offering more options for customization alongside clearer descriptions for each choice.
* 💾 **Database Support:** A notable addition to this version is the inclusion of database support. Challenge data can now be stored using well-known databases like MySQL, SQL Server, PostgreSQL, and SQLite.
* 🧩 **Custom Drivers:** Want to handle certain server-side aspects your own way? IconCaptcha 4 allows you to implement custom drivers for critical features such as session management, storage, and timeout handling.
* 🎣 **Server-side Hooks:** Hook into server-side events like captcha initialization, challenge generation, and solution processing to customize the processes according to your application's needs.
* 🌐 **CORS Support:** Cross-Origin Resource Sharing (CORS) support is now available, allowing IconCaptcha to be integrated into applications spread across different domains while maintaining security standards.

# Wiki
For instructions on installing, setting up, and configuring IconCaptcha, be sure to explore the Wiki pages:

* [Requirements](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Getting-Started#requirements) - A list of requirements to get IconCaptcha working properly.
* [Installation](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Getting-Started#installation) - Instructions on how to install/download IconCaptcha.
* [Implementing](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Implementation) - Step-by-step instructions on how to implement IconCaptcha.
* [Configuration](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Configuration) - Explanation of all available configuration options.
* [Storage](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Storage) - Details about all storage options.
* [Challenge Generator](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Challenge-Generator) - Information about how to implement a custom challenge generator.
* [Validation](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Validation) - Explanation of the validation process and error handling.
* [Hooks & Events](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Hooks-&-Events) - Overview of events which are triggered at different stages in the captcha process.
* [Token](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Token) - Information about the usage of the optional widget security token.
* [Themes](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Themes) - Details on creating and setting up custom themes.
* [Localization](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Localization) - Details on how to customize the localization strings.

## Credits
The icons used in this project are made by [BlendIcons](https://blendicons.com/).

## License
This project is licensed under the [MIT](https://www.fabianwennink.nl/projects/IconCaptcha/license) license.
