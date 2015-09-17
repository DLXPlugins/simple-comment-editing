Simple Comment Editing for WordPress
======================

Simple Comment Editing for WordPress 3.5+

## Description

Simple Comment Editing is a stripped down version of <a href="http://wordpress.org/plugins/wp-ajax-edit-comments/">Ajax Edit Comments</a>.

The biggest differences:
<ol>
<li>Only anonymous users (and logged in users who don't have permission to edit comments) can edit their comments for a period of time (the default is 5 minutes).</li>
<li>There are no styles included with this plugin.  For most themes, the appearance is acceptable.  For advanced customization, see the "Styles" section.</li>
<li>There are no options.  Some defaults can be overwritten using filters.</li>
</ol>

## Installation

1. Just unzip and upload the "simple-comment-editor" folder to your '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

## Frequently Asked Questions

###Why doesn't this plugin come with any styles?
It's impossible to style an inline comment editor for every theme.  We've included basic HTML markup that is easily stylable to fit your theme.

### Where are the options? =
No options :) - Just simple comment editing.

### I want to style the editing interface.  Where do I start?
See "Styles" section.

### What Browsers Have You Tested This In?
<ul>
<li>IE 6-10</li>
<li>Latest versions of Chrome, Firefox, and Safari</li>
<li>iOS Safari</li>
</ul>

### What Themes Have You Tested This In?
<ul>
<li>Twenty Ten</li>
<li>Twenty Eleven</li>
<li>Twenty Twelve</li>
<li>Twenty Thirteen</li>
<li>Genesis</li>
<li>Genesis Mindstream</li>
</ul>

## WordPress Filters

### sce_timer_output - Add custom timer output

```php
/**
* Filter: sce_timer_output
*
* Modify time output
*
* @since 1.6.0
*
* @param string New Timer Format
*/
$timer_format = "{minutes_time} {minutes_text}{sce_and}{seconds_time} {seconds_text}";
$timer_format = apply_filters( 'sce_timer_output', $timer_format );

/*
{minutes_time}, {minutes_text}, {sce_and}, {seconds_time}, and {seconds_text} are your only variables here.
*/
```

Example:

```php
add_filter( 'sce_timer_output', function( $string ) {
	return $string;
    return "{minutes_time}{colon}{seconds_time}"; //format minute:seconds
}, 11, 1 );
```

### sce_loading_img - Change the loading image
```php
/**
* Filter: sce_loading_img
*
* Replace the loading image with a custom version.
*
* @since 1.0.0
*
* @param string  $image_url URL path to the loading image.
*/
```

Example:

```php
//Simple Comment Editing
add_filter( 'sce_loading_img', 'edit_sce_loading_img' );
function edit_sce_loading_img( $default_url ) {
	return 'http://domain.com/new_loading_image.gif';
}
```

### sce_comment_check_errors - Add custom error messages

```php
/**
* Filter: sce_comment_check_errors
*
* Return a custom error message based on the saved comment
*
* @since 1.2.4
*
* @param bool  $custom_error Default custom error. Overwrite with a string
* @param array $comment_to_save Associative array of comment attributes
*/
```

Here's an example:
```php
add_filter( 'sce_comment_check_errors', 'custom_sce_check_comment_length', 15, 2 );
function custom_sce_check_comment_length( $return = false, $comment = array() ) {
	$comment_content = trim( wp_strip_all_tags( $comment[ 'comment_content' ] ) );
	$comment_length = strlen( $comment_content );
	if ( $comment_length < 50 ) {
		return 'Comment must be at least 50 characters';
	}
	return false;
}

```

### sce_allow_delete - Whether to allow comment deletion or not

```php
/**
* Filter: sce_allow_delete
*
* Determine if users can delete their comments
*
* @since 1.1.0
*
* @param bool  $allow_delete True allows deletion, false does not
*/
```

### sce_get_comment - Add extra data to the comment object 

This is only used when retrieving a comment via Ajax and can be used by third-party plugins who post comments using Ajax

```php
/**
* Filter: sce_get_comment
*
* Modify comment object
*
* @since 1.5.0
*
* @param object Comment Object
*/
```

### sce_extra_fields - Add extra HTML to the editing interface 
```php
/**
* Filter: sce_extra_fields
*
* Filter to add additional form fields
*
* @since 1.4.0
*
* @param string Empty string
* @param int post_id POST ID
* @param int comment_id Comment ID
*/
```

### sce_buttons - Add extra buttons to the editing interface (aside from Cancel and Save)
```php
/**
* Filter: sce_buttons
*
* Filter to add button content
*
* @since 1.3.0
*
* @param string  $textarea_buttons Button HTML
* @param int       $comment_id        Comment ID
*/
```

### sce_content - Modify the edit output HTML
```php
/**
* Filter: sce_content
*
* Filter to overral sce output
*
* @since 1.3.0
*
* @param string  $sce_content SCE content 
* @param int       $comment_id Comment ID of the comment
*/
```

### sce_save_before - Modify the comment object before saving via AJAX
```php
/**
* Filter: sce_save_before
*
* Allow third parties to modify comment
*
* @since 1.4.0
*
* @param object $comment_to_save The Comment Object
* @param int $post_id The Post ID
* @param int $comment_id The Comment ID
*/
```

### sce_can_edit - Override the boolean whether a user can edit a comment or not
```php
/**
* Filter: sce_can_edit
*
* Determine if a user can edit the comment
*
* @since 1.3.2
*
* @param bool  true If user can edit the comment
* @param object $comment Comment object user has left
* @param int $comment_id Comment ID of the comment
* @param int $post_id Post ID of the comment
*/
```

Example: https://gist.github.com/ronalfy/6b4fec8b3ac55bc47f3f

### sce_security_key_min - How many security keys will be stored as post meta
```php
/**
* Filter: sce_security_key_min
*
* Determine how many security keys should be stored as post meta before garbage collection
*
* @since 1.0.0
*
* @param int  $num_keys How many keys to store
*/
```

### sce_load_scripts - Whether to load SCE scripts or not
```php
/**
* Filter: sce_load_scripts
*
* Boolean to decide whether to load SCE scripts or not
*
* @since 1.5.0
*
* @param bool  true to load scripts, false not
*/
```

### sce_comment_time - How long in minutes to allow comment editing
```php
/**
* Filter: sce_comment_time
*
* How long in minutes to edit a comment
*
* @since 1.0.0
*
* @param int  $minutes Time in minutes - Max 90 minutes
*/
```

Example:

```php
//Simple Comment Editing
add_filter( 'sce_comment_time', 'edit_sce_comment_time' );
function edit_sce_comment_time( $time_in_minutes ) {
	return 60;
}
```

## WordPress Actions

### sce_save_after - Triggered via Ajax after a comment has been saved

Useful for custom comment fields

```php
/**
* Action: sce_save_after
*
* Allow third parties to save content after a comment has been updated
*
* @since 1.4.0
*
* @param object $comment_to_save The Comment Object
* @param int $post_id The Post ID
* @param int $comment_id The Comment ID
*/
```

## JavaScript Events

### sce.comment.save.pre - Before a comment is submitted via Ajax

```php
/**
* Event: sce.comment.save.pre
*
* Event triggered before a comment is saved
*
* @since 1.4.0
*
* @param int $comment_id The Comment ID
* @param int $post_id The Post ID
*/
```

### sce.comment.save - After a comment has been saved via Ajax

```php
/**
* Event: sce.comment.save
*
* Event triggered after a comment is saved
*
* @since 1.4.0
*
* @param int $comment_id The Comment ID
* @param int $post_id The Post ID
*/
```

### sce.timer.loaded - After a timer has been loaded

```php
/**
* Event: sce.timer.loaded
*
* Event triggered after a commen's timer has been loaded
*
* @since 1.3.0
*
* @param jQuery Element of the comment
*/
```

### sce.comment.loaded - After a comment has been loaded via Ajax

This hook is useful for third-party plugins who post comments via Ajax.

```php
/**
* Event: sce.comment.loaded
*
* Event triggered after SCE has loaded a comment.
*
* @since 1.5.0
*
* @param object Comment Object
*/
```

## JavaScript Hooks

JS hooks are using WP-JS-Hooks: https://github.com/carldanley/WP-JS-Hooks

Please use handle *wp-hooks* when enqueueing for consistency and to prevent conflicts.

### sce.comment.save.data - Before a comment is submitted via Ajax

Used to modify POST object before being sent via Ajax. This is useful for adding extra fields.

```php
/**
* JSFilter: sce.comment.save.data
*
* Event triggered before a comment is saved
*
* @since 1.4.0
*
* @param object $ajax_save_params
*/
```






## Styling
The plugin doesn't come with any styles.  We leave it up to you to style the interface.  It doesn't look horribly ugly on most themes, but we leave the advanced customization up to you.

### Styling the Edit Interface
The overall editing interface has been wrapped in a `div` with class `sce-edit-comment`.

```css
.sce-edit-comment { /* styles here */ }
```

### Styling the Edit Button
The edit button and timer have been wrapped in a `div` with class `sce-edit-button`.

```css
.sce-edit-button { /* styles here */ }
.sce-edit-button a { /* styles here */ }
.sce-edit-button .sce-timer { /* styles here */ }
```

### Styling the Loading Icon
The loading icon has been wrapped in a `div` with class `sce-loading`.
```css
.sce-loading { /* styles here */ }
.sce-loading img { /* styles here */ }
```

### Styling the Textarea
The textarea interface has been wrapped in a `div` with class `sce-textarea`.

The actual `textarea` has been wrapped in a `div` with class `sce-comment-textarea`.
The save/cancel buttons have been wrapped in a `div` with class `sce-comment-edit-buttons`.

```css
.sce-textarea { /* styles here */ }
.sce-textarea .sce-comment-textarea textarea { /* styles here */ }
.sce-comment-edit-buttons { /* styles here */ }
.sce-comment-edit-buttons .sce-comment-save { /* styles here */ }
.sce-comment-edit-buttons .sce-comment-cancel { /* styles here */ }
```

### Styling the Status Message
The status message has been wrapped in a `div` with class `sce-status`.

Here's some sample styles that mimic how error messages are displayed in the WordPress admin area:

```css
.sce-status {
	border-left: 4px solid #FFF;
	-webkit-box-shadow: 0 1px 1px 0 rgba( 0, 0, 0, 0.1 );
	box-shadow: 0 1px 1px 0 rgba( 0, 0, 0, 0.1 );
	background: #fff;
	color: #333;
	padding: 10px;	
	margin-top: 10px;
}
.sce-status.error {
	border-color: #dd3d36;
}
.sce-status.updated {
	border-color: #7ad03a;
}
```

### Testing the Styles
Since most of the interface is hidden, it's a little hard to style.  Just place this into your stylesheet, and remove when you're done.
```css
/* todo - remove me when done styling */
.sce-edit-button,
.sce-loading,
.sce-textarea {
	display: block !important;
}
```
Have fun leaving lots of test comments :) - Recommended is to use the filter (in the FAQ section) to temporarily increase the comment editing time.  Make sure you leave the test comments when you're not logged in.


