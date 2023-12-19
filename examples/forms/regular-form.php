<?php
    // Include the IconCaptcha classes.
    require_once __DIR__ . '/../../vendor/autoload.php';

    // Start a session.
    // * Required when using any 'session' driver in the configuration.
    // * Required when using the IconCaptcha Token, referring to the use of 'IconCaptchaToken' in the form below.
    // For more information, please refer to the documentation.
    session_start();

    // If the form has been submitted, validate the captcha.
    if(!empty($_POST)) {

        // Load the IconCaptcha options.
        $options = require __DIR__ . '/../captcha-config.php';

        // Create an instance of IconCaptcha.
        $captcha = new \IconCaptcha\IconCaptcha($options);

        // Validate the captcha.
        $validation = $captcha->validate($_POST);

        // Confirm the captcha was validated.
        if($validation->success()) {
            $captchaMessage = 'The form has been submitted!';
        } else {
            $captchaMessage = 'Validation failed with error code: ' . $validation->getErrorCode();
        }
    }
?>
<!DOCTYPE HTML>
<html lang="en">
    <head>
        <title>IconCaptcha v4.0.3 - By Fabian Wennink</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=11" />
        <meta name="author" content="Fabian Wennink © <?= date('Y') ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link href="../assets/favicon.ico" rel="shortcut icon" type="image/x-icon" />

        <!-- JUST FOR THE DEMO PAGE -->
        <link href="../assets/demo.css" rel="stylesheet" type="text/css">
        <script src="../assets/demo.js" type="text/javascript"></script>
        <link href="https://fonts.googleapis.com/css?family=Poppins:400,600,700" rel="stylesheet">

        <!-- Include IconCaptcha stylesheet - REQUIRED -->
        <link href="../../assets/client/css/iconcaptcha.min.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div class="container">

            <div class="logo-text">
                <a href="https://github.com/fabianwennink/IconCaptcha-PHP/" target="_blank" rel="noopener">
                    Ic<span>o</span>nCaptcha
                </a>
            </div>

            <div class="shields">
                <div class="shields-row">
                    <a href="https://github.com/fabianwennink/IconCaptcha-PHP/releases" target="_blank" rel="noopener">
                        <img src="https://img.shields.io/badge/version-4.0.3-orange.svg?style=flat-square" alt="Version 4.0.3" />
                    </a>
                    <a href="https://github.com/fabianwennink/IconCaptcha-PHP/blob/master/LICENSE" target="_blank" rel="noopener">
                        <img src="https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square" alt="MIT license" />
                    </a>
                    <a href="https://github.com/fabianwennink/IconCaptcha-PHP/issues" target="_blank" rel="noopener">
                        <img src="https://img.shields.io/github/issues/fabianwennink/IconCaptcha-PHP?style=flat-square" alt="GitHub issues" />
                    </a>
                    <a href="https://github.com/fabianwennink/IconCaptcha-PHP" target="_blank" rel="noopener">
                        <img src="https://img.shields.io/github/stars/fabianwennink/IconCaptcha-PHP?color=%23ffff&logo=github&style=flat-square" alt="GitHub stars" />
                    </a>
                </div>
                <div class="shields-row">
                    <a href="https://sonarcloud.io/dashboard?id=fabianwennink_IconCaptcha-PHP" target="_blank" rel="nofollow noreferrer noopener">
                        <img src="https://img.shields.io/sonar/alert_status/fabianwennink_IconCaptcha-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" alt="SonarCloud Status Badge" />
                        <img src="https://img.shields.io/sonar/security_rating/fabianwennink_IconCaptcha-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud&color=%234c1" alt="SonarCloud Security Rating Badge" />
                        <img src="https://img.shields.io/sonar/bugs/fabianwennink_IconCaptcha-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" alt="SonarCloud Bugs Badge" />
                        <img src="https://img.shields.io/sonar/vulnerabilities/fabianwennink_IconCaptcha-PHP?server=https%3A%2F%2Fsonarcloud.io&style=flat-square&logo=sonarcloud" alt="SonarCloud Vulnerabilities Badge" />
                    </a>
                </div>
            </div>

            <div class="section">

                <?php
                    if(isset($captchaMessage)) {
                        echo '<b>Captcha Message: </b>' . $captchaMessage;
                    }
                ?>

                <!-- The IconCaptcha holder should ALWAYS be placed WITHIN the <form> element -->
                <form action="" method="post">

                    <!-- Additional security token to prevent CSRF. -->
                    <!-- Optional, but highly recommended - disable via IconCaptcha options. -->
                    <!-- Note: using the default IconCaptcha Token class? Make sure to start a PHP session. -->
                    <?= \IconCaptcha\Token\IconCaptchaToken::render() ?>

                    <!-- The IconCaptcha widget will be rendered in this element - REQUIRED -->
                    <div class="iconcaptcha-widget" data-theme="light"></div>

                    <!-- Submit button to test your IconCaptcha input -->
                    <input type="submit" value="Submit demo captcha" class="btn btn-invert">
                </form>

                <!-- Theme selector - JUST FOR THE DEMO PAGE -->
                <div class="themes">
                    <div class="theme theme--light"><span data-theme="light"></span><span>Light Theme</span></div>
                    <div class="theme theme--dark"><span data-theme="dark"></span><span>Dark Theme</span></div>
                </div>
                <small class="smaller">- The theme selector only works when no challenge has been rendered yet -</small>
            </div>

            <div class="copyright">
                <p>Copyright &copy; <?= date('Y'); ?> Fabian Wennink - All rights reserved</p>
                <p>
                    <small>
                        IconCaptcha is licensed under <a href="https://www.fabianwennink.nl/projects/IconCaptcha/license" class="link-underline" target="_blank" rel="noopener">MIT</a>.
                        Icons made by <a href="https://blendicons.com" class="link-underline" target="_blank" rel="nofollow noopener">BlendIcons</a>.
                    </small>
                </p>
            </div>
        </div>

        <a href="../..">
            <div class="btn btn-bottom">
                <span>GO BACK</span>
            </div>
        </a>

        <a href="https://github.com/fabianwennink/IconCaptcha-PHP/" target="_blank" rel="noopener">
            <div class="corner-ribbon top-left">STAR ME ON GITHUB</div>
        </a>

        <!-- Buy Me A Coffee widget - JUST FOR THE DEMO PAGE -->
        <script data-name="BMC-Widget" src="https://cdnjs.buymeacoffee.com/1.0.0/widget.prod.min.js" data-id="fabianwennink"
                data-description="Support me on Buy me a coffee!" data-message="If you like IconCaptcha, please consider supporting me with a coffee!"
                data-color="#ffffff" data-position="right" data-x_margin="25" data-y_margin="25"></script>

        <!-- Include IconCaptcha script - REQUIRED -->
        <script src="../../assets/client/js/iconcaptcha.min.js" type="text/javascript"></script>

        <!-- Initialize the IconCaptcha - REQUIRED -->
        <script type="text/javascript">

            // Note: jQuery can be used as well. Check the README.md for more information.

            document.addEventListener('DOMContentLoaded', function () {

                // Check the README.md for information about the options.
                IconCaptcha.init('.iconcaptcha-widget', {
                    general: {
                        endpoint: '../captcha-request.php',
                        fontFamily: 'inherit',
                    },
                    security: {
                        interactionDelay: 1000,
                        hoverProtection: true,
                        displayInitialMessage: true,
                        initializationDelay: 500,
                        incorrectSelectionResetDelay: 3000,
                        loadingAnimationDuration: 1000,
                    },
                    locale: {
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
                            title: 'Please wait.',
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
    </body>
</html>
