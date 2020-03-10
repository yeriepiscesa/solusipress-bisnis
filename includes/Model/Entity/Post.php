<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class Post extends Entity
{
    protected $_accessible = [
        'ID' => true,
        'post_date' => true,
        'post_title' => true,
        'post_status' => true,
        'post_name' => true,
        'post_parent' => true,
        'post_type' => true,
    ];
}
