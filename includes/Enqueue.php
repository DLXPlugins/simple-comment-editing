<?php
/**
 * Enqueue assets for the blocks.
 *
 * @package SCE
 */

namespace SCE\Includes;

use SCE\Includes\Functions as Functions;
use SCE\Includes\Admin\Options as Options;

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
		if ( 'options-general.php' !== $hook && 'settings_page_comment-edit-lite' !== $hook ) {
			return;
		}
		wp_enqueue_style(
			'sce-admin',
			Functions::get_plugin_url( 'dist/sce-admin.css' ),
			SCE_VERSION,
			'all'
		);
		wp_enqueue_script( 'fancybox', Functions::get_plugin_url( '/fancybox/jquery.fancybox.min.js' ), array( 'jquery' ), SCE_VERSION, true );
		wp_enqueue_style( 'fancybox', Functions::get_plugin_url( '/fancybox/jquery.fancybox.min.css' ), array(), SCE_VERSION, 'all' );
	}
}
