<?php
use SolusiPress\Model\ListQuery;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://solusipress.com/
 * @since      1.0.0
 *
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/admin
 * @author     Yerie Piscesa <yerie@solusipress.com>
 */
class Solusipress_Bisnis_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Solusipress_Bisnis_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Solusipress_Bisnis_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/solusipress-bisnis-admin.css', array(), $this->version, 'all' );
        wp_register_style( 'jquery-confirm', plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/css/jquery-confirm.min.css' );
		wp_register_style( 'datatables_style', plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/css/bootstrap.datatables.min.css' );        		
		wp_register_style( 'knockout-autocomplete', plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/css/knockout.autocomplete.css' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Solusipress_Bisnis_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Solusipress_Bisnis_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

        wp_register_script( 'knockout', plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/js/knockout.js' );
        wp_register_script( 'knockout-autocomplete', plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/js/knockout.autocomplete.js' );
        wp_register_script( 'knockout-select2', plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/js/knockout.select2.js' );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/solusipress-bisnis-admin.js', array( 'jquery' ), $this->version, false );

        wp_register_script( 'jquery-commonlib', plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/js/commonlib.min.js', array( 'jquery' ) );
        wp_register_script( 'jquery-confirm', plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/js/jquery-confirm.min.js', array( 'jquery' ) );
        wp_register_script( 'datatables', plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/js/bootstrap.datatables.min.js', array('jquery'), null, true);
        wp_register_script( 'jquery-number', plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/js/jquery.number.js', array( 'jquery' ) );
		
        wp_register_script( 'cashflow', plugin_dir_url( __FILE__ ) . 'js/cashflow.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'bank', plugin_dir_url( __FILE__ ) . 'js/bank.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'contact-type', plugin_dir_url( __FILE__ ) . 'js/contact-type.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'contact', plugin_dir_url( __FILE__ ) . 'js/contact.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'messages', plugin_dir_url( __FILE__ ) . 'js/messages.js', array( 'jquery' ), $this->version, true );
        wp_register_script( 'debts', plugin_dir_url( __FILE__ ) . 'js/debts.js', array( 'jquery' ), $this->version, true );

	}
	
	private function load_crud_scripts( $handle, $ajax_action='/', $_data=[] ) {
        $defaults = [
            'ajax_action' => home_url( $ajax_action ),
        ];
        $data = array_merge( $defaults, $_data );
        
        wp_enqueue_script( 'underscore' );
        wp_enqueue_script( 'knockout' );
        wp_enqueue_script( 'datatables' );
        wp_enqueue_script( 'jquery-confirm' );
        wp_enqueue_script( 'jquery-commonlib' );
        wp_enqueue_script( $this->plugin_name );        
        
        wp_localize_script( $handle, 'solusipress', $data );
        wp_enqueue_script( $handle ); 
               
        wp_enqueue_style( 'datatables_style' );
        wp_enqueue_style( 'jquery-confirm' );                
        wp_enqueue_style( $this->plugin_name );        
	}
	
	public function create_menu() {
		
        add_menu_page( 
            'SolusiPress Bisnis', 'Bisnis', 
            'manage_options', 
            'solusipress', array( $this, 'solusipress_home' ), 
            'dashicons-chart-bar', 3 );
		add_submenu_page( 'solusipress', 'Kas / Bank', 'Kas / Bank', 'manage_options', 'solusipress', array( $this, 'bank' ) );           
		add_submenu_page( 'solusipress', 'Kas Masuk/Keluar', 'Kas Masuk/Keluar', 'manage_options', 'cashflow', array( $this, 'cashflow' ) );           
		add_submenu_page( 'solusipress', 'Transaksi Hutang', 'Hutang', 'manage_options', 'debts_dr', array( $this, 'debts_dr' ) );           
		add_submenu_page( 'solusipress', 'Transaksi Piutang', 'Piutang', 'manage_options', 'debts_cr', array( $this, 'debts_cr' ) );           

		add_menu_page(
			'Kontak', 'Kontak',
			'manage_options',
			'sp-kontak', array( $this, 'contact_type' ),
			'dashicons-groups', 3
		);
		add_submenu_page( 'sp-kontak', 'Kategori Kontak', 'Kategori', 'manage_options', 'sp-kontak', array( $this, 'contact_type' ) );
		add_submenu_page( 'sp-kontak', 'Data Kontak', 'Data Kontak', 'manage_options', 'sp-contact-data', array( $this, 'contact_data' ) );
		add_submenu_page( 'sp-kontak', 'Pesan', 'Pesan', 'manage_options', 'sp-contact-message', array( $this, 'contact_messages' ) );
		add_submenu_page( 'sp-kontak', 'Pengaturan Kontak', 'Pengaturan', 'manage_options', 'sp-contact-settings', array( $this, 'contact_settings' ) );
	}
	
	public function solusipress_home() {
		include 'partials/solusipress-bisnis-admin-display.php';
	}
	
	public function bank() {
        $this->load_crud_scripts( 'bank', '/bisnis-api/accounts/' );
		require 'partials/bank-crud.php';
	}
	
	public function cashflow() {
        $list_accounts = ListQuery::cash_accounts();
        $this->load_crud_scripts( 'cashflow', '/bisnis-api/cash_flows/', [
        	'accounts' => $list_accounts['data'], 
        	'transfer_action' =>  home_url( '/bisnis-api/transfers/' ),
        	'contact_lookup' => home_url( '/bisnis-api/contacts/lookup/' ),
        ] );
        $url = plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/css/jquery.ui.min.css';
        wp_enqueue_style('jquery-ui-style', $url, false, '1.12.1');
        wp_enqueue_script( 'jquery-ui-datepicker' );
		require 'partials/cashflow.php';
	}
	
	// hutang
	public function debts_dr() {
		$trx_name = 'Hutang';
		$contact_heading = 'Diterima dari';
        $list_accounts = ListQuery::cash_accounts();
        $this->load_crud_scripts( 'debts', '/bisnis-api/debts/', [
	        'dc' => 'd',
	        'label' => $trx_name,
        	'accounts' => $list_accounts['data'], 
        	'contact_lookup' => home_url( '/bisnis-api/contacts/lookup/' ),
        	'payment_action' => home_url( '/bisnis-api/debt_payments/' ),
        ] );
        $url = plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/css/jquery.ui.min.css';
        wp_enqueue_style('jquery-ui-style', $url, false, '1.12.1');
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-number' );
		require 'partials/debts.php';
	}
	
	// piutang
	public function debts_cr() {
		$trx_name = 'Piutang';
		$contact_heading = 'Diberikan kepada';
        $list_accounts = ListQuery::cash_accounts();
        $this->load_crud_scripts( 'debts', '/bisnis-api/debts/', [
	        'dc' => 'c',
	        'label' => $trx_name,
        	'accounts' => $list_accounts['data'], 
        	'contact_lookup' => home_url( '/bisnis-api/contacts/lookup/' ),
        	'payment_action' => home_url( '/bisnis-api/debt_payments/' ),
        ] );
        $url = plugin_dir_url( dirname( __FILE__ ) ) . 'includes/assets/css/jquery.ui.min.css';
        wp_enqueue_style('jquery-ui-style', $url, false, '1.12.1');
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-number' );
		require 'partials/debts.php';
	}
	
	public function contact_type() {
        $this->load_crud_scripts( 'contact-type', '/bisnis-api/contact_types/' );
		require 'partials/contact-type.php';
	}
	
	public function contact_data() {
		$contact_types = ListQuery::contact_types();
        $this->load_crud_scripts( 'contact', '/bisnis-api/contacts/', [
	        'contact_types' => $contact_types
        ] );
		require 'partials/contact.php';
	}
	
	public function contact_messages() {
		$contact_types = ListQuery::contact_types();
        $this->load_crud_scripts( 'messages', '/bisnis-api/contact_messages/' );
		require 'partials/messages.php';
	}
	
	public function contact_settings() {
		
        $form = new Gregwar\Formidable\Form( plugin_dir_path( __FILE__ ) . 'partials/contact-settings.php' );
        $page = $this;
        
        $form->recaptcha_site_key = get_option( 'spb_recaptcha_site_key', '' );
        $form->recaptcha_secret_key = get_option( 'spb_recaptcha_secret_key', '' );

        $form->notification_email = get_option( 'spb_contact_email_notif', '' );
        $form->notification_whatsapp = get_option( 'spb_contact_whatsapp_notif', '' );
        
        $form->activate_recaptcha = get_option( 'spb_activate_recaptcha', '0' );
        
        $form->handle(function() use ( $form, $page ) {
	        update_option( 'spb_recaptcha_site_key', $form->recaptcha_site_key );
	        update_option( 'spb_recaptcha_secret_key', $form->recaptcha_secret_key );
	        
	        update_option( 'spb_contact_email_notif', $form->notification_email );
	        update_option( 'spb_contact_whatsapp_notif', $form->notification_whatsapp );
			
			$_activate_recaptcha = '0';
			if( $form->activate_recaptcha == '1' ) {
				$_activate_recaptcha = '1';	
			}
	        update_option( 'spb_activate_recaptcha', $_activate_recaptcha );
	    } );
		
		echo $form;
	}

}
