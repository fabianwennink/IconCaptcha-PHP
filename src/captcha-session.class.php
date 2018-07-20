<?php
    /**
     * Icon Captcha Plugin: v2.4.0
     * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */

    class CaptchaSession {

        /**
         * @var int             The captcha identifier.
         */
        public $id;

        /**
         * @var array           The array containing all the image hashes used by this captcha.
         */
        public $hashes;

        /**
         * @var int             The amount of times the images have been requested by the captcha.
         */
        public $icon_requests;

        /**
         * @var string          The captcha's theme name.
         */
        public $theme;

        /**
         * @var int             The last icon number that was clicked (1-5)
         */
        public $last_clicked;

        /**
         * @var string          The correct icon's hash.
         */
        public $correct_hash;

        /**
         * @var bool            If the captcha was completed (correct icon selected) or not.
         */
        public $completed;

        /**
         * Creates a new CaptchaSession object. Session data regarding the
         * captcha (given identifier) will be stored and can be retrieved when necessary.
         *
         * @since 2.2.0                     Function was introduced.
         *
         * @param int $id                   The captcha identifier.
         * @param string $theme             The captcha's theme.
         */
        public function __construct($id = 0, $theme = 'light') {
            $this->id = $id;
            $this->theme = $theme;
            $this->hashes = array();
            $this->icon_requests = 0;
            $this->last_clicked = -1;
            $this->correct_hash = '';
            $this->completed = false;

            // Try to load the captcha data from the session, if any data exists.
            $this->load();
        }

        /**
         * This will clear the set hashes, and reset the icon
         * request counter and last clicked icon.
         *
         * @since 2.2.0                     Function was introduced.
         */
        public function clear() {
            $this->hashes = array();
            $this->icon_requests = -1;
            $this->last_clicked = 0;
        }

        /**
         * Loads the captcha's session data based on the earlier set captcha identifier.
         *
         * @since 2.2.0                     Function was introduced.
         */
        public function load() {
            if(self::exists($this->id)) {
                if(isset($_SESSION['icon_captcha'][$this->id]['theme']))
                    $this->theme = $_SESSION['icon_captcha'][$this->id]['theme'];

                if(isset($_SESSION['icon_captcha'][$this->id]['hashes']))
                    $this->hashes = $_SESSION['icon_captcha'][$this->id]['hashes'];

                if(isset($_SESSION['icon_captcha'][$this->id]['icons']))
                    $this->icon_requests = $_SESSION['icon_captcha'][$this->id]['icons'];

                if(isset($_SESSION['icon_captcha'][$this->id]['last_clicked']))
                    $this->last_clicked = $_SESSION['icon_captcha'][$this->id]['last_clicked'];

                if(isset($_SESSION['icon_captcha'][$this->id]['correct_hash']))
                    $this->correct_hash = $_SESSION['icon_captcha'][$this->id]['correct_hash'];

                if(isset($_SESSION['icon_captcha'][$this->id]['completed']))
                    $this->completed = $_SESSION['icon_captcha'][$this->id]['completed'];
            }
        }

        /**
         * Saves the current data to the session. The data will be stored in an array.
         *
         * @since 2.2.0                     Function was introduced.
         */
        public function save() {
            $data = array(
                'hashes' 			=> $this->hashes,
                'icons' 			=> $this->icon_requests,
                'theme' 			=> $this->theme,
                'last_clicked' 		=> $this->last_clicked,
                'correct_hash'      => $this->correct_hash,
                'completed'         => $this->completed
            );

            $_SESSION['icon_captcha'][$this->id] = $data;
        }

        /**
         * Checks if the given captcha identifier has session data stored.
         *
         * @since 2.2.0                     Function was introduced.
         *
         * @param int $id                   The captcha identifier.
         *
         * @return boolean                  TRUE if any session data exists, FALSE if not.
         */
        public static function exists($id) {
            return isset($_SESSION['icon_captcha'][$id]);
        }
    }
?>