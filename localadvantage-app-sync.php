<?php
/**
 * Plugin Name:     Local Advantage App Sync
 * Plugin URI:      https://localadvantage.com.au
 * Description:     Syncs App Data
 * Author:          Chris Bibby
 * Author URI:      https://chrisbibby.com.au
 * Text Domain:     localadvantage-app-sync
 * Domain Path:     /languages
 * Version:         1.0.1
 *
 * @package         Localadvantage_App_Sync
 */

// Your code starts here.
namespace LA_App_Sync;

require( __DIR__ ) . '/vendor/autoload.php';

add_action('plugins_loaded', function(){
	new Plugin(plugin_dir_url(__FILE__));
});
