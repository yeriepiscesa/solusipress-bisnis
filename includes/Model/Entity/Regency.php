<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class Regency extends Entity
{
    protected $_accessible = [
	    'id' => true,
        'province_id' => true,
        'name' => true,
        'province' => true,
        'districts' => true
    ];
}
