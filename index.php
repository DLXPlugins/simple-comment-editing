<?php
/*
Plugin Name: Simple Comment Editing
Plugin URI: http://wordpress.org/extend/plugins/simple-comment-editing/
Description: Simple comment editing for your users.
Author: ronalfy
Version: 1.0.0
Requires at least: 3.5
Author URI: http://www.ronalfy.com
Contributors: ronalfy
*/ 
class Simple_Comment_Editing {
	private static $instance = null;
	private $comment_time = 0; //in minutes
	private $loading_img = '';
	
	//Singleton
	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	} //end get_instance
	
	private function __construct() {
		add_action( 'init', array( $this, 'init' ), 9 );
	} //end constructor
	
	public function init() {
		//* Localization Code */
		load_plugin_textdomain( 'sce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		//Set plugin defaults
		$this->comment_time = intval( apply_filters( 'sce_comment_time', 5 ) );
		$this->loading_img = esc_url( apply_filters( 'sce_loading_img', $this->get_plugin_url( '/images/loading.png' ) ) );
		
		/* BEGIN ACTIONS */
		//When a comment is posted
		add_action( 'comment_post', array( $this, 'comment_posted' ),100,1 );
		
		/* Begin Filters */
		if ( !is_feed() ) {
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
		
		$comment_id = $comment->comment_ID;
		$post_id = $comment->comment_post_ID;
		
		//Check to see if a user can edit their comment
		if ( !$this->can_edit( $comment_id, $post_id ) ) return $comment_content;
		
		$raw_content = $comment_content; //For later usage in the textarea
		
		//Yay, user can edit - Add the initial wrapper
		$comment_content = sprintf( '<div id="sce-comment%d" class="sce-comment">%s</div>', $comment_id, $comment_content );		
		
		//Create Overall wrapper for JS interface
		$comment_content .= sprintf( '<div id="sce-edit-comment%d" class="sce-edit-comment">', $comment_id );
		
		//Edit Button
		$comment_content .= '<div class="sce-edit-button">';
		$ajax_edit_url = add_query_arg( array( 'cid' => $comment_id, 'pid' => $post_id ) , wp_nonce_url( admin_url( 'admin-ajax.php', 'sce-edit-comment' ) ) );
		$comment_content .= sprintf( '<a href="%s">%s</a>', $ajax_edit_url, esc_html__( 'Click to Edit', 'sce' ) );
		$comment_content .= '<span class="sce-timer"></span>';
		$comment_content .= '</div><!-- .sce-edit-button -->';
		
		//Loading button
		$comment_content .= '<div class="sce-loading">';
		$comment_content .= sprintf( '<img src="%1$s" title="%2$s" alt="%2$s" />', $this->loading_img, esc_attr__( 'Loading', 'sce' ) );
		$comment_content .= '</div><!-- sce-loading -->';
		
		
		
		
		$comment_content .= '</div><!-- .sce-edit-comment -->';
		
		return $comment_content;
	
	} //end add_edit_interface
	
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
		//Perform a foreach on the cookie global to see if SimpleCommentEditing is present - processes on the server, but avoids a query if the cookie isn't set
		$is_cookie_present = false;
		foreach( $_COOKIE as $cookie_key => $cookie_value ) {
			if ( 'SimpleCommentEditing' == substr( $cookie_key, 0, 20 ) ) {
				$is_cookie_present = true;
				break;
			}
		}
		if ( !$is_cookie_present ) return false;
		
		//Now check for post meta and cookie values being the same
		$post_meta_hash = get_post_meta( $post_id, '_' . $comment_id, true );
		$cookie_hash = md5( $comment->comment_author_IP . $comment->comment_date_gmt );
		if ( !isset( $_COOKIE[ 'SimpleCommentEditing' . $comment_id . $cookie_hash] ) ) return false;
		
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
		if ( 'spam' == $comment_status ) return; //Marked as spam - no editing allowed
		if ( current_user_can( 'moderate_comments' ) ) return; //They can edit comments anyway, don't do anything
		if ( current_user_can( 'edit_post', $post_id ) ) return; //Post author - User can edit comments for the post anyway
		
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
	
} //end class Simple_Comment_Editing

add_action( 'plugins_loaded', 'sce_instantiate' );
function sce_instantiate() {
	Simple_Comment_Editing::get_instance();
} //end sce_instantiate