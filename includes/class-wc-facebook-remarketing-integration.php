<?php

if ( !defined('ABSPATH') ) exit;

/**
 * Facebook Remarketing integration class
 *
 * @extends WC_Integration
 */
class WC_Facebook_Remarketing_Integration extends WC_Integration
{
    /**
     * Init and hook in the integration.
     *
     * @return void
     */
    public function __construct()
    {
        $this->id                 = 'facebook_remarketing';
        $this->method_title       = __('Facebook Remarketing', 'woocommerce-facebook-remarketing');
        $this->method_description = __('Facebook custom audience event tracking integration for WooCommerce allowing remarketing via targeted ads.', 'woocommerce-facebook-remarketing');

        // Load the settings
        $this->init_form_fields();
        $this->init_settings();

        // Network data
        $this->facebook_id = $this->get_option('fr_facebook_id');

        // Save settings
        add_action('woocommerce_update_options_integration_' . $this->id, [$this, 'process_admin_options']);

        // Tracking code
        add_action('wp_footer', [$this, 'display_tracking_code'], 999999);
    }

    /**
     * Init settings form fields
     *
     * @return void
     */
    public function init_form_fields()
    {
        $this->form_fields = [
            'fr_facebook_id' => [
                'title'         => __('Facebook ID:', 'woocommerce-facebook-remarketing'),
                'description'   => __('Pixel ID for Facebook.', 'woocommerce-facebook-remarketing'),
                'type'          => 'text'
            ]
        ];
    }

    /**
     * Display tracking code globally and on the order received page
     *
     * @return string
     */
    public function display_tracking_code()
    {
		global $wp;

        if ( is_admin() || current_user_can('manage_options') || !$this->facebook_id ) return;

        $this->output_global_tracking_code();

        if ( is_order_received_page() )
        {
            $order_id = isset($wp->query_vars['order-received']) ? $wp->query_vars['order-received'] : 0;

            if ( 0 < $order_id )
            {
                $this->output_order_tracking_code($order_id);
            }
        }
    }

    /**
     * Output the global tracking code for enabled networks
     *
     * @return string
     */
    public function output_global_tracking_code()
    {
        $code  = "<script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
    document,'script','//connect.facebook.net/en_US/fbevents.js');

    fbq('init', '" . esc_js($this->facebook_id) . "');
    fbq('track', 'PageView');
</script>";
        $code .= '<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=' . esc_js($this->facebook_id) . '&ev=PageView&noscript=1"
/></noscript>';

        echo $code;
    }

    /**
     * Output the order tracking code for enabled networks
     *
     * @param int $order_id
     *
     * @return string
     */
    public function output_order_tracking_code($order_id)
    {
        $order = new WC_Order($order_id);

        $code  = "<script>fbq('track', 'Purchase', {value: '" . esc_js( $order->get_total() ) . "', currency: '" . esc_js( $order->get_order_currency() ) . "'});</script>";
        $code .= '<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=' . esc_js($this->facebook_id) . '&ev=Purchase&amp;cd[value]=' . urlencode( $order->get_total() ) . '&amp;cd[currency]=' . urlencode( $order->get_order_currency() ) . '&noscript=1"
/></noscript>';

        echo $code;
    }
}
