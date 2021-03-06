<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';
class Community extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('community_model');
	}
	
	public function list_post() {
		$kind = $this->post("kind");
		$email = $this->post("email");
		$limit = $this->post("limit");
		
		if (!$kind || !$email || !$this->community_model->isExistUser($email)) {
			$this->sendError();
		}
		
		$result;
		if ($kind == "most") {
			$result = $this->community_model->getListByMost($limit);
		} else if ($kind == "featured") {
			$result = $this->community_model->getListByFeatured($limit);
		} else if ($kind == "new") {
			$result = $this->community_model->getListByTime($limit);
		}
		
		if ($result) {
			$this->sendSuccess(array("list"=>$result));
		}
		$this->sendError();
	}
	
	public function update_post() {
		$id = $this->post("id");
		$email = $this->post("email");
		
		if (!$id || !$email || !$this->community_model->isExistUser($email)) {
			$this->sendError();
		}
		
		if ($result = $this->community_model->increaseCallNumber($id)) {
			$this->sendSuccess(array("success"=>$result));
		}
		$this->sendError();
	}
	
	public function details_post() {
		$id = $this->post("id");
		$email = $this->post("email");
		
		if (!$id || !$email || !$this->community_model->isExistUser($email)) {
			$this->sendError();
		}
		
		$result = $this->community_model->getDetailsByID($id);
		
		if ($result) {
			$this->sendSuccess(array("info"=>$result));
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