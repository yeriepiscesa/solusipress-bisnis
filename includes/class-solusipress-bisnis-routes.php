<?php
use Cake\Utility\Inflector;	
class SolusiPress_Routes {
	
	protected $controllerPath = null;
	protected $controller = null;
	protected $className = null;
	protected $returnToWp = false;
	protected $controllerObject = null;
	
	public function __construct( $controller=null, $path=null ) {
		
		$this->controller = $controller;
		$this->controllerPath = $path;

	}
	
	public function dispatch() {
		
		$cls = 'SolusiPress\\Controller\\'.Inflector::camelize( $this->controller ) . 'Controller';
		if( class_exists( $cls ) ) {
			$this->className = $cls;
			add_action( 'init', [ $this, 'handle_route' ], 1, 0 );
		} else {
			$this->returnToWp = true;
		}
		add_filter( 'template_include', [ $this, 'template_include' ] );
		
	}
	
	public function handle_route() {
		
        if( !defined( 'DS' ) ) {
		    define('DS', DIRECTORY_SEPARATOR);
        }
		define('BASE_PATH', dirname(__DIR__) . DS);
			
		$app            = System\App::instance();
		$app->request   = System\Request::instance();
		$app->route     = System\Route::instance($app->request);
		
		$cls = $this->className;
		$this->controllerObject = new $cls();
		$this->controllerObject->name = $this->controller;
		
		$route          = $app->route;
		$obj 			= $this->controllerObject;
		
		$route->before( '/', function() use( $obj ) {
			$obj->authorizeRequest();
		} );
		
        $path_prefix = '/';
        if( is_multisite() ) {
            if( defined( 'SUBDOMAIN_INSTALL' ) && !SUBDOMAIN_INSTALL ) {
                $path_prefix = get_blog_details()->path;
            }
        }
		$route->get( $path_prefix.'bisnis-api/'.$this->controller , [ $obj, 'index_action' ] );
		$route->get( $path_prefix.'bisnis-api/' . $this->controller . '/lookup', [ $obj, 'data_lookup' ] );
		$route->get( $path_prefix.'bisnis-api/'.$this->controller.'/?', [ $obj, 'get_action' ] );		
		$route->post( $path_prefix.'bisnis-api/'.$this->controller, [ $obj, 'insert_action' ] );
		$route->put_post( $path_prefix.'bisnis-api/'.$this->controller.'/?', [ $obj, 'update_action' ] );
		$route->get_put_post( $path_prefix.'bisnis-api/'.$this->controller.'/?/?', [ $obj, 'process_action' ] );
		$route->delete( $path_prefix.'bisnis-api/'.$this->controller.'/?', [ $obj, 'delete_action' ] );
		
		do_action( 'solusipress_bisnis_add_route', $app, $route, $this );
		
		$route->end();
		
	}
	
	public function template_include( $tmpl ) {
		if( !$this->returnToWp ) {
			$tmpl = false;
		}
		return $tmpl;
	}
}