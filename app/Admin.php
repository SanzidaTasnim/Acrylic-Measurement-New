<?php

namespace Sanzida\AcrylicMeasurement\App;

/**
 * Admin Class
 */
class Admin {

    /**
     * class constructor
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue' ] );
        add_action( 'add_meta_boxes_product', [ $this, 'add_custom_meta_box_to_product' ] );
        add_action( 'save_post', [ $this, 'save_data' ] );
    }

    /**
     * Admin Enqueue Assets
     */
    public function admin_enqueue() {
        //Enqueue all css
        wp_enqueue_style( 'admin-acrylic-measurement-css', ACRYLIC_MEASUREMENT_ASSET . '/admin/css/admin.css', '', time(), 'all' );
        //Enqueue all js
        wp_enqueue_script( 'admin-acrylic-measurement-js', ACRYLIC_MEASUREMENT_ASSET . '/admin/js/admin.js', '', time(), 'true' );

    }

    /**
     * custom meta box on product
     * @since 0.0.1
     */
    public function add_custom_meta_box_to_product() {
        add_meta_box(
            'product_category_option',
            'Product Category Section', 
            [ $this, 'custom_product_category_option' ],
            'product',                 
            'normal',               
            'high'                  
        );
    }

    /**
     * custom meta box on product callback
     * @since 0.0.1
     */
    public function custom_product_category_option( $post ) {

        wp_nonce_field( 'am-nonce', 'am_nonce' );
        $updated_value  = get_post_meta( $post->ID, 'category_and_price', true );
        $updated_length = get_post_meta( $post->ID, 'length_ratio', true );
        $updated_width  = get_post_meta( $post->ID, 'width_ratio', true );
        $min_length_z     = get_post_meta( $post->ID, 'min_length', true );
        $max_length_z     = get_post_meta( $post->ID, 'max_length', true );
        $min_width_z      = get_post_meta( $post->ID, 'min_width', true );
        $max_width_z      = get_post_meta( $post->ID, 'max_width', true );
        $min_height_z     = get_post_meta( $post->ID, 'min_height', true );
        $max_height_z     = get_post_meta( $post->ID, 'max_height', true );
        $updated_value  = json_decode( $updated_value );
        $category       = isset( $updated_value->category ) ? $updated_value->category : '';
        $prices         = get_post_meta( $post->ID, 'prices_per_square_meter', true );
        $prices         = !empty( $prices ) ? json_decode( $prices, true ) : [];
        
        echo '
            <div class="am-custom-product-category">
                <label for="custom_meta_key">Select Product Category</label>
                <select name="custom_meta_key" id="custom_meta_key">
                    <option value="">--Select One--</option>
                    <option value="zuschnitt"'.selected( $category, 'zuschnitt', false ).'>Zuschnitt</option>
                    <option value="haube"'.selected( $category, 'haube', false ).'>Haube</option>
                </select>
                <input type="hidden" name="current_category" class="am-current-category" value="" />';

                for ( $i = 1; $i <= 20; $i++ ) {
                    $price = isset( $prices[$i]['price'] ) ? $prices[$i]['price'] : '';
                    $checked = isset( $prices[$i]['enabled'] ) && $prices[$i]['enabled'] ? 'checked' : '';

                    echo '
                        <div class="am-custom-product-price">
                            <label for="price_per_square_meter_'.$i.'">Price per Square Meter ('.$i .'mm Dicke)</label>
                            <div class="am-custom-price-wrapper">
                                <input type="text" name="prices['.$i.'][price]" class="am-price-per-square-meter_zuchi" value="'.$price.'" />
                                <label>
                                    <input type="checkbox" name="prices['.$i.'][enabled]" value="yes" id="am-sq-price" '.$checked.'>
                                    Activate?
                                </label>
                            </div>
                        </div>';
                }

        for ( $i = 3; $i <= 6; $i++ ) {
            // Fetch existing data from post meta
            $haube_data = get_post_meta( $post->ID, 'haube_prices', true );
            $haube_data = !empty( $haube_data ) ? json_decode( $haube_data, true ) : [];

            // Check if there is existing data for this index and set variables accordingly
            $enabled    = isset( $haube_data[$i]['enabled'] ) && $haube_data[$i]['enabled'] ? 'checked' : '';
            $price      = isset( $haube_data[$i]['price'] ) ? $haube_data[$i]['price'] : '';
            $min_length = isset( $haube_data[$i]['min_length'] ) ? $haube_data[$i]['min_length'] : '';
            $max_length = isset( $haube_data[$i]['max_length'] ) ? $haube_data[$i]['max_length'] : '';
            $min_width  = isset( $haube_data[$i]['min_width'] ) ? $haube_data[$i]['min_width'] : '';
            $max_width  = isset( $haube_data[$i]['max_width'] ) ? $haube_data[$i]['max_width'] : '';
            $min_height = isset( $haube_data[$i]['min_height'] ) ? $haube_data[$i]['min_height'] : '';
            $max_height = isset( $haube_data[$i]['max_height'] ) ? $haube_data[$i]['max_height'] : '';

            echo '
            <div class="am-custom-product-price-haube">
                <label for="price_per_square_meter_' . $i . '">Dicke ' . $i . 'mm aktivieren?
                    <input type="checkbox" name="prices_haube[' . $i . '][enabled]" value="yes" id="am-sq-price" ' . $enabled . '>
                </label>
                
                <div class="am-custom-haube-price-wrapper">
                    <label for="haube_mm_price">QM Preis</label>
                    <input type="text" name="prices_haube[' . $i . '][price]" class="am-price-per-square-meter" value="' . $price . '" />
                </div>
                <div class="am-custom-haube-lwh-wrapper">
                    <div class="am-custom-haube-length-wrap">
                        <div class="am-custom-haube-length-min">
                            <label for="haube_mm_price">Min-Länge:</label>
                            <input type="text" name="prices_haube[' . $i . '][min_length]" class="" value="' . $min_length . '" />
                        </div>
                        <div class="am-custom-haube-length-max">
                            <label for="haube_mm_price">Max-Länge:</label>
                            <input type="text" name="prices_haube[' . $i . '][max_length]" class="" value="' . $max_length . '" />
                        </div>
                    </div>
                    <div class="am-custom-haube-length-wrap">
                        <div class="am-custom-haube-length-min">
                            <label for="haube_mm_price">Min-Breite:</label>
                            <input type="text" name="prices_haube[' . $i . '][min_width]" class="" value="' . $min_width . '" />
                        </div>
                        <div class="am-custom-haube-length-max">
                            <label for="haube_mm_price">Max-Breite:</label>
                            <input type="text" name="prices_haube[' . $i . '][max_width]" class="" value="' . $max_width . '" />
                        </div>
                    </div>
                    <div class="am-custom-haube-length-wrap">
                        <div class="am-custom-haube-length-min">
                            <label for="haube_mm_price">Min-Höhe:</label>
                            <input type="text" name="prices_haube[' . $i . '][min_height]" class="" value="' . $min_height . '" />
                        </div>
                        <div class="am-custom-haube-length-max">
                            <label for="haube_mm_price">Max-Höhe:</label>
                            <input type="text" name="prices_haube[' . $i . '][max_height]" class="" value="' . $max_height . '" />
                        </div>
                    </div>    
                </div>
            </div>';
        }


        echo ' 
            <div class="am-ratio-section">
                <div class="am-length">
                    <label for="category-length">Ratio of Length</label>
                    <input type="text" name="category_length" class="am-category-length" value="'.$updated_length.'" />
                </div>
                <div class="am-width">
                    <label for="category-width">Ratio of Width</label>
                    <input type="text" name="category_width" class="am-category-width" value="'.$updated_width.'" />
                </div>
            </div>
            <div class="am-limit-section">
                <div class="am-length-limit">
                    <label for="min_category_length">Length(Minimum Value)</label>
                    <input type="number" name="min_length" class="am-category-length" value="'.$min_length_z.'" />
                    <label for="max_category_length">Length(Maximum Value)</label>
                    <input type="number" name="max_length" class="am-category-length" value="'.$max_length_z.'" />
                </div>
                <div class="am-width-limit">
                    <label for="min_category_width">Width(Minimum Value)</label>
                    <input type="number" name="min_width" class="am-category-width" value="'.$min_width_z.'" />
                    <label for="max_category_width">Width(Maximum Value)</label>
                    <input type="number" name="max_width" class="am-category-width" value="'.$max_width_z.'" />
                </div>
                <div class="am-height-limit">
                    <label for="min_category_height">Height(Minimum Value)</label>
                    <input type="number" name="min_height" class="am-category-height" value="'.$min_height_z.'" />
                    <label for="max_category_width">Height(Maximum Value)</label>
                    <input type="number" name="max_height" class="am-category-height" value="'.$max_height_z.'" />
                </div>
            </div>
        </div>';
    }

    /**
     * saving meta data
     * @since 0.0.1
     */
    public function save_data( $post_id ) {

        // Saving meta for Zuchinitt category

        $am_nonce   = isset( $_POST['am_nonce'] ) ? $_POST['am_nonce'] : '';
        $am_length  = isset( $_POST['category_length'] ) ? $_POST['category_length'] : '';
        $am_width   = isset( $_POST['category_width'] ) ? $_POST['category_width'] : '';
        $min_length = isset( $_POST['min_length'] ) ? $_POST['min_length'] : '';
        $max_length = isset( $_POST['max_length'] ) ? $_POST['max_length'] : '';
        $min_width  = isset( $_POST['min_width'] ) ? $_POST['min_width'] : '';
        $max_width  = isset( $_POST['max_width'] ) ? $_POST['max_width'] : '';
        $min_height = isset( $_POST['min_height'] ) ? $_POST['min_height'] : '';
        $max_height = isset( $_POST['max_height'] ) ? $_POST['max_height'] : '';

        if( ! $this->is_secured( $post_id, $am_nonce, 'am-nonce' ) ) {
            return $post_id;
        }

        if ( isset( $_POST['custom_meta_key'] ) ) {
            $am_value = json_encode(
                [
                    'category' => sanitize_text_field( $_POST['custom_meta_key'] )
                ]
            );
            update_post_meta( $post_id, 'category_and_price', $am_value );
        }

        $prices = isset( $_POST['prices'] ) ? (array) $_POST['prices'] : [];
        $prices_to_save = [];

        foreach ( $prices as $thickness => $data ) {
            if ( isset( $data['price'] ) ) {
                $prices_to_save[$thickness] = [
                    'price'   => sanitize_text_field( $data['price'] ),
                    'enabled' => ( isset( $data['enabled'] ) && $data['enabled'] === "yes" ) ? true : false,
                ];
            }
        }


        update_post_meta( $post_id, 'prices_per_square_meter', json_encode( $prices_to_save ) );
        update_post_meta( $post_id, 'length_ratio', sanitize_text_field( $am_length ) );
        update_post_meta( $post_id, 'width_ratio', sanitize_text_field( $am_width ) );
        update_post_meta( $post_id, 'min_length', sanitize_text_field( $min_length ) );
        update_post_meta( $post_id, 'max_length', sanitize_text_field( $max_length ) );
        update_post_meta( $post_id, 'min_width', sanitize_text_field( $min_width ) );
        update_post_meta( $post_id, 'max_width', sanitize_text_field( $max_width ) );
        update_post_meta( $post_id, 'min_height', sanitize_text_field( $min_height ) );
        update_post_meta( $post_id, 'max_height', sanitize_text_field( $max_height ) );

        // Saving meta for haube category

        $haube_prices = isset( $_POST['prices_haube'] ) ? (array) $_POST['prices_haube'] : [];
        $haube_prices_to_save = [];

        foreach ( $haube_prices as $index => $values ) {
            $enabled = isset( $values['enabled'] ) && $values['enabled'] === 'yes' ? true : false;
            $price = isset( $values['price'] ) ? sanitize_text_field( $values['price'] ) : '';

            // For lengths, widths, and heights
            $min_length = isset( $_POST['prices_haube'][ $index ]['min_length'] ) ? sanitize_text_field($_POST['prices_haube'][ $index ]['min_length'] ) : '';
            $max_length = isset( $_POST['prices_haube'][ $index ]['max_length'] ) ? sanitize_text_field( $_POST['prices_haube'][ $index ]['max_length'] ) : '';

            $min_width = isset( $_POST['prices_haube'][ $index ]['min_width'] ) ? sanitize_text_field( $_POST['prices_haube'][ $index ]['min_width']) : '';
            $max_width = isset( $_POST['prices_haube'][ $index ]['max_width'] ) ? sanitize_text_field( $_POST['prices_haube'][ $index ]['max_width']) : '';

            $min_height = isset( $_POST['prices_haube'][ $index ]['min_height'] ) ? sanitize_text_field( $_POST['prices_haube'][ $index ]['min_height']) : '';
            $max_height = isset( $_POST['prices_haube'][ $index ]['max_height'] ) ? sanitize_text_field( $_POST['prices_haube'][ $index ]['max_height']) : '';

            // Assemble all haube data
            $haube_prices_to_save[ $index ] = [
                'enabled'    => $enabled,
                'price'      => $price,
                'min_length' => $min_length,
                'max_length' => $max_length,
                'min_width'  => $min_width,
                'max_width'  => $max_width,
                'min_height' => $min_height,
                'max_height' => $max_height
            ];
        }

        // Save haube data to post meta
        update_post_meta( $post_id, 'haube_prices', json_encode( $haube_prices_to_save ) );
    }

    /**
     * Check metabox security
     */
    private function is_secured( $post_id, $nonce, $action ) {

        if( ! wp_verify_nonce( $nonce, $action ) ) {
            return false;
        }
        if( ! current_user_can( 'edit_post', $post_id ) ) {
            return false;
        }
        if( wp_is_post_autosave( $post_id ) ) {
            return false;
        }
        if( wp_is_post_revision( $post_id) ) {
            return false;
        }
        
        return true;
    }

}