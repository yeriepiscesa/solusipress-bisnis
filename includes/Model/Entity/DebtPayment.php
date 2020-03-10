<?php namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class DebtPayment extends Entity
{
	
    protected $_accessible = [
        'id' => true,
        'debt_id' => true,
        'debt' => true,
        'trx_date' => true,
        'account_id' => true,
        'account' => true,
        'amount' => true,
        'instll_no' => true,
        'ref_number' => true,
        'note' => true,
        'first_created' => true,
        'last_updated' => true,
	];
	
}