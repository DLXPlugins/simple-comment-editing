<?php
if ( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') ) {
    exit();
}
	global $wpdb;

	$sql = "delete from $wpdb->postmeta where left(meta_value, 6) = 'wpAjax'";
	$wpdb->query($sql);
	delete_option( 'ajax-edit-comments_security_key_count' );
	delete_transient( 'sce_timer_' . get_locale() );
?>
