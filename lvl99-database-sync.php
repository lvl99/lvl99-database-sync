<?php

/*
Plugin Name: LVL99 Database Sync
Plugin URI: http://www.lvl99.com/code/database-sync/
Description: Allows you to easily save your WP database to an SQL file, and to also restore a database from an SQL file.
Author: Matt Scheurich
Author URI: http://www.lvl99.com/
Version: 0.1.1
Text Domain: lvl99-dbs
License: GPL2
*/

/*
  Copyright (c) 2014 Matt Scheurich (email: matt@lvl99.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !defined('ABSPATH') ) exit( 'No direct access allowed' );

// Fallback function if PHP not compiled with ZLIB
// http://stackoverflow.com/a/10381158/1421162
if ( !function_exists('gzdecode') )
{
  function gzdecode( $data )
  {
    return gzinflate( substr( $data, 10, -8 ) );
  }
}

// Environment variable
if ( !defined('WP_ENV') ) define( 'WP_ENV', 'live' );

if ( !class_exists( 'LVL99_DBS' ) )
{
  /*
  @class LVL99_DBS
  */
  class LVL99_DBS
  {
    /*
    @property $version
    @since 0.0.1
    @const
    @description The version number of the plugin
    @type {String}
    */
    public $version = '0.1.1';

    /*
    @property $plugin_dir
    @since 0.0.1
    @private
    @description The path to the plugin's directory
    @type {String}
    */
    private $plugin_dir;

    /*
    @property $default_options
    @since 0.0.1
    @protected
    @description The default options
    @type {Array}
    */
    protected $default_options = array();

    /*
    @property $options
    @since 0.0.1
    @protected
    @description Holds the options for the plugin
    @type {Array}
    */
    protected $options = array();

    /*
    @property $route
    @since 0.0.1
    @protected
    @description The object with the route's information
    @type {Array}
    */
    protected $route = array();

    /*
    @property $notices
    @since 0.0.1
    @description An array of notices to display on the admin side
    @type {Array}
    */
    public $notices = array();

    /*
    @property $start
    @private
    @since 0.0.2
    @description The time the plugin started operating
    @type {int}
    */
    private $start;

    /*
    The text domain for i18n

    @property $textdomain
    @since 0.1.0
    @private
    @type {String}
    */
    private $textdomain = 'lvl99-dbs';

    /*
    Log file reference

    @property $log_file
    @since 0.1.0
    @private
    @type {String}
    */
    private $log_file = 'lvl99-dbs.log';

    /*
    @method __construct
    @since 0.0.1
    @description PHP magic method which runs when class is created
    @returns {Void}
    */
    public function __construct()
    {
      $this->plugin_dir = dirname(__FILE__);

      // Record the time taken
      $this->start = microtime( TRUE );

      // Actions/filters
      register_activation_hook( __FILE__, array( $this, 'activate' ) );
      register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
      add_action( 'init', array( $this, 'i18n' ) );
      add_action( 'admin_init', array( $this, 'initialise' ) );
      add_action( 'admin_menu', array( $this, 'admin_menu' ) );
      add_action( 'admin_notices', array( $this, 'admin_notices' ) );
      add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'admin_plugin_links' ) );
      add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
    }

    /*
    @method check_admin
    @since 0.0.1
    @private
    @description Checks if the user is an admin and can perform the operation
    @returns {Boolean}
    */
    private function check_admin()
    {
      if ( !is_admin() )
      {
        $callee = debug_backtrace();
        error_log( _x( sprintf('LVL99_DBS Error: Non-admin attempted operation %s', $callee[1]['function']), 'lvl99-dbs'), 'wp error_log' );
        wp_die( __('LVL99 DBS Error: You must have administrator privileges to operate this functionality', 'lvl99-dbs') );
      }
      return TRUE;
    }

    /*
    @method i18n
    @since 0.0.1
    @description Loads the plugin's text domain for translation purposes
    @returns {Void}
    */
    public function i18n()
    {
      load_plugin_textdomain( $this->textdomain, FALSE, basename( dirname(__FILE__) ) . '/languages' );
    }

    /*
    @method activate
    @since 0.0.1
    @description Runs when the plugin is activated
    @returns {Void}
    */
    public function activate()
    {
      // Install the options
      $_plugin_installed = get_option( '_' . $this->textdomain . '/installed', FALSE );
      $_plugin_version = get_option( '_' . $this->textdomain . '/version', $this->version );
      if ( !$_plugin_installed )
      {
        // Set the initial options
        foreach ( $this->default_options as $name => $value )
        {
          add_option( $this->textdomain . '/' . $name, $value );
        }
      }

      // Mark that the plugin is now installed
      update_option( '_' . $this->textdomain . '/installed', TRUE );
      update_option( '_' . $this->textdomain . '/version', $this->version );
    }

    /*
    @method deactivate
    @since 0.0.1
    @description Runs when the plugin is deactivated
    @returns {Void}
    */
    public function deactivate()
    {
      $_plugin_installed = get_option( '_lvl99-dbs/installed', TRUE );
      $_plugin_version = get_option( '_lvl99-dbs/version', $this->version );

      if ( $_plugin_installed )
      {
        switch ($_plugin_version)
        {
          default:
            break;

          case FALSE:
            break;

          // Specific operations if deactivating v0.0.1
          case '0.0.1':
            break;
        }
      }
    }

    /*
    @method uninstall
    @since 0.0.1
    @description Runs when the plugin is uninstalled/deleted
    @returns {Void}
    */
    public function uninstall( $_plugin_version = FALSE )
    {
      if ( !$_plugin_version ) $_plugin_version = get_option( '_lvl99-dbs/version', $this->version );

      switch ($_plugin_version)
      {
        default:
          foreach ( $this->options as $key => $value )
          {
            delete_option( 'lvl99-dbs/' . $key );
          }
          delete_option( '_lvl99-dbs/installed' );
          delete_option( '_lvl99-dbs/version' );

        case FALSE:
          break;

        // Specific operations if uninstalling v0.0.1
        case '0.0.1':
          break;
      }
    }

    /*
    @method initialise
    @since 0.0.1
    @description Runs when the plugin is initialised via WP
    @returns {Void}
    */
    public function initialise()
    {
      $this->check_admin();

      // Load in the options (via DB or use defined defaults above)
      $this->load_options();

      // Detect (and run) the route
      $this->detect_route();

      // Create log if doesn't exist
      if ( $this->get_option('show_debug') ) $this->new_log();
    }

    /*
    Enqueue the admin scripts for the plugin (only if viewing page related to plugin)

    @method admin_enqueue_scripts
    @returns {Void}
    */
    public function admin_enqueue_scripts ( $hook_suffix )
    {
      if ( stristr( $hook_suffix, $this->textdomain ) !== FALSE )
      {
        // Styles
        // wp_enqueue_style('thickbox');
        wp_enqueue_style( $this->textdomain, plugins_url( 'css/lvl99-dbs.css', __FILE__ ), array(), $this->version, 'all' );

        // Scripts
        // wp_enqueue_script('media-upload');
        // wp_enqueue_script('thickbox');
        wp_enqueue_script( $this->textdomain, plugins_url( 'js/lvl99-dbs.js', __FILE__ ), array('jquery', 'jquery-ui-sortable'), $this->version, TRUE );

        // Custom page-specific styles and scripts
        // add_action( 'admin_head', array($this, 'admin_head'), 99999999 );
        // add_action( 'admin_footer', array($this, 'admin_footer'), 99999999 );
      }
    }

    /*
    Gets the text domain string

    @method get_textdomain
    @since 0.1.0
    @returns {String}
    */
    public function get_textdomain()
    {
      return $this->textdomain;
    }

    /*
    Loads all options into the class and registers them in WordPress

    @method load_options
    @since 0.0.1
    @returns {Void}
    */
    public function load_options( $init = TRUE )
    {
      // Default options
      $this->default_options = array(
        /*
         * Path to save/load SQL files to
         */
        'path' => array(
          'sanitise_callback' => array( $this, 'sanitise_option_path' ),
          'default' => '{WP_CONTENT_DIR}/backup-db/',
          'field_type' => 'text',
          'label' => _x('SQL file folder path', 'field label: path', 'lvl99-dbs'),
          'help' => _x('<p>The folder must already be created for you to successfully reference it here and have permissions for PHP to write to.<br/>For security purposes, consider referencing to a folder that exists above your <code>www/public_html</code> folder</p>', 'field help: file_name', 'lvl99-dbs'),
          'help_after' => _x('<p>Tags you can use within the path:</p>', 'field help after: file_name', 'lvl99-dbs').'
<ul><li><code>{ABSPATH}</code> ' . sprintf( _x('The absolute path to the WordPress installation (references <code>ABSPATH</code> constant)<br/>Current: <code>%s</code>', 'field help: path {ABSPATH} tag', 'lvl99-dbs'), ABSPATH) . '</li><li><code>{get_home_path}</code> ' . sprintf( _x('The path to the WordPress\'s installation (references function <code>get_home_path()</code>\'s return value)<br/>Current: <code>%s</code>', 'field help: path {get_home_path} tag', 'lvl99-dbs'), get_home_path() ) . '</li>
<li><code>{WP_CONTENT_DIR}</code> ' . sprintf( _x('The path to the wp-content folder (references <code>WP_CONTENT_DIR</code> constant)<br/>Current: <code>%s</code>', 'field help: path {WP_CONTENT_DIR} tag', 'lvl99-dbs'), WP_CONTENT_DIR ) . '</li></ul>',
          // 'show_previous_value' => TRUE,
        ),

        /*
         * SQL file name template
         */
        'file_name' => array(
          'sanitise_callback' => array( $this, 'sanitise_option_file_name' ),
          'default' => '{date:YmdHis} {env} {url} {database}.sql',
          'field_type' => 'text',
          'label' => _x('SQL file name format', 'field label: file_name', 'lvl99-dbs'),
          'help_after' => '<p>' . _x('Tags you can use within the file name:', 'field help: file_name', 'lvl99-dbs') . '</p>
<ul><li><code>{date:<i>...</i>}</code> ' . _x('Replace <code>...</code> with a string representing the date output according to <a href="http://au1.php.net/manual/en/function.date.php" target="_blank">PHP\'s date() function</a>.<br/><b>Note:</b> You cannot use additional semi-colons or curly braces within this tag.', 'field help: file_name {code} tag', 'lvl99-dbs') . '</li>
<li><code>{env}</code> ' . _x('The environment that the site is running in (references constant <code>WP_ENV</code>)', 'field help: file_name {env} tag', 'lvl99-dbs') . '</li>
<li><code>{database}</code> ' . _x('The name of the database', 'field help: file_name {database} tag', 'lvl99-dbs').'</li>
<li><code>{url}</code> ' . _x('The URL of the website (references constant <code>home_url()</code> function)', 'field help: file_name {url} tag', 'lvl99-dbs') . '</li></ul>',
        ),

        /*
         * Compression format
         */
        'compress_format' => array(
          'sanitise_callback' => array( $this, 'sanitise_option_compress_format' ),
          'values' => array(
            array(
              'label' => _x('No compression', 'field value label: compress_format=none', 'lvl99-dbs'),
              'value' => 'none',
            ),
            array(
              'label' => _x('GZIP', 'field value label: compress_format=gzip', 'lvl99-dbs'),
              'value' => 'gzip',
            ),
          ),
          'default' => 'gzip',
          'field_type' => 'radio',
          'label' => _x('Default file compression format', 'field label: compress_format', 'lvl99-dbs'),
        ),

        /*
         * Debugging
         */
        // Underline hides it from $options array
        '_debugging' => array(
          'field_type' => 'heading',
          'label' => 'Debugging',
        ),

        /*
         * Show debug output
         */
        'show_debug' => array(
          'label' => 'Show debug output',
          'field_type' => 'checkbox',
          'default' => TRUE,
          'help_after' => 'If you\'re having some issues or want to debug your server\'s activity, enable this to see what happens during the Database Sync operation.',
          'sanitise_callback' => array( $this, 'sanitise_option_boolean' ),
        ),
      );

      // Get the saved options
      if ( count($this->default_options) > 0 )
      {
        foreach ( $this->default_options as $name => $option  )
        {
          // Ignore static option types: `heading`
          if ( $option['field_type'] == 'heading' ) continue;

          // Ensure `sanitise_callback` is NULL if empty
          if ( !array_key_exists('sanitise_callback', $option) ) $option['sanitise_callback'] = NULL;

          // Get the database's value
          $this->options[$name] = get_option( $this->textdomain . '/' . $name, $option['default'] );

          // Register the setting to be available to all other plugins (I think?)
          if ( $init )
          {
            if ( !is_null($option['sanitise_callback']) )
            {
              register_setting( $this->textdomain, $this->textdomain . '/' . $name, $option['sanitise_callback'] );
            }
            else
            {
              register_setting( $this->textdomain, $this->textdomain . '/' . $name );
            }
          }
        }
      }
    }

    /*
    Sets an option for the plugin

    @method set_option
    @since 0.1.0
    @param {String} $name The name of the option
    @param {Mixed} $default The default value to return if it is not set
    @returns {Mixed}
    */
    public function set_option ( $name = FALSE, $value = NULL )
    {
      if ( !$name || !array_key_exists($name, $this->options) ) return;
      update_option( $this->textdomain . '/' . $name, $value );
      $this->options[$name] = $value;
    }

    /*
    Gets value of plugin's option.

    @method get_option
    @since 0.1.0
    @param {String} $name The name of the option
    @param {Mixed} $default The default value to return if it is not set
    @returns {Mixed}
    */
    public function get_option ( $name = FALSE, $default = NULL )
    {
      if ( !$name || !array_key_exists($name, $this->options) ) return $default;
      return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    /*
    Filters the {tags} in the path option

    @method get_option_path
    @since 0.0.2
    @param {String} $path The string to test as a path; defaults to `$this->get_option('path')`
    @returns {String}
    */
    public function get_option_path( $path = '' )
    {
      if ( empty($path) ) $path = $this->get_option('path');

      $path = $this->replace_tags( $path, array(
        'ABSPATH' => trailingslashit(ABSPATH),
        'get_home_path' => trailingslashit(get_home_path()),
        'WP_CONTENT_DIR' => trailingslashit(WP_CONTENT_DIR),
      ) );

      // Remove duplicate slashes
      $path = preg_replace( '/[\/\\\\]+/', trailingslashit(''), $path );
      return trailingslashit($path);
    }

    /*
    Get an array of the option names

    @method get_option_names
    @since 0.1.0
    @returns {Array}
    */
    protected function get_option_names()
    {
      $option_names = array();

      foreach( $this->options as $name => $option )
      {
        array_push( $option_names, $name );
      }

      return $option_names;
    }

    /*
    Get an array of the default option values

    @method get_default_option_values
    @since 0.1.0
    @returns {Array}
    */
    protected function get_default_option_values()
    {
      $default_option_values = array();

      foreach( $this->options as $name => $option )
      {
        if ( !empty($option['default']) ) {
          $default_option_values[$name] = $option['default'];
        } else {
          $default_option_values[$name] = '';
        }
      }

      return $default_option_values;
    }

    /*
    sanitise the option's value

    @method sanitise_option
    @since 0.1.0
    @param {String} $input
    @returns {Mixed}
    */
    protected function sanitise_option ( $option, $input )
    {
      // If the sanitise_option has been set...
      if ( array_key_exists('sanitise_callback', $option) && !empty($option['sanitise_callback']) && !is_null($option['sanitise_callback']) )
      {
        return call_user_func( $option['sanitise_callback'], $input );
      }

      return $input;
    }

    /*
    sanitise the option's text value (strips HTML)

    @method sanitise_option_text
    @since 0.1.0
    @param {String} $input
    @returns {String}
    */
    public static function sanitise_option_text ( $input )
    {
      // ChromePhp::log( 'sanitise_option_text' );
      // ChromePhp::log( $input );

      return strip_tags(trim($input));
    }

    /*
    sanitise the option's HTML value (strips only some HTML)

    @method sanitise_option_html
    @since 0.1.0
    @param {String} $input
    @returns {String}
    */
    public static function sanitise_option_html ( $input )
    {
      // ChromePhp::log( 'sanitise_option_html' );
      // ChromePhp::log( $input );

      return strip_tags( trim($input), '<b><strong><i><em><u><del><strikethru><a><br><span><div><p><h1><h2><h3><h4><h5><h6><ul><ol><li><dl><dd><dt>' );
    }

    /*
    sanitise the option's number value

    @method sanitise_option_number
    @since 0.1.0
    @param {String} $input
    @returns {Integer}
    */
    public static function sanitise_option_number ( $input )
    {
      // ChromePhp::log( 'sanitise_option_number' );
      // ChromePhp::log( $input );

      return intval( preg_replace( '/\D+/i', '', $input ) );
    }

    /*
    sanitise the option's URL value. Namely, remove any absolute domain reference (make it relative to the current domain)

    @method sanitise_option_url
    @since 0.1.0
    @param {String} $input
    @returns {Integer}
    */
    public static function sanitise_option_url ( $input )
    {
      // ChromePhp::log( 'sanitise_option_url' );
      // ChromePhp::log( $input );

      if ( stristr($input, home_url('/')) !== FALSE )
      {
        $input = str_replace(home_url('/'), '', $input);
      }

      return strip_tags(trim($input));
    }

    /*
    sanitise the option's boolean value

    @method sanitise_option_boolean
    @since 0.1.0
    @param {String} $input
    @returns {Integer}
    */
    public static function sanitise_option_boolean ( $input )
    {
      if ( $input === 1 || strtolower($input) === 'true' || $input === TRUE || $input === '1' ) return TRUE;
      if ( $input === 0 || strtolower($input) === 'false' || $input === FALSE || $input === '0' || empty($input) ) return FALSE;
      return (bool) $input;
    }

    /*
    Sanitises the option value set for 'lvl99-dbs/path'

    @method sanitise_option_path
    @since 0.1.1
    @param {String} $input The new value to sanitise
    @returns {String}
    */
    public function sanitise_option_path( $input )
    {
      // Format to test folder exists
      $test = $this->get_option_path($input);

      // Folder doesn't exist
      if ( !file_exists($test) )
      {
        $this->admin_error( sprintf( _x('Given SQL path value not found at <code>%s</code>. Please ensure the folder exists before changing this value.', 'sanitise option path error', 'lvl99-dbs'), $test ) );
        return FALSE;
      }

      return $input;
    }

    /*
    @method sanitise_option_file_name
    @since 0.0.1
    @description Sanitises the option value set for 'lvl99-dbs/file_name'
    @param {String} $input The new value to sanitise
    @returns {String}
    */
    public function sanitise_option_file_name( $input )
    {
      // Convert slashes to underscores (no subdirectories are supported yet)
      $input = str_replace( '/["\']+/', '_', $input );
      // Add .sql to the end if it doesn't already exist
      if ( !preg_match('/\.sql$/i', $input ) ) $input .= '.sql';
      return $input;
    }

    /*
    @method sanitise_option_compress_format
    @since 0.0.1
    @description Sanitises the option value set for 'lvl99-dbs/compress_format'
    @param {String} $input The new value to sanitise
    @returns {String}
    */
    public function sanitise_option_compress_format( $input )
    {
      // Iterate through the values to see if the input value matches to any of the values permitted
      foreach( $this->default_options['compress_format']['values'] as $option )
      {
        if ( is_array($option) )
        {
          if ( $option['value'] == $input )
          {
            return $input;
          }
        }
        else
        {
          if ( $option == $input )
          {
            return $input;
          }
        }
      }
      // Couldn't find the value within the options' accepted values, use the default
      return $this->default_options['compress_format']['default'];
    }

    /*
    Sanitises SQL, primarily by looking for specific SQL commands

    @method sanitise_sql
    @since 0.1.0
    @param {String} $input The string to sanitise
    @returns {String}
    */
    protected function sanitise_sql ( $input )
    {
      $search = array(
        '/(CREATE|DROP|UPDATE|ALTER|RENAME|TRUNCATE)\s+(TABLE|TABLESPACE|DATABASE|VIEW|LOGFILE|EVENT|FUNCTION|PROCEDURE|TRIGGER)[^;]+/i',
        '/\d\s*=\s*\d/',
        '/;.*/',
      );

      $replace = array(
        '',
        '',
        '',
      );

      $output = preg_replace( $search, $replace, $input );
      return $output;
    }

    /*
    Get option field ID

    @method get_field_id
    @param {String} $field_name The name of the option
    @returns {String}
    */
    protected function get_field_id( $option_name )
    {
      // if ( array_key_exists($option_name, $this->default_options) )
      // {
        return $this->textdomain . '_' . $option_name;
      // }
      // return '';
    }

    /*
    Get option field name

    @method get_field_name
    @param {String} $field_name The name of the option
    @returns {String}
    */
    protected function get_field_name( $option_name )
    {
      if ( array_key_exists($option_name, $this->default_options) )
      {
        return $this->textdomain . '/' . $option_name;
      }
      else
      {
        return $this->textdomain . '_' . $option_name;
      }
    }

    /*
    Render options' input fields.

    @method render_options
    @since 0.1.0
    @param {Array} $options The options to render out
    @returns {Void}
    */
    protected function render_options ( $options )
    {
      $this->check_admin();

      // Check if its the plugin's settings screen
      $screen = get_current_screen();
      $is_settings_options = $screen->id == 'settings_page_' . $this->textdomain . '-options';

      if ( count($options > 0) )
      {
        foreach( $options as $name => $option )
        {
          // ID and name (changes if not settings page)
          $field_id = $is_settings_options ? $this->get_field_id($name) : $this->get_field_id($name);
          $field_name = $is_settings_options ? $this->get_field_name($name) : $this->get_field_id($name);

          // Visible field
          $is_visible = array_key_exists('visible', $option) ? $option['visible'] : TRUE;
          if ( $option['field_type'] == 'hidden' ) $is_visible = FALSE;

          // Headings and other static option types
          if ( $option['field_type'] == 'heading' )
          {
?>
          <div class="lvl99-plugin-option-heading" id="<?php echo esc_attr($field_id); ?>">
            <h3><?php echo $option['label']; ?></h3>
            <hr/>

            <?php if ( isset($option['help']) ) : ?>
            <div class="lvl99-plugin-option-help lvl99-plugin-option-help-before">
              <?php echo $option['help']; ?>
            </div>
            <?php endif; ?>

            <?php if ( isset($option['help_after']) ) : ?>
            <div class="lvl99-plugin-option-help lvl99-plugin-option-help-after">
              <?php echo $option['help_after']; ?>
            </div>
            <?php endif; ?>
          </div>
<?php
            continue;
          }

          // Singular field (e.g. single checkbox or radio)
          $is_singular = $option['field_type'] == 'checkbox' && !array_key_exists('values', $option);

          // Sortable fields
          $is_sortable = ( $option['field_type'] == 'checkbox' && array_key_exists('sortable', $option) && !$is_singular ? $option['sortable'] : FALSE );

          // Input class
          $input_class = !empty($option['input_class']) ? $option['input_class'] : 'widefat';

          // Default values for the option
          $option_value = !empty($this->options[$name]) ? $this->options[$name] : $option['default'];

          if ( $is_visible )
          {
?>
          <div class="lvl99-plugin-option <?php if ($is_sortable && $option['field_type'] != 'checkbox' && $option['field_type'] != 'radio') : ?>lvl99-draggable lvl99-sortable lvl99-sortable-handle<?php endif; ?>">

            <?php do_action( 'lvl99_plugin_option_field_footer_' . $name, '' ); ?>

            <?php if ( !$is_singular ) : ?>
            <label for="<?php echo $field_id; ?>" class="lvl99-plugin-option-label"><?php echo $option['label']; ?></label>
            <?php endif; ?>

            <?php if ( isset($option['help']) ) : ?>
            <div class="lvl99-plugin-option-help lvl99-plugin-option-help-before">
              <?php echo $option['help']; ?>
            </div>
            <?php endif; ?>

            <?php if ( !empty($option['input_before']) ) : ?>
            <span class="lvl99-plugin-option-input-before">
              <?php echo $option['input_before']; ?>
            </span>
            <?php endif; ?>

            <?php if ( $option['field_type'] == 'text' ) : ?>
              <input id="<?php echo $field_id; ?>" type="text" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" class="<?php echo esc_attr($input_class); ?>" />

            <?php elseif ( $option['field_type'] == 'number' ) : ?>
              <input id="<?php echo $field_id; ?>" type="number" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" class="<?php echo esc_attr($input_class); ?>" />

            <?php elseif ( $option['field_type'] == 'email' ) : ?>
              <input id="<?php echo $field_id; ?>" type="email" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" class="<?php echo esc_attr($input_class); ?>" />

            <?php elseif ( $option['field_type'] == 'select' ) : ?>
              <select id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" class="<?php echo esc_attr($input_class); ?>">
              <?php foreach( $option['values'] as $value ) : ?>
                <?php if ( is_array($value) ) : ?>
                <option value="<?php echo $value['value']; ?>" <?php if ( $option_value == $value['value'] ) : ?>selected="selected"<?php endif; ?>>
                <?php if ( isset($value['label']) ) : ?>
                  <?php echo $value['label']; ?>
                <?php else : ?>
                  <?php echo $value['value']; ?>
                <?php endif; ?>
                </option>
                <?php else : ?>
                <option <?php if ( $option_value == $value ) : ?>selected="selected"<?php endif; ?>><?php echo $value; ?></option>
                <?php endif; ?>
              <?php endforeach; ?>
              </select>

            <?php elseif ( $option['field_type'] == 'radio' ) : ?>
              <ul id="<?php echo $field_id; ?>-list">
                <?php foreach( $option['values'] as $value ) : ?>
                <?php if ( is_array($value) ) : ?>
                  <li>
                    <label class="lvl99-plugin-option-value">
                      <input type="radio" name="<?php echo $field_name; ?>" value="<?php echo $value['value']; ?>" <?php if ( $option_value == $value['value'] ) : ?>checked="checked"<?php endif; ?> />
                      <div class="lvl99-plugin-option-value-label">
                        <?php if ( isset($value['label']) ) : ?>
                          <?php echo $value['label']; ?>
                        <?php else : ?>
                          <?php echo $value['value']; ?>
                        <?php endif; ?>
                        <?php if ( !empty($value['description']) ) : ?>
                        <p class="lvl99-plugin-option-value-description"><?php echo $value['description']; ?></p>
                        <?php endif; ?>
                      </div>
                    </label>
                  </li>
                <?php else : ?>
                  <li>
                    <label class="lvl99-plugin-option-value">
                      <input type="radio" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($value); ?>" <?php if ( $option_value == $value ) : ?>checked="checked"<?php endif; ?> />
                      <div class="lvl99-plugin-option-value-label">
                        <?php if ( $is_singular ) : ?>
                        <?php echo $option['label']; ?>
                        <?php else : ?>
                        <?php echo $value; ?>
                        <?php endif; ?>
                        <?php if ( !empty($value['description']) ) : ?>
                        <p class="lvl99-plugin-option-value-description"><?php echo $value['description']; ?></p>
                        <?php endif; ?>
                      </div>
                    </label>
                  </li>
                <?php endif; ?>
                <?php endforeach; ?>
              </ul>

            <?php elseif ( $option['field_type'] == 'checkbox' ) : ?>
              <ul id="<?php echo $field_id; ?>-list" class="<?php if ($is_sortable) : ?>lvl99-sortable<?php endif; ?>">
                <?php $option_values = isset($option['values']) ? $option['values'] : array($option_value); ?>

                <?php if ( $is_sortable ) :
                  // If the field is sortable, we'll need to render the options in the sorted order
                  if ( stristr($option_value, ',') !== FALSE )
                  {
                    $option_values = explode( ',', $option_value );

                    // Add the other values that the $option_values is missing (because they haven't been checked)
                    foreach( $option['values'] as $key => $value )
                    {
                      if ( !in_array($key, $option_values) )
                      {
                        array_push( $option_values, $key );
                      }
                    }

                    // Re-order the options' rendering order
                    $reordered_values = array();
                    foreach ( $option_values as $key => $value )
                    {
                      $reordered_values[$key] = $option['values'][$value];
                    }
                    $option_values = $reordered_values;

                  } ?>
                  <input type="hidden" id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" />
                <?php endif; ?>

                <?php foreach ( $option_values as $value ) : ?>
                  <?php if ( is_array($value) ) : ?>
                  <li <?php if ( $is_sortable ) : ?>class="ui-draggable ui-sortable"<?php endif; ?>>
                    <?php if ($is_sortable) : ?><span class="fa-arrows-v lvl99-sortable-handle"></span><?php endif; ?>
                    <label class="lvl99-plugin-option-value">
                      <input type="checkbox" name="<?php if ( $is_sortable ) : echo esc_attr($name).'['.esc_attr($value['value']).']'; else : echo $field_name; endif; ?>" value="true" <?php if ( stristr($option_value, $value['value'])) : ?>checked="checked"<?php endif; ?> />
                      <div class="lvl99-plugin-option-value-label">
                        <?php if ( isset($value['label']) ) : ?>
                          <?php echo $value['label']; ?>
                        <?php else : ?>
                          <?php echo $value['value']; ?>
                        <?php endif; ?>
                        <?php if ( !empty($value['description']) ) : ?>
                        <p class="lvl99-plugin-option-value-description"><?php echo $value['description']; ?></p>
                        <?php endif; ?>
                      </div>
                    </label>
                  </li>
                  <?php else : ?>
                  <li <?php if ( $is_sortable ) : ?>class="ui-draggable ui-sortable"<?php endif; ?>>
                    <?php if ($is_sortable) : ?><span class="fa-arrows-v lvl99-sortable-handle"></span><?php endif; ?>
                    <label class="lvl99-plugin-option-value">
                      <input type="checkbox" name="<?php if ( $is_sortable ) : echo esc_attr($name).'['.esc_attr($value['value']).']'; else : echo $field_name; endif; ?>" value="<?php echo ( $is_singular ? 'true' : esc_attr($value) ); ?>" <?php if ( !empty($option_value) && $option_value == $value ) : ?>checked="checked"<?php endif; ?> />
                      <div class="lvl99-plugin-option-value-label">
                        <?php if ( $is_singular ) : ?>
                        <?php echo $option['label']; ?>
                        <?php else : ?>
                        <?php echo $value; ?>
                        <?php endif; ?>
                        <?php if ( !empty($value['description']) ) : ?>
                        <p class="lvl99-plugin-option-value-description"><?php echo $value['description']; ?></p>
                        <?php endif; ?>
                      </div>
                    </label>
                  </li>
                  <?php endif; ?>
                <?php endforeach; ?>
              </ul>

            <?php elseif ( $option['field_type'] == 'image' ) : ?>
              <a href="javascript:void(0);" class="upload_file_button">
                <div class="button-primary"><?php _e( 'Upload or select image', 'lvl99' ); ?></div>
                <input type="hidden" id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" />
                <p><img src="<?php echo esc_url($option_value); ?>" style="max-width: 100%; <?php if ( $option_value == "" ) : ?>display: none<?php endif; ?>" /></p>
              </a>
              <a href="javascript:void(0);" class="remove_file_button button" <?php if ( $option_value == "" ) : ?>style="display:none"<?php endif; ?>>Remove image</a>

            <?php elseif ( $option['field_type'] == 'textarea' ) : ?>
              <textarea id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" class="<?php echo esc_attr($input_class); ?>"><?php echo $option_value; ?></textarea>

            <?php endif; ?>

            <?php if ( !empty($option['input_after']) ) : ?>
            <span class="lvl99-plugin-option-input-after">
              <?php echo $option['input_after']; ?>
            </span>
            <?php endif; ?>

            <?php /* if ( isset($option['show_previous_value']) ) : ?>
            <div class="lvl99-plugin-option-previous-value">
              <p>Previous value: <code><?php echo $this->get_option($name); ?></code></p>
            </div>
            <?php endif; */ ?>

            <?php if ( isset($option['help_after']) ) : ?>
            <div class="lvl99-plugin-option-help lvl99-plugin-option-help-after">
              <?php echo $option['help_after']; ?>
            </div>
            <?php endif; ?>

            <?php do_action( 'lvl99_plugin_option_field_footer_' . $name, '' ); ?>

            <?php if ( $is_sortable ) : ?>
            <script type="text/javascript">
              jQuery(document).ready( function () {
                jQuery('#<?php echo $field_id; ?>-list.lvl99-sortable').sortable({
                  items: '> li',
                  handle: '.lvl99-sortable-handle'
                });
              });
            </script>
            <?php endif; ?>
          </div>
<?php
          // Hidden fields
          } else {
            if ( $option['field_type'] == 'hidden' )
            {
?>
          <input type="hidden" id="<?php echo $field_id; ?>" name="<?php echo $field_name; ?>" value="<?php echo esc_attr($option_value); ?>" />
<?php
            }
          }
        } // endforeach;
      }
    }

    /*
    Replace {tags} within a string using an array's properties (and other custom functions)

    @method replace_tags
    @since 0.1.0
    @param {String} $input
    @param {Array} $tags The array with tags to replace
    @returns {String}
    */
    protected function replace_tags( $input, $tags = array() )
    {
      $output = $input;
      preg_match_all( '/\{[^\}]+\}/i', $input, $matches );

      if ( count($matches[0]) )
      {
        foreach( $matches[0] as $tag )
        {
          $tag_search = $tag;
          $tag_name = preg_replace( '/[\{\}]/', '', $tag );
          $tag_replace = '';

          // Get string to replace tag with
          if ( array_key_exists( $tag_name, $tags ) != FALSE )
          {
            $tag_replace = $tags[$tag_name];
          }

          // Tag has arguments
          if ( strstr($tag_name, ':') != FALSE )
          {
            $tag_split = explode( ':', $tag_name );
            $tag_name = $tag_split[0];
            $tag_replace = $tag_split[1];

            // Supported special functions (defined by {function:argument})
            switch ($tag_name)
            {
              case 'date':
                $tag_replace = date( $tag_replace );
                break;
            }
          }

          // Replace
          $output = str_replace( $tag_search, $tag_replace, $output );
        }
      }

      return $output;
    }

    /*
    Detects if a route was fired and then builds `$this->route` object and fires its corresponding method after the plugins have loaded.

    Routes are actions which happen before anything is rendered.

    @method detect_route
    @since 0.1.0
    @returns {Void}
    */
    public function detect_route ()
    {
      // Ignore if doesn't match this plugin's textdomain
      if ( !isset($_GET['page']) && !isset($_REQUEST[$this->textdomain]) ) return;

      // Do the detection schtuff
      if ( (isset($_REQUEST[$this->textdomain]) && !empty($_REQUEST[$this->textdomain])) || ($_GET['page'] == $this->textdomain && isset($_GET['action'])) )
      {
        $this->check_admin();
        $this->load_options(FALSE);

        // Process request params
        $_request = array(
          'get' => $_GET,
          'post' => $_POST,
        );

        $request = array();
        foreach ( $_request as $_method => $_array )
        {
          $request[$_method] = array();
          foreach( $_array as $name => $value )
          {
            if ( stristr($name, $this->textdomain.'_') != FALSE )
            {
              $request[$_method][str_replace( $this->textdomain.'_', '', strtolower($name) )] = is_string($value) ? urldecode($value) : $value;
            }
          }
        }

        // Get the method name depending on the type
        if ( isset($_REQUEST[$this->textdomain]) && !empty($_REQUEST[$this->textdomain]) )
        {
          $method_name = $_REQUEST[$this->textdomain];
        }
        else if ( $_GET['page'] == $this->textdomain && isset($_GET['action']) )
        {
          $method_name = $_GET['action'];
        }

        // Build and set the route to the class for later referral when running the route's method
        $this->route = array(
          'method' => 'route_' . preg_replace( '/[^a-z0-9_]+/i', '', $method_name ),
          'referrer' => isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : NULL,
          'request' => $request,
        );

        if ( !empty($this->route['request']['get']) || !empty($this->route['request']['post']) )
          $this->perform_route();
      }
    }

    /*
    Performs the route's method (only if one exists)

    @method perform_route
    @since 0.1.0
    @returns {Void}
    */
    public function perform_route ()
    {
      $this->check_admin();

      if ( isset($this->route['method']) && !empty($this->route['method']) && method_exists( $this, $this->route['method'] ) )
      {
        call_user_func( array( $this, $this->route['method'] ) );
      }
      else
      {
        error_log( 'LVL99 DBS Error: invalid route method called: ' . $this->route['method'] );
        // $this->admin_error( sprintf( __('Invalid route method was called: <strong><code>%s</code></strong>', $this->textdomain), $this->route['method'] ) );
      }
    }

    /*
    @method enable_maintenance
    @since 0.0.1
    @description Enables the maintenance mode
    @returns {String}
    */
    private function enable_maintenance()
    {
      $maintenance = trailingslashit(ABSPATH) . '.maintenance';
      $_maintenance = trailingslashit($this->plugin_dir) . '.maintenance';

      // Maintenance mode already enabled
      if ( file_exists($maintenance) ) return;

      // If maintenance file doesn't exist, create a new blank one to use
      if ( !file_exists($_maintenance) ) $f_maintenance = fopen( $_maintenance, 'w' );

      // Copy the template to WP's abspath
      copy( $_maintenance, $maintenance );
    }

    /*
    @method disable_maintenance
    @since 0.0.1
    @description Disables the maintenance mode
    @returns {String}
    */
    public function disable_maintenance()
    {
      $maintenance = trailingslashit(ABSPATH) . '.maintenance';

      // Maintenance file already removed
      if ( !file_exists($maintenance) ) return;

      // Remove the file
      unlink($maintenance);
    }

    /*
    @method admin_notice
    @since 0.0.1
    @description Adds a notice to the admin section
    @param $type {String} 'updated' | 'error'
    @param $message {String}
    @returns {Void}
    */
    public function admin_notice( $msg, $type = 'updated' )
    {
      array_push( $this->notices, array(
        'type' => $type,
        'content' => $msg,
      ) );
    }

    /*
    @method admin_error
    @since 0.0.1
    @description Adds an error notice to the admin section
    @param $message {String}
    @returns {Void}
    */
    public function admin_error( $msg )
    {
      array_push( $this->notices, array(
        'type' => 'error',
        'content' => $msg,
      ) );
      error_log( sprintf( __('LVL99_DBS Error: %s', 'lvl99-dbs' ), $msg ) );
    }

    /*
    @method sql_tablelist
    @since 0.0.1
    @description Gets an array of the tables within the SQL database
    @returns {Array}
    */
    public function sql_tablelist()
    {
      global $wpdb;

      $this->check_admin();

      $tables = array();
      $wpdb->query("SET NAMES 'utf8'");

      // Get all of the tables
      $tables = array();
      $result = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
      foreach( $result as $row )
      {
        // Only show the tables which share the same prefix as this site
        if ( preg_match( '/^'.$wpdb->prefix.'/i', $row[0] ) ) $tables[] = $row[0];
      }

      return $tables;
    }

    /*
    @method sql_filelist
    @since 0.0.1
    @description Gets an array of the files within the backup-db folder
    @returns {Array}
    */
    public function sql_filelist()
    {
      $this->check_admin();

      $files = array();
      $path = $this->get_option_path();

      // Check the directory path for SQL files exists
      if ( !file_exists($path) && !is_dir($path) )
      {
        $this->admin_error( sprintf( __('Error: Invalid path set', 'lvl99-dbs'), $path ) );
        return $files;
      }

      // Get the list of files within the directory
      $dir_files = scandir( $path );
      foreach( $dir_files as $file )
      {
        if ( preg_match( '/\.sql(\.gz(ip)?)?$/i', $file ) != FALSE )
        {
          array_push( $files, array(
            'file_name' => $file,
            'size' => filesize( $path . $file ),
            'created' => filectime( $path . $file ),
            'modified' => filemtime( $path . $file ),
          ) );
        }
      }

      return $files;
    }

    /*
    @method build_file_name
    @since 0.0.1
    @description Builds the file name from the file_name option
    @returns {String}
    */
    public function build_file_name( $file_name = FALSE )
    {
      if ( !$file_name || !is_string($file_name) ) $file_name = $this->get_option('file_name');

      // Replace tags
      $output_file_name = $this->replace_tags( $file_name, array(
        'url' => untrailingslashit( preg_replace('/[a-z]+\:\/\//', '', home_url('/') ) ),
        'env' => defined('WP_ENV') ? WP_ENV : '',
        'database' => DB_NAME,
      ) );

      if ( !preg_match('/\.sql$/i', $output_file_name ) ) $output_file_name .= '.sql';
      return $output_file_name;
    }

    /*
    @method save_sql_file
    @since 0.0.1
    @description Saves the tables to a file at the path
    @param {Mixed} $tables '*' {String} will save all the tables, an {Array} with table names will only save those selected tables
    @param {String} $compression 'none' will save as text/plain, 'gzip' will save as a gz file
    @param {Mixed} $file_name File name format as a {String} (if {Boolean} FALSE will use default)
    @returns {Boolean}
    */
    public function save_sql_file( $tables = '*', $compression = 'none', $file_name = FALSE )
    {
      global $wpdb;

      $this->check_admin();

      // Set the file's name and save path
      $file_name = $this->build_file_name( $file_name );
      $file = $this->get_option_path() . $file_name;
      $pathinfo = pathinfo($file);

      //get all of the tables
      if ( $tables == '*' )
      {
        $tables = array();
        $result = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
        foreach( $result as $row )
        {
          // Only save the tables which share the same prefix as this site
          if ( preg_match( '/^'.$wpdb->prefix.'/i', $row[0] ) ) $tables[] = $row[0];
        }
      }
      else
      {
        $tables = is_array($tables) ? $tables : explode(',', $tables);
      }

      // Create the return object
      $return = '
/*
  WP Database Backup
  Created with LVL99 Database Sync v' . $this->version . '

  Site: ' . get_bloginfo('name') . '
  Address: ' . home_url('/') . '
  Path: ' . ABSPATH . '

  File: ' . $file_name . '
  Created: ' . date( 'Y-m-d H:i:s' ) . '
  Tables:
    -- ' . implode("\n    -- ", $tables) . '
*/

';

      // Cycle through
      foreach( $tables as $table )
      {
        $result = $wpdb->get_results( 'SELECT * FROM ' . $table, ARRAY_N );
        $num_fields = sizeof( $wpdb->get_results( 'DESCRIBE ' . $table, ARRAY_N ) );

        $return .= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = $wpdb->get_results( 'SHOW CREATE TABLE ' . $table, ARRAY_N );
        $return .= "\n\n".$row2[0][1].";\n\n";

        foreach ( $result as $i => $row )
        {
          $return.= 'INSERT INTO '.$table.' VALUES(';
          for( $j=0; $j<$num_fields; $j++ )
          {
            $row[$j] = addslashes( $row[$j] );
            $row[$j] = str_replace( "\n", "\\n", $row[$j] );
            if ( isset($row[$j]) )
            {
              $return .= '"'.$row[$j].'"' ;
            }
            else
            {
              $return .= '""';
            }
            if ( $j<($num_fields-1) ) $return .= ',';
          }
          $return .= ");\n";
        }
        $return .= "\n\n\n";
      }

      // Compression
      if ( $compression == 'gzip' )
      {
        $file .= '.gz';
        $file_name .= '.gz';
        $return = gzencode( $return );
      }

      // Create subfolder if need to
      if ( !file_exists($pathinfo['dirname']) ) mkdir( $pathinfo['dirname'], 0755, TRUE );

      // Save file
      $handle = fopen( $file, 'wb' );
      fwrite( $handle, $return );
      fclose( $handle );

      $this->admin_notice( sprintf( __('Database was successfully backed up to <strong><code>%s</code></strong>', 'lvl99-dbs'), $file_name ) );
      return TRUE;
    }

    /*
    @method load_sql_file
    @since 0.0.1
    @description Loads an SQL file to update the database with
    @param {String} $file The file name of the file located at the path to load
    @param {Mixed} $filters {Boolean} false if no filters, {Array} with 'search' and 'replace' arrays
    @param {Boolean} $dryrun Whether to apply to the database after finishing or not
    @param {Boolean} $savetonewfile Saves the filtered SQL to a new file
    @returns {Boolean}
    */
    public function load_sql_file( $file = FALSE, $filters = FALSE, $dryrun = FALSE, $savetonewfile = FALSE )
    {
      global $wpdb;

      $this->check_admin();

      if ( !$file )
      {
        $this->admin_error( _x('No file was selected', 'No file specified for load_sql_file operation', 'lvl99-dbs') );
        return FALSE;
      }

      // Make sure file exists in the path
      $file_name = basename($file);
      $file = $this->get_option_path() . $file;

      if ( !file_exists($file) )
      {
        $this->admin_error( sprintf( __('File does not exist at <strong><code>%s</code></strong>', 'lvl99-dbs'), $file ) );
        return FALSE;
      }

      // Enable maintenance mode (only if actually applying to the DB)
      if ( !$dryrun )
      {
        $this->enable_maintenance();
      }
      else
      {
        $this->log( "Dry run has been selected: the following actions will not be applied to the live database." );
      }

      // Temporary variable, used to store current query
      $templine = '';

      // Read in entire file
      $lines = file($file);

      // Uncompress
      if ( preg_match( '/\.gz(ip)?$/i', $file ) != FALSE )
      {
        $lines = gzdecode( implode('', $lines) );
        $lines = explode( "\n", $lines );
      }

      // Filters
      if ( is_array($filters) && array_key_exists( 'search', $filters ) && array_key_exists( 'replace', $filters ) )
      {
        // Put into single line for processing
        // $lines = implode( "\n", $lines );

        // increase time out limit
        @set_time_limit( 60 * 10 );

        // try to push the allowed memory up, while we're at it
        @ini_set( 'memory_limit', '1024M' );

        $this->log( 'Filters detected' );
        $this->log( $filters );

        // @TODO may need to chunk lines to avoid any lengthy timeouts, depending on how big the SQL file is
        $new_rows = array();
        $row = '';
        $table_name = '';
        $table_cols = array();
        $table_col_count = 0;

        // Stats
        $stats = array(
          'line_count' => 0,
          'lines_processed' => 0,
          'row_count' => 0,
          'rows_skipped' => 0,
          'rows_processed' => 0,
          'tables_count' => 0,
          'inserts_count' => 0,
          'plain_data_count' => 0,
          'serialized_data_count' => 0,
          'valid_queries' => 0,
          'errors_count' => array(
            'table_col_zero' => 0,
            'no_column_parse' => 0,
            'incorrect_column_count' => 0,
            'invalid_queries' => 0,
          ),
        );

        // Process all the necessary rows
        foreach( $lines as $num => $line )
        {
          $stats['line_count']++;

          // Condense lines into single SQL queries (or rows)
          $row .= "\n" . $line;
          if ( !preg_match( '/;$/', $line ) )
          {
            // Row is multi-lined so combine with previous (i.e. move on to the next line)
            continue;
          }

          $stats['lines_processed']++;

          // Remove any whitespace at start
          $row = ltrim($row);

          // Match to table spec
          if ( preg_match( '/^CREATE TABLE ([^\s]+) \(((?:\s*`[^`]+` [^\r\n]+)+)/s', $row, $table_matches ) )
          {
            $table_name = 'unidentified_table_name';

            if ( count($table_matches) > 0 )
            {
              // Save reference to table name
              $table_name = preg_replace( "/[`'\"]+/", '', $table_matches[1] );

              // Process columns
              $table_cols = array();
              if ( count($table_matches) > 2 )
              {
                $table_col_specs = preg_split( "/,\s+/", trim($table_matches[2]) );

                // Save references to columns names
                foreach( $table_col_specs as $i => $col )
                {
                  if ( preg_match( '/^[`\'"]?([^`\'"]+)[`\'"]?\s/', $col, $table_col_name ) )
                  {
                    $table_cols[$i] = $table_col_name[1];
                  }
                }
                $table_col_count = count($table_cols);
              }
              else
              {
                $stats['errors_count']['table_col_zero']++;
                $table_col_count = 0;
              }

              $this->log( "Detected table schema for {$table_name} with {$table_col_count} columns at SQL line #{$num}" );
              $this->log( $row );

              $stats['tables_count']++;
              $stats['rows_processed']++;
            }

            $new_rows[] = $row;
            $row = '';
            $stats['row_count']++;
            continue;
          }

          // Check for column values
          $check_cols = preg_match( '/^INSERT INTO ([^\s]+) VALUES\((.*)\)\;/s', $row, $row_matches );

          // Don't need to check the column data, so continue on, soldier...
          if ( !$check_cols )
          {
            $this->log( "Skipped search/replace on SQL line #{$num}..." );
            $this->log( $row );
            $stats['rows_skipped']++;
            $new_rows[] = $row;
            $row = '';
            $stats['row_count']++;
            continue;
          }

          $stats['inserts_count']++;

          // Process each column
          $columns = array();
          $has_columns = preg_match( "/(?:(?:,|^)(?<!\\\\)\".*?(?<!\\\\)\"(?:(?=,)|$)){" . $table_col_count . "}/s", $row_matches[2] );
          preg_match_all( "/(?:,|^)(?<!\\\\)\"(.*?)(?<!\\\\)\"(?:(?=,)|$)/s", $row_matches[2], $columns );
          $this->log( 'has_columns=' . ($has_columns ? 'yes' : 'no') . ' expected_columns='.$table_col_count );

          // Prep $columns for further processing
          if ( $has_columns )
          {
            // Relies on single capturing group in above preg_match_all regexp
            if ( !empty($columns) && count($columns) == 2 ) $columns = $columns[1];
          }
          else
          {
            $this->log( "Skipped search/replace on SQL line #{$num}: Couldn't detect table columns to filter" );
            $this->log( $row );
            $stats['errors_count']['no_column_parse']++;
            $stats['rows_skipped']++;

            $new_rows[] = $row;
            $row = '';
            $stats['row_count']++;
            continue;
          }

          // Process the columns
          if ( count($columns) == $table_col_count )
          {
            $this->log( "Detected row has {$table_col_count} columns for `{$table_name}` at SQL line #{$num}" );

            // Process each column
            foreach ( $columns as $i => $column )
            {
              // Attempt to unserialize column data...
              if ( !$this->is_serialized( stripslashes($column) ) )
              {
                $columns[$i] = str_replace( $filters['search'], $filters['replace'], $column );
                $this->log( "Basic string search/replace performed on `{$table_name}`.`{$table_cols[$i]}` (column #{$i}) at SQL line #{$num}" );
                $this->log( $columns[$i] );
                $stats['plain_data_count']++;
              }
              else // Serialized data...
              {
                $is_assoc = preg_match( '/^a/', $column );
                $data = unserialize( stripslashes($column) );

                $this->log( "Serialized data found in `{$table_name}`.`{$table_cols[$i]}` (column #{$i}) at SQL line #{$num}:" );
                $this->log( $data );

                // Convert to text JSON to search and replace on
                $data = json_encode( $data, JSON_UNESCAPED_SLASHES );
                $data = str_replace( $filters['search'], $filters['replace'], $data );

                // Decode back to PHP array/object
                $data = json_decode( $data, $is_assoc );

                $this->log( "Performed search/replace on serialized data:" );
                $this->log( $data );

                // Reserialize data
                $columns[$i] = addslashes( serialize($data) );
                $stats['serialized_data_count']++;
              }
            }

            // Put the columns back together
            $row = "INSERT INTO {$table_name} VALUES (\"" . implode( '","', $columns ) . "\");";

            $this->log( "New SQL insert row generated for SQL line #{$num}:" );
            $this->log( $row );
            $stats['rows_processed']++;
          }
          else
          {
            $this->log( "Skipped search/replace on SQL line #{$num}: Columns detected doesn't match table schema (table_col_count={$table_col_count}, columns_count=".count($columns).")" );
            $this->log( $row );
            $stats['rows_skipped']++;
            $stats['errors_count']['incorrect_column_count']++;
          }

          // Add row to the collection and reset for determining next row
          $new_rows[] = $row;
          $row = '';
          $stats['row_count']++;
        }

        $lines = $new_rows;
      }

      // Apply to database only if it's not a Dry Run
      if ( !$dryrun )
      {
        // Loop through each line
        foreach ($lines as $line)
        {
          // Skip it if it's a comment
          if (substr($line, 0, 2) == '--' || $line == '') continue;

          // Add this line to the current segment
          $templine .= $line;

          // If it has a semicolon at the end, it's the end of the query
          if ( substr(trim($line), -1, 1) == ';' )
          {
            // Perform the query
            // -- Error
            if ( $wpdb->query( $templine ) === FALSE )
            {
              $stats['errors_count']['invalid_queries']++;
              wp_die( sprintf( __('LVL99 DBS Error: Something went wrong when processing the SQL file (%s)', 'lvl99-dbs'), $wpdb->last_error ) );
            }
            else // -- Success
            {
              // Reset temp variable to empty
              $templine = '';
              $stats['valid_queries']++;
            }
          }
        }
      }

      // Save to new file (always gzipped)
      if ( $savetonewfile )
      {
        // Set the new file name
        $fn_info = pathinfo($file_name);
        $new_file_name = preg_replace( '/ \(filters applied \d+\)/i', '', $fn_info['filename'] ) . ' (filters applied '. date('U') . ').sql.gz';
        $new_file = $this->get_option_path() . $new_file_name;

        // Add filter information
        $new_file_contents = '
/*
  WP Database Backup
  Created with LVL99 Database Sync v' . $this->version . '

  File: ' . $new_file_name . '
  Created: ' . date( 'Y-m-d H:i:s' ) . '
  Filters applied:
';
        foreach( $filters['search'] as $num => $filter )
        {
          $new_file_contents .= '    -- `' . $filter . '` --> `' . $filters['replace'][$num] . '`'."\n";
        }
        $new_file_contents .= '
*/

';
        $new_file_contents .= implode( "\n", $lines );

        // Write to the new file (gzipped)
        $nfo = fopen( $new_file, 'wb' );
        fwrite( $nfo, gzencode( $new_file_contents ) );
        fclose($nfo);

        $this->log( "Saved filtered SQL to {$new_file_name}" );
      }

      // Get time taken
      $end = microtime( TRUE );
      $time = round($end-$this->start, 2) . ' ' . __('seconds', 'Load SQL process time taken unit seconds', 'lvl99-dbs');

      // Stats
      $this->log( "Operation competed and took " . $time );
      $this->log( $stats );

      // Success message
      if ( !$dryrun )
      {
        if ( defined('WP_CACHE') )
        {
          $this->admin_notice( sprintf( __('Database was successfully restored from <strong><code>%s</code></strong> (time taken: %s). If you have a caching plugin, it is recommended you flush your database cache now.', 'lvl99-dbs'), $file_name, $time ) );
        }
        else
        {
          $this->admin_notice( sprintf( __('Database was successfully restored from <strong><code>%s</code></strong> (time taken: %s)', 'lvl99-dbs'), $file_name, $time ) );
        }
      }
      else
      {
        if ( $savetonewfile )
        {
          $this->admin_notice( sprintf( __('SQL file <strong><code>%s</code></strong> (time taken: %s) has been processed with the "Dry Run" setting and outputted to <strong><code>%s</code></strong>. No changes have been made to your database.', 'lvl99-dbs'), $file_name, $time, $new_file_name ) );
        }
        else
        {
          $this->admin_notice( sprintf( __('SQL file <strong><code>%s</code></strong> (time taken: %s) has been processed with the "Dry Run" setting. No changes have been made to your database.', 'lvl99-dbs'), $file_name, $time ) );
        }
      }

      // Disable maintenance (only if actually applying to the DB)
      if ( !$dryrun ) $this->disable_maintenance();

      return TRUE;
    }

    /*
    Test search/replace ordering

    @method search_replace
    @since 0.1.1
    @param {Mixed} $search The array/string to search (same accepted for preg_replace)
    @param {Mixed} $replace The array/string to search (same accepted for preg_replace)
    @param {Mixed} $subject The object/array to search
    @returns {Mixed} $output
    */
    public function search_replace( $search, $replace, $subject )
    {
      // S/R multiple
      if ( is_array($search) )
      {
        $is_array_replace = is_array($replace);

        foreach( $search as $i => $search_term )
        {
          if ( $is_array_replace )
          {
            $replace_term = array_key_exists( $i, $replace ) ? $replace[$i] : '';
            $subject = str_replace( $search_term, $replace_term, $subject );
          }
          else
          {
            $subject = str_replace( $search_term, $replace, $subject );
          }
        }

        return $subject;
      }
      else
      {
        return str_replace( $search, $replace, $subject );
      }
    }

    /*
    Recursively search/replace on an object/array

    @method recursive_search_replace
    @since 0.1.1
    @param {Mixed} $search The array/string to search (same accepted for preg_replace)
    @param {Mixed} $replace The array/string to search (same accepted for preg_replace)
    @param {Mixed} $subject The object/array to search
    @returns {Mixed} $subject
    */
    // public function recursive_search_replace( $search, $replace, $subject )
    // {
    //   // Array or object
    //   if ( is_array($subject) || is_object($subject) )
    //   {
    //     // Iterate through array/object as array
    //     $is_array = is_array($subject);
    //     foreach ( $subject as $key => $item )
    //     {
    //       // Search and replace
    //       $val = $this->recursive_search_replace( $search, $replace, $item );

    //       // Set subject's item
    //       if ( $is_array )
    //       {
    //         $subject[$key] = $val;
    //       }
    //       else
    //       {
    //         $subject->$key = $val;
    //       }
    //     }

    //     return $subject;
    //   }
    //   else
    //   {
    //     return preg_replace( $search, $replace, $subject );
    //   }
    // }

    /*
    @method download_sql_file
    @since 0.0.1
    @description Downloads an SQL file
    @returns {Boolean}
    */
    public function download_sql_file( $file = FALSE )
    {
      $this->check_admin();

      if ( !$file )
      {
        $this->admin_error( _x('No file was selected', 'Error loading an SQL file', 'lvl99-dbs') );
        return FALSE;
      }

      // Make sure file exists in the path
      $file_name = basename($file);
      $file = $this->get_option_path() . $file_name;
      if ( !file_exists($file) )
      {
        $this->admin_error( sprintf( __('<strong><code>%s</code></strong> does not exist on the server.', 'lvl99-dbs'), $file_name ) );
        return FALSE;
      }

      // Send the file to the user's browser
      header('Cache-Control: public');
      header('Content-Description: File Transfer');
      header('Content-Disposition: attachment; filename='.$file_name);
      header('Content-Type: application/zip');
      readfile($file);
      exit();
    }

    /*
    @method delete_sql_file
    @since 0.0.1
    @description Deletes an SQL file from the server
    @returns {Boolean}
    */
    public function delete_sql_file( $file = FALSE )
    {
      $this->check_admin();

      if ( !$file )
      {
        $this->admin_error( _x('No file was selected', 'Error deleting an SQL file', 'lvl99-dbs') );
        return FALSE;
      }

      // Make sure file exists in the path
      $file_name = basename($file);
      $file = $this->get_option_path() . $file;
      if ( !file_exists( $file) )
      {
        $this->admin_error( sprintf( __('File does not exist at <strong><code>%s</code></strong>', 'lvl99-dbs'), $file ) );
        return FALSE;
      }

      // Delete the file
      if ( unlink( $file ) )
      {
        $this->admin_notice( sprintf( __('<strong><code>%s</code></strong> was successfully deleted', 'lvl99-dbs'), $file_name ) );
        return TRUE;
      }
      else
      {
        $this->admin_error( sprintf( __('Could not remove <strong><code>%s</code></strong> from the server. Please check file and folder permissions', 'lvl99-dbs'), $file_name ) );
        return FALSE;
      }
    }

    /*
    @method route_save
    @since 0.0.1
    @description Saves tables to an SQL file
    @returns {Void}
    */
    public function route_save()
    {
      $this->check_admin();

      // Tables
      $tables = '*';
      if ( isset($this->route['request']['post']['tables']) )
      {
        if ( $this->route['request']['post']['tables'] == 'some' )
        {
          $tables = $this->route['request']['post']['tables_selected'];
        }
      }

      // Compression format
      $compression = $this->get_option('compress_format');
      if ( isset($this->route['request']['post']['compression']) )
      {
        if ( $this->route['request']['post']['compression'] == 'gzip' )
        {
          $compression = 'gzip';
        }
      }

      // File name format
      $file_name = $this->get_option('file_name');
      if ( isset($this->route['request']['post']['file_name']) )
      {
        $file_name = $this->sanitise_option_file_name($this->route['request']['post']['file_name']);
      }

      $this->save_sql_file( $tables, $compression, $file_name );
    }

    /*
    @method route_load
    @since 0.0.1
    @description Loads SQL file to the database
    @returns {Void}
    */
    public function route_load()
    {
      $this->check_admin();

      // Error
      if ( !isset($this->route['request']['post']['file']) && !isset($this->route['request']['post']['fileupload']) )
      {
        $this->admin_error( _x('No file was selected nor uploaded.', 'Error loading/uploading SQL file', 'lvl99-dbs') );
        return;
      }

      // Dry run?
      $dryrun = FALSE;
      if ( isset($this->route['request']['post']['dryrun']) ) $dryrun = TRUE;

      // Save filtered SQL to new file
      $savetonewfile = FALSE;
      if ( isset($this->route['request']['post']['savetonewfile']) ) $savetonewfile = TRUE;

      // Detect if any filters are included and to prepare the object
      $filters = FALSE;
      if ( isset($this->route['request']['post']['filters'])  &&
         !empty($this->route['request']['post']['filters']) )
      {
        $post_filters = $this->route['request']['post']['filters'];
        $_filters = array(
          'search' => array(),
          'replace' => array(),
        );

        // Format the post-processing object
        foreach( $post_filters as $id => $filter )
        {
          // Skip filter if invalid (either no input or output set)
          if ( !array_key_exists( 'input', $filter ) || !array_key_exists( 'output', $filter ) )
            continue;

          $input = $this->sanitise_sql($filter['input']);
          $output = $this->sanitise_sql($filter['output']);

          // Ensure filters are formatted properly for regex
          // if ( !preg_match( '/^\//', $input ) )
          // {
          //   $input = '/' . preg_quote($input, '/') . '/';
          // }
          // else
          // {
          //   $input = stripslashes($input);
          // }

          $_filters['search'][] = $input;
          $_filters['replace'][] = $output;
        }

        if ( count($_filters['search']) > 0 && count($_filters['replace']) > 0 )
          $filters = $_filters;
      }

      // Use an existing file hosted on the server
      if ( isset($this->route['request']['post']['file']) && !empty($this->route['request']['post']['file']) )
      {
        $this->load_sql_file( $this->route['request']['post']['file'], $filters, $dryrun, $savetonewfile );
      }

      // Use an uploaded file
      if ( isset($this->route['request']['post']['fileupload']) && !empty($this->route['request']['post']['fileupload']) )
      {
        $uploaded_file = $this->route['request']['post']['fileupload']['name'];
        $upload_path = $this->get_option_path() . $uploaded_file;

        // Checks
        // -- Duplicate name
        if ( file_exists($upload_path) )
        {
          $uploaded_file = preg_replace( '/(\.sql(\.gz)?)$/', md5(time()) . '$1', $uploaded_file );
          $upload_path = $this->get_option_path() . $uploaded_file;
        }

        // -- PHP file
        if ( strstr($uploaded_file, '.php') != FALSE )
        {
          admin_error( __('PHP files may not be uploaded.', 'lvl99-dbs') );
          return;
        }

        // Move the uploaded file
        if ( move_uploaded_file( $upload_path ) )
        {
          admin_notice( sprintf( __('<strong><code>%s</code></strong> was successfully uploaded.', 'lvl99-dbs'), $uploaded_file ) );
          $this->load_sql_file( $uploaded_file, $filters, $dryrun, $savetonewfile );
        }
        else
        {
          admin_error( __('Could not upload the file. Please check your server\'s permissions.', 'lvl99-dbs') );
          return;
        }
      }
    }

    /*
    @method route_download
    @since 0.0.1
    @description Downloads an SQL file
    @returns {Void}
    */
    public function route_download()
    {
      $this->check_admin();

      // Use an existing file hosted on the server
      if ( isset($this->route['request']['get']['file']) && !empty($this->route['request']['get']['file']) )
      {
        $this->download_sql_file( $this->route['request']['get']['file'] );
      }
    }

    /*
    @method route_delete
    @since 0.0.1
    @description Deletes an SQL file
    @returns {Void}
    */
    public function route_delete()
    {
      $this->check_admin();

      // Use an existing file hosted on the server
      if ( isset($this->route['request']['get']['file']) && !empty($this->route['request']['get']['file']) )
      {
        $this->delete_sql_file( $this->route['request']['get']['file'] );
        wp_redirect( $this->route['referrer'] );
        exit();
      }
    }

    /*
    @method admin_menu
    @since 0.0.1
    @description Runs when initialising admin menu
    @returns {Void}
    */
    public function admin_menu()
    {
      $this->check_admin();

      add_management_page( __('Database Sync', 'lvl99-dbs'), __('Database Sync', 'lvl99-dbs'), 'activate_plugins', 'lvl99-dbs', array( $this, 'view_admin_index' ) );
      add_options_page( __('Database Sync', 'lvl99-dbs'), __('Database Sync', 'lvl99-dbs'), 'activate_plugins', 'lvl99-dbs-options', array( $this, 'view_admin_options' ) );
    }

    /*
    @method admin_plugin_links
    @since 0.0.1
    @description Adds extra links on the plugins list
    @returns {Void}
    */
    public function admin_plugin_links( $links = array() )
    {
      $plugin_links = array(
        '<a href="tools.php?page=lvl99-dbs&action=save">Save</a>',
        '<a href="tools.php?page=lvl99-dbs&action=load">Load</a>',
        '<a href="options-general.php?page=lvl99-dbs-options">Options</a>',
      );
      return array_merge( $plugin_links, $links );
    }

    /*
    @method admin_notices
    @since 0.0.1
    @description Displays the notices in the admin section
    @returns {Void}
    */
    public function admin_notices()
    {
      $this->check_admin();

      if ( count($this->notices) > 0 )
      {
        foreach( $this->notices as $notice )
        {
?>
<div class="<?php echo esc_attr($notice['type']); ?>">
  <p><?php echo $notice['content']; ?></p>
</div>
<?php
        }
      }
    }

    /*
    @method view_admin_index
    @since 0.0.1
    @description Shows the view for the admin page to save/load SQL database files
    @returns {Void}
    */
    public function view_admin_index()
    {
      $this->check_admin();

      $tablelist = $this->sql_tablelist();
      $filelist = $this->sql_filelist();
      include( trailingslashit($this->plugin_dir) . 'views/admin-index.php' );
    }

    /*
    @method view_admin_options
    @since 0.0.1
    @description Shows the view for the options admin page
    @returns {Void}
    */
    public function view_admin_options()
    {
      $this->check_admin();
      include( trailingslashit($this->plugin_dir) . 'views/admin-options.php' );
    }

    /*
    @method format_file_size
    @since 0.0.2
    @description Formats a file size (given in byte value) with KB/MB signifier
    @returns {String}
    */
    public function format_file_size( $input, $decimals = 2 )
    {
      $input = intval( $input );
      if ( $input < 1000000 ) return round( $input/1000 ) . 'KB';
      if ( $input < 1000000000 ) return round( ($input/1000)/1000, $decimals ) . 'MB';
      return $input;
    }


    /*
    Checks if given input string is serialized data or not

    @method is_serialized
    @param {String} $input
    @returns {Boolean}
    */
    public function is_serialized( $input )
    {
      return ( $input == serialize(FALSE) || @unserialize($input) !== FALSE );
    }

    // See: http://php.net/manual/en/function.unserialize.php#71846
    // @returns {Boolean}
    public function wd_check_serialization( $string, &$errmsg )
    {
        $str = 's';
        $array = 'a';
        $integer = 'i';
        $any = '[^}]*?';
        $count = '\d+';
        $content = '"(?:\\\";|.)*?";';
        $open_tag = '\{';
        $close_tag = '\}';
        $parameter = "($str|$array|$integer|$float|$any):($count)" . "(?:[:]($open_tag|$content)|[;])";
        $preg = "/$parameter|($close_tag)/";
        if( !preg_match_all( $preg, $string, $matches ) )
        {
            $errmsg = 'not a serialized string';
            return false;
        }
        $open_arrays = 0;
        foreach( $matches[1] AS $key => $value )
        {
            if( !empty( $value ) && ( $value != $array xor $value != $str xor $value != $integer ) )
            {
                $errmsg = 'undefined datatype';
                return false;
            }
            if( $value == $array )
            {
                $open_arrays++;
                if( $matches[3][$key] != '{' )
                {
                    $errmsg = 'open tag expected';
                    return false;
                }
            }
            if( $value == '' )
            {
                if( $matches[4][$key] != '}' )
                {
                    $errmsg = 'close tag expected';
                    return false;
                }
                $open_arrays--;
            }
            if( $value == $str )
            {
                $aVar = ltrim( $matches[3][$key], '"' );
                $aVar = rtrim( $aVar, '";' );
                if( strlen( $aVar ) != $matches[2][$key] )
                {
                    $errmsg = 'stringlen for string not match';
                    return false;
                }
            }
            if( $value == $integer )
            {
                if( !empty( $matches[3][$key] ) )
                {
                    $errmsg = 'unexpected data';
                    return false;
                }
                if( !is_integer( (int)$matches[2][$key] ) )
                {
                    $errmsg = 'integer expected';
                    return false;
                }
            }
        }
        if( $open_arrays != 0 )
        {
            $errmsg = 'wrong setted arrays';
            return false;
        }
        return true;
    }

    /*
    New log

    @method new_log
    @returns {Void}
    */
    private function new_log ()
    {
      // Only create if show_debug is enabled
      if ( $this->get_option('show_debug') )
      {
        // Log file
        if ( !file_exists( $this->log_path( $this->log_file ) ) )
        {
          $fo = fopen( $this->log_path( $this->log_file ), 'wb' );

          // Mark logging session
          // fwrite( $fo, "---------------------------------------------------------------------\n### LVL99 Database Sync progress log started: ". date('Y-m-d H:i:s') ." ###\n---------------------------------------------------------------------\n" );

          fclose($fo);
        }
      }
    }

    /*
    Log to file

    @method log
    @param {String} $msg The message to log to the file
    @returns {Void}
    */
    private function log ( $msg )
    {
      if ( $this->get_option('show_debug') )
      {
        if ( is_array($msg) || is_object($msg) )
        {
          ob_start();
          var_dump( $msg );
          $msg = ob_get_contents() . "\n";
          ob_end_clean();
        }

        if ( !is_array($msg) && !is_object($msg) )
        {
          $fo = fopen( $this->log_path( $this->log_file ), 'ab' );
          fwrite( $fo, date('[H:i:s] ') . $msg ."\n" );
          fclose($fo);
        }
      }
    }

    /*
    The log file path

    @method log_path
    @returns {String}
    */
    private function log_path ( $file = '' )
    {
      return trailingslashit( $this->plugin_dir ) . trailingslashit('logs') . $file;
    }

    /*
    The log file URL

    @method log_url
    @returns {String}
    */
    public function log_url ( $file = '' )
    {
      return trailingslashit( $this->plugin_url ) . trailingslashit('logs') . $file;
    }

    /*
    Display log file contents

    @method log_output
    @returns {String}
    */
    public function log_contents ()
    {
      $this->check_admin();

      if ( file_exists( $this->log_path( $this->log_file ) ) )
      {
        $file = file_get_contents( $this->log_path( $this->log_file ) );
        return $file;
      }
    }


    public function preg_errtxt($errcode)
    {
        static $errtext;

        if (!isset($errtxt))
        {
            $errtext = array();
            $constants = get_defined_constants(true);
            foreach ($constants['pcre'] as $c => $n) if (preg_match('/_ERROR$/', $c)) $errtext[$n] = $c;
        }

        return array_key_exists($errcode, $errtext)? $errtext[$errcode] : NULL;
    }
  }
}

// The instance of the plugin
$lvl99_dbs = new LVL99_DBS();
define( 'WP_LVL99_DBS', $lvl99_dbs->version );