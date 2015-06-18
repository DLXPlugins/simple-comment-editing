<?php
/*
Plugin Name: Simple Comment Editing
Plugin URI: http://wordpress.org/extend/plugins/simple-comment-editing/
Description: Simple comment editing for your users.
Author: ronalfy
Version: 1.3.0
Requires at least: 3.5
Author URI: http://www.ronalfy.com
Contributors: ronalfy
Text Domain: simple-comment-editing
Domain Path: /languages
*/ 
class Simple_Comment_Editing {
	private static $instance = null;
	private $comment_time = 0; //in minutes
	private $loading_img = '';
	private $allow_delete = true;
	private $errors;
	
	//Singleton
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	} //end get_instance
	
	private function __construct() {
		add_action( 'init', array( $this, 'init' ), 9 );
		
		//* Localization Code */
		load_plugin_textdomain( 'simple-comment-editing', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		//Initialize errors
		$this->errors = new WP_Error();
		$this->errors->add( 'nonce_fail', __( 'You do not have permission to edit this comment.', 'simple-comment-editing' ) );
		$this->errors->add( 'edit_fail', __( 'You can no longer edit this comment', 'simple-comment-editing' ) );
		$this->errors->add( 'comment_empty', __( 'Your comment cannot be empty', 'simple-comment-editing' ) );
		$this->errors->add( 'comment_marked_spam', __( 'This comment was marked as spam', 'simple-comment-editing' ) );
	} //end constructor
	
	public function init() {
		
		if ( is_admin() && !defined( 'DOING_AJAX' ) ) return false;
		
		//Set plugin defaults
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
		$this->allow_delete = (bool)apply_filters( 'sce_allow_delete', $this->allow_delete );
		
		/* BEGIN ACTIONS */
		//When a comment is posted
		add_action( 'comment_post', array( $this, 'comment_posted' ),100,1 );
		
		//Loading scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'add_scripts' ) );
		
		//Ajax
		add_action( 'wp_ajax_sce_get_time_left', array( $this, 'ajax_get_time_left' ) );
		add_action( 'wp_ajax_nopriv_sce_get_time_left', array( $this, 'ajax_get_time_left' ) );
		add_action( 'wp_ajax_sce_save_comment', array( $this, 'ajax_save_comment' ) );
		add_action( 'wp_ajax_nopriv_sce_save_comment', array( $this, 'ajax_save_comment' ) );
		add_action( 'wp_ajax_sce_delete_comment', array( $this, 'ajax_delete_comment' ) );
		add_action( 'wp_ajax_nopriv_sce_delete_comment', array( $this, 'ajax_delete_comment' ) );
		
		/* Begin Filters */
		if ( !is_feed() && !defined( 'DOING_AJAX' ) ) {
			add_filter( 'comment_excerpt', array( $this, 'add_edit_interface'), 1000, 2 );
			add_filter( 'comment_text', array( $this, 'add_edit_interface'), 1000,2 );
			//Notice Thesis compatibility not here?  It's not an accident.
		}
	} //end init
	
	/**
	 * add_edit_interface - Adds the SCE interface if a user can edit their comment
	 * 
	 * Called via the comment_text or comment_excerpt filter to add the SCE editing interface to a comment.
	 *
	 * @since 1.0
	 *
	 */
	public function add_edit_interface( $comment_content, $comment = false) {
		if ( !$comment ) return $comment_content;
				
		$comment_id = absint( $comment->comment_ID );
		$post_id = absint( $comment->comment_post_ID );
		
		//Check to see if a user can edit their comment
		if ( !$this->can_edit( $comment_id, $post_id ) ) return $comment_content;
		
		//Variables for later
		$original_content = $comment_content;
		$raw_content = $comment->comment_content; //For later usage in the textarea
		
		//Yay, user can edit - Add the initial wrapper
		$comment_wrapper = sprintf( '<div id="sce-comment%d" class="sce-comment">%s</div>', $comment_id, $comment_content );	
		
		//Create Overall wrapper for JS interface
		$sce_content = sprintf( '<div id="sce-edit-comment%d" class="sce-edit-comment">', $comment_id );
		
		//Edit Button
		$sce_content .= '<div class="sce-edit-button" style="display:none;">';
		$ajax_edit_url = add_query_arg( array( 'cid' => $comment_id, 'pid' => $post_id ) , wp_nonce_url( admin_url( 'admin-ajax.php' ), 'sce-edit-comment' . $comment_id ) );
		$sce_content .= sprintf( '<a href="%s">%s</a>', esc_url( $ajax_edit_url ), esc_html__( 'Click to Edit', 'simple-comment-editing' ) );
		$sce_content .= '<span class="sce-timer"></span>';
		$sce_content .= '</div><!-- .sce-edit-button -->';
		
		//Loading button
		$sce_content .= '<div class="sce-loading" style="display: none;">';
		$sce_content .= sprintf( '<img src="%1$s" title="%2$s" alt="%2$s" />', esc_url( $this->loading_img ), esc_attr__( 'Loading', 'simple-comment-editing' ) );
		$sce_content .= '</div><!-- sce-loading -->';
		
		//Textarea
		$textarea_content = '<div class="sce-textarea" style="display: none;">';
		$textarea_content .= '<div class="sce-comment-textarea">';
		$textarea_content .= sprintf( '<textarea class="sce-comment-text" cols="45" rows="8">%s</textarea>', esc_textarea( $raw_content ) );
		$textarea_content .= '</div><!-- .sce-comment-textarea -->';
		$textarea_content .= '%s</div><!-- .sce-textarea -->';
		$textarea_button_content = '<div class="sce-comment-edit-buttons">';
		$textarea_buttons = sprintf( '<button class="sce-comment-save">%s</button>', esc_html__( 'Save', 'simple-comment-editing' ) );
		$textarea_buttons .= sprintf( '<button class="sce-comment-cancel">%s</button>', esc_html__( 'Cancel', 'simple-comment-editing' ) );
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
		$textarea_buttons = apply_filters( 'sce_buttons', $textarea_buttons, $comment_id );
		$textarea_button_content .= $textarea_buttons . '</div><!-- .sce-comment-edit-buttons -->';
		$textarea_content = sprintf( $textarea_content, $textarea_button_content );
		
		
		//End
		$sce_content .= $textarea_content . '</div><!-- .sce-edit-comment -->';
		
		//Status Area
		$sce_content .= sprintf( '<div id="sce-edit-comment-status%d" class="sce-status" style="display: none;"></div><!-- .sce-status -->', $comment_id );
		
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
		$sce_content = apply_filters( 'sce_content', $sce_content, $comment_id );
		
		//Return content
		$comment_content = $comment_wrapper . $sce_content;
		return $comment_content;
	
	} //end add_edit_interface
	
	/**
	 * add_scripts - Adds the necessary JavaScript for the plugin (only loads on posts/pages)
	 * 
	 * Called via the wp_enqueue_scripts
	 *
	 * @since 1.0
	 *
	 */
	 public function add_scripts() {
	 	if ( !is_single() && !is_page() ) return;
	 	
	 	//Check if there are any cookies present, otherwise don't load the scripts - WPAC_PLUGIN_NAME is for wp-ajaxify-comments (if the plugin is installed, load the JavaScript file)
	 	if ( !defined( 'WPAC_PLUGIN_NAME' ) ) {
		 	if ( !isset( $_COOKIE ) || empty( $_COOKIE ) ) return;
		 	$has_cookie = false;
		 	foreach( $_COOKIE as $cookie_name => $cookie_value ) {
		 		if ( substr( $cookie_name , 0, 20 ) == 'SimpleCommentEditing' ) {
					$has_cookie = true;
					break;	 		
		 		}
		 	}
		 	if ( !$has_cookie ) return;
		 }
	 	
	 	$main_script_uri = $this->get_plugin_url( '/js/simple-comment-editing.min.js' );
	 	if ( defined( 'SCRIPT_DEBUG' ) ) {
	 		if ( SCRIPT_DEBUG == true ) {
	 			$main_script_uri = $this->get_plugin_url( '/js/simple-comment-editing.js' );
	 		}
	 	}
	 	include( 'class-sce-timer.php' );
	 	$timer_internationalized = new SCE_Timer();
	 	wp_enqueue_script( 'simple-comment-editing', $main_script_uri, array( 'jquery', 'wp-ajax-response' ), '20150618', true );
	 	wp_localize_script( 'simple-comment-editing', 'simple_comment_editing', array(
	 		'and' => __( 'and', 'simple-comment-editing' ),
	 		'confirm_delete' => __( 'Do you want to delete this comment?', 'simple-comment-editing' ),
	 		'comment_deleted' => __( 'Your comment has been removed.', 'simple-comment-editing' ),
	 		'empty_comment' => $this->errors->get_error_message( 'comment_empty' ),
	 		'allow_delete' => $this->allow_delete,
	 		'timer' => $timer_internationalized->get_timer_vars()
	 	) );
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
	 	global $wpdb;
	 	$comment_id = absint( $_POST[ 'comment_id' ] );
	 	$post_id = absint( $_POST[ 'post_id' ] );
	 	
	 	//Check if user can edit comment
	 	if ( !$this->can_edit( $comment_id, $post_id ) ) {
	 		$response = array(
	 			'minutes' => 0,
	 			'seconds' => 0,
	 			'comment_id' => 0,
	 			'can_edit' => false
	 		);
	 		die( json_encode( $response ) );
	 	}
	 	
	 	$comment_time = absint( $this->comment_time );
	 	$query = $wpdb->prepare( "SELECT ( $comment_time * 60 - (UNIX_TIMESTAMP('" . current_time('mysql') . "') - UNIX_TIMESTAMP(comment_date))) comment_time FROM {$wpdb->comments} where comment_ID = %d", $comment_id );
	 	$comment_time_result = $wpdb->get_row( $query, ARRAY_A );
	 	
	 	$time_left = absint( $comment_time_result[ 'comment_time' ] );
	 	$minutes = floor( $time_left / 60 );
		$seconds = $time_left - ( $minutes * 60 );
		$response = array(
			'minutes' => $minutes,
			'comment_id' => $comment_id, 
			'seconds' => $seconds,
			'can_edit' => true
			
		);
		die( json_encode( $response ) );
	 } //end ajax_get_time_left
	 
	 /**
	 * ajax_delete_comment- Removes a WordPress comment, but saves it to the trash
	 * 
	 * Returns a JSON object of the saved comment
	 *
	 * @since 1.1.0
	 *
	 * @param int $_POST[ 'comment_id' ] The Comment ID
	 * @param int $_POST[ 'post_id' ] The Comment's Post ID
	 * @param string $_POST[ 'nonce' ] The nonce to check against
	 * @return JSON object 
	 */
	 public function ajax_delete_comment() {
	 	$comment_id = absint( $_POST[ 'comment_id' ] );
	 	$post_id = absint( $_POST[ 'post_id' ] );
	 	$nonce = $_POST[ 'nonce' ];
	 	
	 	$return = array();
	 	$return[ 'errors' ] = false;
	 	
	 	//Do a nonce check
	 	if ( !wp_verify_nonce( $nonce, 'sce-edit-comment' . $comment_id ) ) {
	 		$return[ 'errors' ] = true;
	 		$return[ 'remove' ] = true;
	 		$return[ 'error' ] = $this->errors->get_error_message( 'nonce_fail' );
	 		die( json_encode( $return ) );
	 	}
	 	
	 	//Check to see if the user can edit the comment
	 	if ( !$this->can_edit( $comment_id, $post_id ) || $this->allow_delete == false ) {
	 		$return[ 'errors' ] = true;
	 		$return[ 'remove' ] = true;
	 		$return[ 'error' ] = $this->errors->get_error_message( 'edit_fail' );
	 		die( json_encode( $return ) );
	 	}	
	 	
	 	wp_delete_comment( $comment_id ); //Save to trash for admin retrieval
	 	$return[ 'error' ] = '';
		die( json_encode( $return ) );
	 } //end ajax_delete_comment
	 
	 /**
	 * ajax_save_comment - Saves a comment to the database, returns the updated comment via JSON
	 * 
	 * Returns a JSON object of the saved comment
	 *
	 * @since 1.0
	 *
	 * @param string $_POST[ 'comment_content' ] The comment to save
	 * @param int $_POST[ 'comment_id' ] The Comment ID
	 * @param int $_POST[ 'post_id' ] The Comment's Post ID
	 * @param string $_POST[ 'nonce' ] The nonce to check against
	 * @return JSON object 
	 */
	 public function ajax_save_comment() {
	 	$new_comment_content = trim( $_POST[ 'comment_content' ] );
	 	$comment_id = absint( $_POST[ 'comment_id' ] );
	 	$post_id = absint( $_POST[ 'post_id' ] );
	 	$nonce = $_POST[ 'nonce' ];
	 	
	 	$return = array();
	 	$return[ 'errors' ] = false;
	 	$return[ 'remove' ] = false; //If set to true, removes the editing interface
	 	
	 	//Do a nonce check
	 	if ( !wp_verify_nonce( $nonce, 'sce-edit-comment' . $comment_id ) ) {
	 		$return[ 'errors' ] = true;
	 		$return[ 'remove' ] = true;
	 		$return[ 'error' ] = $this->errors->get_error_message( 'nonce_fail' );
	 		die( json_encode( $return ) );
	 	}	
	 	
	 	//Check to see if the user can edit the comment
	 	if ( !$this->can_edit( $comment_id, $post_id ) ) {
	 		$return[ 'errors' ] = true;
	 		$return[ 'remove' ] = true;
	 		$return[ 'error' ] = $this->errors->get_error_message( 'edit_fail' );
	 		die( json_encode( $return ) );
	 	}
	 	
	 	//Check that the content isn't empty
	 	if ( '' == $new_comment_content || 'undefined' == $new_comment_content ) {
	 		$return[ 'errors' ] = true;
	 		$return[ 'error' ] = $this->errors->get_error_message( 'comment_empty' );
	 		die( json_encode( $return ) );
	 	}
	 	
	 	//Get original comment
	 	$comment_to_save = get_comment( $comment_id, ARRAY_A);
	 	
	 	//Check the comment
	 	if ( $comment_to_save['comment_approved'] == 1 ) {
			if ( check_comment( $comment_to_save['comment_author'], $comment_to_save['comment_author_email'], $comment_to_save['comment_author_url'], $new_comment_content, $comment_to_save['comment_author_IP'], $comment_to_save['comment_agent'], $comment_to_save['comment_type'] ) ) {
				$comment_to_save['comment_approved'] = 1;
			} else {
				$comment_to_save['comment_approved'] = 0;
			}						
		}
		
		//Check comment against blacklist
		if ( wp_blacklist_check( $comment_to_save['comment_author'], $comment_to_save['comment_author_email'], $comment_to_save['comment_author_url'], $new_comment_content, $comment_to_save['comment_author_IP'], $comment_to_save['comment_agent'] ) ) {
			$comment_to_save['comment_approved'] = 'spam';
		}
		
		//Update comment content with new content
		$comment_to_save[ 'comment_content' ] = $new_comment_content;
		
		//Before save comment
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
		$custom_error = apply_filters( 'sce_comment_check_errors', false, $comment_to_save ); //Filter expects a string returned - $comment_to_save is an associative array
		if ( is_string( $custom_error ) && !empty( $custom_error ) ) {
			$return[ 'errors' ] = true;
	 		$return[ 'error' ] = esc_html( $custom_error );
	 		die( json_encode( $return ) );		
		}
		
		//Save the comment
		wp_update_comment( $comment_to_save );
		
		//If the comment was marked as spam, return an error
		if ( $comment_to_save['comment_approved'] === 'spam' ) {
			$return[ 'errors' ] = true;
			$return[ 'remove' ] = true;
			$return[ 'error' ] = $this->errors->get_error_message( 'comment_marked_spam' );
			$this->remove_comment_cookie( $comment_to_save );
			die( json_encode( $return ) );
		}
		
		//Check the new comment for spam with Akismet
		if ( function_exists( 'akismet_check_db_comment' ) ) {
			if ( akismet_verify_key( get_option( 'wordpress_api_key' ) ) != "failed" ) { //Akismet
				$response = akismet_check_db_comment( $comment_id );
				if ($response == "true") { //You have spam
					wp_set_comment_status( $comment_id, 'spam');
					$return[ 'errors' ] = true;
					$return[ 'remove' ] = true;
					$return[ 'error' ] = $this->errors->get_error_message( 'comment_marked_spam' );
					$this->remove_comment_cookie( $comment_to_save );
					die( json_encode( $return ) );
				}
			}
		}
		
		//Now get the new comment again for security
		if ( isset( $GLOBALS['comment'] ) ) unset( $GLOBALS['comment'] );	//caching
		$comment_to_return = get_comment ( $comment_id ); //todo - cached
		$comment_content_to_return = $comment_to_return->comment_content;
		
		//Format the comment for returning
		if ( function_exists( 'mb_convert_encoding' ) ) {
			$comment_content_to_return = mb_convert_encoding( $comment_content_to_return, ''. get_option( 'blog_charset' ) . '', mb_detect_encoding( $comment_content_to_return, "UTF-8, ISO-8859-1, ISO-8859-15", true ) );
		}
		$comment_content_to_return = apply_filters( 'comment_text', apply_filters( 'get_comment_text', $comment_content_to_return ) );
		
		//Ajax response
		$return[ 'comment_text' ] = $comment_content_to_return;
		$return[ 'error' ] = '';
		die( json_encode( $return ) );
	 } //end ajax_save_comment
	 
	 
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
		global $comment;
		if ( !is_object( $comment ) ) $comment = get_comment( $comment_id, OBJECT );
		
		//Check to see if time has elapsed for the comment
		$comment_timestamp = strtotime( $comment->comment_date );
		$time_elapsed = current_time( 'timestamp', get_option( 'gmt_offset' ) ) - $comment_timestamp;
		$minuted_elapsed = round( ( ( ( $time_elapsed % 604800 ) % 86400 )  % 3600 ) / 60 );
		if ( ( $minuted_elapsed - $this->comment_time ) > 0 ) return false;
		
		//Now check to see if the cookie is present
		if ( !isset( $_COOKIE ) || !is_array( $_COOKIE ) || empty( $_COOKIE ) ) return false;
		
		//Now check for post meta and cookie values being the same
		$cookie_hash = md5( $comment->comment_author_IP . $comment->comment_date_gmt );
		if ( !isset( $_COOKIE[ 'SimpleCommentEditing' . $comment_id . $cookie_hash] ) ) return false;
		$post_meta_hash = get_post_meta( $post_id, '_' . $comment_id, true );
		
		
		
		//Check to see if the cookie value matches the post meta hash
		$cookie_value = $_COOKIE[ 'SimpleCommentEditing' . $comment_id . $cookie_hash ];
		if ( $cookie_value !== $post_meta_hash ) return false;
		
		//All is well, the person/place/thing can edit the comment
		return true;
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
		$comment = get_comment( $comment_id, OBJECT );
		$post_id = $comment->comment_post_ID;
		$comment_status = $comment->comment_approved;
		
		//Do some initial checks to weed out those who shouldn't be able to have editable comments
		if ( 'spam' === $comment_status ) return; //Marked as spam - no editing allowed
		//if ( current_user_can( 'moderate_comments' ) ) return; //They can edit comments anyway, don't do anything
		//if ( current_user_can( 'edit_post', $post_id ) ) return; //Post author - User can edit comments for the post anyway
		
		//Get hash and random security key - Stored in the style of Ajax Edit Comments
		$hash = md5( $comment->comment_author_IP . $comment->comment_date_gmt );
		$rand = '_wpAjax' . $hash . md5( wp_generate_password( 30, true, true ) );
		update_post_meta( $post_id, '_' . $comment_id, $rand );
		
		//Now store a cookie
		$cookie_name = 'SimpleCommentEditing' . $comment_id . $hash;
		$cookie_value = $rand;
		$cookie_expire = time() + (  60 * $this->comment_time );
		setcookie( $cookie_name, $cookie_value, $cookie_expire, COOKIEPATH,COOKIE_DOMAIN);
		
		//Update the security key count (use the same names/techniques as Ajax Edit Comments
		$security_key_count = absint( get_option( 'ajax-edit-comments_security_key_count' ) ); 
		if ( !$security_key_count ) {
			$security_key_count = 1;
		} else {
			$security_key_count += 1;
		}
		
		//Now delete security keys (use the same names/techniques as Ajax Edit Comments
		/**
		* Filter: sce_security_key_min
		*
		* Determine how many security keys should be stored as post meta before garbage collection
		*
		* @since 1.0.0
		*
		* @param int  $num_keys How many keys to store
		*/
		$min_security_keys = absint( apply_filters( 'sce_security_key_min', 100 ) );
		if ( $security_key_count >= $min_security_keys ) {
			global $wpdb;
			$comment_id_to_exclude = "_" . $comment_id;
			/* Only delete the first 50 to make sure the bottom 50 aren't suddenly without to the ability to edit comments - Props Marco Pereirinha */
			$wpdb->query( $wpdb->prepare( "delete from {$wpdb->postmeta} where left(meta_value, 6) = 'wpAjax' and meta_key <> %s ORDER BY {$wpdb->postmeta}.meta_id ASC LIMIT 50 ", $comment_id_to_exclude ) ); 
			$security_key_count = 1;
		}
		update_option( 'ajax-edit-comments_security_key_count', $security_key_count );
	} //end comment_posted
	
	
	/**
	 * get_comment_time - Gets the comment time for editing - max 90 minutes
	 * 
	 *
	 * @since 1.3.0
	 *
	 */
	 public function get_comment_time() {
		 if ( $this->comment_time > 0 ) {
			return $this->comment_time;	 
		}
		/**
		* Filter: sce_comment_time
		*
		* How long in minutes to edit a comment
		*
		* @since 1.0.0
		*
		* @param int  $minutes Time in minutes - Max 90 minutes
		*/
		$comment_time = absint( apply_filters( 'sce_comment_time', 5 ) );
		if ( $comment_time > 90 ) {
			$this->comment_time = 90; 	
		} else {
			$this->comment_time = $comment_time;
		}
		return $this->comment_time;
	}
	
	
	public function get_plugin_dir( $path = '' ) {
		$dir = rtrim( plugin_dir_path(__FILE__), '/' );
		if ( !empty( $path ) && is_string( $path) )
			$dir .= '/' . ltrim( $path, '/' );
		return $dir;		
	}
	//Returns the plugin url
	public function get_plugin_url( $path = '' ) {
		$dir = rtrim( plugin_dir_url(__FILE__), '/' );
		if ( !empty( $path ) && is_string( $path) )
			$dir .= '/' . ltrim( $path, '/' );
		return $dir;	
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
		if ( !is_array( $comment ) ) return;
		
		$hash = md5( $comment[ 'comment_author_IP' ] . $comment[ 'comment_date_gmt' ] );
		$comment_id = $comment[ 'comment_ID' ];
		
		//Expire the cookie
		$cookie_name = 'SimpleCommentEditing' . $comment_id . $hash;
		setcookie( $cookie_name, '', time() - 60, COOKIEPATH,COOKIE_DOMAIN);
	
	} //end remove_comment_cookie
	
} //end class Simple_Comment_Editing

add_action( 'plugins_loaded', 'sce_instantiate' );
function sce_instantiate() {
	Simple_Comment_Editing::get_instance();
} //end sce_instantiate