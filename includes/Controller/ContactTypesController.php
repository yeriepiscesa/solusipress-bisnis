<?php
namespace SolusiPress\Controller;
use SolusiPress\Controller\AppController;
use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;

class ContactTypesController extends AppController {

    protected $modelName = 'ContactTypes';
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
	                return $or->like( 'ContactTypes.name', $_sv )
	                    ->like( 'ContactTypes.description', $_sv );
	            } );
	            return $or;
	        } );
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
            0 => 'ContactTypes.id',
            1 => 'ContactTypes.name',
            2 => 'ContactTypes.ordering',
            3 => 'ContactTypes.is_default',
            4 => 'ContactTypes.description',
        );
        $model = ModelLoader::get( $this->modelName );
        $query = $model->find()->select([
            'ContactTypes.id',
            'ContactTypes.name',
            'ContactTypes.ordering',
            'ContactTypes.is_default',
            'ContactTypes.description',
            'ContactTypes.color',
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
	            $name = $row->name;
	            if( $row->color != '' ) {
		            $name = ' <span class="contact-type-color" style="background-color:' . $row->color . '"></span>&nbsp;' . $name;
	            }
	            if( isset( $request['rowformat'] ) && $request['rowformat'] == 'datatable' ) {
		            $_row = [
	                    $row->id,
	                    $name,
	                    $row->ordering,
						($row->is_default == '1') ? '<i class="dashicons dashicons-yes"></i>':'',
						$row->description,
		            ];
	            } else {
		            $_row = [
	                    'id' => $row->id,
	                    'name' => $name,
	                    'level' => $row->ordering,
						'is_default' => $row->is_default,
						'description' => $row->description,
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
		    	'name' => $_entity->name,
		    	'ordering' => $_entity->ordering,
		    	'is_default' => $_entity->is_default,
		    	'color' => $_entity->color,
		    	'description' => $_entity->description
	        ];
        }
        echo json_encode( $data );
    }
    
    public function insert_action() {
        $data = $this->set_data();        
        $status = false;
        $message = null;
        $insert_id = null;        
        $model = ModelLoader::get( $this->modelName );
        $entity = $model->newEntity();
        $entity = $model->patchEntity( $entity, $data );
        if( empty( $entity->getErrors() ) ) {
            $model->save( $entity );
            $status = true;
            $insert_id = $entity->id;
        } else{
            $message = 'Tambah data gagal, mohon ulangi kembali';
        }
        $result = [
            'status' => $status,
            'message' => $message, 
            'insert_id' => $insert_id
        ];        
        echo json_encode( $result );	    
    }
    
    public function update_action( $id ) {	    
        $data = $this->set_data($id);        
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
        $contact = ModelLoader::get( 'Contacts' );
        $row = $contact->find()->where( [ 'Contacts.contact_type_id' => $id ] )->first();        
        if( !$row ) {	        
	        $model = ModelLoader::get( $this->modelName );
	        $entity = $model->get( $id );
	        if( $model->delete( $entity ) ) {
	            $status = true;
	        } else {
	            $message = 'Hapus data gagal, mohon ulangi kembali';
	        }	        
        } else {
	        $message = 'Hapus data gagal, kategori digunakan pada data kontak';
        }        
        $result = [
            'status' => $status,
            'message' => $message
        ];
        echo json_encode( $result );        
    }

}