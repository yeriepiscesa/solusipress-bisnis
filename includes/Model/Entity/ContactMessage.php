<?php
namespace SolusiPress\Model\Entity;

use Cake\ORM\Entity;

class ContactMessage extends Entity
{
    protected $_accessible = [
        'id' => true,
        'parent_id' => true,
        'contact_id' => true,
        'msg_date' => true,
        'msg_subject' => true,
        'msg_text' => true,
        'contact' => true,
        'dtm_read' => true,
        'dtm_followup' => true,
        'followup_by' => true,
        'user' => true,
        'replies' => true,
    ];
}
