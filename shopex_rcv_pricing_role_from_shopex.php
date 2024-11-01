<?php

	################ Custom REST API $_POST-Endpoint
	add_action('rest_api_init','shopex_cusendpoint');
	function shopex_cusendpoint(){
		register_rest_route(
			'postdpdis','/rcvdpdis/',
			array(
				'methods' => 'POST',
				'callback' => 'shopex_get_dynamic_pricing_and_discount_data',
                'permission_callback' => 'Shopex_dpdis_api_permission'
			)
		);
	}

	function Shopex_dpdis_api_permission($request){
		return true;
	}
    
	function shopex_get_dynamic_pricing_and_discount_data($request) {

		global $wpdb;
		
		if(!empty($request)) {

			$type   = sanitize_text_field($request['type']);
			$string = sanitize_text_field($request['password']);
			$name   = sanitize_text_field($request['name']);
			$status = sanitize_text_field($request['status']);
			$seg 	= sanitize_text_field($request['target']);
			$seg_id = sanitize_text_field($request['target_id']);
			
			
			# Delete if *similar-name* "Dynamic-pricing-and-offer" already exist
			$table_name ="shopex_dpdis";
        	$wpdb->delete( $table, array( 'dpdis_name' => $name) );
			
			
			if($seg_id == 0) {
				$forall = 1;
			}else{
				$forall = 0;
			}


			$f 		= sanitize_text_field($request['f']);
			$t 		= sanitize_text_field($request['t']);
			$osrun 	= sanitize_text_field($request['osrun']);
			$pr 	= sanitize_text_field($request['pr']);
			
			$table_name ="shopex_dpdis_name_and_target_q";
			$data = array('name' => $name,'fullq'=> $seg);
			$wpdb->insert($table_name,$data);
			
			
			$split = explode("_break_",$string);

			if($type == "q_dis") {
				
				$len = count($split);
				
				for($x = 0; $x < $len; $x++) {
					
					$sin = explode("shopex",$split[$x]);
					
					$offer_for_pro_or_cat  		= $sin[0];
					
					$offer_for_pro_or_cat_id   	= $sin[1];
					
					$unit_from  = $sin[2];
					
					$unit_to  	= $sin[3];
					
					$offer      = $sin[4];
					
					$offer_type = $sin[5];
					
					$dtail ="";
					
					$dtail = $unit_from."_next_".$unit_to."_next_".$offer."_next_"."$offer_type";
					
					$data = array('dpdis_name' => $name,'dpdis_type'=> $type,'pro_cat_all_id' => $offer_for_pro_or_cat_id, 'pro_cat_all' => $offer_for_pro_or_cat, 'dtail' => $dtail, 'status' => $status, 'for_all' => $forall, 'f'=> $f, 't' => $t, 'osrun'=> $osrun, 'pr'=>$pr);
					
					$table_name ="shopex_dpdis";
					
					$wpdb->insert($table_name,$data);
				
				}
				
			} elseif ($type == "gift_dis") {
				
				$len = count($split);
				
				for($x = 0; $x < $len; $x++) {
					
					$sin = explode("shopex",$split[$x]);
					
					$offer_for_pro_or_cat  		= $sin[0];
					
					$offer_for_pro_or_cat_id  	= $sin[1];
					
					$gifts  					= $sin[2];
					
					$min_item  					= $sin[3];
					
					$min_amount      			= $sin[4];
					
					$dtail 						="";
					
					$dtail = $gifts."_next_".$min_item."_next_".$min_amount;
					
					$data = array('dpdis_name' => $name,'dpdis_type'=> $type,'pro_cat_all_id' => $offer_for_pro_or_cat_id, 'pro_cat_all' => $offer_for_pro_or_cat, 'dtail' => $dtail, 'status' => $status,'for_all' => $forall, 'f'=> $f, 't' => $t, 'osrun'=> $osrun,'pr'=>$pr);
					
					$table_name ="shopex_dpdis";
					
				
					$wpdb->insert($table_name,$data);
				}
				
			} elseif ($type == "catdis") {
				
				$len = count($split);
				
				for($x = 0; $x < $len; $x++) {
					
					$sin    = explode("shopex",$split[$x]);
            
					$dis    = $sin[0];

					$dis_type   = $sin[1];

					$catid      = $sin[2];

					$dtail      ="";

					$shopex_pro_or_cat  		= 'cat';

					$shopex_offer_rule_id     = $catid;
					
					$dtail = $dis."_next_".$dis_type."_next_".$catid;
					
					$data = array('dpdis_name' => $name,'dpdis_type'=> $type,'pro_cat_all_id' => $shopex_offer_rule_id, 'pro_cat_all' => $shopex_pro_or_cat, 'dtail' => $dtail, 'status' => $status,'for_all' => $forall, 'f'=> $f, 't' => $t, 'osrun'=> $osrun,'pr'=>$pr);
					
					$table_name ="shopex_dpdis";
					
					$wpdb->insert($table_name,$data);
				}
				
			} elseif ($type == "entire") {
				
				$table_name = "shopex_entire";
			
				if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

					$charset_collate = $wpdb->get_charset_collate();

						$sql = "CREATE TABLE $table_name (
								id	 			mediumint(9) NOT NULL AUTO_INCREMENT,
								name     		VARCHAR(200),
								fullq           TEXT,
								PRIMARY KEY (id)
							) $charset_collate;";

						require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

						dbDelta( $sql );
				}
				
				$offer_on_pro_or_cat_id = 9999;
				$offer_on_pro_or_cat = "shopex9999";
				
				$data = array('dpdis_name' => $name,'dpdis_type'=> $type,'pro_cat_all_id' => $offer_on_pro_or_cat_id, 'pro_cat_all' => $offer_on_pro_or_cat, 'dtail' => $string, 'status' => $status,'for_all' => $forall, 'f'=> $f, 't' => $t, 'osrun'=> $osrun,'pr'=>$pr);

				$table_name ="shopex_dpdis";

				global $wpdb;

				$wpdb->insert($table_name,$data);
				
			}
		}
	}
