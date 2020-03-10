<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use Cake\Validation\Validator;
use SolusiPress\Model\Entity\Province;

class ProvincesTable extends WP_Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);        
        $this->setDisplayField('name');
        $this->setEntityClass( Province::class );
        $this->hasMany( 'Regencies', [
            'foreignKey' => 'province_id',
            'className' => 'SolusiPress\Model\Table\RegenciesTable',
            'propertyName' => 'regencies'            
        ] );
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->scalar('id')
            ->maxLength('id', 2)
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        return $validator;
    }
}
