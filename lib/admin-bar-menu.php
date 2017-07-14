<?php

/**
 * This file is used for creating admin bar menu.
 *
 * @author  Tech Banker
 * @package wp-mail-booster/lib
 * @version 2.0
 */
if (!defined("ABSPATH")) {
    exit;
} // Exit if accessed directly
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
                        "SELECT meta_value FROM " . mail_booster_meta() . "
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
                $flag = $capabilities[0];
                break;

            case "author":
                $flag = $capabilities[1];
                break;

            case "editor":
                $flag = $capabilities[2];
                break;

            case "contributor":
                $flag = $capabilities[3];
                break;

            case "subscriber":
                $flag = $capabilities[4];
                break;

            default:
                $flag = $capabilities[5];
                break;
        }

        if ($flag == "1") {
            global $wp_version;
            $wp_admin_bar->add_menu(array
                (
                "id" => "wp_mail_booster",
                "title" => "<img src = \"" . plugins_url("assets/global/img/icon.png", dirname(__FILE__)) . "\" style=\"vertical-align:text-top;  margin-right:5px;\"./>$wp_mail_booster",
                "href" => admin_url("admin.php?page=mail_booster_email_configuration")
            ));
            $wp_admin_bar->add_menu(array
                (
                "parent" => "wp_mail_booster",
                "id" => "email_configuration_mail_booster",
                "title" => $mb_email_configuration,
                "href" => admin_url("admin.php?page=mail_booster_email_configuration")
            ));
            $wp_admin_bar->add_menu(array
                (
                "parent" => "wp_mail_booster",
                "id" => "test_email_mail_booster",
                "title" => $mb_test_email,
                "href" => admin_url("admin.php?page=mail_booster_test_email")
            ));
            $wp_admin_bar->add_menu(array
                (
                "parent" => "wp_mail_booster",
                "id" => "connectivity_test_mail_booster",
                "title" => $mb_connectivity_test,
                "href" => admin_url("admin.php?page=mail_booster_connectivity_test")
            ));
            $wp_admin_bar->add_menu(array
                (
                "parent" => "wp_mail_booster",
                "id" => "email_logs_mail_booster",
                "title" => $mb_email_logs,
                "href" => admin_url("admin.php?page=mail_booster_email_logs")
            ));
            $wp_admin_bar->add_menu(array
                (
                "parent" => "wp_mail_booster",
                "id" => "general_settings_mail_booster",
                "title" => $mb_settings,
                "href" => admin_url("admin.php?page=mail_booster_settings")
            ));
            $wp_admin_bar->add_menu(array
                (
                "parent" => "wp_mail_booster",
                "id" => "roles_and_capabilities_mail_booster",
                "title" => $mb_roles_and_capabilities,
                "href" => admin_url("admin.php?page=mail_booster_roles_and_capabilities")
            ));
            $wp_admin_bar->add_menu(array
                (
                "parent" => "wp_mail_booster",
                "id" => "feedbacks_mail_booster",
                "title" => $mb_feedbacks,
                "href" => admin_url("admin.php?page=mail_booster_feedbacks")
            ));
            $wp_admin_bar->add_menu(array
                (
                "parent" => "wp_mail_booster",
                "id" => "system_information_mail_booster",
                "title" => $mb_system_information,
                "href" => admin_url("admin.php?page=mail_booster_system_information")
            ));
            $wp_admin_bar->add_menu(array
                (
                "parent" => "wp_mail_booster",
                "id" => "mail_booster_upgrade",
                "title" => $mb_upgrade,
                "href" => admin_url("admin.php?page=mail_booster_upgrade")
            ));
        }
    }
}