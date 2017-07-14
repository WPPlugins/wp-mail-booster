<?php

if (!defined("ABSPATH")) {
    exit;
} // Exit if accessed directly
if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/mail-booster-configuration-provider.php")) {
    include_once MAIL_BOOSTER_DIR_PATH . "includes/mail-booster-configuration-provider.php";
}

if (!class_exists("mail_booster_configure_transport")) {

    class mail_booster_configure_transport {

        public function configure_plain_transport() {
            $obj_mail_booster_configuration_provider = new mail_booster_configuration_provider();
            $configuration_setting = $obj_mail_booster_configuration_provider->get_configuration_settings();

            $port = $configuration_setting["port"];
            $enc_type = $configuration_setting["enc_type"];

            //set configurations
            $config = array(
                "port" => $port
            );
            if ($enc_type != "none") {
                $config["ssl"] = $enc_type;
            }

            $config["auth"] = $configuration_setting["auth_type"];
            $config["username"] = $configuration_setting["username"];
            $config["password"] = base64_decode($configuration_setting["password"]);
            return $config;
        }

        public function configure_oauth_transport() {
            $obj_mail_booster_configuration_provider = new mail_booster_configuration_provider();
            $configuration_setting = $obj_mail_booster_configuration_provider->get_configuration_settings();
            $sender_email = $configuration_setting["email_address"];

            //set vendor name for yahoo
            $vendor = "";
            if (mail_booster_zend_mail_helper::email_domains_mail_booster($configuration_setting["hostname"], "yahoo.com")) {
                $vendor = "yahoo";
            }

            $obj_mail_booster_manage_token = mail_booster_manage_token::get_instance();
            // create oauth2 string
            $xoauth2_request = base64_encode(sprintf("user=%s\1auth=Bearer %s\1%s\1", $sender_email, $obj_mail_booster_manage_token->retrieve_access_token_mail_booster(), $vendor));

            //set configurations
            $config = array(
                "ssl" => $configuration_setting["enc_type"],
                "port" => $configuration_setting["port"],
                "auth" => "oauth2",
                "xoauth2_request" => $xoauth2_request
            );
            return $config;
        }

    }

}