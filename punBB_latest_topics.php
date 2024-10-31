<?php
/*
Plugin Name: punBB Latest Topics
Plugin URI: http://www.erikadelson.com/current-projects/punbb-latest-topics-wordpress-widget/
Version: v1.0
Author: Erik Adelson
Description: A plugin to list the latest topics in your punBB forum.
 
Copyright 2007 Erik Adelson  (email : erik [a t ] erikadelson DOT com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/
if (!class_exists("punBBlatestPlugin")) {
	class punBBlatestPlugin {
		var $adminOptionsName = "punBBlatestPluginAdminOptions";
		function punBBlatestPlugin() { //constructor
			
		}
		function init() {
			$this->getAdminOptions();
		}
		//Returns an array of admin options
		function getAdminOptions() {
			$punBBlatestAdminOptions = array('punBB_URL' => 'http://www.yourdomain.com/forum',
			    'db_hostname' => 'localhost',
				'db_name' => '',
				'db_username' => '',
				'db_password' => '',
				'db_prefix' => 'punbb_');
			$punOptions = get_option($this->adminOptionsName);
			if (!empty($punOptions)) {
				foreach ($punOptions as $key => $option)
					$punBBlatestAdminOptions[$key] = $option;
			}			
			update_option($this->adminOptionsName, $punBBlatestAdminOptions);
			return $punBBlatestAdminOptions;
		}
		function findLatestPosts() {
			$punBBltoutput = '';
			$punBBltOptions = $this->getAdminOptions();
			
			$this->db_connect();
			
			$link = mysql_connect($punBBltOptions['db_hostname'], $punBBltOptions['db_username'], $punBBltOptions['db_password']);
			if (!$link) {
			    die('Could not connect: ' . mysql_error());
			}
			$table = $punBBltOptions['db_prefix'].'forums';
			
			mysql_select_db($punBBltOptions['db_name'], $link) or die('Could not select database.');
						
			$sql = 'SELECT `last_post_id` FROM '.$table.' WHERE `last_post_id` IS NOT NULL';
			$result = mysql_query($sql, $link) or die(mysql_errno()." : ".mysql_error().$sql);
			
			while($row = mysql_fetch_array($result)){
				
				$punBBltoutput .= $this->getTopic($row['last_post_id'], $link, $punBBltOptions['punBB_URL'], $punBBltOptions['db_prefix']);
			}
			
			//Get options for the widget text
			$options = get_option('widget_punBBlt');
			$options['text'] = "<br>".$punBBltoutput;
			
			//Update widget text with Forum Latest Topics
			update_option('widget_punBBlt', $options);
			
			//Close the DB connection
			$this->db_close($link);
			
			return;
	    }
		function getTopic ($postID, $link, $punBB_URL, $db_prefix) {
			$topics = '';
			$table = $db_prefix.'topics';
			$sql2 = 'SELECT `poster` , `subject` FROM '.$table.' WHERE `last_post_id` = '.$postID.' ';
			$result = mysql_query($sql2, $link);
								
			while($row = mysql_fetch_array($result)){
				
				$topics .= '<a href="'.$punBB_URL.'/viewtopic.php?pid='.$postID.'">'.$row['subject'].'</a><br>Last Post By: '.$row['poster'].'<br />';
				
			}
			return $topics;
		}
		function db_connect() {
			$punBBltOptions = $this->getAdminOptions();
			$link = mysql_connect($punBBltOptions['db_hostname'], $punBBltOptions['db_username'], $punBBltOptions['db_password']);
			if (!$link) {
				return false;
			    die('Could not connect: ' . mysql_error());
			}
			mysql_select_db($punBBltOptions['db_name'], $link) or die('Could not select database.');
			return $link;
		}
		function db_close($link) {
			
			mysql_close($link);
		}
		//Prints out the admin page
		function printAdminPage() {
					$punBBltOptions = $this->getAdminOptions();
										
					if (isset($_POST['update_punBBlatestPluginSettings'])) { 
						if (isset($_POST['punBBlatest_punBB_URL'])) {
							$punBBltOptions['punBB_URL'] = apply_filters('content_save_pre', $_POST['punBBlatest_punBB_URL']);
						}
						if (isset($_POST['punBBlatest_db_hostname'])) {
							$punBBltOptions['db_hostname'] = apply_filters('content_save_pre', $_POST['punBBlatest_db_hostname']);
						}
						if (isset($_POST['punBBlatest_db_name'])) {
							$punBBltOptions['db_name'] = apply_filters('content_save_pre', $_POST['punBBlatest_db_name']);
						}
						if (isset($_POST['punBBlatest_db_username'])) {
							$punBBltOptions['db_username'] = apply_filters('content_save_pre', $_POST['punBBlatest_db_username']);
						}
						if (isset($_POST['punBBlatest_db_password'])) {
							$punBBltOptions['db_password'] = apply_filters('content_save_pre', $_POST['punBBlatest_db_password']);
						}
						if (isset($_POST['punBBlatest_db_prefix'])) {
							$punBBltOptions['db_prefix'] = apply_filters('content_save_pre', $_POST['punBBlatest_db_prefix']);
						}
						update_option($this->adminOptionsName, $punBBltOptions);
						
						?>
<div class="updated"><p><strong><?php _e("Settings Updated.", "punBBlatestPlugin");?></strong></p></div>
					<?php
					} ?>
<div class=wrap>
<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
<h2>punBB Latest Topics</h2>
<table width="80%">

<tr>
<th align="right">punBB URL:</th><td><input type="text" name="punBBlatest_punBB_URL" size="35" value="<?php _e(apply_filters('format_to_edit',$punBBltOptions['punBB_URL']), 'punBBlatestPlugin') ?>"></input></p></td>
</tr>
<tr>
<td></td><td>Enter the URL of your punBB. ( example: http://www.yourdomain.com/forum )</td>
</tr>
<tr>
<th align="right">DB Hostname: </th><td><input type="text" name="punBBlatest_db_hostname" size="35" value="<?php _e(apply_filters('format_to_edit',$punBBltOptions['db_hostname']), 'punBBlatestPlugin') ?>"></input></p></td>
</tr>
<tr>
<td></td><td>Enter the MySQL hostname. ( In most cases: localhost )</td>
</tr>
<tr>
<th align="right">DB Name: </th>
<td><input type="text" name="punBBlatest_db_name" value="<?php _e(apply_filters('format_to_edit',$punBBltOptions['db_name']), 'punBBlatestPlugin') ?>"></input></td>
</tr>
<td></td><td>Enter the punBB database name.</td>
<tr>
<th align="right">DB Username: </th>
<td><input type="text" name="punBBlatest_db_username"  value="<?php _e(apply_filters('format_to_edit',$punBBltOptions['db_username']), 'punBBlatestPlugin') ?>"></input></p></td>
</tr>
<td></td><td>Enter the database username.</td>
<tr>
<th align="right">DB Password: </th>
<td><input type="text" name="punBBlatest_db_password" value="<?php _e(apply_filters('format_to_edit',$punBBltOptions['db_password']), 'punBBlatestPlugin') ?>"></input></p></td>
</tr>
<td></td><td>Enter the database password.</td>
<tr>
<th align="right">DB Prefix: </th>
<td><input type="text" name="punBBlatest_db_prefix" value="<?php _e(apply_filters('format_to_edit',$punBBltOptions['db_prefix']), 'punBBlatestPlugin') ?>"></input></p></td>
</tr>
<td></td><td>Enter the punBB database prefix. ( example: punbb_ )</td>
</table>
<div class="submit">
<input type="submit" name="update_punBBlatestPluginSettings" value="<?php _e('Update Settings', 'punBBlatestPlugin') ?>" /></div>
</form>
 </div>
					<?php
				}//End function printAdminPage()
	
	}

} //End Class punBBlatestPlugin

if (class_exists("punBBlatestPlugin")) {
	$ea_punBBlatestPlugin = new punBBlatestPlugin();
}

//Initialize the admin panel
if (!function_exists("punBBlatestPlugin_ap")) {
	function punBBlatestPlugin_ap() {
		global $ea_punBBlatestPlugin;
		if (!isset($ea_punBBlatestPlugin)) {
			return;
		}
		if (function_exists('add_options_page')) {
			add_options_page('punBB Latest', 'punBB Latest', 9, basename(__FILE__), array(&$ea_punBBlatestPlugin, 'printAdminPage'));
		}
	}	
}

//Actions and Filters	
if (isset($ea_punBBlatestPlugin)) {
	//Actions
	add_action('punBB_latest_topics.php',  array(&$ea_punBBlatestPlugin, 'init'));
	add_action('admin_menu', 'punBBlatestPlugin_ap');
	//add_action('wp_head', 'findLatestPosts');
	//Filters
	//$ea_punBBlatestPlugin->findLatestPosts();
	
	//$ea_punBBlatestPlugin->findLatestPosts();
	
	
	if ($ea_punBBlatestPlugin->db_connect()) {
	 	$ea_punBBlatestPlugin->findLatestPosts();
	}	
}

function widget_punBBlt_init() {
	
	// Check to see required Widget API functions are defined...
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return; // ...and if not, exit gracefully from the script.

	// This function prints the sidebar widget--the cool stuff!
	function widget_punBBlt($args) {

		// $args is an array of strings which help your widget
		// conform to the active theme: before_widget, before_title,
		// after_widget, and after_title are the array keys.
		extract($args);

		// Collect our widget's options, or define their defaults.
		$options = get_option('widget_punBBlt');
		$title = empty($options['title']) ? 'Forum Latest Topics' : $options['title'];
		$text = empty($options['text']) ? '' : $options['text'];

 		// It's important to use the $before_widget, $before_title,
 		// $after_title and $after_widget variables in your output.
 		
 		echo $before_widget;
		echo $before_title . $title . $after_title;
		echo $text;
		echo $after_widget;
		
	}

	function widget_punBBlt_control() {

		// Get widget's options.
		$options = get_option('widget_punBBlt');

		// handle control form submission.
		if ( $_POST['punBBlt-submit'] ) {
			// Clean up control form submission options
			$newoptions['title'] = strip_tags(stripslashes($_POST['punBBlt-title']));
			$newoptions['text'] = strip_tags(stripslashes($_POST['punBBlt-text']));
		}

		// If original widget options do not match control form
		// submission options, update them.
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_punBBlt', $options);
		}

		// Format options as valid HTML. 
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		$text = htmlspecialchars($options['text'], ENT_QUOTES);

// The HTML below is the control form for editing options.
?>
		<div>
		<label for="punBBlt-title" style="line-height:35px;display:block;">Title: <input type="text" id="punBBlt-title" name="punBBlt-title" value="<?php echo $title; ?>" /></label>
		
		<input type="hidden" name="punBBlt-submit" id="punBBlt-submit" value="1" />
		</div>
	<?php
	// end of widget_punBBlt_control()
	}

	register_sidebar_widget('punBB Latest', 'widget_punBBlt');

	register_widget_control('punBB Latest', 'widget_punBBlt_control');
}

add_action('plugins_loaded', 'widget_punBBlt_init');

?>
