<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Api extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model('welcome_m');
	}
	public function get_news(){
		
		if(isset($_GET['category'])&&($_GET['category']!="0")){
			$type = $_GET['category'];
			$data['news'] = $this->welcome_m->get_archive($type);
		}else{
			$data['news'] = $this->welcome_m->get_all();
		}
		
		$this->load->view('api/show_news', $data);
	}
	public function show_news(){
		$id = $_GET['id'];
		$data['news'] = $this->welcome_m->show_news($id);
		$this->load->view('api/show_news', $data);
	}
}