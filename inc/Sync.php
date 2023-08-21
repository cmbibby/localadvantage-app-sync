<?php

namespace LA_App_Sync;

class Sync
{
	public function __construct()
	{
		add_action('wp_ajax_nopriv_get_offers_all', array($this, 'get_offers_all'));
		add_action('wp_ajax_get_offers_all', array($this, 'get_offers_all'));
		add_action('wp_ajax_nopriv_get_offers_latest', array($this, 'get_offers_latest'));
		add_action('wp_ajax_get_offers_latest', array($this, 'get_offers_latest'));
		add_action('get_offers', array($this, 'get_offers_latest'));
		add_action('get_offers_all', array($this, 'get_offers_all'));

		// We need to bring these in to use media_sideload

		require_once(ABSPATH . 'wp-admin/includes/media.php');
		require_once(ABSPATH . 'wp-admin/includes/file.php');
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		if (!is_admin()) {
			require_once(ABSPATH . 'wp-admin/includes/post.php');
		}
	}

	public function get_offers_all()
	{
		$this->get_offers(true);
	}

	public function get_offers_latest()
	{
		$this->get_offers(false);
	}

	public function get_offers($all)
	{
		global $nginx_purger;

		if ('local-advantage' == APP_SITE_NAME) {
			$api_url = 'https://app.localadvantage.com.au/api/v2/offers';
		}

		if ('holiday-advantage' == APP_SITE_NAME) {
			$api_url = 'https://app.localadvantage.com.au/api/v2/offers?holiday_advantage=1';
		}

		if (false == $all) {
			$last_update = new \DateTime(get_field('last_update_time', 'option'));
			$last_update->modify('-4 hours');

			if ('local-advantage' == APP_SITE_NAME) {
				$api_url = $api_url . '?updates_from=' . $last_update->format('Y-m-d%H:i:s');
			}

			if ('holiday-advantage' == APP_SITE_NAME) {

				// We're appending query param

				$api_url = $api_url . '&updates_from=' . $last_update->format('Y-m-d%H:i:s');
			}
		}

		Utilities::update_timestamp();
		$offers      = array();
		$response    = wp_remote_retrieve_body(wp_remote_get($api_url));
		$response    = json_decode($response, true);
		$offer_count = 0;
		foreach ($response['offers'] as $offer) {

			if ('local-advantage' == APP_SITE_NAME) {
				switch ($offer['region_id']) {
					case Plugin::SW_REGION_ID:
						$post_type     = 'sw_offers';
						$location_type = 'sw_location';
						$category_type = 'sw_category';
						break;
					case Plugin::GS_REGION_ID:
						$post_type     = 'gs_offers';
						$location_type = 'gs_location';
						$category_type = 'gs_category';
						break;
				}
			}

			if ('holiday-advantage' == APP_SITE_NAME) {
				$post_type     = 'offers';
				$location_type = 'ha_offer_location';
				$category_type = 'ha_offer_category';
			}

			$offer_count++;

			// Locations

			$location_term = term_exists($offer['location_name'], $location_type);

			//error_log('Location Term Exists returns : ' .  var_export($location_term, true));

			if (!$location_term) {
				$location_term = wp_insert_term($offer['location_name'], $location_type);
			}

			//	error_log('Location Term will be : ' . var_export($location_term, true));


			// Categories

			$categories = $offer['categories'];

			$categories_to_add = [];
			foreach ($categories as $category) {
				$existing_category = term_exists($category['name'], $category_type);
				if ($existing_category) {
					$categories_to_add[] = $existing_category['term_taxonomy_id'];
				} else {
					$new_category        = wp_insert_term($category['name'], $category_type);
					$categories_to_add[] = $new_category['term_taxonomy_id'];
				}
			}

			// Check if the post exists and if so delete it

			$existing_post_id = post_exists($offer['vendor_name'], '', '', $post_type, 'publish');

			if ($existing_post_id > 0) {
				wp_delete_post($existing_post_id, true);
			}

			// Check the active field and bail if it isn't there

			if (false == $offer['active']) {
				continue;
			}
			// Lets insert the post

			$offer_id = wp_insert_post(
				array(
					'post_name'   => sanitize_title($offer['vendor_name']),
					'post_title'  => $offer['vendor_name'],
					'post_type'   => $post_type,
					'post_status' => 'publish',
					'tax_input'   => array(
						$location_type => $location_term,
						$category_type => $categories_to_add,
					),
				)
			);

			// Update the custom fields

			$acf_fields = array(
				'field_59a55be66555f' => 'offer_title',
				'field_59a55c1365560' => 'offer_description',
				'field_621f64e9b0172' => 'offer_conditions',
				'field_59a55c8365561' => 'vendor_about',
				'field_59a55deb8384e' => 'address',
				'field_59a67fa63fbb0' => 'phone',
				'field_59a55e0b11ceb' => 'website',
				'field_6228978384673' => 'latitude',
				'field_6228978abb5b6' => 'longitude',
			);
			foreach ($acf_fields as $key => $name) {
				update_field($key, $offer[$name], $offer_id);
			}

			// Do the Logo

			$images = $offer['images'];

			$logo = array_shift($images);

			// $media_id = media_sideload_image( 'https://app.localadvantage.com.au/images/catalog/' . $logo, null, $offer['vendor_name'], 'id' );
			$media_id = media_sideload_image('https://app.localadvantage.com.au/images/offers_original/' . $logo, null, $offer['vendor_name'], 'id');

			set_post_thumbnail($offer_id, $media_id);

			// Do the Gallery images

			$gallery_media = array();
			foreach ($images as $image) {
				$image_name      = Utilities::format_image_title($offer['vendor_name'], $image);
				error_log('trying to sideload ' . $image . ' -- ' . $image_name);
				// $gallery_media[] = media_sideload_image( 'https://app.localadvantage.com.au/images/catalog/' . $image, null, $image_name, 'id' );
				$gallery_media[] = media_sideload_image('https://app.localadvantage.com.au/images/offers_original/' . $image, null, $image_name, 'id');
			}

			update_field('field_59a74fe116594', $gallery_media, $offer_id);
		}

		// Update search and filter

		do_action('search_filter_update_post_cache', $offer_id);

		// Remove and reindex Relevanssi

		relevanssi_index_doc($offer_id, true, 'all');

		// Purge Nginx

		$nginx_purger->purge_all();

		$last_updated = get_field('last_update_time', 'option');

		wp_send_json_success(
			array(
				'offer_count'     => $offer_count,
				'last_updated_at' => $last_updated,
			),
			200
		);

		wp_die();
	}
}
