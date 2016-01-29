<?php
use Think\Model;

/**
 * Created by PhpStorm.
 * User: jiulu
 * Date: 2015/12/23
 * Time: 21:58
 * @param $uid
 * @param $title
 * @param $text
 * @return bool
 */

//发送单条通知
function getuiSendDeviceUnicast($uid,$title,$text){
    if(empty($uid) || $title == '' || $text == ''){
        err_ret(-205,'lack of param','缺少参数');
    }

    $model = new Model('apns_user');
    $condition['uid'] = $uid;
    $result = $model->where($condition)->select();

    if(count($result) <= 0){
        return false;
    }

    $type = $result[0]['type'];
    $device_token = $result[0]['device_token'];
//    echo $device_token;
    if($type == 1){//安卓设备
        Vendor('GetuiPush.GetuiPush');
        pushMessageToSingle($device_token,$title,$text);
    }else if($type == 2){//苹果设备

    }

}




?>