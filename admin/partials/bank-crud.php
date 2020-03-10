<div class="wrap sp-bisnis-page solusipress-bisnis-container">
    <h1 class="wp-heading-inline">Kas / Bank</h1>
    <a href="#solusipress-form-entry" rel="jqmodal:open" id="btn-new-form-entry" class="page-title-action">Tambah Data</a>
    <hr class="wp-header-end">
    <ul class="subsub"></ul><br>
    
    <div class="container-notifyjs" style="top: 30px; right: 0px;"></div>        
    
    <table id="bank-table" class="table table-striped table-hover">
        <thead>
            <tr>
                <th style="width:100px;">&nbsp;</th>
                <th>Kas / Bank</th>
                <th>Nomor Rekening</th>
                <th>Nama Pemilik Rekening</th>
                <th style="width:60px;">Publik?</th>
                <th>Keterangan</th>
            </tr>
        </thead>
    </table>    
        
	<div id="solusipress-form-entry" class="jqmodal" style="max-width:650px; display:none;">    
      	<!-- start form -->
		<form method="post" class="frm-form-fields">
		    <h3 id="dialogTitle" data-bind="text: form_title"></h3>     
		    <hr>
		    <input type="hidden" data-bind="value: item().id">
		    
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
	                    <label for="entry_bank">Nama Akun/Kas/Bank</label>
                        <input type="text" class="form-control" id="entry_bank" data-bind="value: item().bank" required>
                    </div>
                </div>
            </div>
		    
            <div class="row">        
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="account_number">Nomor Rekening</label>
                        <input type="text" class="form-control" id="account_number" data-bind="value: item().account_number">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="account_name">Nama Pemilik Akun</label>
                        <input type="text" class="form-control" id="account_name" data-bind="value: item().account_name">
                    </div>
                </div>
            </div>			    
            
            <div class="row">
                <div class="col-md-12">
					<label class="checkbox-inline">
						<input type="checkbox" data-bind="checked: item().public_checked">Rekening Publik ?
					</label>	
					<div>Rekening akan ditampilkan pada halaman publik dengan shortcode : [solusipress_bisnis_bank_accounts]</div>
					<br>
                </div>
            </div>
            
            <div class="row">
                <div class="col-lg-12">
                    <div class="form-group">
                        <label for="logo_url">URL Logo</label>
                        <input type="text" class="form-control" id="logo_url" data-bind="value: item().logo_url">
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
	                    <label for="entry_description">Keterangan</label>
                        <input type="text" class="form-control" id="entry_description" data-bind="value: item().description">
                    </div>
                </div>
            </div>
            
            <hr style="margin-top:5px;margin-bottom:10px;">
            <p class="submit">
                <input type="submit" value="Simpan Data"
                       class="button-primary" id="solusipress-button-save">
            </p>    
            	    
		</form>	      	
      	<!-- end form -->
	</div>    

</div>

