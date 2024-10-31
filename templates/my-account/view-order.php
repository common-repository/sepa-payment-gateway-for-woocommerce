<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
	
if ( $sepa_payment_gateway_data ) : ?>

	<h2><?php echo apply_filters( 'woocommerce_sepa_payment_gateway_my_orders_title', __( 'SEPA Payment Gateway Information', 'sepa-payment-gateway' ) ); ?></h2>

	<table class="shop_table shop_table_responsive my_account_sepa_payment_gateway">
		<thead>
			<tr>
				<th class="account-holder"><span class="nobr"><?php _e( 'Account holder', 'sepa-payment-gateway' ); ?></span></th>
				<th class="iban"><span class="nobr"><?php _e( 'IBAN', 'sepa-payment-gateway' ); ?></span></th>
				<th class="bic"><span class="nobr"><?php _e( 'BIC', 'sepa-payment-gateway' ); ?></span></th>
			</tr>
		</thead>
		<tbody>
			<tr class="sepa-info">
				<td class="account-holder" data-title="<?php _e( 'Account holder', 'sepa-payment-gateway' ); ?>">
					<?php echo esc_html( $sepa_payment_gateway_data['account'] ); ?>
				</td>
				<td class="iban" data-title="<?php _e( 'IBAN', 'sepa-payment-gateway' ); ?>">
					<?php echo esc_html( $sepa_payment_gateway_data['iban'] ); ?>
				</td>
				<td class="bic" data-title="<?php _e( 'BIC', 'sepa-payment-gateway' ); ?>">
				<?php echo esc_html( $sepa_payment_gateway_data['bic'] ); ?>
				</td>
			</tr>
		</tbody>
	</table>

<?php
endif;
