<?php
/*
Plugin Name:    Rate And Comment
Plugin URI:     https://github.com/lgkonline/rate-and-comment
Description:    WordPress plugin for a simple like/dislike rating
Version:        1.1.0
Author:         Lars G. Kliesing (LGK)
Author URI:     https://lgk.io
License:        MIT
License URI:    https://raw.githubusercontent.com/lgkonline/rate-and-comment/master/LICENSE
*/

global $rac_db_version;
$rac_db_version = "1.1.0";

global $wpdb;
global $rac_table_name;
$rac_table_name = $wpdb->prefix . "rate_and_comment";

global $rac_default_settings;
$rac_default_settings = array(
    "rac_like_icon" => "👍",
    "rac_dislike_icon" => "👎",
    "rac_comment_content" => "💬 Comment via Twitter"
);

require "ajax.php";
require "single-content.php";
require "comment-via-twitter.php";


function rac_install() {
    global $wpdb;
    global $rac_db_version;
    global $rac_table_name;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $rac_table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        rac_like mediumint(9) DEFAULT '0' NOT NULL,
        rac_dislike mediumint(9) DEFAULT '0' NOT NULL,
        tweet_id varchar(255),
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . "wp-admin/includes/upgrade.php" );
    dbDelta( $sql );

    add_option( "rac_db_version", $rac_db_version );
}
register_activation_hook(__FILE__, "rac_install");


function rac_register_settings() {
    global $rac_default_settings;

    // Custom CSS setting
    register_setting("discussion", "rac_styling", array(
        "type" => "string",
        "description" => "Custom styling for Rate And Comment."
    ));
    function rac_styling_settings_field_cb() {
        $setting = get_option("rac_styling");
        ?>
        <textarea name="rac_styling" style="width:100%" rows="7"><?php echo isset($setting) ? esc_attr($setting) : "" ?></textarea>
        <?php
    }
    add_settings_field("rac_styling_settings_field", "Custom CSS", "rac_styling_settings_field_cb", "discussion", "rac_settings_section");


    // Custom like icon setting
    register_setting("discussion", "rac_like_icon", array(
        "type" => "string",
        "description" => "Sets icon for the like button.",
        "default" => $rac_default_settings["rac_like_icon"]
    ));
    function rac_like_icon_settings_field_cb() {
        $setting = get_option("rac_like_icon");
        ?>
        <textarea name="rac_like_icon" style="width:100%" rows="4"><?php echo isset($setting) ? esc_attr($setting) : "" ?></textarea>
        <?php
    }
    add_settings_field("rac_like_icon_settings_field", "Like icon (HTML)", "rac_like_icon_settings_field_cb", "discussion", "rac_settings_section");


    // Custom dislike icon setting
    register_setting("discussion", "rac_dislike_icon", array(
        "type" => "string",
        "description" => "Sets icon for the dislike button.",
        "default" => $rac_default_settings["rac_dislike_icon"]
    ));
    function rac_dislike_icon_settings_field_cb() {
        $setting = get_option("rac_dislike_icon");
        ?>
        <textarea name="rac_dislike_icon" style="width:100%" rows="4"><?php echo isset($setting) ? esc_attr($setting) : "" ?></textarea>
        <?php
    }
    add_settings_field("rac_dislike_icon_settings_field", "Dislike icon (HTML)", "rac_dislike_icon_settings_field_cb", "discussion", "rac_settings_section");


    // Custom comment link content setting
    register_setting("discussion", "rac_comment_content", array(
        "type" => "string",
        "description" => "Sets icon for the dislike button.",
        "default" => $rac_default_settings["rac_comment_content"]
    ));
    function rac_comment_content_settings_field_cb() {
        $setting = get_option("rac_comment_content");
        ?>
        <textarea name="rac_comment_content" style="width:100%" rows="5"><?php echo isset($setting) ? esc_attr($setting) : "" ?></textarea>
        <?php
    }
    add_settings_field("rac_comment_content_settings_field", "Comment via Twitter link (HTML)", "rac_comment_content_settings_field_cb", "discussion", "rac_settings_section");


    function rac_settings_section_cb() {
        echo "<p>Here you can customize the Rate And Comment plugin.</p>";
    }
    add_settings_section("rac_settings_section", "Rate And Comment", "rac_settings_section_cb", "discussion");
}
add_action("admin_init", "rac_register_settings");