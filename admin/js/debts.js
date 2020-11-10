var debts_default_item = { 
	contact_id: '', account_id: '',
	dc: solusipress.dc, trx_date: '',
	amount: 0, due_date: '',
	installments: 1, total_paid: 0,
	last_paid: '', fullpaid_date: '',
	ref_number: '', note: '',
	first_created: '', last_updated: ''
};
var debtpayment_default_item = {
	title: '', due_date: '', 
	contact: '', organization: '',
	amount: 0, total: 0, balance: 0, 
	due_date: '', trx_date: '',
	payments: []	
};

function SolusiPress_Data_Debts() {    
    var self = this;    
    self.view_mode = ko.observable();
    self.form_title = ko.pureComputed( function(){
        var title = 'Transaksi Baru';
        if( this.view_mode() != 'new' ) {
            title = 'Ubah Transaksi';
        }
        return title
    }, self );    
    self.item = ko.observable( new SolusiPress_DebtsItem( null, debts_default_item ) );		
    self.payment = ko.observable( new SolusiPress_DebtPayment( null, debtpayment_default_item ) );
}

function SolusiPress_DebtsItem( id, data ) {    
	var self = this;
	self.id = id;
	self.trx_date = ko.observable( data.trx_date );
	self.account_id = data.account_id;
	self.dc = solusipress.dc;
	self.contact_id = ko.observable( data.contact_id );
	self.amount = data.amount;
	self.due_date = data.due_date;
	self.ref_number = data.ref_number;
	self.note = data.note;
}

function SolusiPress_DebtPayment( id, data ) {
	var self = this;
	var title = '';
	var the_title = 'Pembayaran Hutang/Piutang';
	if( data.dc == 'd' ) {
		the_title = 'Pembayaran Hutang';
		title = 'Hutang';
	} else {
		the_title = 'Penerimaan Piutang';
		title = 'Piutang';
	}
	self.debt_id = id;
	self.title = title;
	self.payment_title = the_title;
	self.contact = data.contact;
	self.organization = data.organization;
	self.due_date = data.due_date;
	self.total_debt = data.amount;
	self.debt_payment = data.total;
	self.debt_balance = data.balance;
	
	self.payments = ko.observableArray();
    self.payments_to_delete = [];    
    
    if( data.payments.length > 0 ) {
	    _.each( data.payments, function( row, index ){
		    self.payments.push( new SolusiPress_DebtPayment_Item( row.id, row ) );
		} );
    }
    self.addPayment = function(e){      
	    if( this.constructor.name == 'SolusiPress_Data_Debts' ) {
		    self.payments.push( new SolusiPress_DebtPayment_Item( null, {
                trx_date: '',
                account_id: '',
                amount: 0,
                ref_number: '',
                note: ''
            } ) );
	    }
	};
    self.removePayment = function(e) {
        if( this.constructor.name == 'SolusiPress_DebtPayment_Item' ) {
            if( this.id != null ) {
                var obj = this;
                var obj_id = obj.id;
                jQuery.confirm( {
                    title: 'Konfirmasi',
                    content: "Hapus pembayaran? Data tidak akan terhapus sebelum transaksi disimpan",
                    boxWidth: '350px',
                    buttons: {
                        no: function(){},
                        yes: function(){
                            self.payments_to_delete.push( obj_id );                
                            self.payments.remove( obj );
                            console.log( self.payments );
                        }
                    }
                } );
            } else {
                self.payments.remove( this );
            }
        }
    }    	
}

function SolusiPress_DebtPayment_Item( id, data ) {
	this.id = id;
	this.trx_date = ko.observable(data.trx_date);
	this.account_id = data.account_id;
	this.amount = data.amount;
	this.ref_number = data.ref_number;
	this.note = data.note;
}

( function( $ ) {
	
    var vm = new SolusiPress_Data_Debts();
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
                var data = $.parseJSON( resp );
                $( '#solusipress-form-entry' ).find( '.frm-form-fields' ).loading( 'toggle' );
                if( data.contact_id ) {
	                $( '#entry_contact_id' ).html( '<option value="' + data.contact_id + '">' + data.contact_lookup + '</option>' );
                }
                vm.item( new SolusiPress_DebtsItem( data.id, data ) );
            }
        });           
    }    	
    
    function do_save_payment(evt) {
	    
	    evt.preventDefault();
        $( '#solusipress-form-payment' ).find( '.frm-form-fields' ).loading( { message: 'Menyimpan data...' } );   
        $( '.loading-overlay' ).css( 'z-index', 100000000 );
	    this.inputs = {
			payments: $.parseJSON( ko.toJSON( vm.payment().payments ) ),
			to_delete: $.parseJSON( ko.toJSON( vm.payment().payments_to_delete ) )
	    };
		this.debt_id = vm.payment().debt_id
	    this.send_payment = function(){
		    var self = this;
			$.ajax( {
				method: 'POST',
				url: solusipress.payment_action+self.debt_id+'/update-payment/',
				data: inputs,
				success: function( response ) {
                    var json = $.parseJSON( response ); 
                    if( json.status ) {
						$( '#solusipress-form-payment' ).find( '.frm-form-fields' ).loading( 'toggle' );
			            $.jqmodal.close();
			            $( '#payment-notifyjs' ).notify( 
			                "Transaksi berhasil dilakukan", {
			                className: 'success',
			                position: 'top right'
			            });                            
			            dt.api().ajax.reload( null );					
			        }
				}
			} );			    
	    }
		this.send_payment();
	    
    }
    
    var form_payment_calls = 0;
    function prepare_payment_form( the_id ) {
        $( '#solusipress-form-payment' ).jqmodal();
        $( '#solusipress-form-payment' ).find( '.frm-form-fields' ).loading( { message: 'Mengambil data...' } );   
        $( '.loading-overlay' ).css( 'z-index', 100000000 );
                
        $( 'body' ).on( 'focus', '.datepicker', function(){	    
	        
	        if( $(this).hasClass('payment-item') ) {
		        var bef = $(this).val();
	        	$(this).datepicker();	        	
				$(this).datepicker( "option", "dateFormat", "yy-mm-dd" );
				if( bef != '' ) {
					$(this).val( bef );
				}
	        }
	        
	    } );	    	    
	    
		if( form_payment_calls == 0 ) {	    
			
			$( 'body' ).on( 'focus', '.datepicker', function(){
		        var _x = window.setInterval(function(){
					$(".ui-datepicker").css("z-index", "999999");
					window.clearInterval( _x );
				}, 100);
				
			});			 	        
			
	        $( '#solusipress-form-payment' ).find( '#solusipress-payment-save' ).click( function(event){
		        do_save_payment(event);
	        } );
	    }
	    
	    form_payment_calls++;
        
        $.ajax( {
	        method: 'GET',
	        url: solusipress.payment_action,
	        data: { 'debt_id': the_id },
	        success: function( resp ) {
                $( '#solusipress-form-payment' ).find( '.frm-form-fields' ).loading( 'toggle' );
                var the_org = resp.meta.debt.organization;
                if( the_org == null || the_org == '' ) the_org = 'N/A';
                var data = {
	                contact: resp.meta.debt.contact_name,
	                organization: the_org,
	                dc: resp.meta.debt.dc,
	                due_date: resp.meta.debt.due_date,
	                amount: resp.meta.debt.amount,
	                total: resp.meta.payment.total,
	                balance: resp.meta.payment.balance
                };
                data.payments = [];
                _.each( resp.data, function( obj, idx ){
	                data.payments.push({
		                id: obj.id,
		                trx_date: obj.trx_date,
		                account_id: obj.account_id,
		                amount: obj.amount,
		                ref_number: obj.ref_number,
		                note: obj.note
	                });
	            } );
		        vm.payment( new SolusiPress_DebtPayment( the_id, data ) );
	        }
        } );
    }

    function do_load_data() {
        dt = $('#debts-table').dataTable({
	        scrollx: true,
            paging: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: solusipress.ajax_action,
                type: 'GET',
                data: function( data ) {
	                data.rowformat = 'datatable';
	                data.dc = solusipress.dc;
	                data.completed = $( '#filter-status' ).val();
                    data.duedate_min = $( '#filter-due-date-min' ).val();
                    data.duedate_max = $( '#filter-due-date-max' ).val();
                    data.trxdate_min = $( '#filter-date-min' ).val();
                    data.trxdate_max = $( '#filter-date-max' ).val();
                    data.account = $( '#filter-account' ).val();
                },
                dataSrc: function( response ) {
	                do_render_summary( response.summary );                
	                return response.data;
                }
            },
            columns: [
                {
                    orderable: true,
                    className: 'dt-body-nowrap',
                    render: function( data, type, row, meta ){
                        var html = '';                            
                        html += '<a href="#" class="action-links data-btn-delete" data-id="'+row[0]+'"><i class="dashicons dashicons-trash"></i></a>';
                        html += '&nbsp;&nbsp;';
                        html += '<a href="#solusipress-form-entry" ';
                        html += 'class="action-links data-btn-edit" data-id="'+row[0]+'">';
                        html += '<i class="dashicons dashicons-editor-paste-text"></i></a>&nbsp;&nbsp;'; 
                        html += '<a href="#" title="Tambah Pembayaran" alt="Tambah Pembayaran"';
                        html += 'class="action-links data-btn-payment" data-id="'+row[0]+'">';
                        html += '<i class="dashicons dashicons-welcome-add-page"></i></a>';
                        return html;
	                }
	            },
                {
                    orderable: true,
                    className: 'dt-body-nowrap'
	            }, 
                { className: 'dt-body-nowrap' }, 
                { className: 'dt-body-nowrap' }, 
                null, null, null 
            ],
            order: [ [0, 'desc'] ],
            drawCallback: function( settings ){
	            
                var tbl = this;
                $( '.data-btn-edit' ).click( function( e ){
                    e.preventDefault();
                    vm.view_mode( 'edit' );
                    var the_id = $(this).attr( 'data-id' );
                    prepare_edit_data( the_id );                     
                } );
                
                $( '.data-btn-payment' ).click( function(e) {
	            	e.preventDefault();
	            	vm.view_mode( 'payment' );
                    var the_id = $(this).attr( 'data-id' );
                    prepare_payment_form( the_id );
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
            
		});	
		
		solusipress_functions.setDataTable( dt );					
	}
	
	
	function do_render_summary( obj ) {
		var html = '';
		html += '<div class="col-md-4 col-sm-12">';
		html += '<strong><u>Summary</u></strong>';
		html += '<table class="total-summary" width="100%">';
		html += '<tr><th>Total ' + solusipress.label + ' </th><td> : </td><td align="right"> ' + obj.formatted.amount + '</td>';	
		html += '<tr><th>Total Pembayaran </th><td> : </td><td align="right"> ' + obj.formatted.total_paid + '</td>';	
		html += '<tr><th>Sisa ' + solusipress.label + ' </th><td> : </td><td align="right"> ' + obj.formatted.balance + '</td>';	
		html += '</table>';
		html += '</div>';
		$( '#data-summary' ).html( html );
	}
		
	$( '.select-filter' ).on( 'select2:select', function(e){
		dt.api().draw();
	});
    $( '#filter-status' ).on( 'change', function(e){
		dt.api().draw();
	});
    $( '#filter-date-min,#filter-date-max' ).on( 'change', function(e){
	    var d1 = $( '#filter-date-min' ).val();
	    var d2 = $( '#filter-date-max' ).val();
	    if( d1 != '' && d2 != '' ) {
			dt.api().draw();
		}
	} );
    $( '#filter-due-date-min,#filter-due-date-max' ).on( 'change', function(e){
	    var d1 = $( '#filter-due-date-min' ).val();
	    var d2 = $( '#filter-due-date-max' ).val();
	    if( d1 != '' && d2 != '' ) {
			dt.api().draw();
		}
	} );
	
	$( '#sp-bisnis-refresh-btn' ).click( function(){
		$( '#filter-date-min' ).val('');
		$( '#filter-date-max' ).val('');
		$( '#filter-due-date-min' ).val('');
		$( '#filter-due-date-max' ).val('');
		dt.api().draw();
	} );
	    
    $( document ).ready( function(){        
	    
        ko.applyBindings( vm, document.getElementById( 'solusipress-forms' ) );
        solusipress_functions.setVM( vm );
        $( '#btn-new-form-entry' ).click( function( e ){
            e.preventDefault();
            vm.view_mode('new');
            vm.item( new SolusiPress_DebtsItem( null, debts_default_item ) );
            $( '.datepicker' ).click( function(){
	            $("#ui-datepicker-div").css("z-index", "999999");  
	        } );
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
	    
	    $( ".datepicker" ).datepicker();
	    $( ".datepicker" ).datepicker( "option", "dateFormat", "yy-mm-dd" );
        $( '.select2-input' ).select2();
        
        $( '#entry_contact_id' ).select2({
	        minimumInputLength: 2,
	        placeholder: 'Pilih data kontak',
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