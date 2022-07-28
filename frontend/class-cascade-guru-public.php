<?php

namespace Tjseabury\CascadeGuru\frontend;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    cascade_guru
 * @subpackage cascade_guru/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    cascade_guru
 * @subpackage cascade_guru/public
 * @author     Your Name <email@example.com>
 */
class cascade_guru_Public
{

  /**
   * The ID of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $cascade_guru    The ID of this plugin.
   */
  private $cascade_guru;

  /**
   * The version of this plugin.
   *
   * @since    1.0.0
   * @access   private
   * @var      string    $version    The current version of this plugin.
   */
  private $version;

  /**
   * Initialize the class and set its properties.
   *
   * @since    1.0.0
   * @param      string    $cascade_guru       The name of the plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct($cascade_guru, $version)
  {

    $this->cascade_guru = $cascade_guru;
    $this->version = $version;
  }

  /**
   * Register the stylesheets for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_styles()
  {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in cascade_guru_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The cascade_guru_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    wp_enqueue_style($this->cascade_guru, plugin_dir_url(__FILE__) . 'css/cascade-guru-public.css', array(), $this->version, 'all');
  }

  /**
   * Register the JavaScript for the public-facing side of the site.
   *
   * @since    1.0.0
   */
  public function enqueue_scripts()
  {

    /**
     * This function is provided for demonstration purposes only.
     *
     * An instance of this class should be passed to the run() function
     * defined in cascade_guru_Loader as all of the hooks are defined
     * in that particular class.
     *
     * The cascade_guru_Loader will then create the relationship
     * between the defined hooks and the functions defined in this
     * class.
     */

    wp_enqueue_script($this->cascade_guru, plugin_dir_url(__FILE__) . 'js/cascade-guru-public.js', array('jquery'), $this->version, false);
  }
}
