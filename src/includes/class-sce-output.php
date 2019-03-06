<?php
if (!defined('ABSPATH')) die('No direct access.');
class SCE_Plugin_Output {

	/**
	 * Holds options for SCE Options
	 *
	 * @since 2.3.7
	 * @access public
	 * @var array $options
	 */
	public $options = array();

	public function __construct() {

		// Get SCE options
		$options = get_site_option( 'sce_options', false );
		if( false === $options ) return;
		if( is_array( $options ) ) {
			$this->options = $options;
		}

		$this->init_filters();
	}

	/**
	 * Initializes SCE's various filters.
	 *
	 * Initializes SCE's various filters.
	 *
	 * @since 2.3.7
	 * @access private
	 */
	private function init_filters() {
		add_filter( 'sce_comment_time', array( $this, 'modify_timer' ) );
	}

	/**
	 * Returns a new timer.
	 *
	 * Returns a new timer.
	 *
	 * @since 2.3.7
	 * @access public
	 *
	 * @param int $timer Time in minutes to edit the comment
	 * @return int New time in minutes
	 */
	public function modify_timer( $timer ) {
		$new_timer = isset( $this->options['timer'] ) ? $this->options['timer'] : false;
		if ( false === $new_timer ) return $timer;
		return $new_timer;
	}
}