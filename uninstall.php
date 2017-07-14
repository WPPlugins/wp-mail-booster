<?php

/**
 * This file contains code for remove tables and options at uninstall.
 *
 * @author  Tech Banker
 * @package wp-mail-booster/lib
 * @version 2.0
 */
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
} else {
    if (!current_user_can("manage_options")) {
        return;
    } else {
        $mail_booster_version_number = get_option("mail-booster-version-number");
        if ($mail_booster_version_number != "") {
            // Drop Tables
            global $wpdb;
            $settings_remove_tables = $wpdb->get_var
                    (
                    $wpdb->prepare
                            (
                            "SELECT meta_value FROM " . $wpdb->prefix . "mail_booster_meta
					WHERE meta_key = %s", "settings"
                    )
            );
            $settings_remove_tables_unserialize = unserialize($settings_remove_tables);

            if (esc_attr($settings_remove_tables_unserialize["remove_tables_at_uninstall"]) == "enable") {
                $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "mail_booster");
                $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . "mail_booster_meta");

                // Delete options
                delete_option("mail-booster-version-number");
                delete_option("wp-mail-booster-wizard-set-up");
                delete_option("mail_booster_tech_banker_site_id");
            }
        }
    }
}
