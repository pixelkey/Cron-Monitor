<?php
/**
 * Cron Monitor
 *
 * @package       CRONMONITO
 * @author        Andrew Greirson
 * @license       gplv2
 * @version       1.0.1
 *
 * @wordpress-plugin
 * Plugin Name:   Cron Monitor
 * Plugin URI:    https://pixelkey.com
 * Description:   Provides a way to ping a cron monitoring service periodically to check the WordPress cron is functioning.
 * Version:       1.0.0
 * Author:        Andrew Greirson
 * Author URI:    https://www.pixelkey.com
 * Text Domain:   cron-monitor
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Cron Monitor. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'CRONMONITO_NAME',			'Cron Monitor' );

// Plugin version
define( 'CRONMONITO_VERSION',		'1.0.0' );

// Plugin Root File
define( 'CRONMONITO_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'CRONMONITO_PLUGIN_BASE',	plugin_basename( CRONMONITO_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'CRONMONITO_PLUGIN_DIR',	plugin_dir_path( CRONMONITO_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'CRONMONITO_PLUGIN_URL',	plugin_dir_url( CRONMONITO_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once CRONMONITO_PLUGIN_DIR . 'core/class-cron-monitor.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Andrew Greirson
 * @since   1.0.0
 * @return  object|Cron_Monitor
 */
function CRONMONITO() {
	return Cron_Monitor::instance();
}

CRONMONITO();
