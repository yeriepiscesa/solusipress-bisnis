var bank_default_item = { 
	bank:'', account_number: '', account_name: '', public_account: '0', logo_url: '', description: ''
};

function SolusiPress_Data_Bank() {    
    var self = this;    
    self.view_mode = ko.observable();
    self.form_title = ko.pureComputed( function(){
        var title = 'Data Baru';
        if( this.view_mode() != 'new' ) {
            title = 'Ubah Data';
        }
        return title
    }, self );    
    self.item = ko.observable( new SolusiPress_BankItem( null, bank_default_item ) );		
}

function SolusiPress_BankItem( id, data ) {
	var self = this;
	self.id = id;
	self.bank = data.bank;
	self.account_number = data.account_number;
	self.account_name = data.account_name;
	
	if( data.public_account != '0' ) {
		$da_tf = true;
	} else {
		$da_tf = false;
	}
	self.public_checked = ko.observable( $da_tf );

	self.public_account = ko.pureComputed( function(){
		return self.public_checked() ? '1':'0';
	} );
	
	self.logo_url = data.logo_url;
	self.description = data.description;
}

( function( $ ) {
	
    var vm = new SolusiPress_Data_Bank();
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
                vm.item( new SolusiPress_BankItem( data.id, data ) );
            }
        });           
    }    	
	
    function do_load_data() {
	     
        dt = $('#bank-table').dataTable({
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
                    orderable: false,
                    className: 'dt-body-nowrap',
                    render: function( data, type, row, meta ){
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
                null, null, null, null, null
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
	
    $( document ).ready( function(){        
	    
        ko.applyBindings( vm, document.getElementById( 'solusipress-form-entry' ) );
        solusipress_functions.setVM( vm );
        $( '#btn-new-form-entry' ).click( function( e ){
            e.preventDefault();
            vm.view_mode('new');
            vm.item( new SolusiPress_BankItem( null, bank_default_item ) );
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