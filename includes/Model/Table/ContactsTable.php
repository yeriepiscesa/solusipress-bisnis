<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use Cake\Validation\Validator;
use SolusiPress\Model\Entity\Contact;
use Cake\ORM\RulesChecker;
use Cake\ORM\Rule\IsUnique;

class ContactsTable extends WP_Table {
	
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('first_name');
        $this->setEntityClass( Contact::class );
        
        $this->belongsTo('ContactTypes', [
            'foreignKey' => 'contact_type_id',
            'className' => 'SolusiPress\Model\Table\ContactTypesTable',
            'propertyName' => 'contact_type'
        ]);
        
		$this->hasMany( 'ContactMessages', [
	    	'foreignKey' => 'contact_id',
	    	'className' => 'SolusiPress\Model\Table\ContactMessagesTable',
	    	'propertyName' => 'messages'    
		] );
    }
	
    public function validationDefault( Validator $validator )
    {
        $validator
            ->scalar('id')
            ->maxLength('id', 15)
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('first_name')
            ->notEmpty('first_name');

        $validator
            ->scalar('email')
            ->notEmpty('email','Email harus diisi')
            ->add( 'email', 'unique', [
                    'rule' => 'validateUnique',
                    'provider' => 'table',
                    'message' => 'Email sudah terdaftar'
             ]);

        return $validator;
    }
	
	public function buildRules(RulesChecker $rules)
	{
		$rules->add( $rules->isUnique( ['email'] ) );
		return $rules;
	}	
}