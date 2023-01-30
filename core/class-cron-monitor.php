<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Cron_Monitor' ) ) :

	/**
	 * Main Cron_Monitor Class.
	 *
	 * @package		CRONMONITO
	 * @subpackage	Classes/Cron_Monitor
	 * @since		1.0.0
	 * @author		Andrew Greirson
	 */
	final class Cron_Monitor {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Cron_Monitor
		 */
		private static $instance;

		/**
		 * CRONMONITO helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Cron_Monitor_Helpers
		 */
		public $helpers;

		/**
		 * CRONMONITO settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Cron_Monitor_Settings
		 */
		public $settings;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'cron-monitor' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'cron-monitor' ), '1.0.0' );
		}

		/**
		 * Main Cron_Monitor Instance.
		 *
		 * Insures that only one instance of Cron_Monitor exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Cron_Monitor	The one true Cron_Monitor
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Cron_Monitor ) ) {
				self::$instance					= new Cron_Monitor;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Cron_Monitor_Helpers();
				self::$instance->settings		= new Cron_Monitor_Settings();

				//Fire the plugin logic
				new Cron_Monitor_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'CRONMONITO/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function includes() {
			require_once CRONMONITO_PLUGIN_DIR . 'core/includes/classes/class-cron-monitor-helpers.php';
			require_once CRONMONITO_PLUGIN_DIR . 'core/includes/classes/class-cron-monitor-settings.php';

			require_once CRONMONITO_PLUGIN_DIR . 'core/includes/classes/class-cron-monitor-run.php';
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'cron-monitor', FALSE, dirname( plugin_basename( CRONMONITO_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.