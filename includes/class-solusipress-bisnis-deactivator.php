<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://solusipress.com/
 * @since      1.0.0
 *
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/includes
 * @author     Yerie Piscesa <yerie@solusipress.com>
 */
class Solusipress_Bisnis_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		self::remove_roles();
	}
	
	public static function remove_roles() {
		if( get_role('solusipress_admin_bisnis') ){
	        remove_role( 'solusipress_admin_bisnis' );
	    }	
	    $administrator = get_role( 'administrator' );
	    $administrator->remove_cap( 'manage_solusipress_bisnis' ); 			
	}

}
