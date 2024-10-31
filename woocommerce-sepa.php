<?php
class SEPA_PAYMENT_GATEWAY extends WC_Payment_Gateway 
{
	// Setup our Gateway's id, description and other values
	function __construct() {		
		$this->id = "sepa_payment_gateway";
		$this->method_title = __( "SEPA Payment Gateway", 'sepa-payment-gateway' );
		$this->method_description = __( "Extends WooCommerce support SEPA Payment Gateway.", 'sepa-payment-gateway' );
		$this->title = __( "SEPA Payment Gateway", 'sepa-payment-gateway' );
		$this->icon = null;
		$this->has_fields = true;		

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Turn these settings into variables we can use
		foreach ( $this->settings as $setting_key => $value ) {
			$this->$setting_key = $value;
		}
		
		// Save settings
		if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
            add_action( 'woocommerce_update_options_payment_gateways_' . sanitize_text_field($this->id), array( &$this, 'process_admin_options' ) );
        } else {
            add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
        }	
	}

	// Build the administration fields for this specific Gateway
	public function init_form_fields() 
	{
		$this->form_fields = array(
			'enabled' => array(
				'title'		=> __( 'Aktivieren / Deaktivieren', 'sepa-payment-gateway' ),
				'label'		=> __( 'Dieses Payment Gateway aktivieren', 'sepa-payment-gateway' ),
				'id'       => sanitize_text_field($this->id).'_enabled',
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'title' => array(
				'title'		=> __( 'Titel', 'sepa-payment-gateway' ),
				'id'       => sanitize_text_field($this->id).'_title',
				'type'		=> 'text',
				'description'	=> __( 'Payment title the customer will see during the checkout process.', 'sepa-payment-gateway' ),
				'default'	=> __( 'SEPA Payment Gateway', 'sepa-payment-gateway' ),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'		=> __( 'Beschreibung', 'sepa-payment-gateway' ),
				'id'       => sanitize_text_field($this->id).'_description',
				'type'		=> 'textarea',
				'description'	=> __( 'Payment description the customer will see during the checkout process.', 'sepa-payment-gateway' ),
				'default'	=> __( '', 'sepa-payment-gateway' ),
				'desc_tip'    => true,
				'css'		=> 'max-width:350px;'
			),
			'ask_for_bic' => array(
				'title'		=> __( 'Ask for BIC', 'sepa-payment-gateway' ),
				'id'       => sanitize_text_field($this->id).'_ask_for_bic',
				'label'		=> __( 'Check this if your customers have to enter their BIC/Swift-Number. Some banks accept IBAN-only for domestic transactions.', 'sepa-payment-gateway' ),
				'type'		=> 'checkbox',
				'default'	=> 'no',
			),
			'creditor_account' => array(
				'title'		=> __( 'Creditor account holder', 'sepa-payment-gateway' ),
				'id'       => sanitize_text_field($this->id).'_creditor_account',
				'type'		=> 'text',
				'description'	=> __( 'The account holder oƒ the account that shdll receive the paymer.', 'sepa-payment-gateway' ),
				'default'	=> __( '', 'sepa-payment-gateway' ),
				'desc_tip'    => true,
			),
			'creditor_iban' => array(
				'title'		=> __( 'Creditor IBAN', 'sepa-payment-gateway' ),
				'id'       => sanitize_text_field($this->id).'_creditor_iban',
				'type'		=> 'text',
				'description'	=> __( 'The IBAN oƒ the account that shall receive the payments.', 'sepa-payment-gateway' ),
				'default'	=> __( '', 'sepa-payment-gateway' ),
				'desc_tip'    => true,
			),
			'creditor_bic' => array(
				'title'		=> __( 'Creditor BIC', 'sepa-payment-gateway' ),
				'id'       => sanitize_text_field($this->id).'_creditor_bic',
				'type'		=> 'text',
				'description'	=> __( 'The BIC oƒ the account that shall receive the payments.', 'sepa-payment-gateway' ),
				'default'	=> __( '', 'sepa-payment-gateway' ),
				'desc_tip'    => true,
			),
			'creditor_id' => array(
				'title'		=> __( 'Creditor ID', 'sepa-payment-gateway' ),
				'id'       => sanitize_text_field($this->id).'_creditor_id',
				'type'		=> 'text',
				'description'	=> __( 'The creditor ID to be used in SEPA debits.', 'sepa-payment-gateway' ),
				'default'	=> __( '', 'sepa-payment-gateway' ),
				'desc_tip'    => true,
			),
		);
	}

	public function payment_allowed_html() {
		return array(
			'label' => array(
				'for' => array()
			),
			'p' => array(
				'class' => array()
			),
			'input' => array(
				'id' => array(),
				'class' => array(),
				'type' => array(),
				'autocomplete' => array(),
				'placeholder' => array(),
				'name' => array(),
			),
			'span' => array(
				'class' => array()
			),
		);
	}

	public function payment_fields() {
		wp_register_script('public-sepa-payment-gateway', plugins_url('/public/js/wc-sepa.js', __FILE__), array('jquery'), '', true);
    	wp_enqueue_script('public-sepa-payment-gateway');   

        $fields = array();
		$required_ask_for_bic = ( esc_attr($this->ask_for_bic) == "yes" ) ? '' : '<span class="required">*</span>';
		$default_fields = array(
            'account-field' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr( $this->id ) . '-account">' . __( 'Account holder', 'sepa-payment-gateway' ) . ' <span class="required">*</span></label>
				<input id="' . esc_attr( $this->id ) . '-account" class="input-text wc-sepa-accont-holder" type="text" autocomplete="off" placeholder="John Doe" name="' . esc_attr($this->id) . '-account' . '" />
			</p>',
			'iban-field' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr( $this->id ) . '-iban">' . __( 'IBAN', 'sepa-payment-gateway' ) . ' <span class="required">*</span></label>
				<input id="' . esc_attr( $this->id ) . '-iban" class="input-text wc-sepa-iban" type="text" autocomplete="off" placeholder="•••• •••••••• ••••••••••" name="' .esc_attr($this->id) . '-iban' . '" />
			</p>',

            'bic-field' => '<p class="form-row form-row-wide">
				<label for="' . esc_attr( $this->id ) . '-bic">' . __( 'BIC', 'sepa-payment-gateway' ) . ' '.$required_ask_for_bic.'</label>
				<input id="' . esc_attr( $this->id ) . '-bic" class="input-text wc-sepa-bic" type="text" autocomplete="off" placeholder="XXXXDEYYZZZ" name="' .  esc_attr($this->id). '-bic' . '" />
			</p>',
		);
        $fields = wp_parse_args( $fields, apply_filters( 'woocommerce_sepa_payment_gateway_form_fields', $default_fields, esc_attr($this->id) ) );
		?>
		<fieldset id="<?php echo esc_attr($this->id); ?>-sepa-payment-gateway-form" class='wc-sepa-payment-gateway-form wc-payment-form'>
			<?php do_action( 'woocommerce_sepa_payment_gateway_form_start', esc_attr($this->id) ); ?>
			<?php
				foreach ( $fields as $field ) {
					echo wp_kses( $field, $this->payment_allowed_html() );
				}
			?>
			<?php do_action( 'woocommerce_sepa_payment_gateway_form_end', esc_attr($this->id) ); ?>
			<div class="clear"></div>
		</fieldset>
		<?php
	}

	public function validate_fields() {
		$valid = true; 
		if(empty($_POST['sepa_payment_gateway-account'])) {
			wc_add_notice( __('Bitte Account holder angeben.'), 'error' );
			$valid = false; 
		}
		if(empty($_POST['sepa_payment_gateway-iban'])) {
			wc_add_notice( __('Bitte IBAN angeben.'), 'error' );
			$valid = false; 
		}
		$sepa_ask_for_bic = ( $this->ask_for_bic == "yes" ) ? '1' : '0';
		if(!$sepa_ask_for_bic) {
			if(empty($_POST['sepa_payment_gateway-bic'])) {
				wc_add_notice( __('Bitte BIC angeben.'), 'error' );
				$valid = false; 
			}
		}
		return $valid; 
	}

	public function icon_allowed_html() {
		return array(
			'img' => array(
				'title' => array(),
				'src'   => array(),
				'alt'   => array(),
				'width'   => array(),
			)
		);
	}
	 
    public function get_icon() {
		$icon_html = '<img width="50" src="'.SEPA_PAYMENT_GATEWAY_URL.'/public/images/logo-sepa.svg" alt="' . esc_attr__( 'sepa-payment-gateway-logo', 'sepa-payment-gateway' ) . '" />';
		return apply_filters( 'woocommerce_gateway_icon', wp_kses( $icon_html, $this->icon_allowed_html() ), esc_attr($this->id) );
	}

	public function validate_iban($input)
    {
        $iban = strtolower($input);

        // The official min length is 5. Also prevents substringing too short input.
        if(strlen($iban) < 5) return false;

        // lengths of iban per country
        $Countries = array(
            'al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,
            'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,
            'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,
            'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,
            'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24
        );
        // subsitution scheme for letters
        $Chars = array(
            'a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,
            'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35
        );

        // Check input country code is known
        if (!isset($Countries[ substr($iban,0,2) ])) return false;

        // Check total length for given country code
        if (strlen($iban) != $Countries[ substr($iban,0,2) ]) { return false; }

        // Move first 4 chars to end
        $MovedChar = substr($iban, 4) . substr($iban,0,4);

        // Replace letters by their numeric variant
        $MovedCharArray = str_split($MovedChar);
        $NewString = "";
        foreach ($MovedCharArray as $k => $v) {
            if ( !is_numeric($MovedCharArray[$k]) ) {
                // if any other cahracter then the known letters, its bogus
                if(!isset($Chars[$MovedCharArray[$k]])) return false;
                $MovedCharArray[$k] = $Chars[$MovedCharArray[$k]];
            }
            $NewString .= $MovedCharArray[$k];
        }

        // Now we just need to validate the checksum
        // Use bcmod if available
        if (function_exists("bcmod")) { return bcmod($NewString, '97') == 1; }

        // Else use this workaround
        // http://au2.php.net/manual/en/function.bcmod.php#38474
        $x = $NewString; $y = "97";
        $take = 5; $mod = "";
        do {
            $a = (int)$mod . substr($x, 0, $take);
            $x = substr($x, $take);
            $mod = $a % $y;
        }
        while (strlen($x));
        return (int)$mod == 1;

    }

	// Submit payment and handle response
	public function process_payment( $order_id ) {
		global $woocommerce;
		$order = new WC_Order( $order_id );
        $payment_method = $order->get_payment_method();

        $sepa_enabled = ( $this->enabled == "yes" ) ? '1' : '0';
        if($sepa_enabled && $payment_method == 'sepa_payment_gateway') {
			$check_iban = $this->validate_iban(sanitize_text_field($_POST['sepa_payment_gateway-iban']));
			if($check_iban) {
				$data_sepa = [
					'account' => sanitize_text_field($_POST['sepa_payment_gateway-account']),
					'iban' => sanitize_text_field($_POST['sepa_payment_gateway-iban']),
					'bic' => sanitize_text_field($_POST['sepa_payment_gateway-bic']),
				];
				update_post_meta( $order_id, 'sepa_payment_gateway_data', $data_sepa );

				$order->update_status('on-hold', __('Update status by SEPA Payment Gateway', 'sepa-payment-gateway' )); 
			} else {
				wc_add_notice( __( 'ERROR! The given IBAN account number is not valid.', 'sepa-payment-gateway' ) ,'error');
				return false;
			}
        }

        // Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}
}