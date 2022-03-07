<?php

namespace LA_App_Sync;

class Sync
{
	public function __construct()
	{
		add_action('wp_ajax_nopriv_get_offers_from_api', array($this, 'get_offers'));
		add_action('wp_ajax_get_offers_from_api', array($this, 'get_offers'));
		add_action('get_offers', array($this, 'get_offers'));
	}

	public function get_offers()
	{
		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');

		$offers = array();
		echo 'Off to get some Offers <br /><br /><hr />';
		$response = wp_remote_retrieve_body(wp_remote_get('https://app.localadvantage.com.au/api/v2/offers'));
		$response = json_decode($response, true);
		// echo '<pre>' . var_export( $response['offers'], true ) . '</pre>';
		$offer_count = 0;
		foreach ($response['offers'] as $offer) {
			if (1 == $offer['active'] && 1 == $offer['region_id']) {
				$offer_count++;
				// echo $offer['active'] . '<br />';
				// echo $offer['vendor_name'] . '<br />';
				// echo $offer['offer_title'] . '<br />';
				// echo $offer['offer_description'] . '<br />';
				// echo $offer['offer_conditions'] . '<br />';
				// echo $offer['vendor_about'] . '<br />';
				// echo $offer['address'] . '<br />';
				// echo $offer['phone'] . '<br />';
				// echo $offer['website'] . '<br />';
				// echo '<hr />';
				$offer_id = wp_insert_post(array(
					'post_name' => sanitize_title($offer['vendor_name']),
					'post_title' => $offer['vendor_name'],
					'post_type' => 'sw_offers',
					'post_status' => 'publish'
				));

				$acf_fields = array(
					'field_59a55be66555f' => 'offer_title',
					'field_59a55c1365560' => 'offer_description',
					'field_621f64e9b0172' => 'offer_conditions',
					'field_59a55c8365561' => 'vendor',
					'field_59a55deb8384e' => 'address',
					'field_59a67fa63fbb0' => 'phone',
					'field_59a55e0b11ceb' => 'website',
				);
				foreach ($acf_fields as $key => $name) {
					update_field($key, $offer[$name], $offer_id);
				}

				// Do the Logo

				$images = $offer['images'];

				$logo = array_shift($images);

				$media_id = media_sideload_image('https://app.localadvantage.com.au/images/catalog/' . $logo, null, $offer['vendor_name'], 'id');

				set_post_thumbnail($offer_id, $media_id);

				// Do the Gallery images

				$gallery_media = array();
				foreach($images as $image){
					$image_name = Utilities::format_image_title($offer['vendor_name'], $image);
					$gallery_media[] = media_sideload_image('https://app.localadvantage.com.au/images/catalog/'. $image, null, $image_name, 'id');
				}

				update_field('field_59a74fe116594', $gallery_media, $offer_id);

				// Update the Map

				$map_values = array(
					'lat' => $offers['latitude'],
					'lng' => $offers['longitude']
				);

				update_field('field_59a55ca565562', $map_values, $offer_id);

				// Locations

				$existing_term = term_exists($offer['location_name']);

				if($existing_term){
					wp_set_post_terms($offer_id, $existing_term, 'sw_location', false);
				}else{
					$new_location = wp_insert_term($offer['location_name'], 'sw_location');
					wp_set_post_terms($offer_id, $new_location,false);
				}

				// Categories are in an array so let's grab them

				$categories = $offer['categories'];
				foreach($categories as $category){
					$existing_category = term_exists($offer_id, $category['name']);
					if($existing_category){
						wp_set_post_terms($offer_id, $existing_category, 'sw_category', true);
					}else{
						$new_category = wp_insert_term($category['name'], 'sw_category');
						wp_set_post_terms($offer_id, $new_category, true);
					}
				}
				echo $offer_id . '<br />';
			}
		}
		echo $offer_count;
	}
}
