# Woo Klarna Shipping Addon

**What does this do?**

This plugin will add a single feature to your Woocommerce Klarna V3 integration.
It will take the tracking ID that you have stored on the order, and send that to Klarna.
More infrmation from Klarna on this page https://developers.klarna.com/resources/order-management/integration-guide/delivery-tracking

**What happens**

Klarna wil then take that information and display it in their consumer-friendl app.
Your customers will then be able to track their purchases from your store, inside of Klarnas App.

**What do I need?**

You need to be using the official Klarna Plugin for WooCommerce
https://docs.woocommerce.com/document/klarna-checkout/

You also need to have trackingIds logged to your orders.

This plugin will require a post meta key on the order where you have stored the tracking ID.