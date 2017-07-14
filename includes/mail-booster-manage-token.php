<?php

if (!defined("ABSPATH")) {
    exit;
} // Exit if accessed directly
if (!class_exists("mail_booster_manage_token")) {

    class mail_booster_manage_token {

        public
                $vendor_name,
                $access_token,
                $refresh_token,
                $expiry_time;

        public function __construct() {
            $this->get_token_mail_booster();
        }

        public static function get_instance() {
            static $instance = null;
            if ($instance === null) {
                $instance = new mail_booster_manage_token();
            }
            return $instance;
        }

        public function isValid() {
            $access_token = $this->retrieve_access_token_mail_booster();
            $refresh_token = $this->retrieve_refresh_token_mail_booster();
            return !(empty($access_token) || empty($refresh_token));
        }

        public function get_token_mail_booster() {
            $oauth_token = get_option("mail_booster_auth");
            $this->set_access_token_mail_booster($oauth_token["access_token"]);
            $this->set_refresh_token_mail_booster($oauth_token["refresh_token"]);
            $this->set_token_expirytime_mail_booster($oauth_token["auth_token_expires"]);
            $this->set_vendorname_mail_booster($oauth_token["vendor_name"]);
        }

        // Save the mail booster oauth token properties to the database
        public function save_token_mail_booster() {
            $oauth_token["access_token"] = $this->retrieve_access_token_mail_booster();
            $oauth_token["refresh_token"] = $this->retrieve_refresh_token_mail_booster();
            $oauth_token["auth_token_expires"] = $this->retrieve_token_expiry_time_mail_booster();
            $oauth_token["vendor_name"] = $this->get_vendor_mail_booster();
            update_option("mail_booster_auth", $oauth_token);
        }

        public function get_vendor_mail_booster() {
            return $this->vendor_name;
        }

        public function retrieve_token_expiry_time_mail_booster() {
            return $this->expiry_time;
        }

        public function retrieve_access_token_mail_booster() {
            return $this->access_token;
        }

        public function retrieve_refresh_token_mail_booster() {
            return $this->refresh_token;
        }

        public function set_vendorname_mail_booster($name) {
            $this->vendor_name = esc_html($name);
        }

        public function set_token_expirytime_mail_booster($time) {
            $this->expiry_time = esc_html($time);
        }

        public function set_access_token_mail_booster($token) {
            $this->access_token = esc_html($token);
        }

        public function set_refresh_token_mail_booster($token) {
            $this->refresh_token = esc_html($token);
        }

    }

}