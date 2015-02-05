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

### How do you adjust the comment time?
Place and edit the following into your theme's `functions.php` file:
```php
//Simple Comment Editing
add_filter( 'sce_comment_time', 'edit_sce_comment_time' );
function edit_sce_comment_time( $time_in_minutes ) {
	return 60;
}
```


### How do you change the loading Image?
```php
//Simple Comment Editing
add_filter( 'sce_loading_img', 'edit_sce_loading_img' );
function edit_sce_loading_img( $default_url ) {
	return 'http://domain.com/new_loading_image.gif';
}
```

### Can I catch the comment before saving and add my own error message?
Yes!  Here's an example:
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


