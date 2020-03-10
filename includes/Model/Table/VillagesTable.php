<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use SolusiPress\Model\Entity\Village;

class VillagesTable extends WP_Table
{
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('name');
        $this->setEntityClass( Village::class );
        $this->belongsTo('Districts', [
            'foreignKey' => 'district_id',
            'joinType' => 'INNER',
            'className' => 'SolusiPress\Model\Table\DistrictsTable',
            'propertyName' => 'district'            
        ]);
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->scalar('id')
            ->maxLength('id', 10)
            ->allowEmpty('id', 'create');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmpty('name');

        return $validator;
    }

    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['district_id'], 'Districts'));
        return $rules;
    }
}
