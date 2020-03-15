<div class="wrap sp-bisnis-page solusipress-bisnis-container">
    <h1 class="wp-heading-inline">Transaksi <?php echo $trx_name ?></h1>
    <a href="#solusipress-form-entry" rel="jqmodal:open" id="btn-new-form-entry" class="page-title-action">Tambah Transaksi</a>
    <a href="#" id="sp-bisnis-refresh-btn" class="page-title-action">Refresh</a>
    <hr class="wp-header-end">
    
    <div class="row dt-top-filter">
        <div class="col-md-4" style="padding: 5px 5px;">
	        <div>
		        Data Filter
		        <hr style="margin: 6px 0px;">
	        </div>
	        <select class="select2-input form-control select-filter" id="filter-account" style="width:100%">
		        <option value="">Rekening / Kas</option>
		        <?php foreach( $list_accounts['data'] as $acct ): ?>
		        <option value="<?= $acct['id'] ?>"><?= $acct['text'] ?></option>
		        <?php endforeach; ?>				        
	        </select>
        </div>
        <div class="col-md-3" style="padding: 5px 5px;">
	        <div>
		        Tanggal <?php echo $trx_name; ?>
		        <hr style="margin: 6px 0px;">
	        </div>
            <div class="row row-detail">
	            <div class="col-md-6 col-xs-6">
	                <input id="filter-date-min" class="form-control datepicker" 
	                       type="text" placeholder="Min." autocomplete="off"> 
	            </div>
	            <div class="col-md-6 col-xs-6">
	                <input id="filter-date-max" class="form-control datepicker" 
	                       type="text" placeholder="Max." autocomplete="off">
	            </div>
            </div>
        </div>
        <div class="col-md-3" style="padding: 5px 5px;">
	        <div>
		        Jatuh Tempo
		        <hr style="margin: 6px 0px;">
	        </div>
            <div class="row row-detail">
	            <div class="col-md-6 col-xs-6">
	                <input id="filter-due-date-min" class="form-control datepicker" 
	                       type="text" placeholder="Min." autocomplete="off"> 
	            </div>
	            <div class="col-md-6 col-xs-6">
	                <input id="filter-due-date-max" class="form-control datepicker" 
	                       type="text" placeholder="Max." autocomplete="off">
	            </div>
            </div>
        </div>
        <div class="col-md-2" style="padding: 5px 5px;">
	        <div>
		        Status
		        <hr style="margin: 6px 0px;">
	        </div>
	        <select class="form-control select-filter" id="filter-status" style="width:100%">
		        <option value="0">Belum Lunas</option>
		        <option value="1">Lunas</option>
	        </select>
        </div>
    </div>
        
    <div class="container-notifyjs" style="top: 30px; right: 0px;"></div>        
    <div id="payment-notifyjs" style="top: 30px; right: 0px;"></div>        

    <table id="debts-table" class="table table-striped table-hover">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Tanggal</th>
                <th style="width:150px"><?php echo $contact_heading ?></th>
                <th>Nominal</th>
                <th style="width:250px">Rekening / Kas</th>
                <th>Jatuh Tempo</th>
                <th>Total Bayar</th>
            </tr>
        </thead>
    </table>    
    
    <div id="data-summary"></div>
    
    <div id="solusipress-forms">
		<div id="solusipress-form-entry" class="jqmodal" style="max-width:650px; display:none;">    
	      	<!-- start form -->
			<form method="post" class="frm-form-fields">
			    <h3 id="dialogTitle" data-bind="text: form_title"></h3>     
			    <hr>
			    <input type="hidden" data-bind="value: item().id">
			    
	            <div class="row">
	                <div class="col-lg-12">
	                    <div class="form-group">
		                    <label for="contact_id"><?php echo $contact_heading; ?>*</label>
		                    <select class="form-control" 
			                    data-bind="value: item().contact_id()" 
				                id="entry_contact_id" style="width:100%">
		                    </select>
	                    </div>
	                </div>
	            </div>
	
	            <div class="row">        
	                <div class="col-lg-12">
	                    <div class="form-group">
		                    <label for="entry_account_id">Rekening/Kas/Bank*</label>
		                    <select class="form-control" id="entry_account_id" style="width:100%"
			                    	data-bind="options: solusipress.accounts,
				                    		   select2: { dropdownParent: jQuery('#solusipress-form-entry') },
				                    		   optionsText: 'text', optionsValue: 'id', value: item().account_id">
		                    </select>
	                    </div>
	                </div>
	            </div>	
	            
	            <div class="row">
	                <div class="col-lg-6">
	                    <div class="form-group">
	                        <label for="entry_trx_date">Tanggal*</label>
	                        <input type="text" class="form-control datepicker" id="entry_trx_date" data-bind="value: item().trx_date" autocomplete="off" required>
	                    </div>
	                </div>
	                <div class="col-lg-6">
	                    <div class="form-group">
	                        <label for="entry_amount">Jumlah/Nominal*</label>
	                        <input type="text" class="form-control" id="entry_amount" data-bind="value: item().amount" required>
	                    </div>
	                </div>
	            </div>            		    
	
	            <div class="row">
	                <div class="col-lg-6">
	                    <div class="form-group">
	                        <label for="entry_trx_date">Tanggal Jatuh Tempo*</label>
	                        <input type="text" class="form-control datepicker" id="entry_due_date" data-bind="value: item().due_date" autocomplete="off" required>
	                    </div>
	                </div>
	                <div class="col-lg-6">
	                    <div class="form-group">
	                        <label for="entry_amount">Nomor Referensi</label>
	                        <input type="text" class="form-control" id="entry_ref_number" data-bind="value: item().ref_number">
	                    </div>
	                </div>
	            </div>          
	            <div class="row">        
	                <div class="col-lg-12">
	                    <div class="form-group">
	                        <label for="entry_note">Catatan</label>
	                        <input type="text" class="form-control" id="entry_note" data-bind="value: item().note">
	                    </div>
	                </div>
	            </div>
	              		    
	            <hr style="margin-top:5px;margin-bottom:10px;">
	            <p class="submit">
	                <input type="submit" value="Simpan Transaksi"
	                       class="button-primary" id="solusipress-button-save">
	            </p>    
	
			</form>
		</div>
		
		<div id="solusipress-form-payment" class="jqmodal" style="max-width:900px; display:none;">    
	      	<!-- start form -->
			<form method="post" id="frm-debt-payment" class="frm-form-fields">
				
			    <h3 data-bind="text: payment().payment_title"></h3>     
			    <hr>
			    <div class="row" style="margin-bottom: 10px;">
				    <div class="col-lg-6">
					    <strong>Nama Kontak</strong>
					    <div data-bind="text: payment().contact"></div>
				    </div>
				    <div class="col-lg-6">
					    <strong>Organisasi/Perusahaan</strong>
					    <div data-bind="text: payment().organization"></div>
				    </div>
			    </div>
			    <div class="row">
				    <div class="col-lg-3">
					    <strong>Jatuh Tempo</strong>
					    <div data-bind="text: payment().due_date"></div>
				    </div>
				    <div class="col-lg-3">
					    <strong>Total <span data-bind="text: payment().title"></span></strong>
					    <div data-bind="text: jQuery.number( payment().total_debt, 0, ',', '.' )"></div>
				    </div>
				    <div class="col-lg-3">
					    <strong>Total Pembayaran</strong>
					    <div data-bind="text: jQuery.number( payment().debt_payment, 0, ',', '.' )"></div>
				    </div>
				    <div class="col-lg-3">
					    <strong>Sisa <span data-bind="text: payment().title"></span></strong>
					    <div data-bind="text: jQuery.number( payment().debt_balance, 0, ',', '.' )"></div>
				    </div>
	                <div class="col-lg-12" style="margin-top:20px;">
	                    <strong style="font-size:15px;">Daftar Pembayaran</strong>
	                    <hr style="margin-top:5px;margin-bottom:10px;">
	                    <table class="table table-striped">
	                        <thead>
	                            <tr>
	                                <th>Tanggal</th>
	                                <th style="width:250px;">Kas/Bank</th>
	                                <th>Nominal</th>
	                                <th>No Referensi</th>
	                                <th style="width:200px;">Catatan</th>
	                                <th style="width:50px;">&nbsp;</th>
	                            </tr>
	                        </thead>
							<tbody data-bind="foreach: payment().payments">
	                            <tr class="row-item-payment">
	                                <td>
	                                    <input type="hidden" data-bind="value: $data.id">
	                                    <input type="text" class="form-control payment-item datepicker" style="width:100px;"
	                                           data-bind="value: $data.trx_date" autocomplete="off" required>
	                                </td>
	                                <td>
		                                <select class="form-control" style="width:250px;"
						                    	data-bind="options: solusipress.accounts,
							                    		   select2: { dropdownParent: jQuery('#solusipress-form-payment') },
							                    		   optionsText: 'text', optionsValue: 'id', value: $data.account_id">
					                    </select>
	                                </td>
	                                <td><input type="text" class="form-control" data-bind="value: $data.amount" required></td>
	                                <td><input type="text" class="form-control" data-bind="value: $data.ref_number"></td>
	                                <td><input type="text" class="form-control" data-bind="value: $data.note"></td>
	                                <td>
		                                <a href="#" data-bind="click:$parent.payment().removePayment" 
			                               class="action-links data-btn-delete">
			                                <i class="dashicons dashicons-trash"></i> 
			                            </a>
			                        </td>
	                            </tr>
							</tbody> 
	                        <tfoot>
	                            <tr>
	                                <td colspan="6" align="right">
	                                    <button type="button" class="button" 
	                                            id="btn-add-payment-item"
	                                            data-bind="click: payment().addPayment">
	                                        Tambah Pembayaran
	                                    </button>
	                                </td>
	                            </tr>
	                        </tfoot>
	                    </table>
	                </div>
	                
			    </div>
	            <hr style="margin-top:5px;margin-bottom:10px;">
	            <p class="submit">
	                <input type="submit" value="Simpan Transaksi"
	                       class="button-primary" id="solusipress-payment-save">
	            </p>
	                
			</form>
		</div>
    </div>		    
</div>