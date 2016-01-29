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


function sendDeviceUnicast($uid,$ticker,$title,$text){
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
    if($type == 1){//安卓设备
        Vendor('UmengPushAPI.UmengPushAPI');
        $umengPush = new \UmengPushAPI();
        $umengPush->sendAndroidUnicast($device_token,$ticker,$title,$text);
    }else if($type == 2){//苹果设备

    }

}




?>