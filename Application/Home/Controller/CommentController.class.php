<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;


/*
	评价
*/
class CommentController extends Controller {
  	function deleteComment(){
  		  $param = json_decode(file_get_contents('php://input'), true);

    	  $token = $param['xtoken'];
        init_verify_token($token);

        $id = $param['id'];
        if(empty($id)){
          err_ret(-205,'lack of param','缺少参数');
        }

       	$model = new Model('comment');
       	$condition['id'] = $id;
       	$result = $model->where($condition)->delete();
       	if(!$result){//删除失败，或者删除的记录为0
       		err_ret(-401,'no the record','删除失败，或者删除的记录为0');
       	}else{//删除成功
       	    $data['errno'] = 0;
            $data['id'] = $id;
       		$data['count'] = $result;
       		echo json_encode($data);
       }
  	}

    function addComment(){
      $param = json_decode(file_get_contents('php://input'), true);

      $token = $param['xtoken'];
      init_verify_token($token);

      $uid = $param['uid'];
      if(empty($uid)){
        err_ret(-205,'lack of param','缺少参数');
      }
      $coachid = $param['coachid'];
      if(empty($coachid)){
        err_ret(-205,'lack of param','缺少参数');
      }
      $content = $param['content'];

      //判断是否购买过这个教练的服务，买过才能评价
      $model_my_plan = new Model('my_plan');
      $condition['uid'] = $uid;
      $condition['coachid'] = $coachid;
      $result_my_plan = $model_my_plan->where($condition)->select();
      if(count($result_my_plan) <= 0){ //没有购买过
        $data['errno'] = 1;
        $data['uid'] = $uid;
        $data['coachid'] = $coachid;
        echo json_encode($data);
        die();
      }



      $model_comment = new Model('comment');
      $add_data['coachid'] = $coachid;
      $add_data['userid'] = $uid;
      $add_data['content'] = $content;
      $add_data['time'] = time();

      $result_comment = $model_comment->add($add_data);
      $data['errno'] = 0;
      $data['uid'] = $uid;
      $data['coachid'] = $coachid; 

      echo json_encode($data);
    }


}

?>