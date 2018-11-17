<?php
    /**
     * Icon Captcha Plugin: v2.5.0
     * Copyright © 2017, Fabian Wennink (https://www.fabianwennink.nl)
     *
     * Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
     */

    class CaptchaSession {

        const ICON_CAPTCHA = 'icon_captcha';
        const CAPTCHA_THEME = 'theme';
        const CAPTCHA_HASHES = 'hashes';
        const CAPTCHA_ICONS = 'icons';
        const CAPTCHA_LAST_CLICKED = 'last_clicked';
        const CAPTCHA_CORRECT_HASH = 'correct_hash';
        const CAPTCHA_COMPLETED = 'completed';

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
                if(isset($_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_THEME])) {
                    $this->theme = $_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_THEME];
                }

                if(isset($_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_HASHES])) {
                    $this->hashes = $_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_HASHES];
                }

                if(isset($_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_ICONS])) {
                    $this->icon_requests = $_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_ICONS];
                }

                if(isset($_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_LAST_CLICKED])) {
                    $this->last_clicked = $_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_LAST_CLICKED];
                }

                if(isset($_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_CORRECT_HASH])) {
                    $this->correct_hash = $_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_CORRECT_HASH];
                }

                if(isset($_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_COMPLETED])) {
                    $this->completed = $_SESSION[self::ICON_CAPTCHA][$this->id][self::CAPTCHA_COMPLETED];
                }
            }
        }

        /**
         * Saves the current data to the session. The data will be stored in an array.
         *
         * @since 2.2.0                     Function was introduced.
         */
        public function save() {
            $data = array(
                self::CAPTCHA_HASHES 			=> $this->hashes,
                self::CAPTCHA_ICONS 			=> $this->icon_requests,
                self::CAPTCHA_THEME 			=> $this->theme,
                self::CAPTCHA_LAST_CLICKED 		=> $this->last_clicked,
                self::CAPTCHA_CORRECT_HASH      => $this->correct_hash,
                self::CAPTCHA_COMPLETED         => $this->completed
            );

            $_SESSION[self::ICON_CAPTCHA][$this->id] = $data;
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
            return isset($_SESSION[self::ICON_CAPTCHA][$id]);
        }
    }