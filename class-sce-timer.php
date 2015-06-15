<?php
/**
 * Simple Comment Editing controller for internationalized timers
 *
 * Generates internationalized timer variables for use in JavaScript
 *
 * @since 1.3.0
 *
 * @package WordPress
 */
 class SCE_Timer {
	public function __construct() {
		
	}
	
	/**
	* Return internationalized array of seconds/minutes variables
	*
	* Return internationalized array of seconds/minutes variables
	*
	* @since 1.3.0
	* @access public
	*
	* @return associative array of seconds/minutes internationalized variables
	*/
	public function get_timer_vars( $force_transient = false ) {
		$locale = get_locale();
		$timer_vars_transient = get_transient( 'sce_timer_' . $locale );
		if ( $timer_vars_transient && false === $force_transient ) {
			return $timer_vars_transient;
		} else {
			$timer_vars = array(
				'minutes' => $this->get_minutes(),
				'seconds' => $this->get_seconds()
			);
			set_transient( 'sce_timer_' . $locale, $timer_vars, 12 * HOUR_IN_SECONDS );
			return $timer_vars;
		}
		return array();
	}
	
	/**
	* Return the comment edit time in minutes
	*
	* Return the comment edit time in minutes
	*
	* @since 1.3.0
	* @access private
	*
	* @return int comment time
	*/
	private function get_comment_time() {
		return absint( Simple_Comment_Editing::get_instance()->get_comment_time() );
	}	 
	
	/**
	* Return array of internationalized minutes
	*
	* Return array of internationalized minutes
	*
	* @since 1.3.0
	* @access private
	*
	* @return array minutes
	*/
	private function get_minutes() {
		$comment_minutes = $this->get_comment_time();
		$minutes = array();
		for( $i = 0; $i <= $comment_minutes; $i++ ) {
			$minutes[ $i ] = _n( 'minute', 'minutes', $i );
		}
		return $minutes;
	}
	
	/**
	* Return array of internationalized seconds
	*
	* Return array of internationalized seconds
	*
	* @since 1.3.0
	* @access private
	*
	* @return array seconds
	*/
	private function get_seconds() {
		$seconds = array();
		for( $i = 0; $i < 60; $i++ ) {
			$seconds[ $i ] = _n( 'second', 'seconds', $i );
		}
		return $seconds;
	}
}