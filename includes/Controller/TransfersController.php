<?php
namespace SolusiPress\Controller;
use SolusiPress\Controller\AppController;
use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;

class TransfersController extends AppController {
	
    protected $modelName = 'Transfers';

    public function __construct() {
        parent::__construct();
    }
    
	private function validate() {
		
		$data = $this->set_data();
		if( $data['src_account'] == $data['dst_account'] ) {
			 echo json_encode( [ 'status' => false, 'message' => 'Sumber dan tujuan rekening tidak boleh sama' ] );	
			 exit;
		}
		if( $data['amount'] <= 0 ) {
			 echo json_encode( [ 'status' => false, 'message' => 'Nominal harus diisi' ] );	
			 exit;
		}
		$model = ModelLoader::get( 'CashFlows' );
		$q1 = $model->find();
        $q1->select([
	        'dr' => $q1->func()->sum( "case when CashFlows.dc='d' then CashFlows.amount else 0 end" ),
	        'cr' => $q1->func()->sum( "case when CashFlows.dc='c' then CashFlows.amount else 0 end" ),
        ]);
        $q1->where( [ 'account_id' => $data['src_account'] ] );
		$sum_dc = $q1->first();
		$sum_dr = 0;
		$sum_cr = 0;
		if( $sum_dc ) {
			$sum_dr = $sum_dc->dr;
			$sum_cr = $sum_dc->cr;
		}
		$q1_balance = $sum_dr - $sum_cr;
		
		if( $q1_balance < $data['amount'] ) {
			 echo json_encode( [ 'status' => false, 'message' => 'Saldo pada sumber tidak mencukupi' ] );	
			 exit;
		}
		
	}
	
	public function insert_action() {

        $this->validate();
        
        $status = false;
        $message = 'Transaksi gagal, mohon ulangi kembali';
        $data = $this->set_data();
        
        $model = ModelLoader::get( $this->modelName );
        $entity = $model->newEntity();
        $entity = $model->patchEntity( $entity, $data );
        $errors = $entity->getErrors();
        if( empty( $errors ) ) {
            
            if( $model->save( $entity ) ) {
	            
	            $status = true;
	            $insert_id = $entity->id;
				$message = 'Transaksi berhasil disimpan.';
				// cashflow input
				
				$q = $model->find()->where( [ 'Transfers.id'=>$insert_id ] );
				$row = $q->contain( [
					'FromAccounts', 'ToAccounts'
				] )->first();
				
				$cf = ModelLoader::get( 'CashFlows' );
				$trx_no = 'TRF#'.str_pad( $insert_id, 5, "0", STR_PAD_LEFT );
				
				$to_name = trim( $row->to_account->bank . ' ' . $row->to_account->account_number );
				$from_name = trim( $row->from_account->bank . ' ' . $row->from_account->account_number );
				
				$ent = $cf->newEntity();
				$i1 = [
					'account_id' => $data['src_account'],
					'dc' => 'c',
					'trx_no' => $trx_no.'-C',
					'trx_date' => $data['trf_date'],
					'amount' => $data['amount'],
					'object_model' => 'Transfers',
					'object_id' => $insert_id,
					'from_to_name' => $to_name,
					'note' => $data['note'],
					'last_update' => date( 'Y-m-d H:i:s' )	
				];
				$ent = $cf->patchEntity( $ent, $i1 );
				$cf->save( $ent );
				
				$ent = $cf->newEntity();
				$i1 = [
					'account_id' => $data['dst_account'],
					'dc' => 'd',
					'trx_no' => $trx_no.'-D',
					'trx_date' => $data['trf_date'],
					'amount' => $data['amount'],
					'object_model' => 'Transfers',
					'object_id' => $insert_id,
					'from_to_name' => $from_name,
					'note' => $data['note'],
					'last_update' => date( 'Y-m-d H:i:s' )	
				];
				$ent = $cf->patchEntity( $ent, $i1 );
				$cf->save( $ent );
				
			}
			
        }        
        
        echo json_encode( [
	    	'status' => $status,
	    	'message' => $message,
	    	'errrors' => $entity->getErrors()
        ] );
			
	}

}