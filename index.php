<?php
    /**
     * IconCaptcha Plugin: v2.3.1
     * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */
?>
<!DOCTYPE HTML>
<html>
    <head>
        <title>IconCaptcha Plugin v2 - By Fabian Wennink</title>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=9" />
        <meta name="author" content="Fabian Wennink © <?= date('Y') ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="resources/images/favicon.ico" rel="shortcut icon" type="image/x-icon" />
		
        <!-- Include IconCaptcha stylesheet -->
        <link href="resources/style/css/style.css" rel="stylesheet" type="text/css">

        <!-- CSS to style the page a bit - not important, can be deleted -->
        <style>
            body { font-family: 'Roboto', sans-serif; }
            form { margin-bottom: 50px; }

            .logo {
                display:block;
                margin:25px 0;
            }
            .github a { color: #2d2d2d; margin-bottom: 50px; }

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
        <a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/" target="_blank">
            <img class="logo" src="resources/images/iconcaptcha-logo.png" alt="IconCaptcha - jQuery & PHP Plugin" title="IconCaptcha - jQuery & PHP Plugin" />
        </a>

        <img src="https://img.shields.io/badge/Version-2.3.1-orange.svg?style=flat-square" /> <img src="https://img.shields.io/badge/License-MIT-blue.svg?style=flat-square" />
        <img src="https://img.shields.io/badge/Maintained-Yes-green.svg?style=flat-square" /> <a href="https://paypal.me/nlgamevideosnl" target="_blank"><img src="https://img.shields.io/badge/Donate-PayPal-yellow.svg?style=flat-square" /></a>

        <h2>Examples:</h2>
        <p><a href="examples/regular-form.php">Regular HTML form</a></p>
        <p><a href="examples/ajax-form.php">Ajax form</a></p>

        <h2>Links</h2>
        <p><a href="https://github.com/fabianwennink/IconCaptcha-Plugin-jQuery-PHP/" target="_blank">GitHub repository</a></p>
        <p><a href="https://paypal.me/nlgamevideosnl" target="_blank">Buy me a coffee (PayPal)</a></p>
        <p><a href="https://fabianwennink.nl/en/" target="_blank">Fabian Wennink <small>(IconCaptcha developer)</small></a></p>
    </body>
</html>