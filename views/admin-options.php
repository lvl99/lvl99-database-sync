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
		<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/tools.php?page=lvl99-dbs&action=save" class="nav-tab"><?php _ex('Save', 'Save SQL page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/tools.php?page=lvl99-dbs&action=load" class="nav-tab"><?php _ex('Load', 'Load SQL page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/options-general.php?page=lvl99-dbs-options" class="nav-tab nav-tab-active"><?php _ex('Options', 'Options page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo trailingslashit(WP_SITEURL); ?>wp-admin/tools.php?page=lvl99-dbs&action=help" class="nav-tab"><?php _ex('Help', 'Help page tab', 'lvl99-dbs'); ?></a>
	</h2>

	<div class="lvl99-dbs-page">
		<form method="post" action="options.php">
			<div class="lvl99-dbs-intro"><?php _ex('Configure Database Sync\'s default properties and behaviours.', 'Options page description', 'lvl99-dbs'); ?></div>

			<?php settings_fields( 'lvl99-dbs' ); ?>
			<?php do_settings_sections( 'lvl99-dbs' ); ?>
			<table class="form-table">
				<?php foreach( $lvl99_dbs->default_options as $name => $option ) : ?>
				<?php if ( !preg_match( '/^_/', $name ) ) : ?>
				<?php $option_value = get_option( 'lvl99-dbs/'.$name, $option['default'] ); ?>
				<tr>
					<th scope="row"><?php echo $option['label']; ?></th>
					<td>
					<?php if ( $option['field_type'] == 'text' ) : ?>
						<input type="text" name="<?php echo 'lvl99-dbs/'.$name; ?>" value="<?php echo esc_attr($option_value); ?>" size="40" class="widefat" />
						<?php if ( $name == 'path' ) : ?>
						<div style="margin-top: 0.5em; margin-bottom: 0.5em;"><i>Current path:</i><br/><code style="display: block"><?php echo esc_attr($lvl99_dbs->get_option_path()); ?></code></div>
						<?php endif; ?>

					<?php elseif ( $option['field_type'] == 'number' ) : ?>
						<input type="number" name="<?php echo 'lvl99-dbs/'.$name; ?>" value="<?php echo esc_attr($option_value); ?>" size="40" />

					<?php elseif ( $option['field_type'] == 'email' ) : ?>
						<input type="email" name="<?php echo 'lvl99-dbs/'.$name; ?>" value="<?php echo esc_attr($option_value); ?>" size="40" />

					<?php elseif ( $option['field_type'] == 'select' ) : ?>
						<select name="<?php echo 'lvl99-dbs/'.$name; ?>">
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
						<?php foreach( $option['values'] as $value ) : ?>
						<ul>
						<?php if ( is_array($value) ) : ?>
						<li>
							<label>
								<input type="radio" name="lvl99-dbs/<?php echo esc_attr($name); ?>" value="<?php echo $value['value']; ?>" <?php if ( $option_value == $value['value'] ) : ?>checked="checked"<?php endif; ?> />
								<?php if ( isset($value['label']) ) : ?>
									<?php echo $value['label']; ?>
								<?php else : ?>
									<?php echo $value['value']; ?>
								<?php endif; ?>
							</label>
						</li>
						<?php else : ?>
						<li>
							<label>
								<input type="radio" name="lvl99-dbs/<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" <?php if ( $option_value == $value ) : ?>checked="checked"<?php endif; ?> />
								<?php echo $value; ?>
							</label>
						</li>
						<?php endif; ?>
						<?php endforeach; ?>
					<?php elseif ( $option['field_type'] == 'checkbox' ) : ?>
						<?php $option_values = isset($option['values']) ? $option['values'] : array($option['value']); ?>
						<?php foreach ( $option_values as $value ) : ?>
						<ul>
						<?php if ( is_array($value) ) : ?>
						<li>
							<label>
								<input type="checkbox" name="lvl99-dbs/<?php echo esc_attr($name); ?>" value="<?php echo $value['value']; ?>" <?php if ( $option_value == $value['value'] ) : ?>checked="checked"<?php endif; ?> />
								<?php if ( isset($value['label']) ) : ?>
									<?php echo $value['label']; ?>
								<?php else : ?>
									<?php echo $value['value']; ?>
								<?php endif; ?>
							</label>
						</li>
						<?php else : ?>
						<li>
							<label>
								<input type="checkbox" name="lvl99-dbs/<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" <?php if ( $option_value == $value ) : ?>checked="checked"<?php endif; ?> />
								<?php echo $value; ?>
							</label>
						</li>
						<?php endif; ?>
						<?php endforeach; ?>
					<?php elseif ( $option['field_type'] == 'textarea' ) : ?>
						<textarea name="<?php echo 'lvl99-dbs/'.$name; ?>">
							<?php echo $option_value; ?>
						</textarea>
					<?php endif; ?>
					<?php if ( isset($option['help']) ) : ?>
					<div class="lvl99-dbs-option-help">
					<?php echo $option['help']; ?>
					</div>
					<?php endif; ?>
					</td>
				</tr>
				<?php endif; ?>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
</div>