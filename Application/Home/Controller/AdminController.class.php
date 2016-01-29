<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;


class AdminController extends Controller {
	function getPayedUser($pageNum=NULL){

		$model = new Model();
		$sql ="SELECT username,name,gender,phone,nicker ,coachid, FROM_UNIXTIME(time_end,'%Y-%m-%d %H:%m:%s') AS pay_time,money/100 AS money FROM xorder,user_info WHERE xorder.uid=user_info.id ORDER BY time_end DESC";
		$result = $model->query($sql);

		$rowCount = count($result);

		$pageSize=10;
		$offset=0;
		$url="Admin/getPayedUser";
		$rowPage=getPage($rowCount,$pageSize,$pageNum,$url);
		$offset=$rowPage['offset'];

		$sql ="SELECT username,name,gender,phone,nicker,coachid, FROM_UNIXTIME(time_end,'%Y-%m-%d %H:%m:%s') AS pay_time,money/100 AS money FROM xorder,user_info WHERE xorder.uid=user_info.id ORDER BY time_end DESC LIMIT {$offset},{$pageSize}";
		$result = $model->query($sql);

		foreach($result as $k=>$v){
			$result[$k]['coach']=M("user_info")->where("id={$v['coachid']}")->getField("name");
		}

		$this->assign("offset",$offset+1);
		$this->assign("count",$rowCount);
		$this->assign("pageList",$rowPage['show']);
		$this->assign("result",$result);
		$this->display();








/*		echo "<pre>";
		print_r($result);
		echo "</pre>";*/
/*		$title = "用户名&nbsp&nbsp&nbsp&nbsp姓名&nbsp&nbsp&nbsp&nbsp电话&nbsp&nbsp&nbsp&nbsp昵称&nbsp&nbsp&nbsp&nbsp付款时间&nbsp&nbsp&nbsp&nbsp金额<br>";
		echo $title;
		for ($i=0; $i < count($result); $i++) { 
			$username = $result[$i]['username'];
			$name = $result[$i]['name'];
			$gender = $result[$i]['gender'] == 0 ? '男' : '女';
			$phone = $result[$i]['phone'];
			$nicker = $result[$i]['nicker'];
			$pay_time = $result[$i]['pay_time'];
			$money = $result[$i]['money'];

			$line = $username.'&nbsp&nbsp&nbsp&nbsp'.$name.'&nbsp&nbsp&nbsp&nbsp'.$gender.'&nbsp&nbsp&nbsp&nbsp'.$phone.'&nbsp&nbsp&nbsp&nbsp'.$nicker.'&nbsp&nbsp&nbsp&nbsp'.$pay_time.'&nbsp&nbsp&nbsp&nbsp'.$money.'<br>';
			echo $line;
			
		}

		echo "<br>总共".count($result).'个付款的健友';*/






	}

}

?>