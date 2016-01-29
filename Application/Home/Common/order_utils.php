<?php
use Think\Model;

function newOrderNotification($coachid,$uid){
    if(empty($coachid) || empty($uid)){
//        err_ret(-205,'lack of param','缺少参数');
        die();
    }

    $model = new Model();
    $sql = "SELECT id,name,nicker,phone FROM user_info WHERE id IN ($uid,$coachid)";
    $result = $model->query($sql);

    if(count($result) > 0){
        foreach($result as $value){
            if($value['id'] == $uid){
                $userNicker = $value['nicker'];
            }else if($value['id'] == $coachid){
                $coachName = $value['name'];
                $coachPhone = $value['phone'];
            }
        }

//        $coachPhone = '18612539907';
        $data = array($coachName,$userNicker);
        //发送通知
        $serverIP = C('RL_ServerIP');
        $serverPort = C('RL_ServerPort');
        $softVersion = C('RL_SoftVersion');
        $accountSid = C('RL_AccountSID');
        $accountToken = C('RL_AccountToken');
        $appId = C('RL_AppID');
        $SmsId = C('RL_SMS_ORDER_ID');

        $rest = new \REST($serverIP,$serverPort,$softVersion);
        $rest->setAccount($accountSid,$accountToken);
        $rest->setAppId($appId);

        $result_sms = $rest->sendTemplateSMS($coachPhone,$data,$SmsId);
//        var_dump($result_sms);
    }
}

?>