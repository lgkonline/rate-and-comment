<?php

function rac_box_content() {
    $post_id = get_the_ID();
    $rating = rac_query_rating($post_id);

    ?>
        <div>👍 <?php echo $rating->rac_like;?> likes &nbsp; 👎 <?php echo $rating->rac_dislike;?> dislikes</div><br />

        <label>
            Connected tweet (ID)
            <input name="rac_tweet_id" type="text" value="<?php echo $rating->tweet_id;?>" placeholder="e.g. '1033065080831791106'" style="width:100%">
        </label>
    <?php
}

function rac_editor() {
    add_meta_box(
        "rac_meta_box",
        "Rate And Comment",
        "rac_box_content",
        "post"
    );
}
add_action("add_meta_boxes", "rac_editor");


function rac_save_post_data($post_id) {
    if (array_key_exists("rac_tweet_id", $_POST)) {
        global $wpdb;
        global $rac_table_name;

        $wpdb->update(
            $rac_table_name, 
            array("tweet_id" => $_POST["rac_tweet_id"]),
            array("post_id" => $post_id)
        );
    }
}
add_action("save_post", "rac_save_post_data");