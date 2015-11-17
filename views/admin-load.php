<?php
/*
 * LVL99 Database Sync
 * View - Admin Index
 */

if ( !defined('ABSPATH') || !defined('WP_LVL99_DBS') ) exit('No direct access allowed');

global $lvl99_dbs;

$textdomain = $lvl99_dbs->get_textdomain();

// Saved options to reuse
$posted = NULL;
if ( !empty($lvl99_image_import->route['request']) )
{
  $posted = $lvl99_image_import->route['request']['post'];
}

// Filters
$filters = isset($posted['filters']) ? $posted['filters'] : array();
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
			<input type="hidden" name="lvl99-dbs" value="load" />

			<div class="lvl99-dbs-intro"><?php _ex('Restores database tables from a selected file. Please note that this will overwrite your existing database tables.', 'Load SQL page description', 'lvl99-dbs'); ?></div>

			<table class="form-table">
				<tbody>
					<?php if ( count($filelist) > 0 ) : ?>
					<tr>
						<th scope="row"><?php _ex('Select SQL file...', 'field label: file', 'lvl99-dbs'); ?></th>
						<td>
							<table class="wp-list-table widefat fixed lvl99-dbs-filelist">
								<thead>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-radio">&nbsp;</th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-file"><?php _ex('File', 'column name: file name', 'lvl99-dbs'); ?></th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-size"><?php _ex('Size', 'column name: file size', 'lvl99-dbs'); ?></th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-created"><?php _ex('Created', 'column name: file created', 'lvl99-dbs'); ?></th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-controls"><?php _ex('Controls', 'column name: file controls', 'lvl99-dbs'); ?></th>
								</thead>
								<tbody>
									<?php foreach( $filelist as $file ) : ?>
									<tr class="lvl99-dbs-filelist-file">
										<?php $file_id = md5($file['file_name']); ?>
										<td class="lvl99-dbs-filelist-col-radio">
											<input id="<?php echo $file_id; ?>" type="radio" name="lvl99-dbs_file" value="<?php echo esc_attr($file['file_name']); ?>" />
										</td>
										<td class="lvl99-dbs-filelist-col-file">
											<label for="<?php echo $file_id; ?>"><?php echo $file['file_name']; ?></label>
										</td>
										<td class="lvl99-dbs-filelist-col-size">
											<label for="<?php echo $file_id; ?>"><?php echo $lvl99_dbs->format_file_size( $file['size'], 2); ?></label>
										</td>
										<td class="lvl99-dbs-filelist-col-created">
											<label for="<?php echo $file_id; ?>"><?php echo date( get_option('date_format').' h:i:s', $file['created'] ); ?></label>
										</td>
										<td class="lvl99-dbs-filelist-col-controls">
											<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/tools.php?page=lvl99-dbs&lvl99-dbs=download&lvl99-dbs_file=<?php echo urlencode($file['file_name']); ?>" class="button button-secondary"><span class="fa fa-download"></span> <?php _ex('Download', 'button label download sql file', 'lvl99-dbs'); ?></a>
											<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/tools.php?page=lvl99-dbs&lvl99-dbs=delete&lvl99-dbs_file=<?php echo urlencode($file['file_name']); ?>" class="button button-secondary deletion"><span class="fa fa-delete"></span> <?php _ex('Delete', 'button label delete sql file', 'lvl99-dbs'); ?></a>
										</td>
									</tr>
									<?php endforeach; ?>
								</tbody>
								<tfoot>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-radio">&nbsp;</th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-file"><?php _ex('File', 'column name: file name', 'lvl99-dbs'); ?></th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-size"><?php _ex('Size', 'column name: file size', 'lvl99-dbs'); ?></th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-created"><?php _ex('Created', 'column name: file created', 'lvl99-dbs'); ?></th>
									<th scope="col" class="manage-column column-title lvl99-dbs-filelist-col-controls"><?php _ex('Controls', 'column name: file controls', 'lvl99-dbs'); ?></th>
								</tfoot>
							</table>
						</td>
					</tr>
					<?php endif; ?>
					<tr>
						<th scope="row"><?php _ex('... or upload SQL file', 'field label: fileupload', 'lvl99-dbs'); ?></th>
						<td><input type="file" name="lvl99-dbs_fileupload" value="" /></td>
					</tr>
					<?php /*
					<tr>
						<th scope="row"><?php _ex('Post-processing', 'field label: postprocessing', 'lvl99-dbs'); ?></th>
						<td>
							<div class="lvl99-dbs-option-help">
							<?php _ex( '<p>Post-processing allows you to search for and replace string values within the SQL document before it is loaded. For example, you may use it to replace URL strings that contain the dev/staging site\'s URL with the live/production site\'s URL.</p>
<p>Separate the search and replace values with a new line. Regular expressions are supported (search/replace is performed by <code>preg_replace</code>)</p>', 'field help: postprocessing', 'lvl99-dbs' ); ?>
							</div>
							<table style="width: 100%">
								<tr>
									<td style="width: 50%">
										<label>
											<h4>Search</h4>
											<textarea name="lvl99-dbs_postprocessing_search" style="width: 100%; height: 8em"></textarea>
										</label>
									</td>
									<td style="width: 50%">
										<label>
											<h4>Replace</h4>
											<textarea name="lvl99-dbs_postprocessing_replace" style="width: 100%; height: 8em"></textarea>
										</label>
									</td>
								</tr>
							</table>
						</td>
					</tr> */ ?>

					<tr>
						<th scope="row"><?php _ex('Filters', 'field label: filters', 'lvl99-dbs'); ?></th>
						<td>
							<div class="lvl99-plugin-option-help">
								<p>Apply filters to search and replace any text within the loaded SQL file before being applied to the database. This is useful when migrating databases across differing domain names and you need to change domain names and file paths.</p>
								<p class="small">Plain text matches work, and if you're fancy you can also use <a href="http://www.regex101.com" target="_blank">PCRE regular expressions</a>. <b>Note:</b> The order of filters matters.</p>
							</div>

							<div class="lvl99-dbs-filters lvl99-sortable">
								<?php foreach ( $filters as $num => $filter ) : ?>
								<?php $rand = substr(md5($num), 0, 8); ?>
								<div class="lvl99-dbs-filter-item ui-draggable ui-sortable">
									<div class="lvl99-dbs-filter-method">
										<span class="fa-arrows-v lvl99-sortable-handle"></span>
										<select name="<?php echo esc_attr($textdomain); ?>_filters[<?php echo $rand; ?>][method]">
											<option value="replace"<?php if ($filter['method'] == 'replace') : ?> selected="selected"<?php endif; ?>>Search &amp; Replace</option>
										</select>
									</div>
									<div class="lvl99-dbs-filter-input">
										<input type="text" name="<?php echo esc_attr($textdomain); ?>_filters[<?php echo $rand; ?>][input]" value="<?php echo esc_attr(stripslashes($filter['input'])); ?>" placeholder="Search for..." />
									</div>
									<div class="lvl99-dbs-filter-output">
										<input type="text" name="<?php echo esc_attr($textdomain); ?>_filters[<?php echo $rand; ?>][output]" value="<?php echo esc_attr($filter['output']); ?>" placeholder="Replace with empty string" />
									</div>
									<div class="lvl99-dbs-filter-controls">
										<a href="#remove-filter" class="button button-secondary button-small">Remove</a>
									</div>
								</div>
								<?php endforeach; ?>
							</div>

							<p><a href="#add-filter" class="button button-secondary"><?php echo __( 'Add Filter', $textdomain ); ?></a></p>

							<div class="lvl99-plugin-option-help" style="margin-top: 2em">
								<dl>
									<dt>ABSPATH</dt>
									<dd><code><?php echo ABSPATH; ?></code></dd>
									<dt>WP_HOME</dt>
									<dd><code><?php echo WP_HOME; ?></code></dd>
									<dt>WP_SITEURL</dt>
									<dd><code><?php echo WP_SITEURL; ?></code></dd>
								</dl>
							</div>
						</div>
					</td>

					<tr>
						<th scope="row">Execution options</th>
						<td>
							<ul>
								<li><label><input type="checkbox" name="lvl99-dbs_savetonewfile" value="1" /> <strong>Save to new SQL file</strong><br/>
									<p class="small">Ideal if you want to test the filters' outputted results first before applying to a database (use in conjunction with "Dry Run") or keep a record of filtered SQL file for future.</p></label></li>
								<li><label><input type="checkbox" name="lvl99-dbs_dryrun" value="1" checked="checked" /> <strong>Dry run</strong><br/>
									<p class="small">Performs all actions normally without modifying the database</p></label></li>
							</ul>
						</td>
					</tr>

					<tr>
						<th scope="row">&nbsp;</th>
						<td>
							<input type="submit" name="lvl99-dbs_submit" value="<?php _ex('Load and process SQL file', 'Load SQL page button submit label', 'lvl99-dbs'); ?>" class="button button-primary" />
						</td>
					</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>