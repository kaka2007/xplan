<?php
include_once('common.php');
include_once('CCPRestSDK.php');
include_once('test_function.php');
include_once('time_utils.php');
include_once('file_utils.php');

include_once('umeng_push_utils.php');
include_once('getui_push_utils.php');
include_once('order_utils.php');


function getPage($rowCount,$pageSize,$pageNum,$url,$uid=0){
		$pageCount=ceil($rowCount/$pageSize);

		if ($pageNum<=0) {
			$pageNum=1;
		}
		if ($pageNum>=$pageCount) {
			$pageNum=$pageCount;
		}

		if ($pageCount <10) {
			$start = 1;
			$end = $pageCount;
		}
		else if ($pageCount >= 10 && $pageCount < 20) {
			if($pageNum>4){
			$start = $pageNum - 3;
			if($pageNum==$pageCount){
			$end = $pageNum;
			}else{
				$end = $pageNum+3>$pageCount?$pageCount:$pageNum+3;
			}
			}else{
				$start=1;
				$end=7;
			}
		}
		elseif ($pageCount >= 20) {
			if($pageNum>4){
			$start = $pageNum - 3;
			if($pageNum==$pageCount){
			$end = $pageNum;
			}else{
				$end = $pageNum+3>$pageCount?$pageCount:$pageNum+3;
			}
			}else{
				$start=1;
				$end=8;
			}
		}

		$offset=($pageNum-1)*$pageSize;
		
		$rowPage['pageNum']=$pageNum;
		$rowPage['pageCount']=$pageCount;
		$rowPage['offset']=$offset;

		$next=$pageNum+1;
		$pre=$pageNum-1;
		$pageList.="<span>共".$pageCount."页/第".$pageNum."页</span>";
		if ($pageNum== 1){
			for($i=$start;$i<=$end;$i++){
				if ($pageNum==$i){					
					$pageList.="<span>".$i."</span>";
				}else{
					$pageList.="<a href=\"/xplan/index.php/home/".$url."/uid/".$uid."/pageNum/".$i."\">".$i."</a>";
				}
			}

			$pageList.="<a href='/xplan/index.php/home/".$url."/uid/".$uid."/pageNum/".$next."'>下一页</a>
			<a href=\"/xplan/index.php/home/".$url."/uid/".$uid."/pageNum/".$pageCount."\">尾页</a>";
		
		}else if($pageNum==$pageCount){

			$pageList.="<a href=\"/xplan/index.php/home/".$url."/uid/".$uid."/pageNum/1\">首页</a>
			<a href='/xplan/index.php/home/".$url."/uid/".$uid."/pageNum/".$pre."'>上一页</a>";

			for($i=$start;$i<=$end;$i++){
				if($pageNum==$i){					
					$pageList.="<span>".$i."</span>";
					}else{
					$pageList.="<a href=\"/xplan/index.php/home/".$url."/uid/".$uid."/pageNum/".$i."\">".$i."</a>
					";}
			}
		
		}else if($pageNum > 1 && $pageNum < $pageCount){

			$pageList.="<a href=\"/xplan/index.php/home/".$url."/uid/".$uid."/pageNum/1\">首页</a>
			<a href='/xplan/index.php/home/".$url."/uid/".$uid."/pageNum/".$pre."'>上一页</a>";
			
			for($i=$start;$i<=$end;$i++){

				if($pageNum==$i){	

					$pageList.="<span>".$i."</span>";

				}else{

					$pageList.="<a href=\"/xplan/index.php/home/".$url."/uid/".$uid."/pageNum/".$i."\">".$i."</a>";
					
					}
				}

			$pageList.="<a href='/xplan/index.php/home/".$url."/uid/".$uid."/pageNum/".$next."'>下一页</a>
			<a href=\"/xplan/index.php/home/".$url."/uid/".$uid."/pageNum/".$pageCount."\">尾页</a>";
			
			}
		$rowPage['show']=$pageList;
		return $rowPage;
}
?>

