<?php

//ssl://gateway.sandbox.push.apple.com:2195
//ssl://gateway.push.apple.com:2195
$product = 0; // 0 : development, 1: product

define("SSL_URL", $product ? "ssl://gateway.push.apple.com:2195" : "ssl://gateway.sandbox.push.apple.com:2195");
define("PEM_URL", $product ? "push-apple.pem" : "push-dev.pem");

class ApplePush
{
	protected $passphrase = "admin123";
	protected $pushHandler;
	protected $url;

	public function __construct()
	{
		$this->pushHandler = null;
		$this->url = "./application/config/".PEM_URL;
	}

	public function connectApple()
	{
		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', $this->url);
		if ($this->passphrase)
		stream_context_set_option($ctx, 'ssl', 'passphrase', $this->passphrase);
		$this->pushHandler = stream_socket_client(SSL_URL, $err,
		$errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

		if (!$this->pushHandler)
		return false;

		return true;
	}

	public function sendProgressPush($progressid, $token, $message, $serviceid = 0)
	{
		if (!$this->pushHandler)
			return false;

		$body['aps'] = array(
			'alert' => $message,
			'sound' => 'default',
			'badge' => 1
		);
		
		if ($serviceid)
			$body["serviceid"] = $serviceid;
			
		$body["progressid"] = $progressid;
		// Encode the payload as JSON
		$payload = json_encode($body);
		try {
			$msg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
			fwrite($this->pushHandler, $msg, strlen($msg));
		} catch (Exception $e) {
		}
	}
	
//	public function sendCustomerPush($customerid, $token, $message, $serviceid = 0)
//	{
//		if (!$this->pushHandler)
//			return false;
//
//		$body['aps'] = array(
//			'alert' => $message,
//			'sound' => 'default',
//			'badge' => 1
//		);
//		
//		if ($serviceid)
//			$body["serviceid"] = $serviceid;
//			
//		$body["customerid"] = $customerid;
//		// Encode the payload as JSON
//		$payload = json_encode($body);
//		try {
//			$msg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
//			fwrite($this->pushHandler, $msg, strlen($msg));
//		} catch (Exception $e) {
//		}
//	}
//	
//	public function sendStylerPush($stylerid, $token, $message, $serviceid = 0)
//	{
//		if (!$this->pushHandler)
//			return false;
//
//		$body['aps'] = array(
//			'alert' => $message,
//			'sound' => 'default',
//			'badge' => 1
//		);
//		
//		if ($serviceid)
//			$body["serviceid"] = $serviceid;
//		$body["stylerid"] = $stylerid;
//		// Encode the payload as JSON
//		$payload = json_encode($body);
//		try {
//			$msg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
//			fwrite($this->pushHandler, $msg, strlen($msg));
//		} catch (Exception $e) {
//		}
//	}
	
	public function finish()
	{
		if ($this->pushHandler)
		fclose($this->pushHandler);

		$this->pushHandler = null;
	}
}
?>