<?php
/**
 * Enqueue assets for the SCE admin.
 *
 * @package CommentEditLite
 */

namespace DLXPlugins\CommentEditLite;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access.' );
}

/**
 * Class enqueue
 */
class Enqueue {

	/**
	 * Main init functioin.
	 */
	public function run() {

		// Enqueue general admin scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 10, 1 );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook The page hook name.
	 */
	public function admin_scripts( $hook ) {
		if ( 'options-general.php' !== $hook && 'settings_page_simple-comment-editing' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'sce-admin',
			Functions::get_plugin_url( 'dist/sce-admin.css' ),
			SCE_VERSION,
			'all'
		);
	}
}
