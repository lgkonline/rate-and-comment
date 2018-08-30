<?php
/*
Plugin Name:    Rate And Comment
Plugin URI:     https://github.com/lgkonline/rate-and-comment
Description:    WordPress plugin for a simple like/dislike rating
Version:        1.0.0
Author:         Lars G. Kliesing (LGK)
Author URI:     https://lgk.io
License:        MIT
License URI:    https://raw.githubusercontent.com/lgkonline/rate-and-comment/master/LICENSE
*/

global $rac_db_version;
$rac_db_version = "1.0.0";

global $wpdb;
global $rac_table_name;
$rac_table_name = $wpdb->prefix . "rate_and_comment";

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