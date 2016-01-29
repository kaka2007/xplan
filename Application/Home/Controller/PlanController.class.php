<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class PlanController extends Controller {

    //获取计划的详细信息
    function getPlanDetail(){
        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        init_verify_token($token);

        $pid = $param['pid'];
         //$pid = 1;
        if(empty($pid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        //获取计划的基本信息
        $plan_model = new Model('plan');        
        $condition['id'] = $pid;
        $result = $plan_model->where($condition)->select();

        //获取计划的介绍信息
        $plan_intro_model = new Model('plan_intro');
        unset($condition);
        $condition['pid'] = $pid;
        $result_intro = $plan_intro_model->where($condition)->select();
   
        //查询计划对应的教练的信息(未排序)        
        // $sql_coach = "SELECT coachid,name,duration,header,type,price FROM user_info,(
        //     SELECT coachid FROM plan,plan_coach WHERE plan.id=$pid AND plan.id=plan_coach.pid 
        // ) AS t WHERE t.coachid=user_info.id";

        //查询计划对应的教练的信息(已排序)        
        $sql_coach = "SELECT coachid,name,duration,header,type,price ,score FROM user_info,(
            SELECT coachid FROM plan,plan_coach WHERE plan.id=$pid AND plan.id=plan_coach.pid AND plan_coach.status=0
        ) AS t WHERE t.coachid=user_info.id ORDER BY score DESC";

        $model_coach = new Model();
        $result_coach = $model_coach->query($sql_coach);


        $data['errno'] = 0;
        $data['coverimg'] = $result[0]['coverimg'];
        $data['title'] = $result[0]['title'];
        $data['peoplenumber'] = $result[0]['peoplenumber'];
        $data['coach_list'] = $result_coach;
        $data['intro_list'] = $result_intro;
        echo json_encode($data);
    }

    //获取我参与的计划
    function getMyPlanList(){
        $param = json_decode(file_get_contents('php://input'), true);

        $token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
        //$uid = 1584;
        if(empty($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }

 
        $model = new Model();

        //查询是否有过期的，有过期就更新
        $time=time();
        $sql = "UPDATE my_plan SET status=4 WHERE uid={$uid} AND end_time<{$time}";
        $model->execute($sql);

        $sql = "SELECT t.*, user_info.header,user_info.name,user_info.nicker FROM
            (
                SELECT DISTINCT pid,title,coverimg,type AS isfree,peoplenumber,coachid,status,begin_time,end_time from my_plan,plan where my_plan.pid = plan.id and uid=$uid ORDER BY isfree desc
            ) AS t,user_info 
            WHERE t.coachid=user_info.id";

        $result = $model->query($sql);
        $data['errno'] = 0;
        $data['plan_list'] = $result;
        echo json_encode($data);
    }

    //获取我参与的计划的总数和参与过的计划的总数
    function getMyPlanCount(){
        $param = json_decode(file_get_contents('php://input'), true);

        $token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
        // $uid = 42;
        if(empty($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $model = new Model();

        //正在参与的
        $sql = "SELECT DISTINCT uid,pid FROM my_plan WHERE uid=$uid AND status!=4";
        $result_joining = $model->query($sql);
        $joining = count($result_joining);

        //参与过的
        $sql = "SELECT DISTINCT uid,pid FROM my_plan WHERE uid=$uid AND status=4";
        $result_joined = $model->query($sql);
        $joined = count($result_joined);

        //查询用户的昵称和头像
        $sql = "SELECT nicker,header FROM user_info WHERE id=$uid";
        $result_info = $model->query($sql);

        $data['errno']      = 0;
        $data['nicker']     = $result_info[0]['nicker'];
        $data['header']     = $result_info[0]['header'];
        $data['joining']    = $joining;
        $data['joined']     = $joined;

        echo json_encode($data);
    }
    
    //返回我的体能评估
    function getMyEvaluate(){

    }

    //我的教练
    function getMyCoach(){
        $param = json_decode(file_get_contents('php://input'), true);

        $token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
         //$uid = 1976;
        if(empty($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $sql = "SELECT name,header,price,phone,type FROM user_info WHERE id IN
                    (
                        SELECT DISTINCT coachid FROM my_plan WHERE uid=$uid
                    )";
        $model = new Model();
        $result = $model->query($sql);

        $data['errno'] = 0;
        $data['coach_list'] = $result;
        echo json_encode($data);
    }

    //获取所有的计划
    function getAllPlanList(){
        $param = json_decode(file_get_contents('php://input'), true);

        $token = $param['xtoken'];
        //init_verify_token($token);

        $sql = "SELECT * FROM plan ORDER BY type DESC";
        $model = new Model();
        $result = $model->query($sql);

        $data['errno'] = 0;
        $data['plan_list'] = $result;
        echo json_encode($data);
    }

}