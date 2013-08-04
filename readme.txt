==FAQ==
= How do you adjust the comment time? =
Place and edit the following into your theme's `functions.php` file:
`
//Simple Comment Editing
add_filter( 'sce_comment_time', 'edit_sce_comment_time' );
function edit_sce_comment_time( $time_in_minutes ) {
	return 60;
}
= What Browsers Have You Tested This In? =
IE6-10
Latest versions of Chrome, Firefox, and Safari
iOS Safari

= What Themes Have You Tested This In? =
* Twenty Ten
* Twenty Eleven
* Twenty Twelve 
* Twenty Thirteen
* Genesis
* Genesis Mindstream