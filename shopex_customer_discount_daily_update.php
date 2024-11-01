<?php
add_action('wp', 'Shopex_schedule_daily_database_operation');
	function Shopex_schedule_daily_database_operation() {
		if (!wp_next_scheduled('Shopex_daily_database_operation')) {
			wp_schedule_event(time(), 'daily', 'Shopex_daily_database_operation');
		}
	}

	function Shopex_run_daily_database_operation() {
		
		$table_name ='shopex_customer_and_offer';
            
		$query_sco ="SELECT * from $table_name";
		$lcheck_sco = $wpdb->get_results($query_sco);
		if( is_countable($lcheck_sco) && count($lcheck_sco) > 0) {
			$wpdb->prepare("UPDATE %s SET offer = '' ",array($table_name));
		}
        
           
		$table_name ="shopex_dpdis_name_and_target_q";
		$rows = $wpdb->get_results("SELECT name,fullq from $table_name");

		if( is_countable($rows) && count($rows) > 0) {

			foreach ( $rows as $row ) {

				$offername      = $row->name;
				$query          = $row->fullq;
				$ciphering      = "AES-128-CTR";
				$iv_length      = openssl_cipher_iv_length($ciphering); 
				$options        = 0; 
				$decryption_iv  = '1234567891011121'; 
				$decryption_key = "shopexenccode"; 

				$q = openssl_decrypt ($query, $ciphering, $decryption_key, $options, $decryption_iv); 
				$rows1 = $wpdb->get_results($q);

				if(is_countable($rows1) &&  count($rows1) > 0 )  {

					foreach ( $rows1 as $row1 ) {

						$cusmail = $row1->cusmail;
						$cusid   = $row1->cusid;

						if($cusid != 0 ) {

							$table_name ="shopex_customer_and_offer";
							$get_cusdis = $wpdb->get_row("SELECT * from $table_name WHERE cusid = '".$cusid."'");

							if( is_countable($get_cusdis) &&  count($get_cusdis) == 1){
								
								$pre_offer   = $get_cusdis->offer;
								$offerstring = $pre_offer.$offername."_next_";
								$table_name  ='shopex_customer_and_offer';
								$wpdb->prepare("UPDATE %s SET offer = %s WHERE cusid = %d ",array($table_name,$offerstring,$cusid));
								

							} else {

								$ofrname = $offername."_next_";
								$data = array('cusid' => $cusid,'cusmail'=> $cusmail,'offer'=>$ofrname);
								$table_name  ='shopex_customer_and_offer';
								$wpdb->insert($table_name,$data);
							}
						}
					}
				}
			}
		}
	}
	
	function Shopex_run_scheduled_daily_database_operation() {
		Shopex_run_daily_database_operation();
	}
	add_action('Shopex_daily_database_operation', 'Shopex_run_scheduled_daily_database_operation');

	

