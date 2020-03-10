<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class User extends Entity
{
    protected $_accessible = [
        'ID' => true,
        'user_login' => true,
        'user_pass' => true,
        'user_nicename' => true,
        'user_email' => true,
        'user_url' => true,
        'user_registered' => true,
        'user_activation_key' => true,
        'user_status' => true,
        'display_name' => true,
    ];
}
