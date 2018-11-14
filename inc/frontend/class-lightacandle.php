<?php

namespace Tahlil\Inc\Frontend;

if (!defined('ABSPATH')) { exit; }

class LightACandle {

    private $plugin_text_domain;
    private $version;

    /**
     * Construct everything
     */
    public function __construct($plugin_text_domain, $version) 
    {
        $this->plugin_text_domain = $this->plugin_text_domain;
        $this->version = $version;

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        $this->ajax();
    }

    /**
     * Adds JavaScript to the front-end of the site.
     *
     * @param string $hook
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function enqueue_assets() {
        wp_enqueue_script(
            $this->plugin_text_domain . '-lightacandle',
            plugin_dir_url( __FILE__ ) . 'js/lightacandle.js',
            array( 'jquery' ),
            $this->version,
            true
        );

        $data = array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'light_a_candle' )
        );

        wp_localize_script( $this->plugin_text_domain . '-lightacandle', 'LIGHT_A_CANDLE', $data );
    }

    /**
     * Holds all ajax actions.
     *
     * @access public
     * @since  1.0.0
     * @return void
     */
    public function ajax()
    {
        add_action( 'wp_ajax_nopriv_light_a_candle', array( $this, 'light_a_candle' ) );
        add_action( 'wp_ajax_light_a_candle', array( $this, 'light_a_candle' ) );
    }

    public function light_a_candle()
    {
        // Security check.
        check_ajax_referer( 'light_a_candle', 'nonce' );

        // get the post id
        $post_id = strip_tags( $_POST['post_id'] );

        // check for old data
        $old_data = get_post_meta($post_id, 'cmb_condalances_candles', 1);

        if (!$old_data) {
            add_post_meta( $post_id, 'cmb_condalances_candles', 1 );
        } else {
            update_post_meta( $post_id, 'cmb_condalances_candles', $old_data + 1, $old_data );
        }

        $newdata = get_post_meta($post_id, 'cmb_condalances_candles', 1);
        $string = ' candles are lighting..';
        if ($newdata == 1) {
            $string = ' candle is lighting';
        }
        $thankyou = 'Bedankt voor het aansteken van de kaars.';
        $response = array(
            'string' => $newdata . $string,
            'thankyou' => $thankyou
        );
        wp_send_json_success(__( $response, $this->plugin_text_domain ));
    }
}