<?php
function stcen_add_admin_menu()
{
    add_menu_page('Store Central Deliveries', 'Store Central Deliveries', 'manage_options', 'store_central_deliveries', 'stcen_options_page');
}


function stcen_settings_init()
{
    register_setting('pluginPage', 'stcen_settings', 'stcen_validate_input');

    add_settings_section(
        'stcen_pluginPage_section',
        __('Plugin Settings', 'store-central-deliveries'),
        '',
        'pluginPage'
    );

    add_settings_field(
        'stcen_endpoint',
        __('Ruta para despachos', 'store-central-deliveries'),
        'stcen_endpoint_render',
        'pluginPage',
        'stcen_pluginPage_section'
    );

    add_settings_field(
        'stcen_store_id',
        __('ID tienda', 'store-central-deliveries'),
        'stcen_store_id_render',
        'pluginPage',
        'stcen_pluginPage_section'
    );

    add_settings_field(
        'stcen_no_delivery_message',
        __('Mensaje despacho no disponible', 'store-central-deliveries'),
        'stcen_no_delivery_message_render',
        'pluginPage',
        'stcen_pluginPage_section'
    );
}


function stcen_store_id_render()
{
    $options = get_option('stcen_settings');
    ?>
	<input type='text' name='stcen_settings[stcen_store_id]' style="width: 400px;" value='<?php echo esc_attr($options['stcen_store_id']); ?>'>
<?php

}

function stcen_endpoint_render()
{
    $options = get_option('stcen_settings');
    ?>
	<input type='text' name='stcen_settings[stcen_endpoint]' style="width: 400px;" value='<?php echo esc_attr($options['stcen_endpoint']); ?>'>
<?php

}

function stcen_no_delivery_message_render()
{
    $options = get_option('stcen_settings');
    $settings = array('media_buttons' => false, 'quicktags' => false, 'textarea_rows' => 5);
    echo "<div style='width:650px;'>";
    wp_editor($options['stcen_no_delivery_message'], 'no_delivery_message', $settings);
    echo "</div>";
}

function stcen_options_page()
{
    ?>
	<form action='options.php' method='post'>

		<h2>Store Central Deliveries</h2>

		<?php
            settings_fields('pluginPage');
    do_settings_sections('pluginPage');
    submit_button();
    ?>

	</form>
<?php

}

function stcen_validate_input($input)
{
    $input['stcen_no_delivery_message'] = wp_kses($_POST['no_delivery_message'], $allowedposttags);
    return $input;
}
