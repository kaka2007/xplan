<?php
namespace Home\Controller;
use Think\Controller;

class FileController extends Controller {

	//获取upyun云参数信息
    public function getImageParam(){
        $param = json_decode(file_get_contents('php://input'), true);
        // $token = $param['xtoken'];
        //init_verify_token($token);

        $filename = md5(rand(1, 999999).time()).'.jpg';

        $policy = '{"bucket":"plan-room","expiration":'.(time()+120).',"save-key":"'.$filename.'"}';
        $policy = base64_encode($policy);

        $signature = md5($policy.'&UmJfHW0m0dyCB9cm7t9+a2CIhrc=');

        $jarr=array('errno'=>0, 'data'=>array(
            'policy'=>$policy,
            'signature'=>$signature, 
            'url'=>'http://plan-room.b0.upaiyun.com/'.$filename,
            'upload_url'=>'http://v0.api.upyun.com/plan-room',
            'filename'=>$filename));
        
        $str=json_encode($jarr);
        echo $str;
    }
}
