<?php

namespace LA_App_Sync;


class Options_Page
{

	function __construct()
	{
		add_action('admin_menu', array($this, 'admin_menu'));
	}


	function admin_menu()
	{
		add_management_page('App Sync', 'App Sync', 'install_plugins', 'la_app_sync', array($this, 'page_content'), '');
	}


	public function page_content()
	{
?>
		<h1>App Sync</h1>
		<h2 >Last Update Time : <span id="updateTime"><?php echo get_field('last_update_time','option'); ?></span></h2>
		<p id="infoContainer"></p>
		<button id="import_all" class="button-primary">Import All Offers</button>
		<div id="spinner" class="spinner" style="float:none;width:auto;height:auto;padding:10px 0 10px 50px;background-position:20px 0;">



<?php
	}
}
