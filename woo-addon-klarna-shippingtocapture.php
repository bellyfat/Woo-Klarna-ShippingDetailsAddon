<?php
/*
Plugin Name: Woo Klarna Addin Shipping Info
Plugin URI: https://github.com/mnording/Woo-Klarna-Instant-Shopping
Description: Adds Klarna Instant shopping button to your product pages
Version: 0.1.0
Author: mnording10
Author URI: https://mnording.com
Text Domain: woo-klarna-shipping-details
Domain Path: /languages
*/
require 'vendor/autoload.php';
class KlarnaWooAddShippingToCapture{
    private $eutestid;
        private $eutestpass;
       private $euliveid;
        private $eulivepass;
    function __construct()
    {
        $gateway_settings = get_option('woocommerce_kco_settings');
        $this->eutestid = $gateway_settings["test_merchant_id_eu"];
        $this->eutestpass = $gateway_settings["test_shared_secret_eu"];
        $this->euliveid = $gateway_settings["merchant_id_eu"];
        $this->eulivepass = $gateway_settings["shared_secret_eu"];
        add_action('woocommerce_order_status_completed', array($this, 'addShippingToCapture'), 999999);
        
    }
    function getMid(){
        if ($this->getTestmode()) {
            return $this->eutestid;
        }
       return $this->euliveid;
    }
    function getPass(){
        if ($this->getTestmode()) {
            return $this->eutestpass;
        }
       return $this->eulivepass;
    }
    function getBaseUrl()
    {
        if ($this->getTestmode()) {
            return "https://api.playground.klarna.com";
        }
        return "https://api.klarna.com";
    }
    function getTestmode()
    {
        $testmode = get_option("woo-klarna-instant-shopping");
        return true;
    }
    function getPostMetaKeyForTrackingId()
    {
        return '_msunifaun_web-ta_order_consignment_id';
    }
    function getTrackingUrl(){
        return "https://droppbollen.se/spara-paket/?orderid=";
    }
   function addShippingToCapture($order_id){
        
    $order = wc_get_order($order_id);
    $klanrnaorderid= $order->get_transaction_id();
        $captuereid = get_post_meta( $order_id, '_wc_klarna_capture_id', true );
        $trackingId = get_post_meta($order_id,'_msunifaun_web-ta_order_consignment_id',true);
        $client = new \GuzzleHttp\Client();
        $res = $client->post( $this->getBaseUrl()."/ordermanagement/v1/orders/".$klanrnaorderid."/captures/".$captuereid."/shipping-info", ['verify' => true, 'auth' => [$this->getMid(), $this->getPass()], 'json' => [
                "shipping_info"=> [
                    [
                        "shipping_company"=> "DHL Freight",
                        "shipping_method"=> "PickUpPoint",
                        "tracking_number"=> $trackingId,
                        "tracking_uri"=> $this->getTrackingUrl().$order_id
                    ]
                ]
        ], 'headers' => [
            'User-Agent' => 'Mnording Addon Shipping WP-Plugin',
        ]]);
        $order->add_order_note( "Updated Klarna whith shipment data. Gave them tracking ID ".$trackingId );
        $order->save();
   }    
}
$t = new KlarnaWooAddShippingToCapture();
 