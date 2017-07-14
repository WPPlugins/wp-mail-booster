<?php
/**
 * This file is used for managing data in database.
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

        function get_mail_booster_details_unserialize($email_data_manage, $mb_date1, $mb_date2) {
            $email_details = array();
            foreach ($email_data_manage as $raw_row) {
                $unserialize_data = unserialize($raw_row->meta_value);
                $unserialize_data["id"] = $raw_row->id;
                $unserialize_data["meta_id"] = $raw_row->meta_id;
                if ($unserialize_data["timestamp"] >= $mb_date1 && $unserialize_data["timestamp"] <= $mb_date2) {
                    array_push($email_details, $unserialize_data);
                }
            }
            return $email_details;
        }

        if (isset($_REQUEST["param"])) {
            $obj_dbHelper_mail_booster = new dbHelper_mail_booster();
            switch (esc_attr($_REQUEST["param"])) {
                case "wizard_wp_mail_booster":
                    if (wp_verify_nonce(isset($_REQUEST["_wp_nonce"]) ? esc_attr($_REQUEST["_wp_nonce"]) : "", "mail_booster_check_status")) {
                        $type = isset($_REQUEST["type"]) ? esc_attr($_REQUEST["type"]) : "";
                        update_option("wp-mail-booster-wizard-set-up", $type);
                        if ($type == "opt_in") {
                            $plugin_info_wp_mail_booster = new plugin_info_wp_mail_booster();
                            global $wp_version;
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
                            $plugin_stat_data["event"] = "activate";
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
                    break;


                case "mail_booster_set_hostname_port_module":
                    if (wp_verify_nonce(isset($_REQUEST["_wp_nonce"]) ? esc_attr($_REQUEST["_wp_nonce"]) : "", "mail_booster_set_hostname_port")) {
                        $smtp_user = isset($_REQUEST["smtp_user"]) ? esc_attr($_REQUEST["smtp_user"]) : "";
                        $hostname = substr(strrchr($smtp_user, "@"), 1);
                        $obj_mail_booster_discover_host = new mail_booster_discover_host();
                        $hostname_to_set = $obj_mail_booster_discover_host->get_smtp_from_email($hostname);
                        echo $hostname_to_set;
                    }
                    break;

                case "mail_booster_test_email_configuration_module":
                    if (wp_verify_nonce(isset($_REQUEST["_wp_nonce"]) ? esc_attr($_REQUEST["_wp_nonce"]) : "", "mail_booster_test_email_configuration")) {
                        parse_str(isset($_REQUEST["data"]) ? base64_decode($_REQUEST["data"]) : "", $form_data);
                        global $phpmailer;
                        $logs = array();
                        if (!is_object($phpmailer) || !is_a($phpmailer, "PHPMailer")) {
                            if (file_exists(ABSPATH . WPINC . "/class-phpmailer.php")) {
                                require_once ABSPATH . WPINC . "/class-phpmailer.php";
                            }

                            if (file_exists(ABSPATH . WPINC . "/class-smtp.php")) {
                                require_once ABSPATH . WPINC . "/class-smtp.php";
                            }

                            $phpmailer = new PHPMailer(true);
                        }
                        $phpmailer->SMTPDebug = true;

                        $to = esc_attr($form_data["ux_txt_email"]);
                        $subject = stripcslashes(htmlspecialchars_decode($form_data["ux_txt_subject"], ENT_QUOTES));
                        $message = htmlspecialchars_decode(!empty($form_data["ux_email_configuration_text_area"]) ? esc_attr($form_data["ux_email_configuration_text_area"]) : "This is a demo Test Email for Email Setup - Mail Booster");
                        $headers = "Content-Type: text/html; charset= utf-8" . "\r\n";
                        $result = wp_mail($to, $subject, $message, $headers);
                        $mb_email_configuration_data = $wpdb->get_row
                                (
                                $wpdb->prepare
                                        (
                                        "SELECT meta_value FROM " . mail_booster_meta() .
                                        " WHERE meta_key = %s", "email_configuration"
                                )
                        );
                        $unserialized_email_configuration_data = unserialize($mb_email_configuration_data->meta_value);

                        $settings_data = $wpdb->get_var
                                (
                                $wpdb->prepare
                                        (
                                        "SELECT meta_value FROM " . mail_booster_meta() .
                                        " WHERE meta_key=%s", "settings"
                                )
                        );
                        $settings_data_array = unserialize($settings_data);
                        $debugging_output = "";

                        if (esc_attr($unserialized_email_configuration_data["mailer_type"]) == "smtp") {
                            $mail_booster_mail_status = get_option("mail_booster_mail_status");
                            if (esc_attr($settings_data_array["debug_mode"]) == "enable") {
                                $debugging_output .= $mb_email_configuration_send_test_email_textarea . "\n";
                                $debugging_output .= $mb_test_email_sending_test_email . " " . $to . "\n";
                                $debugging_output .= $mb_test_email_status . " : ";
                                $debugging_output .= get_option("mail_booster_is_mail_sent") == "Sent" ? $mb_status_sent : $mb_status_not_sent;
                                $debugging_output .= "\n----------------------------------------------------------------------------------------\n";
                                $debugging_output .= $mb_email_logs_debugging_output . " :\n";
                                $debugging_output .= "----------------------------------------------------------------------------------------\n";
                            }
                            $debugging_output .= $mail_booster_mail_status;
                            echo $debugging_output;
                        } else {
                            $to_address = $phpmailer->getToAddresses();
                            $cc_address_array = $phpmailer->getCcAddresses();

                            $cc_addresses_data = array();
                            $bcc_addresses_data = array();

                            $bcc_address_array = $phpmailer->getBccAddresses();
                            $email_logs_data_array = array();
                            $email_logs_data_array["email_to"] = $to_address[0][0];
                            foreach ($cc_address_array as $cc_address) {
                                foreach ($cc_address as $address) {
                                    if ($address != "") {
                                        array_push($cc_addresses_data, $address);
                                    }
                                }
                            }
                            $all_cc_addresses = implode(",", $cc_addresses_data);

                            foreach ($bcc_address_array as $bcc_address) {
                                foreach ($bcc_address as $bcc_add) {
                                    if ($bcc_add != "") {
                                        array_push($bcc_addresses_data, $bcc_add);
                                    }
                                }
                            }

                            $all_bcc_addresses = implode(",", $bcc_addresses_data);
                            if ($settings_data_array["monitor_email_logs"] == "enable") {
                                $email_logs_data_array["sender_name"] = $unserialized_email_configuration_data["sender_name"];
                                $email_logs_data_array["sender_email"] = $unserialized_email_configuration_data["sender_email"];
                                $email_logs_data_array["cc"] = $all_cc_addresses;
                                $email_logs_data_array["bcc"] = $all_bcc_addresses;
                                $email_logs_data_array["subject"] = $phpmailer->Subject;
                                $email_logs_data_array["content"] = $phpmailer->Body;
                                $email_logs_data_array["timestamp"] = MAIL_BOOSTER_LOCAL_TIME;

                                if ($result == "true" || $result == "1") {
                                    $email_logs_data_array["status"] = "Sent";
                                } else {
                                    $email_logs_data_array["status"] = "Not Sent";
                                }
                                $email_logs_id = $wpdb->get_var
                                        (
                                        $wpdb->prepare
                                                (
                                                "SELECT id FROM " . mail_booster() .
                                                " WHERE type = %s", "email_logs"
                                        )
                                );

                                $email_logs_data = array();
                                $email_logs_data["meta_id"] = $email_logs_id;
                                $email_logs_data["meta_key"] = "email_logs";
                                $email_logs_data["meta_value"] = serialize($email_logs_data_array);
                                $obj_dbHelper_mail_booster->insertCommand(mail_booster_meta(), $email_logs_data);
                            }
                            echo $result;
                        }
                    }
                    break;

                case "mail_booster_connectivity_test":
                    if (wp_verify_nonce(isset($_REQUEST["_wp_nonce"]) ? esc_attr($_REQUEST["_wp_nonce"]) : "", "mb_connectivity_test_nonce")) {
                        $host = isset($_REQUEST["smtp_host"]) ? esc_attr($_REQUEST["smtp_host"]) : "";
                        $ports = array(25, 587, 465, 2525, 4065, 25025);
                        $ports_result = array();
                        foreach ($ports as $port) {
                            $connection = @fsockopen($host, $port);
                            if (is_resource($connection)) {
                                $ports_result[$port] = "Open";
                                fclose($connection);
                            } else {
                                $ports_result[$port] = "Close";
                            }
                        }
                        foreach ($ports_result as $port_type => $connection) {
                            ?>
                            <tr>
                                <td>
                                    <?php echo $mb_smtp; ?>
                                </td>
                                <td>
                                    <?php echo $host . ":" . intval($port_type); ?>
                                </td>
                                <td>
                                    <span style='<?php echo $connection == "Close" ? "color:red" : ""; ?>'><?php echo $connection; ?></span>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    break;

                case "mail_booster_settings_module":
                    if (wp_verify_nonce(isset($_REQUEST["_wp_nonce"]) ? esc_attr($_REQUEST["_wp_nonce"]) : "", "mail_booster_settings")) {
                        parse_str(isset($_REQUEST["data"]) ? base64_decode($_REQUEST["data"]) : "", $settings_array);

                        $settings_data = array();
                        $settings_data["debug_mode"] = esc_attr($settings_array["ux_ddl_debug_mode"]);
                        $settings_data["remove_tables_at_uninstall"] = esc_attr($settings_array["ux_ddl_remove_tables"]);
                        $settings_data["monitor_email_logs"] = esc_attr($settings_array["ux_ddl_monitor_email_logs"]);

                        $where = array();
                        $settings_data_array = array();
                        $where["meta_key"] = "settings";
                        $settings_data_array["meta_value"] = serialize($settings_data);
                        $obj_dbHelper_mail_booster->updateCommand(mail_booster_meta(), $settings_data_array, $where);
                    }
                    break;

                case "mail_booster_email_configuration_settings_module":
                    if (wp_verify_nonce(isset($_REQUEST["_wp_nonce"]) ? esc_attr($_REQUEST["_wp_nonce"]) : "", "mail_booster_email_configuration_settings")) {
                        parse_str(isset($_REQUEST["data"]) ? base64_decode($_REQUEST["data"]) : "", $form_data);
                        $update_email_configuration_array = array();
                        $update_email_configuration_array["email_address"] = esc_attr($form_data["ux_txt_email_address"]);
                        $update_email_configuration_array["reply_to"] = "";
                        $update_email_configuration_array["cc"] = "";
                        $update_email_configuration_array["bcc"] = "";
                        $update_email_configuration_array["mailer_type"] = esc_attr($form_data["ux_ddl_type"]);
                        $update_email_configuration_array["sender_name_configuration"] = esc_attr($form_data["ux_ddl_from_name"]);
                        $update_email_configuration_array["sender_name"] = isset($form_data["ux_txt_mb_from_name"]) ? esc_html($form_data["ux_txt_mb_from_name"]) : "";
                        $update_email_configuration_array["from_email_configuration"] = esc_attr($form_data["ux_ddl_from_email"]);
                        $update_email_configuration_array["sender_email"] = isset($form_data["ux_txt_mb_from_email_configuration"]) ? esc_html($form_data["ux_txt_mb_from_email_configuration"]) : "";
                        $update_email_configuration_array["hostname"] = esc_html($form_data["ux_txt_host"]);
                        $update_email_configuration_array["port"] = intval($form_data["ux_txt_port"]);
                        $update_email_configuration_array["enc_type"] = esc_attr($form_data["ux_ddl_encryption"]);
                        $update_email_configuration_array["auth_type"] = esc_attr($form_data["ux_ddl_mb_authentication"]);
                        $update_email_configuration_array["client_id"] = esc_html(trim($form_data["ux_txt_client_id"]));
                        $update_email_configuration_array["client_secret"] = esc_html(trim($form_data["ux_txt_client_secret"]));
                        $update_email_configuration_array["username"] = esc_html($form_data["ux_txt_username"]);
                        $update_email_configuration_array["automatic_mail"] = isset($form_data["ux_chk_automatic_sent_mail"]) ? esc_html($form_data["ux_chk_automatic_sent_mail"]) : "";

                        if (preg_match('/^\**$/', $form_data["ux_txt_password"])) {
                            $email_configuration_data = $wpdb->get_var
                                    (
                                    $wpdb->prepare
                                            (
                                            "SELECT meta_value FROM " . mail_booster_meta() .
                                            " WHERE meta_key=%s", "email_configuration"
                                    )
                            );
                            $email_configuration_array = unserialize($email_configuration_data);
                            $update_email_configuration_array["password"] = esc_attr($email_configuration_array["password"]);
                        } else {
                            $update_email_configuration_array["password"] = base64_encode(esc_html($form_data["ux_txt_password"]));
                        }

                        $update_email_configuration_array["redirect_uri"] = esc_html($form_data["ux_txt_redirect_uri"]);

                        update_option("update_email_configuration", $update_email_configuration_array);

                        $mail_booster_auth_host = new mail_booster_auth_host($update_email_configuration_array);
                        if (!in_array($form_data["ux_txt_host"], $mail_booster_auth_host->oauth_domains) && $form_data["ux_ddl_mb_authentication"] == "oauth2") {
                            echo "100";
                            die();
                        }

                        if (esc_attr($update_email_configuration_array["auth_type"]) == "oauth2" && esc_attr($update_email_configuration_array["mailer_type"]) == "smtp") {
                            if ($update_email_configuration_array["hostname"] == "smtp.gmail.com") {
                                $mail_booster_auth_host->google_authentication();
                            } elseif (esc_attr($update_email_configuration_array["hostname"]) == "smtp.live.com" && esc_attr($update_email_configuration_array["mailer_type"]) == "smtp") {
                                $mail_booster_auth_host->microsoft_authentication();
                            } elseif (in_array($update_email_configuration_array["hostname"], $mail_booster_auth_host->yahoo_domains)) {
                                $mail_booster_auth_host->yahoo_authentication();
                            }
                        } else {
                            $update_email_configuration_data_array = array();
                            $where = array();
                            $where["meta_key"] = "email_configuration";
                            $update_email_configuration_data_array["meta_value"] = serialize($update_email_configuration_array);
                            $obj_dbHelper_mail_booster->updateCommand(mail_booster_meta(), $update_email_configuration_data_array, $where);
                        }
                    }
                    break;

                case "mail_booster_email_logs_date_module":
                    if (wp_verify_nonce(isset($_REQUEST["_wp_nonce"]) ? esc_attr($_REQUEST["_wp_nonce"]) : "", "mb_start_end_data_email_logs")) {
                        parse_str(isset($_REQUEST["data"]) ? base64_decode($_REQUEST["data"]) : "", $email_date_logs);
                        $start_date = esc_attr($email_date_logs["ux_txt_mb_start_date"]);
                        $end_date = esc_attr($email_date_logs["ux_txt_mb_end_date"]);
                        $mb_date1 = strtotime($start_date);
                        $mb_date2 = strtotime($end_date) + 86340;

                        $email_manage = $wpdb->get_results
                                (
                                $wpdb->prepare
                                        (
                                        "SELECT * FROM " . mail_booster_meta() . "
								WHERE meta_key = %s ORDER BY id DESC", "email_logs"
                                )
                        );
                        $email_details = get_mail_booster_details_unserialize($email_manage, $mb_date1, $mb_date2);
                        foreach ($email_details as $value) {
                            ?>
                            <tr>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="ux_chk_email_logs_<?php echo intval($value["id"]); ?>" id="ux_chk_email_logs_<?php echo intval($value["id"]); ?>" onclick="check_email_logs(<?php echo intval($value["id"]); ?>)" value="<?php echo intval($value["id"]); ?>">
                                </td>
                                <td id="ux_email_sent_to_<?php echo intval($value["id"]) ?>">
                                    <?php echo isset($value["email_to"]) ? esc_attr($value["email_to"]) : ""; ?>
                                </td>
                                <td id="ux_email_subject_<?php echo $intval($value["id"]) ?>">
                                    <?php echo isset($value["subject"]) != "" ? esc_attr($value["subject"]) : "N/A"; ?>
                                </td>
                                <td id="ux_email_date_time_<?php echo intval($value["id"]) ?>">
                                    <?php echo date("d M Y H:i A", doubleval($value["timestamp"])); ?>
                                </td>
                                <td id="ux_email_status_<?php echo intval($value["id"]) ?>">
                                    <?php echo $value["status"] == "Sent" ? $mb_status_sent : $mb_status_not_sent; ?>
                                </td>
                                <td class="custom-alternative">
                                    <a href="javascript:void(0);">
                                        <i class="icon-custom-reload tooltips" data-original-title="<?php echo $mb_resend; ?>" onclick="resent_email_logs(<?php echo intval($value["id"]) ?>)" data-placement="top"></i>
                                    </a> |
                                    <?php
                                    if (isset($value["debug_mode"])) {
                                        ?>
                                        <a href="admin.php?page=mail_booster_debugging_output&log_id=<?php echo intval($value['id']) ?>">
                                            <i class="icon-custom-doc tooltips" data-original-title="<?php echo $mb_email_logs_show_outputs; ?>" data-placement="top"></i>
                                        </a> |
                                        <?php
                                    }
                                    ?>
                                    <a href="admin.php?page=mail_booster_log_details&log_id=<?php echo intval($value['id']) ?>">
                                        <i class="icon-custom-share-alt tooltips" data-original-title="<?php echo $mb_email_logs_show_details; ?>" data-placement="top"></i>
                                    </a> |
                                    <a href="javascript:void(0);">
                                        <i class="icon-custom-trash tooltips" data-original-title="<?php echo $mb_email_logs_delete; ?>" onclick="delete_email_logs(<?php echo intval($value['id']) ?>)" data-placement="top"></i>
                                    </a>
                                </td>
                            </tr>
                            <script>
                                var oTable = jQuery("#ux_tbl_email_logs").dataTable
                                        ({
                                            "pagingType": "full_numbers",
                                            "language":
                                                    {
                                                        "emptyTable": "No data available in table",
                                                        "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                                                        "infoEmpty": "No entries found",
                                                        "infoFiltered": "(filtered1 from _MAX_ total entries)",
                                                        "lengthMenu": "Show _MENU_ entries",
                                                        "search": "Search:",
                                                        "zeroRecords": "No matching records found"
                                                    },
                                            "bSort": true,
                                            "pageLength": 10
                                        });
                            </script>
                            <?php
                        }
                    }
                    break;

                case "mail_booster_email_logs_bulk_delete_module":
                    if (wp_verify_nonce(isset($_REQUEST["_wp_nonce"]) ? esc_attr($_REQUEST["_wp_nonce"]) : "", "mb_email_logs_bulk_delete")) {
                        $encrypted_records = isset($_REQUEST["encrypted_records"]) ? json_decode(stripslashes($_REQUEST["encrypted_records"])) : "";
                        $email_logs_ids = implode(",", $encrypted_records);
                        $obj_dbHelper_mail_booster->bulk_deleteCommand(mail_booster_meta(), "id", $email_logs_ids);
                    }
                    break;

                case "mail_booster_email_logs_delete_module":
                    if (wp_verify_nonce(isset($_REQUEST["_wp_nonce"]) ? esc_attr($_REQUEST["_wp_nonce"]) : "", "mb_email_logs_delete")) {
                        $where_meta = array();
                        $where_meta["id"] = isset($_REQUEST["id"]) ? intval($_REQUEST["id"]) : "";
                        $obj_dbHelper_mail_booster->deleteCommand(mail_booster_meta(), $where_meta);
                    }
                    break;
            }
            die();
        }
    }
}