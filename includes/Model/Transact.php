<?php
namespace SolusiPress\Model;

use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;

class Transact {
	
	public static function setCashFlow( $post_id=null, $input=[] ) {
		
		$defaults = [
			'account_id' => null,
			'trx_date' => current_time( 'Y-m-d' ),
			'from_to_name' => null,
			'organization' => null,
			'dc' => 'd',
			'amount' => 0,
			'post_id' => null,
			'note' => null		
		];
		$data = array_merge( $defaults, $input );
				
		if( is_null( $data['post_id'] ) ) {
			return false;
		}
		
		$model = ModelLoader::get( 'CashFlows' );
		$find = $model->find()->where( ['post_id'=> $data['post_id']] )->first();
		
		if( $find ) {
			// update
			$entity = $model->get( $find->id );
		} else {
			// insert
			$data[ 'trx_no' ] = current_time( 'timestamp' ) . '.' . uniqid();
			$entity = $model->newEntity();
		}
		
		$data[ 'last_update' ] = current_time( 'mysql' );
        $entity = $model->patchEntity( $entity, $data );
        if( empty( $entity->getErrors() ) ) {
            if( $model->save( $entity ) ) {
	            return true;
            }
        }            
        return false;
		
	}
	
	public static function removeCashFlow( $post_id ) {
		$model = ModelLoader::get( 'CashFlows' );
		$find = $model->find()->where( ['post_id'=> $post_id] )->first();
		if( $find ) {
			$entity = $model->get( $find->id );
			$model->delete( $entity );	
		}
	}
	
	
	public static function contactMessageInput( $data=[] ) {

		if( !empty($data) ) {
			$contact_only = false;
			// check contact
			$mContact = ModelLoader::get( 'Contacts' );
			$qContact = $mContact->find()->where( [ 'email' => trim($data['email']) ] );
			$fContact = $qContact->first();
			
			$mtype = ModelLoader::get( 'ContactTypes' );
			$ctype = $mtype->find()->where( [ 'is_default'=>'1' ] )->first();
			$ctype_id = null;
			if( $ctype ) {
				$ctype_id = $ctype->id;
			}
			
			$dContact = [
				'first_name' => $data['first_name'],
				'last_name' => $data['last_name'],
				'email' => trim($data['email']),
				'whatsapp' => $data['whatsapp'],
				'contact_type_id' => $ctype_id,
				'last_update' => current_time( 'mysql' ),
			];
			
			if( !$fContact ) {
				// create new
				$eContact = $mContact->newEntity();
			} else {
				// update contact
				$eContact = $mContact->get( $fContact->id );
				$dContact['id'] = $fContact->id;
			}
			
			$eContact = $mContact->patchEntity( $eContact, $dContact );
	        $errors = $eContact->getErrors();
	        $contact_id = null;
	        if( empty( $errors ) ) {
	            $mContact->save( $eContact );
	            $contact_id = $eContact->id;
	        }
			
			if( !is_null( $contact_id ) ) {
				
				if( !isset( $data['text'] ) ) {
					
					$contact_only = true;
					$mail_subject = "Kontak baru dari pengunjung web";
					
				} else {
					
					$input = [
						'contact_id' => $contact_id,
						'msg_subject' => $data['subject'],
						'msg_date' => current_time( 'mysql' ),
						'msg_text' => trim( $data['text'] ),
					];
					
					$model = ModelLoader::get( 'ContactMessages' );
					$entity = $model->newEntity();
					$entity = $model->patchEntity( $entity, $input );
					$model->save( $entity );
					$mail_subject = "Pesan baru dari pengunjung web";
					
				}
				
				// send mail
				$admin_mail = get_option( 'spb_contact_email_notif', '' );
				if( $admin_mail == '' ) {
					$admin_mail = get_bloginfo( 'admin_email' );
				}
				$headers = array('Content-Type: text/html; charset=UTF-8');
				$full_name = trim( $data['first_name'] . ' ' . $data['last_name'] );
				ob_start(); ?>
				<html>
					<head>
						<title><?php $mail_subject ?></title>
						<style type="text/css">
						@import url(https://fonts.googleapis.com/css?family=Montserrat:400,600); 
						body { font-family: Montserrat; font-weight: 400; font-family: 14px; }	
						.label { font-weight:600; padding-right: 20px; }
						</style>
					</head>
					<body>
						
						<?php if( $contact_only ) : ?>
						<p>Kontak baru dari pengunjung web dengan detail berikut: </p>
						<?php else: ?>
						<p>Pesan baru telah diterima dari pengunjung web, silahkan review untuk menindaklanjuti</p>
						<?php endif; ?>
						
						<table>
							<tr>
								<td class="label">Nama Lengkap</td>
								<td><?php echo $full_name; ?></td>
							</tr>
							<tr>
								<td class="label">Email</td>
								<td><?php echo $data['email'] ?>
							</tr>
							<tr>
								<td class="label">No WhatsApp</td>
								<td><?php echo $data['whatsapp'] ?>
							</tr>
							
							<?php if( !$contact_only ) : ?>
							<tr>
								<td class="label">Judul/Subjek</td>
								<td><?php echo $data['subject'] ?></td>
							</tr>
							<?php endif; ?>
							
						</table>
						
						<?php if( !$contact_only ) : ?>
						<p><?php echo str_replace( "\n", "<br>", $data['text'] )  ?></p>
						<?php endif; ?>
						
					</body>
				</html>
				<?php
				$body = ob_get_contents();
				ob_end_clean();
				wp_mail( $admin_mail, $mail_subject, $body, $headers );
				
			}
			
		}
	}
	
}