<?php namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class Debt extends Entity
{
	
    protected $_accessible = [
        'id' => true,
        'contact_id' => true,
        'contact' => true,
        'account_id' => true,
        'account' => true,
        'dc' => true,
        'trx_date' => true,
        'amount' => true,
        'due_date' => true,
        'installments' => true,
        'total_paid' => true,
        'last_paid' => true,
        'fullpaid_date' => true,
        'ref_number' => true,
        'note' => true,
        'first_created' => true,
        'last_updated' => true,
        'payments' => true,
    ];
    
}	