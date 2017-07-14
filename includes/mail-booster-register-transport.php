<?php

if (!defined("ABSPATH")) {
    exit;
} // Exit if accessed directly
if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/mail-booster-configure-transport.php")) {
    require_once MAIL_BOOSTER_DIR_PATH . "includes/mail-booster-configure-transport.php";
}

class mail_booster_register_transport {

    public static $transport;

    public function listing_transport_mail_booster($instance) {
        self::$transport = $instance;
    }

    // This function is used to get the transport
    public function retrieve_mailertype_mail_booster() {
        return self::$transport;
    }

}
