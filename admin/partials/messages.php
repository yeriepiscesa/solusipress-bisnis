<div class="wrap sp-bisnis-page solusipress-bisnis-container">
    <h1 class="wp-heading-inline">Pesan</h1>
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
	                    <option value="">Kategori Kontak</option>
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
    <table id="messages-table" class="table table-striped table-hover">
        <thead>
            <tr>
                <th style="width:60px;">&nbsp;</th>
                <th style="width:200px;">Pengirim</th>
                <th style="width:120px;">Tipe</th>
                <th style="width:120px;">Tanggal</th>
                <th>Subjek</th>
            </tr>
        </thead>
    </table>    
    
	<div id="solusipress-form-entry" class="jqmodal" style="max-width:650px; margin-top: 20px; display:none;">    
		<div class="frm-form-fields">
			<h2 data-bind="text: item().msg_subject"></h2>
			<table>
				<tr>
					<td>Dari</td>
					<td>&nbsp;&nbsp;: 
						<span data-bind="text: item().contact_name"></span>
						&lt;<a data-bind="text: item().contact_email, attr:{href: 'mailto:'+item().contact_email}"></a>&gt;
					</td>
				</tr>
				<tr>
					<td>WhatsApp</td>
					<td>&nbsp;&nbsp;: 
						<i class="fa fa-whatsapp"></i>  
						<a target="_blank" data-bind="text: item().contact_whatsapp, attr:{ href: 'https://wa.me/'+ item().contact_whatsapp }"></a>
					</td>
				</tr>
			</table>
			<hr>
			<p style="font-size: 15px" data-bind="html: item().msg_text.replace(/\n/g,'<br>')"></p>
		</div>		
	</div>
	
</div>

