<?php

namespace LA_App_Sync;

class Sync
{
	public function __construct()
	{
		add_action('wp_ajax_nopriv_get_offers_from_api', array($this, 'get_offers'));
		add_action('wp_ajax_get_offers_from_api', array($this, 'get_offers'));
		add_action('get_offers', array($this, 'get_offers'));

		// We need to bring these in to use media_sideload

		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
	}

	public function get_offers()
	{


		$offers = array();
		echo 'Off to get some Offers <br /><br /><hr />';
		$response = wp_remote_retrieve_body(wp_remote_get('https://app.localadvantage.com.au/api/v2/offers'));
		$response = json_decode($response, true);
		$offer_count = 0;
		foreach ($response['offers'] as $offer) {
			if($offer_count > 10){
				return;
			}

			if (1 == $offer['active'] && 1 == $offer['region_id']) {
				$offer_count++;

				// Locations

				$location_term = term_exists($offer['location_name'], 'sw_location');

				if(! $location_term){
					$location_term = wp_insert_term($offer['location_name'], 'sw_location');
				}

				// Categories



				$categories = $offer['categories'];

				$categories_to_add = [];
				foreach($categories as $category){
					$existing_category = term_exists($category['name'],'sw_category');
					if($existing_category){
						$categories_to_add[] = $existing_category['term_taxonomy_id'];
					}else{
						$new_category = wp_insert_term($category['name'], 'sw_category');
						$categories_to_add[] = $new_category['term_taxonomy_id'];
					}
				}

				// Check if the post exists and if so delete it

				$existing_post_id = post_exists($offer['vendor_name'],'','','sw_offers','publish');

				echo '<pre>' . var_export( $existing_post_id, true ) . '</pre>';

				if($existing_post_id > 0){
					wp_delete_post($existing_post_id);
				}

				// Lets insert the post

				$offer_id = wp_insert_post(array(
					'post_name' => sanitize_title($offer['vendor_name']),
					'post_title' => $offer['vendor_name'],
					'post_type' => 'sw_offers',
					'post_status' => 'publish',
					'tax_input' => array(
						'sw_location' => $location_term,
						'sw_category' => $categories_to_add
					)
				));

				$acf_fields = array(
					'field_59a55be66555f' => 'offer_title',
					'field_59a55c1365560' => 'offer_description',
					'field_621f64e9b0172' => 'offer_conditions',
					'field_59a55c8365561' => 'vendor',
					'field_59a55deb8384e' => 'address',
					'field_59a67fa63fbb0' => 'phone',
					'field_59a55e0b11ceb' => 'website',
					'field_6228978384673' => 'latitude',
					'field_6228978abb5b6' => 'longitude'
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



			}
		}
		echo $offer_count;
	}
}
