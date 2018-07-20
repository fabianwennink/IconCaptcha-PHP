<?php
    /**
     * IconCaptcha Plugin: v2.4.0
     * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */

    // Start a PHP session.
    session_start();

    // Include the IconCaptcha classes.
	require('../src/captcha-session.class.php');
    require('../src/captcha.class.php');

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
    // IconCaptcha::setErrorMessages(array('', '', '', ''));

    // If the form has been submitted, validate the captcha.
    if(!empty($_POST)) {
        if(IconCaptcha::validateSubmission($_POST)) {
            echo '<b>Captcha:</b> The form has been submitted!';
        } else {
            echo '<b>Captcha: </b>' . json_decode(IconCaptcha::getErrorMessage())->error;
        }
    }
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>IconCaptcha Plugin v2 - By Fabian Wennink</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=9" />
        <meta name="author" content="Fabian Wennink © <?= date('Y') ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="../assets/favicon.ico" rel="shortcut icon" type="image/x-icon" />
        <link href="../assets/demo.css" rel="stylesheet" type="text/css">

        <!-- Include IconCaptcha stylesheet -->
        <link href="../assets/css/icon-captcha.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="container">

            <div class="logo-text">
                <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/" target="_blank">
                    Ic<span>o</span>nCaptcha
                </a>
            </div>

            <div class="shields">
                <img src="https://img.shields.io/badge/Version-2.4.0-orange.svg?style=flat-square" /> <img src="https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square" />
                <img src="https://img.shields.io/badge/Maintained-Yes-green.svg?style=flat-square" /> <a href="https://paypal.me/nlgamevideosnl" target="_blank"><img src="https://img.shields.io/badge/Donate-PayPal-yellow.svg?style=flat-square" /></a>
            </div>

            <div class="section">

                <!-- Just a basic HTML form, captcha should ALWAYS be placed WITHIN the <form> element -->
                <h2>Form:</h2>
                <form action="form/ajax-submit.php" method="post">

                    <!-- Element that we use to create the IconCaptcha with -->
                    <div class="captcha-holder"></div>

                    <!-- Submit button to test your IconCaptcha input -->
                    <input type="submit" value="Submit demo captcha" class="btn" >
                </form>
            </div>

            <div class="copyright">
                <p>Copyright &copy; <?= date('Y'); ?> Fabian Wennink - All rights reserved</p>
                <p><small>IconCaptcha is licensed under MIT.</small></p>
            </div>
        </div>

        <a href="../">
            <div class="btn btn-bottom">
                <span>GO BACK</span>
            </div>
        </a>

        <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/">
            <div class="corner-ribbon top-left">
                STAR ME ON GITHUB
            </div>
        </a>

        <!-- Include Google Font - Just for demo page -->
        <link href="https://fonts.googleapis.com/css?family=Poppins:400,700" rel="stylesheet">

        <!-- Include jQuery Library -->
        <!--[if lt IE 9]>
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.12.3/jquery.min.js"></script>
        <![endif]-->

        <!--[if (gte IE 9) | (!IE)]><!-->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
        <!--<![endif]-->

        <!-- Include IconCaptcha script -->
        <script src="../assets/js/icon-captcha.min.js" type="text/javascript"></script>

        <!-- Initialize the IconCaptcha -->
        <script async type="text/javascript">
            $(window).ready(function() {

                $('.captcha-holder').iconCaptcha({
                    captchaTheme: ["light", "dark"], // Select the theme(s) of the Captcha(s). Available: light, dark
                    captchaFontFamily: '', // Change the font family of the captcha. Leaving it blank will add the default font to the end of the <body> tag.
                    captchaClickDelay: 500, // The delay during which the user can't select an image.
                    captchaHoverDetection: true, // Enable or disable the cursor hover detection.
                    enableLoadingAnimation: true, // Enable of disable the fake loading animation. Doesn't actually do anything other than look nice.
                    loadingAnimationDelay: 1500, // How long the fake loading animation should play.
                    showCredits: 'show', // Show, hide or disable the credits element. Valid values: 'show', 'hide', 'disabled' (please leave it enabled).
                    requestIconsDelay: 1500, // How long should the script wait before requesting the hashes and icons? (to prevent a high(er) CPU usage during a DDoS attack)
                    captchaAjaxFile: '../src/captcha-request.php', // The path to the Captcha validation file.
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
                .bind('init.iconCaptcha', function(e, id) { // You can bind to custom events, in case you want to execute some custom code.
                    console.log('Event: Captcha initialized', id);
                }).bind('selected.iconCaptcha', function(e, id) {
                    console.log('Event: Icon selected', id);
                }).bind('refreshed.iconCaptcha', function(e, id) {
                    console.log('Event: Captcha refreshed', id);
                }).bind('success.iconCaptcha', function(e, id) {
                    console.log('Event: Correct input', id);
                }).bind('error.iconCaptcha', function(e, id) {
                    console.log('Event: Wrong input', id);
                });
            });
        </script>
    </body>
</html>