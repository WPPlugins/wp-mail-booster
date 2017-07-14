<?php

/*
  Plugin Name: Email SMTP Plugin by Mail Booster
  Plugin URI: http://beta.tech-banker.com
  Description: Mail Booster allows you to send emails from your WordPress site with advanced SMTP Settings or PHPMailer.
  Author: Tech Banker
  Author URI: http://beta.tech-banker.com
  Version: 3.0.4
  License: GPLv3
  Text Domain: wp-mail-booster
  Domain Path: /languages
 */

if (!defined("ABSPATH")) {
    exit;
}// Exit if accessed directly
/* Constant Declaration */
if (!defined("MAIL_BOOSTER_FILE")) {
    define("MAIL_BOOSTER_FILE", plugin_basename(__FILE__));
}
if (!defined("MAIL_BOOSTER_DIR_PATH")) {
    define("MAIL_BOOSTER_DIR_PATH", plugin_dir_path(__FILE__));
}
if (!defined("MAIL_BOOSTER_PLUGIN_DIRNAME")) {
    define("MAIL_BOOSTER_PLUGIN_DIRNAME", plugin_basename(dirname(__FILE__)));
}
if (!defined("MAIL_BOOSTER_LOCAL_TIME")) {
    define("MAIL_BOOSTER_LOCAL_TIME", strtotime(date_i18n("Y-m-d H:i:s")));
}

if (is_ssl()) {
    if (!defined("tech_banker_url")) {
        define("tech_banker_url", "https://tech-banker.com");
    }
    if (!defined("tech_banker_beta_url")) {
        define("tech_banker_beta_url", "https://beta.tech-banker.com");
    }
} else {
    if (!defined("tech_banker_url")) {
        define("tech_banker_url", "http://tech-banker.com");
    }
    if (!defined("tech_banker_beta_url")) {
        define("tech_banker_beta_url", "http://beta.tech-banker.com");
    }
}
if (!defined("tech_banker_stats_url")) {
    define("tech_banker_stats_url", "http://stats.tech-banker-services.org");
}
if (!defined("mail_booster_version_number")) {
    define("mail_booster_version_number", "3.0.4");
}

$memory_limit_mail_booster = intval(ini_get("memory_limit"));
if (!extension_loaded('suhosin') && $memory_limit_mail_booster < 512) {
    @ini_set("memory_limit", "512M");
}

@ini_set("max_execution_time", 6000);
@ini_set("max_input_vars", 10000);

/*
  Function Name: install_script_for_mail_booster
  Parameters: No
  Description: This function is used to create Tables in Database.
  Created On: 15-06-2016 09:52
  Created By: Tech Banker Team
 */

function install_script_for_mail_booster() {
    global $wpdb;
    if (is_multisite()) {
        $blog_ids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
        foreach ($blog_ids as $blog_id) {
            switch_to_blog($blog_id);
            $version = get_option("mail-booster-version-number");
            if ($version < "3.0.0") {
                if (file_exists(MAIL_BOOSTER_DIR_PATH . "lib/install-script.php")) {
                    include MAIL_BOOSTER_DIR_PATH . "lib/install-script.php";
                }
            }
            restore_current_blog();
        }
    } else {
        $version = get_option("mail-booster-version-number");
        if ($version < "3.0.0") {
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "lib/install-script.php")) {
                include_once MAIL_BOOSTER_DIR_PATH . "lib/install-script.php";
            }
        }
    }
}

/*
  Function Name: check_user_roles_mail_booster
  Parameters: Yes($user)
  Description: This function is used for checking roles of different users.
  Created On: 19-10-2016 03:40
  Created By: Tech Banker Team
 */

function check_user_roles_mail_booster() {
    global $current_user;
    $user = $current_user ? new WP_User($current_user) : wp_get_current_user();
    return $user->roles ? $user->roles[0] : false;
}

/*
  Function Name: mail_booster
  Parameters: No
  Description: This function is used to return Parent Table name with prefix.
  Created On: 15-06-2016 10:44
  Created By: Tech Banker Team
 */

function mail_booster() {
    global $wpdb;
    return $wpdb->prefix . "mail_booster";
}

/*
  Function Name: mail_booster_meta
  Parameters: No
  Description: This function is used to return Meta Table name with prefix.
  Created On: 15-06-2016 10:44
  Created By: Tech Banker Team
 */

function mail_booster_meta() {
    global $wpdb;
    return $wpdb->prefix . "mail_booster_meta";
}

/*
  Function Name: get_others_capabilities_mail_booster
  Parameters: No
  Description: This function is used to get all the roles available in WordPress
  Created On: 21-10-2016 12:06
  Created By: Tech Banker Team
 */

function get_others_capabilities_mail_booster() {
    $user_capabilities = array();
    if (function_exists("get_editable_roles")) {
        foreach (get_editable_roles() as $role_name => $role_info) {
            foreach ($role_info["capabilities"] as $capability => $_) {
                if (!in_array($capability, $user_capabilities)) {
                    array_push($user_capabilities, $capability);
                }
            }
        }
    } else {
        $user_capabilities = array(
            "manage_options",
            "edit_plugins",
            "edit_posts",
            "publish_posts",
            "publish_pages",
            "edit_pages",
            "read"
        );
    }
    return $user_capabilities;
}

/*
  Function Name: mail_booster_action_links
  Parameters: Yes
  Description: This function is used to create link for Pro Editions.
  Created On: 24-04-2017 16:40
  Created By: Tech Banker Team
 */

function mail_booster_action_links($plugin_link) {
    $plugin_link[] = "<a href=\"http://beta.tech-banker.com/products/mail-bank/\" style=\"color: red; font-weight: bold;\" target=\"_blank\">Go Pro!</a>";
    return $plugin_link;
}

/*
  Function Name: mail_booster_settings_link
  Parameters: Yes($action)
  Description: This function is used to add settings link.
  Created On: 09-08-2016 02:50
  Created By: Tech Banker Team
 */

function mail_booster_settings_link($action) {
    global $wpdb;
    $user_role_permission = get_users_capabilities_mail_booster();
    $settings_link = '<a href = "' . admin_url('admin.php?page=mail_booster_email_configuration') . '">' . "Settings" . '</a>';
    array_unshift($action, $settings_link);
    return $action;
}

$version = get_option("mail-booster-version-number");
if ($version >= "3.0.0") {
    /*
      Function Name: backend_js_css_for_mail_booster
      Parameters: No
      Description: This function is used for including backend css and js.
      Created On: 25-06-2016 05:02
      Created By: Tech Banker Team
     */

    if (is_admin()) {

        function backend_js_css_for_mail_booster($hook) {
            $pages_mail_booster = array(
                "wp_mail_booster_wizard",
                "mail_booster_email_configuration",
                "mail_booster_test_email",
                "mail_booster_connectivity_test",
                "mail_booster_email_logs",
                "mail_booster_settings",
                "mail_booster_roles_and_capabilities",
                "mail_booster_feedbacks",
                "mail_booster_system_information",
                "mail_booster_upgrade"
            );
            if (in_array(isset($_REQUEST["page"]) ? esc_attr($_REQUEST["page"]) : "", $pages_mail_booster)) {
                wp_enqueue_script("jquery");
                wp_enqueue_script("jquery-ui-datepicker");
                wp_enqueue_script("mail-booster-bootstrap.js", plugins_url("assets/global/plugins/custom/js/custom.js", __FILE__));
                wp_enqueue_script("mail-booster-jquery.validate.js", plugins_url("assets/global/plugins/validation/jquery.validate.js", __FILE__));
                wp_enqueue_script("mail-booster-jquery.datatables.js", plugins_url("assets/global/plugins/datatables/media/js/jquery.datatables.js", __FILE__));
                wp_enqueue_script("mail-booster-jquery.fngetfilterednodes.js", plugins_url("assets/global/plugins/datatables/media/js/fngetfilterednodes.js", __FILE__));
                wp_enqueue_script("mail-booster-toastr.js", plugins_url("assets/global/plugins/toastr/toastr.js", __FILE__));

                wp_enqueue_style("mail-booster-simple-line-icons.css", plugins_url("assets/global/plugins/icons/icons.css", __FILE__));
                wp_enqueue_style("mail-booster-components.css", plugins_url("assets/global/css/components.css", __FILE__));
                wp_enqueue_style("mail-booster-custom.css", plugins_url("assets/admin/layout/css/mail-booster-custom.css", __FILE__));
                wp_enqueue_style("mail-booster-premium-edition.css", plugins_url("assets/admin/layout/css/premium-edition.css", __FILE__));
                if (is_rtl()) {
                    wp_enqueue_style("mail-booster-bootstrap.css", plugins_url("assets/global/plugins/custom/css/custom-rtl.css", __FILE__));
                    wp_enqueue_style("mail-booster-layout.css", plugins_url("assets/admin/layout/css/layout-rtl.css", __FILE__));
                    wp_enqueue_style("mail-booster-tech-banker-custom.css", plugins_url("assets/admin/layout/css/tech-banker-custom-rtl.css", __FILE__));
                    wp_enqueue_style("mail-booster-premium-edition.css", plugins_url("assets/admin/layout/css/premium-edition.css", __FILE__));
                } else {
                    wp_enqueue_style("mail-booster-bootstrap.css", plugins_url("assets/global/plugins/custom/css/custom.css", __FILE__));
                    wp_enqueue_style("mail-booster-layout.css", plugins_url("assets/admin/layout/css/layout.css", __FILE__));
                    wp_enqueue_style("mail-booster-tech-banker-custom.css", plugins_url("assets/admin/layout/css/tech-banker-custom.css", __FILE__));
                }
                wp_enqueue_style("mail-booster-default.css", plugins_url("assets/admin/layout/css/themes/default.css", __FILE__));
                wp_enqueue_style("mail-booster-toastr.min.css", plugins_url("assets/global/plugins/toastr/toastr.css", __FILE__));
                wp_enqueue_style("mail-booster-jquery-ui.css", plugins_url("assets/global/plugins/datepicker/jquery-ui.css", __FILE__), false, "2.0", false);
                wp_enqueue_style("mail-booster-datatables.foundation.css", plugins_url("assets/global/plugins/datatables/media/css/datatables.foundation.css", __FILE__));
            }
        }

        add_action("admin_enqueue_scripts", "backend_js_css_for_mail_booster");
    }


    /*
      Function Name: helper_file_for_mail_booster
      Parameters: No
      Description: This function is used to create Class and Function to perform operations.
      Created On: 15-06-2016 09:52
      Created By: Tech Banker Team
     */

    function helper_file_for_mail_booster() {
        global $wpdb;
        $user_role_permission = get_users_capabilities_mail_booster();
        if (file_exists(MAIL_BOOSTER_DIR_PATH . "lib/helper.php")) {
            include_once MAIL_BOOSTER_DIR_PATH . "lib/helper.php";
        }
    }

    /*
      Function Name: sidebar_menu_for_mail_booster
      Parameters: No
      Description: This function is used to create Admin sidebar menus.
      Created On: 15-06-2016 09:52
      Created By: Tech Banker Team
     */

    function sidebar_menu_for_mail_booster() {
        global $wpdb, $current_user;
        $user_role_permission = get_users_capabilities_mail_booster();
        if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
            include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
        }
        if (file_exists(MAIL_BOOSTER_DIR_PATH . "lib/sidebar-menu.php")) {
            include_once MAIL_BOOSTER_DIR_PATH . "lib/sidebar-menu.php";
        }
    }

    /*
      Function Name: topbar_menu_for_mail_booster
      Parameters: No
      Description: This function is used for creating Top bar menu.
      Created On: 15-06-2016 10:44
      Created By: Tech Banker Team
     */

    function topbar_menu_for_mail_booster() {
        global $wpdb, $current_user, $wp_admin_bar;
        $role_capabilities = $wpdb->get_var
                (
                $wpdb->prepare
                        (
                        "SELECT meta_value FROM " . mail_booster_meta() . "
					WHERE meta_key = %s", "roles_and_capabilities"
                )
        );
        $roles_and_capabilities_unserialized_data = unserialize($role_capabilities);
        $top_bar_menu = $roles_and_capabilities_unserialized_data["show_mail_booster_top_bar_menu"];

        if ($top_bar_menu == "enable") {
            $user_role_permission = get_users_capabilities_mail_booster();
            if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
                include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
            }
            if (get_option("wp-mail-booster-wizard-set-up")) {
                if (file_exists(MAIL_BOOSTER_DIR_PATH . "lib/admin-bar-menu.php")) {
                    include_once MAIL_BOOSTER_DIR_PATH . "lib/admin-bar-menu.php";
                }
            }
        }
    }

    /*
      Function Name: ajax_register_for_mail_booster
      Parameters: No
      Description: This function is used for register ajax.
      Created On: 15-06-2016 10:44
      Created By: Tech Banker Team
     */

    function ajax_register_for_mail_booster() {
        global $wpdb;
        $user_role_permission = get_users_capabilities_mail_booster();
        if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/translations.php")) {
            include MAIL_BOOSTER_DIR_PATH . "includes/translations.php";
        }
        if (file_exists(MAIL_BOOSTER_DIR_PATH . "lib/action-library.php")) {
            include_once MAIL_BOOSTER_DIR_PATH . "lib/action-library.php";
        }
    }

    /*
      Function Name: get_users_capabilities_mail_booster
      Parameters: No
      Description: This function is used to get users capabilities.
      Created On: 21-10-2016 15:21
      Created By: Tech Banker Team
     */

    function get_users_capabilities_mail_booster() {
        global $wpdb;
        $capabilities = $wpdb->get_var
                (
                $wpdb->prepare
                        (
                        "SELECT meta_value FROM " . mail_booster_meta() . "
					WHERE meta_key = %s", "roles_and_capabilities"
                )
        );
        $core_roles = array(
            "manage_options",
            "edit_plugins",
            "edit_posts",
            "publish_posts",
            "publish_pages",
            "edit_pages",
            "read"
        );
        $unserialized_capabilities = unserialize($capabilities);
        return isset($unserialized_capabilities["capabilities"]) ? $unserialized_capabilities["capabilities"] : $core_roles;
    }

    /*
      Function Name: plugin_load_textdomain_mail_booster
      Parameters: No
      Description: This function is used to load the plugin's translated strings.
      Created On: 16-06-2016 09:47
      Created By: Tech Banker Team
     */

    function plugin_load_textdomain_mail_booster() {
        load_plugin_textdomain("wp-mail-booster", false, MAIL_BOOSTER_PLUGIN_DIRNAME . "/languages");
    }

    /*
      Function Name: oauth_handling_mail_booster
      Parameters: No
      Description: This function is used to Manage Redirect.
      Created On: 11-08-2016 11:53
      Created By: Tech Banker Team
     */

    function oauth_handling_mail_booster() {
        if (is_admin()) {
            if ((count($_REQUEST) <= 2) && isset($_REQUEST["code"])) {
                if (file_exists(MAIL_BOOSTER_DIR_PATH . "lib/callback.php")) {
                    include_once MAIL_BOOSTER_DIR_PATH . "lib/callback.php";
                }
            } elseif ((count($_REQUEST) <= 2) && isset($_REQUEST["error"])) {
                $url = admin_url("admin.php?page=mail_booster_email_configuration");
                header("location: $url");
            }
        }
    }

    /*
      Function Name: email_configuration_mail_booster
      Parameters: Yes($phpmailer)
      Description: This function is used for checking test email.
      Created On: 15-06-2016 10:44
      Created By: Tech Banker Team
     */

    function email_configuration_mail_booster($phpmailer) {
        global $wpdb;
        $email_configuration_data = $wpdb->get_var
                (
                $wpdb->prepare
                        (
                        "SELECT meta_value FROM " . mail_booster_meta() . "
					WHERE meta_key = %s", "email_configuration"
                )
        );
        $email_configuration_data_array = unserialize($email_configuration_data);

        $phpmailer->Mailer = "mail";
        if (esc_attr($email_configuration_data_array["sender_name_configuration"]) == "override") {
            $phpmailer->FromName = stripcslashes(htmlspecialchars_decode($email_configuration_data_array["sender_name"], ENT_QUOTES));
        }
        if (esc_attr($email_configuration_data_array["from_email_configuration"]) == "override") {
            $phpmailer->From = esc_attr($email_configuration_data_array["sender_email"]);
        }
        if (esc_attr($email_configuration_data_array["reply_to"]) != "") {
            $phpmailer->clearReplyTos();
            $phpmailer->AddReplyTo(esc_attr($email_configuration_data_array["reply_to"]));
        }
        if (esc_attr($email_configuration_data_array["cc"]) != "") {
            $phpmailer->clearCCs();
            $cc_address_array = explode(",", esc_attr($email_configuration_data_array["cc"]));
            foreach ($cc_address_array as $cc_address) {
                $phpmailer->AddCc($cc_address);
            }
        }
        if (esc_attr($email_configuration_data_array["bcc"]) != "") {
            $phpmailer->clearBCCs();
            $bcc_address_array = explode(",", esc_attr($email_configuration_data_array["bcc"]));
            foreach ($bcc_address_array as $bcc_address) {
                $phpmailer->AddBcc($bcc_address);
            }
        }
        $phpmailer->Sender = esc_attr($email_configuration_data_array["email_address"]);
    }

    /*
      Function Name: admin_functions_for_mail_booster
      Parameters: No
      Description: This function is used for calling admin_init functions.
      Created On: 15-06-2016 10:44
      Created By: Tech Banker Team
     */

    function admin_functions_for_mail_booster() {
        install_script_for_mail_booster();
        helper_file_for_mail_booster();
    }

    /*
      Function Name: mailer_file_for_mail_booster
      Parameters: No
      Description: This function is used for including Mailer File.
      Created On: 30-06-2016 02:13
      Created By: Tech Banker Team
     */

    function mailer_file_for_mail_booster() {
        if (file_exists(MAIL_BOOSTER_DIR_PATH . "includes/mailer.php")) {
            include_once MAIL_BOOSTER_DIR_PATH . "includes/mailer.php";
        }
    }

    /*
      Function Name: user_functions_for_mail_booster
      Parameters: No
      Description: This function is used to call on init hook.
      Created On: 16-06-2016 11:08
      Created By: Tech Banker Team
     */

    function user_functions_for_mail_booster() {
        global $wpdb;
        $meta_values = $wpdb->get_results
                (
                $wpdb->prepare
                        (
                        "SELECT meta_value FROM " . mail_booster_meta() . "
					WHERE meta_key IN(%s,%s)", "settings", "email_configuration"
                )
        );

        $meta_data_array = array();
        foreach ($meta_values as $value) {
            $unserialize_data = unserialize($value->meta_value);
            array_push($meta_data_array, $unserialize_data);
        }
        mailer_file_for_mail_booster();
        if ($meta_data_array[0]["mailer_type"] == "php_mail_function") {
            add_action("phpmailer_init", "email_configuration_mail_booster");
        } else {
            apply_filters("wp_mail", "wp_mail");
        }
        oauth_handling_mail_booster();
    }

    /*
      Description: Override Mail Function here.
      Created On: 30-06-2016 02:13
      Created By: Tech Banker Team
     */

    mailer_file_for_mail_booster();
    mail_booster_auth_host::override_wp_mail_function();


    /*
      Function Name: deactivation_function_for_wp_mail_bank
      Parameters: No
      Description: This function is used for executing the code on deactivation.
      Created On: 21-04-2017 09:22
      Created by: Tech Banker Team
     */

    function deactivation_function_for_wp_mail_booster() {
        $type = get_option("wp-mail-booster-wizard-set-up");
        if ($type == "opt_in") {
            $plugin_info_wp_mail_booster = new plugin_info_wp_mail_booster();
            global $wp_version, $wpdb;
            $theme_details = array();

            if ($wp_version >= 3.4) {
                $active_theme = wp_get_theme();
                $theme_details["theme_name"] = strip_tags($active_theme->Name);
                $theme_details["theme_version"] = strip_tags($active_theme->Version);
                $theme_details["author_url"] = strip_tags($active_theme->{"Author URI"});
            }
            $plugin_stat_data = array();
            $plugin_stat_data["plugin_slug"] = "wp-mail-booster";
            $plugin_stat_data["type"] = "standard_edition";
            $plugin_stat_data["version_number"] = mail_booster_version_number;
            $plugin_stat_data["status"] = $type;
            $plugin_stat_data["event"] = "de-activate";
            $plugin_stat_data["domain_url"] = site_url();
            $plugin_stat_data["wp_language"] = defined("WPLANG") && WPLANG ? WPLANG : get_locale();
            $plugin_stat_data["email"] = get_option("admin_email");
            $plugin_stat_data["wp_version"] = $wp_version;
            $plugin_stat_data["php_version"] = esc_html(phpversion());
            $plugin_stat_data["mysql_version"] = $wpdb->db_version();
            $plugin_stat_data["max_input_vars"] = ini_get("max_input_vars");
            $plugin_stat_data["operating_system"] = PHP_OS . "  (" . PHP_INT_SIZE * 8 . ") BIT";
            $plugin_stat_data["php_memory_limit"] = ini_get("memory_limit") ? ini_get("memory_limit") : "N/A";
            $plugin_stat_data["extensions"] = get_loaded_extensions();
            $plugin_stat_data["plugins"] = $plugin_info_wp_mail_booster->get_plugin_info_wp_mail_booster();
            $plugin_stat_data["themes"] = $theme_details;
            $url = tech_banker_stats_url . "/wp-admin/admin-ajax.php";
            $response = wp_safe_remote_post($url, array
                (
                "method" => "POST",
                "timeout" => 45,
                "redirection" => 5,
                "httpversion" => "1.0",
                "blocking" => true,
                "headers" => array(),
                "body" => array("data" => serialize($plugin_stat_data), "site_id" => get_option("mail_booster_tech_banker_site_id") != "" ? get_option("mail_booster_tech_banker_site_id") : "", "action" => "plugin_analysis_data")
            ));
            if (!is_wp_error($response)) {
                $response["body"] != "" ? update_option("mail_booster_tech_banker_site_id", $response["body"]) : "";
            }
        }
    }

    /* hooks */

    /*
      add_action for admin_functions_for_mail_booster
      Description: This hook contains all admin_init functions.
      Created On: 15-06-2016 09:46
      Created By: Tech Banker Team
     */

    add_action("admin_init", "admin_functions_for_mail_booster");

    /*
      add_action for user_functions_for_mail_booster
      Description: This hook is used for calling the function of user functions.
      Created On: 16-06-2016 11:07
      Created By: Tech Banker Team
     */

    add_action("init", "user_functions_for_mail_booster");

    /*
      add_action for sidebar_menu_for_mail_booster
      Description: This hook is used for calling the function of sidebar menu.
      Created On: 15-06-2016 09:46
      Created By: Tech Banker Team
     */

    add_action("admin_menu", "sidebar_menu_for_mail_booster");

    /*
      add_action for sidebar_menu_for_mail_booster
      Description: This hook is used for calling the function of sidebar menu in multisite case.
      Created On: 19-10-2016 05:18
      Created By: Tech Banker Team
     */

    add_action("network_admin_menu", "sidebar_menu_for_mail_booster");

    /*
      add_action for topbar_menu_for_mail_booster
      Description: This hook is used for calling the function of topbar menu.
      Created On: 15-06-2016 09:46
      Created By: Tech Banker Team
     */

    add_action("admin_bar_menu", "topbar_menu_for_mail_booster", 100);


    /*
      add_action for plugin_load_textdomain_mail_booster
      Description: This hook is used for calling the function of languages.
      Created On: 16-06-2016 09:47
      Created By: Tech Banker Team
     */

    add_action("plugins_loaded", "plugin_load_textdomain_mail_booster");


    /*
      add_action hook for ajax_register_for_mail_booster
      Description: This hook is used to register ajax.
      Created On: 16-06-2016 12:00
      Created By: Tech Banker Team
     */
    add_action("wp_ajax_mail_booster_action", "ajax_register_for_mail_booster");
}

/*
  register_activation_hook for install_script_for_mail_booster
  Description: This hook is used for calling the function of install script.
  Created On: 15-06-2016 09:46
  Created By: Tech Banker Team
 */

register_activation_hook(__FILE__, "install_script_for_mail_booster");

/*
  add_action for install_script_for_mail_booster
  Description: This hook is used for calling the function of install script.
  Created On: 15-06-2016 09:46
  Created By: Tech Banker Team
 */

add_action("admin_init", "install_script_for_mail_booster");

/* deactivation_function_for_wp_mail_booster
  Description: This hook is used to sets the deactivation hook for a plugin.
  Created On: 21-04-2017 01:00
  Created by: Tech Banker Team
 */

register_deactivation_hook(__FILE__, "deactivation_function_for_wp_mail_booster");

/* add_filter create Go Pro link for Mail Booster
  Description: This hook is used for create link for premium Editions.
  Created On: 24-04-2017 16:41
  Created by: Tech Banker Team
 */
add_filter("plugin_action_links_" . plugin_basename(__FILE__), "mail_booster_action_links");

/*
  add_filter for mail_booster_settings_link
  Description: This hook is used for calling the function of settings link.
  Created On: 09-08-2016 02:51
  Created By: Tech Banker Team
 */

add_filter("plugin_action_links_" . plugin_basename(__FILE__), "mail_booster_settings_link", 10, 2);

/*
  Function Name: plugin_activate_mail_booster
  Description: This function is used to add option.
  Parameters: No
  Created On: 27-04-2017 17:01
  Created By: Tech Banker Team
 */

function plugin_activate_mail_booster() {
    add_option("mail_booster_do_activation_redirect", true);
}

/*
  Function Name: mail_booster_redirect
  Description: This function is used to redirect page.
  Parameters: No
  Created On: 27-04-2017 17:04
  Created By: Tech Banker Team
 */

function mail_booster_redirect() {
    if (get_option("mail_booster_do_activation_redirect", false)) {
        delete_option("mail_booster_do_activation_redirect");
        wp_redirect(admin_url("admin.php?page=mail_booster_email_configuration"));
        exit;
    }
}

/*
  register_activation_hook
  Description: This hook is used for calling the function plugin_activate_mail_booster
  Created On: 27-04-2017 17:08
  Created By: Tech Banker Team
 */

register_activation_hook(__FILE__, "plugin_activate_mail_booster");

/*
  add_action for mail_booster_redirect
  Description: This hook is used for calling the function mail_booster_redirect
  Created On: 27-04-2017 17:10
  Created By: Tech Banker Team
 */

add_action("admin_init", "mail_booster_redirect");
