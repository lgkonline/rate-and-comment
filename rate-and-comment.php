<?php
/*
Plugin Name: Rate And Comment
*/

global $rac_db_version;
$rac_db_version = "1.0";

global $wpdb;
global $rac_table_name;
$rac_table_name = $wpdb->prefix . "rate_and_comment";

function rac_install() {
    global $wpdb;
    global $rac_db_version;
    global $rac_table_name;

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $rac_table_name (
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
    global $rac_table_name;

    $wpdb->insert( 
        $rac_table_name, 
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

        <button id="rac-like">👍 <span class="count"></span></button>
        <script>
            var ajaxurl = "<?php echo admin_url("admin-ajax.php"); ?>";
            var postid = <?php the_ID(); ?>;

            jQuery(document).ready(function($) {
                var racLikeBtn = document.getElementById("rac-like");

                function updateVotes(response) {
                    var rating = JSON.parse(response);
                    console.log(response);
                    console.log(rating);

                    racLikeBtn.querySelector(".count").innerText = rating.likes;
                }

                jQuery.post(
                    ajaxurl,
                    {
                        "action": "rac_get_rating",
                        "post_id": postid,
                    },
                    function(response) {
                        updateVotes(response);
                    }
                );

                racLikeBtn.addEventListener("click", function() {
                    jQuery.post(
                        ajaxurl, 
                        {
                            "action": "rac_upvote",
                            "post_id": postid
                        }, 
                        function(response) {
                            updateVotes(response);
                        }
                    );
                
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

function rac_upvote() {
    global $wpdb;
    global $rac_table_name;

    $post_id = $_POST["post_id"];

    $wpdb->query("UPDATE $rac_table_name SET likes = likes + 1 WHERE post_id = '$post_id'");

    rac_get_rating();
    wp_die();
}
add_action("wp_ajax_rac_upvote", "rac_upvote");
add_action("wp_ajax_nopriv_rac_upvote", "rac_upvote");

function rac_get_rating() {
    global $wpdb;
    global $rac_table_name;

    $post_id = $_POST["post_id"];

    $row = $wpdb->get_row("SELECT * FROM $rac_table_name WHERE post_id = '$post_id'");

    echo json_encode($row);
    wp_die();
}
add_action("wp_ajax_rac_get_rating", "rac_get_rating");
add_action("wp_ajax_nopriv_rac_get_rating", "rac_get_rating");