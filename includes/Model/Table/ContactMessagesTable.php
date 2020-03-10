<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use Cake\Validation\Validator;
use SolusiPress\Model\Entity\ContactMessage;
use Cake\ORM\Behavior\TreeBehavior;

class ContactMessagesTable extends WP_Table {
	
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->addBehavior('Tree');
        
        $this->setDisplayField('msg_subject');
        $this->setEntityClass( ContactMessage::class );
        
        $this->belongsTo('Contacts', [
            'foreignKey' => 'contact_id',
            'className' => 'SolusiPress\Model\Table\ContactsTable',
            'propertyName' => 'contact'
        ]);
        $this->belongsTo( 'Users', [
	    	'foreignKey' => 'followup_by',
	    	'className' => 'SolusiPress\Model\Table\UsersTable',
	    	'property_name' => 'user'    
        ] );
        $this->hasMany( 'Replies', [
	    	'foreignKey' => 'parent_id',
	    	'className' => 'SolusiPress\Model\Table\ContactMessagesTable',
	    	'propertyName' => 'replies'    
        ] );
                
        
    }
	
    public function validationDefault( Validator $validator )
    {
        $validator
            ->scalar('id')
            ->maxLength('id', 15)
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('msg_date')
            ->notEmpty('msg_date');

        $validator
            ->scalar('contact_id')
            ->notEmpty('contact_id');

        $validator
            ->scalar('msg_subject')
            ->notEmpty('msg_subject');

        return $validator;
    }
	
}