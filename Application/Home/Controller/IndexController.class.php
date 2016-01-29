<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class IndexController extends Controller {

	public function index(){

		$this->display();		

	}

	public function addDownload(){
		//$userIp=$_SERVER['REMOTE_ADDR'];

/*		$info=M("download_nums")->select();

		//if($info[0]['ip']!=$userIp){

		if(count($info)<=0){

			$aff=M("download_nums")->add(array("num"=>1));

		}else{

		$data['num']=$info[0]['num']+1;
		//$data['ip']=$userIp;
		$aff=M("download_nums")->where("id=1")->save($data);

			}
		//}*/
		header("Location:http://123.57.135.102/plan_files/plan_offical.apk");
	}

}

