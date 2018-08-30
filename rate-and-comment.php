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
        rac_like mediumint(9) DEFAULT '0' NOT NULL,
        rac_dislike mediumint(9) DEFAULT '0' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . "wp-admin/includes/upgrade.php" );
    dbDelta( $sql );

    add_option( "rac_db_version", $rac_db_version );
}
register_activation_hook(__FILE__, "rac_install");


function rac_content($content) {
    if (is_single()) {
        
        ?>
        <?php echo $content; ?>

        <button class="rac-rate-btn" data-action="rac_like" id="rac-like-btn">👍 <span class="count"></span></button>
        <button class="rac-rate-btn" data-action="rac_dislike" id="rac-dislike-btn">👎 <span class="count"></span></button>
        <script>
            var ajaxurl = "<?php echo admin_url("admin-ajax.php"); ?>";
            var postid = <?php the_ID(); ?>;

            jQuery(document).ready(function($) {
                var racRating;

                var racRateBtns = document.getElementsByClassName("rac-rate-btn");
                var racLikeBtn = document.getElementById("rac-like-btn");
                var racDislikeBtn = document.getElementById("rac-dislike-btn");

                function initVoting() {
                    var defaultRacRating = { "rac_like": [], "rac_dislike": [] };
                    racRating = localStorage.getItem("rac-rating") ? JSON.parse(localStorage.getItem("rac-rating")) : defaultRacRating;
                }

                function updateRates(response) {
                    var rating = JSON.parse(response);

                    if (rating.local_rating) {
                        racRating = rating.local_rating;
                        localStorage.setItem("rac-rating", JSON.stringify(rating.local_rating));
                    }

                    racLikeBtn.querySelector(".count").innerText = rating.rac_like;
                    racDislikeBtn.querySelector(".count").innerText = rating.rac_dislike;
                }

                initVoting();

<?php /* Receives rating */ ?>
                jQuery.post(ajaxurl, {"action": "rac_get_rating", "post_id": postid, }, function(response) { updateRates(response); } );

<?php /* When click on like/dislike, make Ajax post request */ ?>
                for (var i = 0; i < racRateBtns.length; i++) {
                    (function(i) {
                        var racRateBtn = racRateBtns[i];
                        var action = racRateBtn.getAttribute("data-action");

                        racRateBtn.addEventListener("click", function() {
                            jQuery.post(ajaxurl, {
                                "action": action,
                                "post_id": postid,
                                "local_rating": JSON.stringify(racRating)
                            }, function(response) { updateRates(response); });
                        });
                    })(i);
                }
            });
        </script>
        <?php
    }
    else {
        return $content;
    }
}
add_action("the_content", "rac_content");


function rac_query_rating() {
    global $wpdb;
    global $rac_table_name;

    $post_id = $_POST["post_id"];

    $row = $wpdb->get_row("SELECT * FROM $rac_table_name WHERE post_id = '$post_id'");

    if (!isset($row)) {
        // row for this post is not created yet, create it now
        $wpdb->insert( 
            $rac_table_name, 
            array( 
                "post_id" => $post_id
            ) 
        );

        $row = $wpdb->get_row("SELECT * FROM $rac_table_name WHERE post_id = '$post_id'");
    }

    return $row;
}

function rac_get_rating() {
    echo json_encode(rac_query_rating());
    wp_die();
}
add_action("wp_ajax_rac_get_rating", "rac_get_rating");
add_action("wp_ajax_nopriv_rac_get_rating", "rac_get_rating");


// This function is for like and dislike
// $action is "rac_like" or "rac_dislike"
function rac_rate($action) {
    global $wpdb;
    global $rac_table_name;

    $post_id = $_POST["post_id"];
    $local_rating_json = $_POST["local_rating"];

    /**
     * Find out other action. This is necessary, if user already has rated. 
     * If e.g. user already has disliked and now likes, the dislike will be removed.
     * If user already disliked and now dislikes again, the dislike will also be removed.
     * The same for likes.
     */
    if ($action == "rac_like") {
        $other_action = "rac_dislike";
    }
    else {
        $other_action = "rac_like";
    }

    $local_rating = json_decode(stripslashes($local_rating_json));

    $did_use_this_action = in_array($post_id, $local_rating->{$action});
    $did_use_other_action = in_array($post_id, $local_rating->{$other_action});

    $op;
    if ($did_use_this_action) {
        $op = "-";
        $index = array_search($post_id, $local_rating->{$action});
        array_splice($local_rating->{$action}, $index, 1);
    }
    else {
        $op = "+";
        array_push($local_rating->{$action}, $post_id);
    }

    $sql = "UPDATE $rac_table_name SET ";
    $sql .= "$action = $action $op 1";

    if ($did_use_other_action) {
        $sql .= ", $other_action = $other_action - 1";

        $index = array_search($post_id, $local_rating->{$other_action});
        array_splice($local_rating->{$other_action}, $index, 1);
    }

    $sql .= " WHERE post_id = '$post_id'";

    $wpdb->query($sql);

    // For the result, get the new state from DB, so on client-side the new result can be displayed.
    $row = rac_query_rating();

    // Also update local_rating, which is what will be stored client-side via localStorage (cookie).
    $row->local_rating = $local_rating;

    echo json_encode($row);
    wp_die();
}

function rac_like() {
    rac_rate("rac_like");
}
add_action("wp_ajax_rac_like", "rac_like");
add_action("wp_ajax_nopriv_rac_like", "rac_like");


function rac_dislike() {
    rac_rate("rac_dislike");
}
add_action("wp_ajax_rac_dislike", "rac_dislike");
add_action("wp_ajax_nopriv_rac_dislike", "rac_dislike");