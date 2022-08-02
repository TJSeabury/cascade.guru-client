<?php

namespace Tjseabury\CascadeGuru;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also src all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://cascade.guru
 * @since             0.1.0
 * @package           CASCADE_GURU
 *
 * @wordpress-plugin
 * Plugin Name:       Cascade Guru
 * Plugin URI:        http://cascade.guru/
 * Description:       This is the Cascade.Guru client Wordpress plugin. It handles talking to the API and forcing Wordpress to serve the optimized per-page css.
 * Version:           0.1.0
 * Author:            Tyler Seabury
 * Author URI:        http://tylerseabury.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cascade-guru
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

/**
 * Currently plugin version.
 */
define('CASCADE_GURU_VERSION', '0.1.0');

/**
 * API endpoint
 */
define('CASCADE_GURU_API_ENDPOINT', 'https://cascade.guru/api/service/optimize');

require __DIR__ . '/vendor/autoload.php';

$myUpdateChecker = \Puc_v4_Factory::buildUpdateChecker(
  'https://github.com/TJSeabury/cascade.guru-client',
  __FILE__, //Full path to the main plugin file or functions.php.
  'cascade-guru'
);

//Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');

/**
 * The code that runs during plugin activation.
 * This action is documented in src/class-cascade-guru-activator.php
 */
function activate_cascade_guru()
{
  \Tjseabury\CascadeGuru\src\CascadeGuruActivator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in src/class-cascade-guru-deactivator.php
 */
function deactivate_cascade_guru()
{
  \Tjseabury\CascadeGuru\src\CascadeGuruDeactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_cascade_guru');
register_deactivation_hook(__FILE__, 'deactivate_cascade_guru');

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cascade_guru()
{

  /**
   * The core plugin class that is used to define internationalization,
   * admin-specific hooks, and public-facing site hooks.
   */
  $plugin = new \Tjseabury\CascadeGuru\src\CascadeGuru();
  $plugin->run();
}
run_cascade_guru();
