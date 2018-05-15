<?php

/**
 * Tag Model Class
 */
class Notification_Model extends CI_Model 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function getScheduleDetail($progressid, $stylerid, $customerid)
	{
		$strQuery = "SELECT progressid, customerid, serviceid, stylerid, servicedate, servicetime, address, latitude, longitude, ".
				"state, cemail, cfirstname, clastname, cphonenumber, cbirthday, cphotourl, cpaymentinfo, cloginstate, ".
				"semail, sfirstname, slastname, sphonenumber, sbirthday, sphotourl, spaymentinfo, sloginstate, ".
				"colname, matname, servicename, servicephotourl, price ".
			"FROM v_progressservice WHERE progressid = $progressid ";
		if ($stylerid)
			$strQuery .= "AND stylerid = $stylerid ";
		else if ($customerid)
			$strQuery .= "AND customerid = $customerid ";
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->row_array());
			
		return null;
	}
	
	function getCustomerScheduleList($customerid, $pageidx, $count)
	{
		$strQuery = "SELECT progressid, stylerid, sfirstname, slastname, sphotourl, state, servicename, servicedate, servicetime ".
			"FROM v_progressservice WHERE customerid = $customerid ";
		$strQuery .= "ORDER BY reservetime DESC ";
		$pageidx *= $count;
		$strQuery .= "LIMIT $pageidx, $count";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());
			
		return array();
	}
	
	function getStylerScheduleList($stylerid, $pageidx, $count)
	{
		$strQuery = "SELECT progressid, customerid, cfirstname, clastname, cphotourl, state, servicename, servicedate, servicetime ".
			"FROM v_progressservice WHERE stylerid = $stylerid ";
		$strQuery .= "ORDER BY reservetime DESC ";
		$pageidx *= $count;
		$strQuery .= "LIMIT $pageidx, $count";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());
			
		return array();
	}
	
	function getCustomerNotificationList($customerid, $pageidx, $count)
	{
		$strQuery = "SELECT notifid, progressid, nstate state, creadflag readflag, stylerid, sfirstname, servicedate, servicetime, ".
			"slastname, sphotourl, servicename ".
			"FROM v_notification WHERE customerid = $customerid ";
			
		$strQuery .= "ORDER BY notifid DESC ";
		$pageidx *= $count;
		$strQuery .= "LIMIT $pageidx, $count";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());
			
		return array();
	}
	
	function getStylerNotificationList($styler, $pageidx, $count)
	{
		$strQuery = "SELECT notifid, progressid, nstate state, sreadflag readflag, customerid, cfirstname, servicedate, servicetime, ".
			"clastname, cphotourl, servicename ".
			"FROM v_notification WHERE stylerid = $styler ";
		$strQuery .= "ORDER BY notifid DESC ";
		$pageidx *= $count;
		$strQuery .= "LIMIT $pageidx, $count";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());
			
		return array();
	}
	
	function readCustomerNotification($notifid, $customerid)
	{
		$this->db->update("tbl_notification", array("ci_creadflag"=>1), 
			array("cip_notifid"=>$notifid, "cif_ncustomerid"=>$customerid));
		if ($this->db->affected_rows())
			return true;
			
		return false;
	}
	
	function readStylerNotification($notifid, $stylerid)
	{
		$this->db->update("tbl_notification", array("ci_sreadflag"=>1), 
			array("cip_notifid"=>$notifid, "cif_nstylerid"=>$stylerid));
		if ($this->db->affected_rows())
			return true;
			
		return false;
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