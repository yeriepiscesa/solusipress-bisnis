<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://solusipress.com/
 * @since      1.0.0
 *
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Solusipress_Bisnis
 * @subpackage Solusipress_Bisnis/includes
 * @author     Yerie Piscesa <yerie@solusipress.com>
 */
class Solusipress_Bisnis_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'solusipress-bisnis',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
