<?php
namespace SolusiPress\Model;
use Cake\ORM\Locator\TableLocator;

class Loader {
    
    static $locator = null;
    
    public static function get( $model ){
        if( is_null( self::$locator ) ) {
            self::$locator = new TableLocator();
        }
        return self::$locator->get( $model, array(
            'className' => "SolusiPress\\Model\\Table\\" . $model . 'Table'
        ) ); 
    }
    
}