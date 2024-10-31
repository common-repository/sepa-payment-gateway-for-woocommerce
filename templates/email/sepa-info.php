<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $sepa_payment_gateway_data ) : ?>
	<h2><?php echo apply_filters( 'woocommerce_sepa_payment_gateway_my_orders_title', __( 'SEPA Payment Gateway Information', 'sepa-payment-gateway' ) ); ?></h2>

	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%;" border="1">
		<thead>
			<tr>
				<th class="account-holder" scope="col" class="td" style="text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;"><?php _e( 'Account holder', 'sepa-payment-gateway' ); ?></th>
				<th class="iban" scope="col" class="td" style="text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;"><span class="nobr"><?php _e( 'IBAN', 'sepa-payment-gateway' ); ?></th>
				<th class="bic" scope="col" class="td" style="text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;"><?php _e( 'BIC', 'sepa-payment-gateway' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr class="sepa-info">
					<td class="account-holder" data-title="<?php _e( 'Account holder', 'sepa-payment-gateway' ); ?>" style="text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;">
						<?php echo esc_html( $sepa_payment_gateway_data['account'] ); ?>
					</td>
					<td class="iban" data-title="<?php _e( 'IBAN', 'sepa-payment-gateway' ); ?>" style="text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;">
						<?php echo esc_html( $sepa_payment_gateway_data['iban'] ); ?>
					</td>
					<td class="bic" data-title="<?php _e( 'BIC', 'sepa-payment-gateway' ); ?>" style="text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4; padding: 12px;">
					<?php echo esc_html( $sepa_payment_gateway_data['bic'] ); ?>
					</td>
				</tr>
		</tbody>
	</table>
	<br /><br />

<?php
endif;
