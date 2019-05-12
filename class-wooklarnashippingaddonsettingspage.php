<?php
/**
 * Settings page of the plugin
 *
 * @file File for settings page class
 * @package main package
 */

/**
 * Undocumented class
 */
class WooKlarnaShippingAddonSettingsPage {
	/**
	 * Construct of the class
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'plugin_admin_init' ) );
	}
	/**
	 * Undocumented function
	 *
	 * @return strng the api_username
	 */
	public function get_mid() {
		if ( $this->get_test_mode() ) {
			return $this->eutestid;
		}
		return $this->euliveid;
	}
	/**
	 * Undocumented function
	 *
	 * @return String The base url of the klarna api
	 */
	public function get_base_url() {
		if ( $this->get_test_mode() ) {
			return 'https://api.playground.klarna.com';
		}
		return 'https://api.klarna.com';
	}
	/**
	 * Undocumented function
	 *
	 * @return String api password.
	 */
	public function get_pass() {
		$settings = get_option( 'woocommerce_kco_settings' );
		$settings['testmode'];
		if ( $this->get_test_mode() ) {
			return $this->eutestpass;
		}
		return $this->eulivepass;
	}
	/**
	 * Undocumented function
	 *
	 * @return Boolean wether or not this is in testmode.
	 */
	public function get_test_mode() {
		$settings = get_option( 'woocommerce_kco_settings' );
		return 'yes' === $settings['testmode'];
	}
	/**
	 * Undocumented function
	 *
	 * @return Boolean If debubug logging should be active.
	 */
	public function should_log_debug() {
		$options = get_option( 'woo-klarna-shipping-addon' );
		return isset( $options['logdebug'] );
	}
	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function render_klarna_settings_page() {       ?>
<div>
	<h2>Klarna Shipping Addon</h2>
	<form action="options.php" method="post">
		<?php settings_fields( 'woo-klarna-shipping-addon' ); ?>
		<?php do_settings_sections( 'woo-klarna-shipping-addon' ); ?>

		<input name="Submit" type="submit" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
	</form>
</div>
<?
}
function plugin_admin_init()
{

	register_setting('woo-klarna-shipping-addon', 'woo-klarna-shipping-addon', 'plugin_options_validate');
	add_settings_section('plugin_main', 'Main Settings', array($this, 'plugin_section_text'), 'woo-klarna-shipping-addon');
	add_settings_field('link', 'Tracking link url', function () {
		$this->plugin_setting_text("trackinglink");
	}, 'woo-klarna-shipping-addon', 'plugin_main');
	add_settings_field('trackingidmeta', 'Meta key for trackingID', function () {
		$this->plugin_setting_text("trackingidmeta");
	}, 'woo-klarna-shipping-addon', 'plugin_main');
	add_settings_field('logdebug', 'Log debug', function () {
		$this->plugin_setting_checkbox("logdebug");
	}, 'woo-klarna-shipping-addon', 'plugin_main');
}

function plugin_setting_text($key)
{
	$option = get_option('woo-klarna-shipping-addon');
	echo '<input type="text" name="woo-klarna-shipping-addon[' . $key . ']" value="' . $option[$key] . '"/>';
}
function plugin_section_text()
{
	echo '<p>Main description of this section here.</p> <p>All the options you might need for instant shopping</p>
		<p><i>All credentials will be fetched from your main Klarna plugin</i></p>';
}
function plugin_setting_checkbox($key)
{
	$options = get_option('woo-klarna-shipping-addon');
	if (!isset($options[$key])) {
		$options[$key] = "";
	}
	?>
<input name='woo-klarna-shipping-addon[<?php echo esc_attr( $key ); ?>]' type='checkbox' value='1' <?php checked( '1', $options[ $key ], true ); ?> />
		<?php

	}
}
