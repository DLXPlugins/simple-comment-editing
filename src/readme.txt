=== Simple Comment Editing ===
Contributors: ronalfy
Tags: ajax, comments,edit comments, edit, comment,
Requires at least: 5.0
Tested up to: 5.2
Stable tag: 2.3.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://mediaron.com/contribute/

Simple Comment Editing for your website.

== Description ==

Simple Comment Editing gives anonymous users the ability to edit and/or delete their comments for a period of time.

[youtube https://www.youtube.com/watch?v=bNCDdQbwA-s&rel=0]

Simple Comment Editing features:
<ol>
<li>No options. Install the plugin. It just works.
<li>Anonymous users can edit comments for 5 minutes.</li>
<li>No styling is necessary. For advanced customization, see the "Other Notes" section.</li>
<li>Advanced customization can be achieved using filters.</li>
</ol>

<h3>Introducing Simple Comment Editing Options</h3>

Introducing Simple Comment Editing Options (a free add-on to Simple Comment Editing). If you lack programming experience and want to customize Simple Comment Editing, then this plugin is for you. See the features below.

<ul>
<li>Set the comment timer</li>
<li>Stop the timer</li>
<li>Hide the timer</li>
<li>Allow unlimited editing for logged in users.</li>
<li>Change the timer output to words or compact (e.g., 10:45)
<li>Select button styles to match your theme</li>
<li>Enable comment editing logging to show an editing history for the comment</li>
<li>See how many people are editing comments</li>
<li>Receive email notifications of edited or deleted comments</li>
<li>Edit the messages and text shown to users</li>
<li>Set a minimum comment length</li>
<li>Disable/Enable comment deletion</li>
<li>And More!</li>
</ul>

> Get <a href="https://mediaron.com/simple-comment-editing-options">the free Simple Comment Editing Options</a> today!

<h3>Help Contribute</h3>

* Leave a star rating
* <a href="https://translate.wordpress.org/projects/wp-plugins/simple-comment-editing">Contribute a translation</a>
* <a href="https://github.com/ronalfy/simple-comment-editing">Contribute some code</a>

== Installation ==

1. Just unzip and upload the "simple-comment-editor" folder to your '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= Why doesn't this plugin come with any styles? =
It's impossible to style an inline comment editor for every theme.  We've included basic HTML markup that is easily stylable to fit your theme.

= Where are the options? =
No options :) - Just simple comment editing. If you prefer options, try out the add-on <a href="https://mediaron.com/simple-comment-editing-options">Simple Comment Editing Options</a>.

= How do I customize this plugin? =
For advanced options, please see the <a href="https://github.com/ronalfy/simple-comment-editing#wordpress-filters">SCE Filter/Action reference</a> or get <a href="https://mediaron.com/simple-comment-editing-options">Simple Comment Editing Options</a>.

= What browsers have you tested this with? =
Simple Comment Editing will work all the way back to IE10.

== Screenshots ==

1. Edit button and timer.
2. Textarea and Save/Cancel buttons.
3. Simple Comment Editing Options admin screen.
4. Simple Comment Editing Options front-end.

== Changelog ==

= 2.3.8 =
* Released 2019-03-17
* Adjusting filters to prevent WSOD

= 2.3.7 =
* Released 2019-03-06
* Added options panel for editing the timer time.

= 2.3.6 =
* Released 2019-02-20
* Added new filter for unlimited editing option.
* Fixing PHP 5.3 fatal error when posting a comment.

= 2.3.5 =
* Released 2019-02-17
* Updating JavaScript hooks to work with WordPress 5.0+

= 2.3.4 =
* Released 2019-02-14
* New hook to allow scripts to load after SCE scripts
* Ability to stop the timer and finish editing

= 2.3.3 =
* Released 2018-11-08
* Fixing timer when it's less than a minute and the timer disaappears

= 2.3.2 =
* Released 2018-11-08
* Added better i18n with JavaScript files. Updated German translation.

= 2.3.1 =
* Released 2018-11-08
* Fixing compatibility with WP Ajaxify Comments

= 2.3.0 =
* Released 2018-11-06
* WordPress 5.0 compatible only
* Enhancement: set the timer past the 90 minute mark
* Enhancement: new filter to hide the timer
* New add-on: <a href="https://mediaron.com/simple-comment-editing-options">Simple Comment Editing Options</a>

= 2.2.1 =
* Released 2018-10-21
* Added CSS around seperator so it can be hidden

= 2.2.0 =
* Released 2018-10-13
* Allow logged in users (author of the post) to bypass the cookies needed for comment editing

= 2.1.11 =
* Released 2018-05-08
* Fixes a bug where a proxy server can give a different IP and prevent the user from editing

= 2.1.9 =
* Released 2018-02-09
* Fixes a bug when the comment is deleted even when canceling the confirmation

= 2.1.7 =
* Released 2017-11-15
* Added filter to remove the delete comment notifications

= 2.1.5 =
* Released 2017-01-20
* Resolving Epoch 1.0 conflict

= 2.1.3 =
* Released 2016-12-07
* Added Thesis compatibility

= 2.1.1 =
* Released 2016-10-18
* Re-added filter `sce_return_comment_text`

= 2.1.0 =
* Released 2016-09-17
* Post meta is no longer used and comment meta is used instead

= 2.0.0 =
* Released 2016-08-14
* Bug fix: Deletion filter now works in JS and in HTML output
* Bug fix: Changing comment time in filter resulted in undefined in JS output
* New filters: Allow changing of edit and save/cancel/delete buttons
* Epoch 2.0 compatible


= 1.9.4 =
* Released 2016-04-02
* Polish translation added

= 1.9.3 =
* Released 2016-03-23
* Fixes issue where Ajax call wouldn't work on non-SSL site but SSL admin
* Resolves double query issue with Epoch
* Resolves comment ghosting with Epoch

= 1.9.1 =
* Released 2015-11-04
* Added minified script for events hooks

= 1.9.0 =
* Released 2015-10-27
* Timer now shows below save/cancel/delete buttons for convenience

= 1.8.5 =
* Released 2015-10-21
* Fixed Portuguese translation (thanks Marco Santos)
* Added Lithuanian translation
* Fixed timer scroll issue where the delay was too long (thanks MamasLT)

= 1.8.3 =
* Released 2015-10-20
* Fixing user logged in issue where unusual timer values are being shown, and the comment appears editable, but is not

= 1.8.1 =
* Released 2015-10-12
* Logged in users who log out can no longer edit comments
* Added Delete button
* Updated translations for language packs

= 1.7.1 =
* Released 2015-09-26
* Fixed Epoch+SCE user logged in dilemma

= 1.7.0 =
* Released 2015-09-20
* Fixed timer issue on many sites. New JS hook for allowing customization of output.

= 1.6.7 =
* Released 2015-09-20
* Fixing PHP bug declaring fatal error for multiple class instances. Props volresource.

= 1.6.5 =
* Released 2015-09-17
* Fixing strings that are not replaced in the timer. Sorry I didn't catch this error.

= 1.6.1 =
* Released 2015-09-16
* Fixed undefined JavaScript errors in timer. Sorry about that.

= 1.6.0 =
* Released 2015-09-16
* Added filter for custom timer output
* Added support for logged in users to bypass cookie checks
* Added support for custom post types

= 1.5.5 =
* Released 2015-09-07
* Fixed return call to be better compatible with third-party customizations
* Added Latvian translation
* Revised WP Ajaxify Comments integration

= 1.5.3 =
* Released 2015-08-23
* Fixing PHP 5.2 error

= 1.5.1 =
* Released 2015-08-19
* Forgot to update minified JS

= 1.5.0 =
* Released 2015-08-19
* Adding hooks for the capability to add extra comment fields.
* Added Epoch compatibility.
* Added JS events so third-party plugins can integrate with SCE.

= 1.3.3 =
* Released 2015-07-22
* Fixing JavaScript error that prevented editing if a certain ID wasn't wrapped around a comment.

= 1.3.2 =
* Released 2015-07-13
* Added filter sce_can_edit for more control over who can or cannot edit a comment.
* Updated translations (Arabic, Dutch, French, German, Norwegian, Persian, Portuguese, Romanian, Russian, Serbian, Spanish, and Swedish).

= 1.3.1 =
* Released 2015-06-26
* Fixed debug error that stated there were two few arguments when there was a percentage sign (%) in a comment. Thank you <a href="https://github.com/ronalfy/simple-comment-editing/issues/7">bernie-simon</a>.

= 1.3.0 =
* Released 2015-06-18
* Improved timer internationalization to accept languages with plurality variations (e.g., Russian)
* Added Russian translation
* Improved the timer to be significantly more accurate
* Added filters to the SCE HTML in order to add custom attributes
* Improved inline documentation
* Added smooth scrolling to the comment after a page load

= 1.2.4 =
* Updated 2015-04-19 - Ensuring WordPress 4.2 compatibility
* Released 2015-02-04
* Added status error message area
* Added filter for custom error messages when saving a comment

= 1.2.2 =
* Updated 2014-12-11 - Ensuring WordPress 4.1 compatibility
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

= 2.3.8 =
Adjusting filters to prevent WSOD

= 2.3.7 =
Added options panel for editing the timer time.

= 2.3.6 =
Added new filter for unlimited editing option. Fixing PHP 5.3 fatal error.

= 2.3.5 =
Updating JavaScript hooks to work with WordPress 5.0+

== Customization ==

For advanced options, please see the <a href="https://github.com/ronalfy/simple-comment-editing#wordpress-filters">SCE Filter/Action reference</a>.