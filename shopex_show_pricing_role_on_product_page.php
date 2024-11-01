<?php 

add_action( 'woocommerce_before_add_to_cart_form', 'shopex_after_add_to_cart_dynamic_price', 11 , 6 );

function shopex_after_add_to_cart_dynamic_price() {
    
    global $wpdb;
    
    $shopex_current_user = wp_get_current_user();
    $userid = $shopex_current_user->ID;
                    
    
    # prepare offer view
    
    global $product;
    $shopex_pro_id = $product->get_id();
    
    $shopex_onsale = 0;
    
    $shopex_is_on_sale = $product->is_on_sale();
    if($shopex_is_on_sale){
        $shopex_onsale = 1;
    }
    
    
    $shopex_arr = array();
    $shopex_product_cats_ids = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
    foreach($shopex_product_cats_ids as $id){
        $shopex_arr[] = $id;
    }
    $shopex_arr = "('" . implode("','", $shopex_arr) . "')";
    
    
    
    $flag        = 0;
    $dpdis_name  = "";
    $cname       = "";
    $shopex_flag = 0;
    $shopex_discount_div ="<div class='row dpdis'>";
    
    
    
    $table_name ="shopex_dpdis";
    
    $shopex_query ="SELECT Distinct dpdis_name,dpdis_type,status,pro_cat_all,pro_cat_all_id 
                    FROM $table_name 
                    WHERE status = 1 
					AND 
					dpdis_type = 'entire' 
                    OR (pro_cat_all = 'prod' AND pro_cat_all_id = '".$shopex_pro_id."') 
                    OR ( pro_cat_all = 'cat' AND pro_cat_all_id IN $shopex_arr) 
                    AND status != 0 
                    ORDER BY pr ASC LIMIT 1";

    
    $get_dp_dis_on_current_product = $wpdb->get_results($shopex_query);
    
    if(is_countable($get_dp_dis_on_current_product) && count($get_dp_dis_on_current_product) > 0 ) {
        
        foreach ( $get_dp_dis_on_current_product as $shopex_row ) {
            
            if($shopex_flag == 0) {

                $dpdis_name           = $shopex_row->dpdis_name;
                $dpdis_type           = $shopex_row->dpdis_type;
                $dpdis_procat         = $shopex_row->pro_cat_all;
                $dpdis_id             = $shopex_row->pro_cat_all_id;
                $shopex_status        = $shopex_row->status;
                $shopex_osrun_disable = $shopex_row->osrun;
                
                if($shopex_onsale == 1 && $shopex_osrun_disable == 1) {
                    $shopex_status = 0;
                }

                if($shopex_status == 1) {

                    if ( $userid != 0 ) {

                        $table = "shopex_customer_and_offer";

                        $get_offer_for_curr_cus = $wpdb->get_results("SELECT * FROM $table WHERE cusid = '".$userid."'");

                        if(is_countable($get_offer_for_curr_cus) && count($get_offer_for_curr_cus) > 0) { 

                            foreach($get_offer_for_curr_cus as $shopex_cusoffer) {

                                $offerstring    = $shopex_cusoffer->offer;
                                $offers         = explode("_next_",$offerstring);

                                if(in_array($dpdis_name, $offers) && $flag == 0) {

                                    $shopex_flag = 1;

                                    if($dpdis_type != 'entire') {

                                        $cname              = 'shopex'.$dpdis_procat."_".$dpdis_id;
                                        $_SESSION[$cname]   = $dpdis_name;
                                        $flag = 1;

                                        $get_all_rows = $wpdb->get_results("SELECT * from shopex_dpdis WHERE dpdis_name = '".$dpdis_name."' AND 
																			pro_cat_all_id = '".$dpdis_id."' AND pro_cat_all = '".$dpdis_procat."'");

                                        if(is_countable($get_all_rows) && count($get_all_rows) > 0) {

                                            foreach ( $get_all_rows as $shopex_row1 ) {

                                                if($dpdis_type == 'q_dis') {

                                                    $dtail      = $shopex_row1->dtail;
                                                    $break      = explode("_next_",$dtail);
                                                    $from       = $break[0];
                                                    $to         = $break[1];
                                                    $offer      = $break[2];
                                                    $offertype  = $break[3];
                                                    $ofr        = "";

                                                    if($offertype == 'percent'){
                                                        $ofr = $offer."% Off";

                                                    }else if($offertype == 'amount'){
                                                        $ofr = $offer."$ Off"; 

                                                    }else{
                                                        $ofr = $offer."$/per unit"; 
                                                    }

                                                    $shopex_discount_div .= "<button value=".$to." 
																				style='color : coral;background:white;margin:10px;font-size:15px; padding : 8px;'> 
																				BUY&nbsp;".$from." - ".$to." [".$ofr."] 
																			 </button>&nbsp;";

                                                } else if($dpdis_type == 'gift_dis') {

                                                    $dtail      = $shopex_row1->dtail;

                                                    $break      = explode("_next_",$dtail);

                                                    $gift       = $break[0];

                                                    $shopex_product = wc_get_product($gift);

                                                    $name       = $shopex_product->get_name();

                                                    $minitem    = $break[1];

                                                    $minamount  = $break[2];

                                                    if($minitem == 'NOITEM') {

														$shopex_discount_div .= "<button style='color:red; background : white;'> 
																					Spend ".$$minamount." on this product to get ".$name." For free
																				</button>&nbsp;";

                                                    } else if ($minamount == 'NOAMOUNT'){

                                                        $shopex_discount_div .= "<button  style='color:red;  background : white;'> 
																					Buy at-least ".$minitem." Unit from  this product to get ".$name." For free
																				</button>&nbsp;";
													}


                                                } else if($dpdis_type == 'catdis') {

                                                    $dtail      = $shopex_row1->dtail;

                                                    $break      = explode("_next_",$dtail);

                                                    $amount_percent_price = $break[0];

                                                    $shopex_type    = $break[1];

                                                    $shopex_cat     = $break[2];

                                                    $shopex_cat = (int)$shopex_cat;


                                                    if( $shopex_term = get_term_by( 'id', $cat, 'product_cat' ) ){
                                                        $catname = $shopex_term->name;
                                                    }


                                                    if($shopex_type == 'amount') {

														$shopex_discount_div .= "<button style='color:red; background : white;'> 
																					".$amount_percent_price."$ off  for this and all the products of catagory 																						'".$catname."' 
																				</button>&nbsp;";

                                                    }else if($shopex_type == 'percent') { 

                                                        $shopex_discount_div .= "<button style='color:red; background : white;'> 
																					".$amount_percent_price."% off  for this and all the products of catagory 																						'".$catname."' 
																				</button>&nbsp;";

                                                    }else if($shopex_type  == 'fixedprice') {

                                                        $shopex_discount_div .= "<button style='color:red; background : white;'> 
																					".$amount_percent_price."$  fixed price for this and all of the products of 																					catagory '".$catname."'  
																				</button>&nbsp;";
													}
                                                }
                                            }
                                        }

                                    } else if($dpdis_type == 'entire') {

                                        $get_all_rows = $wpdb->get_results("SELECT * from shopex_dpdis WHERE dpdis_name = '".$dpdis_name."' AND 
																			dpdis_type = 'entire' AND status = 1");

                                        if(is_countable($get_all_rows) && count($get_all_rows) > 0) {

                                            $cname              = 'shopex_entire'.$shopex_pro_id;
                                            $_SESSION[$cname]   = $dpdis_name;

                                            foreach ( $get_all_rows as $shopex_row1 ) {

                                                $dt  = $shopex_row1->dtail;

                                                $dtt = explode("_next_",$dt);

                                                $shopex_offer = $dtt[0];
                                                $shopex_offertype = $dtt[1];
                                                
                                                if($shopex_offertype == 'amount') {

                                                    $shopex_discount_div .= "<button style='color:red; background : white;'> 
																				".$shopex_offer."$ off  for all the products  
																			</button>&nbsp;";

                                                }else if($shopex_offertype == 'percent') { 

                                                    $shopex_discount_div .= "<button style='color:red; background : white;'> 
																				".$shopex_offer."% off  for all the products 
																			</button>&nbsp;";
                                                }else if($shopex_offertype  == 'fixedprice') {

                                                    $shopex_discount_div .= "<button style='color:red; background : white;'> 
																				".$shopex_offer."$  fixed price for all of the products
																			</button>&nbsp;";  

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
    
    $shopex_discount_div .="</div>";

    ?>
    <script> var dpdiv = <?php echo json_encode($shopex_discount_div);?>;document.write(dpdiv);</script> 
    <?php
}

