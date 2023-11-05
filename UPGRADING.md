# Upgrading

This document provides instructions on upgrading your implementation of IconCaptcha to the latest version. Please follow the appropriate sections based on your current version. 

Before performing an upgrade, it's important to create a backup of your existing implementation. This will ensure that you can revert to the previous version if needed.

## From v3 to v4

### Step 1: Verify Requirements
The requirements for running IconCaptcha have undergone slight changes between versions 3 and 4. Please ensure that your server meets the updated [requirements](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Getting-Started#requirements) before proceeding.

### Step 2: Install IconCaptcha
Install IconCaptcha either using Composer, as described in the [Composer installation guide](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Getting-Started#composer), or manually, following the steps provided in the [manual installation guide](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Getting-Started#manual-installation).

### Step 3: Assets
Update your current client-side assets (`/js/iconcaptcha.min.js`, `/css/iconcaptcha.min.css`, and `/icons`) with the updated ones. You can achieve this in multiple ways:
  - If you are using Composer, copy the assets from the vendor directory by publishing them to your project, as explained [here](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Getting-Started#composer).
  - Alternatively, you can download a compatible release of the client-side repository and copy the assets from there, as detailed in the [manual installation guide](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Getting-Started#manual-installation), steps 5-8.

For the stylesheet and script, you also have the option to use a CDN, as described [here](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Implementation#cdn).
- Note that the current icon set must still be updated using one of the two methods mentioned above.

### Step 4: Configuration File
IconCaptcha 3 had a [limited configuration](https://github.com/fabianwennink/IconCaptcha-PHP/blob/58707495edc3a808454c0aa287d0ff6cd5eeb2e4/examples/regular-form.php#L19) which had to be defined in every file where IconCaptcha was used.
In IconCaptcha 4, these options have been significantly expanded and relocated to a separate configuration file.

- Create a new PHP file, e.g. named `captcha-config.php`, in your application and copy the content of the [template configuration](https://github.com/fabianwennink/IconCaptcha-PHP/blob/a30d567cde722dbba3773b3d567f24df69351b4f/examples/captcha-config.php) into it.
- When using Composer, set the `iconPath` to `null` unless you've published the server assets to your application as described in the [Composer installation guide](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Getting-Started#publishing-server-assets).
- Customize the configuration options according to your preferences. Some v3 options remain available in v4, but have been restructured or renamed:
  - Options under the `image` section in version 3 have been moved to the `challenge` configuration section.
  - Options under the `attempts` section in version 3 are now under the `validation.attempts` configuration section.
  - In version 3, the `token` option was a boolean. In version 4, you should set it to `\IconCaptcha\Token\IconCaptchaToken::class`. 
    - If you had this option set to `false` in your version 3 implementation, update it to `null`.

### Step 5: Processing Widget Requests
- Replace your current v3 `captcha-request.php` file with the content of the `try/catch` block of the updated file [found here](https://github.com/fabianwennink/IconCaptcha-PHP/blob/6aee68ce2e06b5a83ec4446a8a28f8b53c5207d2/examples/captcha-request.php).
  - If you've implemented the contents of the v3 `captcha-request.php` file in a different location or within a controller, make sure to update the code there.
- Ensure that the `captcha-config.php` file created in the previous step is correctly included.
- If you use an error logging service, add it to the `catch` block if needed.

### Step 6: Challenge Validation 
In the part of your code where you handle form submissions and validate the captcha challenge, make the following updates:

- Make sure that the IconCaptcha classes are correctly included, whether through the autoloader or manual inclusion.
- Include the `captcha-config.php` file that you previously created.
- Initialize a new instance of `IconCaptcha` and pass the loaded configuration as a parameter, like this: `$captcha = new IconCaptcha($config);`
-  Replace the current usage of `IconCaptcha::validateSubmission` with the following changes:
    ```php
    // V3
    // Confirm the captcha was validated.
    if(IconCaptcha::validateSubmission($_POST)) {
        // Validated.
    }
    ```
    
    Must be changed to:
    
    ```php
    // V4
    $config = require 'captcha-config.php';
    $captcha = new IconCaptcha($config);
    $validation = $captcha->validate($_POST);
    
    // Confirm the captcha was validated.
    if($validation->success()) {
       // Validated.
    }
    ```
    A full code example can be found [here](https://github.com/fabianwennink/IconCaptcha-PHP/blob/a30d567cde722dbba3773b3d567f24df69351b4f/examples/forms/regular-form.php#L12).

### Step 7: Update Widget Initialization Script
Besides there being a lot of changes in the back-end, the JavaScript code initializing the widgets has also changed. Update the following:

- Ensure the updated stylesheet and script are loaded, whether loading them from your server or using a CDN.
- Replace all instances of the current `iconcaptcha-holder` selector with `iconcaptcha-widget` in both your HTML and JavaScript code.
- If you're using the Token in your `<form>`, update the code to `<?php echo \IconCaptcha\Token\IconCaptchaToken::render(); ?>`. 
  - Make sure that the IconCaptcha classes are correctly included in your page to use the token feature, whether through the autoloader or manual inclusion.
- If you've used the `legacy-light` or `legacy-dark` themes, replace them with `light` and `dark` respectively.
- Update the JavaScript code which initialized the widget. Some option names have been changed, update them accordingly:
    - `general.validationPath` has been renamed to `general.endpoint`.
    - `general.credits` has been renamed to `general.showCredits` and is now a boolean value.
    - `security.clickDelay` has been renamed to `security.interactionDelay`.
    - `security.hoverDetection` has been renamed to `security.hoverProtection`.
    - `security.enableInitialMessage` has been renamed to `security.displayInitialMessage`.
    - `security.initializeDelay` has been renamed to `security.initializationDelay`.
    - `security.selectionResetDelay` has been renamed to `security.incorrectSelectionResetDelay`.
    - `security.loadingAnimationDelay` has been renamed to `security.loadingAnimationDuration`.
    - `security.invalidateTime` has been removed.
    - `messages` has been renamed to `locale`.

After these changes, your IconCaptcha implementation should be updated to version 4. If you encounter any issues during this transition, refer to the [installation guide](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Getting-Started#installation) and the [implementation guide](https://github.com/fabianwennink/IconCaptcha-PHP/wiki/Implementation) for guidance, following the steps as if it were a fresh installation.
