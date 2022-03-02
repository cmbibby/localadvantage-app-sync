<?php
namespace LA_App_Sync;
Class Sync{
	public function __construct()
	{
		add_action('wp_ajax_nopriv_get_offers_from_api', array($this,'get_offers'));
		add_action('wp_ajax_get_offers_from_api', array($this,'get_offers'));
		add_action('get_offers', array($this,'get_offers'));
	}

	public function get_offers(){
		$offers = array();
		echo 'Off to get some Offers <br /><br /><hr />';
		$response = wp_remote_retrieve_body(wp_remote_get('https://app.localadvantage.com.au/api/v2/offers'));
		$response = json_decode($response, true);
		// echo '<pre>' . var_export( $response['offers'], true ) . '</pre>';
		$offer_count = 0;
		foreach($response['offers'] as $offer){
				$offer_count ++;
				echo $offer['active'] . '<br />';
				echo $offer['vendor_name'] . '<br />';
				echo $offer['offer_title'] . '<br />';
				echo $offer['offer_description'] . '<br />';
				echo $offer['offer_conditions'] . '<br />';
				echo $offer['vendor_about'] . '<br />';
				echo $offer['address'] . '<br />';
				echo $offer['phone'] . '<br />';
				echo $offer['website'] . '<br />';
				echo '<hr />';
		}
		echo $offer_count;

	}
}
