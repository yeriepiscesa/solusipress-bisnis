<?php
use Cake\Datasource\ConnectionManager;
use Cake\Cache\Engine\FileEngine;
use Cake\Cache\Cache;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://solusipress.com/
 * @since      1.0.0
 *
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/includes
 * @author     Yerie Piscesa <yerie@solusipress.com>
 */
class Solusipress_Bisnis {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Solusipress_Bisnis_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SOLUSIPRESS_BISNIS_VERSION' ) ) {
			$this->version = SOLUSIPRESS_BISNIS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'solusipress-bisnis';
		
		$this->datasource_config();	
		$this->dispatch_url();		
		$this->setup_rewrite_rule();

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

    private function datasource_config() {
        
        ConnectionManager::setConfig('default', [
            'className' => 'Cake\Database\Connection',
            'driver' => 'Cake\Database\Driver\Mysql',
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASSWORD,
            'cacheMetadata' => true,
            'quoteIdentifiers' => false,
        ]);
        
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        
        $cacheConfig = [
            'className' => FileEngine::class,
            'duration' => '+1 year',
            'serialize' => true,
            'prefix'    => 'orm_',
            'path'      => $upload_dir . '/solusipress-temp/cache/model'
        ];
        Cache::setConfig('_cake_model_', $cacheConfig);

        $cacheConfig = [
            'className' => FileEngine::class,
            'duration' => '+1 year',
            'serialize' => true,
            'prefix'    => 'core_',
            'path'      => $upload_dir . '/solusipress-temp/cache/core'
        ];
        Cache::setConfig('_cake_core_', $cacheConfig); 
        
    }	

	
	private function dispatch_url() {
		$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
                "https" : "http") . "://" . $_SERVER['HTTP_HOST'] .  
                $_SERVER['REQUEST_URI']; 
		$url_parsed = wp_parse_url( $url );		
		$path = [];
		if( isset( $url_parsed['path'] ) && $url_parsed['path'] != '/' ) {
			$_paths = explode( '/', $url_parsed['path'] );
			foreach( $_paths as $p ) {
				if( $p != '' ) { array_push( $path, $p ); }
			}
		}
        if( is_multisite() ) {
            if( defined( 'SUBDOMAIN_INSTALL' ) && !SUBDOMAIN_INSTALL ) {
                unset( $path[0] );
                $path = array_values( $path );
            }
        }
		if( !empty( $path ) && $path[0] != 'wp-admin' ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-solusipress-bisnis-routes.php';
			if( isset( $path[1] ) ) {
				$controller = $path[1];
				$router = new SolusiPress_Routes( $controller, $path );
				$router->dispatch();
			}
		}
	}
	
	private function setup_rewrite_rule() {	
	    $p = get_option( 'solusipress_bisnis_public_page' );
	    if( $p ) {
		    $_post = get_post( $p );
		    $slug = $_post->post_name;    
		    add_rewrite_tag( '%action_page%', '([A-Za-z0-9\-\_]+)' );
		    add_rewrite_tag( '%action_id%', '([A-Za-z0-9\-\_]+)' );    
		    add_rewrite_rule( '^'.$slug.'/([A-Za-z0-9\-\_]+)$','index.php?pagename='.$slug.'&action_page=$matches[1]', 'top' );                
		    add_rewrite_rule( '^'.$slug.'/([A-Za-z0-9\-\_]+)/([A-Za-z0-9\-\_]+)$','index.php?pagename='.$slug.'&action_page=$matches[1]&action_id=$matches[2]', 'top' );
	    }
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Solusipress_Bisnis_Loader. Orchestrates the hooks of the plugin.
	 * - Solusipress_Bisnis_i18n. Defines internationalization functionality.
	 * - Solusipress_Bisnis_Admin. Defines all hooks for the admin area.
	 * - Solusipress_Bisnis_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-solusipress-bisnis-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-solusipress-bisnis-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-solusipress-bisnis-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-solusipress-bisnis-public.php';
		
		$this->loader = new Solusipress_Bisnis_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Solusipress_Bisnis_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Solusipress_Bisnis_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Solusipress_Bisnis_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'create_menu' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Solusipress_Bisnis_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		$this->loader->add_action( 'template_redirect', $plugin_public, 'redirect_after_submit_contact' );
		add_shortcode( 'solusipress_bisnis_contact_form', array( $plugin_public, 'contact_form' ) );
		add_shortcode( 'solusipress_bisnis_bank_accounts', array( $plugin_public, 'public_bank_account' ) );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Solusipress_Bisnis_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}