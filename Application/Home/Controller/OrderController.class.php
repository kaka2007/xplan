<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class OrderController extends Controller {

	//检查交易的状态
	function checkTradeStatus(){
		$param = json_decode(file_get_contents('php://input'), true);

		$token = $param['xtoken'];
        init_verify_token($token);

		$nonceStr = $param['noncestr'];
		if(empty($nonceStr)){
			err_ret(-205,'lack of param','缺少参数');
		}
		
		$model = new Model('xorder');
		$condition['nonce_str'] = $nonceStr;
		$result = $model->where($condition)->select();

		if(count($result) > 0 && $result[0]['status'] == 1){
			$data['errno'] = 0;
			$data['noncestr'] = $nonceStr;
			echo json_encode($data);
		}else{
			err_ret(-401,'No the record','数据库中无记录');
		}
	}

	//查看教练的用户是否超过最大值 
	function checkCoachMaxUsers(){
		$param = json_decode(file_get_contents('php://input'), true);

		$token = $param['xtoken'];
        // init_verify_token($token);

        $coachid = $param['coachid'];
        // $coachid = 53;
        if(empty($coachid)){
        	err_ret(-205,'lack of param','缺少参数');
        }

		//判断教练的客户是否超过10个
		$model = new Model();
		$sql_user = 'SELECT DISTINCT uid  FROM my_plan WHERE coachid='.$coachid;
		$result_user = $model->query($sql_user);

		$studentNums=M("user_info")->where("type=1 and id={$coachid}")->getField("studentnums");

		if(count($result_user) >= $studentNums){
			$model_user = new Model('user_info');
			$save_data['status'] = 1;
			$where_data['id'] = $coachid;
			$where_data['type'] = 1;
			$model_user->where($where_data)->save($save_data);

			$data['errno'] = 0;
			$data['is_max'] = 1;
			$data['coachid'] = $coachid;
			echo json_encode($data);
		}else{
			$data['errno'] = 0;
			$data['is_max'] = 0;
			$data['coachid'] = $coachid;
			echo json_encode($data);

		}
	}

}