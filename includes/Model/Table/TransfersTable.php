<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use Cake\Validation\Validator;
use SolusiPress\Model\Entity\Transfer;

class TransfersTable extends WP_Table {
	
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('trf_date');
        $this->setEntityClass( Transfer::class );
        
        $this->belongsTo('FromAccounts', [
            'foreignKey' => 'src_account',
            'className' => 'SolusiPress\Model\Table\AccountsTable',
            'propertyName' => 'from_account'
        ]);
        
        $this->belongsTo('ToAccounts', [
            'foreignKey' => 'dst_account',
            'className' => 'SolusiPress\Model\Table\AccountsTable',
            'propertyName' => 'to_account'
        ]);
        
    }
	
    public function validationDefault(Validator $validator)
    {
        $validator
            ->scalar('trf_date')
            ->requirePresence( ['trf_date'=>[ 'mode'=>'create', 'message'=>'Tangal diperlukan' ] ] )
            ->notEmpty('trf_date', 'Tanggal harus diisi');

        $validator
            ->scalar('amount')
            ->requirePresence( ['amount'=>[ 'mode'=>'create', 'message'=>'Jumlah transfer diperlukan' ] ] )
            ->notEmpty('amount', 'Jumlah transfer harus diisi');

        return $validator;
    }
	
}