=== Picasa Photo Embed ===
Contributors: divinenephron
Tags: picasa, embed, photo
Requires at least: 2.9
Tested up to: 2.9
Stable tag: 1.5.0

A plugin that allows you to embed picasa photos into your posts with the Wordpress 2.9 embedding feature.

== Description ==

A plugin that allows you to embed picasa photos into your posts with the Wordpress 2.9 embedding feature.
Include the url of the photo on a line of its own or between [embed] tags and the photo will be embedded.

== Installation ==

1. Upload `picasa-picture-embed` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Include the url of the photo on a line of its own or between `[embed]` tags and the photo will be embedded.

== Frequently Asked Questions ==

= What can I embed? =

You can embed individual photos -- embedding an album or a random selection of user's photos isn't supported: there are a lot of other plugins that can do that.

= How do I embed them? =

Include the url of the photo on a line of its own or between [embed] tags and the photo will be embedded, as described in the Codex [Embeds page](http://codex.wordpress.org/Embeds).

= Why does it still show an old version of my photo? =

The plugin caches the photo url so that it does not have to query Google every time a visitor views the page. This cache is cleared every time the post is saved, so if you change the photo on Picasa, you may have to save the post again to see these changes in the post.

= Is the plugin broken? =

1. Are you using Wordpress > 2.9?
2. Have you enabled auto embeds in the `Settings -> Media` page?
3. Is the image you're trying to embed visible to the public?
4. Have you used the right url -- only urls in the form `http://picasaweb.google.com/{user}/{album}#{photo}` are supported (e.g. `http://picasaweb.google.com/linmep/PicAutumn09#5410318127990876210`). Search URLs (e.g. `http://picasaweb.google.com/lh/view?q=leaf&psc=G&filter=1#5325823428363864770`) don't work.

If you can answer "yes" to the above the plugin may indeed be broken.

== Screenshots ==

1. A picasa photo page -- the url you need is shown in the address bar.
2. Writing a post in visual mode with two urls on their own line.
3. The photo has been embedded -- the incorrectly formatted url has remained however.

== Changelog ==

= 1.0 =
* Created the plugin.
* Accepts urls in the form `http://picasaweb.google.com/{user}/{album}#{photo}`.
* Caches urls to avoid querying google on every page visit.
* The cache is invalidated every time the post is saved.
* Automatically chooses the largest necessary size for the photo based on the `width` and `height` attributes.

= 1.1 =
* Added support for urls with queries (e.g. `http://picasaweb.google.com/missyann63/PicAutumn09?feat=featured#5409726203440714818`). Note the `?feat=featured`.

= 1.2 =
* Removed a lot of the unused Zend framework.
* Fixes some random bugs.

= 1.4 =
* Added the "unused" parts of the Zend framework back. They were being used.

== Upgrade Notice ==

= 1.0 =
This is the first version, having it is definite plus.

= 1.3 =
The plugin has been made a lot smaller and sever bugs have been fixed.

= 1.4 =
The missing parts of the Zend framework have been added back in. The plugin consequently works.
