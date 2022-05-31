<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    cascade_guru
 * @subpackage cascade_guru/includes
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
 * @package    cascade_guru
 * @subpackage cascade_guru/includes
 * @author     Your Name <email@example.com>
 */
class cascade_guru
{

  /**
   * The loader that's responsible for maintaining and registering all hooks that power
   * the plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      cascade_guru_Loader    $loader    Maintains and registers all hooks for the plugin.
   */
  protected $loader;

  /**
   * The unique identifier of this plugin.
   *
   * @since    1.0.0
   * @access   protected
   * @var      string    $cascade_guru    The string used to uniquely identify this plugin.
   */
  protected $cascade_guru;

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
  public function __construct()
  {
    if (defined('CASCADE_GURU_VERSION')) {
      $this->version = CASCADE_GURU_VERSION;
    } else {
      $this->version = '1.0.0';
    }
    $this->cascade_guru = 'cascade-guru';

    $this->load_dependencies();
    $this->define_admin_hooks();
    $this->define_public_hooks();
  }

  /**
   * Load the required dependencies for this plugin.
   *
   * Include the following files that make up the plugin:
   *
   * - cascade_guru_Loader. Orchestrates the hooks of the plugin.
   * - cascade_guru_i18n. Defines internationalization functionality.
   * - cascade_guru_Admin. Defines all hooks for the admin area.
   * - cascade_guru_Public. Defines all hooks for the public side of the site.
   *
   * Create an instance of the loader which will be used to register the hooks
   * with WordPress.
   *
   * @since    1.0.0
   * @access   private
   */
  private function load_dependencies()
  {

    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-cascade-guru-loader.php';

    /**
     * The class responsible for defining all actions that occur in the admin area.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-cascade-guru-admin.php';

    /**
     * The class responsible for defining all actions that occur in the public-facing
     * side of the site.
     */
    require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-cascade-guru-public.php';

    $this->loader = new cascade_guru_Loader();
  }

  /**
   * Register all of the hooks related to the admin area functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_admin_hooks()
  {

    $plugin_admin = new cascade_guru_Admin($this->get_cascade_guru(), $this->get_version());

    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
  }

  /**
   * Register all of the hooks related to the public-facing functionality
   * of the plugin.
   *
   * @since    1.0.0
   * @access   private
   */
  private function define_public_hooks()
  {

    $plugin_public = new cascade_guru_Public($this->get_cascade_guru(), $this->get_version());

    $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
    $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    $this->loader->add_filter('query_vars', $this, 'define_query_vars');

    $this->loader->add_action('wp', $this, 'blockAllStyles', 100);
  }

  public function define_query_vars($qvars)
  {
    $qvars[] = 'cascade_guru_optimize';
    return $qvars;
  }

  public function blockAllStyles($ref)
  {
    $shouldOptimize = get_query_var('cascade_guru_optimize', 0);
    if ($shouldOptimize == 1) {
      function pm_remove_all_scripts()
      {
        global $wp_scripts;
        $wp_scripts->queue = array();
      }
      //add_action('wp_print_scripts', 'pm_remove_all_scripts', 100);

      function pm_remove_all_styles()
      {
        global $wp_styles;
        $wp_styles->queue = array();
      }
      //add_action('wp_print_styles', 'pm_remove_all_styles', 100);
    }
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0.0
   */
  public function run()
  {
    $this->loader->run();

    add_action('template_redirect', function ($ref) {
      $currentId = get_queried_object_id();
      $shouldOptimize = get_query_var('cascade_guru_optimize', 0);

      /* var_dump([
        '$shouldOptimize' => $shouldOptimize,
        '$currentId' => $currentId,
        '$ref->ID' => $ref->ID
      ]); */

      if ($shouldOptimize == 1 /* && $currentId === $ref->ID */) {
        $this->hitApi();
      }
    });
  }

  private function hitApi()
  {
    require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-curl-post.php';

    $curl = new CurlPost(CASCADE_GURU_API_ENDPOINT);

    try {
      global $wp;
      $url = home_url($wp->request);
      $package = $curl([
        "email" => "admin@email.com",
        "targetUrl" => "$url/?cascade-guru-optimize=0",
        "apiKey" => "4016f610-a3b6-488d-9a93-de3cdfd9916f"
      ]);

      $data = json_decode($package);

      //echo "<style>{$data->css}</style>";

      $stats = $data->stats;

      //var_dump($stats);
    } catch (\RuntimeException $ex) {
      // catch errors
      die(sprintf('Http error %s with code %d', $ex->getMessage(), $ex->getCode()));
    }
  }

  /**
   * The name of the plugin used to uniquely identify it within the context of
   * WordPress and to define internationalization functionality.
   *
   * @since     1.0.0
   * @return    string    The name of the plugin.
   */
  public function get_cascade_guru()
  {
    return $this->cascade_guru;
  }

  /**
   * The reference to the class that orchestrates the hooks with the plugin.
   *
   * @since     1.0.0
   * @return    cascade_guru_Loader    Orchestrates the hooks of the plugin.
   */
  public function get_loader()
  {
    return $this->loader;
  }

  /**
   * Retrieve the version number of the plugin.
   *
   * @since     1.0.0
   * @return    string    The version number of the plugin.
   */
  public function get_version()
  {
    return $this->version;
  }
}
