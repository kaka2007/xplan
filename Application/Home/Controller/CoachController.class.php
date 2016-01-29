<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class CoachController extends Controller {

    //获取教练的评价
    public function getComment(){
    	$param = json_decode(file_get_contents('php://input'), true);

    	$token = $param['xtoken'];
        init_verify_token($token);

     	$coachid = $param['coachid'];
         //$coachid = 53;
     	if(empty($coachid)){
     		err_ret(-205,'lack of param','缺少参数');
     	}

        $pid = $param['pid'];
         //$pid = 1;
        if(empty($pid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $t_count = C('T_COUNT');        //每次返回的条数
        $t_comment = C('T_COMMENT');    //教练评论表

     	//查询评价信息
        $model = new Model();
		$sql_comment = "SELECT comment.id,comment.coachid,comment.userid,user_info.nicker,user_info.phone,user_info.header,comment.content,comment.time  FROM comment,user_info WHERE coachid=$coachid AND comment.userid=user_info.id";
        $result_comment = $model->query($sql_comment);
        
        
        //查询教练信息
        $t_user_info = C('T_USER_INFO');
        $model_coach = new Model($t_user_info);
        unset($condition);
        $condition['id'] = $coachid;
        $condition['type'] = 1;
        $result_coach = $model_coach->where($condition)->select();

        //查看教练学员数
        $studentNums=$result_coach[0]['studentnums'];

        $sql = 'SELECT DISTINCT uid  FROM my_plan WHERE coachid='.$coachid;
        $result= M("my_plan")->query($sql);
        $nums=count($result);
       
        if($nums>=$studentNums){
            $status=1;
            $aff=M("user_info")->where("type=1 and id={$coachid}")->save(array("status"=>1));
        }else{
             $status=0;
             $aff=M("user_info")->where("type=1 and id={$coachid}")->save(array("status"=>0));
        }
                  


        //查询对应的计划的名称 
        $sql_plan = "SELECT title FROM plan WHERE id=$pid";
        $result_plan = $model->query($sql_plan);
        $plan_name = $result_plan[0]['title'];

        $data = [
        	'errno' 		=> 0,
        	'publicimg' 	=> $result_coach[0]['publicimg'],
        	'price'			=> $result_coach[0]['price'],
            'phone'         => $result_coach[0]['phone'],
        	'header'		=> $result_coach[0]['header'],
        	'type'			=> $result_coach[0]['type'],
        	'gender'		=> $result_coach[0]['gender'],
            'name'          => $result_coach[0]['name'],
            'status'        => $status,
        	'age'			=> floor(((time() - $result_coach[0]['birthday'])/(365 * 24 * 60 * 60))),//年纪
            'height'		=> $result_coach[0]['height'],
        	'weight'		=> $result_coach[0]['weight'],
        	'duration'		=> $result_coach[0]['duration'],
            'plan_name'     => $plan_name,
        	'intro'			=> $result_coach[0]['intro'],
        	'comment_list'	=> $result_comment,
            'username'      => $result_coach[0]['username'],
        ];

        echo json_encode($data);
    }

    //获取我的私人教练
    function getMyCoachList(){
        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
        // $uid = 42;
        if(empty($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $t_user_info = C('T_USER_INFO');
        $t_my_plan = C('T_MY_PLAN');
        $model = new Model();
        $sql = "SELECT user_info.id,user_info.username,user_info.name,user_info.header,user_info.nicker,user_info.type,user_info.price,user_info.phone 
                FROM $t_user_info
                WHERE id IN 
                ( SELECT coachid FROM $t_my_plan WHERE uid=$uid )";

        $result = $model->query($sql);

        $data['errno'] = 0;
        $data['coach_list'] = $result;
        
        echo json_encode($data);
    }

    //获取我的客户
    function getMyCustomer(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);

        $token = $param['xtoken'];
        init_verify_token($token);

        $coachid = $param['coachid'];
         //$coachid = 1985;
        if(empty($coachid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $now=time();
        $aff=M("my_plan")->where("coachid={$coachid} AND end_time<{$now}")->save(array("status"=>4));
        
        $sql = "SELECT DISTINCT user_info.id,user_info.username,user_info.name,user_info.header,user_info.birthday,user_info.gender,user_info.height,user_info.weight,my_plan.pid, my_plan.pay_time , my_plan.status,my_plan.iscontacted
                FROM user_info,my_plan  
                WHERE my_plan.coachid=$coachid AND my_plan.uid=user_info.id AND user_info.type=2";

        

        // $sql = "SELECT user_info.id,user_info.username,user_info.name,user_info.header,user_info.birthday,user_info.gender,user_info.height,user_info.weight
        //         FROM user_info,(SELECT DISTINCT uid FROM my_plan WHERE coachid=$coachid) as a WHERE id=a.uid;";

        // $sql = "SELECT c.id,c.username,c.name,c.header,c.birthday,c.gender,c.height,c.weight,b.pid,b.coachid
        //         FROM user_info AS c, (SELECT DISTINCT a.uid,a.pid,a.coachid FROM my_plan AS a WHERE a.coachid=53) AS b WHERE c.id=b.uid";


        $model = new Model();
        $result = $model->query($sql);

        $data['errno'] = 0;
        $data['user_list'] = $result;
        echo json_encode($data);
    }

    //获取我的客户
    function getMyCustomerEx(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);

        $token = $param['xtoken'];
        init_verify_token($token);

        $coachid = $param['coachid'];
        // $coachid = 53;
        if(empty($coachid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        //最新付款的
        $sql = "SELECT user_info.id,user_info.username,user_info.username,user_info.name,user_info.header,user_info.birthday,user_info.gender,user_info.height,user_info.weight,my_plan.course_time,my_plan.pid,my_plan.pay_time,my_plan.begin_time,my_plan.end_time,my_plan.status,my_plan.iscontacted,my_plan.isfinished
                FROM user_info,my_plan  
                WHERE my_plan.coachid=$coachid AND my_plan.uid=user_info.id AND user_info.type=2";

        $model = new Model();
        $result = $model->query($sql);

        $new_pay_list   = array();     //最新付款
        $complete_test  = array();     //完成测试
        $usual_remind   = array();     //日常督促
        // $update_plan    = array();     //更新计划

        for ($i=0; $i < count($result) ; $i++) { 
            //最新付款的
            if(isSameDay($result[$i]['pay_time'],time()) /*&& $result[$i]['status'] == 1*/){//如果是今天付款的
                $new_pay_list[] = $result[$i];
            }

            //完成测试
            if($result[$i]['status'] == 2){
                $complete_test[] = $result[$i];
            }

            //日常督促(昨天，今天，明天有计划的)
            if(timeToStr($result[$i]['course_time'])  == getYesterday() || 
                timeToStr($result[$i]['course_time']) == getToday() ||
                timeToStr($result[$i]['course_time']) == getTomorrow() ){

                $usual_remind[] = $result[$i]; 
            }

            //更新训练课程的
            // if( (getTomorrow() == timeToStr($result[$i]['course_time']) 
            //    || getAfterTomorrow() == timeToStr($result[$i]['course_time']))
            //    && (timediffDay($result[$i]['course_time'],$result[$i]['end_time']) == 1 
            //     ||( timediffDay($result[$i]['course_time'],$result[$i]['end_time']) == 2))){

            //     $update_plan[] = $result[$i];
            // }
        }

        $data['errno'] = 0;
        $data['new_pay_list']   = $new_pay_list;
        $data['complete_test']  = $complete_test;
        $data['usual_remind']   = $usual_remind;
        $data['update_plan']    = [];

        echo json_encode($data);        
    }

    //获取所有的动作
    function getActionList(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        //init_verify_token($token);

        $t_action = C('T_ACTION');
        $model = new Model($t_action);
        $result = $model->select();

        $data["errno"] = 0;
        $data["action_list"] = $result;
        echo json_encode($data);
    }

    //通过动作id获取
    function getActionInfoById(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'),true);

        $token = $param['xtoken'];
        init_verify_token($token);

        $actionid = $param['actionid'];
        // $actionid = 5;
        if(empty($actionid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $model = new Model('action');
        $condition['id'] = $actionid;
        $result = $model->where($condition)->select();
        $result[0]['errno'] = 0;

        echo json_encode($result[0]);
    }

    //获取教练收入以及每个客户的金额
    function getCoachIncome(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        init_verify_token($token);

        $coachid = $param['coachid'];
        // $coachid = 53;
        if(empty($coachid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $t_xorder = C('T_XORDER');
        $t_user_info = C('T_USER_INFO');

        //获取教练总收入
        $model = new Model();
        $sql = "SELECT DISTINCT name,gender,header,nicker,type,SUM(money)/100 AS income FROM $t_user_info,$t_xorder WHERE coachid=$coachid AND user_info.id=xorder.coachid";
        $result = $model->query($sql);

        //获取我的客户的资料以及每个客户的金额
        $sql_money = "SELECT t.uid,t.name,t.gender,t.header,t.nicker,t.birthday,t.type,t.time_end,t.status, SUM(t.money)/100 AS income  FROM 
        ( 
            SELECT xorder.uid, user_info.name,user_info.gender,user_info.header,user_info.nicker,user_info.birthday,user_info.type,xorder.money,xorder.time_end,xorder.status
            FROM $t_xorder,$t_user_info WHERE coachid=$coachid AND xorder.uid=user_info.id
        ) AS t GROUP BY t.uid; ";
        $result_money = $model->query($sql_money);

        $data['errno'] = 0;
        $data['coach_info'] = $result[0];
        $data['customer_list'] = $result_money;
        echo json_encode($data);
    }

    //获取教练的资料
    function getCoachInfo(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        init_verify_token($token);

        $coachid = $param['coachid'];
        //$coachid = 34;
        if(empty($coachid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $t_user_info = C('T_USER_INFO');
        $sql = "SELECT name,gender,header,nicker,type,publicimg FROM $t_user_info WHERE id=$coachid AND type=1";
        $model = new Model($t_user_info);        
        $result = $model->query($sql);

        $data['errno'] = 0;
        $data['info'] = $result[0];
        echo json_encode($data);
    }

    //获取用户的资料
    function getUserInfo(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
        //$coachid = 34;
        if(empty($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $t_user_info = C('T_USER_INFO');
        $sql = "SELECT username,name,gender,header,nicker,height,weight,type FROM $t_user_info WHERE id=$uid AND type=2";
        $model = new Model($t_user_info);        
        $result = $model->query($sql);

        $data['errno'] = 0;
        $data['info'] = $result[0];
        echo json_encode($data);
    }





    //根据客户 id,coachid,pid获取计划的信息
    function getUserPlanInfoById(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        init_verify_token($token);

        $coachid = $param['coachid'];
        $uid = $param['uid'];
        $pid = $param['pid'];
/*        $coachid=34;
        $uid=1584;
        $pid=1;*/

        if(empty($coachid) || empty($uid) || empty($pid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $model = new Model();
        $sql = "SELECT a.username,a.name,a.gender,a.header,nicker,a.height,a.weight,a.phone,a.city,a.birthday,b.pay_time,b.status,b.iscontacted,b.isfinished,b.tips 
                FROM user_info AS a,my_plan AS b 
                WHERE a.id=b.uid AND b.uid=$uid AND b.pid=$pid AND b.coachid=$coachid";
        $result = $model->query($sql);

        $sql="SELECT time FROM evaluate WHERE uid={$uid} ORDER BY time DESC LIMIT 0,1";
        $einfo=M()->query($sql);

        if(count($einfo)){
        $time=$einfo[0]['time'];
        $time=date("Y/m/d H:i",$time);
        $now=time();
        $birth=$result[0]['birthday'];
        $result[0]['time']=$time;
        $result[0]['birthday'] = floor(abs(($now-$birth)/(60*60*24*365)));
        }

        $result[0]['errno'] = 0;
        echo json_encode($result[0]);
    }

    //更新是否联系过
    function updateContactInfo(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
        $pid = $param['pid'];
        $coachid = $param['coachid'];
        $iscontacted = $param['iscontacted'];//0 1

        $today_time = time();

        // $uid = 42;
        // $pid = 1;
        // $coachid = 34;
        // $iscontacted = 1;

        if(empty($coachid) || empty($uid) || empty($pid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $model = new Model('my_plan');
        $condition['uid'] = $uid;
        $condition['pid'] = $pid;
        $condition['coachid'] = $coachid;

        $save['iscontacted'] = $iscontacted;
        $count = $model->where($condition)->save($save);

        $data['errno'] = 0;
        $data['uid'] = $uid;
        $data['pid'] = $pid;
        $data['coachid'] = $coachid;
        $data['iscontacted'] = $iscontacted;
        $data['count'] = $count;    //受影响的行数

        echo json_encode($data);
    }


    //根据用户名获取用户的基本信息
    function getBaseInfoByPhone(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        init_verify_token($token);

        $username = $param['username'];
        if(empty($username)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $model = new Model();
        $sql = "SELECT id AS uid, username,name,gender,header,nicker,height,weight,birthday 
            FROM user_info WHERE username=$username";

        $result = $model->query($sql);

        echo json_encode($result[0]);
    }

    //通过一个用户列表获取列表中用户的信息
    function getUserInfoByUserList(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);

        $token = $param['xtoken'];
        //init_verify_token($token);

        $userList = $param['user_list'];
        $str = "(".implode(',',$userList).")";
        if(empty($userList)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $sql = "SELECT id AS uid, username,name,gender,header,nicker,height,weight,birthday 
            FROM user_info WHERE username in ".$str;
            
        $model = new Model();
        $result = $model->query($sql);

        $data['errno'] = 0;
        $data['user_list'] = $result;

        echo json_encode($data);
    }

    function getEvaluateInfo(){
    header('Access-Control-Allow-Origin:*');//跨域
    header("Content-type: text/html; charset=utf-8"); 

    $param = json_decode(file_get_contents('php://input'), true);
        
    $token = $param['xtoken'];
    init_verify_token($token);

    $uid=$param['uid'];
    //$uid=38;
    if(empty($uid)){

        err_ret(-205,'lack of param','缺少参数');

        }
    $sql="SELECT * FROM evaluate WHERE uid={$uid} ORDER BY time DESC LIMIT 0,1";
    $result=M()->query($sql);

    $data['errno'] = 0;
    $data['evaluateInfo'] = $result[0];
    echo json_encode($data);
    }

}
