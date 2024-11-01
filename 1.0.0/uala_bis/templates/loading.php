<?php
namespace uala_bis_WoocommercePaymentGateway;

/* Template Name: uala_bis Template */

try{
    $pluginInstance = uala_bis::get_instance();

    //get order
    $order_id = $_GET['order_id'];

    if($order_id){
        $order = wc_get_order( $order_id );

        //get uuid
        $uuid = get_post_meta( $order->get_id(), '_uuid' );

        $middleware = Middleware::getInstance();
        $response = $middleware->getOrder($uuid[0], $pluginInstance);
            
        // update status
        $pluginInstance->update_order_status($order, $response['data']['attributes']);

        //redirect
        header('Refresh: 5; url='.$order->get_checkout_order_received_url().'');
    }

}catch (\Throwable $th){
    header( 'HTTP/1.1 500 ERROR' );
}

?>

<style>
    :root {
        --primary-brand-color: <?php echo esc_attr($pluginInstance->PLUGIN_INFO_PAGE_SECONDARY_COLOR) ?>;
        --primary-brand-R: <?php echo esc_attr($pluginInstance->PLUGIN_INFO_PAGE_R) ?>;
        --primary-brand-G: <?php echo esc_attr($pluginInstance->PLUGIN_INFO_PAGE_G) ?>;
        --primary-brand-B: <?php echo esc_attr($pluginInstance->PLUGIN_INFO_PAGE_B) ?>;
    }


    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        word-break: keep-all
    }

    .loader-body {
        background-color: white;
        font-family: 'Roboto', sans-serif;
    }

    img {
    max-width: 100%;
    }

    .loaderTimeout g:first-child g:first-child {
        display: none;
    }

    .loaderTimeout path {
        fill: var(--primary-brand-color);
        stroke: rgba(var(--primary-brand-R), var(--primary-brand-G), var(--primary-brand-B), 0.3);
    }

    .timeOutPage {
        background: white;
        background: linear-gradient(180.06deg, rgba(187, 181, 250, 0) 44.16%, rgba(197, 172, 233, 0.409457) 55.72%, #D3A0D0 69.38%, #EE7C71 84.99%);
        background-repeat: no-repeat;
        background-position: 0 100px;
        display: flex;
        justify-content: center;
        height: 100vh;
        width: 100%;
    }

    .timeOutPage .container {
        height: 300px;
        margin: 100px auto 0;
        padding: 60px 0 0 0;
        position: relative;
        width: 300px;
    }

    .timeOutPage .animation {
        align-items: center;
        display: flex;
        height: 300px;
        justify-content: center;
        left: 50%;
        position: absolute;
        text-align: center;
        top: 50%;
        width: 300px;
        z-index: 2;
        transform: translate(-50%, -50%);
        -moz-transform: translate(-50%, -50%);
        -webkit-transform: translate(-50%, -50%);
    }

    .timeOutPage .image {
        align-items: center;
        display: flex;
        justify-content: center;
        position: relative;
        z-index: 10;
    }

    .timeOutPage .text-center {
        position: relative;
        text-align: center;
        z-index: 10;
    }

    .timeOutPage .text-center .text-xl {
        font-size: 1.25em;
    }

    .timeOutPage .text-center .text-xs {
        font-size: 0.75em;
    }

    .stroke-primary-brand-color {
        stroke: var(--primary-brand-color)
    }
</style>

<div class="loader-body">
    <section class="timeOutPage">
        <div class="container">
            <div class="animation">
                <div id="lottie"></div>
            </div>
            <div class="image">
                <svg width="98" height="92" viewBox="0 0 98 92" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M65.9867 16.884L69.5201 16.2456L70.1587 19.7808L70.1591 19.7826C71.3952 26.2724 70.0238 32.9457 66.3251 38.3531L66.3248 38.3536L49.7683 62.7166L33.5339 51.6902L50.09 27.3277C53.7896 21.9189 59.4977 18.1202 65.9858 16.8841C65.9861 16.8841 65.9864 16.884 65.9867 16.884Z"
                        class="stroke-primary-brand-color" stroke-width="0.387097" />
                    <path
                        d="M33.265 51.7415L49.9302 27.2184C53.6584 21.7679 59.4109 17.9394 65.9505 16.6938L69.6761 16.0208L70.3492 19.7464C71.5948 26.2861 70.213 33.0119 66.4848 38.4623L49.8196 62.9855L33.265 51.7415Z"
                        class="stroke-primary-brand-color" stroke-width="1.37148" stroke-miterlimit="10" />
                    <path d="M33.2138 51.4719L25.4041 46.1291L41.1733 39.7066L33.2138 51.4719Z"
                        class="stroke-primary-brand-color" stroke-width="0.387097" />
                    <path d="M24.9977 46.0856L41.691 39.2867L33.2651 51.7415L24.9977 46.0856Z"
                        class="stroke-primary-brand-color" stroke-width="1.37148" stroke-miterlimit="10" />
                    <path d="M50.0216 62.914L58.0447 51.1679L57.8497 68.1926L50.0216 62.914Z"
                        class="stroke-primary-brand-color" stroke-width="0.387097" />
                    <path d="M58.0391 68.5538L58.2456 50.5307L49.7519 62.9656L58.0391 68.5538Z"
                        class="stroke-primary-brand-color" stroke-width="1.37148" stroke-miterlimit="10" />
                    <path
                        d="M62.4444 35.4412C60.8983 37.7083 57.7738 38.3368 55.5068 36.7907C53.2595 35.1769 52.631 32.0524 54.1771 29.7854C55.7232 27.5183 58.8477 26.8897 61.1147 28.4358C63.4297 30.0695 63.9906 33.1742 62.4444 35.4412Z"
                        class="stroke-primary-brand-color" stroke-width="1.37148" stroke-miterlimit="10" />
                    <path d="M31.2382 72.4659L39.3701 60.5131" class="stroke-primary-brand-color" stroke-width="1.37148"
                        stroke-miterlimit="10" />
                    <path d="M41.6201 74.9184L46.2386 68.185" class="stroke-primary-brand-color" stroke-width="1.37148"
                        stroke-miterlimit="10" />
                    <path d="M25.133 63.6943L29.6838 56.941" class="stroke-primary-brand-color" stroke-width="1.37148"
                        stroke-miterlimit="10" />
                </svg>
            </div>
            <div class="text-center">
                <div class="text-xl">
                    Danos unos segundos
                </div>
                <div class="text-xs">
                    mientras procesamos tu orden
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bodymovin/5.9.6/lottie.min.js"
        integrity="sha512-yAr4fN9WZH6hESbOwoFZGtSgOP+LSZbs/JeoDr02pOX4yUFfI++qC9YwIQXIGffhnzliykJtdWTV/v3PxSz8aw=="
        crossorigin="anonymous" referrerpolicy="no-referrer">
</script>

<script>
    const animationName = <?php echo file_get_contents(plugin_dir_url( __FILE__ ).'assets/lottie/loader-timeout.json'); ?>

    createAnimation(animationName);

    function createAnimation(json) {
        var animation = bodymovin.loadAnimation({
            container: document.getElementById('lottie'),
            animationData: json,
            loop: true,
            autoplay: true,
            rendererSettings: {
                className: 'loaderTimeout'
            },
            name: "Timeout Loader",
        })
    }
</script>