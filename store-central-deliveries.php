<?php

/**
 * Plugin Name: Store Central Deliveries
 * Plugin URI:
 * Description: Tarifas de envíos de Store Central.
 * Version: 1.1.0
 * Author: Grupo Central
 * Author URI: https://grupocentral.io/
 * Text Domain: store-central-deliveries
 * Domain Path: /i18n/languages/
 * Requires at least: 5.6
 * Requires PHP: 7.0.33
 *
 * @package StoreCentralDeliveries
 */

defined('ABSPATH') || exit;

$plugin_path = trailingslashit(WP_PLUGIN_DIR) . 'woocommerce/woocommerce.php';

if (
    in_array($plugin_path, wp_get_active_and_valid_plugins())
    || in_array($plugin_path, wp_get_active_network_plugins())
) {
    include_once(__DIR__ . '/includes/admin/stcen-admin-functions.php');
    include_once(__DIR__ . '/includes/delivery/stcen-delivery-functions.php');

    add_filter('woocommerce_default_address_fields', 'stcen_override_default_locale_fields');
    add_filter('woocommerce_shipping_methods', 'stcen_add_deliveries');
    add_filter('woocommerce_order_button_html', 'stcen_inactive_order_button_no_shipping');
    add_filter('woocommerce_no_shipping_available_html', 'stcen_no_shipping_message');
    add_filter('woocommerce_cart_no_shipping_available_html', 'stcen_no_shipping_message');
    add_action('woocommerce_after_shipping_rate', 'stcen_action_after_shipping_rate', 20, 2);
    add_action('admin_menu', 'stcen_add_admin_menu');
    add_action('admin_init', 'stcen_settings_init');
}
