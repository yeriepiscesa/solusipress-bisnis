<?php
namespace SolusiPress\Controller;
use SolusiPress\Controller\AppController;
use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;

class AccountsController extends AppController {

    protected $modelName = 'Accounts';
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
	                return $or->like( 'Accounts.account_name', $_sv )
	                    ->like( 'Accounts.account_number', $_sv )
	                    ->like( 'Accounts.bank', $_sv );
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
            0 => 'Accounts.id',
            1 => 'Accounts.bank',
            2 => 'Accounts.account_number',
            3 => 'Accounts.account_name',
            4 => 'Accounts.public_account',
            5 => 'Accounts.description',
        );
        $model = ModelLoader::get( $this->modelName );
        $query = $model->find()->select([
            'Accounts.id',
            'Accounts.bank',
            'Accounts.account_number',
            'Accounts.account_name',
            'Accounts.public_account',
            'Accounts.description',
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
	            
	            if( isset( $request['rowformat'] ) && $request['rowformat'] == 'datatable' ) {
		            $_row = [
	                    $row->id,
	                    $row->bank,
	                    $row->account_number,
	                    $row->account_name,
						($row->public_account == '1') ? '<i class="dashicons dashicons-yes"></i>':'',
						$row->description,
		            ];
	            } else {
		            $_row = [
	                    'id' => $row->id,
	                    'bank' => $row->bank,
	                    'account_number' => $row->account_number,
	                    'account_name' => $row->account_name,
						'public_account' => $row->public_account,
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
		
	public function get_action( $id ){
        $m1 = ModelLoader::get( $this->modelName );
        $_entity = $m1->find()->where( [ 'id' => $id ] )->first();
        $data = [];
        if( $_entity ) {
	        $data = [
		    	'id' => $_entity->id,
		    	'bank' => $_entity->bank,
		    	'account_number' => $_entity->account_number,
		    	'account_name' => $_entity->account_name,
		    	'public_account' => $_entity->public_account,
		    	'logo_url' => $_entity->logo_url,
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
            'insert_id' => $insert_id,
            'errors' => $entity->getErrors()
        ];        
        echo json_encode( $result );        
	}		

	public function update_action( $id ) {		
        $data = $this->set_data( $id );        
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
            'message' => $message,
            'errors' => $entity->getErrors()
        ];                
        echo json_encode( $result );        
	}		

	public function delete_action( $id ) {
        $status = false;
        $message = null;
        $cf = ModelLoader::get( 'CashFlows' );
        $row = $cf->find()->where( [ 'CashFlows.account_id' => $id ] )->first();
        if( !$row ) {
	        $model = ModelLoader::get( $this->modelName );
	        $entity = $model->get( $id );
	        if( $model->delete( $entity ) ) {
	            $status = true;
	        } else {
	            $message = 'Hapus data gagal, mohon ulangi kembali';
	        }
        } else {
	        $message = 'Hapus data gagal, kas/bank ada dalam transaksi cash flow';
        }
        $result = [
            'status' => $status,
            'message' => $message
        ];
        echo json_encode( $result );
	}		

}	