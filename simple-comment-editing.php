<?php

use SCE\Includes\Admin\Options as Options;
use SCE\Includes\Functions as Functions;

class Simple_Comment_Editing {
	private static $instance = null;
	private $comment_time    = 0; // in minutes
	private $loading_img     = '';
	private $allow_delete    = true;
	public $errors;
	private $scheme;

	/**
	 * Mailchimp API variable with <sp> (server prefix) for search/replace.
	 *
	 * @var string Mailchimp API variable.
	 */
	private $mailchimp_api = 'https://<sp>.api.mailchimp.com/3.0/';

	// Singleton
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	} //end get_instance

	private function __construct() {
		add_action( 'init', array( $this, 'init' ), 9 );

		// * Localization Code */
		load_plugin_textdomain( 'simple-comment-editing', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

		// Initialize errors
		$this->errors = new WP_Error();
		$this->errors->add( 'nonce_fail', __( 'You do not have permission to edit this comment.', 'simple-comment-editing' ) );
		$this->errors->add( 'edit_fail', __( 'You can no longer edit this comment.', 'simple-comment-editing' ) );
		$this->errors->add( 'timer_fail', __( 'Timer could not be stopped.', 'simple-comment-editing' ) );
		$this->errors->add( 'comment_empty', __( 'Your comment cannot be empty. Delete instead?', 'simple-comment-editing' ) );
		$this->errors->add( 'comment_marked_spam', __( 'This comment was marked as spam.', 'simple-comment-editing' ) );

		// Determine http/https admin-ajax issue
		$this->scheme = is_ssl() ? 'https' : 'http';
	} //end constructor

	public function init() {

		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return false;
		}

		// Set plugin defaults
		$this->comment_time = $this->get_comment_time();
		/**
		* Filter: sce_loading_img
		*
		* Replace the loading image with a custom version.
		*
		* @since 1.0.0
		*
		* @param string  $image_url URL path to the loading image.
		*/
		$this->loading_img = esc_url( apply_filters( 'sce_loading_img', $this->get_plugin_url( '/images/loading.gif' ) ) );
		/**
		* Filter: sce_allow_delete
		*
		* Determine if users can delete their comments
		*
		* @since 1.1.0
		*
		* @param bool  $allow_delete True allows deletion, false does not
		*/
		$this->allow_delete = (bool) apply_filters( 'sce_allow_delete', $this->allow_delete );

		/*
		 BEGIN ACTIONS */
		// When a comment is posted
		add_action( 'comment_post', array( $this, 'comment_posted' ), 100, 1 );

		// Loading scripts
		add_filter( 'sce_load_scripts', array( $this, 'maybe_load_scripts' ), 5, 1 );
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );

		// Ajax
		add_action( 'wp_ajax_sce_get_time_left', array( $this, 'ajax_get_time_left' ) );
		add_action( 'wp_ajax_nopriv_sce_get_time_left', array( $this, 'ajax_get_time_left' ) );
		add_action( 'wp_ajax_sce_save_comment', array( $this, 'ajax_save_comment' ) );
		add_action( 'wp_ajax_nopriv_sce_save_comment', array( $this, 'ajax_save_comment' ) );
		add_action( 'wp_ajax_sce_delete_comment', array( $this, 'ajax_delete_comment' ) );
		add_action( 'wp_ajax_nopriv_sce_delete_comment', array( $this, 'ajax_delete_comment' ) );
		add_action( 'wp_ajax_sce_get_cookie_var', array( $this, 'generate_cookie_data' ) );
		add_action( 'wp_ajax_nopriv_sce_get_cookie_var', array( $this, 'generate_cookie_data' ) );
		add_action( 'wp_ajax_sce_epoch_get_comment', array( $this, 'ajax_epoch_get_comment' ) );
		add_action( 'wp_ajax_nopriv_sce_epoch_get_comment', array( $this, 'ajax_epoch_get_comment' ) );
		add_action( 'wp_ajax_sce_epoch2_get_comment', array( $this, 'ajax_epoch2_get_comment' ) );
		add_action( 'wp_ajax_nopriv_sce_epoch2_get_comment', array( $this, 'ajax_epoch2_get_comment' ) );
		add_action( 'wp_ajax_sce_get_comment', array( $this, 'ajax_get_comment' ) );
		add_action( 'wp_ajax_nopriv_sce_get_comment', array( $this, 'ajax_get_comment' ) );
		add_action( 'wp_ajax_sce_stop_timer', array( $this, 'ajax_stop_timer' ) );
		add_action( 'wp_ajax_nopriv_sce_stop_timer', array( $this, 'ajax_stop_timer' ) );

		/* Begin Filters */
		if ( ! is_feed() && ! defined( 'DOING_SCE' ) ) {
			add_filter( 'comment_excerpt', array( $this, 'add_edit_interface' ), 1000, 2 );
			add_filter( 'comment_text', array( $this, 'add_edit_interface' ), 1000, 2 );
			add_filter( 'thesis_comment_text', array( $this, 'add_edit_interface' ), 1000, 2 );
		}

		// Epoch Compatibility
		add_filter( 'epoch_iframe_scripts', array( $this, 'epoch_add_sce' ), 15 );

		// Button themes.
		add_filter( 'sce_button_extra_save', array( $this, 'maybe_add_save_icon' ) );
		add_filter( 'sce_button_extra_cancel', array( $this, 'maybe_add_cancel_icon' ) );
		add_filter( 'sce_button_extra_delete', array( $this, 'maybe_add_delete_icon' ) );
		add_filter( 'sce_wrapper_class', array( $this, 'output_theme_class' ) );

		// Add Mailchimp Checkbox.
		add_filter( 'comment_form_defaults', array( $this, 'add_mailchimp_checkbox' ), 100 );
		// When a new comment has been added.
		add_action( 'comment_post', array( $this, 'comment_posted_mailchimp' ), 100, 2 );
	} //end init

	/**
	 * add_edit_interface - Adds the SCE interface if a user can edit their comment
	 *
	 * Called via the comment_text or comment_excerpt filter to add the SCE editing interface to a comment.
	 *
	 * @since 1.0
	 */
	public function add_edit_interface( $comment_content, $passed_comment = false ) {
		global $comment; // For Thesis
		if ( ( ! $comment && ! $passed_comment ) || empty( $comment_content ) ) {
			return $comment_content;
		}
		if ( $passed_comment ) {
			$comment = (object) $passed_comment;
		}

		$comment_id = absint( $comment->comment_ID );
		$post_id    = absint( $comment->comment_post_ID );

		// Check to see if a user can edit their comment
		if ( ! $this->can_edit( $comment_id, $post_id ) ) {
			return $comment_content;
		}

		// Variables for later
		$original_content = $comment_content;
		$raw_content      = $comment->comment_content; // For later usage in the textarea

		// Yay, user can edit - Add the initial wrapper
		$comment_wrapper = sprintf( '<div id="sce-comment%d" class="sce-comment">%s</div>', $comment_id, $comment_content );

		$classes = array( 'sce-edit-comment' );
		/**
		 * Filter: sce_wrapper_class
		 *
		 * Filter allow editing of wrapper class
		 *
		 * @since 2.3.0
		 *
		 * @param array Array of classes for the initial wrapper
		 */
		$classes = apply_filters( 'sce_wrapper_class', $classes );

		// Create Overall wrapper for JS interface
		$sce_content = sprintf( '<div id="sce-edit-comment%d" class="%s">', $comment_id, esc_attr( implode( ' ', $classes ) ) );

		// Edit Button
		$sce_content  .= '<div class="sce-edit-button" style="display:none;">';
		$ajax_edit_url = add_query_arg(
			array(
				'cid' => $comment_id,
				'pid' => $post_id,
			),
			wp_nonce_url( admin_url( 'admin-ajax.php', $this->scheme ), 'sce-edit-comment' . $comment_id )
		);

		/**
		* Filter: sce_text_edit
		*
		* Filter allow editing of edit text
		*
		* @since 2.0.0
		*
		* @param string Translated click to edit text
		*/
		$click_to_edit_text = apply_filters( 'sce_text_edit', __( 'Click to Edit', 'simple-comment-editing' ) );

		/**
		* Filter: sce_text_edit_delete
		*
		* Filter allow editing of the delete text
		*
		* @since 2.6.0
		*
		* @param string Translated delete text
		*/
		$delete_edit_text = apply_filters( 'sce_text_edit_delete', __( 'Delete Comment', 'simple-comment-editing' ) );

		$allow_edit_delete = apply_filters( 'sce_allow_delete_button', false );
		$allow_edit        = apply_filters( 'sce_allow_edit_button', true );

		if ( $allow_edit && ! $allow_edit_delete ) {
			$sce_content .= sprintf( '<a class="sce-edit-button-main" href="%s">%s</a>', esc_url( $ajax_edit_url ), esc_html( $click_to_edit_text ) );
		} elseif ( $allow_edit && $allow_edit_delete ) {
			$sce_content .= sprintf( '<a class="sce-edit-button-main" href="%s">%s</a>', esc_url( $ajax_edit_url ), esc_html( $click_to_edit_text ) );
			$sce_content .= '<span class="sce-seperator">&nbsp;&ndash;&nbsp;</span>';
			$sce_content .= sprintf( '<a class="sce-delete-button-main" href="%s">%s</a>', esc_url( $ajax_edit_url ), esc_html( $delete_edit_text ) );
		} elseif ( ! $allow_edit && $allow_edit_delete ) {
			$sce_content .= sprintf( '<a class="sce-delete-button-main" href="%s">%s</a>', esc_url( $ajax_edit_url ), esc_html( $delete_edit_text ) );
		} else {
			$sce_content .= sprintf( '<a class="sce-edit-button-main" href="%s">%s</a>', esc_url( $ajax_edit_url ), esc_html( $click_to_edit_text ) );
		}

		/**
		 * Filter: sce_show_timer
		 *
		 * Filter allow you to hide the timer
		 *
		 * @since 2.3.0
		 *
		 * @param bool Whether to show the timer or not
		 */
		if ( apply_filters( 'sce_show_timer', true ) && false === apply_filters( 'sce_unlimited_editing', false, $comment ) ) {
			$sce_content .= '<span class="sce-seperator">&nbsp;&ndash;&nbsp;</span>';
			$sce_content .= '<span class="sce-timer"></span>';
		}
		$sce_content .= '</div><!-- .sce-edit-button -->';

		// Loading button
		$sce_content .= '<div class="sce-loading" style="display: none;">';
		$sce_content .= sprintf( '<img src="%1$s" title="%2$s" alt="%2$s" />', esc_url( $this->loading_img ), esc_attr__( 'Loading', 'simple-comment-editing' ) );
		$sce_content .= '</div><!-- sce-loading -->';

		// Textarea
		$textarea_content  = '<div class="sce-textarea" style="display: none;">';
		$textarea_content .= '<div class="sce-comment-textarea">';
		$textarea_content .= '<textarea class="sce-comment-text" cols="45" rows="8">%s</textarea>';
		$textarea_content .= '</div><!-- .sce-comment-textarea -->';

		/**
		* Filter: sce_extra_fields
		*
		* Filter to add additional form fields
		*
		* @since 1.5.0
		*
		* @param string Empty string
		* @param int post_id POST ID
		* @param int comment_id Comment ID
		*/
		$textarea_content .= apply_filters( 'sce_extra_fields', '', $post_id, $comment_id );

		$textarea_content       .= '%s</div><!-- .sce-textarea -->';
		$textarea_button_content = '<div class="sce-comment-edit-buttons">';

		/**
		* Filter: sce_text_save
		*
		* Filter allow editing of save text
		*
		* @since 2.0.0
		*
		* @param string Translated save text
		*/
		$save_text = apply_filters( 'sce_text_save', __( 'Save', 'simple-comment-editing' ) );

		/**
		* Filter: sce_text_cancel
		*
		* Filter allow editing of cancel text
		*
		* @since 2.0.0
		*
		* @param string Translated cancel text
		*/
		$cancel_text = apply_filters( 'sce_text_cancel', __( 'Cancel', 'simple-comment-editing' ) );

		/**
		* Filter: sce_text_delete
		*
		* Filter allow editing of delete text
		*
		* @since 2.0.0
		*
		* @param string Translated delete text
		*/
		$delete_text = apply_filters( 'sce_text_delete', __( 'Delete', 'simple-comment-editing' ) );

		$textarea_buttons = '<div class="sce-comment-edit-buttons-group">';
		$textarea_buttons  .= sprintf( '<button class="sce-comment-save">%s%s</button>', apply_filters( 'sce_button_extra_save', '' ), esc_html( $save_text ) );
		$textarea_buttons .= sprintf( '<button class="sce-comment-cancel">%s%s</button>', apply_filters( 'sce_button_extra_cancel', '' ), esc_html( $cancel_text ) );
		$textarea_buttons .= $this->allow_delete ? sprintf( '<button class="sce-comment-delete">%s%s</button>', apply_filters( 'sce_button_extra_delete', '' ), esc_html( $delete_text ) ) : '';
		$textarea_buttons .= '</div><!-- .sce-comment-edit-buttons-group -->';
		if ( apply_filters( 'sce_show_timer', true ) ) {
			$textarea_buttons .= '<div class="sce-timer"></div>';
		}
		/**
		* Filter: sce_buttons
		*
		* Filter to add button content
		*
		* @since 1.3.0
		*
		* @param string  $textarea_buttons Button HTML
		* @param int     $comment_id       Comment ID
		*/
		$textarea_buttons         = apply_filters( 'sce_buttons', $textarea_buttons, $comment_id );
		$textarea_button_content .= $textarea_buttons . '</div><!-- .sce-comment-edit-buttons -->';
		$textarea_content         = sprintf( $textarea_content, esc_textarea( $raw_content ), $textarea_button_content );

		// End
		$sce_content .= $textarea_content . '</div><!-- .sce-edit-comment -->';

		// Status Area
		$sce_content .= sprintf( '<div id="sce-edit-comment-status%d" class="sce-status" style="display: none;"></div><!-- .sce-status -->', $comment_id );

		/**
		* Filter: sce_content
		*
		* Filter to overral sce output
		*
		* @since 1.3.0
		*
		* @param string  $sce_content SCE content
		* @param int     $comment_id  Comment ID of the comment
		*/
		$sce_content = apply_filters( 'sce_content', $sce_content, $comment_id );

		// Return content
		$comment_content = $comment_wrapper . $sce_content;
		return $comment_content;

	} //end add_edit_interface

	/**
	 * Add a delete icon.
	 *
	 * Add a delete icon.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string $text Button text.
	 *
	 * @return string Button text
	 */
	public function maybe_add_delete_icon( $text ) {
		if ( true === Options::get_options( false, 'show_icons' ) ) {
			return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/><path d="M0 0h24v24H0z" fill="none"/></svg>';
		}
		return $text;
	}

	/**
	 * Add a cancel icon.
	 *
	 * Add a cancel icon.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string $text Button text.
	 *
	 * @return string Button text
	 */
	public function maybe_add_cancel_icon( $text ) {
		if ( true === Options::get_options( false, 'show_icons' ) ) {
			return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="24" viewBox="0 0 24 20"><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"/><path d="M0 0h24v24H0z" fill="none"/></svg>';
		}
		return $text;
	}

	/**
	 * Add a save icon.
	 *
	 * Add a save icon.
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param string $text Button text.
	 *
	 * @return string Button text
	 */
	public function maybe_add_save_icon( $text ) {
		if ( true === Options::get_options( false, 'show_icons' ) ) {
			return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path d="M0 0h24v24H0z" fill="none"/><path d="M17 3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V7l-4-4zm-5 16c-1.66 0-3-1.34-3-3s1.34-3 3-3 3 1.34 3 3-1.34 3-3 3zm3-10H5V5h10v4z"/></svg>';
		}
		return $text;
	}

	/**
	 * Returns a theme class.
	 *
	 * Returns a theme class.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $classes SCE Wrapper class.
	 * @return array $classes New SCE theme classes
	 */
	public function output_theme_class( $classes = array() ) {
		$theme = Options::get_options( false, 'button_theme' );
		if ( false === $theme ) {
			return $classes;
		}
		$classes[] = $theme;
		return $classes;
	}

	/**
	 * add_scripts - Adds the necessary JavaScript for the plugin (only loads on posts/pages)
	 *
	 * Called via the wp_enqueue_scripts
	 *
	 * @since 1.0
	 */
	public function add_scripts() {
		if ( ! is_single() && ! is_singular() && ! is_page() ) {
			return;
		}

		// Check if there are any cookies present, otherwise don't load the scripts - WPAC_PLUGIN_NAME is for wp-ajaxify-comments (if the plugin is installed, load the JavaScript file)

		/**
		 * Filter: sce_load_scripts
		 *
		 * Boolean to decide whether to load SCE scripts or not
		 *
		 * @since 1.5.0
		 *
		 * @param bool  true to load scripts, false not
		 */
		$load_scripts = apply_filters( 'sce_load_scripts', false );
		if ( ! $load_scripts ) {
			return;
		}

		$main_script_uri  = $this->get_plugin_url( '/js/simple-comment-editing.js' );
		$hooks_script_url = $this->get_plugin_url( '/js/event-manager.js' );
		wp_enqueue_script( 'simple-comment-editing', $main_script_uri, array( 'jquery', 'wp-ajax-response', 'wp-i18n', 'wp-hooks' ), SCE_VERSION, true );
		wp_enqueue_style( 'simple-comment-editing', Functions::get_plugin_url( 'dist/sce-frontend.css' ), array(), SCE_VERSION, 'all' );

		/**
		* Action: sce_scripts_loaded
		*
		* Allows other plugins to load scripts after SCE has loaded
		*
		* @since 2.3.4
		*/
		do_action( 'sce_scripts_loaded' );

		/* For translations in JS */
		wp_set_script_translations( 'simple-comment-editing', 'simple-comment-editing' );

		/**
		 * Filter: sce_allow_delete_confirmation
		 *
		 * Boolean to decide whether to show a delete confirmation
		 *
		 * @since 2.1.7
		 *
		 * @param bool true to show a confirmation, false if not
		 */
		$allow_delete_confirmation = (bool) apply_filters( 'sce_allow_delete_confirmation', true );

		wp_localize_script(
			'simple-comment-editing',
			'simple_comment_editing',
			array(
				'and'                       => __( 'and', 'simple-comment-editing' ),
				'confirm_delete'            => apply_filters( 'sce_confirm_delete', __( 'Do you want to delete this comment?', 'simple-comment-editing' ) ),
				'comment_deleted'           => apply_filters( 'sce_comment_deleted', __( 'Your comment has been removed.', 'simple-comment-editing' ) ),
				'comment_deleted_error'     => apply_filters( 'sce_comment_deleted_error', __( 'Your comment could not be deleted', 'simple-comment-editing' ) ),
				'empty_comment'             => apply_filters( 'sce_empty_comment', $this->errors->get_error_message( 'comment_empty' ) ),
				'allow_delete'              => $this->allow_delete,
				'allow_delete_confirmation' => $allow_delete_confirmation,
				'ajax_url'                  => admin_url( 'admin-ajax.php', $this->scheme ),
				'nonce'                     => wp_create_nonce( 'sce-general-ajax-nonce' ),
				'timer_appearance'          => sanitize_text_field( Options::get_options( false, 'timer_appearance' ) ),
			)
		);

		/**
		* Action: sce_load_assets
		*
		* Allow other plugins to load scripts/styyles for SCE
		*
		* @since 2.3.0
		*/
		do_action( 'sce_load_assets' );
	} //end add_scripts

	 /**
	  * ajax_get_time_left - Returns a JSON object of minutes/seconds of the time left to edit a comment
	  *
	  * Returns a JSON object of minutes/seconds of the time left to edit a comment
	  *
	  * @since 1.0
	  *
	  * @param int $_POST[ 'comment_id' ] The Comment ID
	  * @param int $_POST[ 'post_id' ] The Comment's Post ID
	  * @return JSON object e.g. {minutes:4,seconds:5}
	  */
	public function ajax_get_time_left() {
		check_ajax_referer( 'sce-general-ajax-nonce' );
		global $wpdb;
		$comment_id = absint( $_POST['comment_id'] );
		$post_id    = absint( $_POST['post_id'] );
		$comment    = get_comment( $comment_id, OBJECT );
		// Check if user can edit comment
		if ( ! $this->can_edit( $comment_id, $post_id ) ) {
			$response = array(
				'minutes'    => 0,
				'seconds'    => 0,
				'comment_id' => 0,
				'can_edit'   => false,
			);
			die( json_encode( $response ) );
		}

		/**
		 * Filter: sce_unlimited_editing
		 *
		 * Allow unlimited comment editing
		 *
		 * @since 2.3.6
		 *
		 * @param bool Whether to allow unlimited comment editing
		 * @param object Comment object
		 */
		$sce_unlimited_editing = apply_filters( 'sce_unlimited_editing', false, $comment );
		if ( $sce_unlimited_editing ) {
			$response = array(
				'minutes'    => 'unlimited',
				'seconds'    => 'unlimited',
				'comment_id' => $comment_id,
				'can_edit'   => true,
			);
			die( json_encode( $response ) );
		}

		$comment_time = absint( $this->comment_time );
		$query        = $wpdb->prepare( "SELECT ( $comment_time * 60 - (UNIX_TIMESTAMP('" . current_time( 'mysql' ) . "') - UNIX_TIMESTAMP(comment_date))) comment_time FROM {$wpdb->comments} where comment_ID = %d", $comment_id );

		$comment_time_result = $wpdb->get_row( $query, ARRAY_A );

		/**
		 * Filter: sce_get_comment_time_left
		 *
		 * Get the comment time remaining.
		 *
		 * @since 2.8.0
		 *
		 * @param int    Current comment editing time.
		 * @param string Current time format in date/time format.
		 * @param int    Current Post ID.
		 * @param int    Current Comment ID.
		 */
		$time_left = apply_filters( 'sce_get_comment_time_left', $comment_time_result['comment_time'], $comment_time, $post_id, $comment_id );

		if ( $time_left < 0 ) {
			$response = array(
				'minutes'    => 0,
				'comment_id' => $comment_id,
				'seconds'    => 0,
				'can_edit'   => false,
			);
			die( json_encode( $response ) );
		}
		$minutes  = floor( $time_left / 60 );
		$seconds  = $time_left - ( $minutes * 60 );
		$response = array(
			'minutes'    => $minutes,
			'comment_id' => $comment_id,
			'seconds'    => $seconds,
			'can_edit'   => true,

		);
		die( json_encode( $response ) );
	} //end ajax_get_time_left

	 /**
	  * ajax_stop_timer - Removes the timer and stops comment editing
	  *
	  * Removes the timer and stops comment editing
	  *
	  * @since 1.1.0
	  *
	  * @param int    $_POST[ 'comment_id' ] The Comment ID
	  * @param int    $_POST[ 'post_id' ] The Comment's Post ID
	  * @param string $_POST[ 'nonce' ] The nonce to check against
	  * @return JSON object
	  */
	public function ajax_stop_timer() {
		$comment_id = absint( $_POST['comment_id'] );
		$post_id    = absint( $_POST['post_id'] );
		$nonce      = $_POST['nonce'];

		$return           = array();
		$return['errors'] = false;

		// Do a nonce check
		if ( ! wp_verify_nonce( $nonce, 'sce-edit-comment' . $comment_id ) ) {
			$return['errors'] = true;
			$return['remove'] = true;
			$return['error']  = $this->errors->get_error_message( 'nonce_fail' );
			die( json_encode( $return ) );
		}

		// Check to see if the user can edit the comment
		if ( ! $this->can_edit( $comment_id, $post_id ) ) {
			$return['errors'] = true;
			$return['remove'] = true;
			$return['error']  = $this->errors->get_error_message( 'edit_fail' );
			die( json_encode( $return ) );
		}

		/**
		 * Action: sce_timer_stopped
		 *
		 * Allow third parties to take action a timer has been stopped
		 *
		 * @since 2.3.0
		 *
		 * @param int $post_id The Post ID
		 * @param int $comment_id The Comment ID
		 */
		do_action( 'sce_timer_stopped', $post_id, $comment_id );

		delete_comment_meta( $comment_id, '_sce' );

		$return['error'] = '';
		die( json_encode( $return ) );
	} //end ajax_delete_comment

	 /**
	  * ajax_delete_comment- Removes a WordPress comment, but saves it to the trash
	  *
	  * @since 1.1.0
	  *
	  * @param int    $_POST[ 'comment_id' ] The Comment ID
	  * @param int    $_POST[ 'post_id' ] The Comment's Post ID
	  * @param string $_POST[ 'nonce' ] The nonce to check against
	  * @return JSON object
	  */
	public function ajax_delete_comment() {
		$comment_id = absint( $_POST['comment_id'] );
		$post_id    = absint( $_POST['post_id'] );
		$nonce      = $_POST['nonce'];

		$return           = array();
		$return['errors'] = false;

		// Do a nonce check
		if ( ! wp_verify_nonce( $nonce, 'sce-edit-comment' . $comment_id ) ) {
			$return['errors'] = true;
			$return['remove'] = true;
			$return['error']  = $this->errors->get_error_message( 'nonce_fail' );
			die( json_encode( $return ) );
		}

		// Check to see if the user can edit the comment
		if ( ! $this->can_edit( $comment_id, $post_id ) || $this->allow_delete == false ) {
			$return['errors'] = true;
			$return['remove'] = true;
			$return['error']  = $this->errors->get_error_message( 'edit_fail' );
			die( json_encode( $return ) );
		}

		/**
		 * Action: sce_comment_is_deleted
		 *
		 * Allow third parties to take action when a comment has been deleted
		 *
		 * @since 2.3.0
		 *
		 * @param int $post_id The Post ID
		 * @param int $comment_id The Comment ID
		 */
		do_action( 'sce_comment_is_deleted', $post_id, $comment_id );

		wp_delete_comment( $comment_id ); // Save to trash for admin retrieval
		$return['error'] = '';
		die( json_encode( $return ) );
	} //end ajax_delete_comment

	/**
	 * ajax_get_comment - Gets a Comment
	 *
	 * Returns a JSON object of the comment and comment text
	 *
	 * @access public
	 * @since 1.5.0
	 *
	 * @param int $_POST[ 'comment_id' ] The Comment ID
	 * @return JSON object
	 */
	public function ajax_get_comment() {
		check_ajax_referer( 'sce-general-ajax-nonce' );
		$comment_id = absint( $_POST['comment_id'] );

		/**
		* Filter: sce_get_comment
		*
		* Modify comment object
		*
		* @since 1.5.0
		*
		* @param array Comment array
		*/
		$comment                 = apply_filters( 'sce_get_comment', get_comment( $comment_id, ARRAY_A ) );
		$comment['comment_html'] = $this->get_comment_content( (object) $comment );

		if ( $comment ) {
			die( json_encode( $comment ) );
		}
		die( '' );
	}

	/**
	 * ajax_epoch_get_comment - Gets a Epoch formatted comment
	 *
	 * Returns a JSON object of the Epoch comment
	 *
	 * @access public
	 * @since 1.5.0
	 *
	 * @param int $_POST[ 'comment_id' ] The Comment ID
	 * @return JSON object
	 */
	public function ajax_epoch_get_comment() {
		check_ajax_referer( 'sce-general-ajax-nonce' );
		 $comment_id = absint( $_POST['comment_id'] );
		 $comment    = get_comment( $comment_id, ARRAY_A );
		if ( $comment ) {
			$function = 'postmatic\epoch\front\api_helper::add_data_to_comment';
			$comment  = call_user_func( $function, $comment, false );
			die( json_encode( $comment ) );
		}
		die( '' );
	}

	/**
	 * ajax_epoch2_get_comment - Gets a Epoch formatted comment
	 *
	 * Returns a JSON object of the Epoch comment
	 *
	 * @access public
	 * @since 2.0.0
	 *
	 * @param int $_POST[ 'comment_id' ] The Comment ID
	 * @return JSON object
	 */
	public function ajax_epoch2_get_comment() {
		check_ajax_referer( 'sce-general-ajax-nonce' );
		$comment_id = absint( $_POST['comment_id'] );
		$comment    = get_comment( $comment_id, OBJECT );
		if ( $comment ) {
			die( $this->get_comment_content( $comment ) );
		}
		die( '' );
	}

	 /**
	  * ajax_save_comment - Saves a comment to the database, returns the updated comment via JSON
	  *
	  * Returns a JSON object of the saved comment
	  *
	  * @since 1.0
	  *
	  * @param string $_POST[ 'comment_content' ] The comment to save
	  * @param int    $_POST[ 'comment_id' ] The Comment ID
	  * @param int    $_POST[ 'post_id' ] The Comment's Post ID
	  * @param string $_POST[ 'nonce' ] The nonce to check against
	  * @return JSON object
	  */
	public function ajax_save_comment() {
		define( 'DOING_SCE', true );
		$new_comment_content = trim( $_POST['comment_content'] );
		$comment_id          = absint( $_POST['comment_id'] );
		$post_id             = absint( $_POST['post_id'] );
		$nonce               = $_POST['nonce'];

		$return           = array();
		$return['errors'] = false;
		$return['remove'] = false; // If set to true, removes the editing interface

		// Do a nonce check
		if ( ! wp_verify_nonce( $nonce, 'sce-edit-comment' . $comment_id ) ) {
			$return['errors'] = true;
			$return['remove'] = true;
			$return['error']  = $this->errors->get_error_message( 'nonce_fail' );
			die( json_encode( $return ) );
		}

		// Check to see if the user can edit the comment
		if ( ! $this->can_edit( $comment_id, $post_id ) ) {
			$return['errors'] = true;
			$return['remove'] = true;
			$return['error']  = $this->errors->get_error_message( 'edit_fail' );
			die( json_encode( $return ) );
		}

		// Check that the content isn't empty
		if ( '' == $new_comment_content || 'undefined' == $new_comment_content ) {
			$return['errors'] = true;
			$return['error']  = $this->errors->get_error_message( 'comment_empty' );
			die( json_encode( $return ) );
		}

		// Get original comment
		$comment_to_save = $original_comment = get_comment( $comment_id, ARRAY_A );

		// Check the comment
		if ( $comment_to_save['comment_approved'] == 1 ) {
			// Short circuit comment moderation filter.
			add_filter( 'pre_option_comment_moderation', array( $this, 'short_circuit_comment_moderation' ) );
			add_filter( 'pre_option_comment_whitelist', array( $this, 'short_circuit_comment_moderation' ) );
			if ( check_comment( $comment_to_save['comment_author'], $comment_to_save['comment_author_email'], $comment_to_save['comment_author_url'], $new_comment_content, $comment_to_save['comment_author_IP'], $comment_to_save['comment_agent'], $comment_to_save['comment_type'] ) ) {
				$comment_to_save['comment_approved'] = 1;
			} else {
				$comment_to_save['comment_approved'] = 0;
			}
			// Remove Short circuit comment moderation filter.
			remove_filter( 'pre_option_comment_moderation', array( $this, 'short_circuit_comment_moderation' ) );
			remove_filter( 'pre_option_comment_whitelist', array( $this, 'short_circuit_comment_moderation' ) );
		}

		// Check comment against blacklist
		if ( function_exists( 'wp_check_comment_disallowed_list' ) ) {
			if ( wp_check_comment_disallowed_list( $comment_to_save['comment_author'], $comment_to_save['comment_author_email'], $comment_to_save['comment_author_url'], $new_comment_content, $comment_to_save['comment_author_IP'], $comment_to_save['comment_agent'] ) ) {
				$comment_to_save['comment_approved'] = 'spam';
			};
		} else {
			if ( wp_blacklist_check( $comment_to_save['comment_author'], $comment_to_save['comment_author_email'], $comment_to_save['comment_author_url'], $new_comment_content, $comment_to_save['comment_author_IP'], $comment_to_save['comment_agent'] ) ) {
				$comment_to_save['comment_approved'] = 'spam';
			}
		}

		// Update comment content with new content
		$comment_to_save['comment_content'] = $new_comment_content;

		// Before save comment
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
		$custom_error = apply_filters( 'sce_comment_check_errors', false, $comment_to_save ); // Filter expects a string returned - $comment_to_save is an associative array
		if ( is_string( $custom_error ) && ! empty( $custom_error ) ) {
			$return['errors'] = true;
			$return['error']  = esc_html( $custom_error );
			die( json_encode( $return ) );
		}

		/**
		 * Filter: sce_save_before
		 *
		 * Allow third parties to modify comment
		 *
		 * @since 1.5.0
		 *
		 * @param array $comment_to_save The Comment array
		 * @param int $post_id The Post ID
		 * @param int $comment_id The Comment ID
		 */
		$comment_to_save = apply_filters( 'sce_save_before', $comment_to_save, $post_id, $comment_id );

		// Save the comment
		wp_update_comment( $comment_to_save );

		/**
		 * Action: sce_save_after
		 *
		 * Allow third parties to save content after a comment has been updated
		 *
		 * @since 1.5.0
		 *
		 * @param array $comment_to_save The Comment array
		 * @param int $post_id The Post ID
		 * @param int $comment_id The Comment ID
		 * @param array $original_comment The original
		*/
		ob_start();
		do_action( 'sce_save_after', $comment_to_save, $post_id, $comment_id, $original_comment );
		ob_end_clean();

		// If the comment was marked as spam, return an error
		if ( $comment_to_save['comment_approved'] === 'spam' ) {
			$return['errors'] = true;
			$return['remove'] = true;
			$return['error']  = $this->errors->get_error_message( 'comment_marked_spam' );
			$this->remove_comment_cookie( $comment_to_save );
			die( json_encode( $return ) );
		}

		/**
		 * Filter: sce_akismet_enabled
		 *
		 * Allow third parties to disable Akismet.
		 *
		 * @param bool true if Akismet is enabled
		 */
		$akismet_enabled = apply_filters( 'sce_akismet_enabled', true );

		// Check the new comment for spam with Akismet
		if ( function_exists( 'akismet_check_db_comment' ) && $akismet_enabled ) {
			if ( akismet_verify_key( get_option( 'wordpress_api_key' ) ) != 'failed' ) { // Akismet
				$response = akismet_check_db_comment( $comment_id );
				if ( $response == 'true' ) { // You have spam
					wp_set_comment_status( $comment_id, 'spam' );
					$return['errors'] = true;
					$return['remove'] = true;
					$return['error']  = $this->errors->get_error_message( 'comment_marked_spam' );
					$this->remove_comment_cookie( $comment_to_save );
					die( json_encode( $return ) );
				}
			}
		}

		$comment_to_return = $this->get_comment( $comment_id );

		/**
		 * Filter: sce_return_comment_text
		 *
		 * Allow comment manipulation before the comment is returned
		 *
		 * @since 2.1.0
		 *
		 * @param string  Comment Content
		 * @param object  Comment Object
		 * @param int     Post ID
		 * @param int     Comment ID
		 */
		$comment_content_to_return = apply_filters( 'sce_return_comment_text', $this->get_comment_content( $comment_to_return ), $comment_to_return, $post_id, $comment_id );

		// Ajax response
		$return['comment_text'] = $comment_content_to_return;
		$return['error']        = '';
		die( json_encode( $return ) );
	} //end ajax_save_comment

	/**
	 * Short circuit the comment moderation option check.
	 *
	 * @since 2.3.9
	 *
	 * @param bool|mixed $option_value The option value for moderation
	 *
	 * @return int Return a string so there is not a boolean value.
	 */
	public function short_circuit_comment_moderation( $option_value ) {
		return 'approved';
	}

	/**
	 * Checks if the plugin is on a multisite install.
	 *
	 * @return true if multisite, false if not.
	 */
	public static function is_multisite() {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}
		if ( is_multisite() && is_plugin_active_for_network( SCE_SLUG ) ) {
			return true;
		}
		return false;
	}

	/**
	 * can_edit - Returns true/false if a user can edit a comment
	 *
	 * Retrieves a cookie to see if a comment can be edited or not
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id The Comment ID
	 * @param int $post_id The Comment's Post ID
	 * @return bool true if can edit, false if not
	 */
	public function can_edit( $comment_id, $post_id ) {
		global $comment, $post;

		/**
		 * Filter: sce_can_edit_pre
		 *
		 * Determine if a user can edit the comment (can short-circuit.)
		 *
		 * @since 2.9.1
		 *
		 * @param bool  true If user can edit the comment
		 * @param WP_Comment $comment Comment object user has left (may be unset)
		 * @param WP_Post    $post    Post object (may be unset)
		 */
		$can_edit_pre = apply_filters( 'sce_can_edit_pre', true, $comment, $post );
		if ( ! $can_edit_pre ) {
			return false;
		}

		if ( ! is_object( $comment ) ) {
			$comment = get_comment( $comment_id, OBJECT );
		}
		if ( ! is_object( $post ) ) {
			$post = get_post( $post_id, OBJECT );
		}

		if ( $comment->comment_post_ID != $post_id ) {
			return false;
		}
		$user_id = absint( $this->get_user_id() );

		// if we are logged in and are the comment author, bypass cookie check
		$comment_meta      = get_comment_meta( $comment_id, '_sce', true );
		$cookie_bypass     = false;
		$is_comment_author = false;
		if ( is_user_logged_in() && $user_id === absint( $comment->user_id ) ) {
			$is_comment_author = true;
		}

		// If unlimited is enabled and user is comment author, user can edit.
		$sce_unlimited_editing = apply_filters( 'sce_unlimited_editing', false, $comment );
		if ( $is_comment_author && $sce_unlimited_editing ) {
			return apply_filters( 'sce_can_edit', true, $comment, $comment_id, $post_id );
		}

		/**
		 * Filter: sce_can_edit_cookie_bypass
		 *
		 * Bypass the cookie based user verification.
		 *
		 * @since 2.2.0
		 *
		 * @param boolean            Whether to bypass cookie authentication
		 * @param object $comment    Comment object
		 * @param int    $comment_id The comment ID
		 * @param int    $post_id    The post ID of the comment
		 * @param int    $user_id    The logged in user ID
		 */
		$cookie_bypass = apply_filters( 'sce_can_edit_cookie_bypass', $cookie_bypass, $comment, $comment_id, $post_id, $user_id );

		// Check to see if time has elapsed for the comment
		if ( ( $sce_unlimited_editing && $cookie_bypass ) || $is_comment_author ) {
			$comment_timestamp = strtotime( $comment->comment_date );
			$time_elapsed      = current_time( 'timestamp', get_option( 'gmt_offset' ) ) - $comment_timestamp;
			$minutes_elapsed   = ( ( ( $time_elapsed % 604800 ) % 86400 ) % 3600 ) / 60;
			if ( ( $minutes_elapsed - $this->comment_time ) >= 0 ) {
				return false;
			}
		} elseif ( false === $cookie_bypass ) {
			// Set cookies for verification
			$comment_date_gmt = date( 'Y-m-d', strtotime( $comment->comment_date_gmt ) );
			$cookie_hash      = md5( $comment->comment_author_IP . $comment_date_gmt . $comment->user_id . $comment->comment_agent );

			$cookie_value      = $this->get_cookie_value( 'SimpleCommentEditing' . $comment_id . $cookie_hash );
			$comment_meta_hash = get_comment_meta( $comment_id, '_sce', true );
			if ( $cookie_value !== $comment_meta_hash ) {
				return false;
			}
		}

		// All is well, the person/place/thing can edit the comment
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
		return apply_filters( 'sce_can_edit', true, $comment, $comment_id, $post_id );
	} //end can_edit

	/**
	 * comment_posted - WordPress action comment_post
	 *
	 * Called when a comment has been posted - Stores a cookie for later editing
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id The Comment ID
	 */
	public function comment_posted( $comment_id ) {
		$comment        = get_comment( $comment_id, OBJECT );
		$post_id        = $comment->comment_post_ID;
		$post           = get_post( $post_id, OBJECT );
		$comment_status = $comment->comment_approved;

		// Do some initial checks to weed out those who shouldn't be able to have editable comments
		if ( 'spam' === $comment_status ) {
			return; // Marked as spam - no editing allowed
		}

		// Remove expired comments
		$this->remove_security_keys();

		$user_id = $this->get_user_id();

		// Don't set a cookie if a comment is posted via Ajax
		$cookie_bypass = apply_filters( 'sce_can_edit_cookie_bypass', false, $comment, $comment_id, $post_id, $user_id );

		// if we are logged in and are the comment author, bypass cookie check
		if ( 0 != $user_id && ( $post->post_author == $user_id || $comment->user_id == $user_id ) ) {
			$cookie_bypass = true;
			update_comment_meta( $comment_id, '_sce', 'post_author' );
		}
		if ( ! defined( 'DOING_AJAX' ) && ! defined( 'EPOCH_API' ) ) {
			if ( false === $cookie_bypass ) {
				$this->generate_cookie_data( $post_id, $comment_id, 'setcookie' );
			}
		}

	} //end comment_posted

	/**
	 * epoch_add_sce - Adds Simple Comment Editing to Epoch iFrame
	 *
	 * Adds Simple Comment Editing to Epoch iFrame
	 *
	 * @access public
	 * @since 1.5.0
	 *
	 * @param array $scripts Epoch Scripts Array
	 * @return array Added script
	 */
	public function epoch_add_sce( $scripts = array() ) {
		$scripts[] = 'jquery-core';
		$scripts[] = 'wp-ajax-response';
		$scripts[] = 'wp-hooks';
		$scripts[] = 'simple-comment-editing';
		return $scripts;
	} //end epoch_add_sce

	/**
	 * get_cookie_value - Return a cookie's value
	 *
	 * Return a cookie's value
	 *
	 * @access private
	 * @since 1.5.0
	 *
	 * @param string $name Cookie name
	 * @return string $value Cookie value
	 */
	private function get_cookie_value( $name ) {
		if ( isset( $_COOKIE[ $name ] ) ) {
			return $_COOKIE[ $name ];
		} else {
			return false;
		}
	}

	/**
	 * get_comment - Return a comment object
	 *
	 * Return a comment object
	 *
	 * @access private
	 * @since 1.5.0
	 *
	 * @param int $comment_id Comment ID
	 * @return obj Comment Object
	 */
	private function get_comment( $comment_id ) {
		if ( isset( $GLOBALS['comment'] ) ) {
			unset( $GLOBALS['comment'] );   // caching
		}
		$comment_to_return  = get_comment( $comment_id );
		$GLOBALS['comment'] = $comment_to_return;
		return $comment_to_return;
	}

	/**
	 * get_comment_content - Return a string of the comment's text
	 *
	 * Return formatted comment text
	 *
	 * @access private
	 * @since 1.5.0
	 *
	 * @param object $comment Comment Object
	 * @return string Comment text
	 */
	private function get_comment_content( $comment ) {
		$comment_content_to_return = $comment->comment_content;

		// Format the comment for returning
		if ( function_exists( 'mb_convert_encoding' ) ) {
			$comment_content_to_return = mb_convert_encoding( $comment_content_to_return, '' . get_option( 'blog_charset' ) . '', mb_detect_encoding( $comment_content_to_return, 'UTF-8, ISO-8859-1, ISO-8859-15', true ) );
		}
		return apply_filters( 'comment_text', apply_filters( 'get_comment_text', $comment_content_to_return, $comment, array() ), $comment, array() );
	}

	/**
	 * generate_cookie_data - Generate or remove a comment cookie
	 *
	 * Generate or remove a comment cookie - Stored as post meta
	 *
	 * @access public
	 * @since 1.5.0
	 *
	 * @param int    $post_id Post ID
	 * @param int    $comment_id Comment ID
	 * @param string $return_action 'ajax', 'setcookie, 'removecookie'
	 * @return JSON Array of cookie data only returned during Ajax requests
	 */
	public function generate_cookie_data( $post_id = 0, $comment_id = 0, $return_action = 'ajax' ) {
		if ( $return_action == 'ajax' ) {
			check_ajax_referer( 'sce-general-ajax-nonce' );
		}

		if ( $post_id == 0 ) {
			$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
		}

		// Get comment ID
		if ( $comment_id == 0 ) {
			$comment_id = isset( $_POST['comment_id'] ) ? absint( $_POST['comment_id'] ) : 0;
		}

		// Get hash and random security key - Stored in the style of Ajax Edit Comments
		$comment_author_ip = $comment_date_gmt = '';
		if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
					$comment_author_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
				$comment_author_ip = $_SERVER['REMOTE_ADDR'];
		}
		/**
		 * Filter: sce_pre_comment_user_ip
		 *
		 * Whether to use the IP filter (true by default)
		 *
		 * @since 2.7.1
		 *
		 * @param bool  true to use the comment IP filter.
		 */
		if ( apply_filters( 'sce_pre_comment_user_ip', true ) ) {
			// Props: https://github.com/timreeves.
			$comment_author_ip = apply_filters( 'pre_comment_user_ip', $comment_author_ip );
		}
		$comment_date_gmt = current_time( 'Y-m-d', 1 );
		$user_agent       = substr( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '', 0, 254 );
		$hash             = md5( $comment_author_ip . $comment_date_gmt . $this->get_user_id() . $user_agent );

		$rand            = '_wpAjax' . $hash . md5( wp_generate_password( 30, true, true ) ) . '-' . time();
		$maybe_save_meta = get_comment_meta( $comment_id, '_sce', true );
		$cookie_name     = 'SimpleCommentEditing' . $comment_id . $hash;
		$cookie_value    = $rand;
		$cookie_expire   = time() + ( 60 * $this->comment_time );

		if ( ! $maybe_save_meta ) {
			// Make sure we don't set post meta again for security reasons and subsequent calls to this method will generate a new key, so no calling it twice unless you want to remove a cookie
			update_comment_meta( $comment_id, '_sce', $rand );
		} else {
			// Kinda evil, but if you try to call this method twice, removes the cookie
			setcookie( $cookie_name, $cookie_value, time() - 60, COOKIEPATH, COOKIE_DOMAIN );
			die( json_encode( array() ) );
		}

		// Now store a cookie
		if ( 'setcookie' == $return_action ) {
			setcookie( $cookie_name, $cookie_value, $cookie_expire, COOKIEPATH, COOKIE_DOMAIN );
		} elseif ( 'removecookie' == $return_action ) {
			setcookie( $cookie_name, $cookie_value, time() - 60, COOKIEPATH, COOKIE_DOMAIN );
		}

		$return = array(
			'name'       => $cookie_name,
			'value'      => $cookie_value,
			'expires'    => ( time() + ( 60 * $this->comment_time ) ) * 1000,
			'post_id'    => $post_id,
			'comment_id' => $comment_id,
			'path'       => COOKIEPATH,
		);
		if ( 'ajax' == $return_action ) {
			die( json_encode( $return ) );
			exit();
		} else {
			return;
		}
		die( '' ); // Should never reach this point, but just in case I suppose
	}


	/**
	 * get_comment_time - Gets the comment time for editing
	 *
	 * @since 1.3.0
	 */
	public function get_comment_time() {
		if ( $this->comment_time > 0 ) {
			return $this->comment_time;
		}

		$time_do_edit = Options::get_options( false, 'timer' );
		/**
		* Filter: sce_comment_time
		*
		* How long in minutes to edit a comment
		*
		* @since 1.0.0
		*
		* @param int  $minutes Time in minutes
		*/
		$comment_time       = absint( apply_filters( 'sce_comment_time', $time_do_edit ) );
		$this->comment_time = $comment_time;
		return $this->comment_time;
	}


	public function get_plugin_dir( $path = '' ) {
		$dir = rtrim( plugin_dir_path( __FILE__ ), '/' );
		if ( ! empty( $path ) && is_string( $path ) ) {
			$dir .= '/' . ltrim( $path, '/' );
		}
		return $dir;
	}
	// Returns the plugin url
	public function get_plugin_url( $path = '' ) {
		$dir = rtrim( plugin_dir_url( __FILE__ ), '/' );
		if ( ! empty( $path ) && is_string( $path ) ) {
			$dir .= '/' . ltrim( $path, '/' );
		}
		return $dir;
	}

	/**
	 * get_user_id - Get a user ID
	 *
	 * Get a logged in user's ID
	 *
	 * @access private
	 * @since 1.5.0
	 *
	 * @return int user id
	 */
	private function get_user_id() {
		$user_id = 0;
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;
		}
		return $user_id;
	}

	/**
	 * maybe_load_scripts - Whether to load scripts or not
	 *
	 * Called via the sce_load_scripts filter
	 *
	 * @since 1.5.0
	 *
	 * @param bool $yes True or False
	 *
	 * @return bool True to load scripts, false if not
	 */
	public function maybe_load_scripts( $yes ) {
		if ( defined( 'WPAC_PLUGIN_NAME' ) || defined( 'EPOCH_VER' ) || defined( 'EPOCH_VERSION' ) || is_user_logged_in() ) {
			return true;
		}

		/* Return True if user is logged in */
		if ( is_user_logged_in() ) {
			return true;
		}

		if ( ! isset( $_COOKIE ) || empty( $_COOKIE ) ) {
			return;
		}
		$has_cookie = false;
		foreach ( $_COOKIE as $cookie_name => $cookie_value ) {
			if ( substr( $cookie_name, 0, 20 ) == 'SimpleCommentEditing' ) {
				$has_cookie = true;
				break;
			}
		}
		return $has_cookie;
	}

	/**
	 * remove_comment_cookie - Removes a comment cookie
	 *
	 * Removes a comment cookie based on the passed comment
	 *
	 * @since 1.0
	 *
	 * @param associative array $comment The results from get_comment( $id, ARRAY_A )
	 */
	private function remove_comment_cookie( $comment ) {
		if ( ! is_array( $comment ) ) {
			return;
		}

		$this->generate_cookie_data( $comment['comment_post_ID'], $comment['comment_ID'], 'removecookie' );

	} //end remove_comment_cookie

	/**
	 * remove_security_keys - Remove security keys
	 *
	 * When a comment is posted, remove security keys
	 *
	 * @access private
	 * @since 2.0.2
	 */
	private function remove_security_keys() {

		$sce_security = get_transient( 'sce_security_keys' );
		if ( ! $sce_security ) {

			// Remove old SCE keys
			$security_key_count = get_option( 'ajax-edit-comments_security_key_count' );
			if ( $security_key_count ) {
				global $wpdb;
				delete_option( 'ajax-edit-comments_security_key_count' );
				$wpdb->query( "delete from {$wpdb->postmeta} where left(meta_value, 7) = '_wpAjax' ORDER BY {$wpdb->postmeta}.meta_id ASC" );
			}
			// Delete expired meta
			global $wpdb;
			$query = $wpdb->prepare( "delete from {$wpdb->commentmeta} where meta_key = '_sce' AND CAST( SUBSTRING(meta_value, LOCATE('-',meta_value ) +1 ) AS UNSIGNED) < %d", time() - ( $this->comment_time * MINUTE_IN_SECONDS ) );
			$wpdb->query( $query );
			set_transient( 'sce_security_keys', true, HOUR_IN_SECONDS );
		}
	}
	/**
	 * Send Mailchimp when a comment has been posted.
	 *
	 * @param int  $comment_id Comment ID that has been submitted.
	 * @param bool $maybe_comment_approved Whether the comment is approved or not (1, 0, spam).
	 */
	public function comment_posted_mailchimp( $comment_id, $maybe_comment_approved ) {
		$signup_enabled = (bool) filter_input( INPUT_POST, 'sce-mailchimp-signup', FILTER_VALIDATE_BOOLEAN );

		if ( $signup_enabled && 'spam' !== $maybe_comment_approved ) {
			// Get the comment.
			$comment         = get_comment( $comment_id );
			$commenter_email = $comment->comment_author_email;

			$subscriber_added = $this->add_subscriber( $comment_id, $commenter_email, $comment );
		}
	}

	/**
	 * Add a subscriber to mailchimp.
	 *
	 * @param int        $comment_id The comment ID.
	 * @param string     $email      The email address.
	 * @param WP_Comment $comment    The comment object.
	 */
	private function add_subscriber( $comment_id, $email, $comment ) {
		if ( ! is_email( $email ) ) {
			return false;
		}

		$options = Options::get_options();
		$list    = $options['mailchimp_selected_list'] ?? '';
		if ( empty( $list ) ) {
			return false;
		}

		// Format API url for a server prefix..
		$mailchimp_api_url = str_replace(
			'<sp>',
			$options['mailchimp_api_key_server_prefix'],
			$this->mailchimp_api
		);

		$commenter_name    = $comment->comment_author;
		$mailchimp_api_key = $options['mailchimp_api_key'];

		$endpoint = $mailchimp_api_url . 'lists/' . $list . '/members/';

		// Start building up HTTP args.
		$http_args            = array();
		$http_args['headers'] = array(
			'Authorization' => 'Bearer ' . $mailchimp_api_key,
			'Accept'        => 'application/json;ver=1.0',
		);
		$http_args['body']    = wp_json_encode(
			array(
				'email_address' => $email,
				'status'        => 'pending',
				'merge_fields'  => array(
					'FNAME' => $commenter_name,
				),
			)
		);
		$response             = wp_remote_post( esc_url_raw( $endpoint ), $http_args );
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			// Response code can be 400 if the member already exists.
			return false;
		}

		// Now format response from JSON.
		$response_array = json_decode( wp_remote_retrieve_body( $response ), true );
		// Save subscription ID to comment meta.
		add_comment_meta( $comment_id, 'sce_mailchimp_id', $response_array['id'] );
		return true;
	}

	/**
	 * Add an subscribe option below the comment textarea and above the submit button.
	 *
	 * @param array $comment_fields Array of defaults for the form field.
	 */
	public function add_mailchimp_checkbox( $comment_fields ) {
		$options           = Options::get_options();
		$mailchimp_enabled = (bool) $options['enable_mailchimp'];

		// Chceck to see if Mailchimp is enabled.
		if ( ! $mailchimp_enabled || current_user_can( 'moderate_comments' ) ) {
			return $comment_fields;
		}

		// Now get the checkbox details.
		$checked_by_default              = (bool) $options['mailchimp_checkbox_enabled'];
		$mailchimp_html                  = array(
			'sce-mailchimp' => sprintf(
				'<section class="comment-form-sce-mailchimp"><label><input type="checkbox" name="sce-mailchimp-signup" %s /> %s</label></section>',
				checked( $checked_by_default, true, false ),
				esc_html( $options['mailchimp_signup_label'] )
			),
		);
		$comment_fields['submit_button'] = $mailchimp_html['sce-mailchimp'] . $comment_fields['submit_button'];
		return $comment_fields;
	}

} //end class Simple_Comment_Editing

add_action( 'plugins_loaded', 'sce_instantiate' );
function sce_instantiate() {
	Simple_Comment_Editing::get_instance();
	if ( is_admin() && apply_filters( 'sce_show_admin', true ) ) {
		new SCE\Includes\Admin\Admin_Settings();
		$sce_enqueue = new SCE\Includes\Enqueue();
		$sce_enqueue->run();
	}

	if ( apply_filters( 'sce_show_admin', true ) ) {

	}
} //end sce_instantiate


register_activation_hook( Functions::get_plugin_file(), 'sce_plugin_activate' );
add_action( 'admin_init', 'sce_plugin_activate_redirect' );

/**
 * Add an option upon activation to read in later when redirecting.
 */
function sce_plugin_activate() {
	if ( ! Functions::is_multisite() ) {
		add_option( 'comment-edit-lite-activate', sanitize_text_field( Functions::get_plugin_file() ) );
	}
}

/**
 * Redirect to Comment Edit Lite settings page upon activation.
 */
function sce_plugin_activate_redirect() {

	// If on multisite, bail.
	if ( Functions::is_multisite() ) {
		return;
	}

	// Make sure we're in the admin and that the option is available.
	if ( is_admin() && Functions::get_plugin_file() === get_option( 'comment-edit-lite-activate' ) ) {
		delete_option( 'comment-edit-lite-activate' );
		// GEt bulk activation variable if it exists.
		$maybe_multi = filter_input( INPUT_GET, 'activate-multi', FILTER_VALIDATE_BOOLEAN );

		// Return early if it's a bulk activation.
		if ( $maybe_multi ) {
			return;
		}

		$settings_url = admin_url( 'options-general.php?page=comment-edit-lite' );
		if ( class_exists( '\CommentEditPro\Comment_Edit_Pro' ) ) {
			$settings_url = admin_url( 'options-general.php?page=comment-edit-pro' );
		}
		wp_safe_redirect( esc_url( $settings_url ) );
		exit;
	}
}
