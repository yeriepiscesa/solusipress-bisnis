<?php namespace SolusiPress\Model;

use SolusiPress\Model\Loader as ModelLoader;
use Cake\Database\Expression\QueryExpression;
use Cake\ORM\Query;
use Cake\ORM\Table;

class ListQuery 
{
    public static function setBase( Table $t, Array $options ) 
    {
        $q = $t->find( 'all' );
        
        if( isset( $options['sort'] ) ) {
            if( is_array( $options[ 'sort' ] ) ) {
                $q_orders = [];
                foreach( $options['sort'] as $fld => $dir ) {
                    $q_orders[ $fld ] = $dir;
                }
                $q->order( $q_orders );
            } else {
                $q->order( [ $options[ 'sort' ] => $options[ 'sort_dir' ] ] );
            }
        }
        
        if( isset( $options['limit'] ) && $options['limit'] > 0 ) {
            $q->limit( $options[ 'limit' ] );
        }
        
        $offset = 0;
        $page = 1;
        
        if( isset( $options[ 'conditions' ] ) && is_array( $options['conditions'] ) ) {
            $q->where( $options['conditions'] );
        }

        if ( isset( $options['page'] ) && ( isset( $options['limit'] ) && $options['limit'] > 0 ) ) {
            $pg = intval( $options[ 'page' ] );
            $offset = ( ( $pg-1 ) * $options['limit'] );
            $page = $options[ 'page' ];
        } 
        
        if( isset( $options['limit'] ) && $options['limit'] > 0 ) {
            $q->offset($offset);        
        }
        
        return $q;
        
    }
    
    public static function setReturn( Table $t, Query $q, Array $options, Array $data )
    {
        $total_page = 1;
        $count = $q->count();
        
        if( $options['limit'] > 0 )
            $total_page = ceil($count/$options['limit']);            
        
        $return = [
            'page' => $options['page'], 
            'record_count' => $count, 
            'total_page' => $total_page,
            'meta' => [
                'registryAlias' => $t->getRegistryAlias(),
                'alias' => $t->getAlias()
            ],
            'data' => $data
        ];        
        
        return $return;
    }    
    
    public static function provinces( $options=[] ) 
    {
        $defaults = [
            'page' => 1,
            'limit' => -1,
            'sort' => 'Provinces.id',
            'sort_dir' => 'ASC'
        ];
        $options = array_merge( $defaults, $options );
        $t = ModelLoader::get( 'Provinces' );
        $q = self::setBase( $t, $options );
        $rows = $q->all();
        if ($rows) {
            $data = []; $index = 0;
            foreach ($rows as $r) {
                array_push($data, [
                    'id' => $r->id,
                    'name' => $r->name,
                ] );
            }
            $return = self::setReturn( $t, $q, $options, $data ); 
        } else {
            $return = [];            
        }        
        return $return;          
    }
    
    public static function regencies( $options=[] )
    {
        $defaults = [
            'page' => 1,
            'limit' => -1,
            'sort' => 'Regencies.id',
            'sort_dir' => 'ASC'
        ];
        $options = array_merge( $defaults, $options );
        if( !isset( $options['province_id'] ) ) $options['province_id'] = '00';

        $t = ModelLoader::get( 'Regencies' );
        $q = self::setBase( $t, $options );        
        $q->where( [ 'Regencies.province_id' => $options['province_id'] ] );
        $rows = $q->all();        
        if ($rows) {
            $data = []; $index = 0;
            foreach ($rows as $r) {
                array_push($data, [
                    'id' => $r->id,
                    'name' => $r->name,
                ] );
            }
            $return = self::setReturn( $t, $q, $options, $data ); 
        } else {
            $return = [];            
        }        
        return $return;          
    }

    public static function districts( $options=[] )
    {
        $defaults = [
            'page' => 1,
            'limit' => -1,
            'sort' => 'Districts.id',
            'sort_dir' => 'ASC'
        ];
        $options = array_merge( $defaults, $options );
        if( !isset( $options['regency_id'] ) ) $options['regency_id'] = '0000';

        $t = ModelLoader::get( 'Districts' );
        $q = self::setBase( $t, $options );        
        $q->where( [ 'Districts.regency_id' => $options['regency_id'] ] );
        $rows = $q->all();        
        if ($rows) {
            $data = []; $index = 0;
            foreach ($rows as $r) {
                array_push($data, [
                    'id' => $r->id,
                    'name' => $r->name,
                ] );
            }
            $return = self::setReturn( $t, $q, $options, $data ); 
        } else {
            $return = [];            
        }        
        return $return;          
    }    

    public static function villages( $options=[] )
    {
        $defaults = [
            'page' => 1,
            'limit' => -1,
            'sort' => 'Villages.id',
            'sort_dir' => 'ASC'
        ];
        $options = array_merge( $defaults, $options );
        if( !isset( $options['district_id'] ) ) $options['district_id'] = '0000000';

        $t = ModelLoader::get( 'Villages' );
        $q = self::setBase( $t, $options );        
        $q->where( [ 'Villages.district_id' => $options['district_id'] ] );
        $rows = $q->all();        
        if ($rows) {
            $data = []; $index = 0;
            foreach ($rows as $r) {
                array_push($data, [
                    'id' => $r->id,
                    'name' => $r->name,
                ] );
            }
            $return = self::setReturn( $t, $q, $options, $data ); 
        } else {
            $return = [];            
        }        
        return $return;          
    }    
    
    public static function cash_accounts( $options=[], $show_name=false ) {
        
        $defaults = [
            'page' => 1,
            'limit' => -1,
            'sort' => 'Accounts.bank',
            'sort_dir' => 'ASC',
        ];
        
        $options = array_merge( $defaults, $options );
        $t = ModelLoader::get( 'Accounts' );
        $q = self::setBase( $t, $options );
        $rows = $q->all();
        if ($rows) {
            $data = []; $index = 0;
            foreach ($rows as $r) {
	            $acct = trim( $r->bank . ' ' . $r->account_number );
	            if( $show_name ) {
		            if( $r->account_name != '' ) {
		            	$acct .= ' a/n ' . $r->account_name;
		            }
	            }
                array_push($data, [
                    'id' => $r->id,
                    'text' => $acct,
                ] );
            }
            $return = self::setReturn( $t, $q, $options, $data ); 
        } else {
            $return = [];            
        }        
        return $return;          
        
    }
    
    public static function public_accounts( $show_name=true ) {
		return self::cash_accounts( [
			'conditions' => [
				'Accounts.public_account' => '1'
			]
		], $show_name );    
    }
    
    public static function contact_types( $options=[] ) {

        $defaults = [
            'page' => 1,
            'limit' => -1,
            'sort' => 'ContactTypes.ordering',
            'sort_dir' => 'ASC'
        ];
        
        $options = array_merge( $defaults, $options );
        $t = ModelLoader::get( 'ContactTypes' );
        $q = self::setBase( $t, $options );
        $rows = $q->all();
        if ($rows) {
            $data = []; $index = 0;
            foreach ($rows as $r) {
                array_push($data, [
                    'id' => $r->id,
                    'name' => $r->name,
                    'default' => $r->is_default,
                ] );
            }
            $return = self::setReturn( $t, $q, $options, $data ); 
        } else {
            $return = [];            
        }        
        return $return;          
	    
    }
}