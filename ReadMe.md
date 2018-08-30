# WordPress Plugin: Rate And Comment

Adds a simple like/dislike rating at the end of a post.
You can also define a tweet, so visitors can use Twitter to comment your post.

![Screenshot](https://raw.githubusercontent.com/lgkonline/rate-and-comment/master/screenshot.png)

## How does it work?

The plugin will create a small table in the WordPress database, where likes, dislikes and tweet IDs will be stored.
The plugin does not work with personal data. It does only store the like/dislike counts.

To make sure, a user can not vote multiple times, the plugin uses `localStorage` (a cookie). This is client-side, so this will only be saved on the user's device. Of course, a user could easily manipulate this storage, but I don't think that this very critical.