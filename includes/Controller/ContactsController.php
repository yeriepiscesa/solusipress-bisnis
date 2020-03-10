<?php
namespace SolusiPress\Controller;
use SolusiPress\Controller\AppController;
use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;

class ContactsController extends AppController {

    protected $modelName = 'Contacts';
    protected $list_filtered = false;

    public function __construct() {
        parent::__construct();
    }
	
	private function filter_query( $request, $query, $sum=false ) {
		
		$filtered = false;
		if( !empty( $request['search']['value'] ) ) {
	        $_sv = '%'. $request['search']['value'] . '%';
	        $query->where( function( QueryExpression $expr ) use( $_sv ) {    
	            $or = $expr->or_( function( $or ) use( $_sv ) {
	                return $or->like( 'Contacts.first_name', $_sv )
	                    ->like( 'Contacts.last_name', $_sv )
	                    ->like( 'Contacts.organization', $_sv )
	                    ->like( 'Contacts.whatsapp', $_sv )
	                    ->like( 'Contacts.email', $_sv )
	                    ->like( 'Contacts.note', $_sv );
	            } );
	            return $or;
	        } );
	        $filtered = true;
        }        
        
        if( isset( $request['contact_type_id'] ) && $request['contact_type_id'] != '' ) {
	        $query->where( [ 'contact_type_id' => $request['contact_type_id'] ] );
	        $filtered = true;
        }

        $this->list_filtered = $filtered;
        
        return $query;
	}
	
	public function data_lookup() {
		$request = $_GET;
    	header("Content-Type: application/json");
        $model = ModelLoader::get( $this->modelName );
        $query = $model->find()->select([
            'Contacts.id',
            'Contacts.first_name',
            'Contacts.last_name',
            'Contacts.organization',
            'Contacts.email',
        ]);
        $query->contain([
	        'ContactTypes' => [
		        'fields' => [ 'ContactTypes.name' ]
	        ]
        ]);
        
        if( isset( $request['term'] ) && $request['term'] != '' ) {
		    $_sv = '%'. $request['term'] . '%';
	        $query->where( function( QueryExpression $expr ) use( $_sv ) {    
	            $or = $expr->or_( function( $or ) use( $_sv ) {
	                return $or->like( 'Contacts.first_name', $_sv )
	                    ->like( 'Contacts.last_name', $_sv )
	                    ->like( 'Contacts.organization', $_sv )
	                    ->like( 'Contacts.email', $_sv );
	            } );
	            return $or;
	        } );
        }
        
        $query->limit( 10 );
        $rows = $query->all();
        $data = [];
        foreach( $rows as $row ) {
            $_row = [
                'id' => $row->id,
                'contact_name' => trim( $row->first_name . ' ' . $row->last_name ),
				'contact_type_name' => $row->contact_type->name,
				'organization' => $row->organization,
				'email' => $row->email,
            ];
            array_push( $data, $_row );
		}
		echo json_encode( $data );
	}
	
    public function index_action() {
	    
        $request = $_GET;
	    if( isset( $request['rowformat'] ) && $request['rowformat'] == 'datatable' ) {
    		header("Content-Type: application/json");
        }
        $columns = array(
            0 => 'Contacts.id',
            1 => 'Contacts.first_name',
            2 => 'Contacts.organization',
            3 => 'Contacts.email',
            4 => 'Contacts.whatsapp',
            5 => 'Contacts.note',
            6 => 'Contacts.last_update',
        );
        $model = ModelLoader::get( $this->modelName );
        $query = $model->find()->select([
            'Contacts.id',
            'Contacts.first_name',
            'Contacts.last_name',
            'Contacts.contact_type_id',
            'Contacts.organization',
            'Contacts.email',
            'Contacts.whatsapp',
            'Contacts.note',
            'Contacts.last_update',
            'Contacts.instagram',
            'Contacts.twitter',
            'Contacts.facebook',
            'Contacts.linkedin',
        ]);
        $query->contain([
	        'ContactTypes' => [
		        'fields' => [ 'ContactTypes.name', 'ContactTypes.ordering', 'ContactTypes.color' ]
	        ]
        ]);
        $query = $this->filter_query( $request, $query );   
             
		$draw = 1;
		$page = 1;
		$length = 10;
		if( isset( $request['order'] ) ) {
        	$query->order( [ $columns[ $request['order'][0]['column'] ] => $request['order'][0]['dir'] ] );
        }
        if( isset( $request['start'] ) && isset( $request['length'] ) ) {
        	$page = $request['start'] == 0 ? 1 : ($request['start']/$request['length'])+1;
        }
        if( isset( $request['length'] ) ) {
	        $length = $request['length'];
        }
        if( isset( $request['draw'] ) ) {
	        $draw = $request['draw'];
        }
        $query->limit( $length );
        $query->page( $page );

        
        $totalRows = $model->find()->count();       
        $rows = $query->all();
        
        if( !empty( $rows ) ) {
            
            $data = [];
            if( !$this->list_filtered ) {
                $totalData = $totalRows;
            } else {
                $totalData = $query->count();
            }
            
            foreach( $rows as $row ) {
	            $last_update = null;
	            if( !is_null( $row->last_update ) ) {
		            $last_update = $row->last_update->format( "Y-m-d H:i:s" );
	            }
	            
		        $mail = $row->email;
		        $wa = $row->whatsapp;
		        if( $mail != '' ) {
			        $mail = '<a href="mailto:'. $mail .'">' . $mail . '</a>';
		        }
		        if( $wa != '' ) {
					$wa = '<a href="https://wa.me/'. $wa .'" target="_blank">' . $wa . '</a>';		        
		        }
		        
	            $type_color = '';
	            if( $row->contact_type->color != '' ) {
		            $type_color = '<span class="contact-type-color" style="background-color:' . $row->contact_type->color . '" 
		            				alt="' . $row->contact_type->name . '" title="' . $row->contact_type->name . '"></span>&nbsp;&nbsp;';
	            }
	            if( isset( $request['rowformat'] ) && $request['rowformat'] == 'datatable' ) {
		            $_row = [
	                    $row->id,
	                    $type_color . trim($row->first_name . ' ' . $row->last_name),
						$row->organization,
						$mail,
						$wa,
						$row->note,
		            ];
	            } else {
		            $_row = [
	                    'id' => $row->id,
	                    'first_name' => $row->first_name,
	                    'last_name' => $row->last_name,
						'contact_type_id' => $row->contact_type_id,
						'contact_type_name' => $row->contact_type->name,
						'contact_type_color' => $row->contact_type->color,
						'organization' => $row->organization,
						'email' => $row->email,
						'whatsapp' => $row->whatsapp,
						'note' => $row->note,
						'instagram' => $row->instagram,
						'twitter' => $row->twitter,
						'facebook' => $row->facebook,
						'linkedin' => $row->linkedin,
						'last_update' => $row->last_update,
		            ];
	            }
                array_push( $data, $_row );
            }
            
            $json_data = array(
                "draw" => intval( $draw ),
                "recordsTotal" => intval( $totalRows ),
                "recordsFiltered" => intval( $totalData ),
                "data" => $data
            );
            
        } else {
	        
            $json_data = array(
                "data" => array(),
                "recordsTotal" => 0,
                "recordsFiltered" => 0
            );
            
        }
        echo json_encode($json_data);     
        	    
    }
    
    public function get_action( $id ) {	    
        $m1 = ModelLoader::get( $this->modelName );
        $_entity = $m1->find()->where( [ 'id' => $id ] )->first();
        $data = [];
        if( $_entity ) {
	        $data = [
		    	'id' => $_entity->id,
		    	'first_name' => $_entity->first_name,
		    	'last_name' => $_entity->last_name,
		    	'contact_type_id' => $_entity->contact_type_id,
		    	'email' => $_entity->email,
		    	'phone' => $_entity->phone,
		    	'organization' => $_entity->organization,
		    	'note' => $_entity->note,
		    	'whatsapp' => $_entity->whatsapp,
		    	'instagram' => $_entity->instagram,
		    	'twitter' => $_entity->twitter,
		    	'facebook' => $_entity->facebook,
		    	'linkedin' => $_entity->linkedin,
	        ];
        }
        echo json_encode( $data );        
    }
    
    public function insert_action() {	    
        $data = $this->set_data();        
        $data['last_update'] = current_time( 'mysql' );
        $status = false;
        $message = null;
        $insert_id = null;        
        $model = ModelLoader::get( $this->modelName );
        $entity = $model->newEntity();
        $entity = $model->patchEntity( $entity, $data );
        $errors = $entity->getErrors();
        if( empty( $errors ) ) {
            $model->save( $entity );
            $status = true;
            $insert_id = $entity->id;
        } else {
	        $str_errors = '';
	        foreach( $errors as $fld => $err ) {
		        if( $fld == 'email' && isset( $err['unique'] ) ) {
		        	$str_errors .= ' ' . $err['unique'];
		        }
	        }
	        if( $str_errors == '' ) {
		        $str_errors = 'periksa kembali isian Anda';
	        }
            $message = 'Tambah data gagal,' . $str_errors;
        }
        $result = [
            'status' => $status,
            'message' => $message, 
            'insert_id' => $insert_id
        ];        
        echo json_encode( $result );
    }
    
    public function update_action( $id ) {	    
        $data = $this->set_data( $id );        
        $data['last_update'] = current_time( 'mysql' );
        $status = false;
        $message = null;        
        $model = ModelLoader::get( $this->modelName );
        $entity = $model->get( $data['id'] );
        $entity = $model->patchEntity( $entity, $data );
        if( empty( $entity->getErrors() ) ) {
            $model->save( $entity );
            $status = true;
        } else {
            $message = 'Ubah data gagal, mohon ulangi kembali';
        }        
        $result = [
            'status' => $status,
            'message' => $message
        ];        
        echo json_encode( $result );
    }
    
    public function delete_action( $id ) {	    
        $status = false;
        $message = null;        
        $model = ModelLoader::get( $this->modelName );
        $entity = $model->get( $id );
        if( $model->delete( $entity ) ) {
            $status = true;
        } else {
            $message = 'Hapus data gagal, mohon ulangi kembali';
        }        
        $result = [
            'status' => $status,
            'message' => $message
        ];
        echo json_encode( $result );
    }
    
}