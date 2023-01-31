<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * Class Cron_Monitor_Run
 *
 * Thats where we bring the plugin to life
 *
 * @package		CRONMONITO
 * @subpackage	Classes/Cron_Monitor_Run
 * @author		Andrew Greirson
 * @since		1.0.0
 */
class Cron_Monitor_Run
{

	/**
	 * Our Cron_Monitor_Run constructor 
	 * to run the plugin logic.
	 *
	 * @since 1.0.0
	 */
	function __construct()
	{
		$this->add_hooks();
	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOKS
	 * ###
	 * ######################
	 */

	/**
	 * Registers all WordPress and plugin related hooks
	 *
	 * @access	private
	 * @since	1.0.0
	 * @return	void
	 */
	private function add_hooks()
	{
		// Add the cron job
		add_action('cron_monitor_ping', array($this, 'run_ping_url'));

		// Only run if Admin
		if (!is_admin()) {
			return;
		}

		add_action('admin_menu', array($this, 'cron_monitor_admin_menu'), 20);
		register_activation_hook(CRONMONITO_PLUGIN_FILE, array($this, 'activation_hook_callback'));
		register_deactivation_hook(CRONMONITO_PLUGIN_FILE, array($this, 'deactivation_hook_callback'));

		// Initialize the settings page
		add_action('admin_init', array($this, 'cron_monitor_settings_init'));

		// Update the cron job if the interval or ping url changes
		add_action('update_option_cron_monitor_setting_cron_interval', array($this, 'update_cron_job'), 10, 3);
		add_action('update_option_cron_monitor_setting_ping_url', array($this, 'update_cron_job'), 10, 3);

	}

	/**
	 * ######################
	 * ###
	 * #### WORDPRESS HOOK CALLBACKS
	 * ###
	 * ######################
	 */


	/**
	 * Add custom menu pages
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function cron_monitor_admin_menu()
	{
		// Also found in the settings menu as a sub menu
		add_options_page(
			__('Cron Monitor', 'cron-monitor-textdomain'),
			__('Cron Monitor', 'cron-monitor-textdomain'),
			'manage_options',
			'cron-monitor-page',
			array($this, 'cron_monitor_admin_page_contents')
		);
	}

	/**
	 * Add custom menu page content for the following
	 * menu item: custom-menu-slug
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function cron_monitor_admin_page_contents()
	{
?>
		<h1> <?php esc_html_e('Cron Monitor', 'cron-monitor-plugin-textdomain'); ?> </h1>
		<form method="POST" action="options.php">
			<?php
			settings_fields('cron-monitor-page');
			do_settings_sections('cron-monitor-page');
			submit_button();
			?>
		</form>
	<?php
	}


	/**
	 * Cron Monitor Settings Init
	 * 
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function cron_monitor_settings_init()
	{

		add_settings_section(
			'cron_monitor_page_setting_section',
			__('Cron Monitor Ping Settings', 'cron-monitor-textdomain'),
			array($this, 'cron_monitor_setting_section_callback_function'),
			'cron-monitor-page'
		);

		add_settings_field(
			'cron_monitor_setting_ping_url',
			__('Ping URL', 'cron-monitor-textdomain'),
			array($this, 'cron_monitor_setting_markup'),
			'cron-monitor-page',
			'cron_monitor_page_setting_section'
		);

		// Add settings select field for cron recurrance interval
		add_settings_field(
			'cron_monitor_setting_cron_interval',
			__('Cron Interval', 'cron-monitor-textdomain'),
			array($this, 'cron_monitor_setting_cron_interval_markup'),
			'cron-monitor-page',
			'cron_monitor_page_setting_section'
		);


		register_setting('cron-monitor-page', 'cron_monitor_setting_ping_url');
		register_setting('cron-monitor-page', 'cron_monitor_setting_cron_interval');
	}


	function cron_monitor_setting_section_callback_function()
	{
		echo '<p>Enter your ping url for your third-party heartbeat monitor service. This url will be pinged each time the cron has successfully run.</p>';
	}


	function cron_monitor_setting_markup()
	{
	?>
		<input type="text" id="cron_monitor_setting_ping_url" name="cron_monitor_setting_ping_url" value="<?php echo get_option('cron_monitor_setting_ping_url'); ?>">
	<?php
	}

	function cron_monitor_setting_cron_interval_markup()
	{
		$selected = get_option('cron_monitor_setting_cron_interval');

		// Create an array of the default cronn intervals
		$cron_intervals = wp_get_schedules();



		// Include a disabled option and set it as the first option
		$disabled_option = array(
			'display' => __('Select an interval', 'cron-monitor-textdomain'),
			'interval' => 0
		);

		// Add the disabled option to the beginning of the cron intervals array
		array_unshift($cron_intervals, $disabled_option);

		// If selected is not in the list of cron intervals, add it to the list
		if (!array_key_exists($selected, $cron_intervals)) {
			$cron_intervals[$selected] = array(
				'display' => __('Custom', 'cron-monitor-textdomain'),
				'interval' => $selected
			);
		}

		
		// Loop through the cron intervals and add them to the select field
		echo '<select name="cron_monitor_setting_cron_interval" id="cron_monitor_setting_cron_interval">';

		foreach ($cron_intervals as $key => $value) {
			$selected_attr = ($key == $selected) ? 'selected' : '';
			echo '<option value="' . $key . '" ' . $selected_attr . '>' . $value['display'] . ' [' . $key . ']' . '</option>';
		}

		echo '</select>';

	?>

<?php
	}




	/**
	 * ####################
	 * ### Activation/Deactivation hooks
	 * ####################
	 */

	/*
	 * This function is called on activation of the plugin
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function activation_hook_callback()
	{
		$this->custom_log('Plugin has been activated. Cron job added.');
		$this->add_cron_job();
	}

	/*
	 * This function is called on deactivation of the plugin
	 *
	 * @access	public
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	public function deactivation_hook_callback()
	{
		// Remove the cron job
		$this->remove_cron_job();
		$this->custom_log('Plugin has been dectivated. Cron job removed.');
	}

	/**
	 * ####################
	 * ### Cron Job
	 * ####################
	 */
	public function add_cron_job()
	{

		// Get the cron interval
		$cron_interval = get_option('cron_monitor_setting_cron_interval');
		$ping_url = get_option('cron_monitor_setting_ping_url');

		$this->custom_log('Cron interval: ' . $cron_interval);
		$this->custom_log('Ping url: ' . $ping_url);

		// Return if cron interval is disabled
		if ($cron_interval === '0') {
			$this->custom_log('Cron interval disabled due to select option');
			$this->remove_cron_job();
			return;
		}

		// Return if ping url is empty
		if (!$ping_url || $ping_url == '') {
			$this->custom_log('Cron interval disabled due to empty ping url');
			$this->remove_cron_job();
			return;
		}


		// Create the cron job
		if (!wp_next_scheduled('cron_monitor_ping')) {
			wp_schedule_event(time(), $cron_interval, 'cron_monitor_ping');
			$this->custom_log('Cron job added/updated');
		}
	}

	public function remove_cron_job()
	{
		$this->custom_log('Cron job removed');
		// Remove the cron job
		wp_clear_scheduled_hook('cron_monitor_ping');
	}

	// Update the cron job if ping url or cron interval is changed
	public function update_cron_job($old_value, $new_value)
	{

		// Return if the old value is the same as the new value
		if ($old_value == $new_value) {
			return;
		}

		// Remove the cron job
		$this->remove_cron_job();

		// Add the cron job
		$this->add_cron_job();

		$this->custom_log('Cron job update called');
	}


	// Add action to cron hook
	public function run_ping_url()
	{
		// Get the ping url
		$ping_url = get_option('cron_monitor_setting_ping_url');

		// Return if ping url is empty
		if (!$ping_url) {
			return;
		}
		
		$this->custom_log('URL has been pinged');

		// Ping the url
		$response = wp_remote_get($ping_url);

		return $response;
	}

	// Write to log custom log file in wp-content
	public function custom_log($data)
	{
		// // Only log if debug is enabled
		// if ( defined('WP_DEBUG') && true === WP_DEBUG) {
		// 	return;
		// }
		
		// // Get the log file path
		// $log_file_path = WP_CONTENT_DIR . '/cron-monitor.log';

		// // Get the current time
		// $current_time = date('Y-m-d H:i:s');

		// // Write to log file
		// file_put_contents($log_file_path, $current_time . ' - ' . $data . PHP_EOL, FILE_APPEND);
	}

}
