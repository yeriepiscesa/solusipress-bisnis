<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use Cake\Validation\Validator;

use SolusiPress\Model\Entity\Account;

class AccountsTable extends WP_Table {
	
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('bank');
        $this->setEntityClass( Account::class );
    }
	
    public function validationDefault(Validator $validator)
    {
        $validator
            ->scalar('id')
            ->maxLength('id', 13)
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('bank')
            ->notEmpty('bank');

        $validator
            ->scalar('account_number')
            ->notEmpty('account_number','No rekening harus diisi')
            ->add( 'account_number', 'unique', [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                    'message' => 'No rekening sudah terdaftar'
             ]);

        return $validator;
    }
	
}