# IconCaptcha - PHP & JavaScript

![Version](https://img.shields.io/badge/Version-3.1.2-orange.svg?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)
[![Issues](https://img.shields.io/github/issues/fabianwennink/IconCaptcha-Plugin-jQuery-PHP?style=flat-square)](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/issues)
[![Support](https://img.shields.io/badge/Support-PayPal-yellow.svg?style=flat-square)](https://paypal.me/nlgamevideosnl)
[![Support](https://img.shields.io/badge/Support-Buy_Me_A_Coffee-yellow.svg?style=flat-square)](https://www.buymeacoffee.com/fabianwennink)

[![Sonar Quality](https://img.shields.io/sonar/alert_status/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud)](https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-Plugin-jQuery-PHP)
[![Sonar Security](https://img.shields.io/sonar/security_rating/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud&color=%234c1)](https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-Plugin-jQuery-PHP)
[![Sonar Bugs](https://img.shields.io/sonar/bugs/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud)](https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-Plugin-jQuery-PHP)
[![Sonar Vulnerabilities](https://img.shields.io/sonar/vulnerabilities/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud)](https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-Plugin-jQuery-PHP)

<br><img src="http://i.imgur.com/RMUALSz.png" />

IconCaptcha is a self-hosted captcha which is faster, more user-friendly and more customizable than other captchas. Users no longer have to read any annoying 
text images, solve difficult math equations or play any puzzle games. IconCaptcha is simple: Compare up to 8 icons and select the icon type shown the least amount of times.

Aside from being user-friendly, IconCaptcha is also developer-friendly. In just a few steps you can get your own installation of IconCaptcha up and running. 
Even developers new to JavaScript and PHP can easily install IconCaptcha. The included demo pages in this repository contain all the code required to make IconCaptcha work. 
For more detailed information, please read the information written on this page.

<img src="https://i.imgur.com/9RGFZSC.png" title="IconCaptcha" alt="IconCaptcha" />

___
### <a href="https://www.fabianwennink.nl/projects/IconCaptcha/v2/">View live demo</a>
### <a href="https://github.com/fabianwennink/jQuery-Icon-Captcha-Plugin/releases">Download IconCaptcha for PHP now</a>
___

**<a href="https://github.com/fabianwennink/IconCaptcha-Plugin-ASP.NET">Visit IconCaptcha for ASP.NET here.</a>** _(no longer maintained, last stable version is 1.0.0)_
___

## Features
* __User Friendly:__ The captcha uses easily understandable images instead of hard to read texts to complete the captcha.
* __Server-side Validation:__ All validation done by the captcha will be performed on the server-side instead of the client-side.
* __Self Hosted:__ Because IconCaptcha is a self-hosted plugin, you are not relying on any third party.
* __No Data Sharing:__ Unlike captchas such as Google ReCaptcha, no user data will be stored or sold to third parties.
* __jQuery Support:__ IconCaptcha is written in plain JavaScript, but hooks into jQuery to allow you to integrate it in your jQuery code.
* __Modern Design:__ The look and feel of IconCaptcha fits every modern website design.
* __Events:__ Events are triggered at various points in the code, allowing you to hook into them and execute custom code if necessary.
* __Themes:__ Select the design of the captcha without having to ever touch a stylesheet, or create your own custom theme.
* __SASS:__ The project contains a SASS file, allowing you to easily style and compile the stylesheet.
* __IE 10+ Support:__ IconCaptcha has been tested in Internet Explorer 10 & 11 and is functional in both versions.

## New in v3
In version 3 of IconCaptcha, the whole plugin got an overhaul - both client-side and server-side. With better security features, more customizations/options, new 
themes, no more jQuery dependency and 180 icons, version 3 is the biggest change to IconCaptcha yet.

* No longer required to use jQuery, although IconCaptcha can still be used with jQuery.
* More captcha image generation options to increase the difficulty.
* Automatic captcha invalidation after a period of no user interaction.
* Automatic timeouts when too many incorrect selections were made by the user.
* New light and dark themes with more modern designs, with improved support for custom themes.
* Includes 180 new modern icons, created by <a href="https://streamlinehq.com" target="_blank" rel="nofollow">Streamline</a>.
* Better stability, general code improvements and bug fixes.

# Wiki
For information on how to install, set up and configure IconCaptcha, please check the Wiki pages:

* [How To Use](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use) - A guide on how to setup and use IconCaptcha.
    * [Requirements](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#requirements) - A list of requirements to get IconCaptcha working properly on your website.
    * [Download](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#download) - Information on how to download the required client-side and server-side packages.
    * [Usage](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#usage) - Details on how to implement IconCaptcha in both the client-side and server-side.
        * [HTML](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#html)
        * [JavaScript/jQuery](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#javascript--jquery)
        * [PHP](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#php)
    * [Options](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#options) - All available configuration options, including their meaning and default values.
        * [PHP](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#php-1)
        * [JavaScript/jQuery](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#javascript--jquery-1)
    * [Messages](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#messages) - All the localization strings used in IconCaptcha.
        * [PHP](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#php-2)
        * [JavaScript/jQuery](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#javascript--jquery-2)
    * [Events](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#events) - All client-side events which are triggered at various points in the captcha process.
    * [Custom Themes](https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/wiki/How-To-Use#custom-themes) - Details on how to implement and enable custom themes.

## Credits
The icons used in this project are made by <a href="https://streamlinehq.com" target="_blank" rel="nofollow">Streamline</a>.

## License
This project is licensed under the <a href="https://github.com/fabianwennink/jQuery-Icon-Captcha-Plugin/blob/master/LICENSE">MIT</a> license.
