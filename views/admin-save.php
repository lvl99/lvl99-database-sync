<?php
/*
 * LVL99 Database Sync
 * View - Admin Index
 */

if ( !defined('ABSPATH') || !defined('WP_LVL99_DBS') ) exit('No direct access allowed');

global $lvl99_dbs;

// Shared options to render on the Save page
$save_options = array(
	'file_name' => $lvl99_dbs->default_options['file_name'],
	'compress_format' => $lvl99_dbs->default_options['compress_format'],
);
?>

<div class="wrap">
	<h2><?php _e('Database Sync', 'lvl99-dbs'); ?></h2>

	<h2 class="nav-tab-wrapper">
		<a href="<?php echo site_url('/wp-admin/tools.php?page=lvl99-dbs&action=save'); ?>" class="nav-tab nav-tab-active"><?php _ex('Save', 'Save SQL page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo site_url('/wp-admin/tools.php?page=lvl99-dbs&action=load'); ?>" class="nav-tab"><?php _ex('Load', 'Load SQL page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo site_url('/wp-admin/options-general.php?page=lvl99-dbs-options'); ?>" class="nav-tab"><?php _ex('Options', 'Options page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo site_url('/wp-admin/tools.php?page=lvl99-dbs&action=help'); ?>" class="nav-tab"><?php _ex('Help', 'Help page tab', 'lvl99-dbs'); ?></a>
	</h2>

	<div class="lvl99-dbs-page">
		<form method="post">
			<input type="hidden" name="lvl99-dbs" value="save" />

			<div class="lvl99-dbs-intro"><?php _ex('Saves your WP database to an SQL file.', 'Save SQL page description', 'lvl99-dbs'); ?></div>

			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php _ex('Select tables to save to SQL', 'field label: tables', 'lvl99-dbs'); ?></th>
						<td>
							<ul class="lvl99-dbs-tables">
								<li class="lvl99-dbs-tables-all"><label><input type="radio" name="lvl99-dbs_tables" value="all" checked="checked"/> <?php _ex('Back up all tables', 'field value: tables=all', 'lvl99-dbs'); ?></label></li>
								<?php if ( count($tablelist) > 0 ) : ?>
								<li  class="lvl99-dbs-tables-selected">
									<label><input type="radio" name="lvl99-dbs_tables" value="selected" /> <?php _ex('Back up only selected tables', 'field value: tables=some', 'lvl99-dbs'); ?></label>
									<ul class="lvl99-dbs-tablelist">
									<?php foreach( $tablelist as $table ) : ?>
										<li class="lvl99-dbs-tablelist-table"><label><input type="checkbox" name="lvl99-dbs_tables_selected[]" value="<?php echo esc_attr($table); ?>" checked="checked" disabled="disabled" /> <?php echo $table; ?></label></li>
									<?php endforeach; ?>
									</ul>
								</li>
								<?php endif; ?>
							</ul>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<?php $lvl99_dbs->render_options( $save_options ); ?>

							<input type="submit" name="lvl99-dbs_submit" value="<?php _ex('Save SQL file', 'Save SQL page button-submit label', 'lvl99-dbs'); ?>" class="button button-primary" />
						</td>
						<?php /*<th scope="row"><?php echo $lvl99_dbs->default_options['file_name']['label']; ?></th>
						<td>
							<input type="text" name="lvl99-dbs_file_name" value="<?php echo $lvl99_dbs->get_option('file_name'); ?>" class="widefat" />
							<?php if ( isset($lvl99_dbs->default_options['file_name']['help_after']) ) : ?>
							<div class="lvl99-dbs-option-help">
								<?php echo $lvl99_dbs->default_options['file_name']['help_after']; ?>
							</div>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php _ex( 'File compression format', 'Save SQL page field label: compress_format', 'lvl99-dbs' ); ?></th>
						<td>
							<ul>
								<?php foreach( $lvl99_dbs->default_options['compress_format']['values'] as $option ) : ?>
								<li><label><input type="radio" name="lvl99-dbs_compression" value="<?php echo $option['value']; ?>" <?php if ($option['value'] == $lvl99_dbs->default_options['compress_format']['default']) : ?>checked="checked"<?php endif; ?>/> <?php echo $option['label']; ?></label></li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>
					<tr>
						<th scope="row">&nbsp;</th>
						<td><input type="submit" name="lvl99-dbs_submit" value="<?php _ex('Save SQL file', 'Save SQL page button-submit label', 'lvl99-dbs'); ?>" class="button button-primary" /></td>*/ ?>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>