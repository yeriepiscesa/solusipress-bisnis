<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use SolusiPress\Model\Entity\Regency;

class RegenciesTable extends WP_Table
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('name');
        $this->setEntityClass( Regency::class );
        $this->belongsTo('Provinces', [
            'foreignKey' => 'province_id',
            'className' => 'SolusiPress\Model\Table\ProvincesTable',
            'propertyName' => 'province'            
        ]);
        $this->hasMany( 'Districts', [
            'foreignKey' => 'regency_id',
            'className' => 'SolusiPress\Model\Table\DistrictsTable',
            'propertyName' => 'district'            
        ] );
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->scalar('id')
            ->maxLength('id', 4)
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
        $rules->add($rules->existsIn(['province_id'], 'Provinces'));

        return $rules;
    }
}
