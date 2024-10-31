<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shipment Tracking
 *
 * Shows tracking information in the plain text order email
 *
 * @author  WooThemes
 * @package WooCommerce Shipment Tracking/templates/email/plain
 * @version 1.6.4
 */

if ( $sepa_payment_gateway_data ) :

	echo apply_filters( 'woocommerce_sepa_payment_gateway_my_orders_title', __( 'SEPA Payment Gateway Information', 'sepa-payment-gateway' ) );

		echo  "\n";
		echo esc_html( $sepa_payment_gateway_data[ 'account' ] ) . "\n";
		echo esc_html( $sepa_payment_gateway_data['iban'] ) . "\n";
		echo esc_url( $sepa_payment_gateway_data[ 'bic' ] ) . "\n\n";
		
	echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-= \n\n";

endif;

?>
