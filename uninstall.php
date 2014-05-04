<?php
 
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) 
	exit();

global $lvl99_dbs;
$lvl99_dbs->uninstall();