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
									alert( simple_comment_editing.comment_deleted );
									$( "#comment-" + ajax_params.cid ).slideUp(); //Attempt to remove the comment from the theme interface
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
									alert( response.error ); //Alerts may be evil, but they work here - Drawback, they stop the timer, which may result in the user thinking they have more time
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
			
		} );
	};
	sce.get_timer_text = function( minutes, seconds ) {
		if (seconds < 0) { minutes -= 1; seconds = 59; }
		//Create timer text
		var text = '&nbsp;&ndash;&nbsp;';
		if (minutes >= 1) {
		if (minutes >= 2) { text += minutes + " " + simple_comment_editing.minutes; } else { text += minutes + " " + simple_comment_editing.minute; }
		if (seconds > 0) { text += " " + simple_comment_editing.and + " "; }
		}
		if (seconds > 0) {
			if (seconds >= 2) { text += seconds + " " + simple_comment_editing.seconds; } else { text += seconds + " " + simple_comment_editing.second; }
		
		}
		return text;
	};
	sce.timers = new Array();
	sce.textareas = new Array();
	$( '.sce-edit-button' ).simplecommentediting();
} );
