<?php
/**
* Plugin Name: Payment Gateway for Ualá bis on Woocommerce
* Plugin URI: https://ualabis.com.ar/
* Description: Cobra con Ualá Bis en tu tienda y acepta pagos con tarjeta de crédito, débito y prepagas
* Version: 1.0.0
* Author: GeoPagos
* Author URI: http://www.geopagos.com
*/

namespace uala_bis_WoocommercePaymentGateway;

require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * Include helpers
 */
require_once( plugin_dir_path(__FILE__) . "/includes/helper/ErrorManager.php");

/**
 * Includes Middleware class
 */
require_once( plugin_dir_path(__FILE__) . "/middleware.php");


class uala_bis 
{
    protected static $instance;
    protected $data = [
        'APP_NAME' => NULL,
        'PARTNER_NAME' => NULL,
        'PLUGIN_DESCRIPTION' => NULL,
        'PLUGIN_LOGO' => NULL,
        'APP_CURRENCY' => NULL,
        'APP_CURRENCY_CODE' => NULL,
        'URL_AUTH' => NULL,
        'URL_ORDERS' => NULL,
        'URL_ORDERS_STATE' => NULL,
        'URL_CREDENTIALS_ACQUISITION' => NULL,
        'PLUGIN_INFO_PAGE_MAIN_COLOR' => NULL,
        'PLUGIN_INFO_PAGE_R' => NULL,
        'PLUGIN_INFO_PAGE_G' => NULL,
        'PLUGIN_INFO_PAGE_B' => NULL,
        'PLUGIN_INFO_PAGE_MAIN_LIGHT_COLOR' => NULL,
        'PLUGIN_INFO_PAGE_SECONDARY_COLOR' => NULL,
        'PLUGIN_INFO_PAGE_SECONDARY_LIGHT_COLOR' => NULL,
        'INSTANCE_LOGO_FILE_PARAM' => NULL,
        'USER_ERROR' => NULL,
        'USER_GENERAL_ERROR' => NULL
    ];
 
    protected function __construct() 
    {
        $env = plugin_dir_path(__FILE__) . "/config.ini";
        $merchand = parse_ini_file($env, false);
        $this->data = [
            'APP_NAME' => $merchand["APP_NAME"],
            'PARTNER_NAME' => $merchand["NAME"],
            'PLUGIN_DESCRIPTION' => $merchand["PLUGIN_DESCRIPTION"],
            'PLUGIN_LOGO' =>  $merchand["PLUGIN_LOGO"],
            'APP_CURRENCY' =>  $merchand["APP_CURRENCY"],
            'APP_CURRENCY_CODE' =>  $merchand["APP_CURRENCY_CODE"],
            'URL_AUTH' =>  $merchand["URL_AUTH"],
            'URL_ORDERS' =>  $merchand["URL_ORDERS"],
            'URL_ORDERS_STATE' =>  $merchand["URL_ORDERS_STATE"],
            'URL_CREDENTIALS_ACQUISITION' =>  $merchand["URL_CREDENTIALS_ACQUISITION"],
            'PLUGIN_INFO_PAGE_MAIN_COLOR' =>  $merchand["PLUGIN_INFO_PAGE_MAIN_COLOR"],
            'PLUGIN_INFO_PAGE_R' =>  $merchand["PLUGIN_INFO_PAGE_R"],
            'PLUGIN_INFO_PAGE_G' =>  $merchand["PLUGIN_INFO_PAGE_G"],
            'PLUGIN_INFO_PAGE_B' =>  $merchand["PLUGIN_INFO_PAGE_B"],
            'PLUGIN_INFO_PAGE_MAIN_LIGHT_COLOR' =>  $merchand["PLUGIN_INFO_PAGE_MAIN_LIGHT_COLOR"],
            'PLUGIN_INFO_PAGE_SECONDARY_COLOR' =>  $merchand["PLUGIN_INFO_PAGE_SECONDARY_COLOR"],
            'PLUGIN_INFO_PAGE_SECONDARY_LIGHT_COLOR' =>  $merchand["PLUGIN_INFO_PAGE_SECONDARY_LIGHT_COLOR"],
            'INSTANCE_LOGO_FILE_PARAM' =>  $merchand["INSTANCE_LOGO_FILE_PARAM"],
            'USER_ERROR' =>  $merchand["USER_ERROR"],
            'USER_GENERAL_ERROR' =>  $merchand["USER_GENERAL_ERROR"],
            'ADMIN_ROUTE' => admin_url("admin.php?page=wc-settings&tab=checkout"),
            'WP_GENERAL_ROUTE' => admin_url("admin.php?page=wc-settings&tab=general")
        ];        
    }

    public function __get($name)
    {
        if(!array_key_exists($name,$this->data))
        {
            return NULL;
        }
        return $this->data[$name];
    }
   
    public static function get_instance() 
    {
      if ( ! self::$instance ) 
      {
        self::$instance = new uala_bis();
      }
      return self::$instance;
    }

    public function update_order_status( $order, $properties )
    {
        if($order != null && $properties != null){
            if($properties['status'] == 'SUCCESS'){
                if($order->get_status() == 'pending'){
                    $order->update_status('completed', __( 'Completed', 'woocommerce' ));
                }
            }else{
                if($properties['status'] == 'CANCELED'){
                    if($order->get_status() == 'pending'){
                        $order->update_status('cancelled', __( 'Cancelled', 'woocommerce' ));
                    }
                }else{
                    if($properties['status'] == 'PENDING'){
                        if($order->get_status() != 'completed' && $order->get_status() != 'failed' && $order->get_status() != 'cancelled'){
                        	$order->update_status('pending', __( 'Pending payment', 'woocommerce' ));
                    	}
                    }else{
                        if($properties['status'] == 'EXPIRED'){
                            if($order->get_status() == 'pending'){
                                $order->update_status('failed', __( 'Failed', 'woocommerce' ));
                            }
                        }
                    }
                }
            }
        }else{
            throw new \Exception ();
        }
    }    
   
}

/**
 * Plugin Activation
 */
register_activation_hook(__FILE__, function() {
    $pluginInstance = uala_bis::get_instance();

    if ( version_compare( PHP_VERSION, '5.6', '<=' ) and current_user_can( 'activate_plugins' ) ) {
        wp_die('No es posible activar el plugin. Versión de PHP mínima 5.6<br><a href="' . admin_url( 'plugins.php' ) . '">&laquo; 
        Volver a la página de Plugins</a>');
    }
    
    if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) and current_user_can( 'activate_plugins' ) ) {
        add_action('admin_notices',function() {
            $pluginInstance = uala_bis::get_instance();
            $type = 'error';
            $message = ''.$pluginInstance->PARTNER_NAME.' requiere una versión de Woocommerce instalada y activa para funcionar.';
            $url = 'https://woocommerce.com/';
            $button_text='Descargar Woocommerce';
            echo WC_Error_Manager::get_alert_frame( $message, $type, $url, $button_text );
        });
    }

    if (is_plugin_active( 'woocommerce/woocommerce.php' ) and ($pluginInstance->APP_CURRENCY != get_woocommerce_currency()) and current_user_can( 'activate_plugins' )) {
        add_action('admin_notices',function() {
            $pluginInstance = uala_bis::get_instance();
            $type = 'error';
            $message = ''.$pluginInstance->PARTNER_NAME.' no podrá procesar sus ventas por la configuración de la moneda en Woocommerce. Ingresa al panel de configuración y cambiala a '.$pluginInstance->APP_CURRENCY.'';
            $url = $pluginInstance->WP_GENERAL_ROUTE;
            $button_text='Ir a la configuración';
            echo WC_Error_Manager::get_alert_frame( $message, $type, $url, $button_text );
        });
    }

    if(!wp_next_scheduled('uala_bis_cron_update_orders_status')){
        wp_schedule_event(time(), 'daily', 'uala_bis_cron_update_orders_status');
    }

    $objPage = get_page_by_title(''.$pluginInstance->PARTNER_NAME.' Loading', 'OBJECT', 'page');
    if( empty( $objPage ) ){
        // Create post object
        $loading_page = array(
            'post_type'     => 'page',
            'post_title'    => ''.$pluginInstance->PARTNER_NAME.' Loading',
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_author'   => 1
        );

        // Insert the post into the database
        wp_insert_post( $loading_page );
    }
});

// Add page template
add_filter( 'page_template', function(){
    $pluginInstance = uala_bis::get_instance();
    if ( is_page( ''.$pluginInstance->PARTNER_NAME.' Loading' ) ) {
        $page_template = dirname( __FILE__ ) . '/templates/loading.php';
        return $page_template;
    }
});

// plugin desactivation
register_deactivation_hook (__FILE__, function() {	
	$timestamp = wp_next_scheduled ('uala_bis_cron_update_orders_status');
	wp_unschedule_event ($timestamp, 'uala_bis_cron_update_orders_status');
});

//Update orders status for cron.
add_action ('uala_bis_cron_update_orders_status', function() { 
    try {
        $pluginInstance = uala_bis::get_instance();

        $args = array(
            'status' => 'pending',
            'date_created' => '>' . ( time() - 604800 )
        );
        $orders = wc_get_orders( $args );
    
        foreach ($orders as $order) {
            $uuid = get_post_meta( $order->get_id(), '_uuid' );
            $middleware = Middleware::getInstance();
            $response = $middleware->getOrder($uuid[0], $pluginInstance);
    
            $pluginInstance->update_order_status($order, $response['data']['attributes']);

            $message = 'Cron Update Order status '.$uuid[0].'';
            error_log( print_r($message, true) );
        }  
        
    } catch (\Throwable $th) {
        //throw $th;  
        $message = 'Error when Cron update Order status';
        error_log( print_r($message, true) );   
    }
}); 

add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_style( 'admin-page', plugin_dir_url( __FILE__ ).'templates/assets/css/style.css');
    wp_enqueue_style( 'admin-font', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
});

add_action( 'wp_enqueue_scripts', function(){
    wp_enqueue_style( 'loading-template', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap' );
} );

/**
 * Woocommerce sub-menu
 */
add_action('admin_menu', function() {
    $pluginInstance = uala_bis::get_instance(); 
    add_submenu_page( 
        'woocommerce', 
        $pluginInstance->PARTNER_NAME, 
        $pluginInstance->PARTNER_NAME, 
        'manage_options', 
        $pluginInstance->APP_NAME.'_settings', 
        function() use ($pluginInstance) {
            include( plugin_dir_path(__FILE__) . "/templates/index.php");
        } 
    ); 
},99);

/**
 * Woocomerce payment
 */
add_filter( 'woocommerce_payment_gateways', function( $gateways ) {
    $gateways[] = 'uala_bis_WoocommercePaymentGateway\WC_Geopagos_Gateway'; // class name
    return $gateways;
});

/*
 * Gateway Class
 */
add_action( 'plugins_loaded',function() {
    //Check Woocommerce class
    if (!class_exists('WC_Payment_Gateway')){
        add_action('admin_notices',function() {
            $pluginInstance = uala_bis::get_instance(); 
            $type = 'error';
            $message = ''.$pluginInstance->PARTNER_NAME.' requiere una versión de Woocommerce instalada y activa para funcionar.';
            $url = 'https://woocommerce.com/';
            $button_text='Descargar Woocommerce';
            echo WC_Error_Manager::get_alert_frame( $message, $type, $url, $button_text );
        });
        return;
    }
    class WC_Geopagos_Gateway extends \WC_Payment_Gateway {

        private $middleware;

        private $pluginInstance;
        
        public function __construct() {

            $this->pluginInstance = uala_bis::get_instance(); 

            $this->middleware = Middleware::getInstance();

            $this->id = $this->pluginInstance->APP_NAME; // payment gateway plugin ID
            $this->icon = apply_filters('woocommerce_custom_gateway_icon', $this->pluginInstance->PLUGIN_LOGO );  
            $this->has_fields = true; 
            $this->method_title = $this->pluginInstance->PARTNER_NAME;
            $this->method_description = $this->pluginInstance->PLUGIN_DESCRIPTION; 

            $this->supports = array(
                'products'
            );

            // Method with all the options fields
            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();
            $this->title = $this->pluginInstance->PARTNER_NAME;
            $this->description = $this->pluginInstance->PLUGIN_DESCRIPTION;
            $this->enabled = $this->get_option( 'enabled' );
            $this->client_id = $this->get_option( 'client_id' );
            $this->secret_key = $this->get_option( 'secret_key' );

            $this->url_dashboard = $this->pluginInstance->URL_CREDENTIALS_ACQUISITION;

            // This action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
            
            add_action( 'admin_footer', function() {
                echo "
                <script>
                    jQuery('.auth-button_uala_bis').prop('value', 'Verificar Credenciales');
                    jQuery('.auth-button_uala_bis').click((e) => {
                            jQuery('.description:last').html('<div>Cargando...</div>');
                            let data = {
                                action:'call_auth_uala_bis',
                                client_id: jQuery('.client_id').val(),
                                secret_key: jQuery('.secret_key').val()           
                            }
                            jQuery.ajax({
                                type: 'POST',
                                url: '".admin_url('admin-ajax.php')."',
                                data: data,
                                success:function(result) {
                                    jQuery('.description:last').html(result);
                                }
                    
                            });
                        }
                    );
                    jQuery('.redirect-button').prop('value', '¿Como conseguir mis credenciales?');
                    jQuery('.redirect-button').click((e) => {
                            window.open('$this->url_dashboard', '_blank');
                        }
                    );
                </script>";
            });

        }

        /**
         * Plugin options
         */
        public function init_form_fields()
        {

            $this->form_fields = array(
                'enabled' => array(
                    'title'       => 'Habilitar/Deshabilitar',
                    'label'       => 'Habilitar Medio de Pago',
                    'type'        => 'checkbox',
                    'description' => '',
                    'default'     => 'no'
                ),
                'quotes' => array(
                    'type'        => 'button',
                    'default'       => '¿Como conseguir mis credenciales?',
                    'class'       => 'redirect-button',
                    'css'         => '
                                    border: none;
                                    background: transparent;
                                    cursor: pointer;
                                    color: #135e96;
                                    font-size: 14px;
                                    font-weight: 500;
                                    text-decoration: underline;
                                    text-align: start;
                                    '
                ),
                'client_id' => array(
                    'title'       => 'Client ID',
                    'type'        => 'text',
                    'class'       => 'client_id'
                ),
                'secret_key' => array(
                    'title'       => 'Secret ID',
                    'type'        => 'text',
                    'class'       => 'secret_key'
                ),
                'auth_button' => array(
                    'type'        => 'button',
                    'default'     => 'Verificar Credenciales',
                    'description' => 'Verifica si tus credenciales son correctas.',
                    'class'       => 'auth-button_uala_bis',
                    'css'         => '
                                        border: none;
                                        padding: 10px 30px;
                                        background: #135e96;
                                        outline: 0;
                                        cursor: pointer;
                                        border-radius: 3px;
                                        color: #FFF;
                                        font-size: 14px;
                                        transition: all 0.05s;
                                        font-weight: 600;
                                        margin: 0 auto;
                                        font-family: proxima-nova, sans-serif;
                                        font-style: normal;
                                        margin-top: 1rem;
                                    '
                )
            );
    
        }

        public function process_payment( $order_id ) {
            try {
                global $woocommerce;

                $order = wc_get_order( $order_id );

                $this->pluginInstance = uala_bis::get_instance();
                if(!$this->middleware->matchCurrency(get_woocommerce_currency(), $this->pluginInstance)){
                    $order->update_status('failed', __( 'Failed', 'woocommerce' ));
                    add_action( 'admin_notices', function(){
                        $type = 'error';
                        $message = 'No es posible activar el plugin '.$this->pluginInstance->APP_NAME.'';
                        echo WC_Error_Manager::get_alert_frame( $message, $type );
                    });
                    WC_Error_Manager::get_checkout_error($this->pluginInstance->USER_ERROR);
                    return array(
                            'result'   => 'fail',
                            'redirect' => '',
                    );
                }

                $loadingPageURL = get_permalink(get_page_by_title(''.$this->pluginInstance->PARTNER_NAME.' Loading'));
                if($loadingPageURL){
                    if(strpos($loadingPageURL, '?') !== false){
                        $successURL= $loadingPageURL.'&order_id='.$order_id;
                        $failedURL= $loadingPageURL.'&order_id='.$order_id;
                    }else{
                        $successURL= $loadingPageURL.'?order_id='.$order_id;
                        $failedURL= $loadingPageURL.'?order_id='.$order_id;
                    }
                }else{
                    $successURL= get_site_url().'/?p=404_'.$this->pluginInstance->APP_NAME;
                    $failedURL= get_site_url().'/?p=404_'.$this->pluginInstance->APP_NAME;
                }

                // check items count and URL(success and failed)
                if(count($order->get_items()) < 1 || !$successURL || !$failedURL 
                || !$this->client_id || !$this->secret_key){
                    $order->update_status('failed', __( 'Failed', 'woocommerce' ));
                    WC_Error_Manager::get_checkout_error($this->pluginInstance->USER_ERROR);
                    return array(
                            'result'   => 'fail',
                            'redirect' => '',
                    );
                }

                // create order
                $response = $this->middleware->createOrder(
                    $this->client_id,
                    $this->secret_key,
                    $order,
                    $successURL, 
                    $failedURL,
                    $this->pluginInstance
                );   
                $order->update_meta_data( '_uuid', $response['data']['attributes']['uuid'] );
                $order->update_status('pending', __( 'Pending payment', 'woocommerce' ));
                $order->reduce_order_stock();
                $woocommerce->cart->empty_cart();
                return array(
                    'result' => 'success',
                    'redirect' => $response['data']['attributes']['links']['checkout'], 
                );

    
            } catch (\Throwable $th) {
                $order->update_status('failed', __( 'Failed', 'woocommerce' ));                
                WC_Error_Manager::get_checkout_error($this->pluginInstance->USER_GENERAL_ERROR);
                return array(
                        'result'   => 'fail',
                        'redirect' => '',
                );
            }
            
        }

    }
});

/**
 * IPN Callback
 * https://woocommerce.com/document/wc_api-the-woocommerce-api-callback/
 */
add_action( 'woocommerce_api_ipn-callback', function() {
    try {
        $pluginInstance = uala_bis::get_instance(); 

        // Recover IPN parameters
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        $uuid = $data['properties']['tracking']['paymentIntent'] ? $data['properties']['tracking']['paymentIntent']: null;

        if($uuid != null){
            $order = wc_get_orders([
                'meta_key' => '_uuid',
				'meta_value' => $uuid
            ]);
        }

        $properties = $data['properties'] ? $data['properties']: null;
        $order = $order[0] ? $order[0]: null;

        $pluginInstance->update_order_status($order, $properties);

        header( 'HTTP/1.1 200 OK' );
    } catch (\Throwable $th) {
        //throw $th;     
        header( 'HTTP/1.1 500 ERROR' );
    }
    echo "";
    wp_die();
});
