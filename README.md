IconCaptcha Plugin - jQuery & PHP
===================

[![Version](https://img.shields.io/badge/Version-v2.1.0-orange.svg?style=flat-square)]() [![License](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square)]() [![Maintenance](https://img.shields.io/badge/Maintained-Yes-green.svg?style=flat-square)]()
[![Donate](https://img.shields.io/badge/Donate-PayPal-yellow.svg?style=flat-square)](https://paypal.me/nlgamevideosnl)

IconCaptcha is a faster and more user-friendly captcha than most other captchas. You no longer have to read any annoying 
text-images, with IconCaptcha you only have to compare two images and select the image which is only present once.

Besides being user-friendly, IconCaptcha is also developer-friendly. With just a few steps you can get the captcha up and running. 
Even developers new to JavaScript and PHP can easily install IconCaptcha. The demo page contains all the code needed to get the captcha working.

<img src="https://i.imgur.com/IO5XyPV.jpg" /> <img src="https://i.imgur.com/tp7028J.jpg" />

### A WordPress version of the IconCaptcha plugin will soon be available!

### <a href="https://github.com/fabianwennink/jQuery-Icon-Captcha-Plugin/releases">Download IconCaptcha now</a>
### <a href="https://www.fabianwennink.nl/projects/IconCaptcha/v2/">View Demo</a>

## Features
* __User Friendly:__ The captcha uses easily understandable images instead of hard to read texts to complete the captcha.
* __Server-side validation:__ Any validation done by the captcha will be performed on the server-side instead of the client-side.
* __Events:__ Events are triggered at various points in the code, allowing you to hook into them and execute custom code if necessary.
* __Themes:__ Select the design of the captcha without having to ever touch the stylesheet.
* __SCSS:__  The project contains a SCSS file, allowing you to easily style and compile the stylesheet.
* __Supports IE:__  The captcha _supports_ Internet Explorer 8 and up.

## Usage
```html
<form action="" method="post">
    ...
	
    <!-- The captcha will be generated in this element -->
    <div class="captcha-holder"></div>

    ...
</form>

...

<script>
    $('.captcha-holder').iconCaptcha({
        // The captcha options go here
    });
</script>
```

PHP form validation:

```php
<?php
    // Start a PHP session.
    session_start();
	
    // Include the IconCaptcha class.
    require('resources/php/captcha.class.php');

    // Set the path to the captcha icons. Set it as if you were
    // currently in the PHP folder containing the captcha.class.php file.
    // ALWAYS END WITH A /
    // DEFAULT IS SET TO ../icons/
    IconCaptcha::setIconsFolderPath("../icons/");
	
    // If the form has been submitted, validate the captcha.
    if(!empty($_POST)) {
        if(IconCaptcha::validateSubmission($_POST)) {
            echo 'The form has been submitted!';
        } else {
            echo IconCaptcha::getErrorMessage();
        }
    }
?>
```

## Options

The following options allow you to customize the IconCaptcha to your liking. All of the options are __optional__, however you might still want to take a look at the ```captchaAjaxFile``` option and make sure the path is set correctly.

| Option | Description |
| ------ | ------ |
| captchaTheme | Allows you to select the theme of the captcha. At the moment you can only choose between _light_ and _dark_. |
| captchaFontFamily | Allows you to select the font family of the captcha. Leaving this option blank will add the default font _(Roboto)_ to the end of your ```<body>``` tag. |
| captchaClickDelay | The time _(in milliseconds)_ during which the user can't select an image. Set to 0 to disable. |
| captchaHoverDetection | Prevent clicking on any captcha icon until the cursor hovered over the captcha at least once. |
| enableLoadingAnimation | Enable or disable the _fake_ loading animation after clicking on an image.  |
| loadingAnimationDelay | The time _(in milliseconds)_ during which the _fake_ loading animation will play until the user input actually gets validated. |
| showBoxShadow | Enable or disable the CSS box-shadow of the captcha holder. The box-shadow will match the selected captcha theme. |
| showCredits | Enable or disable the credits element of the captcha. <br>_Please leave it enabled so people can find and use the captcha themselved._ |
| captchaAjaxFile | The path to ```captcha-request.php```. Make sure you use the correct path else the captcha won't be able to request the hashes, images or input validation. |
| captchaMessages <sup>__(new)__</sup> | Change the messages used by the captcha. All the changeable strings can be found in the _index.php_. |
| requestIconsDelay <sup>__(new)__</sup> | The captcha will not request hashes or images from the server until after this delay _(in milliseconds)_. If a page displaying one or more captchas gets constantly refreshes (during an attack?), it will not request the resources right away. |

## Events
Events will be triggered at various point in the code. You can use a custom script to hook into the events and execute your own code if necessary.

| Event | Description |
| ------ | ------ |
| init.iconCaptcha | Will fire when the captcha has been fully built and the hashes and icons have been requested from the server. |
| refreshed.iconCaptcha | Will fire when the user selected the incorrect icon and the captcha is done refreshing it's hashes and icons. |
| selected.iconCaptcha | Will fire when the user selects an icon, regarless of if the icon is correct or not. |
| success.iconCaptcha | Will fire when the user selected the correct icon. |
| error.iconCaptcha | Will fire when the user selected the incorrect icon. |

## Requirements
* __PHP 5.2+__
* __jQuery 1.12.3+__

## Where is version 1?!
Version 1 was never created with the intention of it being used on live websites. This caused the security to lack which left massive loopholes around the captcha. 
A big part of the captcha validation was performed on the client-side, making it possible to manipulate the captcha by loading in custom scripts. It's design was also horrible and too big to fit with todays websites.

You can still download version 1 <a href="https://www.fabianwennink.nl/projects/IconCaptcha/v1/" target="_blank">here</a>, but remember that it's a bug- and exploit-filled version __AND SHOULD NOT BE USED ON A LIVE WEBSITE.__

## Credits
The icons used in this project are made by <a href="https://www.webalys.com" target="_blank" rel="nofollow">Webalys</a>.

## License
This project is licensed under the <a href="https://github.com/fabianwennink/jQuery-Icon-Captcha-Plugin/blob/master/LICENSE">MIT</a> license.
