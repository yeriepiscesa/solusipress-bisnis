<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use Cake\Validation\Validator;
use SolusiPress\Model\Entity\CashFlow;

class CashFlowsTable extends WP_Table {
	
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('trx_no');
        $this->setEntityClass( CashFlow::class );
        
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
    }
	
    public function validationDefault(Validator $validator)
    {
        $validator
            ->scalar('id')
            ->maxLength('id', 7)
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('trx_date')
            ->requirePresence( ['trx_date'=>[ 'mode'=>'create', 'message'=>'Tangal nota/transaksi diperlukan' ] ])
            ->notEmpty('trx_date','Tanggal nota/transaksi harus diisi');

        $validator
            ->scalar('amount')
            ->requirePresence( ['amount'=>[ 'mode'=>'create', 'message'=>'Nominal transaksi diperlukan' ]] )
            ->notEmpty('amount', 'Nominal harus diisi');

        $validator
            ->scalar('from_to_name')
            ->requirePresence( ['from_to_name'=>[ 'mode'=>'create', 'message'=>'Nama/kontak diperlukan' ]] )
            ->notEmpty('from_to_name', 'Nama/kontak harus diisi');

        return $validator;
    }
	
}