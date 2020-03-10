<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class CashFlow extends Entity
{
    protected $_accessible = [
        'id' => true,
        'account_id' => true,
        'account' => true,
        'trx_no' => true,
        'trx_date' => true,
        'contact_id' => true,
        'contact' => true,
        'from_to_name' => true,
        'organization' => true,
        'dc' => true,
        'amount' => true,
        'post_id' => true,
        'object_model' => true,
        'object_id' => true,
        'note' => true,
        'last_update' => true,
    ];
}
