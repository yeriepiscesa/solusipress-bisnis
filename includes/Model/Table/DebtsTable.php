<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use Cake\Validation\Validator;
use SolusiPress\Model\Entity\Debt;

class DebtsTable extends WP_Table {
	
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('id');
        $this->setEntityClass( Debt::class );
        
        $this->belongsTo('Accounts', [
            'foreignKey' => 'account_id',
            'className' => 'SolusiPress\Model\Table\AccountsTable',
            'propertyName' => 'account'
        ]);
        
        $this->belongsTo('Contacts',[
	        'foreignKey' => 'contact_id',
	        'className' => 'SolusiPress\Model\Table\ContactsTable',
	        'propertyName' => 'contact'
        ]);
        
        $this->hasMany('DebtPayments', [
	        'foreignKey' => 'debt_id',
	        'className' => 'SolusiPress\Model\Table\DebtPaymentsTable',
	        'propertyName' => 'payments'
        ]);
    }
    
    public function validationDefault(Validator $validator)
    {
        $validator
            ->scalar('id')
            ->maxLength('id', 7)
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('contact_id')
            ->requirePresence( ['contact_id'=>[ 'mode'=>'create', 'message'=>'Kontak diperlukan' ] ])
            ->notEmpty('contact_id','Kontak harus diisi');

        $validator
            ->scalar('account_id')
            ->requirePresence( ['account_id'=>[ 'mode'=>'create', 'message'=>'Kas/Bank diperlukan' ] ])
            ->notEmpty('account_id','Kas/Bank harus diisi');
		
        $validator
            ->scalar('trx_date')
            ->requirePresence( ['trx_date'=>[ 'mode'=>'create', 'message'=>'Tangal transaksi diperlukan' ] ])
            ->notEmpty('trx_date','Tanggal transaksi harus diisi');

        $validator
            ->scalar('due_date')
            ->requirePresence( ['due_date'=>[ 'mode'=>'create', 'message'=>'Tangal jatuh tempo diperlukan' ] ])
            ->notEmpty('due_date','Tanggal jatuh tempo harus diisi');

        $validator
            ->scalar('amount')
            ->requirePresence( ['amount'=>[ 'mode'=>'create', 'message'=>'Nominal transaksi diperlukan' ]] )
            ->notEmpty('amount', 'Nominal harus diisi');
		            
        return $validator;
    }
}