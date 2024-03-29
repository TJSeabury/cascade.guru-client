<?php

namespace Tjseabury\CascadeGuru\src;

/**
 * Fired during plugin deactivation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    cascade_guru
 * @subpackage cascade_guru/src
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    cascade_guru
 * @subpackage cascade_guru/src
 * @author     Your Name <email@example.com>
 */
class CascadeGuruDeactivator
{

  /**
   * Short Description. (use period)
   *
   * Long Description.
   *
   * @since    1.0.0
   */
  public static function deactivate()
  {
    flush_rewrite_rules(true);
  }
}
