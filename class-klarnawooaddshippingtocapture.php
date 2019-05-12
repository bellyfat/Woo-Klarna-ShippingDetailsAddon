<?php
/**
 * Main plugin file
 *
 * @file The main file
 * @package main package file
 */

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
require 'class-wooklarnashippingaddonsettingspage.php';
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
	/**
	 * Undocumented variable
	 *
	 * @var String
	 */
	private $euliveid;
	/**
	 * Undocumented variable
	 *
	 * @var String
	 */
	private $eulivepass;
	/**
	 * Undocumented variable
	 *
	 * @var WooKlarnaShippingAddonSettingsPage
	 */
	private $settingspage;
	/**
	 * Constructor for class
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'create_options_page' ) );
		add_action( 'woocommerce_order_status_completed', array( $this, 'add_shipping_to_capture' ), 999999 );
		$this->settingspage = new WooKlarnaShippingAddonSettingsPage();
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function create_options_page() {
		add_options_page( 'Woo Klarna Shipping Addon', 'Woo Klarna Shipping Addon', 'manage_options', 'woo-klarna-shipping-addon', array( $this->settingspage, 'render_klarna_settings_page' ) );
	}
	/**
	 * Undocumented function
	 *
	 * @return String
	 */
	public function get_mid() {
		if ( $this->settingspage->get_test_mode() ) {
			return $this->eutestid;
		}
		return $this->euliveid;
	}
	/**
	 * Undocumented function
	 *
	 * @return String
	 */
	public function get_pass() {
		if ( $this->settingspage->get_test_mode() ) {
			return $this->eutestpass;
		}
		return $this->eulivepass;
	}
	/**
	 * Undocumented function
	 *
	 * @return String The base url of the API depending on the testmode
	 */
	public function get_base_url() {
		return $this->settingspage->get_base_url();
	}
	/**
	 * Undocumented function
	 *
	 * @return String The meta key of the order where the tracking id is stored
	 */
	public function get_post_meta_for_tracking_id() {
		return get_option( 'woo-klarna-shipping-addon' )['trackingidmeta'];
	}
	/**
	 * Undocumented function
	 *
	 * @param String $order_id The woocommerce orderID.
	 * @return String The tracking ID of the order
	 */
	public function get_tracking_id( $order_id ) {
		if ( ! get_post_meta( $order_id, $this->get_post_meta_for_tracking_id(), true ) ) {
			return $order_id;
		}
		return get_post_meta( $order_id, $this->get_post_meta_for_tracking_id(), true );
	}
	/**
	 * Undocumented function
	 *
	 * @return String the link of tracking the parcel
	 */
	public function get_tracking_url() {
		return get_option( 'woo-klarna-shipping-addon' )['trackinglink'];
	}
	/**
	 * Undocumented function
	 *
	 * @param String $order_id The WooCommerce order id.
	 * @return String
	 */
	public function get_shipping_company( $order_id ) {
		return 'DHL Freight';
	}
	/**
	 * Undocumented function
	 *
	 * @param string $order_id The WooCommerce Order ID.
	 * @return String The shipping Method Used
	 */
	public function get_shipping_method( $order_id ) {
		return 'PickUpPoint';
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	private function get_klarna_values() {
		$gateway_settings = get_option( 'woocommerce_kco_settings' );
		$this->eutestid   = $gateway_settings['test_merchant_id_eu'];
		$this->eutestpass = $gateway_settings['test_shared_secret_eu'];
		$this->euliveid   = $gateway_settings['merchant_id_eu'];
		$this->eulivepass = $gateway_settings['shared_secret_eu'];
	}
	/**
	 * Undocumented function
	 *
	 * @param String $order_id The Woocommerce order id.
	 * @return void
	 */
	public function add_shipping_to_capture( $order_id ) {
		$this->get_klarna_values();
		$order          = wc_get_order( $order_id );
		$klanrnaorderid = $order->get_transaction_id();
		$captuereid     = get_post_meta( $order_id, '_wc_klarna_capture_id', true );
		$tracking_id    = $this->getTrackingId( $order_id );
		$client         = new \GuzzleHttp\Client();
		$tracking_url   = $this->getTrackingUrl() . $order_id;
		$res            = $client->post(
			$this->get_base_url() . '/ordermanagement/v1/orders/' . $klanrnaorderid . '/captures/' . $captuereid . '/shipping-info',
			[
				'verify'  => true,
				'auth'    => [ $this->getMid(), $this->getPass() ],
				'json'    => [
					'shipping_info' => [
						[
							'shipping_company' => $this->getShippingCompany( $order_id ),
							'shipping_method'  => $this->getShippingMethod( $order_id ),
							'tracking_number'  => $tracking_id,
							'tracking_uri'     => $tracking_url,
						],
					],
				],
				'headers' => [
					'User-Agent' => 'Mnording Addon Shipping WP-Plugin',
				],
			]
		);
		$order->add_order_note( 'Updated Klarna whith shipment data. Gave them tracking ID ' . $tracking_id . ' and tracking Url ' . $tracking_url );
		$order->save();
	}
}
$t = new KlarnaWooAddShippingToCapture();
