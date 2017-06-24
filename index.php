<?php
    /**
     * IconCaptcha Plugin: v2.1.0
     * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */

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
            echo '<b>Captcha:</b> The form has been submitted!';
        } else {
            echo '<b>Captcha: </b>' . json_decode(IconCaptcha::getErrorMessage())->error;
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>IconCaptcha Plugin v2 - By Fabian Wennink</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=9" />
        <meta name="author" content="Fabian Wennink © <?= date('Y') ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- Include IconCaptcha stylesheet -->
        <link href="resources/style/css/style.css" rel="stylesheet" type="text/css">

        <!-- CSS to style the page a bit - not important, can be deleted -->
        <style>
            body { font-family: 'Roboto', sans-serif; }
            form { margin-bottom: 50px; }

            .captcha-holder { margin: 20px 0; }
            .github { margin-bottom: 50px; }
            .github a { color: #2d2d2d; }

            input[type="submit"] {
                max-width: 325px;
                width: 100%;
                background: #5f5f5f;
                border: 0;
                padding: 10px;
                color: #fff;
                font-size: 14px;
                border-radius: 3px;
                cursor: pointer;
                outline: 0;
            }
        </style>
    </head>
    <body>
        <h1>IconCaptcha Plugin - jQuery & PHP</h1>

        <img src="https://img.shields.io/badge/Version-v2.1.0-orange.svg?style=flat-square" /> <img src="https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square" />
        <img src="https://img.shields.io/badge/Maintained-Yes-green.svg?style=flat-square" /> <a href="https://paypal.me/nlgamevideosnl" target="_blank"><img src="https://img.shields.io/badge/Donate-PayPal-yellow.svg?style=flat-square" /></a>

        <p class="github"><a href="https://github.com/fabianwennink/jQuery-Icon-Captcha-Plugin/" target="_blank">View project on GitHub</a></p>

        <!-- Just a basic HTML form, captcha should ALWAYS be placed WITHIN the <form> element -->
        <h2>Form #1</h2>
        <form action="" method="post">

            <!-- Element that we use to create the IconCaptcha with -->
            <div class="captcha-holder"></div>

            <!-- Submit button to test your IconCaptcha input -->
            <input type="submit" value="Submit form #1 to test captcha" >
        </form>

        <!-- Just a basic HTML form, captcha should ALWAYS be placed WITHIN the <form> element -->
        <h2>Form #2</h2>
        <form action="" method="post">

            <!-- Element that we use to create the IconCaptcha with -->
            <div class="captcha-holder"></div>

            <!-- Submit button to test your IconCaptcha input -->
            <input type="submit" value="Submit form #2 to test captcha" >
        </form>

        <!-- Include jQuery Library -->
        <!--[if lt IE 9]>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js"></script>
        <![endif]-->

        <!--[if (gte IE 9) | (!IE)]><!-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
        <!--<![endif]-->

        <!-- Include IconCaptcha script -->
        <script src="resources/js/script.min.js" type="text/javascript"></script>

        <!-- Initialize the IconCaptcha -->
        <script async type="text/javascript">
            $(window).ready(function() {
                $('.captcha-holder').iconCaptcha({
                    captchaTheme: ["light", "dark"], // Select the theme(s) of the Captcha(s). Available: light, dark
                    captchaFontFamily: '', // Change the font family of the captcha. Leaving it blank will add the default font to the end of the <body> tag.
                    captchaClickDelay: 500, // The delay during which the user can't select an image.
                    captchaHoverDetection: true, // Enable or disable the cursor hover detection.
                    showBoxShadow: false, // Show or hide the box shadow effect.
                    showCredits: true, // Show or hide the credits element (please leave it enbled).
                    enableLoadingAnimation: true, // Enable of disable the fake loading animation. Doesn't do anything, just looks cool ;)
                    loadingAnimationDelay: 2500, // How long the fake loading animation should play.
                    requestIconsDelay: 1500, // How long should the script wait before requesting the hashes and icons? (to prevent a high(er) CPU usage during a DDoS attack)
                    captchaAjaxFile: 'resources/php/captcha-request.php', // The path to the Captcha validation file.
                    captchaMessages: { // You can put whatever message you want in the captcha.
                        header: "Select the image that does not belong in the row",
                        correct: {
                            top: "Great!",
                            bottom: "You do not appear to be a robot."
                        },
                        incorrect: {
                            top: "Oops!",
                            bottom: "You've selected the wrong image."
                        }
                    }
                })
                .bind('init.iconCaptcha', function() { // You can bind to custom events, in case you want to execute some custom code.
                    console.log('Event: Captcha initialized');
                }).bind('selected.iconCaptcha', function() {
                    console.log('Event: Icon selected');
                }).bind('refreshed.iconCaptcha', function() {
                    console.log('Event: Captcha refreshed');
                }).bind('success.iconCaptcha', function() {
                    console.log('Event: Correct input');
                }).bind('error.iconCaptcha', function() {
                    console.log('Event: Wrong input');
                });
            });
        </script>
    </body>
</html>