<?php

/**
 * Plugin Name: Shein JNE
 */

if ( ! defined( 'WPINC' ) ) {

    die;

}

/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    function shein_jne_method() {
        if ( ! class_exists( 'Shein_JNE_Method' ) ) {
            class Shein_JNE_Method extends WC_Shipping_Method {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct() {
                    $this->id                 = 'shein';
                    $this->method_title       = 'Shein JNE';
                    $this->method_description = 'JNE Shipping Method for Shein';

                    $this->init();
                }

                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init() {
                    // Load the settings API
                    $this->init_form_fields();
                    $this->init_settings();

                    // Save settings in admin if you have any defined
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }

                /**
                 * Define settings field for this shipping
                 * @return void
                 */
                function init_form_fields() {
                    $origins = json_decode( file_get_contents( __DIR__ . '/data/shein-jne-origin.json' ), true );
                    $branches = json_decode( file_get_contents( __DIR__ . '/data/shein-jne-branch.json' ), true );

                    $this->form_fields = array(
                        'enabled' => array(
                            'title' => 'Enable',
                            'type' => 'checkbox',
                            'description' => 'Enable this shipping.',
                            'default' => 'yes'
                        ),
                        'origin' => array(
                            'title' => 'Shipping Origin',
                            'type' => 'select',
                            'description' => 'Shipping Origin.',
                            'options' => $origins
                        ),
                        'branch' => array(
                            'title' => 'Branch',
                            'type' => 'select',
                            'description' => 'Branch.',
                            'options' => $branches
                        ),
                        'endpoint' => array(
                            'title' => 'Endpoint',
                            'type' => 'text',
                            'description' => 'API Endpoint.'
                        ),
                        'username' => array(
                            'title' => 'Username',
                            'type' => 'text',
                            'description' => 'API Username.'
                        ),
                        'apikey' => array(
                            'title' => 'API Key',
                            'type' => 'text',
                            'description' => 'API Key.'
                        ),
                        'cust' => array(
                            'title' => 'Cust No',
                            'type' => 'text',
                            'description' => 'Cust No.'
                        ),
                        'shipper_name' => array(
                            'title' => 'Shipper Name',
                            'type' => 'text',
                            'description' => 'Shipper Name.'
                        ),
                        'shipper_addr_1' => array(
                            'title' => 'Shipper Address 1',
                            'type' => 'text',
                            'description' => 'Shipper Address 1.'
                        ),
                        'shipper_addr_2' => array(
                            'title' => 'Shipper Address 2',
                            'type' => 'text',
                            'description' => 'Shipper Address 2.'
                        ),
                        'shipper_addr_3' => array(
                            'title' => 'Shipper Address 3',
                            'type' => 'text',
                            'description' => 'Shipper Address 3.'
                        ),
                        'shipper_city' => array(
                            'title' => 'Shipper City',
                            'type' => 'text',
                            'description' => 'Shipper City.'
                        ),
                        'shipper_region' => array(
                            'title' => 'Shipper Region',
                            'type' => 'text',
                            'description' => 'Shipper Region.'
                        ),
                        'shipper_zip' => array(
                            'title' => 'Shipper ZIP',
                            'type' => 'text',
                            'description' => 'Shipper ZIP.'
                        ),
                        'shipper_phone' => array(
                            'title' => 'Shipper Phone',
                            'type' => 'text',
                            'description' => 'Shipper Phone.'
                        )
                    );
                }

                /**
                 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package = array() ) {

                    $wc_settings = get_option('woocommerce_shein_settings');

                    $origin = $wc_settings['origin'];
                    $destination = explode( '-', $package["destination"]["address_2"] )[1];

                    $weight = 0;

                    foreach ( $package['contents'] as $values )
                    {
                        $product = $values['data'];
                        $weight = $weight + $product->get_weight() * $values['quantity'];
                    }

                    $weight = ceil( wc_get_weight( $weight, 'kg' ) );

                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => $wc_settings['endpoint'] . '/tracing/api/pricedev',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => 'username=' . $wc_settings['username'] . '&api_key=' . $wc_settings['apikey'] . '&from=' . $origin . '&thru=' . $destination . '&weight=' . $weight,
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/x-www-form-urlencoded'
                        ),
                    ));

                    $response = curl_exec($curl);

                    curl_close($curl);

                    $result = json_decode( $response, true );

                    foreach ($result['price'] as $price) {
                        if ( $price['price'] > 0 ) {
                            $rate = array(
                                'id' => $price['service_code'],
                                'label' => $price['service_display'],
                                'cost' => $price['price'],
                            );

                            $this->add_rate( $rate );
                        }
                    }

                }
            }
        }
    }

    add_action( 'woocommerce_shipping_init', 'shein_jne_method' );

    function add_shein_jne_method( $methods ) {
        $methods[] = 'Shein_JNE_Method';
        return $methods;
    }

    add_filter( 'woocommerce_shipping_methods', 'add_shein_jne_method' );

    add_filter( 'woocommerce_countries',  'custom_woocommerce_countries' );

    function custom_woocommerce_countries( $countries ) {
        $countries = json_decode( file_get_contents( __DIR__ . '/data/shein-jne-country.json' ), true );

        return $countries;
    }

    /**
     * Add or modify States
     */
    add_filter( 'woocommerce_states', 'custom_woocommerce_states' );

    function custom_woocommerce_states( $states ) {

        $states = json_decode( file_get_contents( __DIR__ . '/data/shein-jne-state.json' ), true );

        return $states;

    }

    /**
     * Add or modify Cities
     */
    add_filter( 'woocommerce_default_address_fields' , 'custom_woocommerce_labels' );

    function custom_woocommerce_labels( $fields )
    {
        $fields['country']['label'] = 'Negara';
        $fields['state']['label'] = 'Provinsi';

        $fields['country']['priority'] = 30;
        $fields['state']['priority'] = 31;
        $fields['city']['priority'] = 32;
        $fields['address_2']['priority'] = 33;

        return $fields;
    }

    /**
     * Add or modify Cities
     */
    add_filter( 'woocommerce_default_address_fields' , 'custom_woocommerce_cities' );

    function custom_woocommerce_cities( $fields )
    {

        $cities = json_decode( file_get_contents( __DIR__ . '/data/shein-jne-city.json' ), true );

        $fields['city']['type'] = 'select';
        $fields['city']['label'] = 'Kabupaten';
        $fields['city']['options'] = $cities;

        return $fields;
    }

    /**
     * Add or modify Subdistrict
     */
    add_filter( 'woocommerce_default_address_fields' , 'custom_woocommerce_subdistrict' );

    function custom_woocommerce_subdistrict( $fields )
    {
        $subdistrict = json_decode( file_get_contents( __DIR__ . '/data/shein-jne-address.json' ), true );

        $fields['address_2']['type'] = 'select';
        $fields['address_2']['label'] = 'Kecamatan';
        $fields['address_2']['label_class'] = array();
        $fields['address_2']['required'] = true;
        $fields['address_2']['options'] = $subdistrict;

        return $fields;
    }

    // add the action
    add_action( 'woocommerce_order_status_changed', 'create_awb', 10, 4 );

    function create_awb( $id, $status_from, $status_transition_to, $instance )
    {
        if ( $status_transition_to == 'processing' ) {
            $wc_settings = get_option('woocommerce_shein_settings');

            $curl = curl_init();

            $data = $instance->get_data();

            $items['service'] = '';
            $items['weight'] = 1;
            $items['qty'] = 0;
            $items['total'] = 0;

            foreach ($instance->get_items() as $d) {
                if (isset($d->get_data()['total'])) {
                    $items['total'] = $d->get_data()['total'];
                }
            }

            foreach( $instance->get_items( 'shipping' ) as $shipping ) {
                $items['service'] = $shipping->get_method_title();

                $items['qty']++;
            }

            $posts = array(
                'username' => $wc_settings['username'],
                'api_key' => $wc_settings['apikey'],
                'OLSHOP_BRANCH' => $wc_settings['branch'],
                'OLSHOP_CUST' => $wc_settings['cust'],
                'OLSHOP_ORDERID' => 'SHEIN' . $id,
                'OLSHOP_SHIPPER_NAME' => $wc_settings['shipper_name'],
                'OLSHOP_SHIPPER_ADDR1' => $wc_settings['shipper_addr_1'],
                'OLSHOP_SHIPPER_ADDR2' => $wc_settings['shipper_addr_2'],
                'OLSHOP_SHIPPER_ADDR3' => $wc_settings['shipper_addr_3'],
                'OLSHOP_SHIPPER_CITY' => $wc_settings['shipper_city'],
                'OLSHOP_SHIPPER_REGION' => $wc_settings['shipper_region'],
                'OLSHOP_SHIPPER_ZIP' => $wc_settings['shipper_zip'],
                'OLSHOP_SHIPPER_PHONE' => $wc_settings['shipper_phone'],
                'OLSHOP_RECEIVER_NAME' => $data['shipping']['first_name'] . ' ' . $data['shipping']['last_name'],
                'OLSHOP_RECEIVER_ADDR1' => $data['shipping']['address_1'],
                'OLSHOP_RECEIVER_ADDR2' => '-',
                'OLSHOP_RECEIVER_ADDR3' => '',
                'OLSHOP_RECEIVER_CITY' => explode('-', $data['shipping']['city'])[1],
                'OLSHOP_RECEIVER_REGION' => $data['shipping']['state'],
                'OLSHOP_RECEIVER_ZIP' => $data['shipping']['postcode'],
                'OLSHOP_RECEIVER_PHONE' => $data['billing']['phone'],
                'OLSHOP_QTY' => $items['qty'],
                'OLSHOP_WEIGHT' => $items['weight'],
                'OLSHOP_GOODSDESC' => 'SHEIN PRODUCT',
                'OLSHOP_GOODSVALUE' => $items['total'],
                'OLSHOP_GOODSTYPE' => '1',
                'OLSHOP_INST' => '',
                'OLSHOP_INS_FLAG' => 'N',
                'OLSHOP_ORIG' => $wc_settings['origin'],
                'OLSHOP_DEST' => explode( '-', $data['shipping']['address_2'] )[1],
                'OLSHOP_SERVICE' => $items['service'],
                'OLSHOP_COD_FLAG' => 'N',
                'OLSHOP_COD_AMOUNT' => '0',
            );

            $postfield = '';

            foreach ($posts as $key => $post) {
                $postfield .= $key . '=' . rawurlencode($post) . '&';
            }

            $postfield = substr($postfield, 0, strlen($postfield) - 1);

            curl_setopt_array($curl, array(
                CURLOPT_URL => $wc_settings['endpoint'] . '/tracing/api/generatecnote',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postfield,
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/x-www-form-urlencoded'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $result = json_decode( $response, true );

            update_post_meta( $id, '_shein_jne_cnote_no', $result['detail'][0]['cnote_no'] );
        }
    };

    /**
     * Add js
     */
    add_action( 'wp_enqueue_scripts', 'shein_jne_plugin_script', 100 );

    function shein_jne_plugin_script()
    {
        wp_enqueue_script( 'shein-jne', WP_PLUGIN_URL . '/shein-jne/js/shein-jne.js', array(), false, true );
    }

    add_filter( 'woocommerce_my_account_my_orders_actions', 'shein_tracking_button', 10, 2 );

    function shein_tracking_button( $actions, $order ) {
        foreach ($order->get_meta_data() as $meta) {
            if ( $meta->get_data()['key'] == '_shein_jne_cnote_no' ) {
                $actions['track-awb'] = array(
                    'url'  => plugin_dir_url( 'shein-jne' ) . 'shein-jne/include/shein-tracking.php?cnote_no=' . $meta->get_data()['value'],
                    'name' => 'Lacak'
                );
            }
        }

        return $actions;
    }
}
