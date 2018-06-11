<?php

/**
 * Tag Model Class
 */
class Company_Model extends CI_Model 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function isExistUser($email)
	{
		$strQuery = "SELECT ID FROM wp_users WHERE user_email LIKE '$email' ";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return true;

		return false;
	}
	
	function signupUser($email, $password, $phonenumber)
	{
		$this->db->insert("wp_users", array("user_email"=>$email, "user_pass"=>$password));
		if ($this->db->affected_rows()){
			$userid = $this->db->insert_id();
			$this->db->insert("wp_usermeta", array("user_id"=>$userid, "meta_key"=> "billing_phone", "meta_value"=> $phonenumber));

			return $userid;
		}
			
		return 0;
	}
	
	function updateUser($userid, $arrparams)
	{
		$this->db->update("tbl_User", $arrparams, array("userid"=>$userid));
		if ($this->db->affected_rows())
			return true;
			
		return false;
	}

	function getListByMost($limit) {
		$strQuery = "SELECT id, title, email, phone, site, wp_listing.call FROM wp_listing ORDER BY wp_listing.call DESC";
		if ($limit != "-1") {
			$strQuery .= " LIMIT $limit";
		}
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->result_array());

		return array();
	}

	function getListByFeatured($limit) {
		$strQuery = "SELECT id, title, email, phone, site FROM wp_listing WHERE featured='1'";
		if ($limit != "-1") {
			$strQuery .= " LIMIT $limit";
		}
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->result_array());

		return array();
	}

	function getListByTime($limit) {
		$strQuery = "SELECT id, title, email, phone, site FROM wp_listing ORDER BY created DESC";
		if ($limit != "-1") {
			$strQuery .= " LIMIT $limit";
		}
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->result_array());

		return array();
	}
	
	function increaseCallNumber($id)
	{
		$strQuery = "UPDATE wp_listing SET wp_listing.call=wp_listing.call+1  WHERE id=$id";
		if ($this->db->query($strQuery))
			return true;

		return false;
	}

	function getDetailsByID($id) {
		$strQuery = "SELECT * FROM wp_listing WHERE id=$id";
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->row_array());

		return array();
	}
	
	function setEmailVerifyCode($email, $emailverifycode)
	{
		$this->db->update("tbl_User", array("emailverifycode"=>$emailverifycode), array("email"=>$email));
		if ($this->db->affected_rows())
			return true;
			
		return false;
	}
	
	function checkEmailVerifyCode($email, $emailverifycode)
	{
		$strQuery = "SELECT userid, email, fullname, photourl, phonenumber, emailverifycode, verify_state ";
		$strQuery .= "FROM tbl_User WHERE email LIKE '$email' ";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
		{
			$info = $this->convertKeyValue($query->row_array());
			if ($info["emailverifycode"] != $emailverifycode)
				return ERR_USER_INVALID_VERIFYCODE;
				
			unset($info["emailverifycode"]);
			return $info;
		}

		return ERR_CUSTOMER_NOT_FOUND;
	}
	
	function userFacebookSignup($params)
	{
		$this->db->insert("tbl_User", $params);
		if ($this->db->affected_rows())
			return $this->db->insert_id();
			
		return 0;
	}
	
	function getFacebookUserInfo($facebookid)
	{
		$strQuery = "SELECT userid, email, fullname, photourl, phonenumber ";
		$strQuery .= "FROM tbl_User WHERE facebookid = '$facebookid' ";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->row_array());

		return array();
	}
	
	function getCuisineList()
	{
		$strQuery = "SELECT * FROM tbl_Cuisine ORDER BY cuisineid ASC";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());

		return array();
	}
	
	function postFeed($posterid, $restaurantname, $address, $latitude, $longitude, $cuisine, $dishname, $caption, $feedtime, $posturl){
		$this->db->insert("tbl_Feed", array("posterid"=>$posterid, "restaurant_name"=>$restaurantname, "restaurant_address"=>$address, "latitude"=>$latitude, "longitude"=>$longitude, "cuisine"=>$cuisine, "dish_name"=>$dishname, "caption"=>$caption, "feed_time"=>$feedtime, "postlink"=>$posturl));
		if ($this->db->affected_rows())
			return $this->db->insert_id();
			
		return 0;
	}
	
	function getFeedinfo($feedtime)
	{
		$strQuery = "SELECT feedid, posterid, restaurant_name, restaurant_address, latitude, longitude, cuisine, dish_name, caption, feed_time, postlink ";
		$strQuery .= "FROM tbl_Feed WHERE ";
		if ($feedtime)
			$strQuery .= " feed_time LIKE '$feedtime' ";
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->row_array());

		return array();
	}
	
	function getFeedList()
	{
		$strQuery = "SELECT * FROM tbl_Feed as feed join (tbl_User as user) on user.userid = feed.posterid ORDER BY feed.feedid DESC";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());

		return array();
	}
	
	function likeFeed($likerid, $likedfeedid)
	{
		$this->db->insert("tbl_likefeed", array("likerid"=>$likerid, 
			"likedfeedid"=>$likedfeedid));
		if ($this->db->affected_rows())
			return $this->db->insert_id();
		
		return 0;
	}
	
	function unlikeFeed($likerid, $likedfeedid)
	{
		$this->db->delete("tbl_likefeed", array("likerid"=>$likerid, 
			"likedfeedid"=>$likedfeedid));
		if ($this->db->affected_rows())
			return 1;
		
		return 0;
	}
	
	function getLikedFeed($userid, $pageidx)
	{
		$strQuery = "SELECT * FROM tbl_likefeed as likefeed join (tbl_User as user, tbl_Feed as feed) on user.userid = feed.posterid and feed.feedid = likefeed.likedfeedid ORDER BY likefeed.likefeedid DESC";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());

		return array();
	}
	
	function followUser($fuserid, $tuserid)
	{
		$this->db->insert("tbl_followuser", array("fuserid"=>$fuserid, 
			"tuserid"=>$tuserid));
		if ($this->db->affected_rows())
			return $this->db->insert_id();
		
		return 0;
	}
	
	function unfollowUser($fuserid, $tuserid)
	{
		$this->db->delete("tbl_followuser", array("fuserid"=>$fuserid, 
			"tuserid"=>$tuserid));
		if ($this->db->affected_rows())
			return 1;
		
		return 0;
	}
	
	function getFollowing($userid, $pageidx)
	{
		$strQuery = "SELECT * FROM tbl_followuser as follow join (tbl_User as user) on user.userid = follow.tuserid WHERE fuserid = $userid ORDER BY follow.followid DESC";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());

		return array();
	}
	
	function getFollower($userid, $pageidx)
	{
		$strQuery = "SELECT * FROM tbl_followuser as follow join (tbl_User as user) on user.userid = follow.fuserid WHERE tuserid = $userid ORDER BY follow.followid DESC";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());

		return array();
	}
	
	function blockUser($fuserid, $tuserid)
	{
		$this->db->insert("tbl_blockuser", array("fuserid"=>$fuserid, 
			"tuserid"=>$tuserid));
		if ($this->db->affected_rows())
			return $this->db->insert_id();
		
		return 0;
	}
	
	function unblockUser($fuserid, $tuserid)
	{
		$this->db->delete("tbl_blockuser", array("fuserid"=>$fuserid, 
			"tuserid"=>$tuserid));
		if ($this->db->affected_rows())
			return 1;
		
		return 0;
	}
	
	function getBlockUsers($userid, $pageidx)
	{
		$strQuery = "SELECT * FROM tbl_blockuser as block join (tbl_User as user) on user.userid = block.tuserid WHERE fuserid = $userid ORDER BY block.blockid DESC";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());

		return array();
	}
	
	function setVerifyCode($bubbyid, $verifycode)
	{
		$this->db->update("tbl_customer", array("ci_verifycode"=>$verifycode), array("bubbyid"=>$bubbyid));
		if ($this->db->affected_rows())
			return true;
			
		return false;
	}
	
	function checkVerifyCode($email, $verifycode)
	{
		$strQuery = "SELECT bubbyid, email, firstname, lastname, photourl, phonenumber, birthday, verifycode, paymentinfo, favoritelocation ";
		$strQuery .= "FROM v_customer WHERE email LIKE '$email' ";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
		{
			$info = $this->convertKeyValue($query->row_array());
			if ($info["verifycode"] != $verifycode)
				return ERR_CUSTOMER_INVALID_VERIFYCODE;
				
			unset($info["verifycode"]);
			return $info;
		}

		return ERR_CUSTOMER_NOT_FOUND;
	}

	
	private function convertValue($array)
	{
		$result = array();
		if (count($array) < 1)
		return $result;

		foreach ($array as $item)
		{
			foreach ($item as $key => $value)
			{
				if (is_null($value) || $value === null)
				$item[$key] = "";
				else if (is_array($value))
				$item[$key] = $this->convertValue($value);
			}
				
			array_push($result, $item);
		}
		return $result;
	}

	private function convertKeyValue($item)
	{
		foreach ($item as $key => $value)
		{
			if (is_null($value) || $value === null)
			$item[$key] = "";
		}
			
		return $item;
	}
}

/* End of file company.php */
/* Location: ./application/models/company.php */