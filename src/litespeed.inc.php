<?php
/**
 * LiteSpeed Licensing
 * @author Joe Huss <detain@interserver.net>
 * @copyright 2018
 * @package MyAdmin
 * @category Licenses
 */

/**
 * @param string $ipAddress not used
 * @param string $field1 Product type. Available values: “LSWS” or “LSLB”.
 * @param string $field2 What kind of license. Available values: “1”: 1-CPU license, “2”: 2-CPU license,  “4”: 4-CPU license, “8”: 8-CPU license, “V”: VPS license, “U”: Ultra-VPS license (Available LSWS 4.2.2 and above.), If <order_product> is “LSLB”, <order_cpu> is not required.
 * @param string $period Renewal period. Available values: “monthly”, “yearly”, “owned”.
 * @param mixed $payment Payment method. Available values: “credit”: Use account credit. User can utilize “Add funds” function to pre-deposit money, which will show up as account credit.      “creditcard”: Use credit card to pay. The credit card is pre-defined in the account.  If there is available credit in the account, credit will be applied first, even when the payment method is set to “creditcard”.
 * @param mixed $cvv  (optional) Credit card security code. Try not to set this field. Only if your bank requires this (meaning that the transaction will fail without it) should you then supply this field. CVV code is not stored in the system, so if you need to set it, you have to set this field every time. Other information from your credit card will be taken from your user account.
 * @param mixed $promocode  (optional) Promotional code. If you have a pre-assigned promotional code registered to your account, then you can set it here. Promotional codes are exclusive to each client. If your account is entitled to discounts at the invoice level, you do not need a promotional code.
 * @return array array with the output result. see above for description of output.
 * 		array (
 * 			'LiteSpeed_eService' => array (
 * 				'action' => 'Order',
 * 				'license_id' => '36514',
 * 				'license_type' => 'WS_L_1',
 * 				'invoice_id' => '86300',
 * 				'result' => 'incomplete',
 * 				'message' => 'Invoice 86300 not paid. ',
 * 			),
 * 		)
 */
function activate_litespeed($ipAddress = '', $field1, $field2, $period = 'monthly', $payment = 'credit', $cvv = FALSE, $promocode = FALSE) {
	$ls = new \Detain\LiteSpeed\LiteSpeed(LITESPEED_USERNAME, LITESPEED_PASSWORD);
	$response = $ls->order($field1, $field2, $period, $payment, $cvv, $promocode);
	request_log('licenses', FALSE, __FUNCTION__, 'litespeed', 'order', [$field1, $field2, $period, $payment, $cvv, $promocode], $response);
	myadmin_log('licenses', 'info', "activate LiteSpeed ({$ipAddress}, {$field1}, {$field2}, {$period}, {$payment}, {$cvv}, {$promocode}) Response: ".json_encode($response), __LINE__, __FILE__);
	if (isset($response['LiteSpeed_eService']['serial'])) {
		myadmin_log('licenses', 'info', "Good, got LiteSpeed serial {$response['LiteSpeed_eService']['serial']}", __LINE__, __FILE__);
	} else {
		$subject = "Partial or Problematic LiteSpeed Order {$response['LiteSpeed_eService']['license_id']}";
		$body = $subject.'<br>'.nl2br(json_encode($response, JSON_PRETTY_PRINT));
		admin_mail($subject, $body, FALSE, FALSE, 'admin/licenses_error.tpl');
	}
	return $response;
}

/**
 * @param $ipAddress
 */
function deactivate_litespeed($ipAddress) {
	$ls = new \Detain\LiteSpeed\LiteSpeed(LITESPEED_USERNAME, LITESPEED_PASSWORD);
	$response = $ls->cancel(false, $ipAddress);
	request_log('licenses', FALSE, __FUNCTION__, 'litespeed', 'cancel', [false, $ipAddress], $response);
	myadmin_log('licenses', 'info', "Deactivate LiteSpeed ({$ipAddress}) Resposne: ".json_encode($response), __LINE__, __FILE__);
}
