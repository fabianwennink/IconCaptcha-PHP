<?php
    /**
     * IconCaptcha Plugin: v3.1.2
     * Copyright © 2023, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
     */

    // Start a PHP session.
    session_start();

    // Include the IconCaptcha classes.
    require('../src/captcha-session.class.php');
    require('../src/captcha.class.php');

use IconCaptcha\IconCaptcha;

// Take a look at the README file to see every available option.
    IconCaptcha::options([
        'iconPath' => __DIR__ . '/../assets/icons/', // required
        //'themes' => [
        //    'black' => [
        //        'icons' => 'light', // Which icon type should be used: light or dark.
        //        'color' => [20, 20, 20], // Array contains the icon separator border color, as RGB.
        //    ]
        //],
        'messages' => [
            'wrong_icon' => 'You\'ve selected the wrong image.',
            'no_selection' => 'No image has been selected.',
            'empty_form' => 'You\'ve not submitted any form.',
            'invalid_id' => 'The captcha ID was invalid.',
            'form_token' => 'The form token was invalid.'
        ],
        'image' => [
            'availableIcons' => 180, // Number of unique icons available. By default, IconCaptcha ships with 180 icons.
            'amount' => [
                'min' => 5, // The lowest possible is 5 icons per challenge.
                'max' => 8 // The highest possible is 8 icons per challenge.
            ],
            'rotate' => true,
            'flip' => [
                'horizontally' => true,
                'vertically' => true,
            ],
            'border' => true
        ],
        'attempts' => [
            'amount' => 3,
            'timeout' => 60 // seconds.
        ],
        'token' => true
    ]);

    // If the form has been submitted, validate the captcha.
    if(!empty($_POST)) {
        if(IconCaptcha::validateSubmission($_POST)) {
            $captchaMessage = 'The form has been submitted!';
        } else {
            $captchaMessage = IconCaptcha::getErrorMessage();
        }
    }
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title>IconCaptcha v3.1.2 - By Fabian Wennink</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=10" />
        <meta name="author" content="Fabian Wennink © <?= date('Y') ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="assets/favicon.ico" rel="shortcut icon" type="image/x-icon" />

        <!-- JUST FOR THE DEMO PAGE -->
        <link href="assets/demo.css" rel="stylesheet" type="text/css">
        <script src="assets/demo.js" type="text/javascript"></script>
        <link href="https://fonts.googleapis.com/css?family=Poppins:400,700" rel="stylesheet">

        <!-- Include IconCaptcha stylesheet - REQUIRED -->
        <link href="assets/css/icon-captcha.min.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="container">

            <div class="logo-text">
                <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/" target="_blank" rel="noopener">
                    Ic<span>o</span>nCaptcha
                </a>
            </div>

            <div class="shields">
                <div class="shields-row">
                    <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/releases" target="_blank" rel="noopener">
                        <img src="https://img.shields.io/badge/Version-3.1.2-orange.svg?style=flat-square" alt="Version 3.1.2 Badge"/>
                    </a>
                    <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/blob/master/LICENSE" target="_blank" rel="noopener">
                        <img src="https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square" alt="License-MIT Badge" />
                    </a>
                    <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/issues" target="_blank" rel="noopener">
                        <img src="https://img.shields.io/github/issues/fabianwennink/IconCaptcha-Plugin-jQuery-PHP?style=flat-square" alt="Git Issues Badge" />
                    </a>
                    <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP" target="_blank" rel="noopener">
                        <img src="https://img.shields.io/github/stars/fabianwennink/IconCaptcha-Plugin-jQuery-PHP?color=%23ffff&logo=github&style=flat-square" alt="Git Stars Badge" />
                    </a>
                </div>
                <div class="shields-row">
                    <a href="https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-Plugin-jQuery-PHP" target="_blank" rel="nofollow noreferrer noopener">
                        <img src="https://img.shields.io/sonar/alert_status/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" alt="SonarCloud Status Badge" />
                        <img src="https://img.shields.io/sonar/security_rating/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud&color=%234c1" alt="SonarCloud Security Rating Badge" />
                        <img src="https://img.shields.io/sonar/bugs/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" alt="SonarCloud Bugs Badge" />
                        <img src="https://img.shields.io/sonar/vulnerabilities/fabianwennink_IconCaptcha-Plugin-jQuery-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" alt="SonarCloud Vulnerabilities Badge" />
                    </a>
                </div>
            </div>

            <div class="section">

                <!-- Captcha message placeholder -->
                <p class="message"></p>

                <!-- The IconCaptcha holder should ALWAYS be placed WITHIN the <form> element -->
                <form action="form/ajax-submit.php" method="post">

                    <!-- Additional security token to prevent CSRF. Optional but highly recommended - disable via IconCaptcha options. -->
                    <input type="hidden" name="_iconcaptcha-token" value="<?= IconCaptcha::token() ?>"/>

                    <!-- The IconCaptcha will be rendered in this element - REQUIRED -->
                    <div class="iconcaptcha-holder" data-theme="light"></div>

                    <!-- Submit button to test your IconCaptcha input -->
                    <input type="submit" value="Submit demo captcha" class="btn" >
                </form>

                <!-- Theme selector - JUST FOR THE DEMO PAGE -->
                <div class="themes">
                    <div class="theme theme--light"><span data-theme="light"></span><span>Light</span></div>
                    <div class="theme theme--legacy-light"><span data-theme="legacy-light"></span><span>Legacy Light</span></div>
                    <div class="theme theme--dark"><span data-theme="dark"></span><span>Dark</span></div>
                    <div class="theme theme--legacy-dark"><span data-theme="legacy-dark"></span><span>Legacy Dark</span></div>
                </div>
                <small>(theme selector only works when the challenge has not been requested yet)</small>
            </div>

            <div class="copyright">
                <p>Copyright &copy; <?= date('Y'); ?> Fabian Wennink - All rights reserved</p>
                <p><small>IconCaptcha is licensed under <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/blob/master/LICENSE" target="_blank" rel="noopener">MIT</a>.
				Icons made by by <a href="https://streamlinehq.com" target="_blank" rel="nofollow noopener">Streamline</a>.</small></p>
            </div>
        </div>

        <a href="../">
            <div class="btn btn-bottom">
                <span>GO BACK</span>
            </div>
        </a>

        <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/" target="_blank" rel="noopener">
            <div class="corner-ribbon top-left">STAR ME ON GITHUB</div>
        </a>

        <!-- Buy Me A Coffee widget - JUST FOR THE DEMO PAGE -->
        <script data-name="BMC-Widget" src="https://cdnjs.buymeacoffee.com/1.0.0/widget.prod.min.js" data-id="fabianwennink"
                data-description="Support me on Buy me a coffee!" data-message="If you like IconCaptcha, consider buying me a coffee!"
                data-color="#ffffff" data-position="right" data-x_margin="25" data-y_margin="25"></script>

        <!-- Include IconCaptcha script - REQUIRED -->
        <script src="assets/js/icon-captcha.min.js" type="text/javascript"></script>

        <!-- Include jQuery Library -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

        <!-- Include IconCaptcha script - REQUIRED -->
        <script src="assets/js/icon-captcha.min.js" type="text/javascript"></script>

        <!-- Initialize the IconCaptcha - REQUIRED -->
        <script type="text/javascript">
            $(document).ready(function() {
                $('.iconcaptcha-holder').iconCaptcha({
                    general: {
                        validationPath: '../src/captcha-request.php',
                        fontFamily: 'Poppins',
                        credits: 'show',
                    },
                    security: {
                        clickDelay: 500,
                        hoverDetection: true,
                        enableInitialMessage: true,
                        initializeDelay: 500,
                        selectionResetDelay: 3000,
                        loadingAnimationDelay: 1000,
                        invalidateTime: 1000 * 60 * 2,
                    },
                    messages: {
                        initialization: {
                            verify: 'Verify that you are human.',
                            loading: 'Loading challenge...',
                        },
                        header: 'Select the image displayed the <u>least</u> amount of times',
                        correct: 'Verification complete.',
                        incorrect: {
                            title: 'Uh oh.',
                            subtitle: "You've selected the wrong image."
                        },
                        timeout: {
                            title: 'Please wait 60 sec.',
                            subtitle: 'You made too many incorrect selections.'
                        }
                    }
                })
                // .bind('init', function(e) { // You can bind to custom events, in case you want to execute custom code.
                //     console.log('Event: Captcha initialized', e.detail.captchaId);
                // }).bind('selected', function(e) {
                //     console.log('Event: Icon selected', e.detail.captchaId);
                // }).bind('refreshed', function(e) {
                //     console.log('Event: Captcha refreshed', e.detail.captchaId);
                // }).bind('invalidated', function(e) {
                //     console.log('Event: Invalidated', e.detail.captchaId);
                // }).bind('reset', function(e) {
                //     console.log('Event: Reset', e.detail.captchaId);
                // }).bind('success', function(e) {
                //     console.log('Event: Correct input', e.detail.captchaId);
                // }).bind('error', function(e) {
                //     console.log('Event: Wrong input', e.detail.captchaId);
                // });
            });
        </script>

        <!--
            Script to submit the form(s) with Ajax.

            NOTE: If you want to use FormData instead of .serialize(), make sure to
            include the inputs 'ic-hf-se', 'ic-hf-id' and 'ic-hf-hp' into your FormData object.
            Take a look at the commented code down below.
        -->
        <script type="text/javascript">
            $(document).ready(function() {
                $('form').submit(function(e) {
                    e.preventDefault();

                    // Get the form element.
                    const form = $(this);

                    // Perform the AJAX call.
                    $.ajax({
                        type: 'POST',
                        url: form.attr('action'),
                        data: form.serialize()
                    }).done(function (data) {
                        $('.message').html(data);
                    }).fail(function () {
                        console.log('Error: Failed to submit form.')
                    });

                    // // FormData example:
                    //
                    // // Get the form element.
                    // const form = $(this);
                    //
                    // // Build the FormData object.
                    // const formData = new FormData();
                    // formData.append('ic-hf-se', form.find('input[name="ic-hf-se"]').val());
                    // formData.append('ic-hf-id', form.find('input[name="ic-hf-id"]').val());
                    // formData.append('ic-hf-hp', form.find('input[name="ic-hf-hp"]').val());
                    //
                    // // Perform the AJAX call.
                    // $.ajax({
                    //     type: 'POST',
                    //     url: form.attr('action'),
                    //     data: formData,
                    //     processData: false,
                    //     contentType: false
                    // }).done(function (data) {
                    //     $('.message').html(data);
                    // }).fail(function () {
                    //     console.log('Error: Failed to submit form.')
                    // });
                });
            });
        </script>
    </body>
</html>
