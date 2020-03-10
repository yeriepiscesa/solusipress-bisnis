<div class="form-contact-container">
	<form method="post" class="spb-form" id="spb-contact-form">
		<div class="grid-50">
			<label for="frm-first-name">Nama Depan*</label>
			<input id="frm-first-name" type="text" class="form-fields" name="first_name" required="required">
		</div>
		<div class="grid-50">
			<label for="frm-last-name">Nama Belakang</label>
			<input id="frm-last-name" type="text" class="form-fields" name="last_name">
		</div>

		<div class="grid-50">
			<label for="frm-email">Email*</label>
			<input id="frm-email" type="email" class="form-fields" name="email" required="required">
		</div>
		<div class="grid-50">
			<label for="frm-whatsapp">Nomor WhatsApp*</label>
			<input id="frm-whatsapp" type="number" class="form-fields" name="whatsapp" placeholder="misal. 6281234567890"  required="required">
		</div>
		
		<?php
		if( $atts['text_message'] == 'on' ):
			$sticky_subject = '';
			if( trim( $atts['subject'] ) != '' ) {
				$sticky_subject = ' style="display:none;"';
			}	
			?>
			<div class="grid-100"<?php echo $sticky_subject ?>>
				<label form="frm-subject">Subjek*</label>
				<input id="frm-subject" type="text" class="form-fields" name="msg_subject" required="required">
			</div>
			<div class="grid-100">
				<label form="frm-message">Pesan</label>
				<textarea class="form-fields" name="msg_text" id="frm-message" rows="4"></textarea>
			</div>
		<?php endif; ?>
		
		<?php if( get_option( 'spb_activate_recaptcha', '0' ) == '1' && get_option( 'spb_recaptcha_site_key', '' ) != '' ): ?>
		<div class="grid-100">
		    <div class="g-recaptcha"
		          data-sitekey="<?php echo get_option( 'spb_recaptcha_site_key', '' ) ?>">
		    </div>
		</div>
		<?php endif; ?>		
		
		<div class="clearfix"></div>
		<div class="grid-100">
			<button class="form-buttons button" type="submit" id="spb_contact_button">Kirim</button>
		</div>
	</form>
</div>