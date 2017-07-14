<?php

/**
 * This file is used for creating sidebar menu.
 *
 * @author  Tech Banker
 * @package wp-mail-booster/lib
 * @version 2.0
 */
if (!defined("ABSPATH")) {
    exit;
}// Exit if accessed directly
if (!is_user_logged_in()) {
    return;
} else {
    $access_granted = false;
    foreach ($user_role_permission as $permission) {
        if (current_user_can($permission)) {
            $access_granted = true;
            break;
        }
    }
    if (!$access_granted) {
        return;
    } else {
        $flag = 0;

        $role_capabilities = $wpdb->get_var
                (
                $wpdb->prepare
                        (
                        "SELECT meta_value from " . mail_booster_meta() . "
				WHERE meta_key = %s", "roles_and_capabilities"
                )
        );

        $roles_and_capabilities_unserialized_data = unserialize($role_capabilities);
        $roles_and_capabilities_data = isset($roles_and_capabilities_unserialized_data["roles_and_capabilities"]) ? esc_attr($roles_and_capabilities_unserialized_data["roles_and_capabilities"]) : "";
        $capabilities = explode(",", $roles_and_capabilities_data);

        if (is_super_admin()) {
            $mb_role = "administrator";
        } else {
            $mb_role = check_user_roles_mail_booster();
        }
        switch ($mb_role) {
            case "administrator":
                $privileges = "administrator_privileges";
                $flag = $capabilities[0];
                break;

            case "author":
                $privileges = "author_privileges";
                $flag = $capabilities[1];
                break;

            case "editor":
                $privileges = "editor_privileges";
                $flag = $capabilities[2];
                break;

            case "contributor":
                $privileges = "contributor_privileges";
                $flag = $capabilities[3];
                break;

            case "subscriber":
                $privileges = "subscriber_privileges";
                $flag = $capabilities[4];
                break;

            default:
                $privileges = "other_roles_privileges";
                $flag = $capabilities[5];
                break;
        }

        foreach ($roles_and_capabilities_unserialized_data as $key => $value) {
            if ($privileges == $key) {
                $privileges_value = $value;
                break;
            }
        }

        $full_control = explode(",", $privileges_value);
        if (!defined("full_control")) {
            define("full_control", "$full_control[0]");
        }
        if (!defined("email_configuration_mail_booster")) {
            define("email_configuration_mail_booster", "$full_control[1]");
        }
        if (!defined("test_email_mail_booster")) {
            define("test_email_mail_booster", "$full_control[2]");
        }
        if (!defined("connectivity_test_mail_booster")) {
            define("connectivity_test_mail_booster", "$full_control[3]");
        }
        if (!defined("email_logs_mail_booster")) {
            define("email_logs_mail_booster", "$full_control[4]");
        }
        if (!defined("settings_mail_booster")) {
            define("settings_mail_booster", "$full_control[5]");
        }
        if (!defined("roles_and_capabilities_mail_booster")) {
            define("roles_and_capabilities_mail_booster", "$full_control[6]");
        }
        if (!defined("system_information_mail_booster")) {
            define("system_information_mail_booster", "$full_control[7]");
        }
        $isLicensed = get_option("mail_booster_api_details");
        $check_wp_mail_booster_wizard = get_option("wp-mail-booster-wizard-set-up");

        if ($flag == "1") {
            global $wp_version;
            if ($check_wp_mail_booster_wizard) {
                add_menu_page($wp_mail_booster, $wp_mail_booster, "read", "mail_booster_email_configuration", "", plugins_url("assets/global/img/icon.png", dirname(__FILE__)));
            } else {
                add_menu_page($wp_mail_booster, $wp_mail_booster, "read", "wp_mail_booster_wizard", "", plugins_url("assets/global/img/icon.png", dirname(__FILE__)));
                add_submenu_page($wp_mail_booster, $wp_mail_booster, "", "read", "wp_mail_booster_wizard", "wp_mail_booster_wizard");
            }

            add_submenu_page("mail_booster_email_configuration", $mb_email_configuration, $mb_email_configuration, "read", "mail_booster_email_configuration", $check_wp_mail_booster_wizard == "" ? "wp_mail_booster_wizard" : "mail_booster_email_configuration");
            add_submenu_page("mail_booster_email_configuration", $mb_test_email, $mb_test_email, "read", "mail_booster_test_email", $check_wp_mail_booster_wizard == "" ? "wp_mail_booster_wizard" : "mail_booster_test_email");
            add_submenu_page("mail_booster_email_configuration", $mb_connectivity_test, $mb_connectivity_test, "read", "mail_booster_connectivity_test", $check_wp_mail_booster_wizard == "" ? "wp_mail_booster_wizard" : "mail_booster_connectivity_test");
            add_submenu_page("mail_booster_email_configuration", $mb_email_logs, $mb_email_logs, "read", "mail_booster_email_logs", $check_wp_mail_booster_wizard == "" ? "wp_mail_booster_wizard" : "mail_booster_email_logs");
            add_submenu_page("mail_booster_email_configuration", $mb_settings, $mb_settings, "read", "mail_booster_settings", $check_wp_mail_booster_wizard == "" ? "wp_mail_booster_wizard" : "mail_booster_settings");
            add_submenu_page("mail_booster_email_configuration", $mb_roles_and_capabilities, $mb_roles_and_capabilities, "read", "mail_booster_roles_and_capabilities", $check_wp_mail_booster_wizard == "" ? "wp_mail_booster_wizard" : "mail_booster_roles_and_capabilities");
            add_submenu_page("mail_booster_email_configuration", $mb_feedbacks, $mb_feedbacks, "read", "mail_booster_feedbacks", $check_wp_mail_booster_wizard == "" ? "wp_mail_booster_wizard" : "mail_booster_feedbacks");
            add_submenu_page("mail_booster_email_configuration", $mb_system_information, $mb_system_information, "read", "mail_booster_system_information", $check_wp_mail_booster_wizard == "" ? "wp_mail_booster_wizard" : "mail_booster_system_information");
            add_submenu_page("mail_booster_email_configuration", $mb_upgrade, $mb_upgrade, "read", "mail_booster_upgrade", $check_wp_mail_booster_wizard == "" ? "wp_mail_booster_wizard" : "mail_booster_upgrade");
        }

        /*
          Function Name: wp_mail_booster_wizard
          Parameters: No
          Description: This function is used for creating wp_mail_booster_wizard menu.
          Created On: 21-04-2017 12:35
          Created By: Tech Banker Team
         */

        function wp_mail_booster_wizard() {
            global $wpdb;
            $user_role_permission = get_users_capabilities_mail_booster();
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
                include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "views/wizard/wizard.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "views/wizard/wizard.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/footer.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/footer.php";
            }
        }

        /*
          Function Name: mail_booster_email_configuration
          Parameters: No
          Description: This function is used to create mb_email_configuration menu.
          Created On: 15-06-2016 10:44
          Created By: Tech Banker Team
         */

        function mail_booster_email_configuration() {
            global $wpdb;
            $user_role_permission = get_users_capabilities_mail_booster();
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
                include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/header.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/header.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/queries.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/queries.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "views/email-configuration/email-configuration.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "views/email-configuration/email-configuration.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/footer.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/footer.php";
            }
        }

        /*
          Function Name: mail_booster_test_email
          Parameters: No
          Description: This function is used to test email menu.
          Created On: 21-01-2017 10:44
          Created By: Tech Banker Team
         */

        function mail_booster_test_email() {
            global $wpdb;
            $user_role_permission = get_users_capabilities_mail_booster();
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
                include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/header.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/header.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/queries.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/queries.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "views/test-email/test-email.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "views/test-email/test-email.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/footer.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/footer.php";
            }
        }

        /*
          Function Name: mail_booster_connectivity_test
          Parameters: No
          Description: This function is used to test email menu.
          Created On: 22-04-2017  12:50
          Created By: Tech Banker Team
         */

        function mail_booster_connectivity_test() {
            global $wpdb;
            $user_role_permission = get_users_capabilities_mail_booster();
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
                include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/header.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/header.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/queries.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/queries.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "views/connectivity-test/connectivity-test.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "views/connectivity-test/connectivity-test.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/footer.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/footer.php";
            }
        }

        /*
          Function Name: mail_booster_email_logs
          Parameters: No
          Description: This function is used to create mb_email_logs menu.
          Created On: 15-06-2016 10:44
          Created By: Tech Banker Team
         */

        function mail_booster_email_logs() {
            global $wpdb;
            $user_role_permission = get_users_capabilities_mail_booster();
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
                include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/header.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/header.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/queries.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/queries.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "views/email-logs/email-logs.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "views/email-logs/email-logs.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/footer.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/footer.php";
            }
        }

        /*
          Function Name: mail_booster_settings
          Parameters: No
          Description: This function is used to create mb_settings menu.
          Created On: 15-06-2016 10:44
          Created By: Tech Banker Team
         */

        function mail_booster_settings() {
            global $wpdb;
            $user_role_permission = get_users_capabilities_mail_booster();
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
                include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/header.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/header.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/queries.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/queries.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "views/settings/settings.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "views/settings/settings.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/footer.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/footer.php";
            }
        }

        /*
          Function Name: mail_booster_roles_and_capabilities
          Parameters: No
          Description: This function is used to create mb_roles_and_capabilities menu.
          Created On: 15-06-2016 12:59
          Created By: Tech Banker Team
         */

        function mail_booster_roles_and_capabilities() {
            global $wpdb;
            $user_role_permission = get_users_capabilities_mail_booster();
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
                include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/header.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/header.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/queries.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/queries.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "views/roles-and-capabilities/roles-and-capabilities.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "views/roles-and-capabilities/roles-and-capabilities.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/footer.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/footer.php";
            }
        }

        /*
          Function Name: mail_booster_feedbacks
          Parameters: No
          Description: This function is used to create mb_feedbacks menu.
          Created On: 15-06-2016 10:44
          Created By: Tech Banker Team
         */

        function mail_booster_feedbacks() {
            global $wpdb;
            $user_role_permission = get_users_capabilities_mail_booster();
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
                include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/header.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/header.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/queries.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/queries.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "views/feedbacks/feedbacks.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "views/feedbacks/feedbacks.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/footer.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/footer.php";
            }
        }

        /*
          Function Name: mail_booster_system_information
          Parameters: No
          Description: This function is used to create mb_system_information menu.
          Created On: 15-06-2016 02:29
          Created By: Tech Banker Team
         */

        function mail_booster_system_information() {
            global $wpdb;
            $user_role_permission = get_users_capabilities_mail_booster();
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
                include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/header.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/header.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/queries.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/queries.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "views/system-information/system-information.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "views/system-information/system-information.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/footer.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/footer.php";
            }
        }

        /*
          Function Name: mail_booster_upgrade
          Parameter: No
          Description: This Function is used for upgrade.
          Created On: 24-04-2017 16:55
          Created By: Tech Banker Team
         */

        function mail_booster_upgrade() {
            global $wpdb;
            $user_role_permission = get_users_capabilities_mail_booster();
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
                include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/header.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/header.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/sidebar.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/queries.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/queries.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "views/premium-editions/premium-editions.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "views/premium-editions/premium-editions.php";
            }
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/footer.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "includes/footer.php";
            }
        }

    }
}