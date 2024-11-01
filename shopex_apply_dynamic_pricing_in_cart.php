<?php

    // Calculate prices at cart
    add_action( 'woocommerce_before_calculate_totals', 'shopex_apply_dynamic_pricing', 9999 );

    function shopex_apply_dynamic_pricing( $cart ) {
        
        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
            return;

        if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
            return;

        // Loop through cart items
        
        global $wpdb;
        global $woocommerce;
        
        $gift_flag = 0;
        
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {

            $session_found  = 0;
            $id             = $cart_item['product_id'];
            $shopex_pro_id  = $id; 
                
            $shopex_arr = array();
            $shopex_product_cats_ids = wc_get_product_term_ids( $id, 'product_cat' );
            foreach($shopex_product_cats_ids as $id){
                $shopex_arr[] = $id;
            }
            $shopex_arr = "('" . implode("','", $shopex_arr) . "')";
        
        
            $product__      = $cart_item['data'];
            $quantity       = $cart_item['quantity'];
            $line_sub_total = $cart->get_product_subtotal( $product__, $cart_item['quantity'] );
            
            //$line_sub_total = $cart_item['line_subtotal']; 

            $cookie_pid     = 'shopexprod_'.$id;
            
            $shopex_cookiename = 'shopex_entire'.$id;
            
            if(isset($_SESSION[$shopex_cookiename])) {
            
                $session_found = 1;

                $offer_name = sanitize_text_field($_SESSION[$shopex_cookiename]);
                $get_all_rows = $wpdb->get_results("SELECT * from shopex_dpdis WHERE dpdis_name = '".$offer_name."' AND dpdis_type = 'entire' AND status = 1");
                                        
                if(is_countable($get_all_rows) && count($get_all_rows) > 0) {

                    foreach ( $get_all_rows as $shopex_row1 ) {

                        $dt  = $shopex_row1->dtail;

                        $dtt = explode("_next_",$dt);

                        $shopex_offer = $dtt[0];
                        $shopex_offertype = $dtt[1];

                        if($shopex_offertype == 'percent') {
                            $grp = $cart_item['data']->get_regular_price();
                            $newprice = $grp - (($grp/100)*$shopex_offer);
                            $cart_item['data']->set_price( $newprice );

                        }else if($shopex_offertype == 'amount') {
                            $grp = $cart_item['data']->get_regular_price();
                            $newprice = $grp - $shopex_offer;
                            $cart_item['data']->set_price( $newprice );

                        }else if($shopex_offertype == 'fixedprice') {
                            $cart_item['data']->set_price( $shopex_offer );
                        }

                    }
                }
            
            } else if(isset($_SESSION[$cookie_pid])) {

                $session_found = 1;
                
                $applied_price_role_name = sanitize_text_field($_SESSION[$cookie_pid]);
                
                $get_cusdis = $wpdb->get_results("SELECT * from shopex_dpdis WHERE dpdis_name = '".$applied_price_role_name."' AND pro_cat_all_id = '".$id."'");
                            
                if(is_countable($get_cusdis) && count($get_cusdis) > 0) {
                    
                    foreach($get_cusdis as $row) {
                        
                        $type = $row->dpdis_type;
                        
                        if($type == 'q_dis') {
                            
                            $dis    = $row->dtail;
                            $offer  = explode("_next_",$dis);
                            $from   = $offer[0];
                            $to     = $offer[1];
                            $dis    = $offer[2];
                            $type   = $offer[3];

                            if($quantity >= $from && $quantity <= $to) {

                                if($type == 'percent') {
                                    $grp = $cart_item['data']->get_regular_price();
                                    $newprice = $grp - (($grp/100)*$dis);
                                    $cart_item['data']->set_price( $newprice );
								}else if($type == 'amount') {
                                    $grp = $cart_item['data']->get_regular_price();
                                    $newprice = $grp - $dis;
                                    $cart_item['data']->set_price( $newprice );
								}else if($type == 'fixedprice') {
									$cart_item['data']->set_price( $dis );
                                }
                            }
                        
                        } else if($type == 'gift_dis') {
                            
                            $dtail      = $row->dtail;
                            $break      = explode("_next_",$dtail);
                            $gift       = $break[0];
                            
                            $tmp_gift   = explode("next_gift",$gift);
                            
                            $product    = wc_get_product($gift);
                            $name       = $product->get_name();
                            $minitem    = $break[1];
                            $minamount  = $break[2];
                            
                            if( ($minitem == 'NOITEM' && $line_sub_total >= $minamount) || ($minamount == 'NOAMOUNT' && $quantity >= $minitem) ) {
                                
                                
                                #   Multiple product Gift
                                if(count($tmp_gift) > 1) {

                                    $track_gifted = "shopex_gift_added".$id;
                                    
                                    if(!(isset($_SESSION[$track_gifted]))) {

                                        $_SESSION[$track_gifted] = 1;

                                        foreach($tmp_gift as $proid) {
                                            $proid = (int)$proid;
                                            $woocommerce->cart->add_to_cart($proid);
                                            
                                        }
                                        
                                        if(!(isset($_SESSION['shopex_gift_added_id']))) {
                                            $_SESSION['shopex_gift_added_id'] = $proid;
                                        
                                        } else {
                                            $_SESSION['shopex_gift_added_id'] .= "NEXT_".$proid;
                                        }
                                        
                                    }

                                #   One product Gift    
                                } else if(count($tmp_gift) == 1) {
                                    
                                    $track_gifted = "shopex_gift_added".$id;

                                    if(!(isset($_SESSION[$track_gifted]))) {
                                        
                                        $_SESSION[$track_gifted] = 1;

                                        $proid = (int)$gift;
                                        $woocommerce->cart->add_to_cart($proid);
                                        
                                        #   Set a Session to sub-tract Free Product Price in "woocommerce_cart_calculate_fees" Hook
                                        if(!(isset($_SESSION['shopex_gift_added_id']))) {
                                            $_SESSION['shopex_gift_added_id'] = $proid;

                                        } else {
                                            $_SESSION['shopex_gift_added_id'] .= "NEXT_".$proid;
                                        }
                                    }
                                }
                            }

                        } else if($type == 'catdis') {}
                    }           
                }

            } else {
                
                $cats = $cart_item['data']->get_category_ids();
                
                if(count($cats) > 0) {
                    
                    foreach($cats as $cat) {

                        $cookie_cid = 'shopexcat_'.$cat;
                        
                        if (isset($_SESSION[$cookie_cid])) {

                            $session_found = 1;

                            $cookie = sanitize_text_field($_SESSION[$cookie_cid]);
                            
                            $get_cusdis = $wpdb->get_results("SELECT * from shopex_dpdis WHERE dpdis_name = '".$cookie."' AND pro_cat_all_id = '".$cat."'");
                                        
                            if(is_countable($get_cusdis) && count($get_cusdis) > 0) {
                                
                                foreach($get_cusdis as $row) {
                                    
                                    $type = $row->dpdis_type;
                                    
                                    if($type == 'q_dis') {
                                        
                                        $dis    = $row->dtail;
                                        $offer  = explode("_next_",$dis);
                                        $from   = $offer[0];
                                        $to     = $offer[1];
                                        $dis    = $offer[2];
                                        $type   = $offer[3];
                
                                        if($quantity >= $from && $quantity <= $to) {

                                            if($type == 'percent') {

                                                $grp = $cart_item['data']->get_regular_price();

                                                $newprice = $grp - (($grp/100)*$dis);

                                                $cart_item['data']->set_price( $newprice );

                                            }else if($type == 'amount') {

                                                $grp = $cart_item['data']->get_regular_price();

                                                $newprice = $grp - $dis;

                                                $cart_item['data']->set_price( $newprice );

                                            }else if($type == 'fixedprice') {

                                                $cart_item['data']->set_price( $dis );
                                            }
                                        }
                                        
                                    } else if($type == 'gift_dis') {
                                        
                                        $dtail      = $row->dtail;
                                        $break      = explode("_next_",$dtail);
                                        $gift       = $break[0];

                                        $tmp_gift   = explode("next_gift",$gift);

                                        $product    = wc_get_product($gift);
                                        $name       = $product->get_name();
                                        $minitem    = $break[1];
                                        $minamount  = $break[2];

                                        if( ($minitem == 'NOITEM' && $line_sub_total >= $minamount) || ($minamount == 'NOAMOUNT' && $quantity >= $minitem) ) {

                                            #   Multiple product Gift
                                            if(count($tmp_gift) > 0) {

                                                $track_gifted = "shopex_gift_added".$id;

                                                if(!(isset($_SESSION[$track_gifted]))) {

                                                    $_SESSION[$track_gifted] = 1;

                                                    foreach($tmp_gift as $proid) {
                                                        $proid = (int)$proid;
                                                        $woocommerce->cart->add_to_cart($proid);

                                                    }


                                                    foreach($tmp_gift as $proid) {

                                                        $proid = (int)$proid;
                                                        $cart_item_quantities = $woocommerce->cart->get_cart_item_quantities();
                                                        $product_qty_in_cart  = isset( $cart_item_quantities[ $product_id ] ) ? $cart_item_quantities[ $product_id ] : null;

                                                        #   If Single Quantity Is in Cart then Set its price as 0 to give it free 

                                                        if($product_qty_in_cart == 1){

                                                            foreach ( $cart_object->get_cart() as $key => $value ) {
                                                                if ( $value['product_id'] == $proid ) {
                                                                    $value['data']->set_price(0); 
                                                                }
                                                            }

                                                        #   If Multiple Quantity is added in cart then Save its Id and leter in 
														#   "woocommerce_cart_calculate_fees" Hook sub-tract its single Quantity price from total cost;      
                                                        } else {

                                                            #   Set a Session to sub-tract Free Product Price in "woocommerce_cart_calculate_fees" Hook
                                                            if(!(isset($_SESSION['shopex_gift_added_id']))) {
                                                                $_SESSION['shopex_gift_added_id'] = $proid;
                                                            }else{
                                                                $_SESSION['shopex_gift_added_id'] .= "NEXT_".$proid;
                                                            }

                                                        }
                                                    }

                                                }

                                            #   Single product Gift 
                                            } else {

                                                $track_gifted = "shopex_gift_added".$id;

                                                if(!(isset($_SESSION[$track_gifted]))){

                                                    $_SESSION[$track_gifted] = 1;

                                                    $proid = (int)$gift;
                                                    $woocommerce->cart->add_to_cart($proid);

                                                    $cart_item_quantities   = $woocommerce->cart->get_cart_item_quantities();
                                                    $product_qty_in_cart=isset($cart_item_quantities[$product_id]) ? $cart_item_quantities[ $product_id ] : null;

                                                    if($product_qty_in_cart == 1){

                                                        foreach ( $cart_object->get_cart() as $key => $value ) {
                                                            if ( $value['product_id'] == $proid ) {
                                                                $value['data']->set_price(0); 
                                                            }
                                                        }

                                                    } else {

                                                        #   Set a Session to sub-tract Free Product Price in "woocommerce_cart_calculate_fees" Hook
                                                        if(!(isset($_SESSION['shopex_gift_added_id']))) {
                                                            $_SESSION['shopex_gift_added_id'] = "NEXT_".$proid;
                                                        }

                                                    }

                                                }
                                            }
                                        }
                                        
                                        
                                    } else if($type == 'catdis') {
                                        
                                        $dtail = $row->dtail;
                                                
                                        $break = explode("_next_",$dtail);

                                        $amount_percent_price = $break[0];

                                        $amount_percent_price = (int)$amount_percent_price;

                                        $type = $break[1];

                                        $cat  = $break[2];

                                        $cat  = (int)$cat;

                                        if($type == 'percent') {

                                            $get_reguler_price = $cart_item['data']->get_regular_price();

                                            $newprice = $get_reguler_price - (($get_reguler_price/100)*$amount_percent_price);

                                            $cart_item['data']->set_price( $newprice );

                                        }else if($type == 'amount') {

                                            $get_reguler_price = $cart_item['data']->get_regular_price();

                                            $newprice = $get_reguler_price - $amount_percent_price;

                                            $cart_item['data']->set_price( $newprice );

                                        }else if($type == 'fixedprice') {

                                            $cart_item['data']->set_price( $amount_percent_price );
                                        }
                                    } 
                                }           
                            }
                        }
                    }
                }
            }


            #   If product is added to cart in archive page 
            
            if($session_found == 0) {

                ####

                $shopex_flag = 0;
                
                $table_name ="shopex_dpdis";
        
                $shopex_query ="SELECT Distinct dpdis_name, dpdis_type, status, pro_cat_all, pro_cat_all_id from $table_name 
								WHERE status = 1 AND dpdis_type = 'entire' OR (pro_cat_all = 'prod' AND pro_cat_all_id = '".$shopex_pro_id."') 
								OR ( pro_cat_all = 'cat' AND pro_cat_all_id IN $shopex_arr) AND 
								status != 0 ORDER BY pr ASC";
                
                $get_dp_dis_on_current_product = $wpdb->get_results($shopex_query);
                
                if(count($get_dp_dis_on_current_product)) {
                    
                    foreach ( $get_dp_dis_on_current_product as $shopex_row ) {
                        
                        if($shopex_flag == 0) {

                            $dpdis_name           = $shopex_row->dpdis_name;
                            $dpdis_type           = $shopex_row->dpdis_type;
                            $dpdis_procat         = $shopex_row->pro_cat_all;
                            $dpdis_id             = $shopex_row->pro_cat_all_id;
                            $shopex_status        = $shopex_row->status;
                            $shopex_osrun_disable = $shopex_row->osrun;
                            
                            if($shopex_onsale == 1 && $shopex_osrun_disable == 1) {$shopex_status = 0;}

                            if ( $shopex_status == 1 && $userid != 0 ) {

								$table ="shopex_customer_and_offer";

								$get_offer_for_curr_cus = $wpdb->get_results("SELECT * from $table WHERE cusid = '".$userid."'");

								if(is_countable($get_offer_for_curr_cus) && count($get_offer_for_curr_cus) > 0) { 

									foreach($get_offer_for_curr_cus as $shopex_cusoffer) {

										$offerstring    = $shopex_cusoffer->offer;
										$offers         = explode("_next_",$offerstring);

										if(in_array($dpdis_name, $offers) && $shopex_flag == 0) {

											$shopex_flag = 1;

											if($dpdis_type != 'entire') {

												$flag = 1;

												$get_all_rows = $wpdb->get_results("SELECT * from shopex_dpdis WHERE dpdis_name = '".$dpdis_name."' AND 																							pro_cat_all_id = '".$dpdis_id."' AND pro_cat_all = '".$dpdis_procat."'");

												if(is_countable($get_all_rows) && count($get_all_rows) > 0) {

													foreach ( $get_all_rows as $shopex_row1 ) {

														if($dpdis_type == 'q_dis') {

                                                                #
															$dis    = $shopex_row1->dtail;
															$offer  = explode("_next_",$dis);
															$from   = $offer[0];
															$to     = $offer[1];
															$dis    = $offer[2];
															$type   = $offer[3];

															if($quantity >= $from && $quantity <= $to) {

																if($type == 'percent') {
																	$grp = $cart_item['data']->get_regular_price();
																	$newprice = $grp - (($grp/100)*$dis);
																	$cart_item['data']->set_price( $newprice );

																}else if($type == 'amount') {
																	$grp = $cart_item['data']->get_regular_price();
																	$newprice = $grp - $dis;
																	$cart_item['data']->set_price( $newprice );

																}else if($type == 'fixedprice') {

																	$cart_item['data']->set_price( $dis );
																}
															} 
															#

														} else if($dpdis_type == 'gift_dis') {

															$dtail      = $shopex_row1->dtail;
															$break      = explode("_next_",$dtail);
															$gift       = $break[0];

															$tmp_gift   = explode("next_gift",$gift);

															$product    = wc_get_product($gift);
															$name       = $product->get_name();
															$minitem    = $break[1];
															$minamount  = $break[2];

															if(($minitem == 'NOITEM' && $line_sub_total >= $minamount) || 
															   ($minamount == 'NOAMOUNT' && $quantity >= $minitem)){


																#   Multiple product Gift
																if(count($tmp_gift) > 1) {

																	$track_gifted = "shopex_gift_added".$id;

																	if(!(isset($_SESSION[$track_gifted]))) {

																		$_SESSION[$track_gifted] = 1;

																		foreach($tmp_gift as $proid) {
																			$proid = (int)$proid;
																			$woocommerce->cart->add_to_cart($proid);

																		}

																		if(!(isset($_SESSION['shopex_gift_added_id']))) {
																			$_SESSION['shopex_gift_added_id'] = $proid;

																		} else {
																			$_SESSION['shopex_gift_added_id'] .= "NEXT_".$proid;
																		}

																	}

																	#   One product Gift    
																} else if(count($tmp_gift) == 1) {

																	$track_gifted = "shopex_gift_added".$id;

																	if(!(isset($_SESSION[$track_gifted]))) {

																		$_SESSION[$track_gifted] = 1;

																		$proid = (int)$gift;
																		$woocommerce->cart->add_to_cart($proid);

																		# Set a Session to sub-tract Free Product Price in  
																		# "woocommerce_cart_calculate_fees" Hook
																		if(!(isset($_SESSION['shopex_gift_added_id']))) {
																			$_SESSION['shopex_gift_added_id'] = $proid;
																		} else {
																			$_SESSION['shopex_gift_added_id'] .= "NEXT_".$proid;
																		}
																	}
																}
															}

														} else if($dpdis_type == 'catdis') {

															$dtail = $shopex_row1->dtail;

															$break = explode("_next_",$dtail);

															$amount_percent_price = $break[0];

															$amount_percent_price = (int)$amount_percent_price;

															$type = $break[1];

															$cat  = $break[2];

															$cat  = (int)$cat;

															if($type == 'percent') {

																$get_reguler_price = $cart_item['data']->get_regular_price();

																$newprice = $get_reguler_price - (($get_reguler_price/100)*$amount_percent_price);

																$cart_item['data']->set_price( $newprice );

															}else if($type == 'amount') {

																$get_reguler_price = $cart_item['data']->get_regular_price();

																$newprice = $get_reguler_price - $amount_percent_price;

																$cart_item['data']->set_price( $newprice );

															}else if($type == 'fixedprice') {

																$cart_item['data']->set_price( $amount_percent_price );
															}

														}
													}
												}

											} else if($dpdis_type == 'entire') {

												$get_all_rows=$wpdb->get_results("SELECT * from shopex_dpdis WHERE dpdis_name='".$dpdis_name."' AND 
																				dpdis_type ='entire' AND status = 1");

												if(is_countable($get_all_rows) && count($get_all_rows) > 0) {

													foreach ( $get_all_rows as $shopex_row1 ) {

														$dt  = $shopex_row1->dtail;

														$dtt = explode("_next_",$dt);

														$shopex_offer = $dtt[0];
														$shopex_offertype = $dtt[1];

														if($shopex_offertype == 'percent') {
															$grp = $cart_item['data']->get_regular_price();
															$newprice = $grp - (($grp/100)*$shopex_offer);
															$cart_item['data']->set_price( $newprice );

														}else if($shopex_offertype == 'amount') {
															$grp = $cart_item['data']->get_regular_price();
															$newprice = $grp - $shopex_offer;
															$cart_item['data']->set_price( $newprice );

														}else if($shopex_offertype == 'fixedprice') {
															$cart_item['data']->set_price( $shopex_offer );
														}
													}
												}
											}
										}
									}
								} 
							}
						}
					} 
				}
			}
		}
	}
