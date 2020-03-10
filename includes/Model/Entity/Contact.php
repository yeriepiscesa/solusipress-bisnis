<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class Contact extends Entity
{
    protected $_accessible = [
        'id' => true,
        'first_name' => true,
        'last_name' => true,
        'contact_type_id' => true,
        'organization' => true,
        'email' => true,
        'phone' => true,
        'note' => true,
        'wp_user_id' => true,
        'whatsapp' => true,
        'instagram' => true,
        'twitter' => true,
        'facebook' => true,
        'linkedin' => true,
        'last_update' => true,
        'contact_type' => true,
        'messages' => true,
    ];
}
