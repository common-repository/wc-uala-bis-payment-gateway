<?php 

namespace uala_bis_WoocommercePaymentGateway;


final class Middleware {

    private static $instance;

    /**
     * Return singletone instance
     * @return Middleware
     */
    static public function getInstance() 
    {
        if (self::$instance == NULL) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     */
    protected function __construct() 
    {
        //Check user's credentials
        add_action('wp_ajax_call_auth_uala_bis', [$this, 'checkLogin']);
        add_action('admin_init', [$this, 'adminCurrencyError']);
    }

    /**
     * Check Woocommerce's base currency and Plugin's base currency
     */
    function matchCurrency($currency, $pluginInstance)
    {
        if($pluginInstance->APP_CURRENCY != $currency){
            return false;
        }
        return true;
    }

    /**
     * Checks the login user
     */
    function checkLogin($pluginInstance)
    {
        if(!$pluginInstance)
        {
            $pluginInstance = uala_bis::get_instance();
        }
        $client_id = sanitize_text_field($_POST['client_id']);
        $secret_key = sanitize_text_field($_POST['secret_key']);
        try {
            $this->login($client_id, $secret_key, $pluginInstance);
            die('<div>Tus credenciales son validas.<span class="dashicons dashicons-yes"></span></div>');
        } catch (\Exception $th) {
            // No valid credentials
        }
        die('<div>Tus credenciales NO son validas.<span class="dashicons dashicons-no-alt"></span></div>');        
    }

    /**
     * Validates the currency symbol
     */
    function adminCurrencyError($pluginInstance)
    {
        if(!$pluginInstance)
        {
            $pluginInstance = uala_bis::get_instance();
        }
        if(class_exists('WC_Payment_Gateway')){
            if( !$this->matchCurrency(get_woocommerce_currency(), $pluginInstance))
            {
                add_action('admin_notices',function() use ($pluginInstance) {
                    $type = 'error';
                    $message = ''.$pluginInstance->PARTNER_NAME.' no podrá procesar sus ventas por la configuración de la moneda en Woocommerce. Ingresa al panel de configuración y cambiala a '.$pluginInstance->APP_CURRENCY.'';
                    $url = $pluginInstance->WP_GENERAL_ROUTE;
                    $button_text='Ir a la configuración';
                    echo WC_Error_Manager::get_alert_frame( $message, $type, $url, $button_text );
                });
            }
        }
    }

    /**
     * Set client information locally
     */
    public function setClientInformation($client_id, $secret_key)
    {
        $this->client_id = $client_id;
        $this->secret_key = $secret_key;
    }

    /**
     * JSON validator
     * 
     */
    private function validate_json($json = NULL, array $requiredKeys = []) 
    {
        try {
            if (is_string($json)) {
                $data = json_decode($json, true);
                $r = json_last_error() === JSON_ERROR_NONE;
                foreach($requiredKeys as $key) 
                {
                    $r = $r && array_key_exists($key, $data);
                }
                return $r;
            }
            return false;    
        } catch (\Throwable $th) {
            throw $th;
        }
    }    

    /**
     * Post Request
     * $url string URI 
     * $body string JSON Request body
     * $header array Http header request
     * $requestVerb string Http Verb
     * $jsonRequiredResponseFields array List of valid fields in json response
     */        
    private function generalRequest($url, $body, $header = ['Content-Type: application/json'], $requestVerb = 'POST', $jsonRequiredResponseFields = [], $functioName = 'wp_remote_post')
    {
        try {
            $response = $functioName(
                $url,
                array(
                    'method' => $requestVerb,
                    'headers' => $header,
                    'body' => $body
                )
            );

            if (is_wp_error( $response )) 
            {
                throw new \Exception($err);
            }

            $body = wp_remote_retrieve_body( $response );

            if (!$this->validate_json($body,$jsonRequiredResponseFields))
            {
                throw new \Exception('JSON Response not valid');
            }

            return json_decode( $body, true );
        } catch (\Throwable $th) {
            throw $th;
        }                      
    }

    /**
     * Backend Login. Token required to make transactions
     * client_id: string
     * secret_key: string
     */
    public function login($client_id, $secret_key, $pluginInstance)
    {   
        return $this->generalRequest(
            $pluginInstance->URL_AUTH,
            [
                'grant_type' => 'client_credentials',
                'client_id' => ''.$client_id.'',
                'client_secret' => ''.$secret_key.'',
                'scope' => '*'
            ],
            ['Content-Type: application/json'],
            'POST',
            ['token_type', 'access_token'],
            'wp_remote_post'
        );
    }    

    /**
     * Prepare order data
     * 
     */
    private function prepareOrderData($order, $successURL, $failedURL, $pluginInstance)
    {
        // prepare order items
        $items = $order->get_items();
        $payload_items = [];
        foreach ($items as $id => $item) {
            $payload_items[] = [
                'id' => $id,
                'name' => $item['name'],
                'unitPrice' => [
                    'currency' => $pluginInstance->APP_CURRENCY_CODE,
                    'amount' => ($item['total'] * 100)
                ],
                'quantity' => $item['quantity']
            ];
        }

        //crear una orden
        $data = [
            "attributes" => [
                "items" => $payload_items,
                "source" => "woocommerce",
                "redirect_urls" => [
                    "success" => $successURL,
                    "failed"  => $failedURL
                ],
            ]
        ];


        if($order->get_shipping_method())
        {

            $data["attributes"]["shipping"] = [
                "name" => $order->get_shipping_method(),
                "price" => [
                    "currency" => $pluginInstance->APP_CURRENCY_CODE,
                    "amount" => $order->get_total_shipping()*100
                ]
            ];
        }

        return [
            "data" => $data
        ];
    }

    /**
     * Create Order
     */
    public function createOrder($client_id, $secret_key, $order, $successURL, $failedURL, $pluginInstance)
    {
        try {
            $orderData = $this->prepareOrderData($order, $successURL, $failedURL, $pluginInstance);

            $authData = $this->login($client_id, $secret_key, $pluginInstance);

            // prepare auth information
            $token_type = $authData['token_type'];
            $access_token = $authData['access_token'];

            return $this->generalRequest(
                $pluginInstance->URL_ORDERS,
                json_encode($orderData),
                [
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                    'Authorization' => "{$token_type} {$access_token}"
                ],
                'POST',
                [],
                'wp_remote_post'
            );

        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Get Order
     */
    public function getOrder($uuid, $pluginInstance)
    {
        try {
            return $this->generalRequest(
                $pluginInstance->URL_ORDERS_STATE.'/'.$uuid,
                [],
                [
                    'Content-Type' => 'application/vnd.api+json',
                    'Accept' => 'application/vnd.api+json',
                ],
                'GET',
                [],
                'wp_remote_get'
            );

        } catch (\Throwable $th) {
            throw $th;
        }
    }

}

Middleware::getInstance();