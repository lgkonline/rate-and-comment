<?php

function rac_get_setting($option) {
    global $rac_default_settings;
    $optionValue = get_option($option);

    if ($optionValue) {
        return esc_attr($optionValue);
    }
    else {
        return $rac_default_settings[$option];
    }
}

function rac_content($content) {
    global $rac_default_settings;

    if (is_single()) {
        $post_id = get_the_ID();
        $rating = rac_query_rating($post_id);
        
        $rac_styling = get_option("rac_styling");
        $rac_like_icon = rac_get_setting("rac_like_icon");
        $rac_dislike_icon = rac_get_setting("rac_dislike_icon");
        $rac_comment_content = rac_get_setting("rac_comment_content");
        
        ?>
        <?php echo $content; ?>

        <?php if (isset($rac_styling)) : ?>
        <style type="text/css"><?php echo esc_attr($rac_styling); ?></style>
        <?php endif; ?>

        <div class="rac-content">
            <button class="rac-rate-btn" data-action="rac_like" id="rac-like-btn"><?php echo $rac_like_icon; ?> <span class="count"><?php echo $rating->rac_like;?></span></button>
            <button class="rac-rate-btn" data-action="rac_dislike" id="rac-dislike-btn"><?php echo $rac_dislike_icon; ?> <span class="count"><?php echo $rating->rac_dislike;?></span></button>

            <?php if (isset($rating->tweet_id) && $rating->tweet_id != "") : ?>
            <a class="rac-comment-link" href="https://twitter.com/intent/tweet?in_reply_to=<?php echo $rating->tweet_id;?>" target="_blank"><?php echo $rac_comment_content; ?></a>
            <?php endif; ?>
        </div>

        <script>
            jQuery(document).ready(function($) {
                var racRateBtns = document.getElementsByClassName("rac-rate-btn");
                var racRating = localStorage.getItem("rac-rating") ? JSON.parse(localStorage.getItem("rac-rating")) : { "rac_like": [], "rac_dislike": [] };

<?php /* When click on like/dislike, make Ajax post request */ ?>
                for (var i = 0; i < racRateBtns.length; i++) {
                    (function(i) {
                        var racRateBtn = racRateBtns[i];
                        var action = racRateBtn.getAttribute("data-action");

                        racRateBtn.addEventListener("click", function() {
                            jQuery.post("<?php echo admin_url("admin-ajax.php"); ?>", {
                                "action": action,
                                "post_id": <?php the_ID(); ?>,
                                "local_rating": JSON.stringify(racRating)
                            }, function(response) { 
                                var rating = JSON.parse(response);

                                racRating = rating.local_rating;
                                localStorage.setItem("rac-rating", JSON.stringify(rating.local_rating));

                                document.querySelector("#rac-like-btn .count").innerText = rating.rac_like;
                                document.querySelector("#rac-dislike-btn .count").innerText = rating.rac_dislike;
                             });
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