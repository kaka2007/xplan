<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class UserController extends Controller {

	/*正常账号登录*/
	function login(){
		header('Access-Control-Allow-Origin:*');//跨域
		header("Content-type: text/html; charset=utf-8"); 

		$param = json_decode(file_get_contents('php://input'),true);

		$username = $param['username'];
		$password = $param['password'];

		if(empty($username) || empty($password)){
			err_ret(-205,'username or password is empty','用户名或者密码不正确');
		}

		$model = new Model('user_info');

		//判断用户名和密码
		$condition['username'] = $username;
		$condition['password'] = $password;
		$result = $model->where($condition)->select();
		if(count($result) == 0){
			err_ret(-203,'username or password is incorrect','用户名或者密码不正确');
		}

		//生成token,并添加到数据库
		$token = token_generate($result[0]['id']);
		$where['id'] = $result[0]['id'];
		$save['xtoken'] = $token;
		$count = $model->where($where)->save($save);
		if($count == 0){
			err_ret(-501,'save token failed','保存token失败');
		}

		//登录成功
		$data['errno']          = 0;
		$data['xtoken']         = $token;
		$data['data']['uid'] 	= $result[0]['id'];
		$data['data']['nicker'] = $result[0]['nicker'];
		$data['data']['header'] = $result[0]['header'];
		$data['data']['name']   = $result[0]['name'];
		$data['data']['phone']  = $result[0]['phone'];
		$data['data']['gender'] = $result[0]['gender'];

		echo json_encode($data);
	}

	/*注销登录*/
	function logout(){
		header('Access-Control-Allow-Origin:*');//跨域
		header("Content-type: text/html; charset=utf-8"); 
		
		$param = json_decode(file_get_contents('php://input'),true);
		$uid = $param['uid'];
		if(empty($uid)){
			err_ret(-205,'lack of param','缺少参数');
		}

		$model = new Model('user_info');
		$condition['id'] = $uid;
		$save['xtoken'] = '000';//清空xtoken
		$result = $model->where($condition)->save($save);//返回受影响的行数
		if($result == 1){
			$data['errno'] = 0;
			$data['uid'] = $uid;
			echo json_encode($data);
		}else{
			err_ret(-206,'operator failed','失败');
		}

	
	}

	/*微信登录*/
	function weixinLogin(){
		$param = json_decode(file_get_contents('php://input'), true);
		$code = $param['code'];
		$appid = 'wx76b45a75375ace28';
		$appsectet = '542ac19d16abf289cc11eacd7f231fed';
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appid.'&secret='.$appsectet.'&code='.$code.'&grant_type=authorization_code';

		if(empty($code)){
			err_ret(-205,'lack of code param','缺少code参数');
		}

		//请求openid
		$result_param = json_decode(file_get_contents($url),true);
		$openid = $result_param['openid'];
		$access_token = $result_param['access_token'];

		//获取微信用户的信息
		$url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid;
		$weixin_user_info = json_decode(file_get_contents($url),true);
		$info['errno'] = 0;
		$info['nicker'] = $weixin_user_info['nickname'];
		$info['header'] = $weixin_user_info['headimgurl'];

		//判断数据库是否有绑定
		$model = new Model('user_info');
		$condition['weixin_openid'] = $openid;
		$result = $model->where($condition)->select();		
		if(count($result) == 0){ //没有绑定微信登录
			$data['regtime'] = time();
			$data['weixin_openid'] = $openid;
			$id = $model->add($data);
			$info['uid'] = $id;
			$info['xtoken'] = token_generate($info['uid']);
		}else{//绑定了微信登录
			$info['uid'] = $result[0]['id'];
			$info['xtoken'] = token_generate($info['uid']);
		}

		$str = json_encode($info);
		writeOneLine('/tmp/a.txt',$str);
		echo json_encode($str);
	}

	//注册
	function register(){
		$param = json_decode(file_get_contents('php://input'), true);
		
		//用户名
		$username = $param['username'];
		if(empty($username)){
			err_ret(-205,'lack of param','缺少参数');
		}

		//密码
		$password = $param['password'];
		if(empty($password)){
			err_ret(-205,'lack of param','缺少参数');
		}

		//昵称
		$nicker = $param['nicker'];
		if(empty($nicker)){
			err_ret(-205,'lack of param','缺少参数');
		}

		$gender = $param['gender'];
        if($gender != 0 && $gender != 1){
            $gender = 0;//默认 男
        }

		//头像
		$header = $param['header'];
	    if(empty($header)){
            $header = '';
        }

		//短信验证码
		$verifycode = $param['verifycode'];
		if(empty($verifycode)){
			err_ret(-306,'lack of param verfiy','验证码不能为空');
		}

		//注册时间
		$regtime = time();

		$model = new Model('user_info');

		//判断手机号是否注册过
		$data['username'] = $username;
		$result = $model->where($data)->select();
		if(count($result) > 0){
			err_ret(-305,'phone number is registered','手机号已经注册过');
		}

		//数据库插入一条记录，生成新用户
        $data['username']   = $username;
		$data['password']   = $password;
		$data['nicker']     = $nicker;
		$data['header']     = $header;
		$data['regtime']    = $regtime;
		$data['gender']     = $gender;
		$lastId = $model->add($data);
		
		if(!$lastId){	
			err_ret(-311,'register add new user failed','注册添加新用户时失败');
		}

		//删除此用户临时短信验证码
		$delete_model = new Model('verify_tmp');
		$condition['phone'] = $username;
		$condition['verifycode'] = $verifycode;
		$delete_model->where($condition)->delete(); // 删除id为最大的用户的短信验证码

		//生成用户token并保存
		$token = token_generate($lastId);
		$where['id'] = $lastId;
		$save['xtoken'] = $token;
		$count = $model->where($where)->save($save);
		if($count == 0){
			err_ret(-501,'save token failed','保存token失败');
		}

        //注册环信
        Vendor('EasemobApi.EasemobApi');
        $ease = new \Easemob();

        $result_arr = $ease->registerUser($username,$password,$nicker);
        if(isset($result_arr['error'])){
            $delete_data['username'] = $username;
            $model->where($delete_data)->delete();
            err_ret(-205,'failed registered','注册失败');
        }

		$info['errno'] = 0;
		$info['xtoken'] = $token;
		$info['data']['nicker'] = $nicker;
		$info['data']['header'] = $header;
		$info['data']['uid'] = $lastId;
		echo json_encode($info);
	}

	//判断手机号是否注册过
	function checkPhone(){
		$param = json_decode(file_get_contents('php://input'), true);
		$phone = $param['phone'];
		if(empty($phone)){
			err_ret(-304,'lack of param phone','手机号不能为空');
		}

		$model = new Model('user_info');

		$condition['username'] = $phone;
		$result = $model->where($condition)->select();	
		if(count($result) > 0){
			err_ret(-305,'phone number is registered','手机号已经注册过');
		}else{
			err_ret(0,'phone number is not registered','手机号没有注册过');
		}
	}

	//更新用户的付款后留下用户信息
	function updateUserPayInfo(){
		$param = json_decode(file_get_contents('php://input'),true);
		$token = $param['xtoken'];

		init_verify_token($token);

		$uid 		= $param['uid'];
		$name 		= $param['name'];
		$phone 		= $param['phone'];
		$gender 	= $param['gender'];

		if(!isset($uid) || !isset($name)  || !isset($phone) || !isset($gender)){
			err_ret(-205,'lack of param','缺少参数');
		}

		//更新用户表
		$model_user_info = new Model('user_info');
		$condition['name'] = $name;
		$condition['phone'] = $phone;
		$condition['gender'] = $gender;
		$result = $model_user_info->where('id='.$uid)->save($condition);
		if($result > 0){
			$data['errno'] = 0;
			$data['uid'] = $uid;
			$data['name'] = $name;
			$data['phone'] = $phone;
			echo json_encode($data);
		}else{
			//失败
			err_ret(-206,'db operator failed','数据库操作失败');
		}
	}

	//打卡,代表用户某一天的课程做完了
	function checkMark(){
		$param = json_decode(file_get_contents('php://input'),true);
		$token = $param['xtoken'];

		init_verify_token($token);

		$uid = $param['uid'];
		$pid = $param['pid'];
		$coachid = $param['coachid'];
		$courseid = $param['courseid'];
		$course_time = $param['course_time'];

		if(!isset($uid) || !isset($pid) || !isset($coachid) || !isset($courseid) || !isset($course_time)){
			err_ret(-205,'lack of param','缺少参数');
		}

		$model = new Model();
		$sql = "SELECT * FROM my_plan WHERE uid=$uid AND pid=$pid AND coachid=$coachid AND courseid=$courseid AND FROM_UNIXTIME(course_time,'%Y-%m-%d')=FROM_UNIXTIME($course_time,'%Y-%m-%d')";
		$result = $model->query($sql);
		if(count($result) > 0){
			if($result[0]['isfinished'] == 1){
				$data['errno'] = 1;
				$data['uid'] = $uid;
				$data['pid'] = $pid;
				$data['coachid']  = $coachid;
				$data['courseid'] = $courseid;
				echo json_encode($data);
				die();
			} 
		}else{//这一天没有计划
			$data['errno'] = 2;
			$data['uid'] = $uid;
			$data['pid'] = $pid;
			$data['coachid']  = $coachid;
			$data['courseid'] = $courseid;
			echo json_encode($data);
			die();
		}

		$sql = "UPDATE my_plan SET isfinished=1 
		WHERE FROM_UNIXTIME(course_time,'%Y-%m-%d')=FROM_UNIXTIME($course_time,'%Y-%m-%d') 
		AND uid=$uid AND pid=$pid AND coachid=$coachid AND courseid=$courseid";

		$model->query($sql);

		$data['errno'] = 0;
		$data['uid'] = $uid;
		$data['pid'] = $pid;
		$data['coachid']  = $coachid;
		$data['courseid'] = $courseid;

		echo json_encode($data);
	}
}
?>

