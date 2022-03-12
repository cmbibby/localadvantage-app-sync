<?php
namespace LA_App_Sync;
Class Plugin{
	const SW_REGION_ID = 1;
	const GW_REGION_ID = 2;
	public function __construct()
	{
		new Delete_Media;
	    new Options_Page;
		new Sync;
	}
}
