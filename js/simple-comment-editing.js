jQuery( document ).ready( function( $ ) {
	sce = $.simplecommentediting = $.fn.simplecommentediting = function() {
		var $this = this;
		return this.each( function() {
			var ajax_url = $( this ).find( 'a:first' ).attr( 'href' );
			var ajax_params = wpAjax.unserialize( ajax_url );
			var element = this;

			//Set up event for when the edit button is clicked
			$( element ).on( 'click', 'a', function( evt ) { 
				evt.preventDefault();
				alert( ajax_params.cid );
				sce.test_function();
			} );
			
			//Use siblings to set up events for save/cancel button
			//Load timers
			/*
			1.  Use Ajax to get the amount of time left to edit the comment.
			2.  Display the result
			3.  Set Interval
			*/
			$.post( ajax_url, { action: 'sce_get_time_left', comment_id: ajax_params.cid, post_id: ajax_params.pid }, function( response ) {
				var minutes = parseInt( response.minutes );
				var seconds = parseInt( response.seconds );
				var timer_text = sce.get_timer_text( minutes, seconds );
				$( element ).find( '.sce-timer' ).html( timer_text );
			}, 'json' );
		} );
	};
	sce.test_function = function() {
		alert( 'test_function' );
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
	function test_function() {
		alert( "blah" );
	};
	$( '.sce-edit-button' ).simplecommentediting();
} );