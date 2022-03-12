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
		<p>Last Update Time : <?php echo get_field('last_update_time','option'); ?></p>

<?php
	}
}
