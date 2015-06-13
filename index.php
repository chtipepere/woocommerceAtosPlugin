<?php
/**
 * Plugin Name: WoocommerceAtos
 * Text Domain: woocommerce-atos
 * Plugin URI: https://github.com/chtipepere/woocommerceAtosPlugin
 * Description: Extends Woocommerce with Atos SIPS gateway (French bank).
 * Version: 1.2.1
 * Author: πR
 **/

// Exit if accessed directly
if (false === defined( 'ABSPATH' )) {
    exit;
}

if ( ! class_exists( 'WooCommerce' )) {
    function woocommerce_required()
    {
        echo sprintf(
            '<div class="error"><p>%s</p></div>',
            __( '<strong>Error!</strong> Woocommerce is mandatory. Please install it.', 'woocommerce-atos' )
        );

        return;
    }

    add_action( 'admin_notices', 'woocommerce_required' );
}

define( 'WOOCOMMERCEATOS_PHP_VERSION', '5.3' );
define( 'WOOCOMMERCE_MINIMUM_VERSION', '2.3.5' );

if ( ! version_compare( PHP_VERSION, WOOCOMMERCEATOS_PHP_VERSION, '>=' )) {
    function woocommerce_required_version()
    {
        echo sprintf(
            '<div class="error"><p>%s</p></div>',
            sprintf(
                __( '<strong>Error!</strong> WoocommerceAtos requires at least PHP %s! Your version is: %s. Please upgrade.',
                    'woocommerce-atos' ),
                WOOCOMMERCEATOS_PHP_VERSION,
                PHP_VERSION
            )
        );

        return;
    }

    add_action( 'admin_notices', 'woocommerce_required_version' );
}

if ( ! version_compare( Woocommerce::instance()->version, WOOCOMMERCE_MINIMUM_VERSION, '>=' )) {
    function woocommerce_minimum_version()
    {
        echo sprintf(
            '<div class="error"><p>%s</p></div>',
            sprintf( __( '<strong>Error!</strong> WoocommerceAtos requires at least Woocommerce %s! Your version is: %s. Please upgrade.',
                'woocommerce-atos' ),
                WOOCOMMERCE_MINIMUM_VERSION,
                Woocommerce::instance()->version
            )
        );

        return;
    }

    add_action( 'admin_notices', 'woocommerce_minimum_version' );
}

if (function_exists( 'add_action' )) {
    add_action( 'plugins_loaded', 'woocommerce_atos_init', 0 );
}

function woocommerce_atos_init()
{

    if ( ! class_exists( 'WC_Payment_Gateway' )) {
        return;
    }

    /** Translations */
    $plugin_dir = basename( dirname( __FILE__ ) );
    load_plugin_textdomain( 'woocommerce-atos', false, sprintf( '%s/languages/', $plugin_dir ) );

    /**
     * Add the gateway to Woocommerce
     */
    add_filter( 'woocommerce_payment_gateways', function ( $methods ) {
        $methods[] = 'Woocommerce_atos';

        return $methods;
    } );

    include_once( 'automatic_response.php' );

    add_filter( sprintf( 'plugin_action_links_%s', plugin_basename( __FILE__ ) ), 'add_action_links' );

    /**
     * @param $links
     *
     * @return array
     */
    function add_action_links( $links )
    {
        $mylinks = array(
            sprintf( '<a href="%s">%s</a>',
                admin_url( 'admin.php?page=wc-settings&tab=checkout&section=woocommerce_atos',
                    __( 'Settings', 'woocommerce-atos' ) ) ),
            sprintf( '<a href="https://github.com/chtipepere/woocommerceAtosPlugin/blob/master/README.md">%s</a>',
                __( 'Docs', 'woocommerce-atos' ) )
        );

        return array_merge( $links, $mylinks );
    }

    /**
     * Gateway class
     */
    class Woocommerce_atos extends WC_Payment_Gateway
    {

        protected $params;
        public $msg         = array();
        public $settings    = array();
        public $form_fields = array();

        public function __construct()
        {
            // Go wild in here 
            $this->id                 = 'woocommerce_atos';
            $this->icon               = sprintf( '%s/%s/images/logo.gif', WP_PLUGIN_URL,
                plugin_basename( dirname( __FILE__ ) ) );
            $this->has_fields         = false;
            $this->method_title       = 'Atos';
            $this->method_description = __( 'France based ATOS Worldline SIPS is the leading secure payment solution in Europe. Atos works by sending the user to your bank to enter their payment information.',
                'woocommerce-atos' );

            $this->init_form_fields();
            $this->init_settings();

            $this->description            = $this->get_option( 'woocommerce_atos_description' );
            $this->enabled                = $this->get_option( 'woocommerce_atos_is_enabled' );
            $this->title                  = $this->get_option( 'woocommerce_atos_title' );
            $this->merchant_id            = $this->get_option( 'woocommerce_atos_merchant_id' );
            $this->merchant_name          = $this->get_option( 'woocommerce_atos_merchant_name' );
            $this->pathfile               = $this->get_option( 'woocommerce_atos_pathfile' );
            $this->path_bin_request       = $this->get_option( 'woocommerce_atos_path_bin_request' );
            $this->path_bin_response      = $this->get_option( 'woocommerce_atos_path_bin_response' );
            $this->cancel_return_url      = $this->get_option( 'woocommerce_atos_cancel_return_url' );
            $this->automatic_response_url = $this->get_option( 'woocommerce_atos_automatic_response_url' );
            $this->normal_return_url      = $this->get_option( 'woocommerce_atos_normal_return_url' );
            $this->logo_id2               = $this->get_option( 'woocommerce_atos_logo_id2' );
            $this->advert                 = $this->get_option( 'woocommerce_atos_advert' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id,
                array( $this, 'process_admin_options' ) );
            add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
        }

        public function init_form_fields()
        {

            $this->form_fields = array(
                'woocommerce_atos_is_enabled'             => array(
                    'title'   => __( 'Enable Atos', 'woocommerce-atos' ),
                    'type'    => 'checkbox',
                    'label'   => __( 'Enable Atos SIPS Module.', 'woocommerce-atos' ),
                    'default' => 'no'
                ),
                'woocommerce_atos_title'                  => array(
                    'title'       => sprintf( __( 'Atos Standard %s', 'woocommerce-atos' ),
                        '<img style="vertical-align:middle;margin-top:-4px;margin-left:10px;" src="' . WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) . '/images/logo.gif" alt="Atos">' ),
                    'type'        => 'text',
                    'description' => __( 'This controls the title which the user sees during checkout.',
                        'woocommerce-atos' ),
                    'default'     => __( 'Credit card', 'woocommerce-atos' )
                ),
                'woocommerce_atos_description'            => array(
                    'title'       => __( 'Description:', 'woocommerce-atos' ),
                    'type'        => 'textarea',
                    'description' => __( 'Atos works by sending the user to your bank to enter their payment information.',
                        'woocommerce-atos' ),
                    'default'     => __( 'Paiement sécurisé par Carte Bancaire (Atos)', 'woocommerce-atos' )
                ),
                'woocommerce_atos_merchant_id'            => array(
                    'title'       => __( 'Merchant id', 'woocommerce-atos' ),
                    'type'        => 'text',
                    'description' => __( 'Merchant id given by your bank', 'woocommerce-atos' ),
                    'default'     => '014022286611112'
                ),
                'woocommerce_atos_pathfile'               => array(
                    'title'       => __( 'Pathfile file', 'woocommerce-atos' ),
                    'type'        => 'text',
                    'description' => __( 'Path to the pathfile file given by your bank', 'woocommerce-atos' ),
                    'default'     => '/var/www/site/param/pathfile'
                ),
                'woocommerce_atos_path_bin_request'       => array(
                    'title'       => __( 'Request bin file path', 'woocommerce-atos' ),
                    'type'        => 'text',
                    'description' => __( 'Path to the request bin file given by your bank', 'woocommerce-atos' ),
                    'default'     => '/var/www/site/bin/static/request'
                ),
                'woocommerce_atos_path_bin_response'      => array(
                    'title'       => __( 'Response bin file path', 'woocommerce-atos' ),
                    'type'        => 'text',
                    'description' => __( 'Path to the response bin file given by your bank', 'woocommerce-atos' ),
                    'default'     => '/var/www/site/bin/static/response'
                ),
                'woocommerce_atos_cancel_return_url'      => array(
                    'title'       => __( 'Cancel return url', 'woocommerce-atos' ),
                    'type'        => 'text',
                    'description' => __( 'Return url in case of canceled transaction', 'woocommerce-atos' ),
                    'default'     => site_url( '/cancel' )
                ),
                'woocommerce_atos_normal_return_url'      => array(
                    'title'       => __( 'Normal return url', 'woocommerce-atos' ),
                    'type'        => 'text',
                    'description' => __( 'Return url when a user click on the << Back to the shop >> button',
                        'woocommerce-atos' ),
                    'default'     => site_url( '/thankyou' )
                ),
                'woocommerce_atos_logo_id2'               => array(
                    'title'       => __( 'Logo id2', 'woocommerce-atos' ),
                    'type'        => 'text',
                    'description' => __( 'Right image on Atos page', 'woocommerce-atos' ),
                    'default'     => 'logo_id2.gif'
                ),
                'woocommerce_atos_advert'                 => array(
                    'title'       => __( 'Advert', 'woocommerce-atos' ),
                    'type'        => 'text',
                    'description' => __( 'Center image on Atos page', 'woocommerce-atos' ),
                    'default'     => 'advert.gif'
                ),
                'woocommerce_atos_automatic_response_url' => array(
                    'title'       => __( 'Automatic response url', 'woocommerce-atos' ),
                    'type'        => 'text',
                    'description' => __( 'URL called in case of success payment', 'woocommerce-atos' ),
                    'default'     => site_url( '?page=12' )
                )
            );
        }

        /**
         * Process the payment and return the result
         *
         * @param $order_id
         *
         * @return array
         */
        public function process_payment( $order_id )
        {
            $order = new WC_order( $order_id );

            return array(
                'result'   => 'success',
                'redirect' => $order->get_checkout_payment_url( true )
            );
        }

        /**
         *  There are no payment fields for atos, but we want to show the description if set.
         **/
        public function payment_fields()
        {
            if ($this->description) {
                echo wpautop( wptexturize( $this->description ) );
            }
        }

        /**
         * @param $order_id
         */
        public function receipt_page( $order_id )
        {
            echo sprintf(
                '<p>%s</p>',
                __( 'Thank you for your order, please click the button below to pay.', 'woocommerce-atos' )
            );
            echo $this->generate_atos_form( $order_id );
        }

        public function thankyou_page()
        {
            if ($this->description) {
                echo wpautop( wptexturize( $this->mercitxt ) );
            }
        }

        /**
         * @param $content
         *
         * @return string
         */
        public function showMessage( $content )
        {
            return sprintf(
                '<div class="box %s-box">%s</div>%s',
                $this->msg['class'],
                $this->msg['message'],
                $content
            );
        }

        /**
         * Generate atos button link
         *
         * @param $order_id
         *
         * @return string
         */
        public function generate_atos_form( $order_id )
        {
            // Contains every informations about the basket and the customer
            $order = new WC_order( $order_id );

            $pathfile         = $this->pathfile;
            $path_bin_request = $this->path_bin_request;

            $this->addParam( 'merchant_id', $this->merchant_id );
            $this->addParam( 'merchant_country', 'fr' );
            $this->addParam( 'amount', $this->calcAmount( $order->order_total ) );
            $this->addParam( 'currency_code', 978 );
            $this->addParam( 'pathfile', $pathfile );
            $this->addParam( 'normal_return_url', $this->normal_return_url );
            $this->addParam( 'cancel_return_url', $this->cancel_return_url );
            $this->addParam( 'automatic_response_url', $this->automatic_response_url );
            $this->addParam( 'language', 'fr' );
            $this->addParam( 'payment_means', 'CB,2,VISA,2,MASTERCARD,2' );
            $this->addParam( 'header_flag', 'no' );
            $this->addParam( 'order_id', $order_id );
            $this->addParam( 'logo_id2', $this->logo_id2 );
            $this->addParam( 'advert', $this->advert );

            $parm         = escapeshellcmd( $this->getParams() );
            $result       = exec( "$path_bin_request $parm" );
            $codeAndError = explode( '!', "$result" );

            $code  = $codeAndError[1];
            $error = $codeAndError[2];

            if (( $code == '' ) && ( $error == '' )) {

                return sprintf( '<p>%s %s</p>', __( 'Error calling the atos api: exec request not found',
                    'woocommerce-atos' ), $path_bin_request );

            } elseif ($code != 0) {

                return sprintf( '<p>%s %s</p>', __( 'Atos API error:', 'woocommerce-atos' ), $error );

            } else {

                // Display form with bank cards list
                return $codeAndError[3];
            }
        }

        /**
         * @param $key
         * @param $value
         */
        protected function addParam( $key, $value )
        {
            $param = sprintf( '%s=%s', $key, $value );
            $this->params .= sprintf( ' %s', $param );
        }

        /**
         * @return mixed
         */
        protected function getParams()
        {
            return $this->params;
        }

        /**
         * @param $total
         *
         * @return string
         */
        private function calcAmount( $total )
        {
            $amount = ( $total ) * 100;

            return str_pad( $amount, 3, '0', STR_PAD_LEFT );
        }

    }
}
