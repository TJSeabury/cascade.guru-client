<?php

namespace Tjseabury\CascadeGuru\admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    cascade_guru
 * @subpackage cascade_guru/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    cascade_guru
 * @subpackage cascade_guru/admin
 * @author     Your Name <email@example.com>
 */
class CascadeGuruAdmin
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
   * @param      string    $cascade_guru       The name of this plugin.
   * @param      string    $version    The version of this plugin.
   */
  public function __construct($cascade_guru, $version)
  {

    $this->cascade_guru = $cascade_guru;
    $this->version = $version;
  }

  /**
   * Register the stylesheets for the admin area.
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

    wp_enqueue_style($this->cascade_guru, plugin_dir_url(__FILE__) . 'css/cascade-guru-admin.css', array(), $this->version, 'all');
  }

  /**
   * Register the JavaScript for the admin area.
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

    wp_enqueue_script($this->cascade_guru, plugin_dir_url(__FILE__) . 'js/cascade-guru-admin.js', array('jquery'), $this->version, false);
  }

  public function optimize_all()
  {
    global $wpdb; // this is how you get access to the database

    $whatever = intval($_POST['whatever']);

    $whatever += 10;

    echo $whatever;

    wp_die();
  }
}
