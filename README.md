# IconCaptcha Plugin - jQuery & PHP

![Version](https://img.shields.io/badge/Version-2.5.0-orange.svg?style=flat-square) ![License](https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square) ![Maintenance](https://img.shields.io/badge/Maintained-Yes-green.svg?style=flat-square)
[![Donate](https://img.shields.io/badge/Donate-PayPal-yellow.svg?style=flat-square)](https://paypal.me/nlgamevideosnl)

[![Sonar Quality](https://sonarcloud.io/api/project_badges/measure?project=fabianwennink_IconCaptcha-Plugin-jQuery-PHP&metric=alert_status)](https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-Plugin-jQuery-PHP)
[![Sonar Security](https://sonarcloud.io/api/project_badges/measure?project=fabianwennink_IconCaptcha-Plugin-jQuery-PHP&metric=security_rating)](https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-Plugin-jQuery-PHP)
[![Sonar Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=fabianwennink_IconCaptcha-Plugin-jQuery-PHP&metric=vulnerabilities)](https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-Plugin-jQuery-PHP)

<br><img src="http://i.imgur.com/RMUALSz.png" />

IconCaptcha is a faster and more user-friendly captcha than most other captchas. You no longer have to read any annoying 
text-images, with IconCaptcha you only have to compare two images and select the image which is only present once.

Besides being user-friendly, IconCaptcha is also developer-friendly. With just a few steps you can get the captcha up and running. 
Even developers new to JavaScript and PHP can easily install IconCaptcha. The demo page contains all the code needed to get the captcha working.

<img src="https://i.imgur.com/IO5XyPV.jpg" /> <img src="https://i.imgur.com/tp7028J.jpg" />
___

### <a href="https://github.com/fabianwennink/jQuery-Icon-Captcha-Plugin/releases">Download IconCaptcha for PHP now</a>
### <a href="https://www.fabianwennink.nl/projects/IconCaptcha/v2/">View live demo</a>
___

##### <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-ASP.NET">Visit IconCaptcha for ASP.NET here.</a>
___

## Features
* __User Friendly:__ The captcha uses easily understandable images instead of hard to read texts to complete the captcha.
* __Server-side validation:__ Any validation done by the captcha will be performed on the server-side instead of the client-side.
* __Events:__ Events are triggered at various points in the code, allowing you to hook into them and execute custom code if necessary.
* __Themes:__ Select the design of the captcha without having to ever touch the stylesheet.
* __SASS:__  The project contains a SASS file, allowing you to easily style and compile the stylesheet.
* __Supports IE:__  The captcha _supports_ Internet Explorer 8 and up.

## Requirements
* __PHP >= 5.2__
* __jQuery >= 1.12.3__

## Installation

1. Download <a href="https://github.com/fabianwennink/jQuery-Icon-Captcha-Plugin/releases">IconCaptcha for PHP</a>.
2. Download the <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-Front-End/releases">IconCaptcha Front-End package</a>.
3. Unpack both repositories to somewhere on your computer.
4. Drag the content of the ```dist/``` folder of the IconCaptcha Front-End package into the ```assets/``` folder of the IconCaptcha PHP package.
5. Check the ```assets/``` folder and make sure you see the following sub-folders: ```css/```, ```icons/``` and ```js/```.

_Note: To make it easier to test the example captchas, the Front-End package is already included in this repository. 
However, these files might not always be up-to-date. To ensure you always have the latest version, please follow the instructions above._

## Usage

__HTML form:__
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


__PHP form validation:__

```php
<?php
    // Start a PHP session.
    session_start();
    
    // Include the IconCaptcha classes.
    require('src/captcha-session.class.php');
    require('src/captcha.class.php');

    // Set the path to the captcha icons. Set it as if you were
    // currently in the PHP folder containing the captcha.class.php file.
    // ALWAYS END WITH A /
    // DEFAULT IS SET TO ../icons/
    IconCaptcha::setIconsFolderPath('../assets/icons/');

    // Enable or disable the 'image noise' option.
    // When enabled, some nearly invisible random pixels will be added to the
    // icons. This is done to confuse bots who download the icons to compare them
    // and pick the odd one based on those results.
    // NOTE: Enabling this might cause a slight increase in CPU usage.
    IconCaptcha::setIconNoiseEnabled(true);

    // Use custom messages as error messages (optional).
    // Take a look at the README file to see what each string means.
    // IconCaptcha::setErrorMessages('', '', '', '');
    
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

The following options allow you to customize IconCaptcha to your liking. All of the options are __optional__, however you might still want to take a look at the ```captchaAjaxFile``` option and make sure the path is set correctly.

| Option | Description |
| ------ | ------ |
| theme | Allows you to select the theme of the captcha. At the moment you can only choose between _light_ and _dark_. You can always add your own custom themes by changing the SCSS file. |
| fontFamily | Allows you to select the font family of the captcha. Leaving this option blank will result in the use of the default font ```Arial```. |
| clickDelay | The time _(in milliseconds)_ during which the user can't select an image. Set to 0 to disable. |
| invalidResetDelay | The time _(in milliseconds)_ it takes to reset the captcha after a wrong icon selection. Set to 0 to disable. |
| requestIconsDelay | The captcha will not request hashes or images from the server until after this delay _(in milliseconds)_. If a page displaying one or more captchas gets constantly refreshed (during an attack?), it will not request the resources right away. |
| loadingAnimationDelay | The time _(in milliseconds)_ during which the _fake_ loading animation will play until the user input actually gets validated. |
| hoverDetection | Prevent clicking on any captcha icon until the cursor hovered over the captcha at least once. |
| showCredits | Show, hide or disable the credits element of the captcha. Hiding the credits will still add the credits to the HTML, but it will not be visible (only to crawlers). Disabling the credits will neither show or add the HTML. Use _'show'_, _'hide'_ or _'disabled'_.<br>_Please leave it enabled so people can find and use the captcha themselves._ |
| enableLoadingAnimation | Enable or disable the _fake_ loading animation after clicking on an image.  |
| validationPath | The path to ```captcha-request.php```. Make sure you use the correct path else the captcha won't be able to request the hashes, images or validate the input. |
| messages | Change the messages used by the captcha. All the changeable strings can be found down below. |

## Messages
The following strings will be used by the captcha and can be changed / translated to your liking.

| Error/event | Default | PHP/JS |
| ------ | ------ | ------ |
| Captcha Header | Select the icon that does not belong in the row. | JS |
| Captcha Correct Title | Great! | JS |
| Captcha Correct Subtitle | You do not appear to be a robot. | JS |
| Captcha Incorrect Title | Oops! | JS |
| Captcha Incorrect Subtitle | You've selected the wrong image. | JS |
| Wrong Image | You've selected the wrong image. | PHP |
| No Image Selected | No image has been selected. | PHP |
| Empty Form | You've not submitted any form. | PHP |
| Invalid Captcha ID | The captcha ID was invalid. | PHP |

## Events
Events will be triggered at various point in the code. You can use a custom script to hook into the events and execute your own code if necessary.

| Event | Description |
| ------ | ------ |
| init.iconCaptcha | Will fire when the captcha has been fully built and the hashes and icons have been requested from the server. |
| refreshed.iconCaptcha | Will fire when the user selected the incorrect icon and the captcha is done refreshing it's hashes and icons. |
| selected.iconCaptcha | Will fire when the user selects an icon, regarless of if the icon is correct or not. |
| success.iconCaptcha | Will fire when the user selected the correct icon. |
| error.iconCaptcha | Will fire when the user selected the incorrect icon. |

## Credits
The icons used in this project are made by <a href="https://www.webalys.com" target="_blank" rel="nofollow">Webalys</a>.

## License
This project is licensed under the <a href="https://github.com/fabianwennink/jQuery-Icon-Captcha-Plugin/blob/master/LICENSE">MIT</a> license.
