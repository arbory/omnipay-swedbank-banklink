<?php
/*
 * Company: artTECH Ltd.
 * Web: http://www.arttech.lv/
 * 
 * Author: Aleksandrs Morozovs
 * E-mail: aleksandrs@arttech.lv
 * Twitter: http://twitter.com/Aleksandrs
 * Skype: AMorozovs
*/

header ('Content-type: text/html; charset=utf-8'); // Set header to UTF-8
ini_set('default_charset', 'UTF-8'); // Set default charser to UTF-8
mb_internal_encoding("utf-8"); // Set internal character encoding to UTF-8

define('POST_URL',		'https://ib.swedbank.lv/banklink/'); // Bank link
define('VK_SND_ID',		'YOURID'); // Your ID
define('RETURN_URL',	'https://example.lv/example_swedbank_return.php'); // Return url
define('KEY_LOCATION',	realpath('../').'/certs'); // Folder where are certificates


$orderInfo		= 393823; // Order information
$orderAmount	= 0.01; // Price!
$orderCurrency	= 'LVL'; // Curency

// Set variables
$VK_SERVICE		= '1002';
$VK_VERSION		= '008';
$VK_SND_ID		= VK_SND_ID;
$VK_STAMP		= $orderInfo;
$VK_AMOUNT		= $orderAmount;
$VK_CURR		= $orderCurrency;
$VK_REF			= $orderInfo;
$VK_MSG			= 'Payment message';
$VK_RETURN		= RETURN_URL;
$VK_ENCODING	= 'UTF-8';
$VL_LANG		= 'LAT';

// Prapareing data
$data = str_pad(mb_strlen($VK_SERVICE), 3, '0', STR_PAD_LEFT).$VK_SERVICE;
$data .= str_pad(mb_strlen($VK_VERSION), 3, '0', STR_PAD_LEFT).$VK_VERSION;
$data .= str_pad(mb_strlen($VK_SND_ID), 3, '0', STR_PAD_LEFT).$VK_SND_ID;
$data .= str_pad(mb_strlen($VK_STAMP), 3, '0', STR_PAD_LEFT).$VK_STAMP;
$data .= str_pad(mb_strlen($VK_AMOUNT), 3, '0', STR_PAD_LEFT).$VK_AMOUNT;
$data .= str_pad(mb_strlen($VK_CURR), 3, '0', STR_PAD_LEFT).$VK_CURR;
$data .= str_pad(mb_strlen($VK_REF), 3, '0', STR_PAD_LEFT).$VK_REF;
$data .= str_pad(mb_strlen($VK_MSG), 3, '0', STR_PAD_LEFT).$VK_MSG;

// Reading private key
$fp = fopen(KEY_LOCATION."/my_swedbank.pem", "r");
$priv_key = fread($fp, 8192);
fclose($fp);
$pkeyid = openssl_get_privatekey($priv_key);

// Compute signature
openssl_sign($data, $signature, $pkeyid);
$VK_MAC = base64_encode($signature);

// Create form
$res = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
	<head>
		<title>Checkout</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	</head>
	<body>
		<form method="POST" name="swedbank_form" id="swedbank_form" action="'.htmlspecialchars(POST_URL).'" accept-charset="UTF-8">
			<input type="hidden" name="VK_SERVICE" value="'.$VK_SERVICE.'">
			<input type="hidden" name="VK_VERSION" value="'.$VK_VERSION.'">
			<input type="hidden" name="VK_SND_ID" value="'.$VK_SND_ID.'">
			<input type="hidden" name="VK_STAMP" value="'.htmlspecialchars($VK_STAMP).'">
			<input type="hidden" name="VK_AMOUNT" value="'.$VK_AMOUNT.'">
			<input type="hidden" name="VK_CURR" value="'.$VK_CURR.'">
			<input type="hidden" name="VK_REF" value="'.$VK_REF.'">
			<input type="hidden" name="VK_MSG" value="'.htmlspecialchars($VK_MSG).'">
			<input type="hidden" name="VK_MAC" value="'.$VK_MAC.'">
			<input type="hidden" name="VK_RETURN" value="'.htmlspecialchars($VK_RETURN).'">
			<input type="hidden" name="VK_ENCODING" value="'.$VK_ENCODING.'">
			<input type="hidden" name="VL_LANG" value="'.$VL_LANG.'">
		</form>
		<script type="text/javascript" language="javascript">
			document.swedbank_form.submit();
		</script>
	</body>
</html>
';

echo $res;
?>