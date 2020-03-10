<?php
namespace SolusiPress\Model\Table;

use Cake\ORM\Table as CakeTable;
use Cake\Utility\Inflector;

class WP_Table extends CakeTable {
    
    protected $prefix = null;
    protected $autoTable = true;
    
    public function initialize(array $config)
    {
        if( $this->autoTable ) {
            $this->auto_table();
        }
        parent::initialize($config);
        
    }    
    
    private function auto_table() {
        global $wpdb;
        $this->prefix = $wpdb->prefix;        
        $reflect = new \ReflectionClass( get_called_class() );
        $the_class = str_replace( 'Table', '', $reflect->getShortName() );
        $table_name = Inflector::underscore( $the_class );
        $this->setTable( $this->prefix . 'spb_' . $table_name );
        $this->setPrimaryKey( 'id' );        
    }

}