<?php
# Send Confirmation After Cart To "Order-create" operation   
function shopex_confirm_cart_to_order_convert($order_id) {
	
	global $wpdb;
	
	$mailid = "";
	
	$utm_cam=0;
	$utm_med=0;
	$utm_src=0;
	$shopex_campaign_adds_url=0;
	
	
	if(isset($_COOKIE['shopex_utm_campaign']) && !empty($_COOKIE['shopex_utm_campaign'])){
		$utm_cam = sanitize_text_field($_COOKIE['shopex_utm_campaign']);
	}

	if(isset($_COOKIE['shopex_utm_source']) && !empty($_COOKIE['shopex_utm_source'])){
		$utm_src = sanitize_text_field($_COOKIE['shopex_utm_source']);
	}

	if(isset($_COOKIE['shopex_utm_medium']) && !empty($_COOKIE['shopex_utm_medium'])){
		$utm_med = sanitize_text_field($_COOKIE['shopex_utm_medium']);
	}

    if(isset($_COOKIE['shopex_campaign_adds_url']) && !empty($_COOKIE['shopex_campaign_adds_url'])){
		$shopex_campaign_adds_url = sanitize_text_field($_COOKIE['shopex_campaign_adds_url']);
	}
	
	# DETECT CAMPAIGN URL IN SHOPEX
	$campaign_source = "";
	$rows = $wpdb->get_results("SELECT * FROM shopex_campaign_src_addsurl WHERE (adds_url =  '". $shopex_campaign_adds_url ."')");
    if($rows) {
		foreach ( $rows as $row ) {
			$campaign_source = $row->src;
		}
	}
	
	# Live Cart Completed Response sending
	$userid = 0;
	if(is_user_logged_in()){
		$current_user = wp_get_current_user();
		$mailid = $current_user->user_email;
		$userid = $current_user->ID;
	}
	
	$url 			= 'https://shopex.io/cart/cart_completed.php';
	$base 			= get_site_url();
	$base           = $base."/";

	if (filter_var($mailid, FILTER_VALIDATE_EMAIL)){
	
		$body = [
			'mail' => $mailid,
			'urlshop' => $base,
			'userid'  => $userid,
			'utm_cam' => $utm_cam,
			'utm_med' => $utm_med,
			'utm_src' => $utm_src,
			'orderid' => $order_id,
			'campain_src' => $campaign_source
			
		];

		$body = wp_json_encode( $body );

		$response = wp_remote_post( $url, array(
			'method'      => 'POST',
			'body'        => $body,
			'headers' => array( 
				'Content-type' => 'application/json'
			)
		));

		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			//echo "Something went wrong: $error_message";
		} else {
			#
		}
		
		unset( $_COOKIE['shopex_collected_mail'] );
    	setcookie("shopex_collected_mail", '', time() - 3600, "/"); 
		
		unset( $_COOKIE['shopex_collected_mail_form_show'] );
    	setcookie("shopex_collected_mail_form_show", '', time() - 3600, "/"); 
		
		unset($_COOKIE['shopex_utm_campaign']);
    	setcookie("shopex_utm_campaign", '', time() - 3600, "/"); 
		
		unset( $_COOKIE['shopex_utm_source'] );
    	setcookie("shopex_utm_source", '', time() - 3600, "/"); 
		
		unset( $_COOKIE['shopex_utm_medium'] );
    	setcookie("shopex_utm_medium", '', time() - 3600, "/"); 
		
		unset( $_COOKIE['shopex_campaign_adds_url'] );
    	setcookie("shopex_campaign_adds_url", '', time() - 3600, "/"); 
	}

	
	#	Engage Email Order Created Response sending
	if(isset($_COOKIE['shopex_segengage'])){
		$detect_url_perameter = sanitize_text_field($_COOKIE['shopex_segengage']);
    }		


	if($detect_url_perameter != "") {
		$url = 'https://shopex.io/engage/engage_orders_from_engage.php';
		$body = [
			'mail' => $mailid,
			'userid'  => $userid,
			'order_info' => $detect_url_perameter,
			'order_id' => $order_id,
			'urlshop' => $base,
		];
		$body = wp_json_encode( $body );
        $response = wp_remote_post( $url, array(
			'method'      => 'POST',
			'body'        => $body,
			'headers' => array( 
				'Content-type' => 'application/json'
			)
		));
		if ( is_wp_error( $response ) ) {
			$error_message = $response->get_error_message();
			//echo "Something went wrong: $error_message";
		} else {
			#
		}
		unset($_COOKIE['shopex_segengage']);
    	setcookie("shopex_segengage", '', time() - 3600, "/");
	}
	
}


add_action('woocommerce_checkout_update_order_meta', 'shopex_confirm_cart_to_order_convert');
# Send Confirmation After Cart To "Order create" operation  








# Check engage automation email URL perameter
function shopex_engage_email_utm_parameter() {
	
	if(isset($_GET['segengage']) && !empty($_GET['segengage'])) {
		
		$detect_url_perameter = sanitize_text_field($_GET['segengage']);		
		$cookie_name  = "shopex_segengage";
		$cookie_value = $detect_url_perameter;
		setcookie($cookie_name, $cookie_value, time() + (86400 * 1), "/"); 
	}
	
	if(isset($_GET['utm_source']) && !empty($_GET['utm_source'])) {
		
		$detect_url_perameter = sanitize_text_field($_GET['utm_source']);		
		$cookie_name  = "shopex_utm_source";
		$cookie_value = $detect_url_perameter;
		setcookie($cookie_name, $cookie_value, time() + (86400 * 1), "/"); 
	}
	
	if(isset($_GET['utm_medium']) && !empty($_GET['utm_medium'])) {
		
		$detect_url_perameter = sanitize_text_field($_GET['utm_medium']);		
		$cookie_name  = "shopex_utm_medium";
		$cookie_value = $detect_url_perameter;
		setcookie($cookie_name, $cookie_value, time() + (86400 * 1), "/"); 
	}
	
	
	if(isset($_GET['utm_campaign']) && !empty($_GET['utm_campaign'])) {
		
		$detect_url_perameter = sanitize_text_field($_GET['utm_campaign']);
		$cookie_name  ="shopex_utm_campaign";
		$cookie_value =$detect_url_perameter;
		setcookie($cookie_name, $cookie_value, time() + (86400 * 1), "/"); 
	}
	
	
	$shopex_cam_url="";
	
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {  
		$shopex_cam_url = "https://"; 
	} 
	else { $shopex_cam_url = "http://";  } 
    // Append the host(domain name, ip) to the URL.   
    $shopex_cam_url.= $_SERVER['HTTP_HOST'];   
    // Append the requested resource location to the URL   
    $shopex_cam_url.= $_SERVER['REQUEST_URI'];    
    $cookie_name  = "shopex_campaign_adds_url";
	$cookie_value = $shopex_cam_url;
	//echo "CAM_SRC".$cookie_value;
	setcookie($cookie_name, $cookie_value, time() + (86400 * 1), "/"); 
}

add_action('init', 'shopex_engage_email_utm_parameter');



