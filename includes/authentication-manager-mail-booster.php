<?php

if (!defined("ABSPATH")) {
    exit;
} // Exit if accessed directly
if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/google-authentication-mail-booster.php")) {
    require_once MAIL_BOOSTER_DIR_PATH . "includes/google-authentication-mail-booster.php";
}
if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/microsoft-authentication-mail-booster.php")) {
    require_once MAIL_BOOSTER_DIR_PATH . "includes/microsoft-authentication-mail-booster.php";
}
if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/yahoo-authentication-mail-booster.php")) {
    require_once MAIL_BOOSTER_DIR_PATH . "includes/yahoo-authentication-mail-booster.php";
}
if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/mail-booster-register-transport.php")) {
    include_once MAIL_BOOSTER_DIR_PATH . "includes/mail-booster-register-transport.php";
}

if (!class_exists("authentication_manager_mail_booster")) {

    class authentication_manager_mail_booster {

        public function create_authentication_manager() {
            $obj_mail_booster_register_transport = new mail_booster_register_transport();
            $transport = $obj_mail_booster_register_transport->retrieve_mailertype_mail_booster();
            return $this->create_manager($transport);
        }

        public function create_manager(mail_booster_smtp_transport $transport) {
            $obj_mb_config_provider = new mail_booster_configuration_provider();
            $configuration_settings = $obj_mb_config_provider->get_configuration_settings();
            $authorization_token = mail_booster_manage_token::get_instance();
            $hostname = $configuration_settings["hostname"];
            $client_id = $configuration_settings["client_id"];
            $client_secret = $configuration_settings["client_secret"];
            $sender_email = $configuration_settings["sender_email"];
            $redirect_uri = admin_url("admin-ajax.php");
            if ($this->check_google_service_provider_mail_booster($hostname)) {
                $obj_service_provider = new google_authentication_mail_booster($client_id, $client_secret, $authorization_token, $redirect_uri, $sender_email);
            } elseif ($this->check_microsoft_service_provider_mail_booster($hostname)) {
                $obj_service_provider = new microsoft_authentication_mail_booster($client_id, $client_secret, $authorization_token, $redirect_uri);
            } elseif ($this->check_yahoo_service_provider_mail_booster($hostname)) {
                $obj_service_provider = new yahoo_authentication_mail_booster($client_id, $client_secret, $authorization_token, $redirect_uri);
            }
            return $obj_service_provider;
        }

        public function check_google_service_provider_mail_booster($hostname) {
            return mail_booster_zend_mail_helper::email_domains_mail_booster($hostname, "gmail.com") || mail_booster_zend_mail_helper::email_domains_mail_booster($hostname, "googleapis.com");
        }

        public function check_microsoft_service_provider_mail_booster($hostname) {
            return mail_booster_zend_mail_helper::email_domains_mail_booster($hostname, "live.com");
        }

        public function check_yahoo_service_provider_mail_booster($hostname) {
            return strpos($hostname, "yahoo");
        }

    }

}