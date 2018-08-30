<?php

function rac_content($content) {
    if (is_single()) {
        $post_id = get_the_ID();
        $rating = rac_query_rating($post_id);
        
        ?>
        <?php echo $content; ?>

        <button class="rac-rate-btn" data-action="rac_like" id="rac-like-btn">👍 <span class="count"><?php echo $rating->rac_like;?></span></button>
        <button class="rac-rate-btn" data-action="rac_dislike" id="rac-dislike-btn">👎 <span class="count"><?php echo $rating->rac_dislike;?></span></button>

        <?php if (isset($rating->tweet_id) && $rating->tweet_id != "") : ?>
        <a href="https://twitter.com/intent/tweet?in_reply_to=<?php echo $rating->tweet_id;?>" target="_blank">💬 Comment via Twitter</a>
        <?php endif; ?>

        <script>
            var ajaxurl = "<?php echo admin_url("admin-ajax.php"); ?>";
            var postid = <?php the_ID(); ?>;

            jQuery(document).ready(function($) {
                var racRating;

                var racRateBtns = document.getElementsByClassName("rac-rate-btn");
                var racLikeBtn = document.getElementById("rac-like-btn");
                var racDislikeBtn = document.getElementById("rac-dislike-btn");

                var defaultRacRating = { "rac_like": [], "rac_dislike": [] };
                racRating = localStorage.getItem("rac-rating") ? JSON.parse(localStorage.getItem("rac-rating")) : defaultRacRating;

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
                            }, function(response) { 
                                var rating = JSON.parse(response);

                                racRating = rating.local_rating;
                                localStorage.setItem("rac-rating", JSON.stringify(rating.local_rating));

                                racLikeBtn.querySelector(".count").innerText = rating.rac_like;
                                racDislikeBtn.querySelector(".count").innerText = rating.rac_dislike;
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