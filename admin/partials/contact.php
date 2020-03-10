<div class="wrap sp-bisnis-page solusipress-bisnis-container">
    <h1 class="wp-heading-inline">Data Kontak</h1>
    <a href="#solusipress-form-entry" rel="jqmodal:open" id="btn-new-form-entry" class="page-title-action">Tambah Data</a>
    <hr class="wp-header-end">
    
    <!-- BEGIN Filter -->
    <div class="row dt-top-filter">
        
        <div class="col-md-12" style="padding: 5px 5px;">
            <strong>Data Filter</strong>
            <hr style="margin: 6px 0px;">
			<div class="row row-detail">
			</div>
			<div class="row">
				<div class="col-md-4">
                    <select class="form-control" id="filter_type">
	                    <option value="">Pilih Kategori</option>
	                    <?php foreach( $contact_types['data'] as $ct ): ?>
	                    <option value="<?= $ct['id'] ?>"><?= $ct['name'] ?></option>
	                    <?php endforeach; ?>
                    </select>
				</div>
			</div>
        </div>
    </div>
    <!-- END Filter -->
    
    <ul class="subsub"></ul><br>
    
    <div class="container-notifyjs" style="top: 30px; right: 0px;"></div>        
    
    <table id="contact-table" class="table table-striped table-hover">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th>Nama Lengkap</th>
                <th>Organisasi/Perusahaan</th>
                <th>Email</th>
                <th>WhatsApp</th>
                <th>Catatan</th>
            </tr>
        </thead>
    </table>    
        
	<div id="solusipress-form-entry" class="jqmodal" style="max-width:650px; margin-top: 20px; display:none;">    
      	<!-- start form -->
		<form method="post" class="frm-form-fields">
		    <h3 id="dialogTitle" data-bind="text: form_title"></h3>     
		    <hr>
		    <input type="hidden" data-bind="value: item().id">
		    
            <div class="row">        
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_first_name">Nama depan*</label>
                        <input type="text" class="form-control" id="entry_first_name" data-bind="value: item().first_name" minlength="3" required>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_last_name">Nama belakang*</label>
                        <input type="text" class="form-control" id="entry_last_name" data-bind="value: item().last_name">
                    </div>
                </div>
            </div>			    
			
            <div class="row">        
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_contact_type_id">Kategori*</label>
	                    <select class="form-control" id="entry_contact_type_id"
		                    	data-bind="options: solusipress.contact_types.data,
			                    		   optionsText: 'name',
				                    	   optionsValue: 'id',
					                       value: item().contact_type_id">
	                    </select>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_email">Email*</label>
                        <input type="email" class="form-control" id="entry_email" data-bind="value: item().email" required>
                    </div>
                </div>
            </div>			    
					    
            <div class="row">        
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_first_name">No Telepon</label>
                        <input type="text" class="form-control" id="entry_phone" data-bind="value: item().phone">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_whatsapp">No WhatsApp</label>
                        <input type="text" class="form-control" id="entry_whatsapp" data-bind="value: item().whatsapp" placeholder="contoh: 6281234567890">
                    </div>
                </div>
            </div>		
            	    
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
	                    <label for="entry_organization">Organisasi / Perusahaan</label>
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

            <div class="row">        
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_facebook">Facebook URL</label>
                        <input type="text" class="form-control" id="entry_facebook" data-bind="value: item().facebook">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_instagram">Instagram URL</label>
                        <input type="text" class="form-control" id="entry_instagram" data-bind="value: item().instagram">
                    </div>
                </div>
            </div>		
		    
            <div class="row">        
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_twitter">Twitter URL</label>
                        <input type="text" class="form-control" id="entry_twitter" data-bind="value: item().twitter">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_linkedin">LinkedIn URL</label>
                        <input type="text" class="form-control" id="entry_linkedin" data-bind="value: item().linkedin">
                    </div>
                </div>
            </div>		

            <hr style="margin-top:5px;margin-bottom:10px;">
            <div class="row submit">
	            <div class="col-lg-6">
	                <input type="submit" value="Simpan Data"
	                       class="button-primary" id="solusipress-button-save">
	            </div>
	            <div class="col-lg-6">
		            <div id="form-notify-error"></div>		            
	            </div>
            </div>    
            	    
		</form>	      	
      	<!-- end form -->
	</div>    

</div>

