var ct_default = '';
_.each( solusipress.contact_types.data, function( row, idx, list ){
	if( row.default == '1' ) {
		ct_default = row.id;
	}
} );

var row_default_item = { 
	first_name:'', last_name: '', contact_type_id: ct_default, email: '',
	phone: '', note: '', whatsapp: '', organization: '',
	instagram: '', facebook: '', twitter: '', linkedin: ''
};

function SolusiPress_Data_Contact() {    
    var self = this;    
    self.view_mode = ko.observable();
    self.form_title = ko.pureComputed( function(){
        var title = 'Data Baru';
        if( this.view_mode() != 'new' ) {
            title = 'Ubah Data';
        }
        return title
    }, self );    
    self.item = ko.observable( new SolusiPress_ContactItem( null, row_default_item ) );		
}

function SolusiPress_ContactItem( id, data ) {
	var self = this;
	self.id = id;
	self.first_name = data.first_name;
	self.last_name = data.last_name;
	self.contact_type_id = data.contact_type_id;
	self.email = data.email;
	self.phone = data.phone;
	self.organization = data.organization;
	self.note = data.note;
	self.whatsapp = data.whatsapp;
	self.instagram = data.instagram;
	self.facebook = data.facebook;
	self.twitter = data.twitter;
	self.linkedin = data.linkedin;	
}

( function( $ ) {
	
    var vm = new SolusiPress_Data_Contact();
    var dt = null;
    
    function prepare_edit_data( the_id ) {
        $( '#solusipress-form-entry' ).jqmodal();
        $( '#solusipress-form-entry' ).find( '.frm-form-fields' ).loading( { message: 'Mengambil data...' } );   
        $( '.loading-overlay' ).css( 'z-index', 100000000 );
        $.ajax({
            method: 'GET',
            url: solusipress.ajax_action + the_id,
            success: function( resp ){
                data = $.parseJSON( resp );
                $( '#solusipress-form-entry' ).find( '.frm-form-fields' ).loading( 'toggle' );
                vm.item( new SolusiPress_ContactItem( data.id, data ) );
            }
        });           
    }    	
	
    function do_load_data() {
	     
        dt = $('#contact-table').dataTable({
            responsive: false,
            scrollx:true,
            paging: true,
            processing: true,
            serverSide: true,
            order: [ [0,'DESC'] ],
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
                    orderable: true,
                    className: 'dt-body-nowrap',
                    render: function( data, type, row, meta ){
                        var html = '';                            
                        html += '<a href="#" class="action-links data-btn-delete" data-id="'+data+'"><i class="dashicons dashicons-trash"></i></a>';
                        html += '&nbsp;&nbsp;';
                        html += '<a href="#solusipress-form-entry" ';
                        html += 'class="action-links data-btn-edit" data-id="'+data+'">';
                        html += '<i class="dashicons dashicons-editor-paste-text"></i></a>'; 
                        return html;
                    }
                },
                { className: 'dt-body-nowrap' }, null, null, null, null
            ],
            drawCallback: function( settings ){
	            
                var tbl = this;
                $( '.data-btn-edit' ).click( function( e ){
                    e.preventDefault();
                    vm.view_mode( 'edit' );
                    var the_id = $(this).attr( 'data-id' );
                    prepare_edit_data( the_id );                     
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
        $( '#btn-new-form-entry' ).click( function( e ){
            e.preventDefault();
            vm.view_mode('new');
            vm.item( new SolusiPress_ContactItem( null, row_default_item ) );
        } );       

	    do_load_data();
	    var el_root_id = 'solusipress-form-entry';
        $( '#'+el_root_id ).find( '.frm-form-fields' ).submit( function(e) {
            e.preventDefault();
            if( vm.item().post_id == null ) {
	            if( vm.view_mode() == 'new' ) {
	                solusipress_functions.process_action.call( this, 'insert', el_root_id, true );
	            }
	            if( vm.view_mode() == 'edit' ) {
	                solusipress_functions.process_action.call( this, 'update', el_root_id, false );
	            }
            }
        } );        
        
	} );	
	
} )( jQuery );