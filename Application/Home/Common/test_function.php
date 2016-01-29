<?php
function https_post($url,$data){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
    $result = curl_exec($curl); 
    if (curl_errno($curl)) { 
        return 'Errno'.curl_error($curl);
    }
    curl_close($curl); 
    $result=json_decode($result,true);
    $ticket = empty($result['ticket'])? '':$result['ticket'];
    return $ticket;
} 

function http_post($url, $jsondata){
        $ch = curl_init();
        $timeout = 300;       
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsondata);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json; charset=utf-8',
        'Content-Length: '.strlen($jsondata)));
        ob_start();
        $handles = curl_exec($ch);
        curl_close($ch);      
        return $handles;     
}

function get_token(){
    $appid = "wx4b68b876134be056";
    $appsecret = "da463c9d5c3d81e895de576ca199e2ec";
    $token_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";
    $data = vita_get_url_content($token_url);
    $data=json_decode($data,true);
     //微信规定access_token有效时间为7200
    $access_token = empty($data['access_token'])? '':$data['access_token'];
    return $access_token;
} 


/*
 * 精确时间间隔函数
 * $time 发布时间 如 1356973323
 * $str 输出格式 如 Y-m-d H:i:s
 * 半年的秒数为15552000，1年为31104000，此处用半年的时间
 */
function from_time($time,$str){
    isset($str)?$str:$str='m-d';
    $way = time() - $time;
    $r = '';
    if($way < 60){
        $r = '刚刚';
    }elseif($way >= 60 && $way <3600){
        $r = floor($way/60).'分钟前';
    }elseif($way >=3600 && $way <86400){
        $r = floor($way/3600).'小时前';
    }elseif($way >=86400 && $way <2592000){
        $r = floor($way/86400).'天前';
    }elseif($way >=2592000 && $way <15552000){
        $r = floor($way/2592000).'个月前';
    }else{
        $r = date("$str",$time);
    }
    return $r;
}


?>





