<?php

/*
Plugin Name: LVL99 Database Sync
Plugin URI: http://www.lvl99.com/code/database-sync/
Description: Allows you to easily save your WP database to an SQL file, and to also restore a database from an SQL file.
Author: Matt Scheurich
Author URI: http://www.lvl99.com/
Version: 0.0.1
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
		const VERSION = '0.0.1';
		
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
		@method __construct
		@since 0.0.1
		@description PHP magic method which runs when class is created
		@returns {Void}
		*/
		public function __construct()
		{
			$this->plugin_dir = dirname(__FILE__);

			// Actions/filters
			register_activation_hook( __FILE__, array( &$this, 'activate' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'deactivate' ) );
			add_action( 'init', array( &$this, 'i18n' ) );
			add_action( 'admin_init', array( &$this, 'initialise' ) );
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( &$this, 'admin_plugin_links' ) );
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
				error_log( _x( sprintf('LVL99_DBS: Non-admin attempted operation %s', $callee[1]['function']), 'lvl99-dbs'), 'wp error_log' );
				exit( __('Error: You must have administrator privileges to operate this functionality', 'lvl99-dbs') );
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
			load_plugin_textdomain( 'lvl99-dbs', FALSE, basename( dirname(__FILE__) ) . '/languages' );
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
			$_plugin_installed = get_option( '_lvl99-dbs/installed', FALSE );
			$_plugin_version = get_option( '_lvl99-dbs/version', self::VERSION );
			if ( !$_plugin_installed )
			{
				// Set the initial options
				foreach ( $this->default_options as $name => $value )
				{
					add_option( 'lvl99-dbs/' . $name, $value );
				}
			}
			
			// Mark that the plugin is now installed
			update_option( '_lvl99-dbs/installed', TRUE );
			update_option( '_lvl99-dbs/version', self::VERSION );
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
			$_plugin_version = get_option( '_lvl99-dbs/version', self::VERSION );
			
			if ( $_plugin_installed )
			{
				switch ($_plugin_version)
				{
					default:
						break;
					
					case FALSE:
						break;
						
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
			if ( !$_plugin_version ) $_plugin_version = get_option( '_lvl99-dbs/version', self::VERSION );
			
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
			
			// Plugin scripts and styles
			wp_enqueue_style( 'lvl99-dbs', plugins_url( 'css/lvl99-dbs.css', __FILE__ ), FALSE, self::VERSION, 'all' );
		}
		
		/*
		@method load_options
		@since 0.0.1
		@description Loads all options into the class
		@returns {Void}
		*/
		public function load_options()
		{
			// Default options
			$this->default_options = array(
				'path' => array(
					'sanitise_callback' => NULL,
					'default' => trailingslashit(WP_CONTENT_DIR) . 'backup-db/',
					'field_type' => 'text',
					'label' => _x('SQL file folder path', 'field label: path', 'lvl99-dbs'),
					'help' => '<p>'._x('The folder must already be created for you to successfully reference it here and have permissions for PHP to write to.<br/>Consider referencing to a folder that exists outside your www/public_html folder', 'field help: path', 'lvl99-dbs' ).'</p>',
				),
				'file_name' => array(
					'sanitise_callback' => array( &$this, 'sanitise_option_file_name' ),
					'default' => '{date:YmdHis} {env} {database}.sql',
					'field_type' => 'text',
					'label' => _x('SQL file name format', 'field label: file_name', 'lvl99-dbs'),
					'help' => '<p>' . _x('Tags you can use within the file name:', 'field help: file_name', 'lvl99-dbs') . '</p>
<ul><li><code>{date:<i>...</i>}</code> ' . _x('Replace <code>...</code> with a string representing the date output according to <a href="http://au1.php.net/manual/en/function.date.php" target="_blank">PHP\'s date() function</a>', 'field help: file_name {code} tag', 'lvl99-dbs') . '</li>
<li><code>{env}</code> ' . _x('The environment that the site is running in (references constant <code>WP_ENV</code>)', 'field help: file_name {env} tag', 'lvl99-dbs') . '</li>
<li><code>{database}</code> ' . _x('The name of the database', 'field help: file_name {database} tag', 'lvl99-dbs').'</li>
<li><code>{url}</code> ' . _x('The URL of the website (references constant <code>WP_HOME</code>)', 'field help: file_name {url} tag', 'lvl99-dbs') . '</li></ul>',
				),
				'compress_format' => array(
					'sanitise_callback' => array( &$this, 'sanitise_option_compress_format' ),
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
			);
		
			// Get the saved options
			foreach ( $this->default_options as $name => $option  )
			{
				$this->options[$name] = get_option( 'lvl99-dbs/'.$name, $option['default'] );
				register_setting( 'lvl99-dbs', 'lvl99-dbs/'.$name, $option['sanitise_callback'] );
			}
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
		@method get_option
		@since 0.0.1
		@description Gets an option
		@param {String} $name The name of the option
		@param {Mixed} $default The default value to return if it is not set
		@returns {Mixed}
		*/
		public function get_option( $name = FALSE, $default = NULL )
		{
			if ( !$name || !array_key_exists($name, $this->options) ) return $default;
			return isset($this->options[$name]) ? $this->options[$name] : $default;
		}
		
		/*
		@method set_option
		@since 0.0.1
		@description Sets an option
		@param {String} $name The name of the option
		@param {Mixed} $default The default value to return if it is not set
		@returns {Mixed}
		*/
		public function set_option( $name = FALSE, $value = NULL )
		{
			if ( !$name || !array_key_exists($name, $this->options) ) return;
			update_option( 'lvl99-dbs/'.$name, $value );
			$this->options[$name] = $value;
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
			$path = $this->get_option('path');
			
			// Check the directory path for SQL files exists
			if ( !file_exists($path) && !is_dir($path) ) exit( __('Error: Invalid path set', 'lvl99-dbs') );
			
			// Get the list of files within the directory
			$dir_files = scandir( $path );
			foreach( $dir_files as $file )
			{
				if ( strstr($file, '.sql') != FALSE ) array_push( $files, $file );
			}
			
			return $files;
		}
		
		/*
		@method build_file_name
		@since 0.0.1
		@description Builds the file name from the file_name option
		@returns {String}
		*/
		public function build_file_name()
		{
			$file_name = $this->get_option('file_name');
			$output_file_name = $file_name;
			preg_match_all( '/\{[a-z0-9\:\_\-\/\\\]+\}/i', $file_name, $matches );
			
			if ( count($matches[0]) )
			{
				foreach( $matches[0] as $tag )
				{
					$tag_search = $tag;
					$tag_name = preg_replace( '/[\{\}]/', '', $tag );
					$tag_replace = '';
					
					// Tag has arguments
					if ( strstr($tag_name, ':') != FALSE )
					{
						$tag_split = explode( ':', $tag_name );
						$tag_name = $tag_split[0];
						$tag_replace = $tag_split[1];
					}
					
					// Get the value to replace the tag with
					switch ($tag_name)
					{
						case 'url':
							$tag_replace = untrailingslashit( preg_replace('/[a-z]+\:\/\//', '', WP_HOME ) );
							break;
							
						case 'date':
							$tag_replace = date( $tag_replace );
							break;
						
						case 'env':
							if ( defined('WP_ENV') ) $tag_replace = WP_ENV;
							break;
						
						case 'database':
							$tag_replace = DB_NAME;
							break;
					}
					
					$output_file_name = str_replace( $tag_search, $tag_replace, $output_file_name );
				}
			}
			
			if ( !preg_match('/\.sql$/i', $output_file_name ) ) $output_file_name .= '.sql';
			return $output_file_name;
		}
		
		/*
		@method save_sql_file
		@since 0.0.1
		@description Saves the tables to a file at the path
		@param {Mixed} $tables '*' {String} will save all the tables, an {Array} with table names will only save those selected tables
		@param {String} $compression 'none' will save as text/plain, 'gzip' will save as a gz file
		@returns {Boolean}
		*/
		public function save_sql_file( $tables = '*', $compression = 'none' )
		{
			global $wpdb;
			
			$this->check_admin();
			
			// Set the file's name and save path
			$file_name = $this->build_file_name();
			$file = $this->get_option('path') . $file_name;
			
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
  Created with LVL99 Database Sync v'.self::VERSION.'

  Site: '.get_bloginfo('name').'
  Address: '.WP_HOME.'

  File: '.$file_name.'
  Created: '.date( 'Y-m-d h:i:s' ).'
  Tables:
    -- '.implode("\n    -- ", $tables).'
*/

';
			
			// Cycle through
			foreach( $tables as $table )
			{
				$result = $wpdb->get_results( 'SELECT * FROM ' . $table, ARRAY_N );
				$num_fields = sizeof( $wpdb->get_results( 'DESCRIBE ' . $table, ARRAY_N ) );
				
				$return .= 'DROP TABLE '.$table.';';
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
		
			// Save file
			$handle = fopen( $file, 'w+' );
			fwrite( $handle, $return );
			fclose( $handle );
			
			$this->admin_notice( sprintf( __('Database was successfully backed up to <strong><code>%s</code></strong>', 'lvl99-dbs'), $file_name ) );
			return TRUE;
		}
		
		/*
		@method load_sql_file
		@since 0.0.1
		@description Loads an SQL file to update the database with
		@returns {Boolean}
		*/
		public function load_sql_file( $file = FALSE )
		{
			global $wpdb;
			
			$this->check_admin();
			
			if ( !$file )
			{
				$this->admin_error( 'No file was selected' );
				return FALSE;
			}

			// Make sure file exists in the path
			$file_name = basename($file);
			$file = $this->get_option('path') . $file;
			if ( !file_exists($file) )
			{
				$this->admin_error( sprintf( __('File does not exist at <strong><code>%s</code></strong>', 'lvl99-dbs'), $file ) );
				return FALSE;
			}
			
			// Enable maintenance mode
			$this->enable_maintenance();
			
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
					$wpdb->query( $templine );
					
					// Reset temp variable to empty
					$templine = '';
				}
			}
			
			if ( defined('WP_CACHE') )
			{
				$this->admin_notice( sprintf( __('Database was successfully restored from <strong><code>%s</code></strong>. If you have a caching plugin, it is recommended you flush your database cache now.', 'lvl99-dbs'), $file_name ) );
			}
			else
			{
				$this->admin_notice( sprintf( __('Database was successfully restored from <strong><code>%s</code></strong>', 'lvl99-dbs'), $file_name ) );
			}
			
			// Disable maintenance
			$this->disable_maintenance();
			
			return TRUE;
		}
		
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
			$file = $this->get_option('path') . $file_name;
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
			$file = $this->get_option('path') . $file;
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
		@method detect_route
		@since 0.0.1
		@description Detects if a route was fired and then builds route and fires its method after the plugins have loaded
		@returns {Void}
		*/
		public function detect_route()
		{
			$this->check_admin();
			
			if ( isset($_REQUEST['lvl99_dbs']) && !empty($_REQUEST['lvl99_dbs']) )
			{
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
						if ( strstr($name, 'lvl99_dbs_') != FALSE )
						{
							$request[$_method][str_replace( 'lvl99_dbs_', '', strtolower($name) )] = is_string($value) ? urldecode($value) : $value;
						}
					}
				}
				
				// Build and set the route to the class for later referral when running the route's method
				$this->route = array(
					'method' => 'route_' . preg_replace( '/[^a-z0-9_]+/i', '', $_REQUEST['lvl99_dbs'] ),
					'referrer' => $_SERVER['HTTP_REFERER'],
					'request' => $request,
				);
				
				// Fire the method
				$this->perform_route();
				// add_action( 'plugins_loaded', array( &$this, $this->route['method'] ), 9999 );
			}
		}
		
		/*
		@method perform_route
		@since 0.0.1
		@description Performs the route's method
		@returns {Void}
		*/
		public function perform_route()
		{
			$this->check_admin();
			
			if ( method_exists( $this, $this->route['method'] ) )
			{
				call_user_func( array( &$this, $this->route['method'] ) );
			}
			else
			{
				$this->admin_error( sprintf( __('Invalid route method was called: <strong><code>%s</code></strong>', 'lvl99-dbs'), $this->route['method'] ) );
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
			
			$tables = '*';
			if ( isset($this->route['request']['post']['tables']) )
			{
				if ( $this->route['request']['post']['tables'] == 'some' )
				{
					$tables = $this->route['request']['post']['tables_selected'];
				}
			}
			
			$compression = $this->get_option('compress_format');
			if ( isset($this->route['request']['post']['compression']) )
			{
				if ( $this->route['request']['post']['compression'] == 'gzip' )
				{
					$compression = 'gzip';
				}
			}
			
			$this->save_sql_file( $tables, $compression );
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
			
			// Use an existing file hosted on the server
			if ( isset($this->route['request']['post']['file']) && !empty($this->route['request']['post']['file']) )
			{
				$this->load_sql_file( $this->route['request']['post']['file'] );
			}
			
			// Use an uploaded file
			if ( isset($this->route['request']['post']['fileupload']) && !empty($this->route['request']['post']['fileupload']) )
			{
				$uploaded_file = $this->route['request']['post']['fileupload']['name'];
				$upload_path = $this->get_option('path') . $uploaded_file;
				
				// Checks
				// -- Duplicate name
				if ( file_exists($upload_path) )
				{
					$uploaded_file = preg_replace( '/(\.sql(\.gz)?)$/', md5(time()) . '$1', $uploaded_file );
					$upload_path = $this->get_option('path') . $uploaded_file;
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
					$this->load_sql_file( $uploaded_file );
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
			
			add_management_page( __('Database Sync', 'lvl99-dbs'), __('Database Sync', 'lvl99-dbs'), 'activate_plugins', 'lvl99-dbs', array( &$this, 'view_admin_index' ) );
			add_options_page( __('Database Sync', 'lvl99-dbs'), __('Database Sync', 'lvl99-dbs'), 'activate_plugins', 'lvl99-dbs-options', array( &$this, 'view_admin_options' ) );
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
	}
}

// The instance of the plugin
$lvl99_dbs = new LVL99_DBS();
define( 'WP_LVL99_DBS', LVL99_DBS::VERSION );