<?php
namespace Home\Controller;
use Think\Controller;   
use Think\Model;

class ShowController extends Controller {
    function show(){
/*       echo date("Y-m-d",541008000);
        $model = new Model();
        $sql = "UPDATE student SET age=284 WHERE id=1";
        $result = $model->execute($sql);
        var_dump($result);*/

/*       1451790866
       $time = 1452096000;
       echo timeToString($time);*/

/*       $uid = $_REQUEST['uid'];
       $strTitle = '你有新的消息了';
       $strText = 'PLAN有新的版本了，赶快去下载，有红包相送哦';

       echo "uid=$uid title=$strTitle  text=$strText<br><br>";


       getuiSendDeviceUnicast($uid,$strTitle,$strText);


       newOrderNotification('1369','1367');*/

/*       Vendor('GetuiPush.GetuiPush');

        show();
       pushMessageToSingle();

       echo __ROOT__;
       strtotime("-2 day");
       die();
       echo strtotime('1984-03-07')."<br>";

       $passwd = "0093chenrenjie";
       echo sha1($passwd);*/
/*
curl -X POST -i "https://a1.easemob.com/plan/plan/users" -d '{"username":"18507052795","password":"7cb287b200d17f92781c6cc01d5fe7e4b20a0f2b"}'

       $url = 'https://a1.easemob.com/plan/chatdemoui/token';
       $data['grant_type'] = 'client_credentials';
       $data['client_id'] = 'YXA6zOQxoEyAEeWJs5OjVUur7g';
       $data['client_secret'] = 'YXA63rRzrvgElyfAR2uGrEouPUf0-x0';
       $result = http_post($url,json_encode($data));
       var_dump($result);*/

       Vendor('EasemobApi.EasemobApi');
       $e = new \Easemob();

        //获取token
       $token = $e->getToken();
       echo $token;

        //注册单个用户
       $result = $e->registerUser('1111111',sha1('123456'),'kaka');
       echo json_encode($result);

     /*  $data[] = array('username'=>'aaabbb','password'=>sha1('123456'));
       $data[] = array('username'=>'aaaccc','password'=>sha1('123456'));

       $result = $e->registerManyUser($data);
       echo json_encode($result);*/

      // $e->deleteUser();

    }


    function test(){
        echo 'Show/test';die();
        $uid = $_GET['uid'];
        $coachid = $_GET['coachid'];
        $time = time();

        //通知用户
        $sql = "SELECT id, name,nicker FROM user_info WHERE id IN($uid,$coachid)";
        echo $sql;die();
        $model = new Model();
        $result = $model->query($sql);
        var_dump($result);
        foreach($result as $value){
            if($value['id'] == $uid){//用户
                $userNicker = $value['nicker'];
            }else if($value['id'] == $coachid){
                $coachName = $value['name'];
            }
        }

        $strDate = timeToString($time);
        $strTicker = 'Hi,'.$userNicker.' 你有新的计划啦,查看';
        $strTitle = 'Hi,'.$userNicker.' 你有新的计划啦，查看';
        $strText = "Hi,$userNicker 你的教练$coachName给你安排了$strDate的新的训练计划啦  记得去查看哦";
        //发通知
        sendDeviceUnicast($uid,$strTicker,$strTitle,$strText);


        die();

        //xplan_images/action_f_coverimg
        //http://123.57.135.102/xplan_headers/ac_x001z.jpg
        //fcoverimg


        // $model = new Model('action');
        // $result = $model->select();

        // for ($i=0; $i < count($result); $i++) { 
        //     $filename = basename($result[$i]['coverimg']);
        //     $fcoverimg = 'http://123.57.135.102/xplan_images/action_f_coverimg/'.$filename;
        //     $data['fcoverimg'] = $fcoverimg;
        //     $model->where('id='.$result[$i]['id'])->save($data);

        // }

        // $user = 'd:/user_info.xml';
        // $comment = 'd:/comment.xml';
        // $xml = simplexml_load_file($comment);


        // $model_user_info = new Model('user_info');
        // $model_comment = new Model('comment');
        // $data = array();

        // foreach($xml->children() as $child){
        //     $record_name = $child->getName();
        //     if($record_name == 'RECORD'){
        //         foreach ($child as $subchild) {
        //            $key = $subchild->getName();
        //            if($key == 'coachid'){
        //                 $data['coachid'] = (int)$subchild;
        //            }else if($key == 'userid'){
        //                 $data['userid'] = (int)$subchild + 1251;
        //            }else if($key == 'content'){
        //                 $data['content'] = (string)$subchild;
        //            }else if($key == 'time'){
        //                 $data['time'] = (int)$subchild;
        //            }
        //         }

        //         $model_comment->add($data);
        //     }
        // }



        // foreach($xml->children() as $child){
        //     $record_name = $child->getName();
        //     if($record_name == 'RECORD'){
        //         foreach ($child as $subchild) {
        //            $key = $subchild->getName();
        //            if($key == 'username'){
        //                 $data['username'] = (string)$subchild;
        //            }else if($key == 'password'){
        //                 $data['password'] = (string)$subchild;
        //            }else if($key == 'name'){
        //                 $data['name'] = (string)$subchild;
        //            }else if($key == 'gender'){
        //                 $data['gender'] = (string)$subchild;
        //            }else if($key == 'header'){
        //                 $data['header'] = (string)$subchild;
        //            }else if($key == 'nicker'){
        //                 $data['nicker'] = (string)$subchild;
        //            }else if($key == 'height'){
        //                 $data['height'] = (string)$subchild;
        //            }else if($key == 'weight'){
        //                 $data['weight'] = (string)$subchild;
        //            }else if($key == 'type'){
        //                 $data['type'] = (string)$subchild;
        //            }else if($key == 'qq'){
        //                 $data['qq'] = (string)$subchild;
        //            }else if($key == 'weixin'){
        //                 $data['weixin'] = (string)$subchild;
        //            }else if($key == 'phone'){
        //                 $data['phone'] = (string)$subchild;
        //            }else if($key == 'weibo'){
        //                 $data['weibo'] = (string)$subchild;
        //            }else if($key == 'city'){
        //                 $data['city'] = (string)$subchild;
        //            }else if($key == 'birthday'){
        //                 $data['birthday'] = (string)$subchild;
        //            }else if($key == 'email'){
        //                 $data['email'] = (string)$subchild;
        //            }else if($key == 'regtime'){
        //                 $data['regtime'] = (int)$subchild + 123;
        //            }
        //         }
        //        $model_user_info->add($data);
        //        unset($data);
        //     }
        // }

        // echo "添加评论成功";

    }

    function abc(){
        echo __ROOT__."<br>";
        echo C('TMPL_PARSE_STRING')['__PUBLIC__']."<br>";
        echo C('TMPL_PARSE_STRING')['__JS__']."<br>";
        echo C('TMPL_PARSE_STRING')['__CSS__']."<br>";
        echo C('TMPL_PARSE_STRING')['__IMG']."<br>";
    }

    function cool(){
        Vendor('phpQuery.phpQuery');
        \phpQuery::newDocumentFile('http://job.blueidea.com');  

        $companies = pq('#hotcoms .coms')->find('div');  
        foreach($companies as $company) {  
           echo pq($company)->find('h3 a')->text()."<br>";  
        }  
    }


}   

?>
