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
    function __construct()
    {
        add_action('woocommerce_order_status_completed', array($this, 'addShippingToCapture'), 999999);
    }
   function addShippingToCapture($order_id){
    $order = wc_get_order($order_id);
    $order->add_order_note( "trying to add shipment To klarna order" );
    $order->save();
    $klanrnaorderid= $order->get_transaction_id();
        $captuereid = get_post_meta( $order_id, '_wc_klarna_capture_id', true );
        $trackingId = get_post_meta($order_id,'trackingIdForORder',true);
        var_dump($captuereid);
        var_dump($trackingId);
        $client = new \GuzzleHttp\Client();
        $res = $client->post( "https://api.playground.klarna.com/ordermanagement/v1/orders/".$klanrnaorderid."/captures/".$captuereid."/shipping-info", ['verify' => true, 'auth' => ["PK04149_9ef50d19b0e3", "S3Pl4Di5ovDw0711"], 'json' => [
                "shipping_info"=> [
                    [
                        "shipping_company"=> "DHL",
                        "shipping_method"=> "PickupPoint",
                        "tracking_number"=> "63456415674545679874",
                        "tracking_uri"=> "http://shipping.example/findmypackage?63456415674545679874"
                    ]
                ]
        ], 'headers' => [
            'User-Agent' => 'Mnording Addon Shipping WP-Plugin',
        ]]);
   }    
}
$t = new KlarnaWooAddShippingToCapture();
 