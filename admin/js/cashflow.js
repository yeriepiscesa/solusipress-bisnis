var cashflow_default_item = { 
	trx_no: '', trx_date: '', contact_id: '',
	account_id: '', dc: 'd',
	from_to_name: '', organization: '', 
	amount: 0, note: ''
};
var cashflow_default_transfer = {
	trf_date: '', src_account: '', dst_account: '',
	amount: 0, note: ''
}

function SolusiPress_Data_Cashflow() {    
    
    var self = this;    
    self.view_mode = ko.observable();
    self.item = ko.observable( new SolusiPress_CashflowItem( null, cashflow_default_item ) );		
    
    self.form_title = ko.pureComputed( function(){
        var title = 'Transaksi Baru';
        if( this.view_mode() != 'new' ) {
            title = 'Ubah Transaksi';
        }
        if( this.view_mode() == 'detail' ) {
	        title = 'Detail Transaksi';
        }
        return title
    }, self );    
    
	self.from_to_label = ko.pureComputed( function(){
		return this.item().dc() == 'd' ? 'Diterima dari':'Diberikan kepada';	
	}, self );
	
}

function SolusiPress_Data_Transfer() {
    var self = this;    
    self.view_mode = ko.observable();
	self.item = ko.observable( new SolusiPress_TransferItem( null, cashflow_default_transfer ) );	
}

function SolusiPress_CashflowItem( id, data ) {
	
	var self = this;
	
	self.id = id;
	self.trx_no = data.trx_no;
	self.trx_date = data.trx_date;
	self.account_id = data.account_id;
	self.dc = ko.observable( data.dc );
	self.contact_id = ko.observable( data.contact_id ),
	self.from_to_name = data.from_to_name;
	self.organization = data.organization;
	self.amount = data.amount;
	self.note = data.note;
	self.post_id = data.post_id;
	self.object_id = data.object_id;
	
	self.post_edit_url = ko.pureComputed( function(){
		var url = 'post.php?post=' + this.post_id + '&action=edit';
		return url;
	}, self );		
	
}

function SolusiPress_TransferItem( id, data ) {
	
	var self = this;
	self.trf_date = data.trf_date;
	self.src_account = data.src_account;
	self.dst_account = data.dst_account;
	self.amount = data.amount;
	self.note = data.note;
	
}

( function( $ ) {
	
    var vm = new SolusiPress_Data_Cashflow();
    var trf = new SolusiPress_Data_Transfer();
    var dt = null;

	ko.bindingHandlers.select2 = {
	    after: ["options", "value"],
	    init: function (el, valueAccessor, allBindingsAccessor, viewModel) {
	        $(el).select2(ko.unwrap(valueAccessor()));
	        ko.utils.domNodeDisposal.addDisposeCallback(el, function () {
	            $(el).select2('destroy');
	        });
	    },
	    update: function (el, valueAccessor, allBindingsAccessor, viewModel) {
	        var allBindings = allBindingsAccessor();
	        var select2 = $(el).data("select2");
	        if ("value" in allBindings) {
	            var newValue = "" + ko.unwrap(allBindings.value);
	            if ((allBindings.select2.multiple || el.multiple) && newValue.constructor !== Array) {
	                select2.val([newValue.split(",")]);
	            }
	            else {
	                select2.val([newValue]);
	            }
	        }
	    }
	};

	function do_render_summary( obj ) {
		//console.log(obj);
		var html = '';
		
		html += '<div class="col-md-4 col-sm-12">';
		html += '<strong><u>Summary s/d ' + obj.last.date + '</u></strong>';
		html += '<table class="total-summary" width="100%">';
		html += '<tr><th>Total Pemasukan </th><td> : </td><td align="right"> ' + obj.last.d + '</td>';	
		html += '<tr><th>Total Pengeluaran </th><td> : </td><td align="right"> ' + obj.last.c + '</td>';	
		html += '<tr><th>Saldo akhir </th><td> : </td><td align="right"> ' + obj.last.b + '</td>';	
		html += '</table>';
		html += '</div>';
		
		html += '<div class="col-md-4 col-sm-12">';
		html += '<strong><u>Summary ' + obj.current.start + ' s/d ' + obj.current.end + '</u></strong>';
		html += '<table class="total-summary" width="100%">';
		html += '<tr><th>Total Pemasukan </th><td> : </td><td align="right"> ' + obj.current.d + '</td>';	
		html += '<tr><th>Total Pengeluaran </th><td> : </td><td align="right"> ' + obj.current.c + '</td>';	
		html += '<tr><th>Saldo akhir </th><td> : </td><td align="right"> ' + obj.current.b + '</td>';	
		html += '</table>';
		html += '</div>';
		
		$( '#data-summary' ).html( html );
	}
    
    function prepare_edit_data( the_id ) {
        $( '#solusipress-form-entry' ).jqmodal();
        $( '#solusipress-form-entry' ).find( '.frm-form-fields' ).loading( { message: 'Mengambil data...' } );   
        $( '.loading-overlay' ).css( 'z-index', 100000000 );

        $( '.datepicker' ).click( function(){
            $("#ui-datepicker-div").css("z-index", "999999");  
        } );

        $.ajax({
            method: 'GET',
            url: solusipress.ajax_action + '/' + the_id,
            success: function( resp ){
                data = $.parseJSON( resp );
                $( '#solusipress-form-entry' ).find( '.frm-form-fields' ).loading( 'toggle' );
                if( data.contact_id ) {
	                $( '#entry_contact_id' ).html( '<option value="' + data.contact_id + '">' + data.contact_lookup + '</option>' );
                }
                if( data.post_id != null || data.object_id != null ) {
	                vm.view_mode( 'detail' );
                }
                vm.item( new SolusiPress_CashflowItem( data.id, data ) );
            }
        });           
    }    	
	
    function do_load_data() {
	     
        dt = $('#cashflow-table').dataTable({
            //scrollX: true,
            //responsive: true,
            paging: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: solusipress.ajax_action,
                type: 'GET',
                data: function( data ) {
	                data.rowformat = 'datatable';
                    data.nominal_min = $( '#filter-nominal-min' ).val();
                    data.nominal_max = $( '#filter-nominal-max' ).val();
                    data.trxdate_min = $( '#filter-date-min' ).val();
                    data.trxdate_max = $( '#filter-date-max' ).val();
                    data.account = $( '#filter-account' ).val();
                    data.dc = $( '#filter-dc' ).val();
                },
                dataSrc: function( response ) {
	                do_render_summary( response.summary );                
	                return response.data;
                }
            },
            columns: [
                {
                    orderable: true,
                    render: function( data, type, row, meta ){
                        var html = '';                            
                        html += '<a href="#" class="action-links data-btn-delete" data-id="'+row[0]+'"><i class="dashicons dashicons-trash"></i></a>';
                        html += '&nbsp;&nbsp;';
                        html += '<a href="#solusipress-form-entry" ';
                        html += 'class="action-links data-btn-edit" data-id="'+row[0]+'">';
                        html += '<i class="dashicons dashicons-editor-paste-text"></i></a>'; 
                        return html;
	                }
	            }, 
                {
                    orderable: true,
                    className: 'dt-body-nowrap',
                    render: function( data, type, row, meta ){
                        return data;
	                }
	            }, 
                { className: 'dt-body-center' }, 
                { className: 'dt-body-nowrap' }, 
                { className: 'dt-body-nowrap' }, 
                null, null
            ],
            order: [ [2, 'desc'] ],
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
	
	$( '.select-filter' ).on( 'select2:select', function(e){
		dt.api().draw();
	});
    $( '#filter-nominal-min,#filter-nominal-max' ).on( 'keyup', function(e){
        dt.api().draw();     
    } );    
    $( '#filter-date-min,#filter-date-max' ).on( 'change', function(e) {
	    var d1 = $( '#filter-date-min' ).val();
	    var d2 = $( '#filter-date-max' ).val();
	    if( d1 != '' && d2 != '' ) {
			dt.api().draw();
		}
	} );
	
	$( '#sp-bisnis-refresh-btn' ).click( function(){
		$( '#filter-date-min' ).val('');
		$( '#filter-date-max' ).val('');
		$( '#filter-nominal-min' ).val('');
		$( '#filter-nominal-max' ).val('');
		dt.api().draw();
	} );
	
    $( document ).ready( function(){        
	    
        ko.applyBindings( vm, document.getElementById( 'solusipress-form-entry' ) );
        solusipress_functions.setVM( vm );
        $( '#btn-new-form-entry' ).click( function( e ){
            e.preventDefault();
            vm.view_mode('new');
            vm.item( new SolusiPress_CashflowItem( null, cashflow_default_item ) );
            $( '.datepicker' ).click( function(){
	            $("#ui-datepicker-div").css("z-index", "999999");  
	        } );
        } );       

        ko.applyBindings( trf, document.getElementById( 'solusipress-form-transfer' ) );
        $( '#btn-new-form-transfer' ).click( function( e ){
            e.preventDefault();
            trf.view_mode('new');
            trf.item( new SolusiPress_TransferItem( null, cashflow_default_transfer ) );
            $( '.datepicker' ).click( function(){
	            $("#ui-datepicker-div").css("z-index", "999999");  
	        } );
		} );
		
		$( '#solusipress-form-entry' ).on( $.jqmodal.OPEN, function( event, modal ){
			$( '#entry_contact_id' ).html( '' );	
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
        
        $( '#solusipress-form-transfer' ).find( '.frm-form-fields' ).submit( function(e) {
	    	e.preventDefault();
            $( '#solusipress-form-transfer' ).find( '.frm-form-fields' ).loading( { message: 'Sedang proses...' } );
            $( '.loading-overlay' ).css( 'z-index', 100000000 );
            var input = ko.toJS( trf.item() );  
            $.ajax( {
                method: 'post',
                url: solusipress.transfer_action,
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
                                json.message, {
                                className: 'success',
                                position: 'top right'
                            });                            
                            dt.api().ajax.reload( null, false );
                        } else {
                            $( '#transfer-notify-error' ).notify( 
                                json.message, {
                                className: 'error',
                                position: 'top right'
                            });
                        }
                    }                                        
                },
                always: function(){
					$( '#solusipress-form-transfer' ).find( '.frm-form-fields' ).loading( 'toggle' );	                
                }
			} );
	    	$( '#solusipress-form-transfer' ).find( '.frm-form-fields' ).loading( 'toggle' );
	    } );
	    
	    $( ".datepicker" ).datepicker();
	    $( ".datepicker" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        $( '.select2-input' ).select2();
        
        $( '#entry_contact_id' ).select2({
	        minimumInputLength: 2,
	        placeholder: 'Pilih data kontak atau isi nama lengkap dibawah',
	        allowClear: true,
	        ajax: { 
		        url: solusipress.contact_lookup, dataType: 'json',
		        processResults: function( data, page ){
			        var items = [];
			        _.each( data, function( row ){
				        var the_text = row.contact_name + ' <' + row.email + '>' + "\n";
				        the_text += (row.organization != null ? ' | ' + row.organization : ''); 
				        items.push( { id: row.id, text: the_text } );
				    } );
			        return {
				        results: items
			        }
		        }
		    },
    		dropdownParent: $('#solusipress-form-entry'),
        }).on('select2:select', function (evt) {
	        var data = evt.params.data;
	        vm.item().contact_id( data.id );
		}).on('select2:clear', function(evt){
			vm.item().contact_id('');
		});
        
        $( '.form-group .datepicker' ).focus( function(){
	        $(this).trigger( 'click' );
	    } );
	    
	} );	
	
} )( jQuery );