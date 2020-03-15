<?php 
namespace SolusiPress\Model;

use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;
use Cake\Datasource\Exception\RecordNotFoundException;

class DebtTrx {
	
	public static function updatePaidBalance( $debt_id ) {
		$Debt = ModelLoader::get( 'Debts' );
		$DebtPayment = ModelLoader::get( 'DebtPayments' );
		
		$payments = $DebtPayment->find()->where( [ 'debt_id' => $debt_id ] )->order(['trx_date'=>'DESC'])->all();
		if( $payments && !empty($payments) ) {
			$total_paid = 0;
			$last_paid = null;
			foreach( $payments as $payment ) {
				$last_paid = $payment->trx_date;
				$total_paid = $total_paid + $payment->amount;				
			}
			$debt = $Debt->get( $debt_id );
			$data = [ 
				'total_paid' => $total_paid,
				'last_paid' => $last_paid,
				'last_updated' => current_time( 'mysql' ),
			];
			if( $total_paid >= $debt->amount ) {
				$data['fullpaid_date'] = $last_paid;				
			} else {
				$data['fullpaid_date'] = null;
			}
			$debt = $Debt->patchEntity( $debt, $data );
			$Debt->save( $debt );
		}
	}	
	
	public static function updateCashFlow( $debt_payment_id ) {
		
		$model = ModelLoader::get( 'CashFlows' );
		$cashflow = $model->find()->where([
			'object_model' => 'DebtPayments',
			'object_id' => $debt_payment_id
		])->first();
		
		try {
			
			$payment = ModelLoader::get( 'DebtPayments' )->get( $debt_payment_id, [
				'contain' => [ 'Debts' => [ 'Contacts' ] ]
			] );
			$contact_name = trim( $payment->debt->contact->first_name . ' ' . $payment->debt->contact->last_name );
			if( $payment->debt->dc == 'c' ) {
				$dc = 'd';
				$trx_no = 'PBPT#';
				$note = 'Penerimaan piutang';
			} else {
				$dc = 'c';
				$trx_no = 'PBHT#';
				$note = 'Pembayaran hutang';
			}
			$trx_no .= str_pad( $debt_payment_id, 5, "0", STR_PAD_LEFT );;
			$data = [
				'object_model' => 'DebtPayments',
				'object_id' => $debt_payment_id,
				'trx_date' => $payment->trx_date->format("Y-m-d"),
				'trx_no' => $trx_no,
				'account_id' => $payment->account_id,
				'contact_id' => $payment->debt->contact_id,
				'from_to_name' => $contact_name,
				'organization' => $payment->debt->contact->organization,
				'amount' => $payment->amount,
				'dc' => $dc,
				'note' => $note,
				'last_update' => current_time( 'mysql' )
			];
			
			if( $cashflow ) {
				$entity = $model->get( $cashflow->id );
			} else {
				$entity = $model->newEntity();					
			}
			$entity = $model->patchEntity( $entity, $data );
			$model->save( $entity );
			
		} catch ( RecordNotFoundException $notFound ) {
			
			if( $cashflow ) {
				$entity = $model->get( $cashflow->id );
				$model->delete( $entity );
			}
			
		}
	}
	
}