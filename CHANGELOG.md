# Changelog

All notable changes will be documented in this file. Only changes starting at IconCaptcha 2.0.2 have been recorded.

## 4.0.3 - Dec 19, 2023
Release: [View tag 4.0.3](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/4.0.3)

### Fixed
- Removed the request timestamp check due to unreliability after multiple reports indicated false positives, e.g. when the browser or server clocks were out of sync.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/4.0.2...4.0.3).

## 4.0.2 - Dec 16, 2023
Release: [View tag 4.0.2](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/4.0.2)

### Fixed
- Fixed a bug which would throw an exception when validating a challenge while having the Token option configured to be disabled.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/4.0.1...4.0.2).

## 4.0.1 - Dec 10, 2023
Release: [View tag 4.0.1](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/4.0.1)

### Fixed
- Fixed an issue regarding the challenge image width, which is received in the payload when processing the icon selection call.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/4.0.0...4.0.1).

## 4.0.0 - Nov 5, 2023
Release: [View tag 4.0.0](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/4.0.0)

Version 4 of IconCaptcha introduces significant enhancements to the architecture and feature set, and offers greater flexibility and customization, alongside improved security.

### Added
- IconCaptcha is now available on Packagist, allowing installation via Composer.
- Added database support for MySQL, PostgreSQL, SQL Server and SQLite to store challenge and timeout data.
- Added support for ImageMagick to generate challenge images.
- Added support for implementing custom drivers to handle storage, sessions, challenge generation and timeouts.
- Added server-side hooks to execute custom code at specific points during challenge generation and validation steps.
- Added support for Cross-Origin Resource Sharing (CORS).

### Changed
- Expanded and improved the server-side configuration file with numerous options and descriptions.
- Code related to handling captcha requests has been moved to a dedicated class for easier implementation.
- Changed the way IconCaptcha instances must be initialized while validating challenges, moving away from static function calls.
- Validation of a captcha now returns error codes instead of error messages.
- Challenge images are now returned as base64 strings instead of image links.
- The default widget selector changed to `.iconcaptcha-widget`.
- Widgets now use unique UUIDv4 widget and challenge identifiers instead of incremental integers.
- Renamed several client-side widget configuration options.

### Removed
- Removed the `legacy-light` and `legacy-dark` themes.
- Removed support for Internet Explorer now that it has been officially discontinued by Microsoft.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/3.1.3...4.0.0).

---

## 3.1.3 - November 4, 2023
Release: [View tag 3.1.3](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/3.1.3)

## Changed
- Updated included icons to v3.1.3 of the IconCaptcha widget package, replacing all icons with ones created by [BlendIcons](https://blendicons.com/).

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/3.1.2...3.1.3).

---

## 3.1.2 - April 3, 2023
Release: [View tag 3.1.2](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/3.1.2)

### Added
- Added the 'available icons count' as a configurable option, removing the fixed limit of 180 icons.

### Fixed
- Resolved an issue where nested default options were not correctly merging with custom options during initialization.
- All default icons have been converted to use True Color to address a peculiar problem with the GD `imagerotate` function.

### Changed
- Replaced the use of `dirname(__FILE__)` with the `__DIR__` magic constant for specifying the 'icons' path.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/3.1.1...3.1.2).

---

## 3.1.1 - October 23, 2022
Release: [View tag 3.1.1](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/3.1.1)

### Fixed
- Fixed a bug that, under specific conditions, caused challenge generation to fail on PHP 8.1 due to deprecation warnings being echoed.

### Changed
- Relocated client-side assets to the '/examples' folder for better organization.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/3.1.0...3.1.1).

---

## 3.1.0 - October 8, 2022
Release: [View tag 3.1.0](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/3.1.0)

### Changed
- Upgraded assets to the IconCaptcha client-side package 3.1.0, adding the functionality to reset widgets as discussed in [issue #11](https://github.com/fabianwennink/IconCaptcha-PHP/issues/11).
- Improved CSRF token generation by adding additional fallbacks in case of exceptions being thrown or version incompatibility.
- Made changes to the session class regarding the session key.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/3.0.1...3.1.0).

---

## 3.0.1 - Feb 12, 2022
Release: [View tag 3.0.1](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/3.0.1)

### Changed
- Upgraded assets to the IconCaptcha client-side package 3.0.1, which resolved the issues mentioned in [issue #7](https://github.com/fabianwennink/IconCaptcha-PHP/issues/7) and [issue #9](https://github.com/fabianwennink/IconCaptcha-PHP/issues/9).

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/3.0.0...3.0.1).

---

## 3.0.0 - Sep 25, 2021
Release: [View tag 3.0.0](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/3.0.0)

In version 3 of IconCaptcha, the whole plugin got an overhaul - both client-side and server-side.

### Added
- Added more captcha image generation options to increase the difficulty of challenges.
- Added automatic challenge invalidation after a period of no user interaction.
- Added automatic timeouts when too many incorrect selections were made by the user.
- Added new light and dark themes with more modern designs.
- Added improved support for custom themes.

### Changed
- Replaced all icons with 180 new modern icons, created by [Streamline](https://streamlinehq.com).
- Rewrote the widget script to move away from the required use of jQuery. IconCaptcha can still be used with jQuery if you wish to do so.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/2.5.0...3.0.0).

---

## 2.5.0 - Nov 17, 2018
Release: [View tag 2.5.0](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/2.5.0)

### Changed
- Updated the method for setting custom localization strings.
- Converted recurring strings into constants.
- Removed the use of the global `$_POST` within the challenge validator.

### Fixed
- Fixed a bug that caused blurriness in the demo pages.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/2.4.0...2.5.0).

---

## 2.4.0 - Jul 21, 2018
Release: [View tag 2.4.0](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/2.4.0)

### Changed
- Split the project into separate back-end and front-end repositories.
- Updated the captcha script to interpret success state based on the returned HTTP code rather than a numeric value.
- Relocated front-end assets to a different folder, outside the source and example directories.
- Updated the examples to be compatible with the new front-end package.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/2.3.0...2.4.0).

---

## 2.3.1 - Released with 2.4.0

### Added
- Added the `Content-type: application/json` header in the captcha request file to ensure that the returned data is seen as a JSON string.

### Fixed
- Fixed a bug in the captcha script where wrong error messages were being displayed.

---

## 2.3.0 - May 5, 2018
Release: [View tag 2.3.0](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/2.3.0)

### Added
- Added an option to enable adding nearly invisible image noise to the icon images.
- Added the 'showCredits' option to allow control over whether the credits are displayed in widgets.

### Changed
- Replaced the PHP `rand()` function with `mt_rand()` to improve execution speed.
- Updated the icon hash algorithm types to enhance hashing speed.
- Removed the unnecessary MIME-type check, as only PNG icons are used.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/2.2.0...2.3.0).

---

## 2.2.0 - Jan 7, 2018
Release: [View tag 2.2.0](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/2.2.0)

### Changed
- Implemented the `CaptchaSession` class to handle all session related data.
- Removed direct session calls within the code, replacing them with the `CaptchaSession` class.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/2.1.3...2.2.0).

---

## 2.1.3 - Jul 19, 2017
Release: [View tag 2.1.3](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/2.1.3)

### Added
- Added an example page demonstrating how to use the captcha when the form is submitted via ajax.
- Added the data attribute `captcha-id` to the captcha holder.

### Fixed
- Fixed a bug that caused the form identifier to reset when the incorrect icon was selected.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/2.1.2...2.1.3).

---

## 2.1.2 - Jun 29, 2017
Release: [View tag 2.1.2](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/2.1.2)

### Added
- Added security checks to prevent scripts from trying to request images from the server after the initial call.
- Added a feature that restricts repeatedly clicking the same image until the correct image is selected.

### Changed
- Replaced error strings with more appropriate HTTP error codes.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/2.1.0...2.1.2).

---

## 2.1.1 - Released with 2.1.2

### Added
- Added a function that allows for setting custom error messages.

### Changed
- The loading animation has been updated to play until the captcha icons are fully loaded.
- The captcha ID will now be included in all client-side events.

---

## 2.1.0 - Jun 24, 2017
Release: [View tag 2.1.0](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/2.1.0)

### Added
- Added support for multiple captcha widgets on a single page.
- Added support for setting a different theme per captcha widget.
- Added an optional delay on the hash and icon server requests.
- Added a hash length check for the 'get-image-by-hash' request.

### Changed
- Updated the width and padding of the icons to improve the way they are displayed.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/2.0.2...2.1.0).

---

## 2.0.2 - Jun 23, 2017
Release: [View tag 2.0.2](https://github.com/fabianwennink/IconCaptcha-PHP/releases/tag/2.0.2)

### Added
- Added a new localization option.

### Changed
- Reduced the size of the captcha widget and implemented full responsive design.
- Updated the hash and salt algorithms to improve security.

For a full list of commits and changes, please refer to the [full commit changelog](https://github.com/fabianwennink/IconCaptcha-PHP/compare/2.0.1...2.0.2).
