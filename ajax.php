<?php

function rac_query_rating($post_id) {
    global $wpdb;
    global $rac_table_name;

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
    $post_id = $_POST["post_id"];
    echo json_encode(rac_query_rating($post_id));
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
    $row = rac_query_rating($post_id);

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