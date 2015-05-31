<?php
/** WoocommerceAtos **/

add_shortcode( 'woocommerce_atos_automatic_response', 'woocommerce_atos_automatic_response' );

function woocommerce_atos_automatic_response( $atts ) {
	$atos = new woocommerce_atos();

	if ( isset( $_POST['DATA'] ) ) {
		$transauthorised = false;

		$data = escapeshellcmd( sanitize_text_field($_POST['DATA']) );

		$message = sprintf('message=%s', $data);
		$pathfile = sprintf('pathfile=%s', $atos->pathfile);

		$path_bin_response = $atos->path_bin_response;
		$result = exec( "$path_bin_response $pathfile $message" );

		$results = explode( '!', $result );

		$response = array(
			'code'               => $results[1],
			'error'              => $results[2],
			'merchantid'         => $results[3],
			'merchantcountry'    => $results[4],
			'amount'             => $results[5],
			'transactionid'      => $results[6],
			'paymentmeans'       => $results[7],
			'transmissiondate'   => $results[8],
			'paymenttime'        => $results[9],
			'paymentdate'        => $results[10],
			'responsecode'       => $results[11],
			'paymentcertificate' => $results[12],
			'authorisationid'    => $results[13],
			'currencycode'       => $results[14],
			'cardnumber'         => $results[15],
			'cvvflag'            => $results[16],
			'cvvresponsecode'    => $results[17],
			'bankresponsecode'   => $results[18],
			'complementarycode'  => $results[19],
			'complementaryinfo'  => $results[20],
			'returncontext'      => $results[21],
			'caddie'             => $results[22],
			'receiptcomplement'  => $results[23],
			'merchantlanguage'   => $results[24],
			'language'           => $results[25],
			'customerid'         => $results[26],
			'orderid'            => $results[27],
			'customeremail'      => $results[28],
			'customeripaddress'  => $results[29],
			'captureday'         => $results[30],
			'capturemode'        => $results[31],
			'data'               => $results[32]
		);
		$order    = new WC_order( $response['orderid'] );
		if ( ( $response['responsecode'] == '' ) && ( $response['error'] == '' ) ) {

			$atos->msg['class']   = 'error';
			$atos->msg['message'] = __('Thank you for shopping with us. However, the transaction has been declined.', 'woocommerce-atos');

		} elseif ( $response['responsecode'] != 0 ) {
			$atos->msg['class']   = 'error';
			$atos->msg['message'] = __('Thank you for shopping with us. However, the transaction has been declined.', 'woocommerce-atos');

		} else {

			if ( $response['responsecode'] == 00 ) {

				$transauthorised = true;
                $order->update_status('processing');
                $order->payment_complete($response['transactionid']);
				$order->add_order_note( __('Payment accepted by the bank', 'woocommerce-atos') );

                WC()->cart->empty_cart();
			}

		}
		if ( $transauthorised == false ) {
			$order->update_status( 'failed' );
			$order->add_order_note( 'Failed' );
			$order->add_order_note( $atos->msg['message'] );
		}
	} else { // end of check post
		echo __('Precondition failed.', 'woocommerce-atos');
	}
}
