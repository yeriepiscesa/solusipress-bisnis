function datatable_get_data_row( tbl ) {
    var id = jQuery( this ).attr( 'data-id' );
    var $tr = jQuery( this ).closest( 'tr' );
    var data_row = tbl.api().row( $tr ).data();        
    return { 
        id: id,
        row: data_row
    };
}
var solusipress_functions = (function($){    
    var vm = null;
    var dt = null;    
    return {
        setVM: function(a) { vm = a; },
        setDialog: function(b) { dialog = b; },
        setDataTable: function( c ) { dt = c; },
        process_action: function( method, root_id, goto_1st_page ) {   
            if( goto_1st_page == undefined ) {
                goto_1st_page = false;
            }
            
            $( '#'+root_id ).find( '.frm-form-fields' ).loading( { message: 'Sedang proses...' } );
            $( '.loading-overlay' ).css( 'z-index', 100000000 );
            
            var input = ko.toJS( vm.item() );  
            var post_url = solusipress.ajax_action;
            if( input.id && input.id != undefined ) {
	            post_url += input.id;
            }
            $.ajax( {
                method: 'POST',
                url: post_url,
                data: input,
                success: function( response ){
                    if( response == '' ) {
                        $( '.container-notifyjs' ).notify( 
                            "Tidak ada proses yang dijalankan", {
                            className: 'error',
                            position: 'top right'
                        });                            
                    } else {                    
                        var json = $.parseJSON( response ); 
                        if( json.status ) {
                            $.jqmodal.close();
                            $( '.container-notifyjs' ).notify( 
                                "Data berhasil disimpan", {
                                className: 'success',
                                position: 'top right'
                            });                            
                            dt.api().ajax.reload( null, goto_1st_page );
                        } else {
	                        var err_msg = 'Data gagal disimpan, mohon ulangi kembali';
	                        if( json.message && json.message != '' ) {
		                        err_msg = json.message;
	                        }
	                        
	                        var err_container = '.container-notifyjs';
	                        if( $( '.form-notify-error' ) ) {
		                        err_container = '.form-notify-error';
	                        }
	                        
                            $( err_container ).notify( 
                                err_msg, {
                                className: 'error',
                                position: 'top right'
                            });
                        }
                    }
                    $( '#'+root_id ).find( '.frm-form-fields' ).loading( 'toggle' );                    
                    $( 'body' ).trigger( 'solusipress_after_process', [ method ] );
                }
            } );                        
        },        
        process_delete: function( tbl ){
            var data = datatable_get_data_row.call( this, tbl );       
            $('#wpbody').loading( { message: 'Sedang proses...' } );
            $.ajax( {
                url: solusipress.ajax_action + data.id,
                method: 'DELETE',
                success: function( response ) {
                    var json = $.parseJSON( response );  
                    var msg = json.message || '';
                    
                    if( json.status ) {
	                    
	                    if( msg == '' ) { msg = 'Data berhasil dihapus'; }
                        $( '.container-notifyjs' ).notify( 
                            msg, {
                            className: 'success',
                            position: 'top right'
                        });
                                            
                    } else {
	                    
	                    if( msg == '' ) { msg = 'Hapus data gagal, mohon ulangi kembali'; }
                        $( '.container-notifyjs' ).notify( 
                            msg, {
                            className: 'error',
                            position: 'top right'
                        });
                                            
                    }
                    dt.api().ajax.reload( null, false );                    
                    $( 'body' ).trigger( 'solusipress_after_process', [ 'delete' ] );
                }
            } ).always( function(){
                $('#wpbody').loading( 'toggle' );
            } );  
        }        
    }    
    
})(jQuery);