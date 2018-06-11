<?php

/**
 * Tag Model Class
 */
class Event_Model extends CI_Model 
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
		$strQuery = "SELECT id, title, image, place, time, website FROM wp_whatson ORDER BY wp_whatson.call DESC";
		if ($limit != "-1") {
			$strQuery .= " LIMIT $limit";
		}
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->result_array());

		return array();
	}

	function getListByFeatured($limit) {
		$strQuery = "SELECT id, title, image, place, time, website FROM wp_whatson WHERE featured='1'";
		if ($limit != "-1") {
			$strQuery .= " LIMIT $limit";
		}
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->result_array());

		return array();
	}

	function getListByTime($limit) {
		$strQuery = "SELECT id, title, image, place, time, website FROM wp_whatson ORDER BY time DESC";
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
		$strQuery = "UPDATE wp_whatson SET wp_whatson.call=wp_whatson.call+1 WHERE id=$id";
		if ($this->db->query($strQuery))
			return true;

		return false;
	}

	function getDetailsByID($id) {
        $strQuery = "SELECT * FROM wp_whatson WHERE id=$id";
			
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

/* End of file event.php */
/* Location: ./application/models/event.php */