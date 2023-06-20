/**
 * Covers comment editing on the frontend.
 */
import sendCommand from '../SendCommand';

// get i18n variables.
const { __, _n } = wp.i18n;

// get hooks.
const { createHooks } = wp.hooks;

// Get the timer placeholder.
const timers = [];

// Get the textareas placeholder.
const textareas = [];

// Start any hooks that are registered.
const sceHooks = createHooks();

// Onload, get dom entries.
window.addEventListener( 'load', () => {
	const comment_edit_buttons = document.querySelectorAll( '.sce-edit-button' );
	if ( ! comment_edit_buttons ) {
		return;
	}

	/**
	 * Gets the time left for the comment.
	 *
	 * @param {number} commentId The Comment ID.
	 * @param {number} postId    The Post ID.
	 * @param {string} nonce     The ajax nonce.
	 * @param {string} ajaxUrl   The ajax url.
	 */
	const getTimeLeft = async( commentId, postId, nonce, ajaxUrl ) => {
		const response = await sendCommand( 'sce_get_time_left', { comment_id: commentId, post_id: postId, _ajax_nonce: nonce }, ajaxUrl );
		return response;
	};

	/**
	 * Show the edit button.
	 *
	 * @param {Element} button The element to show.
	 */
	const showEditButton = ( button ) => {
		button.classList.remove( 'sce-hide' );
		button.classList.add( 'sce-show' );
	};

	/**
	 * Hide the edit button.
	 *
	 * @param {Element} button The element to show.
	 */
	const hideEditButton = ( button ) => {
		button.classList.remove( 'sce-show' );
		button.classList.add( 'sce-hide' );
	};

	/**
	 * Gets a timer's text label.
	 *
	 * @param {number} minutes Number of minutes.
	 * @param {number} seconds Number of seconds.
	 * @return {string} Timer label.
	 */
	const getTimerText = ( minutes, seconds ) => {
		if ( seconds < 0 ) {
			minutes -= 1; seconds = 59;
		}
		//Create timer text
		let text = '';
		let days = 0;
		let hours = 0;
		if ( minutes >= 1 ) {
			// Get mniutes in seconds
			let minute_to_seconds = Math.abs( minutes * 60 );
			days = Math.floor( minute_to_seconds / 86400 );

			// Get Days
			if ( days > 0 ) {
				// Get days
				text += days + ' ' + _n( 'day', 'days', days, 'simple-comment-editing' );
				text += ' ' + __( 'and', 'simple-comment-editing' ) + ' ';
				minute_to_seconds -= days * 86400;
			}

			// Get hours
			hours = Math.floor( minute_to_seconds / 3600 ) % 24;
			if ( hours >= 0 ) {
				if ( hours > 0 ) {
					text += hours + ' ' + _n( 'hour', 'hours', hours, 'simple-comment-editing' );
					text += ' ' + __( 'and', 'simple-comment-editing' ) + ' ';
				}
				minute_to_seconds -= hours * 3600;
			}

			// Get minutes
			const minutesIncluded = Math.floor( minute_to_seconds / 60 ) % 60;
			minute_to_seconds -= minutesIncluded;
			if ( minutes > 0 ) {
				text += minutes + ' ' + _n( 'minute', 'minutes', minutes, 'simple-comment-editing' );
			}

			// Get seconds
			if ( seconds > 0 ) {
				text += ' ' + __( 'and', 'simple-comment-editing' ) + ' ';
				text += seconds + ' ' + _n( 'second', 'seconds', seconds, 'simple-comment-editing' );
			}
		} else {
			text += seconds + ' ' + _n( 'second', 'seconds', seconds, 'simple-comment-editing' );
		}
		/**
		 * JSFilter: sce.comment.timer.text
		 *
		 * Filter triggered before a timer is returned
		 *
		 * @since 1.4.0
		 *
		 * @param string comment text
		 * @param string minute text,
		 * @param string second text,
		 * @param int    number of minutes left
		 * @param int    seconds left
		 */
		text = sceHooks.applyFilters( 'sce.comment.timer.text', text, _n( 'day', 'days', days, 'simple-comment-editing' ), _n( 'hour', 'hours', hours, 'simple-comment-editing' ), _n( 'minute', 'minutes', minutes, 'simple-comment-editing' ), _n( 'second', 'seconds', seconds, 'simple-comment-editing' ), days, hours, minutes, seconds );
		return text;
	};

	/**
	 * Deletes a comment.
	 *
	 * @param {Element} element   The element to show.
	 * @param {number}  commentId The Comment ID.
	 * @param {number}  postId    The Post ID.
	 * @param {string}  nonce     The ajax nonce.
	 * @param {string}  ajaxUrl   The ajax url.
	 */
	const deleteComment = async( element, commentId, postId, nonce, ajaxUrl ) => {
		// todo - remove events.
		// $( element ).siblings( '.sce-textarea' ).off();
		// $( element ).off();

		//Remove elements
		element.parentNode.remove();
		await sendCommand( 'sce_delete_comment', { comment_id: commentId, post_id: postId, nonce }, ajaxUrl ).then( ( response ) => {
			if ( ! response.data.success ) {
				alert( simple_comment_editing.comment_deleted_error );
			} else {
				const status = document.querySelector( '#sce-edit-comment-status' + commentId );

				// Remove all classes.
				status.classList.remove( 'sce-status', 'sce-status-error', 'sce-status-updated' );
				status.classList.add( 'sce-status', 'updated' );
				status.innerHTML = simple_comment_editing.comment_deleted;
				status.style.display = 'block';
				setTimeout( function() {
					document.querySelector( '#comment-' + commentId ).remove();
				}, 3000 ); //Attempt to remove the comment from the theme interface
			}
			return response;
		} );
	};

	// Loop through all edit buttons.
	comment_edit_buttons.forEach( ( button ) => {
		// Get first link
		const ajaxUrl = button.querySelector( 'a:first-of-type' ).getAttribute( 'href' );

		// Get comment ID from URL query param.
		const urlParams = new URLSearchParams( ajaxUrl );
		const commentId = urlParams.get( 'cid' );
		const postId = urlParams.get( 'pid' );
		const nonce = urlParams.get( 'nonce' );

		console.log( commentId, postId, nonce );

		// Get the time left for the comment.
		getTimeLeft( commentId, postId, simple_comment_editing.nonce, ajaxUrl ).then( ( response ) => {
			// Get the timer element.
			const { data } = response.data;
			let { minutes, seconds } = data;
			const { can_edit } = data;

			// Show button if time left.
			if ( 'unlimited' === minutes && 'unlimited' === seconds ) {
				showEditButton( button );
				return;
			}

			// Convert to integers.
			minutes = parseInt( minutes );
			seconds = parseInt( seconds );

			//Determine via JS if a user can edit a comment - Note that if someone were to finnagle with this, there is still a server side check when saving the comment
			if ( ! can_edit ) {
				// Get sibling textareas and turn off events.
				const textareas = button.parentNode.querySelectorAll( '.sce-textarea' );
				textareas.forEach( ( textarea ) => {
					// Turn off all parent and child events.

				} );

				// todo - remove events from elements.
				// $( element ).siblings( '.sce-textarea' ).off();
				// $( element ).off();

				//Remove elements
				button.parentNode.remove();
				return;
			}

			// Update the timer text placeholder.
			const timerText = getTimerText( minutes, seconds );
			const timerTextElement = button.querySelector( '.sce-timer' );
			if ( null !== timerTextElement ) {
				timerTextElement.textContent = timerText;
			}

			// Update other timers that are siblings.
			const siblingTimers = button.parentNode.querySelectorAll( '.sce-textarea .sce-timer' );
			if ( null !== siblingTimers ) {
				siblingTimers.forEach( ( siblingTimer ) => {
					siblingTimer.textContent = timerText;
				} );
			}

			// Show the edit interface.
			showEditButton( button );

			// Trigger new event for timer being loaded for the button.
			const timerLoadedEvent = new CustomEvent( 'sceTimerLoaded', {
				detail: {
					button,
					commentId,
					postId,
				},
			} );
			button.dispatchEvent( timerLoadedEvent );

			// Save textarea.
			const currentTextarea = document.querySelector( `#sce-edit-comment${ commentId } textarea` );
			if ( null !== currentTextarea ) {
				textareas[ commentId ] = currentTextarea.value;
			}

			// Save and set timer for one second.
			timers[ commentId ] = {
				minutes,
				seconds,
				start: new Date().getTime(),
				time: 0,
				timer: () => {
					let timer_seconds = timers[ commentId ].seconds - 1;
					let timer_minutes = timers[ commentId ].minutes;
					if ( timer_minutes <= 0 && timer_seconds <= 0 ) {
						// todo - remove events from elements.
						// $( element ).siblings( '.sce-textarea' ).off();
						// $( element ).off();

						//Remove elements
						button.parentNode.remove();
						return;
					}
					if ( timer_seconds < 0 ) {
						timer_minutes -= 1; timer_seconds = 59;
					}
					const newTimerText = getTimerText( timer_minutes, timer_seconds );
					const sceTimer = button.querySelector( '.sce-timer' );
					if ( null !== sceTimer ) {
						sceTimer.textContent = newTimerText;
					}
					//Update other timers that are siblings
					const newSiblingTimers = button.parentNode.querySelectorAll( '.sce-textarea .sce-timer' );
					if ( null !== newSiblingTimers ) {
						newSiblingTimers.forEach( ( siblingTimer ) => {
							siblingTimer.textContent = newTimerText;
						} );
					}

					// Trigger countdown event.
					const timerCountdownEvent = new CustomEvent( 'sceTimerCountdown', {
						detail: {
							button,
							commentId,
							postId,
							timer_seconds,
							timer_minutes,
						},
					} );
					button.dispatchEvent( timerCountdownEvent );

					// Update timer with new values.
					timers[ commentId ].seconds = timer_seconds;
					timers[ commentId ].minutes = timer_minutes;
					//Get accurate time
					const timer_obj = timers[ commentId ];
					timer_obj.time += 1000;
					const diff = ( new Date().getTime() - timer_obj.start ) - timer_obj.time;
					window.setTimeout( timer_obj.timer, ( 1000 - diff ) );
				},
			};
			window.setTimeout( timers[ commentId ].timer, 1000 );
		} );

		//Set up event for when the edit button is clicked
		const editButton = document.querySelector( `#sce-edit-comment${ commentId } .sce-edit-button-main` );
		if ( null !== editButton ) {
			editButton.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				const statusElement = document.querySelector( `#sce-edit-comment-status${ commentId }` );
				if ( null !== statusElement ) {
					statusElement.classList.remove( 'sce-error' );
					statusElement.classList.remove( 'sce-success' );
					statusElement.classList.add( 'sce-status' );
					statusElement.style.display = 'none';
				}
				//Hide the edit button and show the textarea
				const editButtonWrapper = editButton.closest( '.sce-edit-comment' );
				editButtonWrapper.querySelectorAll( '.sce-textarea button' ).disabled = false;
				editButtonWrapper.querySelector( '.sce-textarea' ).style.display = 'block';
				editButton.parentNode.style.display = 'none';

				const textarea = editButtonWrapper.querySelector( '.sce-textarea textarea:first-of-type' );
				const showEditTextAreaEvent = new CustomEvent( 'sceEditTextareaShow', {
					detail: {
						textarea,
						commentId,
						postId,
					},
				} );
				button.dispatchEvent( showEditTextAreaEvent );
				textarea.focus();
			} );

			// For when the delete button is clicked.
			const deleteButton = button.querySelector( '.sce-delete-button-main' );
			if ( null !== deleteButton ) {
				deleteButton.addEventListener( 'click', ( e ) => {
					e.preventDefault();
					if ( simple_comment_editing.allow_delete_confirmation ) {
						if ( confirm( simple_comment_editing.confirm_delete ) ) {
							deleteComment( button, commentId, postId, nonce, ajaxUrl );
						}
					} else {
						deleteComment( button, commentId, postId, nonce, ajaxUrl );
					}
				} );
			}

			// Set up main delete button event.
			const commentDeleteButton = button.parentNode.querySelector( '.sce-textarea .sce-comment-delete' );
			if ( null !== commentDeleteButton ) {
				commentDeleteButton.addEventListener( 'click', ( e ) => {
					e.preventDefault();
					if ( simple_comment_editing.allow_delete_confirmation ) {
						if ( confirm( simple_comment_editing.confirm_delete ) ) {
							deleteComment( button, commentId, postId, nonce, ajaxUrl );
						}
					} else {
						deleteComment( button, commentId, postId, nonce, ajaxUrl );
					}
				} );
			}
		}
	} );

	if ( 'compact' === simple_comment_editing.timer_appearance ) {
		sceHooks.addFilter( 'sce.comment.timer.text', 'simple-comment-editing', function( timer_text, days_text, hours_text, minutes_text, seconds_text, days, hours, minutes, seconds ) {
			timer_text = '';
			if ( days > 0 ) {
				if ( days < 10 ) {
					timer_text += '' + '0' + days;
				} else {
					timer_text += days;
				}
				timer_text += ':';
			}
			if ( hours > 0 ) {
				if ( hours < 10 ) {
					timer_text += '' + '0' + hours;
				} else {
					timer_text += hours;
				}
				timer_text += ':';
			} else if ( hours === 0 && days > 0 ) {
				timer_text += '00';
				timer_text += ':';
			}
			if ( minutes > 0 ) {
				if ( minutes < 10 ) {
					timer_text += '' + '0' + minutes;
				} else {
					timer_text += minutes;
				}
				timer_text += ':';
			} else if ( minutes === 0 && hours > 0 ) {
				timer_text += '00';
				timer_text += ':';
			}
			if ( seconds > 0 ) {
				if ( seconds < 10 ) {
					timer_text += '' + '0' + seconds;
				} else {
					timer_text += seconds;
				}
			} else if ( seconds === 0 && minutes > 0 ) {
				timer_text += '00';
			}
			return timer_text;
		} );
	}
} );
//Callback when comments have been updated (for wp-ajaxify-comments compatibility) - http://wordpress.org/plugins/wp-ajaxify-comments/faq/
window.SCE_comments_updated = ( comment_url ) => {
	const match = comment_url.match( /comment-(\d+)/ );
	if ( ! match ) {
		return;
	}
	const comment_id = match[ 1 ];
	//jQuery( '#comment-' + comment_id ).find( '.sce-edit-button' ).simplecommentediting();
};
