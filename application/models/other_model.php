<?php

/**
 * Tag Model Class
 */
class Other_Model extends CI_Model 
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

	function getAbout() {
        $strQuery = "SELECT * FROM wp_aboutus WHERE id>0";
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->row_array());

		return array();
	}

	function getListByFeatured($limit) {
		$strQuery;
		if ($limit == "-1") {
            $strQuery = "SELECT id, title, content FROM wp_community WHERE featured='1'";
		} else {
            $strQuery = "SELECT id, title, image, created FROM wp_community WHERE featured='1' LIMIT $limit";
        }
			
		$query = $this->db->query($strQuery);
		if ($query->num_rows())
			return $this->convertKeyValue($query->result_array());

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

/* End of file community.php */
/* Location: ./application/models/community.php */