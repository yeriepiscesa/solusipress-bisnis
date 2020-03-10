<?php

/**
 * Fired during plugin activation
 *
 * @link       https://solusipress.com/
 * @since      1.0.0
 *
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/includes
 * @author     Yerie Piscesa <yerie@solusipress.com>
 */
class Solusipress_Bisnis_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate( $network_wide ) {
		
	    if ( is_multisite() && $network_wide ) {
		    global $wpdb;
	        // Get all blogs in the network and activate plugin on each one
	        $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
	        foreach ( $blog_ids as $blog_id ) {
	            switch_to_blog( $blog_id );
				self::do_activation();		
	            restore_current_blog();
	        }
	    } else {
			self::do_activation();		
	    }		
	    
	}

	public static function delete_files( $files ) {
		foreach( $files as $file ){ // iterate files
			if( is_file( $file ) ) {
				unlink( $file ); // delete file
			}
		}		
	}
	
	public static function do_activation() {
        $upload = wp_upload_dir();
        $upload_dir = $upload['basedir'];
        $upload_dir = $upload_dir . '/solusipress-temp';
        if ( ! is_dir( $upload_dir ) ) {
            mkdir( $upload_dir, 0775 );   
            $cache_dir = $upload_dir . '/cache';
            if( ! is_dir( $cache_dir ) ) {
                mkdir( $cache_dir, 0775 );
                $cache_core_dir = $cache_dir . '/core';
                if( ! is_dir( $cache_core_dir ) ) {
                    mkdir( $cache_core_dir, 0775 );
                }
                $cache_model_dir = $cache_dir . '/model';
                if( ! is_dir( $cache_model_dir ) ) {
                    mkdir( $cache_model_dir, 0775 );
                }
            }
        }
        
        $current_db_version = '1.0.0';
        $db_version = get_option( 'solusipress_database_version' );
             
        if( $db_version == '' ) {
	        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');        
            self::setup_database();
        }
        
        // clear model cache
		$files = glob(  $upload_dir  . '/cache/core/*' );        
		self::delete_files( $files );
		$files = glob(  $upload_dir  . '/cache/model/*' );        
		self::delete_files( $files );

        update_option( 'solusipress_database_version', $current_db_version );        
		
	}
    
    public static function setup_database() {
	    
        global $wpdb;
        
        $sql  = "";
        $sql .= "CREATE TABLE `{$wpdb->prefix}spb_accounts` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `bank` varchar(100) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `account_number` varchar(50) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `account_name` varchar(100) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `public_account` char(1) COLLATE {$wpdb->collate} DEFAULT '0',
			  `logo_url` varchar(150) COLLATE {$wpdb->collate} DEFAULT NULL,			  
			  `description` varchar(200) COLLATE {$wpdb->collate} DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB COLLATE={$wpdb->collate}; ";
			
		$sql .= "CREATE TABLE `{$wpdb->prefix}spb_cash_flows` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `account_id` int(11) DEFAULT NULL,
			  `trx_no` varchar(100) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `trx_date` date DEFAULT NULL,
			  `contact_id` bigint(20) DEFAULT NULL,
			  `from_to_name` varchar(150) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `organization` varchar(150) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `dc` char(1) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `amount` float DEFAULT NULL,
			  `post_id` bigint(20) DEFAULT NULL,
			  `object_model` varchar(100) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `object_id` bigint(20) DEFAULT NULL,
			  `note` varchar(250) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `last_update` datetime DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB COLLATE={$wpdb->collate}; ";	
			
		$sql .= "CREATE TABLE `{$wpdb->prefix}spb_contact_types` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `name` varchar(100) COLLATE {$wpdb->collate} NOT NULL,
			  `is_default` char(1) COLLATE {$wpdb->collate} DEFAULT '0',
			  `color` varchar(10) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `ordering` int(11) DEFAULT NULL,
			  `description` varchar(150) COLLATE {$wpdb->collate} DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB COLLATE={$wpdb->collate}; ";	
			
		$sql .= "CREATE TABLE `{$wpdb->prefix}spb_contacts` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `first_name` varchar(50) COLLATE {$wpdb->collate} NOT NULL,
			  `last_name` varchar(50) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `contact_type_id` int(11) DEFAULT NULL,
			  `organization` varchar(200) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `email` varchar(200) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `phone` varchar(15) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `note` varchar(200) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `wp_user_id` bigint(20) DEFAULT NULL,
			  `whatsapp` varchar(15) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `instagram` varchar(100) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `twitter` varchar(100) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `facebook` varchar(100) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `linkedin` varchar(100) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `last_update` datetime DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB COLLATE={$wpdb->collate}; ";

		$sql .= "CREATE TABLE `{$wpdb->prefix}spb_contact_messages` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `parent_id` bigint(20) DEFAULT NULL,
			  `lft` bigint(20) DEFAULT NULL,
			  `rght` bigint(20) DEFAULT NULL,
			  `contact_id` bigint(20) NOT NULL,
			  `msg_date` datetime DEFAULT NULL,
			  `msg_subject` varchar(150) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `msg_text` text COLLATE {$wpdb->collate},
			  `dtm_read` datetime DEFAULT NULL,
			  `dtm_followup` datetime DEFAULT NULL,
			  `followup_by` bigint(20) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB COLLATE={$wpdb->collate}; ";
			
		$sql .= "CREATE TABLE `{$wpdb->prefix}spb_transfers` (
			  `id` bigint(20) NOT NULL AUTO_INCREMENT,
			  `src_account` char(13) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `dst_account` char(13) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `trf_date` date DEFAULT NULL,
			  `amount` float DEFAULT NULL,
			  `note` varchar(250) COLLATE {$wpdb->collate} DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB COLLATE={$wpdb->collate}; ";	
			
		$sql .= "CREATE TABLE `{$wpdb->prefix}spb_debts` (
			  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `contact_id` bigint(20) DEFAULT NULL,
			  `account_id` int(11) DEFAULT NULL,
			  `dc` char(1) COLLATE {$wpdb->collate} DEFAULT NULL COMMENT 'D=Debet/Hutang, C=Credit/Piutang',
			  `trx_date` date DEFAULT NULL,
			  `amount` float DEFAULT NULL,
			  `due_date` date DEFAULT NULL,
			  `installments` smallint(6) DEFAULT NULL,
			  `total_paid` float DEFAULT NULL,
			  `last_paid` date DEFAULT NULL,
			  `fullpaid_date` date DEFAULT NULL,
			  `ref_number` varchar(50) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `note` varchar(250) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `first_created` datetime DEFAULT NULL,
			  `last_updated` datetime DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB COLLATE={$wpdb->collate}; ";	
			
		$sql .= "CREATE TABLE `{$wpdb->prefix}spb_debt_payments` (
			  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			  `debt_id` bigint(20) DEFAULT NULL,
			  `trx_date` date DEFAULT NULL,
			  `account_id` int(11) DEFAULT NULL,
			  `amount` float DEFAULT NULL,
			  `instll_no` smallint(6) DEFAULT NULL,
			  `ref_number` varchar(50) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `note` varchar(250) COLLATE {$wpdb->collate} DEFAULT NULL,
			  `first_created` datetime DEFAULT NULL,
			  `last_updated` datetime DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB COLLATE {$wpdb->collate};";	
			
        dbDelta( $sql );    
        
	}

}