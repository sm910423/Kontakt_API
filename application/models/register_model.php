<?php

/**
 * Tag Model Class
 */
class Register_Model extends CI_Model 
{
	function __construct()
	{
		parent::__construct();
	}
	
	function addStylerTime($stylerid, $weekid, $time)
	{
		$this->db->insert("tbl_stylerweektime", array("cif_wstylerid"=>$stylerid, 
			"ci_weekid"=>$weekid, "ci_availabletime"=>$time));
		if ($this->db->affected_rows())
			return $this->db->insert_id();
			
		return 0;
	}
	
	function addCategory($name, $photourl)
	{
		$this->db->insert("tbl_category", array("ci_catename"=>$name, "ci_cphotourl"=>$photourl));
		if ($this->db->affected_rows())
		{
			$categoryid = $this->db->insert_id();
			$this->db->insert("tbl_service", array("cif_categoryid"=>$categoryid, "ci_sname"=>""));
			return $categoryid;
		}
			
		return 0;
	}
	
	function addStylerService($serviceid, $stylerid, $price)
	{
		$this->db->insert("tbl_stylerservice", array("cif_sserviceid"=>$serviceid, 
			"cif_sstylerid"=>$stylerid, "ci_price"=>$price));
		if ($this->db->affected_rows())
			return $this->db->insert_id();
			
		return 0;
	}
	
	function addService($name, $categoryid, $photourl)
	{
		$this->db->insert("tbl_service", array("cif_categoryid"=>$categoryid, 
			"ci_sname"=>$name, "ci_sphotourl"=>$photourl));
		if ($this->db->affected_rows())
			return $this->db->insert_id();
			
		return 0;
	}
	
	function addMaterial($name, $serviceid)
	{
		$this->db->update("tbl_service", array("ci_materialflag"=>1), array("cip_serviceid"=>$serviceid));
		$arrMat = explode(",", $name);
		foreach ($arrMat as $item)
		{
			$item = trim($item);
			$this->db->insert("tbl_material", array("ci_matname"=>$item, "cif_serviceid"=>$serviceid));
		}
		
		if ($this->db->affected_rows())
			return 1;
			
		return 0;
	}
	
	
	function addColor($name, $serviceid)
	{
		$this->db->update("tbl_service", array("ci_colorflag"=>1), array("cip_serviceid"=>$serviceid));
		$arrMat = explode(",", $name);
		foreach ($arrMat as $item)
		{
			$item = trim($item);
			$this->db->insert("tbl_color", array("ci_colname"=>$item, "cif_serviceid"=>$serviceid));
		}
		
		if ($this->db->affected_rows())
			return 1;
			
		return 0;
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