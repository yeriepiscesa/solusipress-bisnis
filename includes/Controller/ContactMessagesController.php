<?php
namespace SolusiPress\Controller;
use SolusiPress\Controller\AppController;
use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;

class ContactMessagesController extends AppController {

    protected $modelName = 'ContactMessages';
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
	                return $or->like( 'ContactMessages.msg_subject', $_sv )
	                    ->like( 'ContactMessages.msg_text', $_sv )
	                    ->like( 'Contacts.first_name', $_sv )
	                    ->like( 'Contacts.last_name', $_sv );
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

    public function index_action() {
        $request = $_GET;
	    if( isset( $request['rowformat'] ) && $request['rowformat'] == 'datatable' ) {
    		header("Content-Type: application/json");
        }
        $columns = array(
            0 => 'ContactMessages.id',
            1 => 'Contacts.first_name',
            2 => 'ContactTypes.name',
            3 => 'ContactMessages.msg_date',
            4 => 'ContactMessages.msg_subject'
        );
        $model = ModelLoader::get( $this->modelName );
        $query = $model->find()->select([
            'ContactMessages.id',
            'ContactMessages.contact_id',
            'ContactMessages.msg_date',
            'ContactMessages.msg_subject',
            'ContactMessages.msg_text',
            'ContactMessages.dtm_read',
            'ContactMessages.dtm_followup',
            'ContactMessages.followup_by',
        ]);
        $query->contain([
	        'Contacts' => [
		        'ContactTypes' => [
			    	'fields' => [ 'ContactTypes.name', 'ContactTypes.color' ]    
		        ],
		        'fields' => [ 'Contacts.first_name', 'Contacts.last_name', 'Contacts.contact_type_id' ]
	        ],
	        'Users' => [
		        'fields' => [ 'Users.display_name', 'Users.user_login' ]
	        ]
        ]);
        $query->where( ['ContactMessages.parent_id IS' => null, 'Contacts.id IS NOT' => null] );
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

        
        $totalRows = $model->find()
        				->contain( ['Contacts'] )
        				->where( [ 'Contacts.id IS NOT' => null ] )
        				->count();       
        $rows = $query->all();
        
        if( !empty( $rows ) ) {
            
            $data = [];
            if( !$this->list_filtered ) {
                $totalData = $totalRows;
            } else {
                $totalData = $query->count();
            }
            
            foreach( $rows as $row ) {
	            
	            if( isset( $request['rowformat'] ) && $request['rowformat'] == 'datatable' ) {
		            $from = trim( $row->contact->first_name . ' ' . $row->contact->last_name );
		            $type = '';
		            if( $row->contact->contact_type->color != '' ) {
			            $type = '<span class="contact-type-color" style="background-color:' . 
			            		  $row->contact->contact_type->color . '"></span>&nbsp;';
	            	}
		            $msg_date = $row->msg_date->format( 'd/m/Y H:i:s' );
		            $msg_subject = $row->msg_subject;
		            if( is_null( $row->dtm_read ) || $row->dtm_read == '' ) {
			            $from = '<strong>' . $from . '</strong>';
			            $msg_subject = '<strong>' . $msg_subject . '</strong>';
		            }
					$_row = [
						$row->id,
						$from,
						$type . '&nbsp;' . $row->contact->contact_type->name,
						$msg_date,
						$msg_subject,	
					];	            
		        } else {
			        $_row = [
						'id' => $row->id,
						'contact_id' => $row->contact_id,
						'contact_first_name' => $row->contact->first_name,
						'contact_last_name' => $row->contact->last_name,
						'contact_type_id' => $row->contact->contact_type_id,
						'contact_type' => $row->contact->contact_type->name,
						'contact_type_color' => $row->contact->contact_type->color,
						'msg_date' => $row->msg_date,
						'msg_subject' => $row->msg_subject,	
						'msg_text' => $row->msg_text,
						'dtm_read' => $row->dtm_read,
						'dtm_followup' => $row->dtm_followup,
						'followup_by' => $row->followup_by,
						'followup_user' => $row->user->user_login,
						'followup_name' => $row->user->display_name,
				        
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
        $query = $m1->find();
        $query->contain([
	        'Contacts' => [
		        'fields' => [ 
		        	'Contacts.first_name', 
		        	'Contacts.last_name', 
		        	'Contacts.email', 
		        	'Contacts.whatsapp', 
		        	'Contacts.wp_user_id' 
		        ]
	        ],
        ]);
        $_entity = $query->where( [ 'ContactMessages.id' => $id ] )->first();
        
        $data = [];
        if( $_entity ) {
	        $contact_name = trim( $_entity->contact->first_name . ' ' . $_entity->contact->last_name );
	        $wa = $_entity->contact->whatsapp;
	        $data = [
		    	'id' => $_entity->id,
		    	'contact_id' => $_entity->contact_id,
		    	'contact_name' => $contact_name,
		    	'contact_email' => $_entity->contact->email,
		    	'contact_whatsapp' => $wa,
		    	'msg_date' => $_entity->msg_date->format( 'Y-m-d H:i:s' ),
		    	'msg_subject' => $_entity->msg_subject,
		    	'msg_text' => $_entity->msg_text,
		    	'dtm_read' => $_entity->dtm_read,
		    	'dtm_followup' => $_entity->dtm_followup,
		    	'followup_by' => $_entity->followup_by,
		    	'user_id' => $_entity->contact->wp_user_id,
	        ];
        }
        echo json_encode( $data );        
	}	
	
	public function insert_action($data=null) {
		if( is_null( $data ) ) {
        	$data = $this->set_data();
        }      
        $data['msg_date'] = current_time( 'mysql' );  
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
            $message = 'Tambah data gagal, periksa kembali isian Anda!';
        }
        $result = [
            'status' => $status,
            'message' => $message, 
            'insert_id' => $insert_id,
            'errors' => $errors
        ];        
        echo json_encode( $result );
	}
	
	public function update_action( $id ) {
		if( is_array( $id ) ) {
			$data = $id;
		} else {
	        $data = $this->set_data( $id );
		}
        $status = false;
        $message = null;        
        $model = ModelLoader::get( $this->modelName );
        $entity = $model->get( $data['id'] );
        $entity = $model->patchEntity( $entity, $data );
        $errors = $entity->getErrors();
        if( empty( $errors ) ) {
            $model->save( $entity );
            $status = true;
        } else {
            $message = 'Ubah data gagal, periksa kembali isian Anda!';
        }        
        $result = [
            'status' => $status,
            'message' => $message,
            'errors' => $errors
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
	
    public function process_action( $id, $process ) {
	    
	    switch( $process ) {
		    case 'set-read':
		    	$this->set_read( $id );
		    	break;
			case 'set-followup':
				$data = app('request')->body;
				$this->set_followup( $id, $data['user_id'] );
				break;
	    }
	    
    }
    
    private function set_read( $id ) {
		$this->update_action( [
			'id' => $id,
			'dtm_read' => current_time( 'mysql' )
		] );    
    }
    
    private function set_followup( $id, $user_id ) {
		$this->update_action( [
			'id' => $id,
			'dtm_followup' => current_time( 'mysql' ),
			'followup_by' => $user_id
		] );    
    }

}
