<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;


/*
 *验证模块
 */
class VerifyController extends Controller {

	//请求验证码
    function getVerifyCode(){
    	$param = json_decode(file_get_contents('php://input'),true);
    	$phone = $param['phone'];
        // $phone = '13269627267';
    	if(empty($phone)){
    		err_ret(-205,'lack of param','手机号码为空');
    	}

    	$model = new Model('verify_tmp');

    	//查询对应手机的最后一条记录
    	$sql_max = "SELECT * from verify_tmp where id = (select max(id) from verify_tmp WHERE phone = '".$phone."')";
        $result = $model->query($sql_max);

    	if(count($result) > 0){
    		$time = time() - $result[0]['gen_time'];
    		if($time <  60){ //小于1分钟
    			err_ret(-302,'too many time get verifycode','请求验证码过于频繁');
    		}
    	}

        //判断是否注册过
        $model = new Model('user_info');
        $where['username'] = $phone;
        $result_user_info = $model->where($where)->select();
        if(count($result_user_info) > 0){
            err_ret(-205,'phone number is already registered','此手机号码已经注册过');
        }

    	//生成验证码和短信模板    	
		$code = rand(1000,9999);
    	$data = array($code,'5');
   
   		//发送短信验证码
    	$serverIP = C('RL_ServerIP');
    	$serverPort = C('RL_ServerPort');
    	$softVersion = C('RL_SoftVersion');
    	$accountSid = C('RL_AccountSID');
    	$accountToken = C('RL_AccountToken');
    	$appId = C('RL_AppID');
        $SmsId = C('RL_SMS_TEMPLATE_ID');

        $rest = new \REST($serverIP,$serverPort,$softVersion);
     	$rest->setAccount($accountSid,$accountToken);
     	$rest->setAppId($appId);

     	$result = $rest->sendTemplateSMS($phone,$data,$SmsId);
     	
     	if($result->statusCode != 0) {
       	 	err_ret($result->statusCode,$result->statusMsg);
     	}else{//生成验证码，插入数据库
     		$value['phone'] = $phone;
    		$value['verifycode'] = $code;
    		$value['gen_time'] = time();
    		M("verify_tmp")->add($value);
    		http_ret(0,'verify code send success','验证码发送成功');
     	}
    }

    //判断验证码是否正确
	function checkVerfiyCode(){
		$param 	= json_decode(file_get_contents('php://input'), true);
		$phone 	= $param['phone'];
		$verfiycode = $param['verfiycode'];

		if(empty($phone)){
			err_ret(-205,'lack of param','缺少参数');
		}

		if(empty($verfiycode)){
			err_ret(-205,'lack of param','缺少参数');
		}

        //万能验证码
        if($verfiycode == '0228'){
            http_ret(0,'verify code is correct','验证码正确');
        }

		$model = new Model('verify_tmp');
		$condition['phone'] = $phone;
		$condition['verifycode'] = $verfiycode;
		$result = $model->where($condition)->select();

		if(count($result) <= 0){
			err_ret(-307,'verfiy code is incorrect','验证码不正确');
		}

		$time = time() - $result[0]['gen_time'];
		if($time > 5 * 60){
			err_ret(-308,'verify code is invalid','验证码已过期');
		}

		if($verfiycode == $result[0]['verifycode']){
			http_ret(0,'verify code is correct','验证码正确');
		}else{
			err_ret(-307,'verfiy code is incorrect','验证码不正确');
		}
	}





}
