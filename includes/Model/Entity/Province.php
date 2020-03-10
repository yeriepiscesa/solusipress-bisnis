<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class Province extends Entity
{
    protected $_accessible = [
	    'id' => true,
        'name' => true,
        'regencies' => true
    ];
}
