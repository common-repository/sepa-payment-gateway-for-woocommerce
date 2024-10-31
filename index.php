<?php
/*
* Plugin Name: SEPA Payment Gateway for WooCommerce
* Plugin URI: https://nhathuynhvan.com/
* Description: Extends WooCommerce support SEPA Payment Gateway.
* Version: 1.0.0
* Author: Nhat Huynh Van
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
* Requires at least: 4.9
* Requires PHP: 5.6 or later
* Tested up to: 6.0
* Text Domain: sepa-payment-gateway
* Domain Path: /languages
*
* This program is free software; you can redistribute it and/or modify it under the terms of the GNU
* General Public License version 2, as published by the Free Software Foundation. You may NOT assume
* that you can use any other version of the GPL.
*
* This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
* even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

if ( ! defined( 'SEPA_PAYMENT_GATEWAY_PATH' ) ) {
    define( 'SEPA_PAYMENT_GATEWAY_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'SEPA_PAYMENT_GATEWAY_URL' ) ) {
    define( 'SEPA_PAYMENT_GATEWAY_URL', plugin_dir_url( __FILE__ ) );
}

if($uploads  = wp_upload_dir( null, false )) {
    if ( !defined( 'SEPA_PAYMENT_GATEWAY_LOG_PATH' ) ) {
        $logs_dir = $uploads['basedir'] . '/sepa-payment-gateway-for-woocommerce-logs';
        if($logs_dir) {
            define( 'SEPA_PAYMENT_GATEWAY_LOG_PATH', $logs_dir );
        }
    }
    
    if ( !defined( 'SEPA_PAYMENT_GATEWAY_LOG_URL' ) ) {
        $logs_url = $uploads['baseurl'] . '/sepa-payment-gateway-for-woocommerce-logs';
        if($logs_url) {
            define( 'SEPA_PAYMENT_GATEWAY_LOG_URL', $logs_url );
        }
    }
} 

include_once SEPA_PAYMENT_GATEWAY_PATH.'/vendor/autoload.php';

if ( ! class_exists( 'SEPA_PAYMENT_GATEWAY_IMPLEMENT' ) ) {
    class SEPA_PAYMENT_GATEWAY_IMPLEMENT {
        public function __construct() {
            $this->init();
            $this->hooks();
        }

        private function init(){
            $includes = array(
                'settings',
                'implement',
            );

            foreach( $includes as $files ){
                require_once( SEPA_PAYMENT_GATEWAY_PATH . "{$files}.php" );
            }

            register_activation_hook(__FILE__, array($this, 'sepa_payment_gateway_install'));
            register_deactivation_hook(__FILE__, array($this, 'sepa_payment_gateway_uninstall'));
        }


        public function sepa_payment_gateway_install() {
            if ( !is_dir( SEPA_PAYMENT_GATEWAY_LOG_PATH ) ) {
                mkdir( SEPA_PAYMENT_GATEWAY_LOG_PATH, 0755, true );
            }
        }

        public function sepa_payment_gateway_uninstall() {
        }

        private function hooks(){
            add_action( 'plugins_loaded', array($this, 'load_plugin_textdomain'));
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueue_scripts'));
        }

        function load_plugin_textdomain() {
            load_plugin_textdomain('sepa-payment-gateway', FALSE, basename(SEPA_PAYMENT_GATEWAY_PATH) . '/languages/');
        }

        public function admin_enqueue_scripts() {
            wp_enqueue_style( 'admin-sepa-payment-gateway-style', SEPA_PAYMENT_GATEWAY_URL . 'admin/css/style.css' );
        }

        function public_enqueue_scripts() {
            wp_enqueue_style( 'public-sepa-payment-gateway-style', SEPA_PAYMENT_GATEWAY_URL . 'public/css/wc-sepa.css' );
        }
    }

    $SEPA_PAYMENT_GATEWAY_IMPLEMENT = new SEPA_PAYMENT_GATEWAY_IMPLEMENT();
}