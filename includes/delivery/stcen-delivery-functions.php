<?php

function stcen_add_deliveries($methods)
{
    require_once(__DIR__ . '/class-store-central-delivery.php');

    $methods[] = 'StoreCentralDelivery';

    return $methods;
}

function stcen_inactive_order_button_no_shipping($button)
{
    $package_counts = [];

    $packages = WC()->shipping->get_packages();
    foreach ($packages as $key => $pkg) {
        $package_counts[$key] = count($pkg['rates']);
    }

    if (in_array(0, $package_counts)) {
        $style = 'style="background:Silver !important; color:white !important; cursor: not-allowed !important;"';
        $button_text = apply_filters('woocommerce_order_button_text', __('Place order'));
        $button = '<a class="button" ' . $style . '>' . $button_text . '</a>';
    }
    return $button;
}

function stcen_action_after_shipping_rate($rate, $index)
{
    if (strpos($rate->id, 'stcen_delivery') !== false) {
        echo __('<br><div><small>' . esc_html($rate->meta_data['description']) . '</div></small>');
    }
}

function stcen_convert_to_grams($weight, $unit)
{
    switch ($unit) {
        case 'kg':
            return $weight * 1000.0;
        case 'lbs':
            return $weight * 453.6;
        case 'oz':
            return $weight * 16.0;
        default:
            return $weight;
    }
}

function stcen_override_default_locale_fields($fields)
{
    $fields['city']['priority'] = 80;
    $fields['state']['priority'] = 70;
    return $fields;
}

function stcen_no_shipping_message($message)
{
    $options = get_option('stcen_settings');
    return isset($options['stcen_no_delivery_message']) && $options['stcen_no_delivery_message'] != ''
        ? $options['stcen_no_delivery_message']
        : $message;
}
