<?php

/**
 * Tag Model Class
 */
class Service_Model extends CI_Model 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function getServiceList()
	{
		$strQuery = "SELECT serviceid, name, photourl, categoryid, catename, cphotourl, ".
			"colornames, matnames, subflag FROM v_service ORDER BY categoryid, subflag, name ";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
		{
			$result = array();
			$list = $query->result_array();
			$catitem = null;
			$arrservice = null;
			$lastcatid = -1;
			foreach ($list as $item) 
			{
				if ($item["categoryid"] != $lastcatid)
				{
					if ($catitem)
					{
						$catitem["services"] = $arrservice; 
						array_push($result, $catitem);
					}
					
					$catitem = array();
					$arrservice = array();
					$catitem["categoryid"] = $item["categoryid"];
					$catitem["name"] = $item["catename"];
					$catitem["photourl"] = $item["cphotourl"];
					$catitem["serviceid"] = $item["serviceid"];
					$lastcatid = $item["categoryid"];
				}
				
				if (!$item["subflag"])
					continue;
					
				$serviceitem = array();
				$serviceitem["serviceid"] = $item["serviceid"];
				$serviceitem["name"] = $item["name"];
				$serviceitem["photourl"] = $item["photourl"];
				$serviceitem["colors"] = $this->getColors($item["colornames"]);
				$serviceitem["materials"] = $this->getMaterials($item["matnames"]);
				array_push($arrservice, $serviceitem);
			}
			
			$catitem["services"] = $arrservice; 
			array_push($result, $catitem);
			return $result;
		}
			
		return array();
	}
	
	private function getColors($colornames)
	{
		if ($colornames)
		{
			$result = array();
			$array = explode(";", $colornames);
			foreach ($array as $item)
			{
				$tmp = explode(",", $item);
				if (count($tmp) == 2)
				{
					$colorinfo["colorid"] = $tmp[0];
					$colorinfo["name"] = $tmp[1];
					array_push($result, $colorinfo);
				}
			}
			
			return $result;
		}
		
		return array();
	}
	
	private function getMaterials($materiallist)
	{
		if ($materiallist)
		{
			$result = array();
			$array = explode(";", $materiallist);
			foreach ($array as $item)
			{
				$tmp = explode(",", $item);
				if (count($tmp) == 2)
				{
					$matinfo["materialid"] = $tmp[0];
					$matinfo["name"] = $tmp[1];
					array_push($result, $matinfo);
				}
			}
			
			return $result;
		}
		
		return array();
	}
	
	function getFeedbackList($stylerid, $pageidx, $count)
	{
		$strQuery = "SELECT feedbackid, firstname, lastname, comment, mark, ".
				"ftime, sname, sphotourl FROM v_feedback ";
		$strQuery .= "WHERE stylerid = $stylerid ORDER BY feedbackid DESC ";
		$pageidx *= $count;
		$strQuery .= "LIMIT $pageidx, $count ";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());
			
		return array();
	}
	
	function getFeedbackInfo($feedbackid)
	{
		$strQuery = "SELECT feedbackid, firstname, lastname, comment, mark, ".
				"ftime, sname, sphotourl FROM v_feedback ";
		$strQuery .= "WHERE feedbackid = $feedbackid";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
		{
			$feedbackinfo = $query->row_array();
			return $this->convertKeyValue($feedbackinfo);
		}
			
		return array();
	}
	
	function getProgressInfoForCheck($progressid)
	{
		$strQuery = "SELECT progressid, customerid, serviceid, stylerid, reservetime, state ".
			"FROM v_progressservice WHERE progressid = $progressid ";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $query->row_array();
		
		return array();
	}
	
	function leaveFeedback($progressid, $customerid, $stylerid, $rate, $comment)
	{
		$this->db->insert("tbl_feedback", array("cif_fcustomerid"=>$customerid,
			"ci_comment"=>$comment, "ci_mark"=>$rate, "cif_progressid"=>$progressid,
			"cif_fstylerid"=>$stylerid));
		if ($this->db->affected_rows())
		{
			$this->registerNotification($progressid, 120, $customerid);
			$this->sendFeedbackPush($progressid);
			return $this->db->insert_id();
		}
			
		return false;
	}
	
	private function registerNotification($progressid, $state, $customerid = 0, $stylerid = 0)
	{
		$strQuery = "SELECT cif_rcustomerid customerid, cif_rstylerid stylerid ".
			"FROM tbl_progressservice WHERE cip_progressid = $progressid";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
		{
			$creadflag = $sreadflag = 0;
			if ($customerid) $creadflag = 1;
			if ($stylerid) $sreadflag = 1;
			$info = $query->row_array();
			$this->db->insert("tbl_notification", array("cif_nprogressid"=>$progressid, "ci_nstate"=>$state,
				"cif_ncustomerid"=>$info["customerid"], "cif_nstylerid"=>$info["stylerid"], "ci_sreadflag"=>$sreadflag, 
				"ci_creadflag"=>$creadflag));
			
			if ($this->db->insert_id())
				return true;
		}
		return false;
	}
	
	function acceptProgress($progressid)
	{
		$strQuery = "UPDATE tbl_progressservice SET ci_state = 1, ci_acctime = NOW() ".
			"WHERE cip_progressid = $progressid";
		$this->db->query($strQuery);
		if ($this->db->affected_rows())
		{
			$this->registerNotification($progressid, 1, 0, 1);
			$this->sendAcceptPush($progressid);
			return true;
		}
		
		return false;
	}
	
	function declineProgress($progressid)
	{
		$strQuery = "UPDATE tbl_progressservice SET ci_state = 10, ci_comptime = NOW() ".
			"WHERE cip_progressid = $progressid";
		$this->db->query($strQuery);
		if ($this->db->affected_rows())
		{
			$this->registerNotification($progressid, 10, 0, 1);
			$this->sendDeclinePush($progressid);
			return true;
		}
		
		return false;
	}
	
	function completeProgress($progressid)
	{
		$strQuery = "UPDATE tbl_progressservice SET ci_state = 2, ci_comptime = NOW() ".
			"WHERE cip_progressid = $progressid";
		$this->db->query($strQuery);
		if ($this->db->affected_rows())
		{
			$this->registerNotification($progressid, 2, 1);
			$this->sendCompletePush($progressid);
			return true;
		}
		
		return false;
	}
	
	function cancelProgress($progressid, $stylerid, $customerid)
	{
		$updatecause = "";
		$state = 101;
		if ($stylerid)
			$updatecause = " ci_state = 101, cif_cstylerid = $stylerid ";
		else if ($customerid)
		{
			$state = 100;
			$updatecause = " ci_state = 100, cif_ccustomerid = $customerid ";
		}
		else 
			return false;
			
		$strQuery = "UPDATE tbl_progressservice SET $updatecause, ci_comptime = NOW() ".
			"WHERE cip_progressid = $progressid";
		$this->db->query($strQuery);
		if ($this->db->affected_rows())
		{
			$this->registerNotification($progressid, $state, $customerid, $stylerid);
			$this->sendCancelPush($progressid, $stylerid, $customerid);
			return true;
		}
		
		return false;
	}
	
	function getStylerList($serviceid, $weekid, $pageidx, $count)
	{
		$strQuery = "SELECT stylerid, price, email, firstname, lastname, photourl, phonenumber, ".
				"paymentinfo, location, latitude, longitude, loginstate, mark, reviewcount, ci_availabletime time ".
			"FROM v_stylerservice LEFT JOIN tbl_stylerweektime ON stylerid = cif_wstylerid ".
			"WHERE serviceid = $serviceid GROUP BY stylerid ";
				
		$strQuery .= "ORDER BY reviewcount DESC, stylerid DESC ";
		$pageidx *= $count;
		$strQuery .= "LIMIT $pageidx, $count";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertValue($query->result_array());
			
		return array();
	}
	
	function reserveService($customerid, $stylerid, $serviceid, $time, $location, 
			$lat, $long, $colorid = 0, $materialid = 0)
	{
		$params = array();
		$params["cif_rcustomerid"] = $customerid;
		$params["cif_rstylerid"] = $stylerid;
		$params["cif_rserviceid"] = $serviceid;
		$params["ci_reservetime"] = $time;
		$params["ci_raddress"] = $location;
		$params["ci_rlatitude"] = $lat;
		$params["ci_rlongitude"] = $long;
		if ($colorid) $params["cif_colorid"] = $colorid;
		if ($materialid) $params["cif_materialid"] = $materialid;
		
		$this->db->insert("tbl_progressservice", $params);
		if ($this->db->affected_rows())
		{
			$progressid = $this->db->insert_id();
			$this->registerNotification($progressid, 0, $customerid);
			$this->sendReservePush($progressid);
			return $progressid;
		}
			
		return false;
	}
	
	
	private function sendFeedbackPush($progressid)
	{
		$strQuery = "SELECT customerid, cfirstname, clastname, stylerid, sdevicetype, stoken, sloginstate, servicename, serviceid ".
			"FROM v_progresspush WHERE progressid = $progressid";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
		{
			$info = $query->row_array();
			if (!$info["stoken"] || !$info["sloginstate"])
				return;

			$message = $info["cfirstname"]." ".$info["clastname"]." left feedback to you for ".$info["servicename"];
			$this->sendPush($message, $progressid, $info["stoken"], $info["sdevicetype"]);
		}
	}

	private function sendCancelPush($progressid, $stylerid, $customerid)
	{
		if ($customerid)
		{
			$strQuery = "SELECT customerid, cfirstname, clastname, stylerid, sdevicetype, stoken, sloginstate, servicename, serviceid ".
				"FROM v_progresspush WHERE progressid = $progressid";
			$query = $this->db->query($strQuery);
			if ($query->num_rows())
			{
				$info = $query->row_array();
				if (!$info["stoken"] || !$info["sloginstate"])
					return;
	
				$message = $info["cfirstname"]." ".$info["clastname"]." canceled ".$info["servicename"];
				$this->sendPush($message, $progressid, $info["stoken"], $info["sdevicetype"]);
			}
		}
		else 
		{
			$strQuery = "SELECT customerid, cdevicetype, ctoken, cloginstate, stylerid, sfirstname, slastname, servicename, serviceid ".
				"FROM v_progresspush WHERE progressid = $progressid";
			$query = $this->db->query($strQuery);
			if ($query->num_rows())
			{
				$info = $query->row_array();
				if (!$info["ctoken"] || !$info["cloginstate"])
					return;
	
				$message = $info["sfirstname"]." ".$info["slastname"]." canceled ".$info["servicename"];
				$this->sendPush($message, $progressid, $info["ctoken"], $info["cdevicetype"]);
			}
		}
	}
	
	private function sendAcceptPush($progressid)
	{
		$strQuery = "SELECT customerid, cdevicetype, ctoken, cloginstate, stylerid, sfirstname, slastname, servicename, serviceid ".
			"FROM v_progresspush WHERE progressid = $progressid";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
		{
			$info = $query->row_array();
			if (!$info["ctoken"] || !$info["cloginstate"])
				return;

			$message = $info["sfirstname"]." ".$info["slastname"]." accepted your request for ".$info["servicename"];
			$this->sendPush($message, $progressid, $info["ctoken"], $info["cdevicetype"]);
		}
	}
	
	private function sendDeclinePush($progressid)
	{
		$strQuery = "SELECT customerid, cdevicetype, ctoken, cloginstate, stylerid, sfirstname, slastname, servicename, serviceid ".
			"FROM v_progresspush WHERE progressid = $progressid";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
		{
			$info = $query->row_array();
			if (!$info["ctoken"] || !$info["cloginstate"])
				return;

			$message = $info["sfirstname"]." ".$info["slastname"]." rejected your request for ".$info["servicename"];
			$this->sendPush($message, $progressid, $info["ctoken"], $info["cdevicetype"]);
		}
		
	}
	
	private function sendCompletePush($progressid)
	{
		$strQuery = "SELECT customerid, cfirstname, clastname, stylerid, sdevicetype, stoken, sloginstate, servicename, serviceid ".
			"FROM v_progresspush WHERE progressid = $progressid";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
		{
			$info = $query->row_array();
			if (!$info["stoken"] || !$info["sloginstate"])
				return;

			$message = $info["cfirstname"]." ".$info["clastname"]." finished ".$info["servicename"];
			$this->sendPush($message, $progressid, $info["stoken"], $info["sdevicetype"]);
		}
	}
	
	private function sendReservePush($progressid)
	{
		$strQuery = "SELECT customerid, cfirstname, clastname, stylerid, sdevicetype, stoken, sloginstate, servicename, serviceid ".
			"FROM v_progresspush WHERE progressid = $progressid";
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
		{
			$info = $query->row_array();
			if (!$info["stoken"] || !$info["sloginstate"])
				return;

			$message = $info["cfirstname"]." ".$info["clastname"]." requested ".$info["servicename"]." to you";
			$this->sendPush($message, $progressid, $info["stoken"], $info["sdevicetype"]);
		}
	}
	
	private function sendPush($message, $progressid, $token, $devicetype)
	{
		if ($devicetype == 0) //iOS
		{
			$this->load->library('ApplePush');
			$apns = new ApplePush();
			$apns->connectApple();
			$apns->sendProgressPush($progressid, $token, $message);
			$apns->finish();
		}
		else // Android
		{
			
		}
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