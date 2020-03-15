<?php
namespace SolusiPress\Controller;
use SolusiPress\Controller\AppController;
use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use DateTime;
use Cake\Datasource\Exception\RecordNotFoundException;

class DebtsController extends AppController {

    protected $modelName = 'Debts';
    protected $list_filtered = false;

    protected $date_filtered = false;
    protected $due_date_filtered = false;
    protected $min_date = null;
    protected $max_date = null;
    protected $min_due_date = null;
    protected $max_due_date = null;
    
    public function __construct() {
        parent::__construct();
    }
    
	private function filter_query( $request, $query, $sum=false ) {
		
		$filtered = false;
		if( !empty( $request['search']['value'] ) ) {
	        $_sv = '%'. $request['search']['value'] . '%';
	        $query->where( function( QueryExpression $expr ) use( $_sv ) {    
	            $or = $expr->or_( function( $or ) use( $_sv ) {
	                return $or->like( 'Debts.ref_number', $_sv )
	                    ->like( 'Debts.note', $_sv )
	                    ->like( 'Contacts.first_name', $_sv )
	                    ->like( 'Contacts.last_name', $_sv )
	                    ->like( 'Contacts.organization', $_sv );
	            } );
	            return $or;
	        } );
	        $filtered = true;
        }
        
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
        
        $min_duedate = null;
        $max_duedate = null;
        if( isset( $request['duedate_min'] ) && $request['duedate_min'] != '' ) {
            $min_duedate = $request['duedate_min'];
        }
        if( isset( $request['duedate_max'] ) && $request['duedate_max'] != '' ) {
            $max_duedate = $request['duedate_max'];
        }
        if( !is_null( $min_duedate ) && !is_null( $max_duedate ) ) {
			$filtered = true;
			$this->due_date_filtered = true;
			$this->min_due_date = $min_duedate;
			$this->max_due_date = $max_duedate;
			
			if( !$sum ) {
	            $query->where(function (QueryExpression $exp, Query $q) use( $min_duedate, $max_duedate ) {
	                return $exp->between( 'due_date', $min_duedate, $max_duedate );
	            });
            }            
        }
        
        if( isset( $request['account'] ) && $request['account'] != '' ) {
	        $query->where( [ 'account_id' => $request['account'] ] );
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
            0 => 'Debts.id',
            1 => 'Debts.trx_date',
            2 => 'Contacts.first_name',
            3 => 'Debts.amount',
            4 => 'Accounts.bank',
            5 => 'Debts.due_date',
            6 => 'Debts.total_paid'
        );

        $model = ModelLoader::get( $this->modelName );
        $query = $model->find();
        $query->contain([
	        'Contacts' => [
		    	'fields' => [ 'first_name', 'last_name', 'organization' ]    
	        ],
        	'Accounts'=>[
				'fields' => [ 'bank', 'account_number', 'account_name' ]
        	]
        ]);
        $query = $this->filter_query( $request, $query );  


        $sumquery = $model->find();
        $sumquery->contain( [ 'Contacts', 'Accounts' ] );
        $sumquery = $this->filter_query( $request, $sumquery );

        $query->where( [ 'Debts.dc' => $request['dc'] ] );   
        $sumquery->where( [ 'Debts.dc' => $request['dc'] ] );   
           
        if( $request['completed'] == '1' ) {
	        $query->where( [ 'Debts.fullpaid_date IS NOT' => null ] );
	        $sumquery->where( [ 'Debts.fullpaid_date IS NOT' => null ] );
        } elseif( $request['completed'] == '0' ) {
	        $query->where( [ 'Debts.fullpaid_date IS' => null ] );
	        $sumquery->where( [ 'Debts.fullpaid_date IS' => null ] );
        }
        
        $sumquery->select([
	        'total_amount' => $sumquery->func()->sum( "Debts.amount" ),
	        'total_paid' => $sumquery->func()->sum( "Debts.total_paid" )
        ]);
        
		$sum = $sumquery->first();
		
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
        
        $totalRows = $model->find()->where([ 'dc' => $request['dc'] ])->count();       
        $rows = $query->all();
        
        if( !empty( $rows ) ) {
            
            $data = [];
            if( !$this->list_filtered ) {
                $totalData = $totalRows;
            } else {
                $totalData = $query->count();
            }
            
            foreach( $rows as $row ) {
		        $fpd = $row->fullpaid_date;
		        $lup = $row->last_updated;
		        if( !is_null( $fpd ) ) {
			        $fpd = $fpd->format( "Y-m-d" );
		        }
		        if( !is_null( $lup ) ) {
			        $lup = $lup->format( "Y-m-d H:i:s" );
		        }
	            if( isset( $request['rowformat'] ) && $request['rowformat'] == 'datatable' ) {
		            $amount = solusipress_format_money( $row->amount );
		            $cname = trim( $row->contact->first_name . ' ' . $row->contact->last_name );
		            $acct = trim($row->account->bank . ' ' . $row->account->account_number);
		            $total_paid = solusipress_format_money( $row->total_paid );
		            $_row = [
	                    $row->id,
	                    $row->trx_date->format("Y-m-d"),
	                    $cname,
	                    $amount,
	                    $acct,
	                    $row->due_date->format("Y-m-d"),
	                    $total_paid,
		            ];
	            } else {
		            $_row = [
	                    'id' => $row->id,
				    	'contact_id' => $row->contact_id,
				    	'contact_first_name' => $row->contact->first_name,
				    	'contact_last_name' => $row->contact->last_name,
				    	'contact_organization' => $row->contact->organization,
				    	'account_id' => $row->account_id,
				    	'account_bank' => $row->account->bank,
				    	'account_number' => $row->account->account_number,
				    	'account_name' => $row->account->account_name,
				    	'dc' => $row->dc,
				    	'trx_date' => $row->trx_date->format("Y-m-d"),
				    	'amount' => $row->amount,
				    	'due_date' => $row->due_date->format("Y-m-d"),
				    	'installments' => $row->installments,
				    	'total_paid' => $row->total_paid,
				    	'last_paid' => $row->last_paid,
				    	'fullpaid_date' => $fpd,
				    	'ref_number'=> $row->ref_number,
				    	'note' => $row->note,
				    	'first_created' => $row->first_created->format("Y-m-d H:i:s"),
				    	'last_updated' => $lup	
		            ];
	            }
                array_push( $data, $_row );
            }
            
            $sum_amount = 0;
            $sum_paid = 0;
            if( !is_null( $sum->total_amount ) ) {
	            $sum_amount = $sum->total_amount;
            }
            if( !is_null( $sum->total_paid ) ) {
	            $sum_paid = $sum->total_paid;
            }
            
            $json_data = array(
                "draw" => intval( $draw ),
                "recordsTotal" => intval( $totalRows ),
                "recordsFiltered" => intval( $totalData ),
                "data" => $data,
                "summary" => array(
	                'amount' => $sum_amount,
	                'total_paid' => $sum_paid,
	                'formatted' => [
		                'amount' => solusipress_format_money( $sum_amount ),
		                'total_paid' => solusipress_format_money( $sum_paid ),
		                'balance' => solusipress_format_money( $sum_amount - $sum_paid ),
	                ]
                )
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
        			->contain( [
	        			'Contacts' => [
	        				'fields' => [ 'first_name', 'last_name', 'organization', 'email' ]
	        			],
	        			'Accounts' => [
		        			'fields' => [ 'bank','account_number', 'account_name' ]
	        			]
        			] )
        			->where( [ 'Debts.id' => $id ] )->first();
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
	        $fpd = $_entity->fullpaid_date;
	        $lup = $_entity->last_updated;
	        if( !is_null( $fpd ) ) {
		        $fpd = $fpd->format( "Y-m-d" );
	        }
	        if( !is_null( $lup ) ) {
		        $lup = $lup->format( "Y-m-d H:i:s" );
	        }
	        $data = [
		    	'id' => $_entity->id,
		    	'contact_id' => $_entity->contact_id,
		    	'contact_lookup' => $contact_lookup,
		    	'contact_first_name' => $_entity->contact->first_name,
		    	'contact_last_name' => $_entity->contact->last_name,
		    	'contact_organization' => $_entity->contact->organization,
		    	'account_id' => $_entity->account_id,
		    	'account_bank' => $_entity->account->bank,
		    	'account_number' => $_entity->account->account_number,
		    	'account_name' => $_entity->account->account_name,
		    	'dc' => $_entity->dc,
		    	'trx_date' => $_entity->trx_date->format("Y-m-d"),
		    	'amount' => $_entity->amount,
		    	'due_date' => $_entity->due_date->format("Y-m-d"),
		    	'installments' => $_entity->installments,
		    	'total_paid' => $_entity->total_paid,
		    	'last_paid' => $_entity->last_paid,
		    	'fullpaid_date' => $fpd,
		    	'ref_number'=> $_entity->ref_number,
		    	'note' => $_entity->note,
		    	'first_created' => $_entity->first_created->format("Y-m-d H:i:s"),
		    	'last_updated' => $lup	
			];
		}
        echo json_encode( $data );        
	}
	
	private function update_cashflow( $source, $id=null ) {
		
		$model = ModelLoader::get( 'CashFlows' );
		if( is_null( $id ) ) {
			$entity = $model->newEntity();
		} else {
			$entity = $model->get( $id );
		}
		$contact = \SolusiPress\Model\EntityDescriber::contact_info( $source->contact_id );
		$trx_no = '';
		if( $source->dc == 'd' ) {
			$trx_no .= 'HTG#';
		} elseif( $source->dc == 'c' ) {
			$trx_no .= 'PTG#';
		}

		if( $trx_no != '' ) {
			$trx_no .= str_pad( $source->id, 5, "0", STR_PAD_LEFT );
			$data = [
				'account_id' => $source->account_id,
				'trx_no' => $trx_no,
				'trx_date' => $source->trx_date->format( "Y-m-d" ),	
				'contact_id' => $source->contact_id,
				'dc' => $source->dc,
				'amount' => $source->amount,
				'object_model' => 'Debts',
				'object_id' => $source->id,
				'note' => $source->note,
				'last_update' => current_time( 'mysql' )
			];
			if( !empty( $contact ) ) {
				$data['from_to_name'] = $contact['contact_name'];
				$data['organization'] = $contact['organization'];
			}
		    $entity = $model->patchEntity( $entity, $data );
	        $errors = $entity->getErrors();
	        if( empty( $errors ) ) {
	            $model->save( $entity );
	        }	
        } 
        
	}
	
	public function insert_action() {

        $data = $this->set_data();        
        if( !isset( $data['installments'] ) ) {
	        $data['installments'] = 1;
        }
        $data['first_created'] = current_time( 'mysql' );
        if( !isset( $data['installments'] ) ) {
	        $data['installments'] = 1;
        }
        $data['total_paid'] = 0;
        
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
            $this->update_cashflow( $entity );
        } else {
	        $str_errors = '';
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
        $_data = $this->set_data( $id );        
        $data = [];
        
        $allow = [ 'id', 'installments', 'due_date', 'note', 'ref_number' ];
        foreach( $allow as $fld ) {
	        $data[ $fld ] = $_data[ $fld ];
        }
        $data['last_updated'] = current_time( 'mysql' );
        
        $status = false;
        $message = null;        
        $model = ModelLoader::get( $this->modelName );
        $entity = $model->get( $data['id'] );
        $entity = $model->patchEntity( $entity, $data );
        if( empty( $entity->getErrors() ) ) {
            $saved = $model->save( $entity );
            $status = true;
            $mCF = ModelLoader::get( 'CashFlows' );
            $cashflow = $mCF->find()->where([
				'object_model' => 'Debts',
				'object_id' => $id,
			])->first();
			if( $cashflow ) {
	            $this->update_cashflow( $saved, $cashflow->id );
	        }
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
        
    	try {
	        
	        $entity = $model->get( $id );
	        if( $model->delete( $entity ) ) {
	            $status = true;
	            $this->delete_cashflow( $id );
	        } else {
	            $message = 'Hapus data gagal, mohon ulangi kembali';
	        }
	        
        } catch( RecordNotFoundException $notFound ) {
	        $message = 'Transaksi tidak ditemukan';
        }
        
        $result = [
            'status' => $status,
            'message' => $message
        ];
        
        echo json_encode( $result );
	}
	
	private function delete_cashflow( $id ) {
		
		$model = ModelLoader::get( 'CashFlows' );
		$cashflow = $model->find()->where([
			'object_model' => 'Debts',
			'object_id' => $id,
		])->first();
		
		if( $cashflow ) {
			
			$debtPayment = ModelLoader::get( 'DebtPayments' );
			$payments = $debtPayment->find()->where([
				'debt_id' => $id
			])->all();
			if( $payments && !empty( $payments ) ) {
				$to_delete = [];
				foreach( $payments as $payment ) {
					array_push( $to_delete, $payment->id );
				}
				$model->deleteAll([
					'object_model' => 'DebtPayments',
					'object_id IN' => $to_delete
				]);
				$debtPayment->deleteAll( [ 'debt_id' => $id ] );
			}
			$model->delete( $cashflow );
		}
		
	}
	
}