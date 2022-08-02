<?php

namespace Tjseabury\CascadeGuru\src;

/**
 * The file that defines the core plugin class
 *
 * A class definition that src attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    cascade_guru
 * @subpackage cascade_guru/src
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
 * @subpackage cascade_guru/src
 * @author     Your Name <email@example.com>
 */
class CascadeGuru
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

    /**
     * The class responsible for orchestrating the actions and filters of the
     * core plugin.
     */
    $this->loader = new \Tjseabury\CascadeGuru\src\CascadeGuruLoader();

    $this->define_admin_hooks();
    $this->define_public_hooks();
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
    $plugin_admin = new \Tjseabury\CascadeGuru\admin\CascadeGuruAdmin($this->get_cascade_guru(), $this->get_version());

    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
    $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    $this->loader->add_action('wp_ajax_optimize_all', $plugin_admin, 'optimize_all');
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

    $plugin_public = new \Tjseabury\CascadeGuru\frontend\CascadeGuruPublic($this->get_cascade_guru(), $this->get_version());

    //$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
    //$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

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
      function cg_remove_all_scripts()
      {
        global $wp_scripts;
        $wp_scripts->queue = array();
      }
      //add_action('wp_print_scripts', 'cg_remove_all_scripts', 100);


      add_action('wp_print_styles', function () {
        global $wp_styles;
        $wp_styles->queue = array();
      }, 1);

      function cg_reenqueue_wpadminbar()
      {
        wp_enqueue_style('admin-bar');
      }
      //add_action('wp_print_styles', 'cg_reenqueue_wpadminbar', 101);
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
    if (\is_user_logged_in() === true) {
      $shouldBypass = true;
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

    add_action('save_post', function ($post_id) {
      $url = get_page_link($post_id);
      $this->hitApi($url);
    });

    add_action('template_redirect', function () {
      global $wp;
      $url = home_url($wp->request);
      $bundleName = $this->bundleName($url);
      if (!$this->shouldBypass()) {
        if (file_exists($bundleName['fullPath'])) {
          //$css = file_get_contents($bundleName['fullPath']);
          add_action(
            'wp_print_styles',
            function () use ($bundleName) {
              echo "<link id=\"cascade-guru-bundle\" rel=\"preload\" type=\"text/css\" href=\"{$bundleName['fullUrl']}\" rel=\"preload\" as=\"style\" onload=\"this.onload=null;this.rel='stylesheet'\">";
              echo "<noscript><link id=\"cascade-guru-bundle\" rel=\"stylesheet\" type=\"text/css\" href=\"{$bundleName['fullUrl']}\" ></noscript>";
            },
            PHP_INT_MAX
          );
        }
      }
    });
  }

  private function bundleName($url)
  {
    $url = wp_make_link_relative($url);
    $up = wp_get_upload_dir();
    return [
      'fullPath' => $up['basedir'] . '/cg_optimized' . $url . '/cg_optimized_bundle.css',
      'fullUrl' => $up['baseurl'] . '/cg_optimized' . $url . '/cg_optimized_bundle.css',
      'path' => $up['basedir'] . '/cg_optimized' . $url . '/',
      'url' => $up['baseurl'] . '/cg_optimized' . $url . '/'
    ];
  }

  private function file_force_contents($dir, $contents)
  {
    if (empty($contents)) return;
    $dir = str_replace('\\', '/', $dir);
    $parts = explode('/', $dir);
    $file = array_pop($parts);
    $dir = array_shift($parts);
    foreach ($parts as $part) {
      if (!is_dir($dir .= DIRECTORY_SEPARATOR . $part)) mkdir($dir);
    }
    file_put_contents($dir . DIRECTORY_SEPARATOR . $file, $contents);
  }

  private function hitApi(string $url): void
  {
    $curl = new \Tjseabury\CascadeGuru\src\CurlPost(CASCADE_GURU_API_ENDPOINT);

    try {
      $package = $curl([
        "email" => "admin@cascade.guru",
        "targetUrl" => "$url?{$this->bypassVar}=true",
        "apiKey" => "89d39a28-e352-44b3-8aef-e19a2caede70"
      ]);

      $data = json_decode($package);

      $bundleName = $this->bundleName($url);

      $this->file_force_contents(
        $bundleName['fullPath'],
        $data->css
      );
      $this->file_force_contents(
        $bundleName['path'] . 'stats.json',
        json_encode($data->stats, JSON_PRETTY_PRINT)
      );
      $this->file_force_contents(
        $bundleName['path'] . 'errors.json',
        json_encode($data->errors, JSON_PRETTY_PRINT)
      );
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
