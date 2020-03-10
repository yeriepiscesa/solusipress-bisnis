var row_default_item = { 
	contact_id:'', msg_date:'', msg_subject:'', msg_text:'',
	dtm_read:'', dtm_followup:'', follwoup_by:''
};

function SolusiPress_Data_Message() {    
    var self = this;    
    self.item = ko.observable( new SolusiPress_MessageItem( null, row_default_item ) );		
}

function SolusiPress_MessageItem( id, data ) {
	var self = this;
	self.id = id;
	self.contact_id = data.contact_id;
	self.msg_date = data.msg_date;
	self.msg_subject = data.msg_subject;
	self.msg_text = data.msg_text;
	self.dtm_read = data.dtm_read;
	self.dtm_followup = data.dtm_followup,
	self.followup_by = data.followup_by;
	self.contact_name = data.contact_name;
	self.contact_email = data.contact_email;	
	self.contact_whatsapp = data.contact_whatsapp;
}

( function( $ ) {
	
    var vm = new SolusiPress_Data_Message();
    var dt = null;
	    
    function prepare_view_data( the_id ) {
        $( '#solusipress-form-entry' ).jqmodal();
        $( '#solusipress-form-entry' ).find( '.frm-form-fields' ).loading( { message: 'Mengambil data...' } );   
        $( '.loading-overlay' ).css( 'z-index', 100000000 );
        $.ajax({
            url: solusipress.ajax_action + the_id,
            method: 'GET',
            success: function( resp ){
                $( '#solusipress-form-entry' ).find( '.frm-form-fields' ).loading( 'toggle' );
                data = $.parseJSON( resp );
                vm.item( new SolusiPress_MessageItem( data.id, data ) );
                if( vm.item().dtm_read == null ) {
	                $.ajax({
		                url: solusipress.ajax_action + the_id + '/set-read',
		                method: 'POST',
		                success: function(){
			                dt.api().draw();
		                }
	                });
                }
            }
        });           
    }    	

    function do_load_data() {
	     
        dt = $('#messages-table').dataTable({
            paging: true,
            processing: true,
            serverSide: true,
            order: [ [3,'DESC'] ],
            ajax: {
                url: solusipress.ajax_action,
                method: 'GET',
                data: function( data ) {
	                data.rowformat = 'datatable';
                    data.contact_type_id = $( '#filter_type' ).val();
                }
            },
            columns: [
                { 
                    orderable: false,
                    className: 'dt-body-nowrap',
                    render: function( data, type, row, meta ){
                        var html = '';                            
                        html += '<a href="#" class="action-links data-btn-delete" data-id="'+data+'"><i class="dashicons dashicons-trash"></i></a>';
                        html += '&nbsp;&nbsp;';
                        html += '<a href="#solusipress-form-entry" ';
                        html += 'alt="Lihat pesan" title="Lihat pesan" ';
                        html += 'class="action-links data-btn-edit" data-id="'+data+'">';
                        html += '<i class="dashicons dashicons-visibility"></i></a>';   
                        return html;
                    }
                },
                {
	                render: function( data, type, row, meta ) {
		                var html = '';
		                html += '<a href="#solusipress-form-entry" class="action-links data-btn-edit" data-id="' + row[0] + '">' + data + '</a>';
		                return html;
	                }
                },null,null,null
            ],
            drawCallback: function( settings ) {
                var tbl = this;
                $( '.data-btn-edit' ).click( function( e ){
                    e.preventDefault();
                    var the_id = $(this).attr( 'data-id' );
                    prepare_view_data( the_id );                     
                } );
                $( '.data-btn-delete' ).click( function( e ) {
                    e.preventDefault();
                    var obj_link = this;
                    $.confirm( {
                        useBootstrap: false,
                        title: 'Konfirmasi',
                        content: "Hapus data? proses tidak dapat dikembalikan",
                        boxWidth: '350px',
                        buttons: {
                            no: function(){},
                            yes: function(){
                                solusipress_functions.process_delete.call( obj_link, tbl );
                            }
                        }
                    } );
                } );
            }
        } );   
        
        solusipress_functions.setDataTable( dt );
        
	}
	
	$( '#filter_type' ).on( 'change', function(e){
		dt.api().draw();
	});
	
    $( document ).ready( function(){    
        ko.applyBindings( vm );
        solusipress_functions.setVM( vm );
	    do_load_data();
	} );
		
} )( jQuery );