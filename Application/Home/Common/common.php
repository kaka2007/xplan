<?php

use Think\Model;

function err_ret($errno, $errmsg, $usermsg) {
	$err = array('error'=>array('errno'=>$errno,'errmsg'=>$errmsg,'usermsg'=>$usermsg));
	$str = json_encode($err);
	die($str);	
}

function http_ret($errno,$errmsg,$usermsg){
	$http_arr = array('errno'=>$errno,'errmsg'=>$errmsg,'usermsg'=>$usermsg);
	$http_str = json_encode($http_arr);
	die($http_str);
}

//根据用户的uid生成token
function token_generate($uid){
	$str = time().$uid.rand(100000,999999);
	return md5($str);
}

//检查token
function init_verify_token($token){
	if($token == ''){
		err_ret(-205, 'lack of param xtoken','缺少xtoken参数');
	}

	$model = new Model('user_info');
	
	$condition['xtoken'] = $token;
	$result = $model->where($condition)->select();
	if(count($result) <= 0){
		err_ret(-505,'tokan is invalid','token 失效');
	}
}


//计算年数差
//参数：旧年，旧月，标年，标月
function reckonPeriod($by, $bm, $my,$mm){
	 $bd=new DateTime();
	 $bd->setDate($by, $bm,1);
	 $md=new DateTime();
	 $md->setDate($my, $mm,1);
	 $diff=$md->diff($bd);
	 return $diff->m > 6? $diff->y+1:$diff->y;
}



?>