<?php
namespace LA_App_Sync;
Class Utilities{
public static function format_image_title($vendor_name,$image){

	$image_name = preg_replace("([^\\s]+(\\.(?i)(jpg|png|gif|bmp))$)", '', $image);
	return sanitize_title($vendor_name . '-' . $image_name);
}

public static function update_timestamp(){
	update_field('last_update_time',current_time('Y-m-d H:i:s'),'option');
}


}
