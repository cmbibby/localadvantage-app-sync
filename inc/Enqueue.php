<?php
namespace LA_App_Sync;
Class Enqueue{
	public function __construct()
	{
		// Hook up the js

		add_action('admin_enqueue_scripts', array($this,'action_enqueue_js'));
	}

	public function action_enqueue_js($hook){
		if ('tools_page_la_app_sync' !== $hook){
			return;
		}

		wp_enqueue_script('la-app-sync', Plugin::$plugin_dir_url . 'js/la-app-sync.js', ['wp-util']);
	}
}
