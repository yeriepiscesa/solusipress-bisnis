<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class Transfer extends Entity
{
    protected $_accessible = [
        'id' => true,
        'src_account' => true,
        'dst_account' => true,
        'trf_date' => true,
        'amount' => true,
        'note' => true,
        'from_account' => true,
        'to_account' => true
    ];
}