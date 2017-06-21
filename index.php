<?php
    /**
     * Icon Captcha Plugin: v2.0.1
     * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */

    // Start a PHP session.
    session_start();

    // Include the Captcha class.
    require('resources/php/captcha.class.php');

    // Set the path to the captcha icons. Set it as if you were
    // currently in the PHP folder containing the captcha.class.php file.
    // ALWAYS END WITH A /
    // DEFAULT IS SET TO ../icons/
    Captcha::setIconsFolderPath("../icons/");

    // If the form has been submitted, validate the captcha.
    if(!empty($_POST)) {
        if(Captcha::validateSubmission($_POST)) {
            echo 'The form has been submitted!';
        } else {
            echo Captcha::getErrorMessage();
        }
    }
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Icon Captcha Plugin v2 - By Fabian Wennink</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=9" />
        <meta name="author" content="Fabian Wennink © <?= date('Y') ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="resources/style/css/style.css" rel="stylesheet" type="text/css">

        <!-- CSS to style the page a bit - not important, can be deleted -->
        <style>
            body {
                font-family: 'Roboto', sans-serif;
            }

            #captcha-holder {
                margin: 20px 0;
            }

            input[type="submit"] {
                max-width: 375px;
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
        <!-- Just a basic HTML form, captcha should ALWAYS be placed WITHIN the <form> element -->
        <form action="" method="post">

            <!-- Element that we use to create the Captcha with -->
            <div id="captcha-holder"></div>

            <!-- Submit button to test your Captcha input -->
            <input type="submit" value="Submit form to test captcha" >
        </form>

        <p><a href="https://github.com/fabianwennink/jQuery-Icon-Captcha-Plugin/" target==_blank>View project on GitHub</a></p>

        <!-- Include jQuery Library -->
        <!--[if lt IE 9]>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js"></script>
        <![endif]-->

        <!--[if (gte IE 9) | (!IE)]><!-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
        <!--<![endif]-->

        <!-- Include Captcha jquery script -->
        <script src="resources/js/script.min.js" type="text/javascript"></script>

        <!-- Initiate the Captcha -->
        <script async type="text/javascript">
            $(window).ready(function() {
                $('#captcha-holder').iconCaptcha({
                    captchaTheme: 'light', // Select the theme of the Captcha. Available: light, dark
                    captchaFontFamily: '', // Change the font family of the captcha. Leaving it blank will add the default font to the end of the <body> tag.
                    captchaClickDelay: 500, // The delay during which the user can't select an image.
                    captchaHoverDetection: true, // Enable or disable the cursor hover detection.
                    showBoxShadow: false, // Show or hide the box shadow effect.
                    showCredits: true, // Show or hide the credits element (please leave it enbled).
                    enableLoadingAnimation: true, // Enable of disable the fake loading animation. Doesn't do anything, just looks cool ;)
                    loadingAnimationDelay: 2500, // How long the fake loading animation should play.
                    captchaAjaxFile: 'resources/php/captcha-request.php' // The path to the Captcha validation file.
                })
                .bind('selected.iconCaptcha', function() { // You can bind to custom events, in case you want to execute some custom code.
                    console.log('Event: Icon selected');
                }).bind('refreshed.iconCaptcha', function() {
                    console.log('Event: Captcha refreshed');
                }).bind('init.iconCaptcha', function() {
                    console.log('Event: Captcha initialized');
                }).bind('success.iconCaptcha', function() {
                    console.log('Event: Correct input');
                }).bind('error.iconCaptcha', function() {
                    console.log('Event: Wrong input');
                });
            });
        </script>
    </body>
</html>