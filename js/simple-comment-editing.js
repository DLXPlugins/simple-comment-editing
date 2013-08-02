jQuery( document ).ready( function( $ ) {
	sce = $.simplecommentediting = $.fn.simplecommentediting = function() {
		var $this = this;
		return this.each( function() {
			//Set up event for when the edit button is clicked
			$( this ).on( 'click', 'a', function( evt ) { 
				evt.preventDefault();
				sce.test_function();
			} );
			//Load timers
		} );
	};
	sce.test_function = function() {
		alert( 'test_function' );
	}
	function test_function() {
		alert( "blah" );
	};
	$( '.sce-edit-button' ).simplecommentediting();
} );