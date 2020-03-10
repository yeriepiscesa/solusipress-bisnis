<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class Village extends Entity
{
    protected $_accessible = [
	    'id' => true,
        'district_id' => true,
        'name' => true,
        'district' => true
    ];
}
