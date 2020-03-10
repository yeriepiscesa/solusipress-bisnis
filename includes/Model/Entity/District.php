<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class District extends Entity
{
    protected $_accessible = [
	    'id' => true,
        'regency_id' => true,
        'name' => true,
        'regency' => true,
        'villages' => true
    ];
}
