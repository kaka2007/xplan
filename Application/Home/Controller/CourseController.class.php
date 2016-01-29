<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class CourseController extends Controller {

    //设置某一天的课程
    function addOneDayCourse(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 
/*
{
    "xtoken": "35dsakfsdjfcvjdsajfkdsf234",
    "coachid": 23,
    "uid": 333,
    "pid": 11,
    "time": "3825843",
    "action_list": [
        {
            "actionid": 34,
            "group": 3,
            "count": 20,
            "order": 1,
            "type": 1,
            "duration": 34
        },
        {
            "actionid": 34,
            "group": 3,
            "count": 20,
            "order": 1,
            "type": 2,
            "duration": 54
        }
    ]
}
*/


       $param = json_decode(file_get_contents('php://input'), true);

//         $str = '{
//     "xtoken": "35dsakfsdjfcvjdsajfkdsf234",
//     "coachid": 34,
//     "uid": 76,
//     "pid": 2,
//     "time": "1448255875",
//     "action_list": [
//         {
//             "actionid": 34,
//             "group": 3,
//             "count": 200,
//             "order": 1,
//             "type": 1,
//             "duration": 20
//         },
//         {
//             "actionid": 44,
//             "group": 3,
//             "count": 150,
//             "order": 1,
//             "type": 1,
//             "duration": 30
//         }
//     ]
// }';
        // $param = json_decode($str, true);



        $token = $param['xtoken'];
        init_verify_token($token);

        $coachid = $param['coachid'];
        // $coachid = 34;
        if(!isset($coachid)){
        	err_ret(-205,'lack of param','缺少参数');
        }

        $uid = $param['uid'];
        // $uid = 42;
        if(!isset($uid)){
        	err_ret(-205,'lack of param','缺少参数');
        }

        $pid = $param['pid'];
        // $pid = 1;
        if(!isset($pid)){
        	err_ret(-205,'lack of param','缺少参数');
        }

        //时间有可能是未来的某天的课程
        $time = $param['time'];
        // $time = 1447257600;
        if(!isset($time)){
            err_ret(-205,'lack of param','缺少参数');
        }

        //先查询数据库中是否购买了课程
        $model_my_plan = new Model('my_plan');
        $condition['uid'] = $uid;
        $condition['pid'] = $pid;
        $condition['coachid'] = $coachid;
        $result = $model_my_plan->where($condition)->select();
        if(count($result) <= 0){
            err_ret(-206,'user has no buy plan','客户没有购买计划');
        }

        $model_course_record = new Model('course_record');
        $next_courseid = $model_course_record->max('courseid') + 1;

        //再查询用户在这一天是否有课程
        $sql = "SELECT * FROM my_plan WHERE FROM_UNIXTIME(course_time,'%Y-%m-%d')=FROM_UNIXTIME($time,'%Y-%m-%d') AND uid=$uid AND pid=$pid AND coachid=$coachid";
        $model = new Model();
        $result = $model->query($sql);

        

        if(count($result) > 0){//这一天已经有课程了，修改课程
            $cur_courseid = $result[0]['courseid'];

            //根据cur_courseid删除原来的课程
            $delete_course_sql = "DELETE FROM course_record WHERE courseid=".$cur_courseid;
            $model->execute($delete_course_sql);

            //添加课程
            $action_list = $param['action_list'];
            for ($i=0; $i < count($action_list); $i++) { 
                $action_list[$i]['courseid'] = $cur_courseid;
                $model_course_record->add($action_list[$i]);
            }

            //修改课程状态 
            $save_data['status'] = 3;
            $save_data['iscontacted'] = 1;
            $save_data['isfinished'] = 0;
            $save_data['tips'] = '';
            $save_data['pay_time'] = $result[0]['pay_time'];
            $save_data['course_time'] = $time;
            $save_data['begin_time'] = $result[0]['begin_time'];
            $save_data['end_time'] = $result[0]['end_time'];
            $model_my_plan->where('id='.$result[0]['id'])->save($save_data);

            //返回结果
            $data['errno'] = 0;
            $data['courseid'] = $cur_courseid;
            echo json_encode($data);
        }else{//这一天没有课程

            //添加课程
            $action_list = $param['action_list'];
            for ($i=0; $i < count($action_list); $i++) { 
                $action_list[$i]['courseid'] = $next_courseid;
                $model_course_record->add($action_list[$i]);
            }


            //找到开始时间和结束时间
            $result_my_plan = $model_my_plan->where($condition)->select();
            $begin_time =$result_my_plan[0]['begin_time'];
            $end_time = $result_my_plan[0]['end_time'];
            $pay_time = $result_my_plan[0]['pay_time'];

            $course_time = $result_my_plan[0]['course_time'];

            if($course_time==1 || $course_time==0){

                $begin_time=time();
                $end_time=$begin_time+30*86400;

            }

            //把课程添加到计划
            unset($condition);
            $condition['uid'] = $uid;
            $condition['pid'] = $pid;
            $condition['coachid'] = $coachid;
            $condition['courseid'] = 0;

            unset($result_my_plan);
            $result_my_plan = $model_my_plan->where($condition)->select();
            if(count($result_my_plan) > 0){
                $id = $result_my_plan[0]['id'];
                $save_data['uid'] = $uid;
                $save_data['pid'] = $pid;
                $save_data['coachid'] = $coachid;
                $save_data['courseid'] = $next_courseid;
                $save_data['status'] = 3;
                $save_data['iscontacted'] = 1;
                $save_data['isfinished'] = 0;
                $save_data['tips'] = '';
                $save_data['pay_time'] = $pay_time;
                $save_data['course_time'] = $time;
                $save_data['begin_time'] = $begin_time;
                $save_data['end_time'] = $end_time;
                $model_my_plan->where('id='.$id)->save($save_data);
            }else{
                $add_data['uid'] = $uid;
                $add_data['pid'] = $pid;
                $add_data['coachid'] = $coachid;
                $add_data['courseid'] = $next_courseid;
                $add_data['status'] = 3;
                $add_data['iscontacted'] = 1;
                $add_data['isfinished'] = 0;
                $add_data['tips'] = '';
                $add_data['pay_time'] = $pay_time;
                $add_data['course_time'] = $time;
                $add_data['begin_time'] = $begin_time;
                $add_data['end_time'] = $end_time;
                $model_my_plan->add($add_data);
            }
            
            //返回数据
            $data['errno'] = 0;
            $data['courseid'] = $next_courseid;
            echo json_encode($data);

            //通知用户
            $sql = "SELECT id, name,nicker FROM user_info WHERE id IN($uid,$coachid)";
            $model = new Model();
            $result = $model->query($sql);
            foreach($result as $value){
                if($value['id'] == $uid){//用户
                    $userNicker = $value['nicker'];
                }else if($value['id'] == $coachid){
                    $coachName = $value['name'];
                }
            }

            $strDate = timeToString($time);
            $strTicker = 'Hi,'.$userNicker.' 你有新的计划啦,快点开看看吧';
            $strTitle = 'Hi,'.$userNicker.' 你有新的计划啦，快点开看看吧';
//            $strText = 'Hi,'.$userNicker.' 你的教练'.$coachName.'给你安排了'.$time.'的训练计划啦  记得去查看哦~';
            $strText = 'Hi,'.$userNicker.' 你的教练'.$coachName.'给你安排了新的训练计划啦  记得去查看哦~';



            //发通知
//            sendDeviceUnicast($uid,$strTicker,$strTitle,$strText);
            getuiSendDeviceUnicast($uid,$strTitle,$strText);
        }


    }




/*
{
    "xtoken":"35dsakfsdjfcvjdsajfkdsf234",
    "coachid":45,
    "uid":444,
    "pid":41,
    "course_time":3825843,
    "course_list":[
    {
        "action_list":[
        {
            "actionid":34,
            "group":3,
            "count":20,
            "order":1,
            "type":1,
            "duration":34
        },
        {   
            "actionid":34,
            "group":3,
            "count":20,
            "order":1,
            "type":2,
            "duration":54
        }]
    },
    {
        "action_list":[
        {
            "actionid":34,
            "group":3,
            "count":20,
            "order":1,
            "type":1,
            "duration":34
        },
        {   
            "actionid":34,
            "group":3,
            "count":20,
            "order":1,
            "type":2,
            "duration":54
        }]
    }]
}
*/

    //添加多天的课程
    function addManyDayCourse(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);
     /*   $str = '{
    "xtoken":"35dsakfsdjfcvjdsajfkdsf234",
    "coachid":45,
    "uid":999,
    "pid":41,
    "course_list":[
    {
        "course_time":32435435,
        "action_list":[
        {
            "actionid":177,
            "group":3,
            "count":20,
            "order":1,
            "type":1,
            "duration":34
        },
        {   
            "actionid":188,
            "group":3,
            "count":20,
            "order":1,
            "type":2,
            "duration":54
        }]
    },
    {
        "course_time":32438935,
        "action_list":[
        {
            "actionid":199,
            "group":3,
            "count":20,
            "order":1,
            "type":1,
            "duration":34
        },
        {   
            "actionid":166,
            "group":3,
            "count":20,
            "order":1,
            "type":2,
            "duration":54
        }]
    }]
}';*/
        // $param = json_decode($str,true);
        $token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
        $pid = $param['pid'];
        $coachid = $param['coachid'];

        if(!isset($uid) || !isset($pid) || !isset($coachid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $course_list = $param['course_list'];

        //找到开始时间和结束时间
        $begin_time = $course_list[0]['course_time'];
        $end_time = $course_list[0]['course_time'];
        for ($i=0; $i < count($course_list); $i++) { 
            if($course_list[$i]['course_time'] <= $begin_time ){
                $begin_time = $course_list[$i]['course_time'];
            }

            if($course_list[$i]['course_time'] >= $end_time){
                $end_time = $course_list[$i]['course_time'];
            }
        }


        //课程id
        $model_course_record = new Model('course_record');
        $cur_courseid = $model_course_record->max('courseid') + 1;

        //获取 pay_time
        $model_my_plan = new Model('my_plan');
        $condition['uid'] = $uid;
        $condition['pid'] = $pid;
        $condition['coachid'] = $coachid;
        $condition['courseid'] = 0;
        $result_pay_time = $model_my_plan->select();
        $pay_time = $result_pay_time[0]['pay_time'];
        $model_my_plan->where($condition)->delete();//删除记录为courseid=0的

        for ($i=0; $i < count($course_list); $i++) { 

            //添加课程记录
            $action_list = $course_list[$i]['action_list'];
            for ($j=0; $j < count($action_list); $j++) { 
                $action_list[$j]['courseid'] = $cur_courseid;
                $model_course_record->add($action_list[$j]);
            }

            //把相应的课程记录添加到my_plan
            unset($data_my_plan);
            $data_my_plan['uid'] = $uid;
            $data_my_plan['pid'] = $pid;
            $data_my_plan['coachid'] = $coachid;
            $data_my_plan['courseid'] = $cur_courseid;
            $data_my_plan['status'] = 3;//课程已经制定
            $data_my_plan['iscontacted'] = 0;
            $data_my_plan['isfinished'] = 0;
            $data_my_plan['pay_time'] = $pay_time;
            $data_my_plan['course_time'] = $course_list[$i]['course_time'];
            $data_my_plan['begin_time'] = $begin_time;
            $data_my_plan['end_time'] = $end_time;

            $model_my_plan->add($data_my_plan);
            $cur_courseid++;
        }

        $data['errno'] = 0;
        $data['uid'] = $uid;
        $data['pid'] = $pid;
        $data['coachid'] = $coachid;
        echo json_encode($data);
    }


    //获取我的某一个计划的所有训练课程(html5没有用到,只给app用了)
    function getMyAllCourse(){
        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
        //$uid = 1438;
        if(empty($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }   

        $pid = $param['pid'];
        //$pid = 1;
        if(empty($pid)){
            err_ret(-205,'lack of param','缺少参数');
        }   

        $coachid = $param['coachid'];
        //$coachid=53;
        if(empty($coachid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        //参与此计划的人数
        $condition['pid'] = $pid;
        $model_my_plan = new Model('my_plan');
        $userCount = $model_my_plan->where($condition)->count("id");

        //查询计划的天数
        $model = new Model();
        $sql = "SELECT * FROM xorder WHERE uid=$uid AND pid=$pid AND coachid=$coachid AND status=1 ORDER BY time_end";
        $result_totaldays = $model->query($sql);
        if(count($result_totaldays) > 0){
            $paytime=$result_totaldays[0]['time_end'];
            $tdays=M("my_plan")->where("uid={$uid} and pid={$pid} and coachid={$coachid} and pay_time={$paytime}")->limit(0,1)->select();
            $total_days=($tdays[0]['end_time']-$tdays[0]['begin_time'])/86400;
            $total_days=floor($total_days);
            // $total_days = 30 * count($result_totaldays);
            $min_begin_time = $result_totaldays[0]['time_end'];
            $max_end_time = $min_begin_time + ( 86400 * $total_days );
        }else{
            $total_days = 0;
            $min_begin_time = 0;
            $max_end_time = 0;
        }

        //参与此计划的用户的头像
        $sql_user_header = "SELECT DISTINCT header FROM user_info WHERE id in ( SELECT DISTINCT uid FROM my_plan WHERE pid=$pid)";
        $result_user_header = $model->query($sql_user_header);

        //查询我的所有的课程
        $sql_my_course = "SELECT DISTINCT a.courseid,a.course_time,a.status,a.iscontacted,a.isfinished,a.tips,b.id,b.actionid,b.group,b.count,b.order,b.type AS isfree,b.duration,c.title,c.subtitle,c.desc,c.hard,c.part,c.type,c.equipment,c.kalu,c.url,c.coverimg,c.voice 
        FROM my_plan AS a,course_record AS b ,action AS c 
        WHERE a.courseid=b.courseid AND c.id=b.actionid AND a.uid=$uid AND a.pid=$pid AND a.coachid=$coachid";
        $model_course = new Model();
        $result = $model->query($sql_my_course);

        //找出所有的天
        for ($i=0; $i < count($result); $i++) { 
            $days[] = date('Y-m-d',$result[$i]['course_time']);
        }
        $days = array_unique($days);

        $data_result = array();
        $data_days = array();
        foreach ($days as $value) {
            foreach ($result as $key => $record) {
                $one_day = date('Y-m-d',$record['course_time']);
                if($one_day == $value){
                    $data_result[$value][] = $record;
                }
            }
            $data['course_list'][]['one_day_list'] = $data_result[$value];
        }

        $data['errno'] = 0;
        $data['uid'] = $uid;
        $data['pid'] = $pid;
        $data['coachid'] = $coachid;
        $data['number'] = $userCount;
        $data['days'] = $total_days;
        $data['begin_time'] = $min_begin_time;
        $data['end_time'] = $max_end_time;
        $data['header_list'] = $result_user_header;
        echo json_encode($data);
    }

    //获取我的计划对应的某一天的训练课程
    function getMyOneDayCourse(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
        // $uid = 62;
        if(empty($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }   

        $pid = $param['pid'];
        // $pid = 3;
        if(empty($pid)){
            err_ret(-205,'lack of param','缺少参数');
        }   

        $coachid = $param['coachid'];
        // $coachid = 51;
        if(empty($coachid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $time = $param['time'];
        // $time = 1450022400;
        if(empty($time)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $t_my_plan = C('T_MY_PLAN');
        $t_course_record = C('T_COURSE_RECORD');

        $sql = "SELECT course_record.courseid,action.title,action.subtitle,action.desc,action.equipment,action.hard,t.course_time,action.url,action.type, course_record.actionid,course_record.group,course_record.count,course_record.order 
        FROM course_record,action, (SELECT my_plan.courseid ,my_plan.course_time FROM my_plan WHERE uid=$uid AND pid=$pid AND coachid=$coachid) AS t
        WHERE course_record.actionid=action.id AND course_record.courseid=t.courseid
                ORDER BY course_record.order";

        $model = new Model();
        $result = $model->query($sql);

        $data_result = array();
        for ($i=0; $i < count($result); $i++) { 
            if(timeToStr($result[$i]['course_time']) == timeToStr($time)){
                $data_result[] = $result[$i];
            }
        }
        
        $data['errno'] = 0;
        $data['uid'] = $uid;
        $data['pid'] = $pid;
        $data['coachid'] = $coachid;
        $data['course_time'] = $time;
        $data['action_list'] = $data_result;
        echo json_encode($data);
    }

    //获取我的课程
    function getMyCourseByPid(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);
        
        $token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
        // $uid = 42;
        if(!isset($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $pid = $param['pid'];
        // $pid = 1;
        if(!isset($pid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $coachid = $param['coachid'];
        // $coachid = 34;
        if(!isset($coachid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        //用来区别是接口给h5的接口还是提供给app的接口
        $app_type = $param['app_type'];
        // $app_type = 'html5';
        if(!isset($app_type)){
            $app_type = 'html5';
        }

        $sql = "SELECT DISTINCT a.courseid,a.course_time,a.status,a.iscontacted,a.isfinished,a.tips,b.actionid,b.group,b.count,b.order,b.type AS isfree,b.duration,c.title,c.subtitle,c.desc,c.hard,c.part,c.type,c.equipment,c.kalu,c.url,c.coverimg,c.voice 
        FROM my_plan AS a,course_record AS b ,action AS c 
        WHERE a.courseid=b.courseid AND c.id=b.actionid AND a.uid=$uid AND a.pid=$pid AND a.coachid=$coachid";
        $model = new Model();
        $result = $model->query($sql);

        //返回给html5端的数据
        if($app_type == 'html5'){
            //找出所有的天
            for ($i=0; $i < count($result); $i++) { 
                $days[] = date('Y-m-d',$result[$i]['course_time']);
            }   
            $days =  array_unique($days);
            $data_result = array();
            $data_days = array();
            foreach ($days as $value) {
                foreach ($result as $key => $record) {
                    $one_day = date('Y-m-d',$record['course_time']);
                    if($one_day == $value){
                        $data_result[$value][] = $record;
                    }
                }
                // $data['course_list'][][ $value ] = $data_result[$value];
                $data['course_list'][ $value ] = $data_result[$value];
            }

            $data['errno'] = 0;
            $data['uid'] = $uid;
            $data['pid'] = $pid;
            $data['coachid'] = $coachid;
            echo json_encode($data);

        //返回给安卓端的数据
        }else if($app_type == 'android'){
            //找出所有的天
            for ($i=0; $i < count($result); $i++) { 
                $days[] = date('Y-m-d',$result[$i]['course_time']);
            }   
            $days =  array_unique($days);
            $data_result = array();
            $data_days = array();
            foreach ($days as $value) {
                foreach ($result as $key => $record) {
                    $one_day = date('Y-m-d',$record['course_time']);
                    if($one_day == $value){
                        $data_result[$value][] = $record;
                    }
                }
                $data['course_list'][]['one_day_list'] = $data_result[$value];
            }

            $data['errno'] = 0;
            $data['uid'] = $uid;
            $data['pid'] = $pid;
            $data['coachid'] = $coachid;
            echo json_encode($data);
        }//end if
    }

    function searchActionByKeyword(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);
        
        $word = $param['word'];
        if($word == ''){
            echo '';
            die();
        }

        $sql = "SELECT * FROM action WHERE title LIKE '%$word%'";
        $model = new Model();
        $result = $model->query($sql);

        echo json_encode($result);
    }


    //更新难度 
    function updateCourseHard(){
        header('Access-Control-Allow-Origin:*');//跨域
        header("Content-type: text/html; charset=utf-8"); 

        $param = json_decode(file_get_contents('php://input'), true);

        $token = $param['xtoken'];
        init_verify_token($token);

        $course_record_id = $param['course_record_id'];
        // $course_record_id = 293;
        if(empty($course_record_id)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $hard = $param['hard'];
        // $hard = 2;
        if(empty($hard)){
            $hard = 1;
        }

        $model = new Model('course_record');
        $condition['id'] = $course_record_id;
        $save_data['hard'] = $hard;
        $model->where($condition)->save($save_data);

        $data['errno'] = 0;
        $data['course_record_id'] = $course_record_id;
        $data['hard'] = $hard;

        echo json_encode($data);
    }



}