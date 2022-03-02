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
		echo 'yep we go get oofers';
		$response = wp_remote_retrieve_body(wp_remote_get('https://app.localadvantage.com.au/api/v2/offers'));
		$response = json_decode($response,true);
		$offers[] = $response;

	}
}
