<?php namespace SolusiPress\Model;

use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;
	
class EntityDescriber {
	
	public static function contact_info( $contact_id ) {
		$data = [];
		$m = ModelLoader::get( 'Contacts' );
		$contact = $m->find()->where( [ 'Contacts.id' => $contact_id ] )->first();
		if( $contact ) {
			$data['contact_name'] = trim( $contact->first_name . ' ' . $contact->last_name );
			$data['contact_email'] = $contact->email;
			$data['organization'] = $contact->organization;
		}	       
		return $data; 
	}
	
}