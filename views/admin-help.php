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
		<a href="<?php echo site_url('/wp-admin/options-general.php?page=lvl99-dbs-options'); ?>" class="nav-tab"><?php _ex('Options', 'Options page tab', 'lvl99-dbs'); ?></a>
		<a href="<?php echo site_url('/wp-admin/tools.php?page=lvl99-dbs&action=help'); ?>" class="nav-tab nav-tab-active"><?php _ex('Help', 'Help page tab', 'lvl99-dbs'); ?></a>
	</h2>

	<div class="lvl99-dbs-page">

		<h2>LVL99 Database Sync <small>v<?php echo $lvl99_dbs->version; ?></small></h2>
		<div class="lvl99-dbs-intro">
			<p><b>LVL99 Database Sync</b> is a WordPress plugin which allows you to easily save your WP database to an SQL file, and to also restore a database from an SQL file.</p>
			<p>Created and maintained by <a href="mailto:matt@lvl99.com?subject=LVL99 Database Sync">Matt Scheurich</a></p>
		</div>

		<div class="lvl99-dbs-section">
			<p>Its creation was inspired by how I manage WP site development. I often have a local development server, a staging server and a live server. These can all each have their own database or a shared database (often I have two staging servers: one which uses local development server files and database, the other uses local development server files and the staging server's database). I've found it frustrating keeping database entries consistent across multiple sites/databases, so I figured I'd create an easy and simple solution to save and load SQL data.</p>
			<p>Since I also use <a href="http://git-scm.com/" target="_blank">git</a> I can use that to watch the directory where the SQL files are located and using a file naming scheme I can save and restore per development environment.</p>
			<ul class="list-text">
				<li>Visit <a href="http://github.com/lvl99/lvl99-database-sync" target="_blank">github.com/lvl99/lvl99-database-sync</a> for news and updates</li>
				<li>Fork development of this plugin at <a href="http://github.com/lvl99/lvl99-database-sync" target="_blank">github.com/lvl99/lvl99-database-sync</a></li>
				<li>Consider supporting this free plugin's creation and development by donating via the methods below:<br/>
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
						<input type="hidden" name="cmd" value="_donations">
						<input type="hidden" name="business" value="matt.scheurich@gmail.com">
						<input type="hidden" name="lc" value="AU">
						<input type="hidden" name="item_name" value="Matt Scheurich">
						<input type="hidden" name="no_note" value="0">
						<input type="hidden" name="currency_code" value="USD">
						<input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_SM.gif:NonHostedGuest">
						<input type="image" src="https://www.paypalobjects.com/en_AU/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal — The safer, easier way to pay online.">
						<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
					</form>
					<a href="https://flattr.com/submit/auto?user_id=lvl99&url=http%3A%2F%2Fwww.lvl99.com%2Fcode%2Flvl99-dbs" target="_blank"><img src="//api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0"></a></li>
				</li>
			</ul>
		</div>

		<h3>Development and usage licence</h3>
		<pre>Copyright © 2014 Matt Scheurich (email: matt@lvl99.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA</pre>

	</div>

</div>