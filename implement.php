<?php
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;
use Digitick\Sepa\PaymentInformation;
class SEPA_PAYMENT_GATEWAY_IMPLEMENTS{

    public function __construct(){
        add_action( 'plugins_loaded', array($this, 'sepa_payment_gateway_init'), 0 );
        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array($this, 'sepa_payment_gateway_action_links'));
        add_action( 'add_meta_boxes', array($this, 'sepa_payment_gateway_info'));
        add_action( 'wp_ajax_nopriv_export_sepa_xml', array($this, 'export_sepa_xml'));
        add_action( 'wp_ajax_export_sepa_xml', array($this, 'export_sepa_xml'));   
        add_action( 'admin_footer', array($this, 'export_sepa_xml_admin_js'));
        add_action( 'woocommerce_view_order', array($this, 'display_sepa_info'));
        add_action( 'woocommerce_email_before_order_table', array( $this, 'email_display' ), 0, 4 );
        add_action( 'woocommerce_thankyou', array($this, 'display_sepa_thankyou'), 10, 1);
    }

    public function sepa_payment_gateway_init() {
        if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;
        
        // If we made it this far, then include our Gateway Class
        include_once( 'woocommerce-sepa.php' );
    
        // Now that we have successfully included our class,
        // Lets add it too WooCommerce
        add_filter( 'woocommerce_payment_gateways', 'sepa_payment_gateway_add' );
        function sepa_payment_gateway_add( $methods ) {
            $methods[] = 'SEPA_PAYMENT_GATEWAY';
            return $methods;
        }
    }

    public function sepa_payment_gateway_action_links( $links ) {
        $plugin_links = array(
            '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=sepa_payment_gateway' ) . '">' . __( 'Settings', 'sepa-payment-gateway' ) . '</a>',
        );
    
        // Merge our new link with the default ones
        return array_merge( $plugin_links, $links );
    }

    public function sepa_payment_gateway_info() {
        add_meta_box( 'sepa_payment_gateway_data', __('SEPA Payment Info','woocommerce'), array($this, 'sepa_payment_gateway_data_fields'), 'shop_order', 'side', 'core' );
    }

    public function sepa_payment_gateway_data_fields() {
        global $post;
        $sepa_payment_gateway_data = get_post_meta( $post->ID, 'sepa_payment_gateway_data', true );
        if($sepa_payment_gateway_data) {
            echo '<div class="inside">
                    <ul>
                        <li><strong>' . __( 'Account holder:', 'sepa-payment-gateway' ) . '</strong> '.esc_attr($sepa_payment_gateway_data['account']).'</li>
                        <li><strong>' . __( 'IBAN:', 'sepa-payment-gateway' ) . '</strong> '.esc_attr($sepa_payment_gateway_data['iban']).'</li>
                        <li><strong>' . __( 'BIC:', 'sepa-payment-gateway' ) . '</strong> '.esc_attr($sepa_payment_gateway_data['bic']).'</li>
                        <li class="wide">
                            <a class="button button-primary export_sepa_xml" href="javascript:void(0)" order_id="'.esc_attr($post->ID).'">'. __('Export SEPA XML', 'sepa-payment-gateway' ) .'</a>
                        </li>
                    <ul>
                </div>';
        } else {
            echo '<p><strong>No data</strong></p>';
        }
    }

    public function export( $order_id ) {

        $order = wc_get_order( $order_id );
    
        $sepa_payment_gateway = new SEPA_PAYMENT_GATEWAY();
        $data_settings = $sepa_payment_gateway->settings;
        
        // Set the initial information
        // third parameter 'pain.008.003.02' is optional would default to 'pain.008.002.02' if not changed
        $directDebit = TransferFileFacadeFactory::createDirectDebit( $data_settings['creditor_bic'] . '-' . date( 'Ymd-His' ), $data_settings['creditor_account'], 'pain.008.003.02' );
        
        $paymentName = get_bloginfo('name').'-ID-' . date( 'Ymd-His' );
        $directDebit->addPaymentInfo(
            $paymentName,
            array(
                'id'                  => strtoupper(sanitize_title($paymentName)),
                'dueDate'             => new \DateTime( 'now + 0 days' ),
                'creditorName'        => esc_attr($data_settings['creditor_account']),
                'creditorAccountIBAN' => esc_attr($data_settings['creditor_iban']),
                'creditorAgentBIC'    => esc_attr($data_settings['creditor_bic']),
                'creditorId'          => esc_attr($data_settings['creditor_id']),
                'seqType'             => PaymentInformation::S_ONEOFF,
                'localInstrumentCode' => 'CORE' // default. optional.
            )
        );
    
        $sepa_payment_gateway_data = get_post_meta( $order_id, 'sepa_payment_gateway_data', true );
        $directDebit->addTransfer(
            $paymentName,
            array(
                'amount'                => $order->get_total(),
                'debtorIban'            => esc_attr($sepa_payment_gateway_data['iban']),
                'debtorBic'             => esc_attr($sepa_payment_gateway_data['bic']),
                'debtorName'            => esc_attr($sepa_payment_gateway_data['account']),
                'debtorMandate'         => $order_id,
                'debtorMandateSignDate' => new \DateTime( date('Y-m-d', $order->get_date_modified()) ),
                'remittanceInformation' => sprintf( __( 'Order %s', 'sepa-payment-gateway' ), $order->get_order_number() ),
            )
        );
    
        $filename = 'sape-export-order-' . md5($order_id) . '.xml';
        $check = $this->save_data_xml($filename, $directDebit->asXML());
        if($check) {
            $url_download = SEPA_PAYMENT_GATEWAY_LOG_URL.$filename;
            $code = 200;
        } else {
            $url_download = '';
            $code = 500;
        }
    
        $return = array(
            'url_download' => $url_download,
            'file_name' => $filename,
            'code' => $code,
        );
         
        echo wp_send_json($return);
    
        exit();
    }
    
    public function save_data_xml($file_name, $data) {
        file_put_contents(SEPA_PAYMENT_GATEWAY_LOG_PATH . '/' . $file_name, $data);
        return true;
    }

    public function export_sepa_xml() {
        if ( isset( $_POST['order_id'] ) && !empty( $_POST['order_id'] ) ) {
            $order_id = sanitize_key( $_POST['order_id'] );
            $this->export($order_id);
        }
        wp_die();
    }

    public function export_sepa_xml_admin_js() { ?>
        <script type="text/javascript">
            jQuery(function($){
                function download(url, filename) {
                    fetch(url)
                        .then(response => response.blob())
                        .then(blob => {
                        const link = document.createElement("a");
                        link.href = URL.createObjectURL(blob);
                        link.download = filename;
                        link.click();
                    })
                    .catch(console.error);
                }
    
                $('body').on('click', 'a.export_sepa_xml', function() {
                    var order_id = $(this).attr('order_id');
                    $.ajax({
                        type : "post", 
                        url : '<?php echo admin_url('admin-ajax.php');?>',
                        data : {
                            action: "export_sepa_xml", 
                            order_id : order_id,
                        },
                        context: this,
                        beforeSend: function(){
                            $('#sepa_payment_gateway_data').css('opacity', '0.2');
                        },
                        success: function(response) {
                            if(response.code == 200) {
                                download(response.url_download, response.file_name);
                            } else {
                                console.log('error');
                            }
    
                            $('#sepa_payment_gateway_data').css('opacity', '1');
                        }
                    })
                    return false;
                });
            });
        </script>
        <?php 
    }

    public function sepa_payment_gateway_hide($order_id) {
        $sepa_payment_gateway_data = get_post_meta( $order_id, 'sepa_payment_gateway_data', true );
        if($sepa_payment_gateway_data) {
            $sepa = [
                'account' => sanitize_text_field($sepa_payment_gateway_data['account']),
                'iban' => sanitize_text_field($sepa_payment_gateway_data['iban']),
                'bic' => sanitize_text_field($sepa_payment_gateway_data['bic']),
            ];

            $str_iban = '';
            $len_iban = strlen(sanitize_text_field($sepa_payment_gateway_data['iban'])) - 4;
            if($len_iban) {
                for ($i=0; $i < $len_iban; $i++) { $str_iban .= '*'; }
                $sepa['iban'] = str_replace(substr(sanitize_text_field($sepa_payment_gateway_data['iban']), 0, $len_iban), $str_iban, sanitize_text_field($sepa_payment_gateway_data['iban']));
            }
            
            $str_bic = '';
            $len_bic = strlen($sepa_payment_gateway_data['bic']) - 4;
            if($len_bic) {
                for ($i=0; $i < $len_bic; $i++) { $str_bic .= '*'; }
                $sepa['bic'] = str_replace(substr(sanitize_text_field($sepa_payment_gateway_data['bic']), 0, $len_bic), $str_bic, sanitize_text_field($sepa_payment_gateway_data['bic']));
            }

            return $sepa;
        }
        
    }

    public function display_sepa_info( $order_id ) {
		wc_get_template( 'my-account/view-order.php', array( 'sepa_payment_gateway_data' => $this->sepa_payment_gateway_hide($order_id) ), 'sepa-payment-gateway-for-woocommerce/', SEPA_PAYMENT_GATEWAY_PATH. '/templates/' );
	}

    public function email_display( $order, $sent_to_admin, $plain_text = null, $email = null ) {
		if ( is_a( $email, 'WC_Email_Customer_Refunded_Order' ) ) {
			return;
		}

		$order_id = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
		if ( true === $plain_text ) {
			wc_get_template( 'email/plain/sepa-info.php', array( 'sepa_payment_gateway_data' => $this->sepa_payment_gateway_hide($order_id) ), 'sepa-payment-gateway-for-woocommerce/', SEPA_PAYMENT_GATEWAY_PATH . '/templates/' );
		} else {
			wc_get_template( 'email/sepa-info.php', array( 'sepa_payment_gateway_data' => $this->sepa_payment_gateway_hide($order_id) ), 'sepa-payment-gateway-for-woocommerce/', SEPA_PAYMENT_GATEWAY_PATH . '/templates/' );
		}
	}

    public function display_sepa_thankyou($order_id) {
        $sepa_payment_gateway_data = $this->sepa_payment_gateway_hide($order_id);
        if(!empty( $sepa_payment_gateway_data)) {
           
            ?>
            <table class="shop_table shop_table_responsive my_account_sepa_payment_gateway">
                <thead>
                    <tr>
                        <th class="account-holder"><span class="nobr"><?php _e( 'Account holder', 'sepa-payment-gateway' ); ?></span></th>
                        <th class="iban"><span class="nobr"><?php _e( 'IBAN', 'sepa-payment-gateway' ); ?></span></th>
                        <th class="bic"><span class="nobr"><?php _e( 'BIC', 'sepa-payment-gateway' ); ?></span></th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="bank-sepa-info">
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
        }
    }

}
new SEPA_PAYMENT_GATEWAY_IMPLEMENTS();

