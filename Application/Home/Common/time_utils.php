<?php


//判断两个时间戳是否是同一天
function isSameDay($time1,$time2){
	if(empty($time1) || empty($time2)){
		return false;
	}

	$str1 = date('Y-m-d',$time1);
	$str2 = date('Y-m-d',$time2);
	
	return $str1 == $str2;
}

//昨天字符串形式
function getYesterday(){
	return date("Y-m-d",strtotime("-1 day"));
}

//今天字符串形式
function getToday(){
	return date("Y-m-d");
}

//明天字符串形式
function getTomorrow(){
	return date("Y-m-d",strtotime("+1 day"));
}

//后天字符串形式
function getAfterTomorrow(){
	return date("Y-m-d",strtotime("+2 day"));
}

//把时间戳转化为日期
function timeToStr($time){
	return date("Y-m-d",$time);
}

//获取明天的时间戳
function getTomorrowInt(){
	return strtotime("+1 day");
}

//获取后天的时间戳
function getAfterTomorrowInt(){
	return strtotime("+2 day");
}


//时间戳判断两个时间相差的天数
function timediffDay( $begin_time, $end_time ) { 
    if ( $begin_time < $end_time ) { 
        $starttime = $begin_time; 
        $endtime = $end_time; 
    } else { 
        $starttime = $end_time; 
        $endtime = $begin_time; 
    } 
    $timediff = $endtime - $starttime; 
    $days = intval( $timediff / 86400 ); 
   
    return $days; 
} 

//化成年月日的形式  如 12月23号
function timeToString($time){
    $year = date('Y',$time);
    $moth = date('m',$time);
    $day = date('d',$time);

    echo $moth.'月'.$day.'号';

}


?>