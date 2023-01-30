<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Cron_Monitor_Settings
 *
 * This class contains all of the plugin settings.
 * Here you can configure the whole plugin data.
 *
 * @package		CRONMONITO
 * @subpackage	Classes/Cron_Monitor_Settings
 * @author		Andrew Greirson
 * @since		1.0.0
 */
class Cron_Monitor_Settings{

	/**
	 * The plugin name
	 *
	 * @var		string
	 * @since   1.0.0
	 */
	private $plugin_name;

	/**
	 * The plugin capabilities
	 *
	 * @var		array
	 * @since	1.0.0
	 */
	private $capabilities;

	/**
	 * Our Cron_Monitor_Settings constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct(){

		$this->plugin_name = CRONMONITO_NAME;
		$this->capabilities = array(
			'default' => 'manage_options',
		);
	}

	/**
	 * ######################
	 * ###
	 * #### CALLABLE FUNCTIONS
	 * ###
	 * ######################
	 */

	/**
	 * Return the plugin name
	 *
	 * @access	public
	 * @since	1.0.0
	 * @return	string The plugin name
	 */
	public function get_plugin_name(){
		return apply_filters( 'CRONMONITO/settings/get_plugin_name', $this->plugin_name );
	}

	/**
	 * Return the specified plugin capability
	 *
	 * @access	public
	 * @since	1.0.0
	 * @return	string The chosen capability
	 */
	public function get_capability( $identifier = 'default' ) {

		$capability = $this->capabilities[ 'default' ];
		if( ! empty( $identifier ) && isset( $this->capabilities[ $identifier ] ) ){
			$capability = $this->capabilities[ $identifier ];
		}

		return apply_filters( 'CRONMONITO/settings/get_capability', $capability, $identifier, $this->capabilities );
	}

}
