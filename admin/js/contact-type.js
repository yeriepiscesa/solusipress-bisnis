var row_default_item = { 
	name:'', is_default: '0', ordering: 0, description: '', color: '#6699CC'
};

function SolusiPress_Data_ContactType() {    
    var self = this;    
    self.view_mode = ko.observable();
    self.form_title = ko.pureComputed( function(){
        var title = 'Data Baru';
        if( this.view_mode() != 'new' ) {
            title = 'Ubah Data';
        }
        return title
    }, self );    
    self.item = ko.observable( new SolusiPress_ContactTypeItem( null, row_default_item ) );		
}

function SolusiPress_ContactTypeItem( id, data ) {
	var self = this;
	self.id = id;
	self.name = data.name;
	self.ordering = data.ordering;
	self.color = data.color;

	if( data.is_default != '0' ) {
		$def_tf = true;
	} else {
		$def_tf = false;
	}
	self.default_checked = ko.observable( $def_tf );

	self.is_default = ko.pureComputed( function(){
		return self.default_checked() ? '1':'0';
	} );
	
	self.description = data.description;
}

( function( $ ) {
	
    var vm = new SolusiPress_Data_ContactType();
    var dt = null;
    
    function prepare_edit_data( the_id ) {
        $( '#solusipress-form-entry' ).jqmodal();
        $( '#solusipress-form-entry' ).find( '.frm-form-fields' ).loading( { message: 'Mengambil data...' } );   
        $( '.loading-overlay' ).css( 'z-index', 100000000 );
        $.ajax({
            method: 'GET',
            url: solusipress.ajax_action + '/' + the_id,
            success: function( resp ){
                data = $.parseJSON( resp );
                $( '#solusipress-form-entry' ).find( '.frm-form-fields' ).loading( 'toggle' );
                vm.item( new SolusiPress_ContactTypeItem( data.id, data ) );
            }
        });           
    }    	
	
    function do_load_data() {
	             
        dt = $('#contact-type-table').dataTable({
            /*responsive: true,*/
            paging: true,
            processing: true,
            serverSide: true,
			ajax: {
                url: solusipress.ajax_action,
                type: 'GET',
                data: { "rowformat":"datatable" }
            },            
            columns: [
                { 
                    orderable: true,
                    className: 'dt-body-nowrap',
                    render: function( data, type, row, meta ) {
                        var html = '';                            
                        html += '<a href="#" class="action-links data-btn-delete" data-id="'+data+'"><i class="dashicons dashicons-trash"></i></a>';
                        html += '&nbsp;&nbsp;';
                        html += '<a href="#solusipress-form-entry" ';
                        html += 'class="action-links data-btn-edit" data-id="'+data+'">';
                        html += '<i class="dashicons dashicons-editor-paste-text"></i></a>';                            
                        html += '&nbsp;&nbsp;<strong>ID:' + data + '</strong>';  
                        return html;
                    }
                },
                null, null, null, null
            ],
            order: [ [2, 'asc'] ],
            drawCallback: function( settings ) {
	            
                var tbl = this;
                $( '.data-btn-edit' ).click( function( e ) {
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
	
	
    $( document ).ready( function(){        
	    
        ko.applyBindings( vm, document.getElementById( 'solusipress-form-entry' ) );
        solusipress_functions.setVM( vm );
        $( '#btn-new-form-entry' ).click( function( e ){
            e.preventDefault();
            vm.view_mode('new');
            vm.item( new SolusiPress_ContactTypeItem( null, row_default_item ) );
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