<?php header('Content-Type: text/html; charset=utf-8');
/**
 * Copyright 2008-2011 Amazon.com, Inc., or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *    http://aws.amazon.com/apache2.0/
 *
 * or in the "license" file accompanying this file.
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 * either express or implied. See the License for the specific language governing permissions and limitations under the License.
 */
ini_set('include_path','../..');

require_once('signature/merchant/cart/html/MerchantHTMLCartFactory.php');
require_once('signature/common/cart/xml/XMLCartFactory.php');
require_once('signature/common/signature/SignatureCalculator.php');


// seller credentials - enter your own here
$merchantID="A38IKXLJN4QLCK";
$accessKeyID="AKIAI354I2GHQV73ANQQ";
$secretKeyID="7pYL+NY/wFhKqnmPCJycpqru6uI85ymCo1l81f2a";


echo "<b>--------------------- Initialization ------------------------</b><br/>\n";
echo "Initialized program with arguments:<br/>\n";
echo "Merchant ID: " . $merchantID . "</br>\n";
echo "Access Key ID: " . $accessKeyID . "</br>\n";
echo "Secret Key ID: " . $secretKeyID . "</br>\n";


/////////////////////////////////////////////////////////
// HTML cart demo
// Create the cart and the signature
/////////////////////////////////////////////////////////
$cartFactory = new MerchantHTMLCartFactory();
$calculator = new SignatureCalculator();

$cart = $cartFactory->getSignatureInput($merchantID, $accessKeyID);
$signature = $calculator->calculateRFC2104HMAC($cart, $secretKeyID);
$cartHtml = $cartFactory->getCartHTML($merchantID, $accessKeyID, $signature);

echo "<b>--------------------- HTML Cart Example ---------------------</b><br/>\n";
echo "1a. Merchant signature input: <pre>" . htmlspecialchars($cart, ENT_QUOTES) . "</pre>\n";
echo "1b. Generated signature: <pre>" . $signature . "</pre>\n";
echo "1c. Generated cart html:<br/> <pre>" . htmlspecialchars($cartHtml, ENT_QUOTES) . "</pre>\n";


/////////////////////////////////////////////////////////
// XML cart demo
// Create the cart and the signature
/////////////////////////////////////////////////////////
$cartFactory = new XMLCartFactory();
$calculator = new SignatureCalculator();

$cart = $cartFactory->getSignatureInput($merchantID, $accessKeyID);
$signature = $calculator->calculateRFC2104HMAC($cart, $secretKeyID);
$cartHtml = $cartFactory->getCartHTML($merchantID, $accessKeyID, $signature);

echo "<b>--------------------- XML Cart Example ---------------------</b><br/>\n";
echo "1a. Merchant signature input: <pre>" . htmlspecialchars($cart, ENT_QUOTES) . "</pre>\n";
echo "1b. Generated signature: <pre>" . $signature . "</pre>\n";
echo "1c. Generated cart html:<br/> <pre>" . htmlspecialchars($cartHtml, ENT_QUOTES) . "</pre>\n";
?>
