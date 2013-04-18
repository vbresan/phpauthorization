<?php

class AuthorizerClient {
	
	/**
	 * @return string
	 */
	private static function getClientAddress() {
		
		$ipAddress = trim($_SERVER["REMOTE_ADDR"]);
		$hostName  = trim($_SERVER["REMOTE_HOST"]);
	
		if (strlen($hostName) != 0) {
			return $hostName;
		} else {
			return $ipAddress;
		}
	}

	/**
	 * @param string $url
	 * @return boolean
	 */
	private static function isAuthorized($url) {
		
		$doc = new DOMDocument();
  		$doc->load($url);
  		
  		$response = $doc->getElementsByTagName("AuthorizerResponse")->item(0);
  		$result   = $response->getElementsByTagName("Authorized")->item(0)->nodeValue;
  		
  		return strcmp($result, "true") == 0;
	}	
	
	/**
	 *
	 * @param string $service
	 * @param string $key
	 * @param int $credit
	 * @return boolean
	 */
	public static function hasCredit($service, $key, $credit = 1) {
		
		$address = AuthorizerClient::getClientAddress();
		
		//TODO: enter here your hostname and path to service
		$url  = "http://www.your_hostname.net/phpauthorization/?";
		$url .= "service=" . $service . "&";
		$url .= "address=" . $address . "&";
		$url .= "key="     . $key     . "&";
		$url .= "credit="  . $credit;		
		
		return AuthorizerClient::isAuthorized($url);
	}
	
	/**
	 * @return string
	 */
	public static function hasNoCreditMessage() {
		
		//TODO: enter here your custom message 
		$ret_val  = "You have reached the maximum number of queries for your IP address." . "\n";
		$ret_val .= "In case you need more queries, please contact the administrator using the " . "\n";
		$ret_val .= "form on address http://www.your_hostname.net/contact.php" ."\n";
		
		return $ret_val;
	}
}

?>