<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

/**
 * Created by PhpStorm.
 * User: jiulu
 * Date: 2015/12/23
 * Time: 20:02
 * 友盟推送服务端API
 */

class UmengpushController extends Controller {
    function sendDeviceUnicast(){
        $param = json_decode(file_get_contents('php://input',true));

        $xtoken = $param['xtoken'];
        init_verify_token($xtoken);

        $uid = $param['uid'];
        if(empty($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $title = $param['title'];
        if($title == ''){
            err_ret(-205,'lack of param','缺少参数');
        }

        $text = $param['text'];
        if($text == ''){
            err_ret(-205,'lack of param','缺少参数');
        }

        sendDeviceUnicast(1369,'你中2亿元大奖啦','快去领奖吧','快去领奖吧，领奖记得要捐款啊');
    }


    //上传deviceToken
    function updateDeviceToken(){
        $param = json_decode(file_get_contents('php://input'), true);

        $xtoken = $param['xtoken'];
        init_verify_token($xtoken);

        $uid = $param['uid'];
        if(empty($uid)){
            err_ret(-205,'lack of param','缺少参数');
        }

        $device_token = $param['device_token'];
        if(empty($device_token)){
            err_ret(-205,'lack of param','缺少参数');
        }

        //设备类型 1安卓手机  2苹果手机
        $type = $param['type'];
        if($type != 1 && $type != 2){
            err_ret(-205,'param is error','参数错误');
        }

        $model = new Model('apns_user');
        $where_data['uid'] = $uid;
        $result = $model->where($where_data)->select();

        if(count($result) > 0){//如果已经有了deviceToken
            $old_type = $param[0]['type'];
            $old_device_token = $param[0]['device_token'];
            if($old_device_token != $device_token){
                $type = $old_type == 1 ? 2 : 1;
                $condition['uid'] = $uid;
                $save_data['device_token'] = $device_token;
                $save_data['type'] = $type;
                $model->where($condition)->save($save_data);
            }
        }else{
            $add_data['device_token'] = $device_token;
            $add_data['uid'] = $uid;
            $add_data['type'] = $type;
            $result = $model->add($add_data);
        }

        $data['errno'] = 0;
        $data['uid'] = $uid;
        $data['type'] = $type;
        $data['device_token'] = $device_token;
        echo json_encode($data);
    }




}
?>
