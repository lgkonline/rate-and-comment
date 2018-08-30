# WordPress Plugin: Rate And Comment

Adds a simple like/dislike rating at the end of a post.
You can also define a tweet, so visitors can use Twitter to comment your post.

![Screenshot](https://raw.githubusercontent.com/lgkonline/rate-and-comment/master/screenshot.png)

# [⬇️ Get the latest release](https://github.com/lgkonline/rate-and-comment/releases/latest)

## How does it work?

The plugin will create a small table in the WordPress database, where likes, dislikes and tweet IDs will be stored.
The plugin does not work with personal data. It does only store the like/dislike counts.

To make sure, a user can not vote multiple times, the plugin uses `localStorage` (a cookie). This is client-side, so this will only be saved on the user's device. Of course, a user could easily manipulate this storage, but I don't think that this very critical.


## Install

Just download the ZIP file from the release section, install it with the WordPress Dashboard and activate it.

If you want to use the "comment via Twitter" feature, just set the tweet ID on the edit page of a post. The "Comment via Twitter" link will only be visible when you set a tweet ID.


## Customize

You can customize this plugin by setting custom CSS and define how the buttons should look like.
Go to **Settings** > **Discussion**, there you'll find the **Rate And Comment** section.

<img alt="LGK Logo" src="https://lib.lgkonline.com/favicon.png" width="80px">
