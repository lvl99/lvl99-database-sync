<?php
/*
 * LVL99 Database Sync
 * View - Admin Index
 */

if ( !defined('ABSPATH') || !defined('WP_LVL99_DBS') ) exit('No direct access allowed');

$action = isset($_GET['action']) ? $_GET['action'] : 'save';

if ( $action == 'save' ) include( 'admin-save.php' );
if ( $action == 'load' ) include( 'admin-load.php' );
if ( $action == 'options' ) include( 'admin-options.php' );
if ( $action == 'help' ) include( 'admin-help.php' );
?>