<?php

namespace Sanzida\AcrylicMeasurement\App;

use Error;

/**
 * Front Class
 */
class Front {

    /**
     * class constructor
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'front_enqueue' ] );
        add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'add_custom_fields' ] );
        add_action( 'woocommerce_single_product_summary', [ $this, 'custom_product_data_to_js' ], 30 );
        add_action( 'wp_ajax_update_haube_custom_price', [ $this, 'handle_haube_custom_price_update' ] );
        add_action( 'wp_ajax_nopriv_update_haube_custom_price', [ $this, 'handle_haube_custom_price_update' ] );
        add_filter( 'woocommerce_before_calculate_totals', [ $this, 'adjust_price_based_on_custom_logic'], 40 );
        //new code
        add_filter( 'woocommerce_add_cart_item_data', [ $this, 'cart_item_data' ], 10, 3 );
        add_filter( 'woocommerce_get_item_data' , [ $this, 'get_item_data' ], 10, 2 );
        add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'create_order_line_item' ], 10 ,4 ); 

        add_filter('woocommerce_order_item_get_formatted_meta_data', [ $this, 'hide_price_per_sqm_from_frontend' ], 10, 2);

    }
    
    /**
     * Add custom data to cart item
     */
    public function cart_item_data($cart_item_data, $product_id, $variation_id) {
        if (isset($_POST['haube_custom_length'], $_POST['haube_custom_width'], $_POST['haube_custom_height'], $_POST['am_thickness'], $_POST['thickness_value'])) {
            $cart_item_data['haube_custom_length'] = sanitize_text_field($_POST['haube_custom_length']);
            $cart_item_data['haube_custom_width'] = sanitize_text_field($_POST['haube_custom_width']);
            $cart_item_data['haube_custom_height'] = sanitize_text_field($_POST['haube_custom_height']);
            $cart_item_data['am_thickness'] = sanitize_text_field($_POST['am_thickness']);
            $cart_item_data['thickness'] = sanitize_text_field($_POST['thickness_value']);
            $cart_item_data['unique_key'] = md5(microtime() . rand());

            $custom_price = WC()->session->get('custom_price_' . $product_id);
            if (!is_null($custom_price)) {
                $cart_item_data['custom_price'] = $custom_price;
                WC()->session->__unset('custom_price_' . $product_id);
            }
        }

        if (isset($_POST['custom_length'], $_POST['custom_width'], $_POST['am_thickness'], $_POST['thickness_value'])) {
            $cart_item_data['custom_length'] = sanitize_text_field($_POST['custom_length']);
            $cart_item_data['custom_width'] = sanitize_text_field($_POST['custom_width']);
            $cart_item_data['custom_field_select'] = sanitize_text_field($_POST['custom_field_select']);
            $cart_item_data['am_thickness'] = sanitize_text_field($_POST['am_thickness']);
            $cart_item_data['thickness'] = sanitize_text_field($_POST['thickness_value']);
            $cart_item_data['unique_key'] = md5(microtime() . rand());

            $custom_price = WC()->session->get('custom_price_' . $product_id);
            if (!is_null($custom_price)) {
                $cart_item_data['custom_price'] = $custom_price;
                WC()->session->__unset('custom_price_' . $product_id);
            }
        }

        return $cart_item_data;
    }

    /**
     * Display custom data in cart
     */
    public function get_item_data($item_data, $cart_item) {
        if (isset($cart_item['haube_custom_length'], $cart_item['haube_custom_width'], $cart_item['haube_custom_height'], $cart_item['am_thickness'], $cart_item['thickness'])) {
            $item_data[] = ['name' => 'Länge', 'value' => $cart_item['haube_custom_length']];
            $item_data[] = ['name' => 'Breite', 'value' => $cart_item['haube_custom_width']];
            $item_data[] = ['name' => 'Höhe', 'value' => $cart_item['haube_custom_height']];
            $item_data[] = ['name' => 'Materialdicke', 'value' => $cart_item['thickness'] . 'mm'];
            // $item_data[] = ['name' => 'Price Per Sqm', 'value' => wc_price($cart_item['am_thickness'])];
        }

        if (isset($cart_item['custom_length'], $cart_item['custom_width'], $cart_item['custom_field_select'], $cart_item['am_thickness'], $cart_item['thickness'])) {
            $item_data[] = ['name' => 'Länge', 'value' => $cart_item['custom_length']];
            $item_data[] = ['name' => 'Breite', 'value' => $cart_item['custom_width']];
            $item_data[] = ['name' => 'Kanten', 'value' => $cart_item['custom_field_select']];
            $item_data[] = ['name' => 'Materialdicke', 'value' => $cart_item['thickness'] . 'mm'];
            // $item_data[] = ['name' => 'Price Per Sqm', 'value' => wc_price($cart_item['am_thickness'])];
        }

        return $item_data;
    }

    /**
     * Add custom data to order line item
     */
    public function create_order_line_item($item, $cart_item_key, $values, $order) {
        if (isset($values['haube_custom_length'], $values['haube_custom_width'], $values['haube_custom_height'], $values['am_thickness'], $values['thickness'])) {
            $item->add_meta_data('Länge', $values['haube_custom_length']);
            $item->add_meta_data('Breite', $values['haube_custom_width']);
            $item->add_meta_data('Höhe', $values['haube_custom_height']);
            $item->add_meta_data('Materialdicke', $values['thickness'] . 'mm');
            // $item->add_meta_data('Price Per Sqm', wc_price($values['am_thickness']));
        }

        if (isset($values['custom_length'], $values['custom_width'], $values['custom_field_select'], $values['am_thickness'], $values['thickness'])) {
            $item->add_meta_data('Länge', $values['custom_length']);
            $item->add_meta_data('Breite', $values['custom_width']);
            $item->add_meta_data('Kanten', $values['custom_field_select']);
            $item->add_meta_data('Materialdicke', $values['thickness'] . 'mm');
            // $item->add_meta_data('Price Per Sqm', wc_price($values['am_thickness']));
        }
    }

    public function hide_price_per_sqm_from_frontend( $formatted_meta, $order_item ) {
        if (!is_admin()) {
            foreach ( $formatted_meta as $key => $meta ) {
                if ('Price Per Sqm' === $meta->key) {
                    unset( $formatted_meta[$key] );
                }
            }
        }

        return $formatted_meta;
    }

    /**
     * Enqueuing Front Files
     */
    public function front_enqueue() {
        //Enqueue all css
        wp_enqueue_style( 'front-acrylic-measurement-css', ACRYLIC_MEASUREMENT_ASSET . '/front/css/front.css', '', time(), 'all' );

        //Enqueue all js
        wp_enqueue_script( 'front-acrylic-measurement-js', ACRYLIC_MEASUREMENT_ASSET . '/front/js/front.js', '', time(), 'true' );
    }

    public function add_custom_fields() {
        global $product;

        if ( ! is_a($product, 'WC_Product') ) {
            return;
        }
        $meta_value    = get_post_meta( $product->get_id(), 'category_and_price', true );
        $prices        = get_post_meta( $product->get_id(), 'prices_per_square_meter', true );
        $updated_value = json_decode( $meta_value );
        $category      = isset( $updated_value->category ) ? $updated_value->category : '';
        if (!$prices) {
            echo '<p>Thickness options are not available.</p>';
            return;
        }

        $prices = json_decode( $prices, true );

        if (empty($prices)) {
            echo '<p>Thickness options are not set.</p>';
            return;
        }

        if ( $category == 'zuschnitt'):
        ?>
            <div class="custom_fields">
                <p class="custom_fields_category">
                    <label for="custom_field_select">Kanten</label>
                    <select name="custom_field_select" id="custom_field_select">
                        <option value="">--Wählen Sie eine Option--</option>
                        <option value="roh" selected>Roh</option>
                        <option value="gebrochen">Gebrochen</option>
                        <option value="gebrochen und poliert">Gebrochen und Poliert</option>
                    </select>
                </p>
                <div class="am-thickness-selector">
                    <label for="am_thickness">Materialdicke wählen:</label>
                    <select id="am_thickness" name="am_thickness">
                        <option value="">Materialdicke wählen</option>
                        <?php
                            foreach ( $prices as $thickness => $data ) {
                                if ( $data['enabled' ] ) {
                                    echo '<option value="' . esc_attr( $data['price'] ) . '">' . esc_html( $thickness ) . 'mm </option>';
                                }
                            }
                        ?>
                    </select>
                    <input type="hidden" id="thickness_value" name="thickness_value" value="">
                </div>
                <div class="am-reset-link">
                    <p class="reset_zuschnitt_fields">Leeren</p>
                </div>
                <div class="am_custom_field_category_text">
                    <h2></h2>
                </div>
                <p class="am-custom-field">
                    <label for="custom_length">Länge (mm): </label>
                    <input type="number" id="custom_length" name="custom_length" min="0" step="any">
                </p>
                <p class="am-custom-field">
                    <label for="custom_width">Breite (mm): </label>
                    <input type="number" id="custom_width" name="custom_width" min="0" step="any">
                </p>
                <p class="am-custom-field">
                    <span>Total Quadratmeter</span>
                    <span class="am-sqm-area">0.00</span>
                </p>
                <p class="am-custom-field">
                    <span>Finaler Preis pro Stück</span>
                    <span class="am-custom-field-price"></span>
                </p>
            </div>
        <?php
        endif;

        if ( $category == 'haube'):
            $haube_prices = get_post_meta( $product->get_id(), 'haube_prices', true );
            $haube_prices_decode = json_decode( $haube_prices );

            $has_enabled_thickness = false;
            foreach ( $haube_prices_decode as $item ) {
                if ( ! empty( $item->enabled ) ) {
                    $has_enabled_thickness = true;
                    break;
                }
            }
            if ( ! $has_enabled_thickness ) {
                echo '<p>Thickness options are not set.</p>';
                return;
            }
        ?>
            <div class="am_haube_custom_fields">
                <div class="am-thickness-selector">
                    <label for="am_thickness">Materialdicke wählen:</label>
                    <select id="am_thickness" name="am_thickness" class="am_haube_thickness">
                        <!-- <option value="">Materialdicke wählen</option> -->
                        <?php
                        foreach ( $haube_prices_decode as $thickness => $data ) {
                            if ( $data->enabled ) {
                                echo '<option value="' . esc_attr( $data->price ) . '">' . esc_html( $thickness ) . 'mm </option>';
                            }
                        }
                        ?>
                    </select>
                    <input type="hidden" id="thickness_value" name="thickness_value" value="">
                </div>
                <p class="am-haube-custom-field">
                    <label for="haube_custom_length">Länge (mm): </label>
                    <input type="number" id="haube_custom_length" name="haube_custom_length" min="0" step="any">
                </p>
                <p class="am-haube-custom-field">
                    <label for="haube_custom_width">Breite (mm): </label>
                    <input type="number" id="haube_custom_width" name="haube_custom_width" min="0" step="any">
                </p>
                <p class="am-haube-custom-field">
                    <label for="haube_custom_height">Höhe (mm): </label>
                    <input type="number" id="haube_custom_height" name="haube_custom_height" min="0" step="any">
                </p>
                <p class="am-haube-custom-field-mc">
                    <span>Total Quadratmeter</span>
                    <span class="am-haube-sqm-area" style="display: none;"></span>
                </p>
                <p class="am-haube-custom-field-mc" >
                    <span>Preis pro Quadratmeter</span>
                    <span class="am-haube-square_meter"></span>
                </p>
                <p class="am-haube-custom-field-mc">
                    <span>Material Cost</span>
                    <span class="am-haube-material-cost" style="display: none;"></span>
                </p>
                <p class="am-haube-custom-field-wc">
                    <span>Workload Cost</span>
                    <span class="am-haube-workload-cost" style="display: none;"></span>
                </p>
                <p class="am-haube-custom-field">
                    <span>Finaler Preis pro Stück</span>
                    <span class="am-haube-final-price"></span>
                    <input type="number" class="am-custom-field-price-input">
                </p>
                <div class="am-haube-reset-link">
                    <p class="reset_haube_fields">Leeren</p>
                </div>
            </div>
        <?php
        endif;
    }

    public function custom_product_data_to_js() {
        global $product;

        if ( ! is_a($product, 'WC_Product') ) {
            return;
        }

        // meta value for zuchinitt category

        $meta_value    = get_post_meta( $product->get_id(), 'category_and_price', true );
        $updated_value = json_decode( $meta_value );
        $category      = isset( $updated_value->category ) ? $updated_value->category : '';
        $length_ratio  = get_post_meta( $product->get_id(), 'length_ratio', true );
        $width_ratio   = get_post_meta( $product->get_id(), 'width_ratio', true ); 
        $min_length    = get_post_meta( $product->get_id(), 'min_length', true );
        $max_length    = get_post_meta( $product->get_id(), 'max_length', true );
        $min_width     = get_post_meta( $product->get_id(), 'min_width', true );
        $max_width     = get_post_meta( $product->get_id(), 'max_width', true );
        $min_height    = get_post_meta( $product->get_id(), 'min_height', true );
        $max_height    = get_post_meta( $product->get_id(), 'max_height', true );
        
        $nonce = wp_create_nonce( 'am_nonce' );

        // meta value for haube categpry

        $haube_prices = get_post_meta($product->get_id(), 'haube_prices', true);
        $haube_prices = !empty($haube_prices) ? json_decode($haube_prices, true) : [];

        // Create an array to pass to JavaScript that includes haube data
        $haube_data = [];
        foreach ( $haube_prices as $thickness => $data ) {
            $haube_data[$thickness] = [
                'enabled'    => $data['enabled'] ?? '',
                'price'      => $data['price'] ?? '',
                'min_length' => $data['min_length'] ?? '',
                'max_length' => $data['max_length'] ?? '',
                'min_width'  => $data['min_width'] ?? '',
                'max_width'  => $data['max_width'] ?? '',
                'min_height' => $data['min_height'] ?? '',
                'max_height' => $data['max_height'] ?? '',
            ];
        }

        // Preparing data to pass
        $data = array(
            'category'     => $category,
            'admin_url'    => admin_url('admin-ajax.php'),
            'price'        => '',
            'nonce'        => $nonce,
            'length_ratio' => $length_ratio,
            'width_ratio'  => $width_ratio,
            'min_length'   => $min_length,
            'max_length'   => $max_length,
            'min_width'    => $min_width,
            'max_width'    => $max_width,
            'min_height'   => $min_height,
            'max_height'   => $max_height,
            'haube_data'   => $haube_data,
        );
 
        // Localize the script
        wp_localize_script( 'front-acrylic-measurement-js', 'AM_ARR', $data );
    }

    public function handle_haube_custom_price_update(){

        $am_nonce = isset( $_POST['nonce'] ) ? $_POST['nonce'] : '';
        check_ajax_referer( 'am_nonce', 'nonce' );

        $product_id = intval( $_POST['product_id'] );
        $custom_price = floatval( $_POST['price'] );

        if ($product_id > 0 && $custom_price > 0 ) {
			if (!WC()->session->has_session()) {
                WC()->session->set_customer_session_cookie(true);
            }
            WC()->session->set('custom_price_' . $product_id, $custom_price);
            wp_send_json_success( 'price updated successfully' );
        } else {
            wp_send_json_error('Invalid data');
        }
        wp_die();
    }

    function adjust_price_based_on_custom_logic( $cart_object ) {
        // Ensure the cart isn't empty
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
    
        foreach ($cart_object->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['custom_price'])) {
                $custom_price = $cart_item['custom_price'];
                $cart_item['data']->set_price($custom_price);
            }
        }
    }
    
    
}





