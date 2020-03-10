<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use SolusiPress\Model\Entity\User;

class UsersTable extends WP_Table {
	
    protected $autoTable = false;
    
    public function initialize(array $config)
    {
	    global $wpdb;
        parent::initialize($config);
        $this->prefix = $wpdb->prefix;        
        if( is_multisite() ) {
            $blog_id = get_current_blog_id();
            $this->prefix = str_replace( $blog_id.'_', '', $this->prefix );
        }
        $this->setTable( $this->prefix.'users'  );
        $this->setPrimaryKey( 'ID' );        
        $this->setDisplayField('display_name');
        $this->setEntityClass( User::class );
    }
	
}