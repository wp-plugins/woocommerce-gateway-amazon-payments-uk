<?php

/**
 * A simple class that demostrates how to generate a signature with the
 * user specified paramters: Merchant ID, AWS Access Key ID and AWS Secret Key
 * ID.
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
class SignatureCalculator {

  protected static $HMAC_SHA1_ALGORITHM = "sha1";

  public function SignatureCalculator() {
  }

  /**
   * Computes RFC 2104-compliant HMAC signature.
   * 
   * @param data
   *            The data to be signed.
   * @param key
   *            The signing key, a.k.a. the AWS secret key.
   * @return The base64-encoded RFC 2104-compliant HMAC signature.
   */
  public function calculateRFC2104HMAC($data, $key) {
    // compute the hmac on input data bytes, make sure to set returning raw hmac to be true
    $rawHmac = hash_hmac(SignatureCalculator::$HMAC_SHA1_ALGORITHM, $data, $key, true);

    // base64-encode the raw hmac
    return base64_encode($rawHmac);
  }
}
?>
