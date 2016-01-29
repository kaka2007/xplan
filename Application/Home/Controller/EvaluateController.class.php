<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;


//我的评估模块
class EvaluateController extends Controller {

	//获取我的评估表
	function getMyEvaluateList(){
		$param = json_decode(file_get_contents('php://input'), true);

    	$token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
         //$uid = 1369;
        if(empty($uid)){
        	err_ret(-205,'lack of param','缺少参数');
        }

        $model_evaluate = new Model('evaluate');
        $condition['uid'] = $uid;
        $sql="SELECT e.*,u.nicker FROM evaluate AS e LEFT JOIN user_info AS u ON e.uid=u.id WHERE e.uid={$uid}";
        $result_evaluate=M()->query($sql);
        //$result_evaluate = $model_evaluate->where($condition)->select();
        $data['errno'] = 0;
        $data['evaluate_list'] = $result_evaluate;
        echo json_encode($data);
	}

    function getMyEvaluateById(){
       $param = json_decode(file_get_contents('php://input'), true);

        $token = $param['xtoken'];
        init_verify_token($token);

        /*$uid = $param['uid'];
         $uid = 1584;
        if(empty($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }*/

        $eid = $param['eid'];
         //$eid = 12;
        if(empty($eid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        /*$model_user = new Model('user_info');
        $condition['id'] = $uid;
        $result_user = $model_user->where($condition)->select();*/

        $model_evaluate = new Model('evaluate');

        $result_evaluate = $model_evaluate->where("id={$eid}")->select();

        $result_evaluate[0]['errno']=0;
        $data=$result_evaluate[0];

        echo json_encode($data);
    }

    //更新评估的基础信息
    function updateEvaluateBaseInfo(){
        $param = json_decode(file_get_contents('php://input'), true);

        $token = $param['xtoken'];
        init_verify_token($token);

        $uid = $param['uid'];
        // $uid = 42;
        if(empty($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $eid = $param['eid'];
        // $eid = 2;
        if(empty($eid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $gender = $param['gender'];
        $birth  = $param['birth'];
        $height = $param['height'];
        $weight = $param['weight'];
        $bustwidth   = $param['bustwidth'];
        $waistwidth  = $param['waistwidth'];
        $breechwidth = $param['breechwidth'];

        $condition = array();
        if(!empty($gender) || !empty($birth)){
            if(!empty($gender)){
                $condition['gender'] = $gender;
            }

            if(!empty($birth)){
                $condition['birthday'] = $birth;
            }

            $model_user = new Model('user_info');
            $str = "id = ".$uid;
            $model_user->where($str)->save($condition);
            unset($condition);
        }

        if(!empty($height)){
            $condition['height'] = $height;
        }

        if(!empty($weight)){
            $condition['weight'] = $weight;
        }

        if(!empty($bustwidth)){
            $condition['bustwidth'] = $bustwidth;
        }

        if(!empty($waistwidth)){
            $condition['waistwidth'] = $waistwidth;
        }

        if(!empty($breechwidth)){
            $condition['breechwidth'] = $breechwidth;
        }
        
        $str = "id = ".$eid;
        $model_evaluate = new Model('evaluate');
        $model_evaluate->where($str)->save($condition);
 
        $data['errno'] = 0;
        echo json_encode($data);
    }

    public function addEvaluateMsg(){
      $param = json_decode(file_get_contents('php://input'), true);
      $token = $param['xtoken'];
      init_verify_token($token);

      $uid = $param['uid'];

      if(empty($uid)){
        err_ret(-205,'lack of param','缺少参数');
        }

      if($param['gender']!=0 && $param['gender']!=1){
            $param['gender']=0;
        }

      if(empty($param['height'])){
            err_ret(-205,'lack of param','缺少参数');
        }

      if(empty($param['weight'])){
           err_ret(-205,'lack of param','缺少参数');
        }

      if(empty($param['bustwidth'])){
            err_ret(-205,'lack of param','缺少参数');
        }

      if(empty($param['waistwidth'])){
            err_ret(-205,'lack of param','缺少参数');
        }

      if(empty($param['breechwidth'])){
           err_ret(-205,'lack of param','缺少参数');
        }


        $param['time']=time();
        $param['birthday']=strtotime($param['birthday']);

        //user_info表更新
        $user['birthday']=$param['birthday'];
        $user['height']=$param['height'];
        $user['weight']=$param['weight'];
        $user['bustwidth']=$param['bustwidth'];
        $user['waistwidth']=$param['waistwidth'];
        $user['breechwidth']=$param['breechwidth'];
        $aff1=M("user_info")->where("id={$uid}")->save($user);

        $aff2=M("evaluate")->add($param);   

        if($aff2){
            $aff3=M("my_plan")->where("uid={$uid} AND status<2")->save(array("status"=>2));

            $data=array();
            $data=$param;
            $data['errno'] = 0;
            echo json_encode($data);
        }else{
            err_ret(-501,"add evaluate failed","添加体能评估失败");
        }
    }
}

