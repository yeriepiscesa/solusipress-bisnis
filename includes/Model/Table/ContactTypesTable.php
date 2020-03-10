<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;

use Cake\Validation\Validator;
use Cake\Event\Event;
use Cake\Datasource\EntityInterface;
use ArrayObject;

use SolusiPress\Model\Entity\ContactType;

class ContactTypesTable extends WP_Table {
	
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('name');
        $this->setEntityClass( ContactType::class );
    }
	
    public function validationDefault(Validator $validator)
    {
        $validator
            ->scalar('id')
            ->maxLength('id', 11)
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('name')
            ->notEmpty('name');

        return $validator;
    }
	
    public function beforeSave(Event $event, EntityInterface $entity, ArrayObject $options) 
    {
	    if( $entity->is_default == '1' ) {
		    // change all to 0
			$this->query()
		        ->update()
		        ->set(['is_default' => '0'])
		        ->execute();
	    }
    }
}