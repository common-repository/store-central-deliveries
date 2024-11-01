<?php

if (!class_exists('StoreCentralDelivery')) {
    class StoreCentralDelivery extends WC_Shipping_Method
    {
        /**
         * The ID of the shipping method.
         *
         * @var string
         */
        public $id;

        /**
         * The title of the method.
         *
         * @var string
         */
        public $method_title;

        /**
         * The description of the method.
         *
         * @var string
         */
        public $method_description;

        /**
         * The supported features.
         *
         * @var array
         */
        public $supports = [
            'settings',
        ];

        /**
         * Initialize a new shipping method instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->id = 'stcen_delivery';
            $this->method_title = __('Cotizador de despachos Store Central');
            $this->method_description = __('Plugin para cotizar despachos desde los diferentes warehouses disponibles de Store Central.');
            $this->init_form_fields();
            $this->init_settings();
            $this->registerHooks();

            $this->enabled = isset($this->settings['enabled']) ? $this->settings['enabled'] : 'no';
            $this->title = isset($this->settings['title']) ? $this->settings['title'] : 'Custom Shipping';
        }

        /**
         * Initialize the form fields.
         *
         * @return void
         */
        public function init_form_fields()
        {
            $this->form_fields = [
                'enabled' => [
                    'title' => __('Enable'),
                    'type' => 'checkbox',
                    'description' => __('Enable this shipping method.'),
                    'default' => 'yes',
                ],
                'title' => [
                    'title' => $this->method_title,
                    'type' => 'text',
                    'description' => __('Title for this shipping method.'),
                    'default' => $this->method_title,
                ],
                'description' => [
                    'title' => $this->method_description,
                    'type' => 'textarea',
                ]
            ];
        }

        /**
         * Calculate the shipping fees.
         *
         * @param  array  $package
         * @return void
         */
        public function calculate_shipping($package = [])
        {
            try {
                $options = get_option('stcen_settings');

                $url = $options['stcen_endpoint'] . '/sellers_config/' . $options['stcen_store_id'] . '/platforms/woocommerce/rates';

                $weight_unit = get_option('woocommerce_weight_unit');
                $dimension_unit = get_option('woocommerce_dimension_unit');
                $items = [];

                foreach ($package['contents'] as $key => $item) {
                    $item_id = $item['variation_id'] == '0' ? $item['product_id'] : $item['variation_id'];
                    $product = $item['data'];
                    $dimensions = $product->get_dimensions(false);
                    $weight = (float) $product->get_weight();
                    $items[] = [
                        'platformId' => $item_id,
                        'quantity' => $item['quantity'],
                        'price' => wc_get_price_including_tax($product),
                        'measures' => [
                            'width' => [
                                'value' => (!empty($dimensions['width'])) ? $dimensions['width'] : 0,
                                'unit' => $dimension_unit,
                            ],
                            'length' => [
                                'value' => (!empty($dimensions['length'])) ? $dimensions['length'] : 0,
                                'unit' => $dimension_unit,
                            ],
                            'height' => [
                                'value' => (!empty($dimensions['height'])) ? $dimensions['height'] : 0,
                                'unit' => $dimension_unit,
                            ],
                            'weight' => [
                                'value' => (!empty($weight)) ? $weight : 0,
                                'unit' => $weight_unit,
                            ],
                        ],
                    ];
                }

                $wc_countries = WC()->countries->get_countries();
                $wc_states = WC()->countries->get_states();
                $country_code = $package['destination']['country'];
                $state_code = $package['destination']['state'];

                $country = $wc_countries[$country_code];
                $region = isset($wc_states[$country_code][$state_code]) ? $wc_states[$country_code][$state_code] : $state_code;

                $body =	[
                    'destination' => [
                        'country' => $country,
                        'region' => $region,
                        'municipality' => $package['destination']['city'],
                        'address1' => $package['destination']['address_1'],
                    ],
                    'items' => $items,
                ];

                $args = [
                    'body' => $body
                ];

                $despachos = wp_remote_post($url, $args);
                $body = array_values(json_decode(wp_remote_retrieve_body($despachos), true));

                if (count($body) == 0) {
                    return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', false, $package);
                }

                foreach ($body as $despacho) {
                    $this->add_rate(
                        [
                            'id' => 'stcen_delivery_' . $despacho['id'],
                            'label' => $despacho['method_title'],
                            'cost' => $despacho['rate'],
                            'meta_data' => array(
                                'description' => $despacho['method_description'],
                                'method_id' => 'stcen_delivery_' . $despacho['id'],
                            ),
                        ]
                    );
                }
            } catch(Exception $e) {
                return apply_filters('woocommerce_shipping_' . $this->id . '_is_available', false, $package);
            }
        }

        /**
         * Register the shipping method hooks.
         *
         * @return void
         */
        public function registerHooks()
        {
            add_action("woocommerce_update_options_shipping_{$this->id}", [$this, 'process_admin_options']);
        }
    }
}
