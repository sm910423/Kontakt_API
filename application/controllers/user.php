<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';
class User extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('user_model');
	}
	
	public function user_signup_post(){
		if (!$this->post("email"))
			$this->sendError();
			
		$email = $this->post("email");
		if ($this->user_model->isExistUser($email))
			$this->sendError(ERR_USER_EMAIL_DUPLICATE);			
		
		$phonenumber = $this->post("phonenumber");
		$password = md5($this->post("password"));

		$token = "";
		if ($this->post("token"))
			$token = $this->post("token");
		$devicetype = 0;
		if ($this->post("devicetype")) 		
			$devicetype = 1;
		
		$result = $this->user_model->signupUser($email, $password, $phonenumber);
		if ($result)
		{
			$info = $this->user_model->getUserinfo($email);
			$this-> sendSuccess(array("userinfo"=>$info));
		}
			
		$this->sendError();
	}
	
	public function user_update_profile_post()
	{
		if (!$this->post("userid"))
			$this->sendError();
			
		$userid = $this->post("userid");
		$arrparams = array();
		if ($this->post("fullname"))
			$arrparams["fullname"] = $this->post("fullname");
			
		if ($this->post("email"))
			$arrparams["email"] = $this->post("email");
				
		if ($this->post("phonenumber"))
			$arrparams["phonenumber"] = $this->post("phonenumber");
			
		if ($this->post("address"))
			$arrparams["address"] = $this->post("address");
			
		if ($this->post("cur_location"))
			$arrparams["cur_location"] = $this->post("cur_location");
			
		if ($this->post("cur_latitude"))
			$arrparams["cur_latitude"] = $this->post("cur_latitude");
			
		if ($this->post("cur_longitude"))
			$arrparams["cur_longitude"] = $this->post("cur_longitude");
			
		if ($this->post("maximum_distance"))
			$arrparams["maximum_distance"] = $this->post("maximum_distance");
		
		$photourl = $this->saveImage("photo", "$userid");
		if ($photourl)
			 $arrparams["photourl"] = STR_PHOTO_PREFIX.$photourl;
			 
		if (count($arrparams) < 1)
			$this->sendError();
			
		$result = $this->user_model->updateUser($userid, $arrparams);
		if ($result)
		{
			$info = $this->user_model->getUserinfo(null, $userid);
			$this->sendSuccess(array("userinfo"=>$info));
		}
			
		$this->sendError(ERR_USER_UPDATE_FAILED);
	}

	public function user_login_post()
	{
		if (!$this->post("email") || !$this->post("password"))
			$this->sendError();

		$email = $this->post("email");
		$password = md5($this->post("password"));
		$info = $this->user_model->getUserinfo($email, 0, true);

		if (!$info)
			$this->sendError(ERR_USER_NOT_FOUND);
			
		if ($info["user_pass"] != $password)
			$this->sendError(ERR_USER_INVALID_PASSWORD);
		
		unset($info["user_pass"]);
		
		$this->user_model->updateUserLoginInfo($info["ID"]);
		$this->sendSuccess(array("userinfo"=>$info));
	}
	
	public function user_detail_post()
	{
		if (!$this->post("userid"))
			$this->sendError();
			
		$userid = $this->post("userid");
		$info = $this->user_model->getUserinfo(null, $userid);
		$this->sendSuccess(array("userinfo"=>$info));
	}
	
	public function get_cuisine_list_post()
	{
		if (!$this->post("userid"))
			$this->sendError();
			
		$userid = $this->post("userid");
		$result = $this->user_model->getCuisineList();
		$this->sendSuccess(array("result"=>$result));
	}
	
	public function post_feed_post()
	{
		if (!$this->post("restaurantname") || !$this->post("posterid"))
			$this->sendError();
		
		$posterid = $this->post("posterid");
		$restaurantname = $this->post("restaurantname");
		$address = $this->post("address");
		$latitude = $this->post("latitude");
		$longitude = $this->post("longitude");
		$cuisine = $this->post("cuisine");
		$dishname = $this->post("dishname");
		$caption = $this->post("caption");
		$feedtime = $this->post("feedtime");
		
		$posturl = $this->saveImage("postphoto", "$posterid");		
		if (!$posturl) 
			$posturl = "";
		else 
			$posturl = STR_PHOTO_PREFIX.$posturl;
		
		$result = $this->user_model->postFeed($posterid, $restaurantname, $address, $latitude, $longitude, $cuisine, $dishname, $caption, $feedtime, $posturl);
		if ($result)
		{
			$info = $this->user_model->getFeedinfo($feedtime);
			$this->sendSuccess(array("feedinfo"=>$info));
		}
			
		$this->sendError();
	}
	
	public function get_feedlist_post()
	{
		if (!$this->post("userid"))
			$this->sendError();
			
		$userid = $this->post("userid");
		$result = $this->user_model->getFeedList();
		$this->sendSuccess(array("feedsList"=>$result));
	}
	
	private function processLikeFeed($likeflag)
	{
		if(!$this->post('likerid') || !$this->post('likedfeedid'))
		$this->sendError();
			
		$likerid = $this->post('likerid');
		$likedfeedid = $this->post('likedfeedid');
		if ($likeflag){
			$result = $this->user_model->likeFeed($likerid, $likedfeedid);
			if (!$result)
			$this->sendError(ERR_USER_LIKE_FEED_FAILED);
		}
		else
		$result = $this->user_model->unlikeFeed($likerid, $likedfeedid);
		$this->sendSuccess();
	}

	function like_feed_post()
	{
		$this->processLikeFeed(true);
	}

	function unlike_feed_post()
	{
		$this->processLikeFeed(false);
	}
	
	function get_likedfeed_list_post()
	{
		if (!$this->post('userid'))
		$this->sendError();
			
		$pageidx = 0;
		if ($this->post('pageidx'))
		$pageidx = $this->post('pageidx');
		$userid = $this->post('userid');
		$result = $this->user_model->getLikedFeed($userid, $pageidx);
		$this->sendSuccess(array("result"=>$result, "pageidx"=>$pageidx));
	}
	
	private function processFollow($followflag)
	{
		if(!$this->post('fuserid') || !$this->post('tuserid'))
		$this->sendError();
			
		$fuserid = $this->post('fuserid');
		$tuserid = $this->post('tuserid');
		if ($followflag){
			$result = $this->user_model->followUser($fuserid, $tuserid);
			if (!$result)
			$this->sendError(ERR_USER_FOLLOW_FAILED);
		}
		else
		$result = $this->user_model->unfollowUser($fuserid, $tuserid);
		$this->sendSuccess();
	}

	function follow_user_post()
	{
		$this->processFollow(true);
	}

	function unfollow_user_post()
	{
		$this->processFollow(false);
	}
	
	function get_following_list_post()
	{
		if (!$this->post('userid'))
		$this->sendError();
			
		$pageidx = 0;
		if ($this->post('pageidx'))
		$pageidx = $this->post('pageidx');
		$userid = $this->post('userid');
		$result = $this->user_model->getFollowing($userid, $pageidx);
		$this->sendSuccess(array("result"=>$result, "pageidx"=>$pageidx));
	}
	
	function get_follower_list_post()
	{
		if (!$this->post('userid'))
		$this->sendError();
			
		$pageidx = 0;
		if ($this->post('pageidx'))
		$pageidx = $this->post('pageidx');
		$userid = $this->post('userid');
		$result = $this->user_model->getFollower($userid, $pageidx);
		$this->sendSuccess(array("result"=>$result, "pageidx"=>$pageidx));
	}
	
	private function processBlock($blockflag)
	{
		if(!$this->post('fuserid') || !$this->post('tuserid'))
		$this->sendError();
			
		$fuserid = $this->post('fuserid');
		$tuserid = $this->post('tuserid');
		if ($blockflag){
			$result = $this->user_model->blockUser($fuserid, $tuserid);
			if (!$result)
			$this->sendError(ERR_USER_BLOCK_FAILED);
		}
		else
		$result = $this->user_model->unblockUser($fuserid, $tuserid);
		$this->sendSuccess();
	}

	function block_user_post()
	{
		$this->processBlock(true);
	}

	function unblock_user_post()
	{
		$this->processBlock(false);
	}
	
	function get_block_list_post()
	{
		if (!$this->post('userid'))
		$this->sendError();
			
		$pageidx = 0;
		if ($this->post('pageidx'))
		$pageidx = $this->post('pageidx');
		$userid = $this->post('userid');
		$result = $this->user_model->getBlockUsers($userid, $pageidx);
		$this->sendSuccess(array("result"=>$result, "pageidx"=>$pageidx));
	}
	
	public function forget_pass_post()
	{
		if (!$this->post("email"))
			$this->sendError();
			
		$email = $this->post("email");
		$customerinfo = $this->user_model->getCustomerinfo($email);
		if (!$customerinfo)
			$this->sendError(ERR_CUSTOMER_NOT_FOUND);
			
		$verifycode = $this->generateVerifyCode();
		$result = $this->user_model->setVerifyCode($customerinfo["customerid"], $verifycode);
		if ($result)
		{
			$this->sendVerifyCode($email, $verifycode);
			$this->sendSuccess(array("vcode"=>$verifycode));
		}
		
		$this->sendError(ERR_CUSTOMER_NOT_FOUND);
	}
	
	public function verify_post()
	{
		if (!$this->post("email") || !$this->post("verifycode") || !$this->post("password"))
			$this->sendError();
			
		$email = $this->post("email");
		$verifycode = $this->post("verifycode");
		$result = $this->user_model->checkVerifyCode($email, $verifycode);
		if (is_array($result))
		{
			$password = md5($this->post("password"));
			$arrparams = array();
			$arrparams["ci_password"] = md5($this->post("password"));
			$state = $this->user_model->updateCustomer($result["customerid"], $arrparams);
			$this->sendSuccess(array("customerinfo"=>$result));
		}
			
		$this->sendError($result);
	}
	
	private function sendPassword($email, $password)
	{
		$subject = 'HirerABuddy Notification';
		$message = 'Your password is ' . $password;
		$headers = 'From: hirerabuddy@example.com' . "\r\n" .
    			'Reply-To: hirerabuddy@example.com' . "\r\n" .
    			'X-Mailer: PHP/';

		mail($email, $subject, $message, $headers);
	}
	
	private function generateEmailVerifyCode()
	{
		$characters = '0123456789';
		$string = '';
		for ($p = 0; $p < 6; $p++)
			$string .= $characters[mt_rand(0, 10) % 10];
		
		return $string;
	}
	
	private function sendEmailVerifyCode($email, $code)
	{
		$subject = 'Munch Scout Notification';
		$message = 'Your email verify code is ' . $code;
		$headers = 'From: munchscount@example.com' . "\r\n" .
    			'Reply-To: munchscount@example.com' . "\r\n" .
    			'X-Mailer: PHP/';

		mail($email, $subject, $message, $headers);
	}	
	
	private function sendVerifyCode($email, $code)
	{
		$this->load->library('email');
		$config['protocol'] = 'smtp';
		$config['wordwrap'] = TRUE;
		$config['smtp_user'] = "dhernandez@wundertec.com";
		$config['smtp_pass'] = "V1RPv7YRBDk5GDIseAEsFw";
		$config['smtp_port'] = "587";
		$config['smtp_host'] = "smtp.mandrillapp.com";
			
		$message = "Your verify code is $code for Hair Flip app.";
		$this->email->initialize($config);
		$this->email->from('dhernandez@wundertec.com', 'Hair Flip');
		$this->email->to($email);
		$this->email->set_mailtype("text");
		$this->email->subject('Hair Flip Verification.');
		$this->email->message($message);
		$status = $this->email->send();
	}
	
	private function saveImage($keyword, $prefix)
	{
		if (isset( $_FILES ) && isset( $_FILES["$keyword"]))
		{
			$date = new DateTime();
			$timestamp = $date->getTimestamp();
			//$year = date("Y");
			$photo = "$prefix"."_$timestamp.jpg";
			$upload_path = $_SERVER['DOCUMENT_ROOT'].STR_UPLOAD_PATH.$photo;
			$fname=  $this->upload($keyword, $upload_path );
			if( $fname == null )
			{
				if (file_exists($upload_path))
					unlink($upload_path);
				return "";
			}
			
			return $photo;
		}
		
		return  "";
	}
	
	private function upload($upload_name, $upload_path, $overwrite=true)
	{
		if( !isset( $_FILES ) || !isset( $_FILES[$upload_name] ) )
			return null;

		$file = $_FILES[$upload_name];
		if( $file['error'] != UPLOAD_ERR_OK ){
			return null;
		}

		$upload_dir = dirname( $upload_path );
		$upload_file = basename( $upload_path );

		if ( !is_dir($upload_dir) && !mkdir($upload_dir, 0777, true)) {
			return null;
		}

		$upload_path = $upload_dir.'/'.$upload_file;
		if( is_file( $upload_path) ){
			if( $overwrite ){
				unlink( $upload_path);
			}else{
				return null;
			}
		}
		if( $file['error'] == UPLOAD_ERR_OK &&  move_uploaded_file( $file['tmp_name'], $upload_path ) ){
			return $file['name'];
		}

		return null;
	}
	
	private function generatePassword()
	{
		$characters = '0123456789';
		$string = '';
		for ($p = 0; $p < 6; $p++)
			$string .= $characters[mt_rand(0, 10) % 10];
		
		return $string;
	}
	
	private function generateVerifyCode()
	{
		$characters = '0123456789';
		$string = '';
		for ($p = 0; $p < 5; $p++)
			$string .= $characters[mt_rand(0, 10) % 10];
		
		return $string;
	}
	
	private function sendSuccess($param = array())
	{
		$param["status"] = 200;
		$this->response($param, 200);
	}

	private function sendError($errorcode = ERR_PACKET_NO_FIELD)
	{
		$message = "";
		$this->response(array("status"=>$errorcode, "message"=>$message), 200);
	}
}