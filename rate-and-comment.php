<?php
/*
Plugin Name: Rate And Comment
*/

global $rac_db_version;
$rac_db_version = "1.0";

function rac_install() {
    global $wpdb;
    global $rac_db_version;

    $table_name = $wpdb->prefix . "rate_and_comment";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        likes mediumint(9) DEFAULT '0' NOT NULL,
        dislikes mediumint(9) DEFAULT '0' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . "wp-admin/includes/upgrade.php" );
    dbDelta( $sql );

    add_option( "rac_db_version", $rac_db_version );
}
register_activation_hook(__FILE__, "rac_install");

function rac_install_data() {
    global $wpdb;

    $table_name = $wpdb->prefix . "rate_and_comment";

    $wpdb->insert( 
        $table_name, 
        array( 
            "post_id" => 1,
            "likes" => 1
        ) 
    );
}
register_activation_hook(__FILE__, "rac_install_data");

function rac_content($content) {
    if (is_single()) {
        ?>
        <?php echo $content; ?>

        <button id="rac-like">👍</button>
        <script>
            jQuery(document).ready(function($) {
                var racLikeBtn = document.getElementById("rac-like");
                    racLikeBtn.addEventListener("click", function() {
                        var data = {
                        "action": "rac_action",
                        "whatever": 1234,
                        "identifier": <?php the_ID(); ?>
                    };

                    jQuery.post("<?php echo admin_url("admin-ajax.php"); ?>", data, function(response) {
                        alert("Got this from the server: " + response);
                    });
                });
            });
        </script>
        <?php
    }
    else {
        return $content;
    }
}
add_action("the_content", "rac_content");

function rac_action() {
    global $wpdb;

    $whatever = intval( $_POST["whatever"] );
    $whatever += 10;

    echo $whatever;
    wp_die();
}
add_action( "wp_ajax_rac_action", "rac_action" );

function rac_get_rating() {
    // TO DO: Ajax result for one post
}