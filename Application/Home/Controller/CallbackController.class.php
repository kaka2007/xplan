<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

class CallbackController extends Controller {

	//微信支付回调接口
	function weixinPay(){
		$param = file_get_contents('php://input');

		$uid = $_REQUEST['uid'];
		$pid = $_REQUEST['pid'];
		$coachid = $_REQUEST['coachid'];

		$condition = array();
		$condition['uid'] = $uid;
		$condition['pid'] = $pid;
		$condition['coachid'] = $coachid;

		$xml=simplexml_load_string($param);
		foreach($xml->children() as $child){
			$key = $child->getName();
			if($key == "nonce_str"){
				$condition['nonce_str'] = (string)$child;
			}else if($key == "out_trade_no"){
				$condition['out_trade_no'] = (string)$child;
			}else if($key == "transaction_id"){
				$condition['transaction_id'] =(string)$child;
			}else if($key == "time_end"){
				$condition['time_end'] = time();
			}else if($key == "total_fee"){
				$condition['money'] = (int)$child;
			}else if($key == "sign"){
				$condition['sign'] = (string)$child;
			}else if($key == "result_code"){
				$result_code = (string)$child;	
				if($result_code == 'SUCCESS'){
					$condition['status'] = 1;//支付成功
				}else{
					$condition['status'] = 2;//支付失败
				}
			}
		}

        //测试
        if($condition['status'] == 1){
            //发送通知
            newOrderNotification($coachid,$uid);

            $model_order = new Model('xorder');
            $count = $model_order->add($condition);

            $con['uid'] = $uid;
            $con['pid'] = $pid;
            $con['coachid'] = $coachid;
            $result = $model_order->where($con)->select();

            $model_my_plan = new Model('my_plan');
            if(count($result) == 1){//第一次，添加一条记录
                $first_pay_time = $result[0]['time_end'];
                $con['courseid'] = 0;
                $con['status'] = 1;
                $con['iscontacted'] = 0;
                $con['isfinished'] = 0;
                $con['tips'] = '';
                $con['pay_time'] = $first_pay_time;
                $con['course_time'] = 0;
                $con['begin_time'] = $first_pay_time + 86400; //付款的第二天
                $con['end_time'] = $first_pay_time + 86400 + count($result) * 30 * 86400;//计划的结束时间
                $result_my_plan = $model_my_plan->add($con);
//			writeOneLine('/tmp/a.txt',"第一次添加my_plan count=$count");
            }else{//不是第一次付款，更新我的计划的结束时间
                $save_condition['uid'] = $uid;
                $save_condition['pid'] = $pid;
                $save_condition['coachid'] = $coachid;

                $result_my_plan = $model_my_plan->where($save_condition)->select();
                $save_data['end_time'] = $result_my_plan[0]['begin_time'] + 86400 + count($result) * 30 * 86400;
                if($result_my_plan[0]['status']==4){
                	$save_data['status']=3;
                }
                $result_my_plan = $model_my_plan->where($save_condition)->save($save_data);
//			writeOneLine('/tmp/a.txt',"不是第一次添加my_plan count=$count");
            }
        }else if($condition['status'] == 2){
        }
		echo '<xml><return_code><![CDATA[SUCCESS]]></return_code></xml>';
	}

}
?>