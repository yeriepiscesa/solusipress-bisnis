<?php
namespace SolusiPress\Model\Table;

use SolusiPress\Model\Table\WP_Table;
use SolusiPress\Model\Entity\Post;

class PostsTable extends WP_Table {
	
    protected $autoTable = false;
    
    public function initialize(array $config)
    {
	    global $wpdb;
        parent::initialize($config);
        $this->prefix = $wpdb->prefix;        
        $this->setTable( $this->prefix.'posts'  );
        $this->setPrimaryKey( 'ID' );        
        $this->setDisplayField('post_title');
        $this->setEntityClass( Post::class );
    }
	
}