<?php
    /**
     * Icon Captcha Plugin: v2.0.3
     * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */

    class IconCaptcha {

        private static $error;
        private static $session_name = "icon_captcha";

        protected static $captcha_id = 0;

        /**
         * Sets the icon folder path variable.
         *
         * @since 2.0.0                     Function was introduced.
         *
         * @param string $file_path         The path to the icons folder.
         */
        public static function setIconsFolderPath($file_path) {
            $_SESSION[self::$session_name]['icon_path'] = $file_path;
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
         * Return a correct icon class + multiple incorrect classes
         *
         * @since 2.0.1                     Function was introduced.
         *
         * @param string $theme             The theme of the captcha.
         *
         * @return string			        The JSON encoded array containing the correct icon id, incorrect icon id and hashes.
         */
        public static function getCaptchaData($theme, $captcha_id) {
            $a = rand(1, 89); // Get a random number (correct image)
            $b = 0; // Get another random number (incorrect image)

            // Set the captcha id property
            self::$captcha_id = $captcha_id;

            // Save the theme to the session
            $_SESSION[self::$session_name][$captcha_id]['theme'] = $theme;

            // Pick a random number for the incorrect icon.
            // Loop until a number is found which doesn't match the correct icon ID.
            while($b == 0) {
                $c = rand(1, 89);
                if($c !== $a) $b = $c;
            }

            // Unset the previous session data
            unset($_SESSION[self::$session_name][$captcha_id]['selected']);

            $d = rand(1, 5); // At which position the correct hash will be stored in the array.
            $e = array(); // Array containing the hashes

            for($i = 1; $i < 6; $i++) {
                if($i == $d) {
                    array_push($e, self::getImageHash('icon-' . $a . '-' . $i));
                } else {
                    array_push($e, self::getImageHash('icon-' . $b . '-' . $i));
                }
            }

            // Set the new session data
            $_SESSION[self::$session_name][$captcha_id]['selected']['answer'] = $e[$d - 1];
            $_SESSION[self::$session_name][$captcha_id]['selected']['data'] = array($a, $b, $e); // correct id, incorrect id, hashes

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
        public static function validateSubmission($post = null) {
            if(!empty($post)) {

                // Check if the captcha ID is set.
                if(!isset($post['captcha-idhf']) || !is_numeric($post['captcha-idhf']) || !isset($_SESSION[self::$session_name][$post['captcha-idhf']])) {
                    self::$error = json_encode(array('id' => 4, 'error' => 'The captcha ID was invalid.'));
                    return false;
                }

                // Set the captcha id property
                self::$captcha_id = $post['captcha-idhf'];

                // Check if the 'selected' session and hidden captcha field are set
                if(isset($_SESSION[self::$session_name][$post['captcha-idhf']]['selected']['correct']) && isset($post['captcha-hf'])) {

                    // If the hashes match, unset the session data and allow the form to submit
                    if(($_SESSION[self::$session_name][$post['captcha-idhf']]['selected']['correct'] === true) && (self::getCorrectIconHash() === $post['captcha-hf'])) {
                        unset($_SESSION[self::$session_name][$post['captcha-idhf']]['selected']['correct']);
                        unset($_SESSION[self::$session_name][$post['captcha-idhf']]['selected']['answer']);

                        return true;
                    } else {
                        self::$error = json_encode(array('id' => 1, 'error' => 'You\'ve selected the wrong image.'));
                    }
                } else {
                    self::$error = json_encode(array('id' => 2, 'error' => 'No image has been selected.'));
                }
            } else {
                self::$error = json_encode(array('id' => 3, 'error' => 'You\'ve not submitted any form.'));
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
        public static function setSelectedAnswer($post = null) {
            if(!empty($post)) {

                // Check if the captcha ID is set.
                if(!isset($_POST['cID']) || !is_numeric($_POST['cID'])) {
                    return false;
                }

                // Set the captcha id property
                self::$captcha_id = $_POST['cID'];

                // Check if the hash is set and matches the correct hash.
                if(isset($post['pC']) && (self::getCorrectIconHash() === $post['pC'])) {
                    $_SESSION[self::$session_name][$_POST['cID']]['selected']['correct'] = true;
                    return true;
                } else {
                    $_SESSION[self::$session_name][$_POST['cID']]['selected']['correct'] = false;
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
         * @param null $hash                The icon hash.
         */
        public static function getIconFromHash($hash = null, $captcha_id = null) {

            // Check if the hash and captcha id are set
            if(isset($hash) && isset($captcha_id)) {
                $a = $_SESSION[self::$session_name][$captcha_id]['selected']['data'];

                //die(var_dump($a));

                // Set the captcha id property
                self::$captcha_id = $captcha_id;

                // Check if the hash is present in the session data
                if(in_array($hash, $a[2])) {
                    $icons_path = $_SESSION[self::$session_name]['icon_path']; // Icons folder path
                    $file = $icons_path . ((substr($icons_path, -1) === '/') ? '' : '/') . $_SESSION[self::$session_name][$captcha_id]['theme'] . '/icon-' . ((self::getCorrectIconHash() === $hash) ? $a[0] : $a[1]) . '.png';

                    // Check if the icon exists
                    if (file_exists($file)) {
                        $mime = null;

                        // Grab the MIME type of the image (all default images are image/png)
                        // Use either finfo_open or mime_content_type, depending on the PHP version
                        if (function_exists("finfo_open")) {
                            $file_info = finfo_open(FILEINFO_MIME_TYPE);
                            $mime = finfo_file($file_info, $file);
                        } else if (function_exists("mime_content_type")) {
                            $mime = mime_content_type($file);
                        }

                        // Show the image and exit the code
                        header('Content-type: ' . $mime);
                        readfile($file);
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
            return (isset(self::$captcha_id) && is_numeric(self::$captcha_id)) ? $_SESSION[self::$session_name][self::$captcha_id]['selected']['answer'] : "";
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
            return (!empty($image) && (isset(self::$captcha_id) && is_numeric(self::$captcha_id))) ? hash('tiger192,3', $image . self::getSalt()) : "";
        }

        /**
         * Returns a randomly generated temporary salt used to hash the image names with.
         *
         * @since 2.0.1                     Function was introduced.
         *
         * @return string                   The random generated salt.
         */
        private static function getSalt() {
            return (isset($_SESSION[self::$session_name][self::$captcha_id]['selected']['salt'])) ? $_SESSION[self::$session_name][self::$captcha_id]['selected']['salt'] : hash('crc32', uniqid());
        }
    }
?>