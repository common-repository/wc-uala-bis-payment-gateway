<?php

namespace uala_bis_WoocommercePaymentGateway;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
* Error Manager Class
*/
class WC_Error_Manager
{

    static public function get_alert_frame($message, $type, $url=null, $button_text= null){
        $pluginInstance = uala_bis::get_instance();
        $notice = '<div style="display: flex; align-items: center;" class="notice notice-'.$type.' is-dismissible"> 
                        <img style="width: 35px;
                        margin: 5px;" src="'.$pluginInstance->PLUGIN_LOGO.'">
                        <div style="display: flex;flex-direction: column; margin-left: 5px;">
                            <p>'.$message.'</p>
                            <a href="'.$url.'" target="_blank" style="border: none;
                                            padding: 5px 10px;
                                            background: #135e96;
                                            outline: 0;
                                            cursor: pointer;
                                            border-radius: 3px;
                                            color: #FFF;
                                            font-size: 12px;
                                            transition: all 0.05s;
                                            font-weight: normal;
                                            margin: 5px;
                                            text-decoration: none;
                                            width: 10rem;
                                            text-align: center;
                                            margin-top: 0px;
                                            margin-bottom: 10px;
                            ">'.$button_text.'</a>
                       </div>
                   </div>';
        return $notice;
    }

    static public function get_checkout_error($message){
        $pluginInstance = uala_bis::get_instance();
        $url= plugins_url().$pluginInstance->INSTANCE_LOGO_FILE_PARAM;
        wc_add_notice('<div><img style="width: 50px;float: left;
        margin: 7px;" src="'.$url.'"><p style="line-height: 200%;">'.$message.'</p></div>','error');
    }

}
