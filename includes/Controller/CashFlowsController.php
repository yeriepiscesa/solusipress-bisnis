<?php
namespace SolusiPress\Controller;
use SolusiPress\Controller\AppController;
use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use DateTime;

class CashFlowsController extends AppController {

    protected $modelName = 'CashFlows';
    protected $list_filtered = false;

    protected $date_filtered = false;
    protected $min_date = null;
    protected $max_date = null;
    
    public function __construct() {
        parent::__construct();
    }

	private function filter_query( $request, $query, $sum=false ) {
		
		$filtered = false;
		if( !empty( $request['search']['value'] ) ) {
	        $_sv = '%'. $request['search']['value'] . '%';
	        $query->where( function( QueryExpression $expr ) use( $_sv ) {    
	            $or = $expr->or_( function( $or ) use( $_sv ) {
	                return $or->like( 'CashFlows.trx_no', $_sv )
	                    ->like( 'CashFlows.from_to_name', $_sv )
	                    ->like( 'Accounts.account_number', $_sv )
	                    ->like( 'CashFlows.note', $_sv )
	                    ->like( 'Accounts.bank', $_sv );
	            } );
	            return $or;
	        } );
	        $filtered = true;
        }
        
        $min_nom = 0;
        $max_nom = 0;        
        if( isset( $request['nominal_min'] ) && $request['nominal_min'] != '' ) {
            $min_nom = intval( $request['nominal_min'] );
        }
        if( isset( $request['nominal_max'] ) && $request['nominal_max'] != '' ) {
            $max_nom = intval( $request['nominal_max'] );
        }
        if( $min_nom > 0 && $max_nom > 0 ) {
            $query->where(function (QueryExpression $exp, Query $q) use( $min_nom, $max_nom ) {
                return $exp->between( 'amount', $min_nom, $max_nom );
            });            
        } elseif( $min_nom > 0 && $max_nom == 0 ) {
            $query->where(function (QueryExpression $exp, Query $q) use ( $min_nom ) {
                return $exp->gte( 'amount', $min_nom );
            });                        
        } elseif( $min_nom == 0 && $max_nom > 0 ) {
            $query->where(function (QueryExpression $exp, Query $q) use( $max_nom ) {
                return $exp->lte( 'amount', $max_nom );
            });                        
        }
        if( $min_nom > 0 || $max_nom > 0 ) { $filtered = true; }	
        
        $min_trxdate = null;
        $max_trxdate = null;
        if( isset( $request['trxdate_min'] ) && $request['trxdate_min'] != '' ) {
            $min_trxdate = $request['trxdate_min'];
        }
        if( isset( $request['trxdate_max'] ) && $request['trxdate_max'] != '' ) {
            $max_trxdate = $request['trxdate_max'];
        }
        if( !is_null( $min_trxdate ) && !is_null( $max_trxdate ) ) {
			
			$filtered = true;
			
			$this->date_filtered = true;
			$this->min_date = $min_trxdate;
			$this->max_date = $max_trxdate;
			
			if( !$sum ) {
	            $query->where(function (QueryExpression $exp, Query $q) use( $min_trxdate, $max_trxdate ) {
	                return $exp->between( 'trx_date', $min_trxdate, $max_trxdate );
	            });
            }            
        }
        
        if( isset( $request['account'] ) && $request['account'] != '' ) {
	        $query->where( [ 'account_id' => $request['account'] ] );
	        $filtered = true;
        }
        
        if( !$sum ) {
	        if( isset( $request['dc'] ) && $request['dc'] != '' ) {
		        $query->where( [ 'dc' => $request['dc'] ] );
		        $filtered = true;
	        }
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
            0 => 'CashFlows.id',
            1 => 'CashFlows.trx_no',
            2 => 'CashFlows.trx_date',
            3 => 'CashFlows.from_to_name',
            4 => 'CashFlows.dc',
            5 => 'Accounts.bank',
            6 => 'CashFlows.amount',
            7 => 'CashFlows.note',
            8 => 'CashFlows.last_update'
        );
        $model = ModelLoader::get( $this->modelName );
        $query = $model->find()->select([
            'CashFlows.id',
            'CashFlows.trx_no',
            'CashFlows.trx_date',
            'CashFlows.dc',
            'CashFlows.contact_id',
            'CashFlows.account_id',
            'CashFlows.from_to_name',
            'CashFlows.amount',
            'CashFlows.note',
            'CashFlows.last_update',
        ]);
        $query->contain([
        	'Accounts'=>[
				'fields' => [ 'Accounts.bank', 'Accounts.account_number' ]
        	]
        ]);
        
        $sumquery = $model->find();
        $sumquery->contain(['Accounts']);

        $query = $this->filter_query( $request, $query );        
        $sumquery = $this->filter_query( $request, $sumquery, true );
		
		if( !$this->date_filtered ) {
			$_min_date = current_time( 'Y-m-01' );
			$_max_date = current_time( 'Y-m-t' );
		} else {
			$_min_date = $this->min_date;
			$_max_date = $this->max_date;
		}
		
		$timezone = wp_timezone();
		$wp_dtm = new DateTime( $_min_date, $timezone );    
		$wp_dtm->modify( '-1 day' );
		$last_period = $wp_dtm->format( 'Y-m-d' );
			
        $sumquery->select([
	        'dr' => $sumquery->func()->sum( "case when CashFlows.dc='d' and CashFlows.trx_date between '" . $_min_date . "' and '" . $_max_date . "' then CashFlows.amount else 0 end" ),
	        'cr' => $sumquery->func()->sum( "case when CashFlows.dc='c' and CashFlows.trx_date between '" . $_min_date . "' and '" . $_max_date . "' then CashFlows.amount else 0 end" ),
	        'dr_last' => $sumquery->func()->sum( "case when CashFlows.dc='d' and CashFlows.trx_date <= '" . $last_period . "' then CashFlows.amount else 0 end" ),
	        'cr_last' => $sumquery->func()->sum( "case when CashFlows.dc='c' and CashFlows.trx_date <= '" . $last_period . "' then CashFlows.amount else 0 end" )
        ]);
        
		$sum_dc = $sumquery->first();
		$sum_dr = 0;
		$sum_cr = 0;
		$sum_dr_last = 0;
		$sum_cr_last = 0;
		
		if( $sum_dc ) {
			$sum_dr = $sum_dc->dr;
			$sum_cr = $sum_dc->cr;
			$sum_dr_last = $sum_dc->dr_last;
			$sum_cr_last = $sum_dc->cr_last;
		}
        
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
	            $trx_date = null;
	            $last_update = null;
	            if( !is_null( $row->trx_date ) ) {
		            $trx_date = $row->trx_date->format( "Y-m-d" );
	            }
	            if( !is_null( $row->last_update ) ) {
		            $last_update = $row->last_update->format( "Y-m-d H:i:s" );
	            }
	            $amount = number_format( $row->amount, 0, ',', '.');
	            if( isset( $request['rowformat'] ) && $request['rowformat'] == 'datatable' ) {
		            $_row = [
	                    $row->id,
	                    $row->trx_no,
	                    $trx_date, 
	                    $row->from_to_name,
	                    $row->dc == 'd' ? 'D':'C',
	                    $row->account->get( 'account_view' ),
	                    $amount,
	                    $row->note,
	                    $last_update            
		            ];
	            } else {
		            $_row = [
	                    'id' => $row->id,
	                    'trx_no' => $row->trx_no,
	                    'trx_date' => $trx_date, 
	                    'contact_id' => $row->contact_id,
	                    'from_to_name' => $row->from_to_name,
	                    'account_id' => $row->account_id,
	                    'account' => $row->account->get( 'account_view' ),
	                    'dc' => $row->dc,
	                    'amount' => $row->amount,
	                    'note' => $row->note,
	                    'last_update'=>$last_update            
		            ];
	            }
                array_push( $data, $_row );
            }
            
            $json_data = array(
                "draw" => intval( $draw ),
                "recordsTotal" => intval( $totalRows ),
                "recordsFiltered" => intval( $totalData ),
                "data" => $data,
                "summary" => [
	                'last' => [
		            	'date' => $last_period,
		            	'd' => solusipress_format_money( $sum_dr_last ),
		            	'c' => solusipress_format_money( $sum_cr_last ),
		            	'b' => solusipress_format_money( $sum_dr_last - $sum_cr_last )    
	                ],
	                'current' => [
		                'start' => $_min_date,
		                'end' => $_max_date, 
	                	'd' => solusipress_format_money( $sum_dr ), 
	                	'c' => solusipress_format_money( $sum_cr ), 
	                	'b' => solusipress_format_money( ($sum_dr - $sum_cr) + ($sum_dr_last - $sum_cr_last) ) 
	                ]
                ]
            );
            
        } else {
	        
            $json_data = array(
                "data" => array(),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "summary" => array()
            );
            
        }
        echo json_encode($json_data);              
		
	}
	
	public function get_action( $id ) {
		
        $m1 = ModelLoader::get( $this->modelName );
        $_entity = $m1->find()
        	->contain([
	        	'Contacts' => [
		        	'fields' => [ 'Contacts.first_name', 'Contacts.last_name', 'Contacts.organization', 'Contacts.email' ]
	        	]
        	])
        	->where( [ 'CashFlows.id' => $id ] )->first();
        $data = [];
        
        if( $_entity ) {
	        $contact_lookup = null;
	        if( $_entity->contact ) {
		        $contact_lookup = trim( $_entity->contact->first_name . ' ' . $_entity->contact->last_name );
		        if( $_entity->contact->email != '' ) {
		        	$contact_lookup .= ' &lt;' . $_entity->contact->email . '&gt;';
		        }
		        if( $_entity->contact->organization ) {
			        $contact_lookup .= ' | ' . $_entity->contact->organization;
		        }
	        }
	        $data = [
		    	'id' => $_entity->id,
		    	'account_id' => $_entity->account_id,
		    	'trx_no' => $_entity->trx_no,
		    	'trx_date' => $_entity->trx_date->format( 'Y-m-d' ),
		    	'contact_id' => $_entity->contact_id,
		    	'contact_lookup' => $contact_lookup,
		    	'from_to_name' => $_entity->from_to_name,
		    	'organization' => $_entity->organization,
		    	'dc' => $_entity->dc,
		    	'amount' => $_entity->amount,
		    	'post_id' => $_entity->post_id,
		    	'object_model' => $_entity->object_model,
		    	'object_id' => $_entity->object_id,
		    	'note' => $_entity->note   
	        ];
        }
        
        echo json_encode( $data );
		
	}
	
	private function _set_data( $id=null ) {
        
        $data = $this->set_data( $id );        
        $data['last_update'] = current_time( 'mysql' );
        if( isset( $data['contact_id'] ) ) {
			$desc = \SolusiPress\Model\EntityDescriber::contact_info( $data['contact_id'] );
			if( !empty( $desc ) ) {
				$data['from_to_name'] = $desc['contact_name'];
				$data['organization'] = $desc['organization'];
			}
        }
		return $data;
		
	}
	
	public function insert_action() {

        $data = $this->_set_data();
        $status = false;
        $message = null;
        $insert_id = null;
        $model = ModelLoader::get( $this->modelName );
        $entity = $model->newEntity();
        $entity = $model->patchEntity( $entity, $data );
        $errors = $entity->getErrors();
        if( empty( $errors ) ) {
	        $continue = true;
	        if( $data['dc'] == 'c' ) {
	        	$available = \SolusiPress\Model\Transact::creditBalance( $data['account_id'], $data['amount'] );
	        	if( $available < 0 ) {
		        	$continue = false;
		        	$message = 'Saldo tidak cukup, mohon cek jumlah yang akan dikurangi pada kas/bank';
	        	}
	        }
	        if( $continue ) {
	            $model->save( $entity );
	            $status = true;
	            $insert_id = $entity->id;
	        }
        } else{
            $message = 'Tambah data gagal, mohon ulangi kembali';
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
		
        $data = $this->_set_data( $id );
        $status = false;
        $message = null;
        
        $model = ModelLoader::get( $this->modelName );
        $entity = $model->get( $data['id'] );
        
        if( is_null( $entity->post_id ) && is_null( $entity->object_id ) ) {
        
	        $entity = $model->patchEntity( $entity, $data );
	        $errors = $entity->getErrors();
	        if( empty( $errors ) ) {
		        $continue = true;
		        if( $data['dc'] == 'c' ) {
			        $amount_before = $entity->amount;
			        $available = \SolusiPress\Model\Transact::creditBalance( $data['account_id'], $data['amount'] );
			        $balanceAfter = ( $available - $data['amount'] + $amount_before ) - $data['amount'];
			        if( $balanceAfter < 0 ) {
				        $continue = false;
						$message = 'Saldo tidak cukup, mohon cek jumlah yang akan dikurangi pada kas/bank';
			        }
		        }
		        if( $continue ) {
		            $model->save( $entity );
		            $status = true;
	            }
	        } else {
	            $message = 'Ubah data gagal, mohon ulangi kembali';
	        }        
	        
        } else {
	        $message = 'Tidak dapat mengubah data, transaksi diluar modul penerimaan/pengeluaran';
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
			
        if( is_null( $entity->post_id ) && is_null( $entity->object_id ) ) {
	        if( $model->delete( $entity ) ) {
	            $status = true;
	        } else {
	            $message = 'Hapus data gagal, mohon ulangi kembali';
	        }
	    } else {
	        $message = 'Tidak dapat mengubah data, transaksi diluar modul penerimaan/pengeluaran';
	    }
        
        $result = [
            'status' => $status,
            'message' => $message
        ];
        echo json_encode( $result );
	}
}
