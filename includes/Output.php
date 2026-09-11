<?php
/**
 * Map plugin options onto Simple Comment Editing filters and actions.
 *
 * @package CommentEditLite
 */

namespace DLXPlugins\CommentEditLite;

if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access.' );
}

/**
 * Output SCE option values through existing filters.
 */
class Output {

	/**
	 * Main class constructor.
	 */
	public function __construct() {
		$this->init_filters();
		$this->init_actions();
	}

	/**
	 * Initialize SCE option filters.
	 *
	 * @since 3.4.0
	 */
	private function init_filters() {
		add_filter( 'sce_show_timer', array( $this, 'show_timer' ) );
		add_filter( 'sce_text_save', array( $this, 'save_button_text' ) );
		add_filter( 'sce_text_cancel', array( $this, 'save_cancel_text' ) );
		add_filter( 'sce_text_delete', array( $this, 'save_delete_text' ) );
		add_filter( 'sce_text_edit', array( $this, 'edit_text' ) );
		add_filter( 'sce_allow_delete_confirmation', array( $this, 'allow_delete_confirmation' ) );
		add_filter( 'sce_allow_delete', array( $this, 'allow_deletion' ) );
		add_filter( 'sce_confirm_delete', array( $this, 'message_confirm_delete' ) );
		add_filter( 'sce_comment_deleted', array( $this, 'message_comment_deleted' ) );
		add_filter( 'sce_comment_deleted_error', array( $this, 'message_comment_deleted_error' ) );
		add_filter( 'sce_empty_comment', array( $this, 'message_empty_comment' ) );
		add_filter( 'sce_unlimited_editing', array( $this, 'maybe_unlimited_editing' ), 10, 2 );
		add_filter( 'sce_force_delete', array( $this, 'force_delete' ), 10, 3 );
		add_filter( 'sce_can_edit_pre', array( $this, 'maybe_can_edit_pre' ), 10, 3 );
	}

	/**
	 * Initialize SCE option actions.
	 *
	 * @since 3.4.0
	 */
	private function init_actions() {
		add_action( 'trashed_comment', array( $this, 'maybe_delete_comment' ), 10, 2 );
		add_action( 'spammed_comment', array( $this, 'maybe_spam_and_delete_comment' ), 10, 2 );
	}

	/**
	 * Determine if a user can edit based on their logged-in status.
	 *
	 * @since 3.4.0
	 *
	 * @param bool   $can_edit Whether the user can edit.
	 * @param object $comment  The comment object.
	 * @param object $post     The post object.
	 * @return bool Whether the user can edit.
	 */
	public function maybe_can_edit_pre( $can_edit, $comment, $post ) {
		unset( $comment, $post );
		$require_login_for_editing = (bool) Options::get_options( false, 'require_login_for_editing' );

		if ( $require_login_for_editing && ! is_user_logged_in() ) {
			return false;
		}

		return $can_edit;
	}

	/**
	 * Maybe delete a comment permanently if the moderator setting is on.
	 *
	 * @since 3.4.0
	 *
	 * @param int         $comment_id The comment ID.
	 * @param \WP_Comment $comment    The comment object.
	 */
	public function maybe_delete_comment( $comment_id, $comment ) {
		unset( $comment );
		$trash_or_delete = Options::get_options( false, 'delete_behavior_moderators' );
		if ( 'delete' === $trash_or_delete && current_user_can( 'moderate_comments' ) ) {
			wp_delete_comment( $comment_id, true );
		}
	}

	/**
	 * Maybe delete a comment permanently after spamming it if the setting is on.
	 *
	 * @since 3.4.0
	 *
	 * @param int         $comment_id The comment ID.
	 * @param \WP_Comment $comment    The comment object.
	 */
	public function maybe_spam_and_delete_comment( $comment_id, $comment ) {
		unset( $comment );
		$maybe_delete = Options::get_options( false, 'spam_behavior_moderators' );
		if ( 'delete' === $maybe_delete && current_user_can( 'moderate_comments' ) ) {
			wp_delete_comment( $comment_id, true );
		}
	}

	/**
	 * Force delete a comment when a user deletes it.
	 *
	 * @since 3.4.0
	 *
	 * @param bool $force      Whether to force delete.
	 * @param int  $comment_id Comment ID.
	 * @param int  $post_id    Post ID.
	 * @return bool Whether to force delete.
	 */
	public function force_delete( $force, $comment_id, $post_id ) {
		unset( $comment_id, $post_id );
		$trash_or_delete = Options::get_options( false, 'delete_behavior_users' );
		if ( 'delete' === $trash_or_delete ) {
			return true;
		}
		return $force;
	}

	/**
	 * Maybe allow unlimited editing for the logged-in comment author.
	 *
	 * @since 3.4.0
	 *
	 * @param bool   $unlimited Whether to allow unlimited comment editing.
	 * @param object $comment   Comment object.
	 * @return bool Whether to allow unlimited editing.
	 */
	public function maybe_unlimited_editing( $unlimited, $comment ) {
		if ( ! is_user_logged_in() ) {
			return false;
		}

		$allow_unlimited_editing = (bool) Options::get_options( false, 'allow_unlimited_editing' );
		if ( true !== $allow_unlimited_editing ) {
			return $unlimited;
		}

		if ( ! is_object( $comment ) ) {
			return $unlimited;
		}

		global $current_user;
		$user_id = absint( $current_user->ID );
		if ( absint( $comment->user_id ) === $user_id ) {
			return true;
		}

		return false;
	}

	/**
	 * Return an error when a comment is empty.
	 *
	 * @since 3.4.0
	 *
	 * @param string $message Empty comment error.
	 * @return string New empty comment error.
	 */
	public function message_empty_comment( $message ) {
		return $this->get_text_option( 'comment_empty_error', $message );
	}

	/**
	 * Return a delete error message.
	 *
	 * @since 3.4.0
	 *
	 * @param string $message Delete error message.
	 * @return string New delete error message.
	 */
	public function message_comment_deleted_error( $message ) {
		return $this->get_text_option( 'comment_deleted_error', $message );
	}

	/**
	 * Return a message when a comment is removed.
	 *
	 * @since 3.4.0
	 *
	 * @param string $message Delete removal message.
	 * @return string New delete removal message.
	 */
	public function message_comment_deleted( $message ) {
		return $this->get_text_option( 'comment_deleted', $message );
	}

	/**
	 * Return delete confirmation modal text.
	 *
	 * @since 3.4.0
	 *
	 * @param string $message Delete confirmation message.
	 * @return string New delete confirmation message.
	 */
	public function message_confirm_delete( $message ) {
		return $this->get_text_option( 'confirm_delete', $message );
	}

	/**
	 * Return whether a delete option is available.
	 *
	 * @since 3.4.0
	 *
	 * @param bool $allow_deletion Whether to allow comment deletion.
	 * @return bool Whether to allow comment deletion.
	 */
	public function allow_deletion( $allow_deletion ) {
		$allow_delete = Options::get_options( false, 'allow_delete' );
		if ( is_bool( $allow_delete ) ) {
			return $allow_delete;
		}
		return (bool) $allow_deletion;
	}

	/**
	 * Return whether a delete confirmation appears when deleting a comment.
	 *
	 * @since 3.4.0
	 *
	 * @param bool $allow_delete_confirmation Whether to allow confirmation modal.
	 * @return bool Whether to allow confirmation modal.
	 */
	public function allow_delete_confirmation( $allow_delete_confirmation ) {
		$allow_confirmation = Options::get_options( false, 'allow_delete_confirmation' );
		if ( is_bool( $allow_confirmation ) ) {
			return $allow_confirmation;
		}
		return (bool) $allow_delete_confirmation;
	}

	/**
	 * Return the edit text.
	 *
	 * @since 3.4.0
	 *
	 * @param string $edit_text The main edit text for SCE.
	 * @return string New edit text.
	 */
	public function edit_text( $edit_text ) {
		return $this->get_text_option( 'click_to_edit_text', $edit_text );
	}

	/**
	 * Return button text for the delete button.
	 *
	 * @since 3.4.0
	 *
	 * @param string $button_text Button text.
	 * @return string New button text.
	 */
	public function save_delete_text( $button_text ) {
		return $this->get_text_option( 'delete_text', $button_text );
	}

	/**
	 * Return button text for the cancel button.
	 *
	 * @since 3.4.0
	 *
	 * @param string $button_text Button text.
	 * @return string New button text.
	 */
	public function save_cancel_text( $button_text ) {
		return $this->get_text_option( 'cancel_text', $button_text );
	}

	/**
	 * Return button text for the save button.
	 *
	 * @since 3.4.0
	 *
	 * @param string $button_text Button text.
	 * @return string New button text.
	 */
	public function save_button_text( $button_text ) {
		return $this->get_text_option( 'save_text', $button_text );
	}

	/**
	 * Return whether to show a timer.
	 *
	 * @since 3.4.0
	 *
	 * @param bool $show_timer Whether to show the timer or not.
	 * @return bool Whether to show the timer or not.
	 */
	public function show_timer( $show_timer ) {
		$new_show_timer = Options::get_options( false, 'show_timer' );
		if ( is_bool( $new_show_timer ) ) {
			return $new_show_timer;
		}
		return (bool) $show_timer;
	}

	/**
	 * Return a stored text option or the incoming fallback when empty.
	 *
	 * @since 3.4.0
	 *
	 * @param string $key      Option key.
	 * @param string $fallback Fallback text.
	 * @return string Option text or fallback.
	 */
	private function get_text_option( $key, $fallback ) {
		$value = Options::get_options( false, sanitize_key( $key ) );
		if ( ! is_string( $value ) || '' === $value ) {
			return sanitize_text_field( $fallback );
		}
		return sanitize_text_field( $value );
	}
}
