<?php
/*
Plugin Name: Payment Bac-Credomatic
Plugin URI: https://www.baccredomatic.com/
Author Name: Luis Mora
Description: Este es un plugin para usar el metodo de pago del Bac-Credomatic
Version: 0.0.1
Licence: 0.0.1
* License URL: http://www.gnu.org/licenses/gpl-2.0.txt
* text-domain: bac-pay-woo
*/

function activar(){

}

function desactivar(){

}

register_activation_hook(__FILE__,'activar');
register_deactivation_hook(__FILE__,'desactivar');

//Verifica si WC esta instalado
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) return;

add_action( 'plugins_loaded', 'bac_payment_init', 11 );

function bac_payment_init() {
    if( class_exists( 'WC_Payment_Gateway' ) ) {
        class WC_bac_pay_Gateway extends WC_Payment_Gateway {
            public function __construct() {
                $this->id   = 'bac_payment';
                $this->icon = apply_filters( 'woocommerce_bac_icon', plugins_url('/assets/icon.png', __FILE__ ) );
                $this->has_fields = false;
                $this->method_title = __( 'bac Payment', 'bac-pay-woo');
                $this->method_description = __( 'bac local content payment systems.', 'bac-pay-woo');

                $this->title = $this->get_option( 'title' );
                $this->description = $this->get_option( 'description' );
                $this->instructions = $this->get_option( 'instructions', $this->description );

                $this->init_form_fields();
                $this->init_settings();

                add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
                // add_action( 'woocommerce_thank_you_' . $this->id, array( $this, 'thank_you_page' ) );
            }

            public function init_form_fields() {
                $this->form_fields = apply_filters( 'woo_bac_pay_fields', array(
                    'enabled' => array(
                        'title' => __( 'Enable/Disable', 'bac-pay-woo'),
                        'type' => 'checkbox',
                        'label' => __( 'Enable or Disable bac Payments', 'bac-pay-woo'),
                        'default' => 'no'
                    ),
                    'title' => array(
                        'title' => __( 'bac Payments Gateway', 'bac-pay-woo'),
                        'type' => 'text',
                        'default' => __( 'bac Payments Gateway', 'bac-pay-woo'),
                        'desc_tip' => true,
                        'description' => __( 'Add a new title for the bac Payments Gateway that customers will see when they are in the checkout page.', 'bac-pay-woo')
                    ),
                    'description' => array(
                        'title' => __( 'bac Payments Gateway Description', 'bac-pay-woo'),
                        'type' => 'textarea',
                        'default' => __( 'Please remit your payment to the shop to allow for the delivery to be made', 'bac-pay-woo'),
                        'desc_tip' => true,
                        'description' => __( 'Add a new title for the bac Payments Gateway that customers will see when they are in the checkout page.', 'bac-pay-woo')
                    ),
                    'instructions' => array(
                        'title' => __( 'Instructions', 'bac-pay-woo'),
                        'type' => 'textarea',
                        'default' => __( 'Default instructions', 'bac-pay-woo'),
                        'desc_tip' => true,
                        'description' => __( 'Instructions that will be added to the thank you page and odrer email', 'bac-pay-woo')
                    ),
                    // 'enable_for_virtual' => array(
                    //     'title'   => __( 'Accept for virtual orders', 'woocommerce' ),
                    //     'label'   => __( 'Accept COD if the order is virtual', 'woocommerce' ),
                    //     'type'    => 'checkbox',
                    //     'default' => 'yes',
                    // ),
                ));
            }

            public function process_payments( $order_id ) {
                
                $order = wc_get_order( $order_id );

                $order->update_status( 'on-hold',  __( 'Awaiting bac Payment', 'bac-pay-woo') );

                // if ( $order->get_total() > 0 ) {
                    // Mark as on-hold (we're awaiting the cheque).
                // } else {
                    // $order->payment_complete();
                // }

               // $this->clear_payment_with_api();

                $order->reduce_order_stock();

                WC()->cart->empty_cart();

                return array(
                    'result'   => 'success',
                    'redirect' => $this->get_return_url( $order ),
                );
            }

            // public function clear_payment_with_api() {

            // }

            public function thank_you_page(){
                if( $this->instructions ){
                    echo wpautop( $this->instructions );
                }
            }
        }
    }
}

add_filter( 'woocommerce_payment_gateways', 'add_to_woo_bac_payment_gateway');

function add_to_woo_bac_payment_gateway( $gateways ) {
    $gateways[] = 'WC_bac_pay_Gateway';
    return $gateways;
}