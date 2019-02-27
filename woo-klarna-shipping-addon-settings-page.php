<?php
class WooKlarnaShippingAddonSettingsPage
{
    function __construct()
    {
        add_action('admin_init', array($this, 'plugin_admin_init'));
    }
    function getmid()
    {
        if ($this->getTestmode()) {
            return $this->eutestid;
        }
        return $this->euliveid;
    }
    function getBaseUrl()
    {
        if ($this->getTestmode()) {
            return "https://api.playground.klarna.com";
        }
        return "https://api.klarna.com";
    }
    function getpass()
    {
        $settings = get_option('woocommerce_kco_settings');
        $settings['testmode'];
        if ($this->getTestmode()) {
            return $this->eutestpass;
        }
        return $this->eulivepass;
    }
    function getTestmode()
    {
        $testmode = get_option("woo-klarna-shipping-addon");
        return isset($testmode["testmode"]);
    }
    function shouldLogDebug()
    {
        $options =  get_option('woo-klarna-shipping-addon');
        return isset($options["logdebug"]);
    }
    function RenderKlarnaSettingsPage()
    {
        ?>
<div>
    <h2>Klarna Shipping Addon</h2>
    <form action="options.php" method="post">
        <?php settings_fields('woo-klarna-shipping-addon'); ?>
        <?php do_settings_sections('woo-klarna-shipping-addon'); ?>

        <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
    </form>
</div>
<?
}
function CreateNewButton()
{

    return $this->buttonGenerator->generateButtonKey();
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
<input name='woo-klarna-shipping-addon[<?php echo $key ?>]' type='checkbox' value='1' <?php checked("1", $options[$key], true) ?> />
<?php

}
function plugin_setting_selectpage($key)
{
    $options = get_option('woo-klarna-shipping-addon');
    if (!isset($options[$key])) {
        $options[$key] = -1;
    }
    $args = array(
        'sort_order' => 'asc',
        'sort_column' => 'post_title',
        'parent' => -1,
        'post_type' => 'page',
        'post_status' => 'publish'
    );
    $pages = get_pages($args);

    echo '<select name="woo-klarna-shipping-addon[' . $key . ']">';
    foreach ($pages as $page) {
        $selected = ($options[$key] == $page->ID) ? "selected" : "";
        echo '<option ' . $selected . ' value="' . $page->ID . '">' . $page->post_title . '</option>';
    }
    echo '</select> ';
}
}
?> 