<?php

/**
 * Tag Model Class
 */
class Offer_Model extends CI_Model 
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

	function getListByMost($limit) {
		$strQuery;
		if ($limit == "-1") {
            $strQuery = "SELECT id, title, image FROM wp_offers ORDER BY wp_offers.call DESC";
		} else {
            $strQuery = "SELECT id, title, image, created FROM wp_offers ORDER BY wp_offers.call DESC LIMIT $limit";
        }
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->result_array());

		return array();
	}

	function getListByFeatured($limit) {
		$strQuery;
		if ($limit == "-1") {
            $strQuery = "SELECT id, title, image FROM wp_offers WHERE featured='1'";
		} else {
            $strQuery = "SELECT id, title, image, created FROM wp_offers WHERE featured='1' LIMIT $limit";
        }
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->result_array());

		return array();
	}

	function getListByTime($limit) {
		$strQuery;
		if ($limit == "-1") {
            $strQuery = "SELECT id, title, image FROM wp_offers ORDER BY created DESC";
		} else {
            $strQuery = "SELECT id, title, image, created FROM wp_offers ORDER BY created DESC LIMIT $limit";
        }
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->result_array());

		return array();
	}
	
	function increaseCallNumber($id)
	{
		$strQuery = "UPDATE wp_offers SET wp_offers.call=wp_offers.call+1 WHERE id=$id";
		if ($this->db->query($strQuery))
			return true;

		return false;
	}

	function getDetailsByID($id) {
        $strQuery = "SELECT * FROM wp_offers WHERE id=$id";
			
        $query = $this->db->query($strQuery);

        if ($query->num_rows())
			return $this->convertKeyValue($query->row_array());

		return array();
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

/* End of file offer.php */
/* Location: ./application/models/offer.php */