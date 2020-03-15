<div class="wrap sp-bisnis-page solusipress-bisnis-container">
    <h1 class="wp-heading-inline">Kas Masuk/Keluar</h1>
    <a href="#solusipress-form-entry" rel="jqmodal:open" id="btn-new-form-entry" class="page-title-action">Tambah Transaksi</a>
    <a href="#solusipress-form-transfer" rel="jqmodal:open" id="btn-new-form-transfer" class="page-title-action">Transfer</a>
    <a href="#" id="sp-bisnis-refresh-btn" class="page-title-action">Refresh</a>
    <hr class="wp-header-end">
    <!-- BEGIN Filter -->
    <div class="row dt-top-filter">
        
        <div class="col-md-6" style="padding: 5px 5px;">
            <strong>Data Filter</strong>
            <hr style="margin: 6px 0px;">
			<div class="row row-detail">
		        <div class="col-md-6">
			        <select class="select2-input form-control select-filter" id="filter-account" style="width:100%">
				        <option value="">Rekening / Kas</option>
				        <?php foreach( $list_accounts['data'] as $acct ): ?>
				        <option value="<?= $acct['id'] ?>"><?= $acct['text'] ?></option>
				        <?php endforeach; ?>				        
			        </select>
		        </div>
		        <div class="col-md-6">
			        <select class="select2-input form-control select-filter" id="filter-dc" style="width:100%">
				        <option value="">Arus Kas</option>
				        <option value="d">Pemasukan</option>
				        <option value="c">Pengeluaran</option>
			        </select>
		        </div>
			</div>
        </div>

        <div class="col-md-3" style="padding: 5px 5px;">
            <strong>Tanggal Nota / Transaksi</strong>
            <hr style="margin: 6px 0px;">
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
        
        <div class="col-md-3" style="padding:5px 5px;">
            <strong>Nominal</strong>
            <hr style="margin: 6px 0px;">
            <div class="row row-detail">
	            <div class="col-md-6 col-xs-6">
	                <input id="filter-nominal-min" class="form-control" 
	                       type="number" placeholder="Min."> 
	            </div>
	            <div class="col-md-6 col-xs-6">
	                <input id="filter-nominal-max" class="form-control" 
	                       type="number" placeholder="Max.">
	            </div>
            </div>
        </div>
                
    </div>
    <!-- END Filter -->

    
    <div class="container-notifyjs" style="top: 30px; right: 0px;"></div>        

    <table id="cashflow-table" class="table table-striped table-hover">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th style="min-width:100px;">No Transaksi/Nota</th>
                <th>Tanggal Nota</th>
                <th style="min-width:150px;"><div>Diterima dari (D)</div><div style="border-top:1px solid #000">Diberikan kepada (C)</div></th>
                <th>D/C</th>
                <th style="min-width:250px;">Rekening / Kas</th>
                <th>Nominal</th>
            </tr>
        </thead>
    </table>    
    
    <div id="data-summary"></div>

	<div id="solusipress-form-entry" class="jqmodal" style="max-width:650px;">    
      	<!-- start form -->
		<form method="post" class="frm-form-fields">
		    <h3 id="dialogTitle" data-bind="text: form_title"></h3>     
		    <hr>
            <div class="form-notify-error"></div>
		    <input type="hidden" data-bind="value: item().id">
            <div class="row">        
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_trx_no">No Transaksi/Nota*</label>
                        <input type="text" class="form-control" id="entry_trx_no" data-bind="value: item().trx_no" minlength="3" required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_trx_date">Tanggal Nota*</label>
                        <input type="text" class="form-control datepicker" id="entry_trx_date" data-bind="value: item().trx_date" autocomplete="off" required>
                    </div>
                </div>
                <div class="col-lg-12">
		            <p class="description">Isi nomor transaksi dengan nomor invoice/tagihan/nota untuk melakukan pembayaran. 
			            Jika tidak ada, gunakan pengkodean sendiri untuk memudahkan pencarian</p>
                </div>
            </div>			    
            <div class="row">        
                <div class="col-md-12">
                    <div class="form-group">
                        <label>Arus Kas</label>
	                    <div>
	                        <label for="entry_dr" class="radio-horizontal">
		                        <input type="radio" class="form-control" id="entry_dr" value="d" data-bind="checked: item().dc"> Pemasukan
	                        </label>
	                        <label for="entry_cr" class="radio-horizontal">
	                        	<input type="radio" class="form-control" id="entry_cr" value="c" data-bind="checked: item().dc"> Pengeluaran
	                        </label>
                        </div>
                    </div>
                </div>
            </div>		
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
	                    <label for="entry_account_id">Rekening/Kas/Bank</label>
	                    <select class="form-control" id="entry_account_id" style="width:100%"
		                    	data-bind="options: solusipress.accounts,
			                    		   select2: { dropdownParent: jQuery('#solusipress-form-entry') },
			                    		   optionsText: 'text', optionsValue: 'id', value: item().account_id">
	                    </select>
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
                <div class="col-lg-12">
                    <div class="form-group">
	                    <label for="contact_id" data-bind="text: from_to_label"></label>
	                    <select class="form-control" 
		                    data-bind="value: item().contact_id()" 
			                id="entry_contact_id" style="width:100%">
	                    </select>
                    </div>
                </div>
            </div>
            
            <div class="row" data-bind="visible: item().contact_id() == '' || item().contact_id() == null || item().contact_id() == undefined">
                <div class="col-lg-6">
                    <div class="form-group">
	                    <label for="from_to_name">Nama Lengkap</label>
                        <input type="text" class="form-control" id="entry_from_to_name" data-bind="value: item().from_to_name">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_amount">Organisasi/Alamat</label>
                        <input type="text" class="form-control" id="entry_organization" data-bind="value: item().organization">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
	                    <label for="entry_note">Catatan</label>
                        <input type="text" class="form-control" id="entry_note" data-bind="value: item().note">
                    </div>
                </div>
            </div>
            
            <hr style="margin-top:5px;margin-bottom:10px;">
            <p class="submit">
                <input type="submit" value="Simpan Transaksi"
                       class="button-primary" id="solusipress-button-save"
                       data-bind="visible: item().post_id == null && item().object_id==null">
                       
				<span style="display:none" data-bind="hidden: item().post_id == null">
					Tidak dapat diedit pada layar ini. Silahkan edit pada								                       
	                <a data-bind="attr:{href: item().post_edit_url}"><strong>halaman transaksi</strong></a>
				</span>
            </p>    
            	    
		</form>	      	
      	<!-- endf form -->
	</div>    

	<div id="solusipress-form-transfer" class="jqmodal" style="max-width:600px;">    
      	<!-- start form -->
		<form method="post" class="frm-form-fields" autocomplete="on">
		    <h3>Transfer</h3>     
		    <hr>
            <div id="transfer-notify-error" class="form-notify-error"></div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
	                    <label for="src_account">Rekening/Kas Sumber*</label>
	                    <select class="form-control" id="src_account" style="width:100%"
		                    	data-bind="options: solusipress.accounts,
			                    		   select2: { dropdownParent: jQuery('#solusipress-form-transfer') },
			                    		   optionsText: 'text', optionsValue: 'id', value: item().src_account">
	                    </select>
                    </div>
                </div>
                <div class="col-lg-6">
	                    <label for="dst_account">Rekening/Kas Tujuan*</label>
	                    <select class="form-control" id="dst_account" style="width:100%"
		                    	data-bind="options: solusipress.accounts,
			                    		   select2: { dropdownParent: jQuery('#solusipress-form-transfer') },
			                    		   optionsText: 'text', optionsValue: 'id', value: item().dst_account">
	                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group">
	                    <label for="transfer_date">Tanggal*</label>
                        <input type="text" class="form-control datepicker" id="transfer_date" data-bind="value: item().trf_date" autocomplete="off" required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
	                    <label for="transfer_amount">Nominal/Jumlah*</label>
                        <input type="text" class="form-control" id="transfer_amount" data-bind="value: item().amount" required>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
	                    <label for="transfer_note">Catatan</label>
                        <input type="text" class="form-control" id="transfer_note" data-bind="value: item().note">
                    </div>
                </div>
            </div>
            
            <hr style="margin-top:5px;margin-bottom:10px;">
            <p class="submit">
                <input type="submit" value="Simpan Transaksi"
                       class="button-primary" id="solusipress-button-save-transfer">
            </p>    
            
		</form>
	</div>
    
</div>

