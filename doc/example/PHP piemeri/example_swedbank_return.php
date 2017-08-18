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

define('VK_REC_ID',		'YOURID'); // Your ID
define('KEY_LOCATION',	realpath('../').'/certs'); // Folder where are certificates


// When we receive VK_SERVICE 1101 it could be GET either POST
if(isset($_GET) && !empty($_GET)) {
	$method = $_GET;
} elseif(isset($_POST) && !empty($_POST)) {
	$method = $_POST;
}

function _verify($mac, $signature) {
	$cert = file_get_contents(KEY_LOCATION.'/swedbank.pem');
	$key = openssl_get_publickey($cert);
	$ok = openssl_verify($mac, $signature, $key);
	
	openssl_free_key($key);
	return $ok;
}

function checkThePurchase($orderInfo) {
	// You need to check the order and mark it as purchased, if the order ir confirmed, the return true, if not, return false
	
	return true; // now we will just return true
}

if($method['VK_REC_ID'] != VK_REC_ID) {
	echo '<!--VK_REC_ID is bad-->';
	exit;
}

$signature_ok = false;

if($method['VK_SERVICE'] == '1101') {
	$mac = str_pad(mb_strlen($method['VK_SERVICE']), 3, '0', STR_PAD_LEFT).$method['VK_SERVICE'];
	$mac .= str_pad(mb_strlen($method['VK_VERSION']), 3, '0', STR_PAD_LEFT).$method['VK_VERSION'];
	$mac .= str_pad(mb_strlen($method['VK_SND_ID']), 3, '0', STR_PAD_LEFT).$method['VK_SND_ID'];
	$mac .= str_pad(mb_strlen($method['VK_REC_ID']), 3, '0', STR_PAD_LEFT).$method['VK_REC_ID'];
	$mac .= str_pad(mb_strlen($method['VK_STAMP']), 3, '0', STR_PAD_LEFT).$method['VK_STAMP'];
	$mac .= str_pad(mb_strlen($method['VK_T_NO']), 3, '0', STR_PAD_LEFT).$method['VK_T_NO'];
	$mac .= str_pad(mb_strlen($method['VK_AMOUNT']), 3, '0', STR_PAD_LEFT).$method['VK_AMOUNT'];
	$mac .= str_pad(mb_strlen($method['VK_CURR']), 3, '0', STR_PAD_LEFT).$method['VK_CURR'];
	$mac .= str_pad(mb_strlen($method['VK_REC_ACC']), 3, '0', STR_PAD_LEFT).$method['VK_REC_ACC'];
	$mac .= str_pad(mb_strlen($method['VK_REC_NAME']), 3, '0', STR_PAD_LEFT).$method['VK_REC_NAME'];
	$mac .= str_pad(mb_strlen($method['VK_SND_ACC']), 3, '0', STR_PAD_LEFT).$method['VK_SND_ACC'];
	$mac .= str_pad(mb_strlen($method['VK_SND_NAME']), 3, '0', STR_PAD_LEFT).$method['VK_SND_NAME'];
	$mac .= str_pad(mb_strlen($method['VK_REF']), 3, '0', STR_PAD_LEFT).$method['VK_REF'];
	$mac .= str_pad(mb_strlen($method['VK_MSG']), 3, '0', STR_PAD_LEFT).$method['VK_MSG'];
	$mac .= str_pad(mb_strlen($method['VK_T_DATE']), 3, '0', STR_PAD_LEFT).$method['VK_T_DATE'];
	$signature_ok = _verify($mac, base64_decode($method['VK_MAC']));
} else if ($_POST['VK_SERVICE'] == '1901') {
	$mac = str_pad(mb_strlen($_POST['VK_SERVICE']), 3, '0', STR_PAD_LEFT).$_POST['VK_SERVICE'];
	$mac .= str_pad(mb_strlen($_POST['VK_VERSION']), 3, '0', STR_PAD_LEFT).$_POST['VK_VERSION'];
	$mac .= str_pad(mb_strlen($_POST['VK_SND_ID']), 3, '0', STR_PAD_LEFT).$_POST['VK_SND_ID'];
	$mac .= str_pad(mb_strlen($_POST['VK_REC_ID']), 3, '0', STR_PAD_LEFT).$_POST['VK_REC_ID'];
	$mac .= str_pad(mb_strlen($_POST['VK_STAMP']), 3, '0', STR_PAD_LEFT).$_POST['VK_STAMP'];
	$mac .= str_pad(mb_strlen($_POST['VK_REF']), 3, '0', STR_PAD_LEFT).$_POST['VK_REF'];
	$mac .= str_pad(mb_strlen($_POST['VK_MSG']), 3, '0', STR_PAD_LEFT).$_POST['VK_MSG'];
	$signature_ok = _verify($mac, base64_decode($_POST['VK_MAC']));
} else {
	echo '<!--bad request:' . $method['VK_SERVICE'] . '-->';
	return;
}

if($signature_ok == false || $signature_ok == 0) {
	echo '<!--signature is bad-->';
	exit;
}

if($method['VK_SERVICE'] == '1101') {
	$orderInfo = intval($method['VK_REF']);
	
	/*
	Bank allways sends a GET, but if the client clicks on the buttons to send back to the service provider web page we will receive POST.
	At first, we will receive GET first, but if the client is very fast, or there is some internet communication delay, first will be POST.
	So we need to double check!
	*/
	
	if ($method['VK_AUTO'] == 'Y') {
		// USER PURCHASED
		// Received GET
		/*
		We received a invisible (Client can't see this) GET
		We need to set thet order has paid
		*/
		
		// we need to check the order and if add is well, you need to mark it as paid
		checkThePurchase($orderInfo);
	} else {
		// USER PURCHASED
		// Received POST
		/*
		This could be only if the client clicked on the button to send back to the service provider web page.
		*/
		
		// we need to check the order and if add is well, you need to mark it as paid
		$checkPurchase = checkThePurchase($orderInfo);
		
		if($checkPurchase === true) {
			echo 'Paid, well done';
		} else {
			echo 'Something went wrong';
		}
	}
} else {
	if ($_POST['VK_AUTO'] != 'Y') {
		// USER CANCELED
		// Received POST
		echo 'You canceled the payment';
	}
}
?>