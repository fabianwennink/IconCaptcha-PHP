<?php
    /**
     * Icon Captcha Plugin: v2.5.0
     * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */

    class IconCaptcha {

        const ICON_CAPTCHA = 'icon_captcha';
        const CAPTCHA_NOISE = 'icon_noise';
        const CAPTCHA_ICON_PATH = 'icon_path';
        const CAPTCHA_FIELD_HASH = 'captcha-hf';
        const CAPTCHA_FIELD_ID = 'captcha-idhf';

        /**
         * @var string                      A JSON encoded error message, which will be shown to the user.
         */
        private static $error;

        /**
         * @var int                         The current captcha identifier.
         */
        private static $captcha_id = 0;

        /**
         * @var array                       The (possible) custom error messages.
         */
        private static $error_messages = array(
            'You\'ve selected the wrong image.',
            'No image has been selected.',
            'You\'ve not submitted any form.',
            'The captcha ID was invalid.'
        );

        /**
         * @var CaptchaSession              The session containing captcha information.
         */
        private static $session;

        /**
         * Sets the icon folder path variable.
         *
         * @since 2.0.0                     Function was introduced.
         *
         * @param string $file_path         The path to the icons folder.
         */
        public static function setIconsFolderPath($file_path) {
            $_SESSION[self::ICON_CAPTCHA][self::CAPTCHA_ICON_PATH] = (is_string($file_path)) ? $file_path : '';
        }

        /**
         * Enables or disables the random pixel noise generator.
         * Adding random pixels to the icons will make it harder for bots to detect the odd image
         * by simply downloading and comparing them. Pixels will be added along the sides of the
         * icons.
         *
         * Note: Enabling this might cause a slight increase in CPU usage.
         *
         * @since 2.3.0                     Function was introduced.
         *
         * @param boolean $noise            TRUE if noise should be added, FALSE if not.
         */
        public static function setIconNoiseEnabled($noise) {
            $_SESSION[self::ICON_CAPTCHA][self::CAPTCHA_NOISE] = (is_bool($noise)) ? $noise : false;
        }

        /**
         * Sets the custom error messages array. When set, these messages will
         * be returned by getErrorMessage() instead of the default messages.
         *
         * Message 1 = You've selected the wrong image.
         * Message 2 = No image has been selected.
         * Message 3 = You've not submitted any form.
         * Message 4 = The captcha ID was invalid.
         *
         * Array format: array('', '', '', '')
         *
         * @since 2.1.1                     Function was introduced.
         *
         * @param string $wrongIcon         Message 1
         * @param string $noImage           Message 2
         * @param string $noForm            Message 3
         * @param string $invalidId         Message 4
         */
        public static function setErrorMessages($wrongIcon = '', $noImage = '', $noForm = '', $invalidId = '') {
            if(!empty($wrongIcon) && is_string($wrongIcon)) {
                self::$error_messages[0] = $wrongIcon;
            }
            if(!empty($noImage) && is_string($noImage)) {
                self::$error_messages[1] = $noImage;
            }
            if(!empty($noForm) && is_string($noForm)) {
                self::$error_messages[2] = $noForm;
            }
            if(!empty($invalidId) && is_string($invalidId)) {
                self::$error_messages[3] = $invalidId;
            }
        }

        /**
         * Returns the validation error message.
         *
         * @since 2.0.0                     Function was introduced.
         *
         * @return string			        The JSON encoded error message containing the error ID and message.
         */
        public static function getErrorMessage() {
            return self::$error;
        }

        /**
         * Return a correct icon hash + multiple incorrect hashes.
         *
         * @since 2.0.1                     Function was introduced.
         *
         * @param string $theme             The theme of the captcha.
         * @param int $captcha_id           The captcha identifier.
         *
         * @return string			        The JSON array containing the correct icon, incorrect icon and hashes.
         */
        public static function getCaptchaData($theme, $captcha_id) {
            $a = mt_rand(1, 91); // Get a random number (correct image)
            $b = 0; // Get another random number (incorrect image)

            // Set the captcha id property
            self::$captcha_id = self::tryCreateSession($captcha_id, $theme);

            // Pick a random number for the incorrect icon.
            // Loop until a number is found which doesn't match the correct icon ID.
            while($b === 0) {
                $c = mt_rand(1, 91);

                if($c !== $a) {
                    $b = $c;
                }
            }

            $d = -1; // At which position the correct hash will be stored in the array.
            $e = array(); // Array containing the hashes

            // Pick a random number for the correct icon.
            // Loop until a number is found which doesn't match the previously clicked icon ID.
            while($d === -1) {
                $f = mt_rand(1, 5);
                $g = (self::$session->last_clicked > -1) ? self::$session->last_clicked : 0;

                if($f !== $g) {
                    $d = $f;
                }
            }

            // Hash the icon and push it into the array with hashes.
            for($i = 1; $i <= 5; $i++) {
                $e[] = self::getImageHash('icon-' . (($i === $d) ? $a : $b) . '-' . $i);
            }

            // Unset the previous session data
            self::$session->clear();

            // Set (or override) the hashes and reset the icon request count.
            self::$session->hashes = array($a, $b, $e); // correct id, incorrect id, hashes
            self::$session->correct_hash = $e[$d - 1];
            self::$session->icon_requests = 0;
            self::$session->save();

            // Return the JSON encoded array
            return json_encode($e);
        }

        /**
         * Validates the user form submission. If the captcha is incorrect, it
         * will set the error variable and return false, else true.
         *
         * @since 2.0.0                     Function was introduced.
         *
         * @param array $post			    The HTTP POST request.
         *
         * @return boolean			        TRUE if the captcha was correct, FALSE if not.
         */
        public static function validateSubmission($post) {
            if(!empty($post)) {

                // Check if the captcha ID is set.
                if(!isset($post[self::CAPTCHA_FIELD_ID]) || !is_numeric($post[self::CAPTCHA_FIELD_ID])
                    || !CaptchaSession::exists($post[self::CAPTCHA_FIELD_ID])) {
                    self::$error = json_encode(array('id' => 4, 'error' => self::$error_messages[3]));
                    return false;
                }

                // Set the captcha id property
                self::$captcha_id = self::tryCreateSession($post[self::CAPTCHA_FIELD_ID]);

                // Check if the hidden captcha field is set.
                if(!empty($post[self::CAPTCHA_FIELD_HASH])) {

                    // If the hashes match, the form can be submitted. Return true.
                    if(self::$session->completed === true && self::getCorrectIconHash() === $post[self::CAPTCHA_FIELD_HASH]) {
                        return true;
                    } else {
                        self::$error = json_encode(array('id' => 1, 'error' => self::$error_messages[0]));
                    }
                } else {
                    self::$error = json_encode(array('id' => 2, 'error' => self::$error_messages[1]));
                }
            } else {
                self::$error = json_encode(array('id' => 3, 'error' => self::$error_messages[2]));
            }

            return false;
        }

        /**
         * Checks and sets the captcha session. If the user selected the
         * correct image, the value will be true, else false.
         *
         * @since 2.0.0                     Function was introduced.
         *
         * @param array $post			    The HTTP Post request.
         *
         * @return boolean			        TRUE if the correct image was selected, FALSE if not.
         */
        public static function setSelectedAnswer($post) {
            if(!empty($post)) {

                // Check if the captcha ID is set.
                if(!isset($post['cID']) || !is_numeric($post['cID'])) {
                    return false;
                }

                // Set the captcha id property
                self::$captcha_id = self::tryCreateSession($post['cID']);

                // Check if the hash is set and matches the correct hash.
                if(isset($post['pC']) && (self::getCorrectIconHash() === $post['pC'])) {
                    self::$session->completed = true;

                    // Unset the data to at least save some space in the session.
                    self::$session->clear();
                    self::$session->save();

                    return true;
                } else {
                    self::$session->completed = false;
                    self::$session->save();

                    // Set the clicked icon ID
                    if(in_array($post['pC'], self::$session->hashes[2])) {
                        $i = array_search($post['pC'], self::$session->hashes[2]);
                        self::$session->last_clicked = $i + 1;
                    }
                }
            }

            return false;
        }

        /**
         * Shows the icon image based on the hash. The hash matches either the correct or incorrect id
         * and will fetch and show the right image.
         *
         * @since 2.0.1                     Function was introduced.
         *
         * @param string|null $hash         The icon hash.
         * @param int|null $captcha_id      The captcha identifier.
         */
        public static function getIconFromHash($hash = null, $captcha_id = null) {

            // Check if the hash and captcha id are set
            if(!empty($hash) && (isset($captcha_id) && $captcha_id > -1)) {

                // Set the captcha id property
                self::$captcha_id = self::tryCreateSession($captcha_id);

                // Check the amount of times an icon has been requested
                if(self::$session->icon_requests >= 5) {
                    header('HTTP/1.1 403 Forbidden');
                    exit;
                }

                // Update the request counter
                self::$session->icon_requests += 1;
                self::$session->save();

                // Check if the hash is present in the session data
                if(in_array($hash, self::$session->hashes[2])) {
                    $icons_path = $_SESSION[self::ICON_CAPTCHA][self::CAPTCHA_ICON_PATH]; // Icons folder path

                    $icon_file = $icons_path . ((substr($icons_path, -1) === '/') ? '' : '/') . self::$session->theme . '/icon-' .
                        ((self::getCorrectIconHash() === $hash) ? self::$session->hashes[0] : self::$session->hashes[1]) . '.png';

                    // Check if the icon exists
                    if (is_file($icon_file)) {

                        // Check if noise is enabled or not.
                        $add_noise = (isset($_SESSION[self::ICON_CAPTCHA][self::CAPTCHA_NOISE])
                            && $_SESSION[self::ICON_CAPTCHA][self::CAPTCHA_NOISE]);

                        // If noise is enabled, add the random pixel noise.
                        if($add_noise) {
                            $icon = imagecreatefrompng($icon_file);
                            $noise_color = imagecolorallocatealpha($icon, 0, 0, 0, 126);

                            // Add some random pixels to the icon
                            for ($i = 0; $i < 5; $i++) {
                                $randX = ($i < 3) ? mt_rand(0, 2) : mt_rand(28, 30);
                                $randY = ($i < 3) ? mt_rand(0, 15) : mt_rand(16, 30);

                                imagesetpixel($icon, $randX, $randY, $noise_color);
                            }
                        }

                        // Set the content type header to the PNG MIME-type.
                        header('Content-type: image/png');

                        // Disable caching of the icon, even though the images might
                        // be 'random' due to the added pixels.
                        header('Expires: 0');
                        header('Cache-Control: no-cache, no-store, must-revalidate');
                        header('Cache-Control: post-check=0, pre-check=0', false);
                        header('Pragma: no-cache');

                        // Show the image and exit the code
                        if($add_noise && isset($icon)) {
                            imagepng($icon);
                            imagedestroy($icon);
                        } else {
                            readfile($icon_file);
                        }

                        exit;
                    }
                }
            }
        }

        /**
         * Returns the correct icon hash. Used to validate the user's input.
         *
         * @since 2.0.1                     Function was introduced.
         *
         * @return string			        The correct icon hash.
         */
        private static function getCorrectIconHash() {
            self::tryCreateSession();

            return (isset(self::$captcha_id) && is_numeric(self::$captcha_id))
                ? self::$session->correct_hash : '';
        }

        /**
         * Returns the hash of an image name.
         *
         * @since 2.0.1                     Function was introduced.
         *
         * @param null|string $image        The image name which will be hashed.
         *
         * @return string                   The image hash.
         */
        private static function getImageHash($image = null) {
            self::tryCreateSession();

            return (!empty($image) && (isset(self::$captcha_id) && is_numeric(self::$captcha_id)))
                ? hash('tiger192,3', $image . hash('crc32b', uniqid('ic_', true))) : '';
        }

        /**
         * Tries to create a new CaptchaSession if none exist.
         * Will return the correct captcha ID.
         *
         * @since 2.5.0                     Function was introduced.
         *
         * @param int $captchaId            The ID of the captcha.
         * @param string $theme             The theme of the captcha.
         *
         * @return int                      The captcha's correct ID.
         */
        private static function tryCreateSession($captchaId = -1, $theme = 'light') {
            // If the given captcha ID if valid, overwrite the stored one.
            if($captchaId > -1) {
                self::$captcha_id = $captchaId;
            }

            // If the session is not loaded yet, load it.
            if(!isset(self::$session)) {
                self::$session = new CaptchaSession(self::$captcha_id, $theme);
            }

            return self::$captcha_id;
        }
    }