<?php
	
namespace SolusiPress\Controller;

class AppController {
	
	public $name = null;
	public function __construct() {
	}
	
	public function authorizeRequest() {
		if( !$this->checkAuth() ) {
			$this->noAuthMessage();
		}
	}
	
    protected function set_data( $id=null ){
		$data = app('request')->body;
        if( !is_null( $id ) ) {
            $data['id'] = $id;
        }
        return $data;
    }
	
	protected function checkAuth() {	
		$auth = false;
		if( is_user_logged_in() && current_user_can( 'manage_solusipress_bisnis' ) ) {
			$auth = true;
		} else {
			if( defined('JWT_AUTH_SECRET_KEY') && class_exists( 'Firebase\JWT\JWT' ) ) {
				$headers = app('request')->headers;
				if( isset( $headers['ect_http_authorization'] ) ) {
			        $token = str_replace('Bearer ', '', $headers['ect_http_authorization'] );
			        if( $token != '' ) {
				        try {
				        	$payload = \Firebase\JWT\JWT::decode( $token, JWT_AUTH_SECRET_KEY, ['HS256'] );
				        	if( $payload ) {
					        	$u = new \WP_User( $payload->data->user->id );
					        	if( isset( $u->allcaps[ 'manage_solusipress_bisnis' ] ) && $u->allcaps['manage_solusipress_bisnis'] === true ) {
					        		$auth = true;
					        	}
				        	}
				        } catch( Exception $e ) {
					        $auth = false;
				        }
			        }
				}
			}
		} 
		return $auth;		
	}
	
	protected function noAuthMessage() {
		header('Content-Type: application/json');
		$data = [
			'status' => false,
			'message' => 'You are not allowed to access the resource!',	
		];
		echo json_encode( $data );
		die();			
	}
	
}	