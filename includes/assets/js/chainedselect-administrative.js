(function( $ ) {

    var map_id = {
        'province_id' : 'acf-field_5dbc0b191d966',
        'regency_id' : 'acf-field_5dbc0b411d967',
        'district_id' : 'acf-field_5dbc0b531d968',
        'village_id' : 'acf-field_5dbc0b5b1d969'
    };

    $( function(){
		
	  	var $regency, $district, $village;
        
		$( '#' + map_id.province_id ).on( 'change', function() {
			var data = { 
			  	"action": "get_administrative", 
			  	"province": $(this).val(),
			  	"security": chainedselect.security
			};			  			  
		  	$regency = clear_selects( 'regency_id', 'Select Regency' );			  
		  	$district = clear_selects( 'district_id', 'Select District' );
		  	$village = clear_selects( 'village_id', 'Select Village' );
		  	fill_data( $regency, data );
		} );        
	  
		$( '#' + map_id.regency_id ).on( 'change', function() {
		  	$district = clear_selects( 'district_id', 'Select District' );
		  	$village = clear_selects( 'village_id', 'Select Village' );
			var data = { 
			  	"action": "get_administrative", 
				"regency": $(this).val(),
				"security": chainedselect.security
			};			  			  
			fill_data( $district, data );	  
		} );
		
		$( '#' + map_id.district_id ).on( 'change', function() {
		  	$village = clear_selects( 'village_id', 'Select Village' );
			var data = { 
			  	"action": "get_administrative", 
				"district": $(this).val(),
				"security": chainedselect.security
			};			  			  
			fill_data( $village, data );	  
		} );

	});

	function clear_selects( id, empty_val ) {
		var $element = $( '#' + map_id[ id ] );
		$element.empty();			  
		$element.append( new Option( '-- ' + empty_val + ' --', '', false, false ) );			  	
	  	return $element;
	}	  
 	
  	function fill_data( $element, data ) {
	  
	  	$.ajax({
			"url": chainedselect.ajaxurl,
		  	"type": "POST",
		  	"dataType": "html",
		  	"data": data,
		  	"success": function( response ) {
				var result = $.parseJSON(response);
				$.each( result, function( key, val ) {					 	 
					var option = new Option( val, key, false, false );
					$element.append(option);
				});
			}
		});
	}
  	  
})( jQuery );