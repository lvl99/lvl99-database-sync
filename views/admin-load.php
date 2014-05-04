<?php
/*
 * LVL99 Database Sync
 * View - Admin Index
 */

if ( !defined('ABSPATH') || !defined('WP_LVL99_DBS') ) exit('No direct access allowed');
?>

<div class="wrap">
	<h2><?php _e('Database Sync', 'lvl99-dbs'); ?></h2>
	
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/tools.php?page=lvl99-dbs&action=save" class="nav-tab"><?php _ex('Save', 'Save SQL page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/tools.php?page=lvl99-dbs&action=load" class="nav-tab nav-tab-active"><?php _ex('Load', 'Load SQL page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/options-general.php?page=lvl99-dbs-options" class="nav-tab"><?php _ex('Options', 'Options page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/tools.php?page=lvl99-dbs&action=help" class="nav-tab"><?php _ex('Help', 'Help page tab', 'lvl99-dbs'); ?></a>
	</h2>
	
	<div class="lvl99-dbs-page">
		<form method="post" enctype="multipart/form-data">
			<input type="hidden" name="lvl99_dbs" value="load" />
			
			<div class="lvl99-dbs-intro"><?php _ex('Restores database tables from a selected file. Please note that this will overwrite your existing database tables.', 'Load SQL page description', 'lvl99-dbs'); ?></div>
			
			<table class="form-table">
				<tbody>
					<?php if ( count($filelist) > 0 ) : ?>
					<tr>
						<th scope="row"><?php _ex('Select SQL file', 'field label: file', 'lvl99-dbs'); ?></th>
						<td>
							<table class="wp-list-table widefat fixed lvl99-dbs-filelist">
								<thead>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-radio">&nbsp;</th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-file"><?php _ex('File', 'column name: file name', 'lvl99-dbs'); ?></th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-controls"><?php _ex('Controls', 'column name: file controls', 'lvl99-dbs'); ?></th>
								</thead>
								<tbody>
									<?php foreach( $filelist as $file ) : ?>
									<tr class="lvl99-dbs-filelist-file">
										<?php $file_id = md5($file); ?>
										<td class="lvl99-dbs-filelist-col-radio">
											<input id="<?php echo $file_id; ?>" type="radio" name="lvl99_dbs_file" value="<?php echo esc_attr($file); ?>" />
										</td>
										<td class="lvl99-dbs-filelist-col-file">
											<label for="<?php echo $file_id; ?>"><?php echo $file; ?></label>
										</td>
										<td class="lvl99-dbs-filelist-col-controls">
											<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/tools.php?page=lvl99-dbs&lvl99_dbs=download&lvl99_dbs_file=<?php echo urlencode($file); ?>" class="button button-secondary"><span class="fa fa-download"></span> <?php _ex('Download', 'button label download sql file', 'lvl99-dbs'); ?></a> 
											<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/tools.php?page=lvl99-dbs&lvl99_dbs=delete&lvl99_dbs_file=<?php echo urlencode($file); ?>" class="button button-secondary deletion"><span class="fa fa-delete"></span> <?php _ex('Delete', 'button label delete sql file', 'lvl99-dbs'); ?></a>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
								<tfoot>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-radio">&nbsp;</th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-file"><?php _ex('File', 'column name: file name', 'lvl99-dbs'); ?></th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-controls"><?php _ex('Controls', 'column name: file controls', 'lvl99-dbs'); ?></th>
								</tfoot>
							</table>
						</td>
					</tr>
					<tr>
						<th scope="row">&nbsp;</th>
						<td><input type="submit" name="lvl99_dbs_submit" value="<?php _ex('Load selected SQL file', 'Load SQL page button submit label action=load', 'lvl99-dbs'); ?>" class="button button-primary" /></td>
					</tr>
					<?php endif; ?>
					<tr>
						<th scope="row"><?php _ex('Upload SQL file', 'field label: fileupload', 'lvl99-dbs'); ?></th>
						<td><input type="file" name="lvl99_dbs_fileupload" value="" /><br/>
						<input type="submit" name="lvl99_dbs_submit" value="<?php _ex('Upload SQL file', 'Load SQL page button submit label action=upload', 'lvl99-dbs'); ?>" class="button button-primary" /></td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>