<?php
    
    //  Get All order data after plugin activition
    //add_action('woocommerce_after_register_post_type', "Shopex_after_install_table_process");

    function Shopex_after_install_table_process() {
    
        global $wpdb;
        
        # First time order data Input
       
        $rows = $wpdb->get_results("SELECT orderid from shopex_orders LIMIT 1");
        
        if(count($rows) < 1) {
            
            $orders = wc_get_orders( array( 'numberposts' => -1, 'orderby' => 'date', 'order' => 'ASC'));
            
            foreach($orders as $detail) {

                $order_id = $detail->id;
                
                //$detail = wc_get_order( $order_id );
                
                $order_status = $detail->get_status();
                
                if($order_status != 'auto-draft') {
                
                $ff  = $detail->get_date_created();
                
                if($ff != NULL){
                    $od = $detail->get_date_created()->date('Y-m-d');
                }
                
                $order_cid     = $detail->get_customer_id();
                $order_cusname = $detail->get_billing_first_name()." ".$detail->get_billing_last_name();
                $mailid        = $detail->get_billing_email();
                $cusphone      = $detail->get_billing_phone();

                $ba1        =   $detail->get_billing_address_1();
                $ba2        =   $detail->get_billing_address_2();
                $bcity      =   $detail->get_billing_city();
                $bstate     =   $detail->get_billing_state();
                $bcountry   =   $detail->get_billing_country();
                $bpc        =   $detail->get_billing_postcode();
                
                $sa1        =   $detail->get_shipping_address_1();
                $sa2        =   $detail->get_shipping_address_2();
                $scity      =   $detail->get_shipping_city();
                $sstate     =   $detail->get_shipping_state();
                $scountry   =   $detail->get_shipping_country();
                $spc        =   $detail->get_shipping_postcode();

                $paymeth    =   $detail->get_payment_method_title();

                $discount       = $detail->get_total_discount();
                $discount_tax   = $detail->get_discount_tax();

                $shipping        = $detail->get_shipping_total();
                $shipping_tax    = $detail->get_shipping_tax();
                $shipping_method = $detail->get_shipping_method();

                $order_total=   $detail->get_total();
                $total_tax  =   $detail->get_total_tax();

                $cart_tax   =   $detail->get_cart_tax();

                $coupons    =   $detail->get_items('coupon');
                $num_coupon =   count($coupons);

                $created_via = $detail->get_created_via();
                $transid     = $detail->get_transaction_id();
                $odtime = "";
                
                // Get and Loop Over Order Items
                $onsale = 0;
                $pros   = 0;
                $tu  = 0;


                foreach ( $detail->get_items() as $item_id => $item ) {

                    $pid = $item->get_product_id();
                    
                    $productname     = $item->get_name();

                    $provid          = $item->get_variation_id();

                    // Error Creating :: $product  = $item->get_product();
                    $uprice   = $item->get_price();

                    //$pdt = wc_get_product( $pid );
                    //$uprice = $pdt->get_price();

                    $unit     = $item->get_quantity();
                    $subtotal = $item->get_subtotal();
                    $tprice   = $item->get_total();


                    $couponused = 0;
                    if($subtotal > $tprice) {
                        $couponused = 1;
                    }
                    
                    //$tax                = $item->get_subtotal_tax();
                    //$taxclass           = $item->get_tax_class();
                    //$taxstat            = $item->get_tax_status();
                    //$allmeta            = $item->get_meta_data();
                    //$somemeta           = $item->get_meta( '_whatever', true );
                    //$type               = $item->get_type();
                
                    
                    #   Check N'-TH BUY #   
                    $dmail = 0; $did = 0;
                    $rows = $wpdb->get_results("SELECT count(distinct case when cusid = 0 AND cusmail = '".$mailid."' then orderid end) as distinctmail, count(distinct case when cusid != 0 AND cusid = '".$order_cid."' then orderid end) as  distinctcusid from shopex_order_lineitem WHERE proid = '".$pid."' AND orderid != '".$order_id."'");

                    if($rows) {
                        foreach ( $rows as $row ) {
                            $dmail = $row->distinctmail;
                            $did   = $row->distinctcusid;
                        }
                    }

                    $nth = $dmail + $did;
                    #   Check N'-TH BUY #   

                
                

                    #   Get First buy date of this product #   
                    $fpur = $od;
                    $rows = $wpdb->get_results("SELECT min(atdate) as fpur from shopex_order_lineitem WHERE ((cusid = 0 AND cusmail = '".$mailid."') or (cusid != 0 and cusid = '".$order_cid."')) AND proid = '".$pid."' AND orderid != '".$order_id."'");

                    if($rows) {
                        foreach ( $rows as $row ) {
                            $fpur = $row->fpur;
                        }
                    }




                    #   get First buy date of this product #   

                    $profit = 0;$cog = 0;
                    $data = array('proname' => $productname,
                            'proid'=> $pid,
                            'onsale' => $onsale, 
                            'couponused' => $couponused, 
                            'unit' => $unit, 
                            'cog' => $cog,
                            'uprice' => $uprice,
                            'tprice' => $tprice,
                            'profit' => $profit,
                            'orderid' => $order_id,
                            'order_status' => $order_status,
                            'atdate' => $od,
                            'cusname' => $order_cusname,
                            'cusmail' => $mailid,
                            'cusid' => $order_cid,
                            'nth_buy' => $nth,
                            'provid' => $provid,
                            'fpur' => $fpur
                    );

                    $table_name ="shopex_order_lineitem";
                    $wpdb->insert($table_name,$data);


                    $pros = $pros + 1;
                    $tu   = $tu + $unit;

                }
                
                #   Get ret after #   
                
                
                
                //$fpur = $order_date;
                $ret_after = 0;
                $lpur="";
                $rows = $wpdb->get_row("SELECT * from shopex_orders WHERE (cusmail='".$mailid."' OR (cusid != 0 and cusid='".$order_cid."')) ORDER BY atdate DESC LIMIT 1");
                if( is_countable($rows) && count($rows) > 0) {
                    
                    //foreach($rows as $row){
                        $lpur = $rows->atdate;
                    //}
                    
                    if($lpur!="" && $lpur!=NULL){
                        $date1 = date_create($lpur);
                        $date2 = date_create($od);
                        $diff = date_diff($date1,$date2);
                        $ret_after = $diff->format("%r%a");
                    }
                    
                }
                
                
                
                $newDateTime="";

                
                # IF A CUSTOMER WITH SPECIFIC EMAIL ADDRESS PLACED ORDER BEFORE REGISTRATION 
                if($order_cid != 0) {
                    $table_name  ='shopex_orders';
                    //$wpdb->query( $wpdb->prepare("UPDATE $table_name SET cusid = '".$order_cid."' WHERE cusid = 0 AND cusmail = '".$mailid."'"));
                    $wpdb->prepare("UPDATE %s SET cusid = %d WHERE cusid = 0 AND cusmail = %s ",array($table_name,$order_cid,$mailid));

                    $table_name  ='shopex_order_lineitem';
                    //$wpdb->query( $wpdb->prepare("UPDATE $table_name SET cusid = '".$order_cid."' WHERE cusid = 0 AND cusmail = '".$mailid."'"));
                    $wpdb->prepare("UPDATE %s SET cusid = %d WHERE cusid = 0 AND cusmail = %s ",array($table_name,$order_cid,$mailid));
                }
                



                #   Check if its First Order From this customer #   WP Method
                $foro = 1;
                $rows = $wpdb->get_results("SELECT orderid as oo from shopex_orders WHERE ((cusid = 0 AND cusmail = '".$mailid."') OR (cusid != 0 and cusid = '".$order_cid."')) AND orderid != '".$order_id."'");
                if(is_countable($rows) && count($rows) > 0){
                    $foro = count($rows) + 1;
                }
                #   Check if its First Order From this customer #   WP Method



                # Insert Into shopex_orders WP Method

                $data = array('orderid' => $order_id,
                            'created_via' => $created_via,
                            'atdate'=> $od,
                            'attime' => $odtime, 

                            'cusname' => $order_cusname, 
                            'cusmail' => $mailid, 
                            'cusid' => $order_cid,

                            'fo_ro' => $foro,
                            'ret_after' => $ret_after,

                            'discount' => $discount,
                            'discount_tax' => $discount_tax,

                            'shipping_meth' => $shipping_method,
                            'shipping' => $shipping,
                            'shipping_tax' => $shipping_tax,

                            'cart_tax' => $cart_tax,
                            'total_tax' => $total_tax,

                            'num_coupon' => $num_coupon,

                            'order_status' => $order_status,
                            'order_total' => $order_total,

                            'total_product' => $pros,
                            'total_unit' => $tu,

                            'bstate' => $bstate,
                            'bcity' => $bcity,
                            'bcountry' => $bcountry,
                            'ba1' => $ba1,
                            'ba2' => $ba2,
                            'bpc' => $bpc,  


                            'sstate' => $sstate,
                            'scity' => $scity,
                            'scountry' => $scountry,
                            'sa1' => $sa1,
                            'sa2' => $sa2,
                            'spc' => $spc,  
                              
                              
                            'paymeth' => $paymeth,
                            'trans_id' => $transid,
                            'helo' => $cusphone
                        );

                $table_name ="shopex_orders";
                $wpdb->insert($table_name,$data);
            }
            }        
        }
        
        
       
        // Shopex Update of Each customer's Offer Table 
        
        $table_name ='shopex_customer_and_offer';
        
        $query_sco ="SELECT * from $table_name";
        $lcheck_sco = $wpdb->get_results($query_sco);
        if( is_countable($lcheck_sco) && count($lcheck_sco) > 0) {
            $wpdb->prepare(" UPDATE %s SET offer = '' ",array($table_name));
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
                            $get_cusdis = $wpdb->get_row("SELECT * FROM $table_name WHERE cusid = '".$cusid."'");

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

    register_activation_hook( __FILE__, 'Shopex_after_install_table_process' );

    include 'shopex_show_pricing_role_on_product_page.php';    
  
    include 'shopex_apply_dynamic_pricing_in_cart.php';
  
    /*
    add_action( 'woocommerce_cart_calculate_fees', 'shopex_discount_based_on_total', 25, 1 );

    function shopex_discount_based_on_total( $cart ) {

        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

        $total = $cart->cart_contents_total;
        
        // Percentage discount (10%)
        //  if( $total < 200 )
        //    $discount = $total * 0.1;
        //  Fixed amount discount ($20)
        //  else
        //    $discount = 20;

        // Add the discount
        
        if(isset($_SESSION['shopex_gift_added_id'])) {
            
            $shopex_gifts = sanitize_text_field($_SESSION['shopex_gift_added_id']);
            
            $shopex_pu = explode("NEXT_",$shopex_gifts);
            
            foreach($shopex_pu as $i) {
                
                $shopex_j = (int)$i;
                
                if($shopex_j) {
                    
                    $shopex_product = wc_get_product( $shopex_j );
                    $shopex_j_price = $shopex_product->get_price();
                    $shopex_j_name  = $shopex_product->get_name();
                    $cart->add_fee( __($shopex_j_name, 'woocommerce'), -$shopex_j_price );
                }
            }
        }
    }
    */