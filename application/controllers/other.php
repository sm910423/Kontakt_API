<?php defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'/libraries/REST_Controller.php';
class Other extends REST_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model('other_model');
	}
	
	public function about_post() {
		$email = $this->post("email");
		
		if (!$email || !$this->other_model->isExistUser($email)) {
			$this->sendError();
		}

        $result = $this->other_model->getAbout();
		
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