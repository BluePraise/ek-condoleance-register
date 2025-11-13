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
        $post_id = intval( $_POST['post_id'] );
        $candle_name = sanitize_text_field($_POST['candle_name']);
        $candle_date = sanitize_text_field($_POST['candle_date']);
        // check for old data
        $old_data = get_post_meta($post_id, 'cmb_condalances_candles', 1);
        if (!is_array($old_data)) {
            //echo 'jmd1';exit;
            $data = ['count' => 1];
            if ($candle_name) {
                $data['authors'][] = [
                    [
                        'candle_name' => $candle_name,
                        'candle_date' => $candle_date
                    ]
                ];
            }
            update_post_meta($post_id, 'cmb_condalances_candles', $data);
        } else {
            //echo 'jmd2';exit;

            if ($candle_name) {
                $old_data['authors'][] = [
                    'candle_name' => $candle_name,
                    'candle_date' => $candle_date
                ];
            }
            update_post_meta($post_id, 'cmb_condalances_candles', [
                'count' => $old_data['count'] + 1,
                'authors' => $old_data['authors']
            ]);
        }

        $newdata = get_post_meta($post_id, 'cmb_condalances_candles', 1);

        $string = 'Er zijn ' . $newdata['count'] . ' kaarsjes aangestoken.';

        if ($newdata['count'] == 1) {
            $string = ' Er is 1 kaars aangestoken.';
        }
        $thankyou = 'Bedankt voor het aansteken van de kaars.';
        $response = array(
            'string' => $string,
            'thankyou' => $thankyou,
            'authors' => json_encode(array_reverse($newdata['authors']))
        );
        wp_send_json_success(__( $response, $this->plugin_text_domain ));
    }
}