<?php
/**
Plugin Name: Woo Klarna Shipping Info Addon
Plugin URI: https://github.com/mnording/
Description: Sends tracking info to Klarna after an order has been captured
Version: 0.1.0
Author: mnording10
Author URI: https://mnording.com
Text Domain: woo-klarna-shipping-details
Domain Path: /languages
 */
require 'vendor/autoload.php';
require 'woo-klarna-shipping-addon-settings-page.php';
/**
 * KlarnaWooAddShippingToCapture
 */
class KlarnaWooAddShippingToCapture {
	/**
	 * Undocumented variable
	 *
	 * @var String The store merchant ID of the merchant in the klarna Playground enviournment
	 */
	private $eutestid;
	/**
	 * Undocumented variable
	 *
	 * @var String The stored api password of the merchant in the Klarna Playground Enviournment
	 */
	private $eutestpass;
	private $euliveid;
	private $eulivepass;
	private $settingspage;
	/**
	 * Constructor for class
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'create_options_page' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'addShippingToCapture' ), 999999 );
		$this->settingspage = new WooKlarnaShippingAddonSettingsPage();
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function create_options_page() {
		add_options_page( 'Woo Klarna Shipping Addon', 'Woo Klarna Shipping Addon', 'manage_options', 'woo-klarna-shipping-addon', array( $this->settingspage, 'RenderKlarnaSettingsPage' ) );
	}
	function getMid() {
		if ( $this->settingspage->getTestmode() ) {
			return $this->eutestid;
		}
		return $this->euliveid;
	}
	function getPass() {
		if ( $this->settingspage->getTestmode() ) {
			return $this->eutestpass;
		}
		return $this->eulivepass;
	}
	function getBaseUrl() {
		if ( $this->settingspage->getTestmode() ) {
			return 'https://api.playground.klarna.com';
		}
		return 'https://api.klarna.com';
	}

	function getPostMetaKeyForTrackingId() {
		return get_option( 'woo-klarna-shipping-addon' )['trackingidmeta'];
	}
	function getTrackingId( $order_id ) {
		if ( ! get_post_meta( $order_id, $this->getPostMetaKeyForTrackingId(), true ) ) {
			return $order_id;
		}
		return get_post_meta( $order_id, $this->getPostMetaKeyForTrackingId(), true );
	}
	function getTrackingUrl() {
		 return get_option( 'woo-klarna-shipping-addon' )['trackinglink'];
	}
	function getShippingCompany( $order_id ) {
		return 'DHL Freight';
	}
	function getShippingMethod( $order_id ) {
		return 'PickUpPoint';
	}
	function getKlarnaValues() {
		$gateway_settings = get_option( 'woocommerce_kco_settings' );
		$this->eutestid   = $gateway_settings['test_merchant_id_eu'];
		$this->eutestpass = $gateway_settings['test_shared_secret_eu'];
		$this->euliveid   = $gateway_settings['merchant_id_eu'];
		$this->eulivepass = $gateway_settings['shared_secret_eu'];
	}
	function addShippingToCapture( $order_id ) {
		$this->getKlarnaValues();
		$order          = wc_get_order( $order_id );
		$klanrnaorderid = $order->get_transaction_id();
		$captuereid     = get_post_meta( $order_id, '_wc_klarna_capture_id', true );
		$trackingId     = $this->getTrackingId( $order_id );
		$client         = new \GuzzleHttp\Client();
		$trackingUrl    = $this->getTrackingUrl() . $order_id;
		$res            = $client->post(
			$this->getBaseUrl() . '/ordermanagement/v1/orders/' . $klanrnaorderid . '/captures/' . $captuereid . '/shipping-info',
			[
				'verify'  => true,
				'auth'    => [ $this->getMid(), $this->getPass() ],
				'json'    => [
					'shipping_info' => [
						[
							'shipping_company' => $this->getShippingCompany( $order_id ),
							'shipping_method'  => $this->getShippingMethod( $order_id ),
							'tracking_number'  => $trackingId,
							'tracking_uri'     => $trackingUrl,
						],
					],
				],
				'headers' => [
					'User-Agent' => 'Mnording Addon Shipping WP-Plugin',
				],
			]
		);
		$order->add_order_note( 'Updated Klarna whith shipment data. Gave them tracking ID ' . $trackingId . ' and tracking Url ' . $trackingUrl );
		$order->save();
	}
}
$t = new KlarnaWooAddShippingToCapture();

