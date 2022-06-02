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

  protected $bypassVar = 'cascade-guru-bypass-optimizer';

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

    $this->loader->add_action('init', $this, 'add_custom_endpoints');

    $this->loader->add_action('wp', $this, 'blockAllStyles', 100);
  }

  public function add_custom_endpoints()
  {
    flush_rewrite_rules(true);
    add_rewrite_endpoint($this->bypassVar, EP_ALL, false);
  }

  public function blockAllStyles()
  {
    if (!$this->shouldBypass()) {
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
      add_action('wp_print_styles', 'pm_remove_all_styles', 100);
    }
  }

  private function shouldBypass(): bool
  {
    $sanitizedVar = '';
    $shouldBypass = false;
    if (isset($_GET[$this->bypassVar])) {
      $sanitizedVar = filter_var(
        $_GET[$this->bypassVar],
        FILTER_SANITIZE_URL
      );
      $shouldBypass = filter_var(
        $sanitizedVar,
        FILTER_VALIDATE_BOOLEAN
      );
    }
    return $shouldBypass;
  }

  /**
   * Run the loader to execute all of the hooks with WordPress.
   *
   * @since    1.0.0
   */
  public function run()
  {
    $this->loader->run();

    add_action('template_redirect', function () {
      $bundleName = $this->bundleName();

      if (!$this->shouldBypass()) {
        if (file_exists($bundleName['path'])) {
          $css = file_get_contents($bundleName['path']);
          add_action(
            'wp_print_styles',
            function () use ($css) {
              //echo "<link id=\"cascade-guru-bundle\" rel=\"stylesheet\" type=\"text/css\" href=\"{$bundleName['url']}\" >";
              echo "<style id=\"cascade-guru-bundle\">{$css}</style>";
            },
            PHP_INT_MAX
          );
        } else {
          $this->hitApi();
          $css = file_get_contents($bundleName['path']);
          add_action(
            'wp_print_styles',
            function () use ($css) {
              //echo "<link id=\"cascade-guru-bundle\" rel=\"stylesheet\" type=\"text/css\" href=\"{$bundleName['url']}\" >";
              echo "<style id=\"cascade-guru-bundle\">{$css}</style>";
            },
            PHP_INT_MAX
          );
        }
      }
    });
  }

  private function bundleName()
  {
    global $wp;
    $up = wp_get_upload_dir();
    return [
      'path' => $up['basedir'] . '/cg_optimized/' . $wp->request . '/cg_optimized_bundle.css',
      'url' => $up['baseurl'] . '/cg_optimized/' . $wp->request . '/cg_optimized_bundle.css'
    ];
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
        "targetUrl" => "$url/?{$this->bypassVar}=true",
        "apiKey" => "4016f610-a3b6-488d-9a93-de3cdfd9916f"
      ]);

      $data = json_decode($package);
      $css = $data->css;
      $stats = $data->stats;
      $errors = $data->errors;

      function file_force_contents($dir, $contents)
      {
        $dir = str_replace('\\', '/', $dir);
        $parts = explode('/', $dir);
        $file = array_pop($parts);
        $dir = array_shift($parts);
        foreach ($parts as $part) {
          if (!is_dir($dir .= DIRECTORY_SEPARATOR . $part)) mkdir($dir);
        }
        file_put_contents($dir . DIRECTORY_SEPARATOR . $file, $contents);
      }

      $bundleName = $this->bundleName();

      file_force_contents($bundleName['path'], $css);

      add_action('wp_print_footer_scripts', function () use ($stats, $errors) {
        echo '<pre>';
        var_dump($stats);
        var_dump($errors);
        echo '</pre>';
      });
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
