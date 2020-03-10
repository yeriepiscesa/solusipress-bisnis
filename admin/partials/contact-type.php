<div class="wrap sp-bisnis-page solusipress-bisnis-container">
    <h1 class="wp-heading-inline">Kategori Kontak</h1>
    <a href="#solusipress-form-entry" rel="jqmodal:open" id="btn-new-form-entry" class="page-title-action">Tambah Data</a>
    <hr class="wp-header-end">
    <ul class="subsub"></ul><br>
    
    <div class="container-notifyjs" style="top: 30px; right: 0px;"></div>        
    
    <table id="contact-type-table" class="table table-striped table-hover">
        <thead>
            <tr>
                <th style="width:100px;">&nbsp;</th>
                <th>Nama</th>
                <th>Level</th>
                <th>Default?</th>
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
                <div class="col-md-6">
                    <div class="form-group">
	                    <label for="entry_name">Nama</label>
                        <input type="text" class="form-control" id="entry_name" data-bind="value: item().name">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
	                    <label for="entry_color">Warna</label>
                        <input type="color" class="form-control" id="entry_color" data-bind="value: item().color">
                    </div>
                </div>
            </div>
		    
            <div class="row">        
                <div class="col-lg-6">
                    <div class="form-group">
                        <label for="entry_ordering">Level</label>
                        <input type="text" class="form-control" id="entry_ordering" data-bind="value: item().ordering">
                    </div>
                </div>
                <div class="col-lg-6">
	                <label>Default kategori</label>
					<label class="checkbox-inline" style="margin-top: 5px;">
						<input type="checkbox" data-bind="checked: item().default_checked"> Centang untuk set default
					</label>	
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

