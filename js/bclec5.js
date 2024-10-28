(function($) {
	
	$(function() {
		
		$("#loading-screen").dialog({
            autoOpen: false,
            draggable: false,
            resizable: false,
            width: 385,
            height: 200
        }); // end dialog

		$( "#bcl-upload-form" ).submit(function( event ) {
			$(".bcl-temp").hide();
			$("#loading-screen").dialog("open");
		});

		$( "#loading-screen" ).on( "dialogclose", function( event, ui ) {
			window.location.reload();
		});

	});

})( jQuery );