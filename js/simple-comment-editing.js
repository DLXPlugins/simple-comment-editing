jQuery( document ).ready( function( $ ) {
	sce = $.simplecommentediting = $.fn.simplecommentediting = function() {
		var $this = this;
		return this.each( function() {
			var ajax_url = $( this ).find( 'a:first' ).attr( 'href' );
			var ajax_params = wpAjax.unserialize( ajax_url );

			//Set up event for when the edit button is clicked
			$( this ).on( 'click', 'a', function( evt ) { 
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
				console.log( response );
			}, 'json' );
		} );
	};
	sce.test_function = function() {
		alert( 'test_function' );
	};
	sce.timers = new Array();
	function test_function() {
		alert( "blah" );
	};
	$( '.sce-edit-button' ).simplecommentediting();
} );