<?php
/**
 * Plugin Name:         WooCommerce - Amazon UK
 * Plugin URI:          http://agilesolutionspk.com/woocommerce-gateway-amazon-payments-uk/
 * Description:         Allows you to use Amazon UK payment gateway with the WooCommerce plugin.
 * Author:              S. A. Kamran
 * Author URI:          http://www.agilesolutionspk.com
 * License: 			GPLv2 or later
 * Version:             1.0.3
 * Requires at least:   3.3
 * Tested up to:        3.4
 *
 */

 /*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/**
 * Required functions
 * */
if ( ! function_exists( 'is_woocommerce_active' ) ) require_once 'woo-includes/woo-functions.php';

/* Load Amazon UK standard checkout gateway into WooCommerce. */
add_action( 'plugins_loaded', 'add_woocommerce_amazonuk_gateway', 1 );

function add_woocommerce_amazonuk_gateway() {

	/* Don't continue if WooCommerce isn't activated. */
	register_activation_hook( __FILE__, 'woocommerce_amazonuk_activate' );

	function woocommerce_amazonuk_activate() {
		if ( !class_exists( 'WC_Payment_Gateway' ) )
			add_action( 'admin_notices', 'ukpreCheckNotice' );

		if ( !function_exists( 'curl_init' ) )
			add_action( 'admin_notices', 'ukpreCheckNoticeCURL' );
	}

	/* Deactivate automatically if WooCommerce doesn't exist. */
	add_action( 'admin_init', 'woocommerce_amazonuk_check', 0 );

	function woocommerce_amazonuk_check() {

		if ( !class_exists( 'WC_Payment_Gateway' ) ) {
			deactivate_plugins ( plugin_basename( __FILE__ ) );
			add_action( 'admin_notices', 'ukpreCheckNotice' );
		}

	}

	function ukpreCheckNotice() {
		echo __( '<div class="error"><p>WooCommerce is not installed or is inactive.  Please install/activate WooCommerce before activating the WooCommerce Amazon UK Plugin</p></div>');
	}

	function ukpreCheckNoticeCURL() {
		echo __( '<div class="error"><p>PHP CURL is required for the WooCommerce Amazon UK Plugin</p></div>' );
	}

	/* Add the gateway to Woocommerce. */
	add_filter( 'woocommerce_payment_gateways', 'add_amazonuk_gateway', 40 );

	function add_amazonuk_gateway( $methods ) {
		$methods[] = 'WC_Gateway_Amazon_UK';
		return $methods;
	}

	/* Don't continue if WooCommerce isn't activated. */
	if ( !class_exists( 'WC_Payment_Gateway' ) )
		return false;

	class WC_Gateway_Amazon_UK extends WC_Payment_Gateway {

		var $plugin_dir;

		public function __construct() {
			$this->plugin_dir   = trailingslashit( dirname( __FILE__ ) );
			$this->id           = 'amazonuk';
			$this->icon         = apply_filters( 'woocommerce_amazonuk_icon', plugin_dir_url( __FILE__ ) . 'amazon-checkout-button.gif' );
			$this->has_fields   = false;
			$this->method_title = __( 'Amazon UK');

			/* Load the form fields. */
			$this->init_form_fields();

			/* Amazonuk Configuration. */
			$this->init_settings();

			$this->enabled        = $this->settings['enabled'];
			$this->title          = $this->settings['title'];
			$this->description    = $this->settings['description'];
			$this->amazonAccessID = $this->settings['access_key'];
			$this->amazonSecretID = $this->settings['secret_key'];
			$this->amazonMerchantID = $this->settings['merchant_id'];

			$this->mode          = $this->settings['gateway_mode'];
			$liveURL              = 'https://static-eu.payments-amazon.com/cba/js/gb/PaymentWidgets.js';
			$sandboxURL           = 'https://static-eu.payments-amazon.com/cba/js/gb/sandbox/PaymentWidgets.js';

			$this->url = ( $this->mode == 'yes' ) ? $sandboxURL : $liveURL;

			add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
			add_action( 'woocommerce_receipt_amazonuk', array( &$this, 'receipt_page' ) );

			if ( !$this->is_valid_for_use() ) $this->enabled = false;
			
		}

		/**
		 * Check if this gateway is enabled and available in the user's country
		 */
		function is_valid_for_use() {

			$this->currencyCode = get_woocommerce_currency();

			return in_array( $this->currencyCode, array( 'GBP' ) );

		}

		/**
		 * Initialise Gateway Settings Form Fields
		 */
		function init_form_fields() {

			$this->form_fields = array(
				'enabled' => array(
					'title'      => __( 'Enable/Disable' ),
					'type'       => 'checkbox',
					'label'      => __( 'Enable Amazon UK'),
					'default'    => 'yes'
				),
				'title' => array(
					'title'      => __( 'Title' ),
					'type'       => 'text',
					'description'=> __( 'This controls the title which the user sees during checkout.', 'wc_amazon_sp' ),
					'default'    => __( 'Amazon UK')
				),
				'description' => array(
					'title'      => __( 'Description' ),
					'type'       => 'textarea',
					'description'=> __( 'This controls the description which the user sees during checkout.'),
					'default'    => __( "Checkout securely through Amazon UK" )
				),
				'access_key' => array(
					'title'      => __( 'Access Key ID'),
					'type'       => 'text',
					'description'=> __( 'Access Key ID obtained from your Amazon Security Credentials page.' ),
					'default'    => ''
				),
				'secret_key' => array(
					'title'      => __( 'Secret Access Key' ),
					'type'       => 'text',
					'description'=> __( 'Secret Access Key obtained from your Amazon Security Credentials page. ' ),
					'default'    => ''
				),
				'merchant_id' => array(
					'title'      => __( 'Merchant ID' ),
					'type'       => 'text',
					'description'=> __( 'Merchant ID for you Amazon seller account. ' ),
					'default'    => ''
				),
				'gateway_mode' => array(
					'title'      => __( 'AmazonUK sandbox'),
					'type'       => 'checkbox',
					'label'      => __( 'Enable AmazonUK  sandbox'),
					'default'    => 'yes',
				),
			);

		} // End init_form_fields()

		/**
		 * Admin Panel Options
		 */
		public function admin_options() {

?>
	    	<h3><?php _e( 'Amazon UK' ); ?></h3>
	    	<p><?php _e( 'Amazon UK works by sending the user to Amazon to enter their payment information.' ); ?></p>
	    	<table class="form-table">
	    	<?php
			if ( $this->is_valid_for_use() ) {

				// Generate the HTML For the settings form.
				$this->generate_settings_html();

			} else {

?>
	            		<div class="inline error"><p><strong><?php _e( 'Gateway Disabled' ); ?></strong>: <?php _e( 'AmazonUK does not support your store currency. GBP is required.'); ?></p></div>
	        		<?php

			}
?>
			</table><!--/.form-table-->
	    	<?php
		} // End admin_options()


		public function printForm( $order_id ) {
			global $woocommerce;
			
			wp_enqueue_script('amazonjquery','https://images-na.ssl-images-amazon.com/images/G/01/cba/js/jquery.js');
			wp_enqueue_script('amazonurl',$this->url);
			$woocommerce->add_inline_js( "jQuery(document).ready(function () {new CBA.Widgets.StandardCheckoutWidget({ merchantId: '".$this->amazonMerchantID."', buttonSettings: { size: 'medium', color: 'orange', background: 'light' }, orderInput: { format: 'HTML', value: 'CBACartForm' } }).render('cbaButton'); });"
			);
			$mycart=array(
						"aws_access_key_id" => $this->amazonAccessID,
						"currency_code" => "GBP"
					);
			$nn=0;
			$order = new WC_Order( $order_id );
			$itms = $order->get_items();
			
			?>
				<div id="cbaButton"></div>
				<form method="POST" action="" id="CBACartForm">
					<input type="hidden" name="aws_access_key_id" value="<?php echo $this->amazonAccessID;?>" />
					<input type="hidden" name="currency_code" value="GBP" />
					<?php foreach($itms as $itm){
							$nn++;
							$prod = new WC_Product($itm['id']);
							?>
						<input type="hidden" name="item_merchant_id_<?php echo $nn;?>" value="<?php echo $this->amazonMerchantID;?>" />
						<?php $mycart["item_merchant_id_".$nn] = $this->amazonMerchantID;?>
						<input type="hidden" name="item_price_<?php echo $nn;?>" value="<?php echo $itm['line_total']/$itm['qty'];?>" />
						<?php $mycart["item_price_".$nn] = $itm['line_total']/$itm['qty'];?>
						<input type="hidden" name="item_quantity_<?php echo $nn;?>" value="<?php echo $itm['qty'];?>" />
						<?php $mycart["item_quantity_".$nn] = $itm['qty'];?>
						<input type="hidden" name="item_sku_<?php echo $nn;?>" value="<?php echo $prod->get_sku();?>" />
						<?php $mycart["item_sku_".$nn] = $prod->get_sku();?>
						<input type="hidden" name="item_title_<?php echo $nn;?>" value="<?php echo $itm['name'];?>" />
						<?php $mycart["item_title_".$nn] = $itm['name'];?>
					<?php } //foreach ends 
							ksort($mycart);
							$mypth=plugin_dir_path(__FILE__);
							require_once('signature/merchant/cart/html/MerchantHTMLCartFactory.php');
							$cartFactory = new MerchantHTMLCartFactory();
							$cart = $cartFactory->getSignatureInput2($mycart); 
							
							require_once('signature/common/signature/SignatureCalculator.php');
							$calculator = new SignatureCalculator();
							$signature = $calculator->calculateRFC2104HMAC($cart, $this->amazonSecretID);
						?>
					<input type="hidden" name="merchant_signature" value="<?php echo $signature;?>" />
				</form>
			<?php
		}

		/**
		 * Receipt page
		 * */
		function receipt_page( $order_id ) {

			echo '<p>'.__( 'Thank you for your order, please click the button below to pay with Amazon UK').'</p>';

			echo $this->printForm( $order_id );

		}

		/**
		 * Payment fields
		 * */

		function payment_fields() {
		
			if ( ! empty( $this->description ) )
				echo wpautop( wptexturize( $this->description ) );

		}

		/**
		 * Process the payment and return the result
		 * */
		function process_payment( $order_id ) {

			$order = new WC_Order( $order_id );

			return array(
				'result'  => 'success',
				'redirect' => add_query_arg( 'order', $order->id, add_query_arg( 'key', $order->order_key, get_permalink( woocommerce_get_page_id( 'pay' ) ) ) )
			);

		}

	}

}