<?php
namespace SolusiPress\Controller;
use SolusiPress\Controller\AppController;
use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use DateTime;
use Cake\Datasource\Exception\RecordNotFoundException;

class DebtPaymentsController extends AppController {

    protected $modelName = 'DebtPayments';
    protected $list_filtered = false;

    protected $date_filtered = false;
    protected $min_date = null;
    protected $max_date = null;
    
    public function __construct() {
        parent::__construct();
    }
    
	public function index_action() { 
    	
        $request = $_GET;
    	header("Content-Type: application/json");
        if( isset( $request['debt_id'] ) ) {
	        
	        try {
		        
		        $debt = ModelLoader::get( 'Debts' )->get( $request['debt_id'],[
			        'contain' => [ 'Accounts', 'Contacts' ]
		        ] );
		        
				$contact_name = trim( $debt->contact->first_name . ' ' . $debt->contact->last_name );
		        $meta = [
			        'debt' => [
				        'trx_date' => $debt->trx_date->format( 'Y-m-d' ),
				        'due_date' => $debt->due_date->format( 'Y-m-d' ),
				        'contact_name' => $contact_name,
				        'organization' => $debt->contact->organization,
				        'bank' => $debt->account->bank,
				        'account_number' => $debt->account->account_number,
				        'dc' => $debt->dc,
				        'amount' => $debt->amount,
				        'ref_number' => $debt->ref_number,
				        'note' => $debt->note,
			        ]
		        ];
		        
		        $model = ModelLoader::get( $this->modelName );
		        $query = $model->find();
		        $query->contain( [ 
		        	'Accounts' => [
			        	'fields' => [ 'bank', 'account_number', 'account_name' ]
					]
		        ] );
		        $where = [ 'DebtPayments.debt_id' => $request['debt_id'] ];
		        $query->where( $where );
				$rows = $query->all();
				
		        $total_paid = 0;
		        if( !empty( $rows ) ) {
		            $data = [];
		            foreach( $rows as $_row ) {
				        $lup = $_row->last_updated;
				        if( !is_null( $lup ) ) {
					        $lup = $lup->format( "Y-m-d H:i:s" );
				        }
			            $row = [
				            'id' => $_row->id,
				            'trx_date' => $_row->trx_date->format( 'Y-m-d' ),
				            'account_id' => $_row->account_id,
				            'account_bank' => $_row->account->bank,
				            'account_number' => $_row->account->account_number,
				            'account_name' => $_row->account->account_name,
				            'amount' => $_row->amount,
				            'ref_number' => $_row->ref_number,
				            'note' => $_row->note,
				            'first_created' => $_row->first_created->format( 'Y-m-d H:i:s' ),
				            'last_updated' => $lup
			            ];
			            array_push( $data, $row );
			            $total_paid = $total_paid + $_row->amount;
		            }
		        }
		        $meta['payment'] = [
			    	'total' => $total_paid,
			    	'balance' => $meta['debt']['amount'] - $total_paid    
		        ];
				
		        $json_data = array(
					'data' => $data,
					'meta' => $meta,
					'recordsTotal' => $rows->count()
		        );
		        
	        } catch( RecordNotFoundException $notFound ) {
		        $json_data = array(
			        'data' => array(),
			        'message' => 'Transaksi tidak ditemukan'
		        );
	        }
	        
        } else {
	    	$json_data = array(
		    	'data' => array(),
		    	'message' => 'Referensi transaksi diperlukan'	
	    	); 
        }
		
        echo json_encode($json_data);     
        
	}
	    
	public function insert_action() {
		
		if( is_null( $this->data ) ) {
        	$data = $this->set_data();    
        } else {
	        $data = $this->data;
        }    
        $data['first_created'] = current_time( 'mysql' );
		if( !isset( $data['instll_no'] ) ) {
			$data['instll_no'] = 1;
		}		
        
        $status = false;
        $message = null;
        $insert_id = null;   
        
        $continue = true;
        if( !isset( $data['debt_dc'] ) ) {
	        $debt = $this->get_debt( $data['debt_id'] );
	        if( $debt ) {
		        $data['debt_dc'] = $debt->dc;
	        } else {
		        $continue = false;
		        $message = 'Referensi hutang/piutang tidak ditemukan';
	        }
        }
         
        if( $continue ) {    
	         
	        $model = ModelLoader::get( $this->modelName );
	        $entity = $model->newEntity();
	        $entity = $model->patchEntity( $entity, $data );
	        $errors = $entity->getErrors();
	        if( empty( $errors ) ) {
		        if( $data['debt_dc'] == 'd' ) {
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
					\SolusiPress\Model\DebtTrx::updatePaidBalance( $data['debt_id'] );            
					\SolusiPress\Model\DebtTrx::updateCashFlow( $insert_id );
				}
	        } else {
		        $str_errors = '';
		        if( $str_errors == '' ) {
			        $str_errors = 'periksa kembali isian Anda';
		        }
	            $message = 'Tambah data gagal,' . $str_errors;
	        }
	        
        }
        $result = [
            'status' => $status,
            'message' => $message, 
            'insert_id' => $insert_id
        ];        
        
        if( !is_null( $this->data ) ) {
	        return $result;
        }
        echo json_encode( $result );
		         		
	}
	
	public function update_action( $id ) {
		if( is_null( $this->data ) ) {
        	$data = $this->set_data();    
        } else {
	        $data = $this->data;
        }    
        $data['last_updated'] = current_time( 'mysql' );
		if( !isset( $data['instll_no'] ) ) {
			$data['instll_no'] = 1;
		}		
        $status = false;
        $message = null;
        $continue = true;
        if( !isset( $data['debt_dc'] ) ) {
	        $debt = $this->get_debt( $data['debt_id'] );
	        if( $debt ) {
		        $data['debt_dc'] = $debt->dc;
	        } else {
		        $continue = false;
		        $message = 'Referensi hutang/piutang tidak ditemukan';
	        }
        }
        
        if( $continue ) {
	        $model = ModelLoader::get( $this->modelName );
	        $entity = $model->get( $id );
	        $entity = $model->patchEntity( $entity, $data );
	        $errors = $entity->getErrors();
	        if( empty( $errors ) ) {
		        if( $data['debt_dc'] == 'd' ) {
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
					\SolusiPress\Model\DebtTrx::updatePaidBalance( $data['debt_id'] );            
					\SolusiPress\Model\DebtTrx::updateCashFlow( $id );
				}	
	        } else {
		        $str_errors = '';
		        if( $str_errors == '' ) {
			        $str_errors = 'periksa kembali isian Anda';
		        }
	            $message = 'Tambah data gagal,' . $str_errors;
	        }
	    }
	        
        $result = [
            'status' => $status,
            'message' => $message
        ];        
        
        if( !is_null( $this->data ) ) {
	        return $result;
        }
        
        echo json_encode( $result );
	}

	public function delete_action( $id ) {
        $status = false;
        $message = null;
        $model = ModelLoader::get( $this->modelName );
        $entity = $model->get( $id );
        if( $entity ) {
	        $debt_id = $entity->debt_id;
	        if( $model->delete( $entity ) ) {
		        $status = true;
				\SolusiPress\Model\DebtTrx::updatePaidBalance( $debt_id );            
				\SolusiPress\Model\DebtTrx::updateCashFlow( $id );
			} else {
				$message = 'Hapus data gagal, mohon ulangi kembali.';
			}
		}
        $result = [
            'status' => $status,
            'message' => $message
        ];        
        if( !$this->http_call ) {
	        return $result;
        }
        echo json_encode( $result );
	}
	
	private function get_debt( $id=null ) {
		$debt = ModelLoader::get( 'Debts' )->find()->where( ['id'=>$id] )->first();
		return $debt;
	}
	
	public function process_action( $debt_id, $process ) {
		
		$status = false;
		$message = 'Tidak ada proses yang dijalankan';
		
		if( $process == 'update-payment' ) {
			$data = app('request')->body;
			if( isset( $data['payments'] ) ) {
				$debt = $this->get_debt( $debt_id );
				if( $debt ) {
					$message = '';
					foreach( $data['payments'] as $payment ) {
						$this->data = $payment;
						$this->data['debt_id'] = $debt_id;
						$this->data['debt_dc'] = $debt->dc;
						if( $payment['id'] == null ) {
							$_result = $this->insert_action();
						} else {
							$_result = $this->update_action( $payment['id'] );
						}	
						if( !$_result['status'] ) {
							if( $message != '' ) {
								$message .= "\n";
							}
						}
					}									
					$this->data = null;
					$status = true;
				} else {
					$message = 'Referensi hutang/piutang tidak ditemukan';
				}
			}
			if( isset( $data['to_delete'] ) ) {
				$this->http_call = false;
				foreach( $data['to_delete'] as $paymentID ) {
					$this->delete_action( $paymentID );
				}		
				$this->http_call = true;
			}
		}
		
        $result = [
            'status' => $status,
            'message' => $message
        ];        
        echo json_encode( $result );
		
	}
	
}