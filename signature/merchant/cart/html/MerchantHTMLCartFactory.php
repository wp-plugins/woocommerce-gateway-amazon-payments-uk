<?php
require_once($mypth."signature/common/cart/html/HTMLCartFactory.php");
/**
 * Returns a simple static cart to generate a signature from.
 *
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
class MerchantHTMLCartFactory extends HTMLCartFactory {

   /**
    * Instantiate an instance of the cart factory.
    */
   public function MerchantHTMLCartFactory()
   {
   }

   public function getCart($merchantID, $awsAccessKeyID) {
       $parameterMap = $this->getCartMap($merchantID, $awsAccessKeyID);
       return $this->getCartFromMap($merchantID, $awsAccessKeyID, $parameterMap);
   }


   /**
    * Get map representation of the cart.
    * Replace with your own cart here to try out
    * different promotions, tax, shipping, etc. 
    */
   protected function getCartMap($merchantID, $awsAccessKeyID) {
      $cart = array(
          "item_merchant_id_1" => $merchantID,
          "item_title_1" => "Red Fish From PHP HTML Sample Code",
          "item_sku_1" => "RedFish123",
          "item_description_1" => "A red fish packed in spring water. From PHP Sample Code.",
          "item_price_1" => "19.99",
          "item_quantity_1" => "1",
          "currency_code" => "GBP",
          "aws_access_key_id" => $awsAccessKeyID
      );

      // sort cart by key
      ksort($cart);

      return $cart;
   }

   /**
    * Construct a very basic cart with one item.
    */
   public function getCartHTML($merchantID, $awsAccessKeyID, $signature) {
       $cartInput = $this->getCart($merchantID, $awsAccessKeyID);
       return $this->getCartHTMLFromCartInput($merchantID, $awsAccessKeyID, $signature,
                                 $cartInput);
   }


   /**
    * Get the input to generate the hmac-sha1 signature.
    */
   public function getSignatureInput($merchantID, $awsAccessKeyID) {
      $parameterMap = $this->getCartMap($merchantID, $awsAccessKeyID);
      return $this->getSignatureInputFromMap($parameterMap);
   }
	public function getSignatureInput2($parameterMap){
		return $this->getSignatureInputFromMap($parameterMap);
	}
}
?>
