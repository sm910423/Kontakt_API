<?php

/**
 * Tag Model Class
 */
class Styler_Model extends CI_Model 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function isStylerService($stylerid, $serviceid)
	{
		$strQuery = "SELECT serviceid FROM v_servicecheck WHERE serviceid = $serviceid ".
			"AND stylerid = $stylerid";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return true;
			
		return false;
	}
	
	function isExistStyler($email)
	{
		$strQuery = "SELECT stylerid FROM v_styler WHERE email LIKE '$email'";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return true;
			
		return false;
	}
	
	function getStylerinfo($email, $stylerid = 0, $allflag = false)
	{
		$strQuery = "SELECT stylerid, email, firstname, lastname, photourl, phonenumber, birthday, paymentinfo ";
		if ($allflag)
			$strQuery .= ", password, devicetype, token ";
		$strQuery .= "FROM v_styler WHERE ";
		if ($email)
			$strQuery .= " email LIKE '$email' ";
		else if ($stylerid)
			$strQuery .= " stylerid = $stylerid ";
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->row_array());

		return array();
	}
	
	function signupStyler($email, $firstname, $lastname, $password, $phonenumber, $birthday, 
			$photourl, $token, $devicetype, $lat, $long, $location, $payinfo)
	{
		$this->db->insert("tbl_styler", array("ci_email"=>$email, "ci_firstname"=>$firstname, "ci_password"=>$password,
			"ci_lastname"=>$lastname, "ci_phonenumber"=>$phonenumber, "ci_birthday"=>$birthday, "ci_photourl"=>$photourl,
			"ci_token"=>$token, "ci_devicetype"=>$devicetype, "ci_paymentinfo"=>$payinfo, "ci_latitude"=>$lat, 
			"ci_longitude"=>$long, "ci_location"=>$location));
		
		if ($this->db->affected_rows())
			return $this->db->insert_id();
			
		return 0;
	}
	
    function logout($stylerid)
    {
        $strQuery = "UPDATE tbl_styler SET ci_loginstate = 0 ";
        $strQuery .= " WHERE cip_stylerid = $stylerid ";
        $this->db->query($strQuery);
    }
    
	function updateLoginState($stylerid, $token = null, $devicetype = -1)
	{
		$strQuery = "UPDATE tbl_styler SET ci_signindate = NOW(), ci_loginstate = 1 ";
		if ($token)
			$strQuery .= ", ci_token = '$token' ";
		
		if ($devicetype > -1)
			$strQuery .= ", ci_devicetype = $devicetype ";
		$strQuery .= " WHERE cip_stylerid = $stylerid ";
		$this->db->query($strQuery);
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

/* End of file user.php */
/* Location: ./application/models/user.php */