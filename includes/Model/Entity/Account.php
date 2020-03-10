<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class Account extends Entity
{
    protected $_accessible = [
        'id' => true,
        'bank' => true,
        'account_number' => true,
        'account_name' => true,
        'public_account' => true,
        'logo_url' => true,
        'description' => true,
    ];
    
    protected function _getAccountView() {
	    
	    return trim( $this->bank . ' ' . $this->account_number );
    }
}
