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
	} //end init
	
	/**
	 * comment_posted - WordPress action comment_post
	 * 
	 * Called when a comment has been posted
	 *
	 * @since 1.0
	 *
	 * @param int $comment_id The Comment ID
	 * @return   type                Description
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
		$min_security_keys = absint( apply_filters( 'sce_security_key_min', 10 ) );
		if ( $security_key_count >= $min_security_keys ) {
			global $wpdb;
			$comment_id_to_exclude = "_" . $comment_id;
			@$wpdb->query( $wpdb->prepare( "delete from {$wpdb->postmeta} where left(meta_value, 6) = 'wpAjax' and meta_key <> %s", $comment_id_to_exclude ) );
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