<?php
/**
 * Uninstall Simple Comment Editing.
 *
 * @package SimpleCommentEditing
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

global $wpdb;

$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->postmeta} WHERE LEFT( meta_value, %d ) = %s",
		6,
		'wpAjax'
	)
);
delete_option( 'ajax-edit-comments_security_key_count' );
delete_transient( 'sce_timer_' . get_locale() );
