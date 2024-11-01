<?php 

    function shopex_create_shopex_custom_table() {
	 
        global $wpdb;

        $table_name = "shopex_campaign_src_addsurl";
            
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                
            $charset_collate = $wpdb->get_charset_collate();
                
                $sql = "CREATE TABLE $table_name (
                        id	 		  mediumint(9) NOT NULL AUTO_INCREMENT,
                        adds_url      VARCHAR(200),
                        src           VARCHAR(200),
                        PRIMARY KEY (id)
                    ) $charset_collate;";
                
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                
                dbDelta( $sql );
        }
     
     
        $table_name = "shopex_orders";
         
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
             
            $charset_collate = $wpdb->get_charset_collate();
             
            $sql = "CREATE TABLE $table_name (
                    id	 		mediumint(9) NOT NULL AUTO_INCREMENT,
                    orderid             INT(6),
                    created_via			VARCHAR(150),

                    atdate              DATE,
                    attime              VARCHAR(150),

                    cusname             VARCHAR(50),
                    cusmail             VARCHAR(50),
                    
                    cusid               INT(6),

                    fo_ro               INT(6),
                    ret_after			INT(6),

                    discount            INT(6) NOT NULL,
                    discount_tax        INT(6) NOT NULL,
                    shipping_meth       VARCHAR(50),
                    shipping            INT(6) NOT NULL,
                    shipping_tax        INT(6) NOT NULL,
                    cart_tax            INT(6) NOT NULL,
                    total_tax           INT(6) NOT NULL,
                    num_coupon          INT(6) NOT NULL,

                    order_status        VARCHAR(50) NOT NULL,
                    order_total         INT(6) NOT NULL,
                    total_product       INT(6) NOT NULL,
                    total_unit          INT(6) NOT NULL,


                    bstate              VARCHAR(150),
                    bcity               VARCHAR(150),
                    bcountry            VARCHAR(150),
                    ba1                 VARCHAR(150),
                    ba2                 VARCHAR(150),
                    bpc					VARCHAR(50),

                    sstate              VARCHAR(150),
                    scity               VARCHAR(150),
                    scountry            VARCHAR(150),
                    sa1                 VARCHAR(150),
                    sa2                 VARCHAR(150),
                    spc					VARCHAR(50),

                    paymeth             VARCHAR(150),
                    trans_id			VARCHAR(150),
                    
                    helo                VARCHAR(15),
                    
                    ownerid			 TINYINT(1) DEFAULT 1,
                    shopid			 TINYINT(1) DEFAULT 1,
                    
                    PRIMARY KEY (id)
                ) $charset_collate;";
            
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            
            dbDelta( $sql );
        } 
     
        $table_name = "shopex_order_lineitem";
         
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
             
            $charset_collate = $wpdb->get_charset_collate();
             
            $sql = "CREATE TABLE $table_name (
                id	 			mediumint(9) NOT NULL AUTO_INCREMENT,
                
                proname     	VARCHAR(200),
                proid           INT(6),
                onsale          tinyint(1),
                couponused      TINYINT(1),

                unit             INT(6),
                cog              DOUBLE,
                
                uprice           DOUBLE,
                tprice           DOUBLE,

                profit           DOUBLE,
                orderid          INT(6),
                order_status     VARCHAR(50),
                atdate           DATE,
                cusname          VARCHAR(50),
                cusmail          VARCHAR(50),
                cusid            INT(6),
                nth_buy          INT(6),

                provid           INT(6),
                fpur             DATE,
                ownerid			 TINYINT(1) DEFAULT 1,
                shopid			 TINYINT(1) DEFAULT 1,
                PRIMARY KEY (id)
            ) $charset_collate;";
        
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        
            dbDelta( $sql );
        }
     
     
     
        $table_name = "shopex_dpdis_name_and_target_q";
         
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
    
    
    
        $table_name = "shopex_dpdis";
            
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                        id	 		mediumint(9) NOT NULL AUTO_INCREMENT,
                        dpdis_name		varchar(200) NOT NULL,
                        dpdis_type 		varchar(20) NOT NULL,
                        pro_cat_all_id 			int(6) NOT NULL,
                        pro_cat_all	varchar(20) NOT NULL,
                        dtail 		TEXT NOT NULL,
                        status		TINYINT(1) NOT NULL,
                        for_all	    TINYINT(1) NOT NULL,
                        f			varchar(20) NOT NULL,
                        t			varchar(20) NOT NULL,
                        osrun		TINYINT(1),
                        pr      	int(3) NOT NULL,
                        PRIMARY KEY (id)
                    ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            dbDelta( $sql );

        }
     
     
        $table_name = "shopex_customer_and_offer";
            
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                        id	 		mediumint(9) NOT NULL AUTO_INCREMENT,
                        cusid		int(6) NOT NULL,
                        cusmail 	varchar(30) NOT NULL,
                        offer 		TEXT NOT NULL,
                        PRIMARY KEY (id)
                    ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            dbDelta( $sql );

        }
        
     
        $table_name = "shopex_last_query";
            
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                        id	 			mediumint(9) NOT NULL AUTO_INCREMENT,
                        last_check		int(11) UNSIGNED,
                        PRIMARY KEY (id)
                    ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            dbDelta( $sql );
            
        }
    
        $deff=11;
        $data = array('last_check' => $deff);
        $wpdb->insert($table_name,$data);
        
        
        $table_name = "shopex_oseg";
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql ="CREATE TABLE $table_name (
                    id	 		mediumint(9) NOT NULL AUTO_INCREMENT,
                    segname		varchar(200) NOT NULL,
                    filters 		TEXT NOT NULL,
                    fullq 			TEXT NOT NULL,
                    seg_type	varchar(100) NOT NULL,
                    atdate 		DATE,
                    tflag		INT(6),
                    preq	    TEXT,
                    vars			TEXT,
                    dp_fullq			TEXT,
                    PRIMARY KEY (id)
                ) $charset_collate;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
    
     
     
        $table_name = "shopex_order_edited";
        if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql ="CREATE TABLE $table_name (
                    id	    mediumint(9) NOT NULL AUTO_INCREMENT,
                    edited  INT(6),
                    PRIMARY KEY (id)
                ) $charset_collate;";
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
    }

    register_activation_hook( __FILE__, 'shopex_create_shopex_custom_table' );
