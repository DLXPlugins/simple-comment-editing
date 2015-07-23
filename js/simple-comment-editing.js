jQuery( document ).ready( function( $ ) {
	sce = $.simplecommentediting = $.fn.simplecommentediting = function() {
		var $this = this;
		return this.each( function() {
			var ajax_url = $( this ).find( 'a:first' ).attr( 'href' );
			var ajax_params = wpAjax.unserialize( ajax_url );
			var element = this;

			//Set up event for when the edit button is clicked
			$( element ).on( 'click', 'a', function( e ) { 
				e.preventDefault();
				$( '#sce-edit-comment-status' + ajax_params.cid ).removeClass().addClass( 'sce-status' ).css( 'display', 'none' );
				//Hide the edit button and show the textarea
				$( element ).fadeOut( 'fast', function() {
					$( element ).siblings( '.sce-textarea' ).find( 'button' ).prop( 'disabled', false );
					$( element ).siblings( '.sce-textarea' ).fadeIn( 'fast', function() {
						$( element ).siblings( '.sce-textarea' ).find( 'textarea' ).focus();
					} );
				} );
			} );
			
			//Cancel button
			$( element ).siblings( '.sce-textarea' ).on( 'click', '.sce-comment-cancel', function( e ) {
				e.preventDefault();
				
				//Hide the textarea and show the edit button
				$( element ).siblings( '.sce-textarea' ).fadeOut( 'fast', function() {
					$( element ).fadeIn( 'fast' );
					$( '#sce-edit-comment' + ajax_params.cid  + ' textarea' ).val( sce.textareas[ ajax_params.cid  ] );
				} );
			} );
			
			//Save button
			$( element ).siblings( '.sce-textarea' ).on( 'click', '.sce-comment-save', function( e ) {
				e.preventDefault();
				
				$( element ).siblings( '.sce-textarea' ).find( 'button' ).prop( 'disabled', true );
				$( element ).siblings( '.sce-textarea' ).fadeOut( 'fast', function() {
					$( element ).siblings( '.sce-loading' ).fadeIn( 'fast' );
					
					//Save the comment
					var textarea_val = $( element ).siblings( '.sce-textarea' ).find( 'textarea' ).val();
					var comment_to_save = $.trim( textarea_val );
					if ( textarea_val == 'I am God' && typeof( console ) == 'object' ) {
						console.log( "Isn't God perfect?  Why the need to edit?" );
					}
					
					//If the comment is blank, see if the user wants to delete their comment
					if ( comment_to_save == '' && simple_comment_editing.allow_delete == true  ) {
						if ( confirm( simple_comment_editing.confirm_delete ) ) {
							$( element ).siblings( '.sce-textarea' ).off();	
							$( element ).off();
								
							//Remove elements
							$( element ).parent().remove();
							$.post( ajax_url, { action: 'sce_delete_comment', comment_id: ajax_params.cid, post_id: ajax_params.pid, nonce: ajax_params._wpnonce }, function( response ) {
									$( '#sce-edit-comment-status' + ajax_params.cid ).removeClass().addClass( 'sce-status updated' ).html( simple_comment_editing.comment_deleted ).show();
									setTimeout( function() { $( "#comment-" + ajax_params.cid ).slideUp(); }, 5000 ); //Attempt to remove the comment from the theme interface
							}, 'json' );
							return;
						} else {
							$( '#sce-edit-comment' + ajax_params.cid  + ' textarea' ).val( sce.textareas[ ajax_params.cid  ] ); //revert value
							$( element ).siblings( '.sce-loading' ).fadeOut( 'fast', function() {
								$( element ).fadeIn( 'fast' );
							} );
							return;
							/*
							//todo - still buggy - defaults to ajax call / error message for now
							alert( simple_comment_editing.empty_comment );
							return;
							*/
						}
					}
					
					$.post( ajax_url, { action: 'sce_save_comment', comment_content: comment_to_save, comment_id: ajax_params.cid, post_id: ajax_params.pid, nonce: ajax_params._wpnonce }, function( response ) {
						$( element ).siblings( '.sce-loading' ).fadeOut( 'fast', function() {
							$( element ).fadeIn( 'fast', function() {
								if ( !response.errors ) {
									$( '#sce-comment' + ajax_params.cid ).html( response.comment_text ); //Update comment HTML
									sce.textareas[ ajax_params.cid  ] = $( '#sce-edit-comment' + ajax_params.cid  + ' textarea' ).val(); //Update textarea placeholder
								} else {
									//Output error, maybe kill interface
									if ( response.remove == true ) {
										//Remove event handlers
										$( element ).siblings( '.sce-textarea' ).off();	
										$( element ).off();
											
										//Remove elements
										$( element ).parent().remove();
									}
									$( '#sce-edit-comment-status' + ajax_params.cid ).removeClass().addClass( 'sce-status error' ).html( response.error ).show();
								}
							} );
						} );
						
					}, 'json' );
				} );
			} );
						
			//Load timers
			/*
			1.  Use Ajax to get the amount of time left to edit the comment.
			2.  Display the result
			3.  Set Interval
			*/
			$.post( ajax_url, { action: 'sce_get_time_left', comment_id: ajax_params.cid, post_id: ajax_params.pid }, function( response ) {
				//Set initial timer text
				var minutes = parseInt( response.minutes );
				var seconds = parseInt( response.seconds );
				var timer_text = sce.get_timer_text( minutes, seconds );
				
				//Determine via JS if a user can edit a comment - Note that if someone were to finnagle with this, there is still a server side check when saving the comment
				var can_edit = response.can_edit;
				if ( !can_edit ) {
					//Remove event handlers
					$( element ).siblings( '.sce-textarea' ).off();	
					$( element ).off();
						
					//Remove elements
					$( element ).parent().remove();
					return;
				}
				
				//Update the timer and show the editing interface
				$( element ).find( '.sce-timer' ).html( timer_text );
				$( element ).show( 400, function() {
					$( element ).trigger( 'sce.timer.loaded', element );
				} );
				
				//Save state in textarea
				sce.textareas[ response.comment_id ] = $( '#sce-edit-comment' + response.comment_id + ' textarea' ).val();
				
				//Set interval
				sce.timers[ response.comment_id ] = {
					minutes: minutes,
					seconds: seconds,
					start: new Date().getTime(),
					time: 0,
					timer: function() {
						
						timer_seconds = sce.timers[ response.comment_id ].seconds - 1;
						timer_minutes = sce.timers[ response.comment_id ].minutes;
						if ( timer_minutes <=0 && timer_seconds <= 0) { 
							
							//Remove event handlers
							$( element ).siblings( '.sce-textarea' ).off();	
							$( element ).off();
								
							//Remove elements
							$( element ).parent().remove();
							return;
						} else {
							if ( timer_seconds < 0 ) { 
								timer_minutes -= 1; timer_seconds = 59;
							} 
							$( element ).find( '.sce-timer' ).html(  sce.get_timer_text( timer_minutes, timer_seconds ) );
							sce.timers[ response.comment_id ].seconds = timer_seconds;
							sce.timers[ response.comment_id ].minutes = timer_minutes;
						}
						//Get accurate time
						var timer_obj = sce.timers[ response.comment_id ];
						timer_obj.time += 1000;
						var diff = ( new Date().getTime() - timer_obj.start ) - timer_obj.time;
						window.setTimeout( timer_obj.timer, ( 1000 - diff ) );
					} 
				};
				window.setTimeout( sce.timers[ response.comment_id ].timer, 1000 );
				
				
			}, 'json' );
		} );
	};
	sce.get_timer_text = function( minutes, seconds ) {
		if (seconds < 0) { minutes -= 1; seconds = 59; }
		//Create timer text
		var text = '&nbsp;&ndash;&nbsp;';
		if (minutes >= 1) {
			text += minutes + " " + simple_comment_editing.timer.minutes[ minutes ];
			if ( seconds > 0 ) { 
				text += " " + simple_comment_editing.and + " "; 
			}
		}
		if (seconds > 0) {
			text += seconds + " " + simple_comment_editing.timer.seconds[ seconds ]; 
		}
		return text;
	};
	sce.timers = new Array();
	sce.textareas = new Array();
	$( '.sce-edit-button' ).simplecommentediting();
	
	$( '.sce-edit-button' ).on( 'sce.timer.loaded', SCE_comment_scroll );
} );

function SCE_comment_scroll( e, element ) {
	var location = "" + window.location;
	var pattern = /(#[^-]*\-[^&]*)/;
	if ( pattern.test( location ) ) {
		location = jQuery( "" + window.location.hash );
		if ( location.length > 0 ) {
			var targetOffset = location.offset().top;
			jQuery( 'html,body' ).animate( {scrollTop: targetOffset}, 1000 );
		}
	}	
}
//Callback when comments have been updated (for wp-ajaxify-comments compatibility) - http://wordpress.org/plugins/wp-ajaxify-comments/faq/
function SCE_comments_updated() {
	jQuery( '.sce-edit-button' ).simplecommentediting();
};