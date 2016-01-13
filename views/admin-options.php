<?php
/*
 * LVL99 Database Sync
 * View - Admin Options
 */

if ( !defined('ABSPATH') || !defined('WP_LVL99_DBS') ) exit('No direct access allowed');

global $lvl99_dbs;
?>

<div class="wrap">
	<h2><?php _e('Database Sync', 'lvl99-dbs'); ?></h2>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo site_url('/wp-admin/tools.php?page=lvl99-dbs&action=save'); ?>" class="nav-tab"><?php _ex('Save', 'Save SQL page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo site_url('/wp-admin/tools.php?page=lvl99-dbs&action=load'); ?>" class="nav-tab"><?php _ex('Load', 'Load SQL page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo site_url('/wp-admin/options-general.php?page=lvl99-dbs-options'); ?>" class="nav-tab nav-tab-active"><?php _ex('Options', 'Options page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo site_url('/wp-admin/tools.php?page=lvl99-dbs&action=help'); ?>" class="nav-tab"><?php _ex('Help', 'Help page tab', 'lvl99-dbs'); ?></a>
	</h2>

	<div class="lvl99-dbs-page">
		<form method="post" action="options.php">
			<div class="lvl99-dbs-intro"><?php _ex('Configure Database Sync\'s default properties and behaviours.', 'Options page description', 'lvl99-dbs'); ?></div>

			<?php settings_fields( 'lvl99-dbs' ); ?>
			<?php do_settings_sections( 'lvl99-dbs' ); ?>
			<?php $lvl99_dbs->render_options( $lvl99_dbs->default_options ); ?>
			<?php submit_button(); ?>
		</form>
	</div>
</div>