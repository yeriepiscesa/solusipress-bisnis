<div class="wrap sp-bisnis-page solusipress-bisnis-container">
    <h1 class="wp-heading-inline">Pengaturan Kontak</h1>
	<form method="post" class="spb-form">
		
		<h2 class="title">Google Recaptcha V2 (Tickbox)</h2>
		<p>Dapatkan site key & secret key <a href="https://www.google.com/recaptcha/intro/v3.html" target="_blank">disini</a>, pastikan memilih v2 tickbox</p>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">Site Key</th>
					<td><input type="text" name="recaptcha_site_key" size="40"></td>
				</tr>
				<tr>
					<th scope="row">Secret Key</th>
					<td><input type="text" name="recaptcha_secret_key" size="40"></td>
				</tr>
			</tbody>
		</table>	
		
		<h2 class="title">Form Kontak</h2>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row">Notifikasi Email</th>
					<td><input type="text" name="notification_email" size="40" placeholder="<?= get_bloginfo( 'admin_email' ) ?>">
						<p class="description">Alamat email untuk notifikasi pesan baru.</p>
					</td>
				</tr>
				<tr>
					<th scope="row">Nomor WhatsApp</th>
					<td><input type="text" name="notification_whatsapp" size="20" placeholder="6281234567890"></td>
				</tr>
				<tr>
					<td class="td-full" colspan="2">
						<label for="activate_recaptcha">
						<input name="activate_recaptcha" id="activate_recaptcha" type="checkbox" value="1">
							Aktifkan Google Recaptcha (pastikan isian site key &amp; secret key benar)</label>
					</td>
				</tr>
			</tbody>
		</table>	
		<?php do_action( 'solusipress_bisnis_contact_setting_fields' ); ?>
		<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Simpan Pengaturan"></p>		
			
	</form>
</div>
