<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use Cake\Validation\Validator;
use Cake\ORM\RulesChecker;
use SolusiPress\Model\Entity\District;

class DistrictsTable extends WP_Table
{

    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->setDisplayField('name');
        $this->setEntityClass( District::class );

        $this->belongsTo('Regencies', [
            'foreignKey' => 'regency_id',
            'joinType' => 'INNER',
            'className' => 'SolusiPress\Model\Table\RegenciesTable',
            'propertyName' => 'regency'            
        ]);
        $this->hasMany( 'Villages', [
            'foreignKey' => 'district_id',
            'className' => 'SolusiPress\Model\Table\VillagesTable',
            'propertyName' => 'villages'
        ] );
    }

    public function validationDefault(Validator $validator)
    {
        $validator
            ->scalar('id')
            ->maxLength('id', 7)
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
        $rules->add($rules->existsIn(['regency_id'], 'Regencies'));

        return $rules;
    }
}
