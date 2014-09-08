=== Simple Comment Editing ===
Contributors: ronalfy
Tags: ajax, comments,edit comments, edit, comment, admin
Requires at least: 3.5
Tested up to: 4.0
Stable tag: 1.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Simple Comment Editing for your website.

== Description ==

Simple Comment Editing gives anonymous users the ability to edit and/or delete their comments for a period of time.

Simple Comment Editing is a stripped down version of <a href="http://wordpress.org/plugins/wp-ajax-edit-comments/">Ajax Edit Comments</a>.

The biggest differences:
<ol>
<li>Only anonymous users (and logged in users who don't have permission to edit comments) can edit their comments for a period of time (the default is 5 minutes).</li>
<li>There are no styles included with this plugin.  For most themes, the appearance is acceptable.  For advanced customization, see the "Other Notes" section.</li>
<li>There are no options.  Some defaults can be overwritten using filters.</li>
</ol>

<h3>Spread the Word</h3>
If you like this plugin, please help spread the word.  Rate the plugin.  Write about the plugin.  Something :)

<h3>Translations</h3>
<ul>
<li>Arabic - Thanks Soufiane Sabiri.</li>
<li>Czech - Thanks <a href="http://blog.doprofilu.cz/">Petr Baloun</a>.</li>
<li>Dutch (Netherlands) - Thanks <a href="https://github.com/senlin">Senlin</a></li>
<li>French (France) - Thanks <a href="http://wordpress.org/support/profile/colin101">colin101</a>.</li>
<li>German - Thanks Wilfried Kahrs.</li>
<li>Norwegian Bokmål - Thanks <a href="http://www.metronet.no/menneskene/jan-hagen/">Jan Hagen</a>.</li>
<li>Norwegian Nynorsk - Thanks <a href="http://www.metronet.no/menneskene/jan-hagen/">Jan Hagen</a>.</li>
<li>Persian - Thanks <a href="http://www.wordpress98.com/">Ali HajiMohamadi</a>.</li>
<li>Portuguese (European) - Thanks <a href="https://twitter.com/porreirinha">Marco Pereirinha</a>.</li>
<li>Romanian (with diacritics) - Thanks Vasile Ruscior.</li>
<li>Serbian - Thanks <a href="https://twitter.com/lanche86">Milan Ivanovic</a>.</li>
<li>Swedish - Thanks Håkan Persson.</li>
</ul>

If you would like to contribute a translation, please leave a support request with a link to your translation  or <a href="http://www.ronalfy.com/contact/">get in touch</a>.  If you would like to update an existing translation, please visit <a href="https://poeditor.com/join/project?hash=b65f6d06a1d423e3d4713a9f4a304d5c">POEditor.com and request access</a>.

You are welcome to help us out and <a href="https://github.com/ronalfy/simple-comment-editing">contribute on GitHub</a>.

== Installation ==

1. Just unzip and upload the "simple-comment-editor" folder to your '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= Why doesn't this plugin come with any styles? =
It's impossible to style an inline comment editor for every theme.  We've included basic HTML markup that is easily stylable to fit your theme.
= Where are the options? =
No options :) - Just simple comment editing.

= How do you adjust the comment time? =
Place and edit the following into your theme's `functions.php` file:
`
//Simple Comment Editing
add_filter( 'sce_comment_time', 'edit_sce_comment_time' );
function edit_sce_comment_time( $time_in_minutes ) {
	return 60;
}
`


= How do you change the loading Image? =
`
//Simple Comment Editing
add_filter( 'sce_loading_img', 'edit_sce_loading_img' );
function edit_sce_loading_img( $default_url ) {
	return 'http://domain.com/new_loading_image.gif';
}
`

= How do you disable the delete comment functionality? =
`
//Simple Comment Editing
add_filter( 'sce_allow_delete', '__return_false' );
`

= Do you work with other plugins? =
Yes, the plugin will work with WP-Ajaxify-Comments (http://wordpress.org/plugins/wp-ajaxify-comments/)

Simply enable WP-Ajaxify-Comments, get it working per their settings, and add the Simple Comment Editing callback in the plugin's options.

The callback is:
`
SCE_comments_updated();
`

= I want to style the editing interface.  Where do I start? =
See "Other Notes".

= What Browsers Have You Tested This In? =
<ul>
<li>IE 6-10</li>
<li>Latest versions of Chrome, Firefox, and Safari</li>
<li>iOS Safari</li>
</ul>

= What Themes Have You Tested This In? =
<ul>
<li>Twenty Ten</li>
<li>Twenty Eleven</li>
<li>Twenty Twelve</li>
<li>Twenty Thirteen</li>
<li>Genesis</li>
<li>Genesis Mindstream</li>
</ul>


== Screenshots ==

1. Edit button and timer.
2. Textarea and Save/Cancel buttons.

== Changelog ==

= 1.2.2 =
* Released 2014-09-02
* Added Romanian language
* Added French language
* Added Dutch language
* Added better support for cached pages
* Fixed a bug where cached pages showed other users they could edit a comment, but in reality, they could not (saving would have failed, so this is not a severe security problem, although upgrading is highly recommended).

= 1.2.1 =
* Released 2014-08-27
* Added Arabic and Czech languages
* Ensuring WordPress 4.0 compatibility

= 1.2.0 =
* Released 2014-05-13
* Added Swedish translation
* Added better support for internationalization
* Removed barrier for admins/editors/authors to edit comments

= 1.1.2 =
* Released 2014-04-14
* Added support for WP-Ajaxify-Comments

= 1.1.1 =
* Released 2014-02-06
* Fixed an error where users were erroneously being told their comment was marked as spam

= 1.1.0 =
* Released 2014-02-05
* Added JavaScript textarea save states when hitting the cancel button
* Allow commenters to delete their comments when they leave an empty comment

= 1.0.7 =
* Released 2013-09-15
* Added Persian translation file

= 1.0.6 =
* Released 2013-09-12
* Added Serbian translation file

= 1.0.5 =
* Released 2013-09-12
* Added Portuguese translation file

= 1.0.4 =
* Released 2013-09-06
* Added German translation file

= 1.0.3 =
* Released 2013-08-23
* Fixed slashes being removed in the plugin

= 1.0.2 =
* Released 2013-08-05
* Fixed an internationalization bug and added Norwegian translations.

= 1.0.1 =
* Released 2013-08-05
* Improved script loading performance

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.2.2 =
Added Romanian and French languages.  Fixed a bug where cached pages showed other users they could edit a comment, but in reality, they could not (saving would have failed, so this is not a severe security problem, although upgrading is highly recommended).

= 1.2.1 =
Added Arabic and Czech languages.  Ensuring WordPress 4.0 compatibility.

= 1.2.0 =
Added Swedish translation.  Added better support for internationalization.  Removed barrier for admins/editors/authors to edit comments.

= 1.1.2 =
Added support for WP-Ajaxify-Comments

= 1.1.1 =
Fixed an error where users were erroneously being told their comment was marked as spam

= 1.1.0 =
Added JavaScript textarea save states when hitting the cancel button.  Allow commenters to delete their comments when they leave an empty comment.

= 1.0.7 =
Added Persian translation file

= 1.0.6 =
Added Serbian translation file

= 1.0.5 =
Added Portuguese translation file

= 1.0.4 =
Added German translation file

= 1.0.3 =
Fixed slashes being removed in the plugin

= 1.0.2 =
Fixed an internationalization bug and added Norwegian translations

= 1.0.1 =
Improved script loading performance

= 1.0 =
Initial Release

== Styling ==
The plugin doesn't come with any styles.  We leave it up to you to style the interface.  It doesn't look horribly ugly on most themes, but we leave the advanced customization up to you.

== Styling the Edit Interface ==
The overall editing interface has been wrapped in a `div` with class `sce-edit-comment`.

`
.sce-edit-comment { /* styles here */ }
`

== Styling the Edit Button ==
The edit button and timer have been wrapped in a `div` with class `sce-edit-button`.

`
.sce-edit-button { /* styles here */ }
.sce-edit-button a { /* styles here */ }
.sce-edit-button .sce-timer { /* styles here */ }
`

== Styling the Loading Icon ==
The loading icon has been wrapped in a `div` with class `sce-loading`.
`
.sce-loading { /* styles here */ }
.sce-loading img { /* styles here */ }
`

== Styling the Textarea ==
The textarea interface has been wrapped in a `div` with class `sce-textarea`.

The actual `textarea` has been wrapped in a `div` with class `sce-comment-textarea`.
The save/cancel buttons have been wrapped in a `div` with class `sce-comment-edit-buttons`.

`
.sce-textarea { /* styles here */ }
.sce-textarea .sce-comment-textarea textarea { /* styles here */ }
.sce-comment-edit-buttons { /* styles here */ }
.sce-comment-edit-buttons .sce-comment-save { /* styles here */ }
.sce-comment-edit-buttons .sce-comment-cancel { /* styles here */ }
`

== Testing the Styles ==
Since most of the interface is hidden, it's a little hard to style.  Just place this into your stylesheet, and remove when you're done.
`
/* todo - remove me when done styling */
.sce-edit-button,
.sce-loading,
.sce-textarea {
	display: block !important;
}
`
Have fun leaving lots of test comments :) - Recommended is to use the filter (in the FAQ section) to temporarily increase the comment editing time.  Make sure you leave the test comments when you're not logged in.

