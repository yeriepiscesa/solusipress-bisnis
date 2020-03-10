<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use Cake\Validation\Validator;
use SolusiPress\Model\Entity\DebtPayment;

class DebtPaymentsTable extends WP_Table {
	
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('id');
        $this->setEntityClass( DebtPayment::class );
        
        $this->belongsTo('Debts',[
	        'foreignKey' => 'debt_id',
	        'className' => 'SolusiPress\Model\Table\DebtsTable',
	        'propertyName' => 'debt'
        ]);
        $this->belongsTo('Accounts',[
	        'foreignKey' => 'account_id',
	        'className' => 'SolusiPress\Model\Table\AccountsTable',
	        'propertyName' => 'account'
        ]);
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator
            ->scalar('id')
            ->maxLength('id', 7)
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('account_id')
            ->requirePresence( ['account_id'=>[ 'mode'=>'create', 'message'=>'Kas/Bank diperlukan' ] ])
            ->notEmpty('account_id','Kas/Bank harus diisi');
		
        $validator
            ->scalar('trx_date')
            ->requirePresence( ['trx_date'=>[ 'mode'=>'create', 'message'=>'Tangal transaksi diperlukan' ] ])
            ->notEmpty('trx_date','Tanggal nota/transaksi harus diisi');

        $validator
            ->scalar('amount')
            ->requirePresence( ['amount'=>[ 'mode'=>'create', 'message'=>'Nominal transaksi diperlukan' ]] )
            ->notEmpty('amount', 'Nominal harus diisi');
		            
        return $validator;
    }
    
}