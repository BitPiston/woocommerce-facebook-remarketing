<?php
/*
 * Plugin Name: WooCommerce Facebook Remarketing
 * Plugin URI:  https://github.com/BitPiston/woocommerce-facebook-remarketing
 * Description: Facebook custom audience event tracking integration for WooCommerce allowing remarketing via targeted ads.
 * Version:     0.1
 * Author:      BitPiston Studios
 * Author URI:  http://bitpiston.com/
 */

if ( !defined('ABSPATH') ) exit;

/**
 * WC_Facebook_Remarketing class
 */
class WC_Facebook_Remarketing
{
    /**
     * @var WC_Facebook_Remarketing Single instance of this class
     */
    protected static $instance;

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     * @return void
     */
    public function __construct()
    {
        if ( class_exists('WC_Integration') && defined('WOOCOMMERCE_VERSION') )
        {
            require_once('includes/class-wc-facebook-remarketing-integration.php');

            // Register the integration
            add_filter('woocommerce_integrations', [$this, 'add_integration']);
        }
    }

    /**
     * Main instance, ensures only one instance is/can be loaded.
     *
     * @return WC_Facebook_Remarketing
     */
    public static function get_instance()
    {
        if ( is_null(self::$instance) )
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Add a new integration to WooCommerce.
     *
     * @param array $integrations WooCommerce integrations
     *
     * @return array WC_Facebook_Remarketing_Integration
     */
    public function add_integration($integrations)
    {
        $integrations[] = 'WC_Facebook_Remarketing_Integration';

        return $integrations;
    }
}

add_action('plugins_loaded', ['WC_Facebook_Remarketing', 'get_instance'], 0);
