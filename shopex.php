<?php
/*
Plugin Name: Shopex
Plugin URI: https://shopex.io/
Description: WooCommerce store analytics tool to understand customer behaviors better, segment and visualize store data, specifric price offer for the specific targeted customer segment.
Author: ahm shaogat alam
Author URI: https://shopex.io/
Version: 1.0.0
Text Domain: shopex
Domain Path: /languages
License: GPL2
*/
include 'shopex_custom_database_tables.php';
	
add_action('rest_api_init','shopex_ep_runpause');
function shopex_ep_runpause(){
	register_rest_route(
		'postdpstatus','/rcvdpstatus/',
		array(
			'methods' => 'POST',
			'callback' => 'shopex_rcv_dpsta',
			'permission_callback' => 'permissionCheck1'
		)
	);
}

function shopex_rcv_dpsta($request) {
	global $wpdb;
	if(!empty($request)) {
		$name       = sanitize_text_field($request['name']);
		$status     = sanitize_text_field($request['status']);
		$table_name ="shopex_dpdis";
		//$wpdb->query($wpdb->prepare("UPDATE $table_name SET status='".$status."' WHERE dpdis_name = '".$name."'"));
		$wpdb->prepare("UPDATE %s SET status=%s WHERE dpdis_name = %s",array( $table_name, $status, $name ));
	}
}

function permissionCheck1($request){
	return true;
}
	

add_action('rest_api_init','shopex_cam_endpoint');
function shopex_cam_endpoint(){
	register_rest_route('post_cam_data','/rcv_camdata/', 
						array('methods' => 'POST',
							  'callback' => 'shopex_campaign_data',
							  'permission_callback' => 'permissionCheck2'
							 )
					   );
}
function shopex_campaign_data($request) {

	global $wpdb;

	if(!empty($request)) {

		$src      = sanitize_text_field($request['src_name']);
		$adds_url = sanitize_text_field($request['add_url']);

		$table_name = "shopex_campaign_src_addsurl";

		$data = array('adds_url' => $adds_url,'src'=> $src);
		$wpdb->insert($table_name,$data);
	}
}
function permissionCheck2($request){
	return true;
}
	

include 'shopex_new_order.php';

include 'shopex_rcv_pricing_role_from_shopex.php';

include 'shopex_dprice_discount.php';

//include 'shopex_live_cart.php';

//include 'shopex_live_visitor.php';

add_action('before_delete_post', function($id) {
	$post_type = get_post_type($id);
	if ($post_type !== 'shop_order') {
		return;
	}
	global $wpdb;
	$table = 'shop_order';
	$wpdb->delete( $table, array( 'orderid' => $id ) );
	$table = 'shop_order_lineitem';
	$wpdb->delete( $table, array( 'orderid' => $id ) );
}, 10, 1);


#	Run a function when the WooCommerce order is updated
// add_action( 'edit_post_shop_order', 'shopex_edit_post_shop_order_callback', 99, 2 );
// //add_action( 'woocommerce_update_order', 'shopex_woocommerce_update_order_callback', 99, 1 );
// function shopex_edit_post_shop_order_callback( $post_ID, $post ) {
// //function shopex_woocommerce_update_order_callback( $post_ID ) {
// 	global $wpdb;
//     // gets the WC_Order object
//     if ( get_post_type( $post_ID ) == 'shop_order' ) {
//     	$shopex_edited_id = $post_ID;
//         $data = array('edited' => $shopex_edited_id);
//         $table_name ="shopex_order_edited";
//         $res = $wpdb->insert($table_name,$data);

//     }
// }


// Customer offer - Daily update 
global $wpdb;
if (!defined('ABSPATH')) {
	require_once(ABSPATH . 'wp-config.php');
}
if (!function_exists('wp')) {
	require_once(ABSPATH . 'wp-load.php');
}


include 'shopex_customer_discount_daily_update.php';