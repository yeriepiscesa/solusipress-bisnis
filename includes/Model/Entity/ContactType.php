<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class ContactType extends Entity
{
    protected $_accessible = [
        'id' => true,
        'name' => true,
        'is_default' => true,
        'color' => true,
        'ordering' => true,
        'description' => true,
    ];
}
