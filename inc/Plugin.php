<?php

namespace LA_App_Sync;

class Plugin
{
	const SW_REGION_ID            = 1;
	const GS_REGION_ID            = 3;
	public static $plugin_dir_url = '';
	public function __construct($plugin_dir_url)
	{
		self::$plugin_dir_url = $plugin_dir_url;
		set_time_limit(10800);
		new Enqueue;
		new Delete_Media;
		new Options_Page;
		new Sync;
	}
}
