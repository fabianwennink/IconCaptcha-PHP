<?php
    /**
     * IconCaptcha Plugin: v3.0.0
     * Copyright © 2021, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */

    // Start a PHP session.
    session_start();

    // Include the IconCaptcha classes.
	require('../src/captcha-session.class.php');
    require('../src/captcha.class.php');

    // Take a look at the README file to see every available option.
    IconCaptcha::options([
        'iconPath' => '../assets/icons/', // required
        'messages' => [
            'wrong_icon' => "You've selected the wrong image.",
            'no_selection' => 'No image has been selected.',
            'empty_form' => "You've not submitted any form.",
            'invalid_id' => 'The captcha ID was invalid.'
        ]
    ]);
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title>IconCaptcha v3.0.0 - By Fabian Wennink</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=8" />
        <meta name="author" content="Fabian Wennink © <?= date('Y') ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="../assets/favicon.ico" rel="shortcut icon" type="image/x-icon" />
        <link href="../assets/demo.css" rel="stylesheet" type="text/css">

        <!-- Include IconCaptcha stylesheet -->
        <link href="../assets/css/icon-captcha.min.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="container">

            <div class="logo-text">
                <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/" target="_blank">
                    Ic<span>o</span>nCaptcha
                </a>
            </div>

            <div class="shields">
                <div class="shields-row">
                    <img src="https://img.shields.io/badge/Version-3.0.0-orange.svg?style=flat-square" />
                    <img src="https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square" />
                    <img src="https://img.shields.io/github/issues/fabianwennink/IconCaptcha-Plugin-jQuery-PHP?style=flat-square" />
                    <img src="https://img.shields.io/github/stars/fabianwennink/IconCaptcha-Plugin-jQuery-PHP?color=%23ffff&logo=github&style=flat-square" />
                </div>
                <div class="shields-row">
                    <img src="https://img.shields.io/sonar/alert_status/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" />
                    <img src="https://img.shields.io/sonar/security_rating/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud&color=%234c1" />
                    <img src="https://img.shields.io/sonar/bugs/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" />
                    <img src="https://img.shields.io/sonar/vulnerabilities/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" />
                </div>
            </div>

            <div class="section">

                <!-- Captcha message placeholder -->
                <p class="message"></p>

                <!-- The IconCaptcha holder should ALWAYS be placed WITHIN the <form> element -->
                <form action="form/ajax-submit.php" method="post">

                    <!-- Element that we use to create the IconCaptcha with -->
                    <div class="iconcaptcha-holder" data-theme="light"></div>

                    <!-- Element that we use to create the IconCaptcha with -->
                    <div class="iconcaptcha-holder" data-theme="dark"></div>

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

        <!--
            Script to submit the form(s) with Ajax.

            NOTE: If you want to use FormData instead of .serialize(), make sure to
            include the inputs 'captcha-idhf' and 'captcha-hf' into your FormData object. Take a
            look at the commented code down below.
        -->
        <script type="text/javascript">
            $(document).ready(function() {
                $('form').submit(function(e) {
                    e.preventDefault();

                    var form = $(this);

                    $.ajax({
                        type: 'POST',
                        url: form.attr('action'),
                        data: form.serialize()
                    }).done(function (data) {
                        $('.message').html(data);
                    }).fail(function (data) {
                        console.log('Error: Failed to submit form.')
                    });

                    // FormData example:

                    // var captchaID = form.find('input[name="captcha-idhf"]').val();
                    // var captchaIcon = form.find('input[name="captcha-hf"]').val();

                    // var formData = new FormData();
                    // formData.append( 'captcha-idhf', captchaID );
                    // formData.append( 'captcha-hf', captchaIcon );

                    // $.ajax({
                    //     type: 'POST',
                    //     url: form.attr('action'),
                    //     data: formData,
                    //     processData: false,
                    //     contentType: false
                    // }).done(function (data) {
                    //     $('.message').html(data);
                    // }).fail(function (data) {
                    //     console.log('Error: Failed to submit form.')
                    // });
                });
            });
        </script>

        <!-- buy me a coffee -->
        <script data-name="BMC-Widget" src="https://cdnjs.buymeacoffee.com/1.0.0/widget.prod.min.js" data-id="fabianwennink"
                data-description="Support me on Buy me a coffee!" data-message="If you like IconCaptcha, consider buying me a coffee!"
                data-color="#ffffff" data-position="right" data-x_margin="25" data-y_margin="25"></script>
        <!-- /buy me a coffee -->

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
                $('.iconcaptcha-holder').iconCaptcha({
                    fontFamily: '', // Change the font family of the captcha. Leaving it blank will add the default font to the end of the <body> tag.
                    clickDelay: 500, // The delay during which the user can't select an image.
                    invalidResetDelay: 3000, // After how many milliseconds the captcha should reset after a wrong icon selection.
                    loadingAnimationDelay: 1500, // How long the fake loading animation should play.
                    hoverDetection: true, // Enable or disable the cursor hover detection.
                    showCredits: 'show', // Show, hide or disable the credits element. Valid values: 'show', 'hide', 'disabled' (please leave it enabled).
                    enableLoadingAnimation: true, // Enable of disable the fake loading animation. Doesn't actually do anything other than look nice.
                    validationPath: '../src/captcha-request.php', // The path to the Captcha validation file.
                    messages: { // You can put whatever message you want in the captcha.
                        header: "Select the image displayed the least amount of times",
                        correct: {
                            top: "Great!",
                            bottom: "You do not appear to be a robot."
                        },
                        incorrect: {
                            top: "Uh oh.",
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