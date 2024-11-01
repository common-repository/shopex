<?php
    // Get Newly created order data
    add_action('woocommerce_new_order',"Shopex_newly_created_order"); 
        
    function Shopex_newly_created_order($order_id) {
          
        global $wpdb;
        
        $detail = wc_get_order( $order_id );
                    
        $order_status = $detail->get_status();

        $od = $detail->get_date_created()->date('Y-m-d');

        $order_cid = $detail->get_customer_id();
        $order_cusname= $detail->get_billing_first_name()." ".$detail->get_billing_last_name();
        $mailid     =   $detail->get_billing_email();
        $cusphone   =   $detail->get_billing_phone();



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
        $tu     = 0;


        foreach ( $detail->get_items() as $item_id => $item ) {

            $pid = $item->get_product_id();

            // Error Creating :: $productname = $sss->get_name();
            // Error Creating :: if ( $sss->is_on_sale() )  {    
            // Error Creating ::    $onsale = 1;
            // Error Creating ::}

            $productname     = $item->get_name();
            $provid          = $item->get_variation_id();

            // Error Creating :: $product  = $item->get_product();
            // Error Creating :: $uprice   = $product->get_price();
            
            $pdt = wc_get_product( $pid );
            $uprice = $pdt->get_price();
            
            
            //$product  = $item->get_product();
            //$uprice   = $product->get_price();

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

            if(is_countable($rows) && count($rows) > 0 ) {
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

            if(is_countable($rows) && count($rows) > 0) {
                foreach ( $rows as $row ) {
                    $fpur = $row->fpur;
                }
            }



            $cog = 0;
            
            #   get First buy date of this product #   

            $profit = 0;
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
            $tu = $tu + $unit;

        }

        #   Get ret after #   



        //$fpur = $order_date;
        $ret_after = 0;
        $lpur="";
        $rows = "SELECT * from shopex_orders WHERE (cusmail='".$mailid."' OR (cusid != 0 and cusid='".$order_cid."'))  ORDER BY atdate DESC  LIMIT 1";
        
        $rowres = $wpdb->get_results($rows);
        
        if(is_countable($rowres) && count($rowres) >0) {
            
            //foreach($rows as $row){
            $lpur = $rowres->atdate;
            //}

            if($lpur!="" && $lpur!=NULL){
                $date1 = date_create($lpur);
                $date2 = date_create($od);
                $diff = date_diff($date1,$date2);
                $ret_after = $diff->format("%r%a");
            }
        }
        
        

        $newDateTime="";


        # IF A CUSTOMER WITH SPECIFIC EMAIL ADDRESS PLACED ONE OR MORE ORDER BEFORE REGISTRATION THEN UPDATE "CUSID" COLUMN VALUE IN "ORDERS" TABLE 
        if($order_cid != 0) {
            $table_name  ='shopex_orders';
            //$wpdb->query( $wpdb->prepare("UPDATE $table_name SET cusid = '".$order_cid."' WHERE cusid = 0 AND cusmail = '".$mailid."'"));
            $wpdb->prepare("UPDATE %s SET cusid = %d WHERE cusid = 0 AND cusmail = %s ",array($table_name,$order_cid,$mailid));

            $table_name  ='shopex_order_lineitem';
            //$wpdb->query( $wpdb->prepare("UPDATE $table_name SET cusid = '".$order_cid."' WHERE cusid = 0 AND cusmail = '".$mailid."'"));
            $wpdb->prepare("UPDATE %s SET cusid = %d WHERE cusid = 0 AND cusmail = %s ",array($table_name,$order_cid,$mailid));
        }
        # IF A CUSTOMER WITH SPECIFIC EMAIL ADDRESS PLACED ONE OR MORE ORDER BEFORE REGISTRATION THEN UPDATE "CUSID" COLUMN VALUE IN "ORDERS" TABLE 



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
