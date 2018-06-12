<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';
class Company extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('company_model');
	}
	
	public function list_post() {
		$kind = $this->post("kind");
		$email = $this->post("email");
		$limit = $this->post("limit");
		$sub_category_id = $this->post("sub_category_id");
		
		if (!$email || !$this->company_model->isExistUser($email)) {
			$this->sendError();
		}
		
		$result;
		if ($sub_category_id > 0) {
			$result = $this->company_model->getListBySubCategory($sub_category_id);
		} else if ($kind == "most") {
			$result = $this->company_model->getListByMost($limit);
		} else if ($kind == "featured") {
			$result = $this->company_model->getListByFeatured($limit);
		} else if ($kind == "new") {
			$result = $this->company_model->getListByTime($limit);
		}
		
		if ($result) {
			$this->sendSuccess(array("list"=>$result));
		}
		$this->sendError();
	}
	
	public function update_post() {
		$id = $this->post("id");
		$email = $this->post("email");
		
		if (!$id || !$email || !$this->company_model->isExistUser($email)) {
			$this->sendError();
		}
		
		if ($result = $this->company_model->increaseCallNumber($id)) {
			$this->sendSuccess(array("success"=>$result));
		}
		$this->sendError();
	}
	
	public function details_post() {
		$id = $this->post("id");
		$email = $this->post("email");
		
		if (!$id || !$email || !$this->company_model->isExistUser($email)) {
			$this->sendError();
		}
		
		$result = $this->company_model->getDetailsByID($id);
		
		if ($result) {
			$this->sendSuccess(array("info"=>$result));
		}
		$this->sendError();
	}

	public function categories_post() {
		$email = $this->post("email");
		
		if (!$email || !$this->company_model->isExistUser($email)) {
			$this->sendError();
		}

		$result = $this->company_model->getCategories();

		if ($result) {
			$this->sendSuccess(array("categories"=>$result));
		}
		$this->sendError();
	}

	public function subcategories_post() {
		$category_id = $this->post("category_id");
		$email = $this->post("email");
		
		if (!$category_id || !$email || !$this->company_model->isExistUser($email)) {
			$this->sendError();
		}

		$result = $this->company_model->getSubCategories($category_id);

		if ($result) {
			$this->sendSuccess(array("subcategories"=>$result));
		}
		$this->sendError();

	}
	
	private function sendSuccess($param = array()) {
		$param["status"] = 200;
		$this->response($param, 200);
	}
	
	private function sendError($errorcode = ERR_PACKET_NO_FIELD) {
		$message = "";
		$this->response(array("status"=>$errorcode, "message"=>$message), 200);
	}
}