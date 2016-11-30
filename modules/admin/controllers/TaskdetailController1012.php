<?php
namespace app\modules\admin\controllers;
use Yii;
use app\modules\admin\models\AnnounceForm;
use app\models\Enterpris;
use app\modules\admin\models\IctWebService;
use app\modules\admin\models\AuthToken;
use app\models\Curl;
use Rest;
use Client;
class TaskdetailController extends \yii\web\Controller
{
	public $enableCsrfValidation = false;//yii默认表单csrf验证，如果post不带改参数会报错！
//    public $layout  = 'main';
    public $layout  = false;
//    public $layout  = 'layout';

    public function actionCreate()
    {	 
        if(isset($_FILES["upload_file"])){                                        
            if($_FILES["upload_file"]["name"]){
                if(((int)$_FILES["upload_file"]["size"]/1024)>200*1024){
                    echo "<script language='javascript'>";                                     
                    echo 'upload_file_end(0,"上传失败","错误！文件不可大于200MB。");';
                    echo "</script>";                                                          
                    exit;
                }
                $path = "upload/".time();

                $return = move_uploaded_file($_FILES["upload_file"]["tmp_name"],$path);
                $fileclient =  new Curl();
                $file = $fileclient->post("http://127.0.0.1/offline_media/offline_upload.php?group=0", array("upload"=>"@".$_FILES["upload_file"]["tmp_name"].time()));
                $filePath = json_decode($file,true);
                $fileinfo[0]['name'] = $_FILES["upload_file"]["name"];
                $fileinfo[0]['path'] = $filePath["result"]["url"];
                $fileinfo[0]['size'] = $_FILES["upload_file"]["size"];
                echo "<script language='javascript'>";                                  
              //  echo " alert('"."文件上传成功!"."');";            
                echo 'parent.upload_file_end(1,"上传成功","",'.json_encode($fileinfo).');';
                echo "</script>";                                                          
            }
            exit;
        }  
    	$model = new AnnounceForm();
    	$model->receiver='公司全体员工';
        return $this->render('create', [
                'model' => $model,
            ]);
    }

    public function actionSaveB()
    {   
        $announceForm =new AnnounceForm();
        //print_r(Yii::$app->request->post('AnnounceForm'));
        if(!$announceForm->save(Yii::$app->request->post('AnnounceForm'))){
            print_r('error');
            exit;
        }
        return $this->render('create', [
                'model' => $announceForm,
            ]);
    }

    public function actionIndex()
    {  
    	
       // Yii::app()->user->setFlash('success','操作成功');
    	
        if(isset($_FILES["upload_file"])){                                        
            if($_FILES["upload_file"]["name"]){
                if(((int)$_FILES["upload_file"]["size"]/1024)>200*1024){
                    echo "<script language='javascript'>";                                     
                    echo 'upload_file_end(0,"上传失败","错误！文件不可大于200MB。");';
                    echo "</script>";                                                          
                    exit;
                }
                $path = "upload/".time();

         //       $return = move_uploaded_file($_FILES["upload_file"]["tmp_name"],$path);
                $fileclient =  new Curl();

                $file = $fileclient->post("http://127.0.0.1/offline_media/offline_upload.php?group=0", array("upload"=>"@".$_FILES["upload_file"]["tmp_name"]));
                $filePath = json_decode($file,true);
                header("Content-type:text/html;charset=utf-8");                
                //file_put_contents("/usr/local/www/nginx/html/partyqing/wt.txt", date("D M d H:i:s Y") . " =======fffff=========" . $_FILES["upload_file"]["name"]."\n", FILE_APPEND);               
                //$_FILES["upload_file"]["name"] = iconv("GBK", "UTF-8",  $_FILES["upload_file"]["name"]);                
                $fileinfo['name'] = $_FILES["upload_file"]["name"];               
                $fileinfo['path'] = $filePath["result"]["url"];
                $fileinfo['size'] = $_FILES["upload_file"]["size"];
                $taskID = Yii::$app->request->post('taskID');
                echo "<script language='javascript'>";                                  
          //      echo " alert('"."$fileclient"."');";            
                echo 'parent.upload_file_end(1,"上传成功","",'.json_encode($fileinfo).",$taskID".');';
                echo "</script>";                                                          
            }
            exit;
        }        
        $model = new AnnounceForm();                     
        $sendSucceed = 0;
        if(Yii::$app->request->get('send') == "succeed"){
        	$sendSucceed = 1;
        }      
        $id=\yii::$app->request->get('id');
        $uid=\Yii::$app->session['user.uid'];
       // echo $id;
        //$uid="8@3";
        //\Yii::$app->session['user.uid']=$uid;
        //$auth = new AuthToken();
        //$auth->authTokenSession();
        $connection = Yii::$app->db;
        $connection->open();  //初始化数据库
        header("Content-type:text/html;charset=utf-8");      
        $sq0="SELECT * FROM task_submit s,djresponsitask t WHERE s.taskId='".$id."' AND s.taskNo=t.taskNo";
        //$sq0="SELECT * FROM djtask_list l,djtask_content c,djtask_procedure p WHERE l.taskId='".$id."' AND l.reporterId='".$uid."' AND l.taskNo=c.taskNo AND p.taskId=l.taskId";         
        $command = $connection->createCommand($sq0);
        $info = $command->queryOne();
        //print_r($info);
        $zp=0;
        if($info['assignuid']==$uid){$zp=1;}else{
        if($info['assign']==1){
        	echo 1;
        	return $this->redirect(['taskdetail/zp',"suid"=>$info['assignuid'],"id"=>$id]);
        }}
        return $this->render('index', [
                'info'=>$info, 'zp' => $zp,'taskId' => $id,'model' => $model,'sendSucceed'=>$sendSucceed,
            ]);
    }
    public function actionSave()
    {   
        $ictWS = new IctWebService();
        $announceForm =new AnnounceForm();
        if(Yii::$app->request->post('AnnounceForm')['receiverId'] == 0){
            $announceForm->memberTree = $ictWS->getICTContacts();
        }
        //file_put_contents("wt.txt", date("D M d H:i:s Y") . "WT0 " . json_encode(Yii::$app->request->post('AnnounceForm')) ."\n", FILE_APPEND);        
        //$q=$announceForm->saveQ(Yii::$app->request->post('AnnounceForm'));
        if(!$announceForm->save(Yii::$app->request->post('AnnounceForm'))){
            print_r('error');
            exit;
        }
     // file_put_contents("wt.txt", date("D M d H:i:s Y") . "WT1 " . json_encode(Yii::$app->request->get('main')) ."\n", FILE_APPEND);       
        return $this->render('finish',['id'=>Yii::$app->request->post('AnnounceForm')['taskId']]);
        return $this->redirect(['announce/index',"send"=>"succeed","gid"=>Yii::$app->request->post('AnnounceForm')['group']]);
        
    }
    public function actionSavezp()
    {
    	$id=\yii::$app->request->get('id');
    	$suid=\yii::$app->request->get('uid');
    	$suid=trim($suid, ',');
    	$uid=\Yii::$app->session['user.uid'];
    	//$uid="7@3";
    	$connection = Yii::$app->db;
    	$connection->open();  //初始化数据库
    	header("Content-type:text/html;charset=gbk");
    	$now=date('Y-m-d H:i:s',time());
    	//$sq2="INSERT INTO  task_submit(taskNo,approverId,submituid) VALUES('$id','$uid','$suid')";
    	$sq2="UPDATE task_submit SET assign=1,assignuid='".$suid."' WHERE taskId='".$id."'";
    	$command = $connection->createCommand($sq2);
    	$command->execute();
    	$sq0="SELECT * FROM task_submit s,djresponsitask t WHERE s.taskId='".$id."' AND s.reporterId='".$uid."' AND s.taskNo=t.taskNo";
    	$command = $connection->createCommand($sq0);
    	$info = $command->queryOne();
    	$sq0="SELECT name FROM memberlist WHERE guid='".$suid."'";
    	$command = $connection->createCommand($sq0);
    	$con = $command->queryOne();
    	$name=$con['name'];
    	require_once(dirname(__FILE__)."/config.php");
    	$eid=explode('@',$uid);
    	$api_key = "36116967d1ab95321b89df8223929b14207b72b1";
    	$authtoken=\Yii::$app->session['user.token'];
    	//file_put_contents("/usr/local/www/nginx/html/notice/wt.txt", date("D M d H:i:s Y") . " =======fffff=========" . $eid."/n".$authtoken."/n".$noticeid."\n", FILE_APPEND);
    	$data9=array();
    	$data9["uids[0]"]="$suid";
    	$data9["id"]="4";
    	$data9["api_key"]=$api_key;
    	$data9["eid"]="$eid[1]";
    	$data9["title"]="代办通知";
    	$data9["url"]="/partyqing/web/index.php?r=admin/taskinstead/index&s=1&uid=";
        $data9["auth_token"]="$token";
    	$pc= $elggclient->post('/rest/json/?method=lapp.notice',$data9);
    	//file_put_contents("/usr/local/www/nginx/html/partyqing/wt.txt", date("D M d H:i:s Y") . "WT1 " . json_encode($pc) ."\n", FILE_APPEND);
    	return $this->render('indexzp',['id'=>$id,'info'=>$info,'name'=>$name]);
    	//return $this->redirect(['taskdetail/zp','id'=>$id,'suid'=>$suid]);
    }
    public function actionZp()
    {
    	$id=\yii::$app->request->get('id');
    	$suid=\yii::$app->request->get('suid');
    	$suid=trim($suid, ',');
    	$uid=\Yii::$app->session['user.uid'];
    	//$uid="7@3";
    	$connection = Yii::$app->db;
    	$connection->open();  //初始化数据库
    	header("Content-type:text/html;charset=gbk");
    	$now=date('Y-m-d H:i:s',time());
    	//$sq2="INSERT INTO  task_submit(taskNo,approverId,submituid) VALUES('$id','$uid','$suid')";
    	$sq0="SELECT * FROM task_submit s,djresponsitask t WHERE s.taskId='".$id."' AND s.reporterId='".$uid."' AND s.taskNo=t.taskNo";
    	$command = $connection->createCommand($sq0);
    	$info = $command->queryOne();
    	$sq0="SELECT name FROM memberlist WHERE guid='".$suid."'";
    	$command = $connection->createCommand($sq0);
    	$con = $command->queryOne();
    	$name=$con['name'];
    	// file_put_contents("wt.txt", date("D M d H:i:s Y") . "WT1 " . json_encode(Yii::$app->request->get('main')) ."\n", FILE_APPEND);
    	return $this->render('indexzp',['id'=>$id,'info'=>$info,'name'=>$name]);
    	//return $this->redirect(['taskdetail/indexzp','id'=>$id,'info'=>$info,'name'=>$name]);
    }
    public function actionCancel()
    {
    	$taskId=\yii::$app->request->get('id');
    	$connection = Yii::$app->db;
    	$connection->open();  //初始化数据库
    	header("Content-type:text/html;charset=utf-8");
        $sq0="UPDATE task_submit SET assign=0 WHERE taskId='".$taskId."'";       	 
        $command = $connection->createCommand($sq0);
        $command->execute();
        return $this->redirect(['taskdetail/index',"id"=>$taskId]);        
   }
    public function actionContacts(){
        $ictWS = new IctWebService();
        //$contacts= $ictWS->getICTContacts();
        require_once(dirname(__FILE__)."/config.php");
        $connection = Yii::$app->db;
        $connection->open();  //初始化数据库
        header("Content-type:text/html;charset=utf-8");
        $uid=\Yii::$app->session['user.uid'];
        //$uid="7@3";
        $eid=explode('@',$uid);
        $data=array();
        $sq0="SELECT officeAddress FROM memberlist WHERE guid='".$uid."'";
        $command = $connection->createCommand($sq0);
        $info = $command->queryOne();
        if($info['officeAddress']){
        $guid=explode(',',$info['officeAddress']);
        $count=count($guid);
        for($i=0;$i<$count;$i++){
        	$sq0="SELECT uid FROM organization WHERE oid='".$guid[$i]."'";
        	$command = $connection->createCommand($sq0);
        	$g = $command->queryOne();
        	$guid[$i]=$g['uid'];
        	$data["id_list[$i]"]=$guid[$i];
        }
        }else{       	
        	$sq0="SELECT oid FROM memberlist WHERE guid='".$uid."'";
        	$command = $connection->createCommand($sq0);
        	$info = $command->queryOne();
        	$oid=$info['oid'];
        	$sq0="SELECT level,parentID FROM organization WHERE oid='".$oid."'";
        	$command = $connection->createCommand($sq0);
        	$g = $command->queryOne();        	
        	if($g['level']==3){
        		$oid=$g['parentID'];    		
        	}
        	$sq0="SELECT uid FROM organization WHERE oid='".$oid."'";
        	$command = $connection->createCommand($sq0);
        	$g = $command->queryOne();
        	$data["id_list[0]"]=$g['uid']; 
        	$count=0;
        }
     
        $api_key = "36116967d1ab95321b89df8223929b14207b72b1";       
        $data["api_key"]=$api_key;
        $data["auth_token"]="$token";
        $data["attr_list[0]"]="true";
        $data["eid"]="$eid[1]";
        //$data["id_list[0]"]="4@3";
        //$data["id_list[1]"]="6@3";
        //$data["id_list[0]"]="null";
        $pc= $elggclient->post('/rest/json/?method=ldap.web.search',$data);
       // echo json_encode($pc);
        //exit;
        //$tree['children'][0] = $ictWS->createTreeData($pc['result']['0']['data']);
        //$tree['children'][1] = $ictWS->createTreeData($pc['result']['1']['data']);
        if($count>1){
        for($i=0;$i<$count;$i++){
        	$tree['children'][$i] = $ictWS->createTreeData($pc['result'][$i]['data']);
        }
        $tree['text']="";
        }else{
        	$tree = $ictWS->createTreeData($pc['result'][0]['data']);
        }
        $tree['isExpand'] = true;
        $return['status'] = 1;
        $return['tree'] = ["data"=>[$tree],'ajaxType'=>"get"];
        echo json_encode($return);
        exit;       
    }
    public function actionContactzp(){
    	$ictWS = new IctWebService();
    	//$contacts= $ictWS->getICTContacts();
    	require_once(dirname(__FILE__)."/config.php");
    	$connection = Yii::$app->db;
    	$connection->open();  //初始化数据库
    	header("Content-type:text/html;charset=utf-8");
    	$uid=\Yii::$app->session['user.uid'];
    	//$uid="7@3";
    	$eid=explode('@',$uid);
    	$data=array();
    	$sq0="SELECT officeAddress FROM memberlist WHERE guid='".$uid."'";
    	$command = $connection->createCommand($sq0);
    	$info = $command->queryOne();
    
    		$sq0="SELECT oid FROM memberlist WHERE guid='".$uid."'";
    		$command = $connection->createCommand($sq0);
    		$info = $command->queryOne();
    		$oid=$info['oid'];
    		$sq0="SELECT level,parentID FROM organization WHERE oid='".$oid."'";
    		$command = $connection->createCommand($sq0);
    		$g = $command->queryOne();
    		if($g['level']==3){
    			$oid=$g['parentID'];
    		}
    		$sq0="SELECT uid FROM organization WHERE oid='".$oid."'";
    		$command = $connection->createCommand($sq0);
    		$g = $command->queryOne();
    		$data["id_list[0]"]=$g['uid'];    	
    	   	 
    	$api_key = "36116967d1ab95321b89df8223929b14207b72b1";
    	$data["api_key"]=$api_key;
    	$data["auth_token"]="$token";
    	$data["attr_list[0]"]="true";
    	$data["eid"]="$eid[1]";
    	$data["id_list[0]"]="4@3";
    	//$data["id_list[1]"]="6@3";
    	//$data["id_list[0]"]="null";
    	$pc= $elggclient->post('/rest/json/?method=ldap.web.search',$data);    
    		$tree = $ictWS->createTreeData($pc['result'][0]['data']);   
    	$tree['isExpand'] = true;
    	$return['status'] = 1;
    	$return['tree'] = ["data"=>[$tree],'ajaxType'=>"get"];
    	echo json_encode($return);
    	exit;
    }
    public function actionContactss(){    	
    	$tree['text'] = "55";
		$tree['isExpand'] = true;
		$count="2";
		for($i=0;$i<$count;$i++){
				$tree['children'][$i]['text'] = "77";
		        $tree['children'][$i]['id'] = "99";
		        $tree['children'][$i]['isExpand'] = false;
		     
		        $tree['children'][$i]['photo'] = "";
		        $tree['children'][$i]['icon'] = "";
		        $tree['children'][$i]['mobile'] = "77";			 
		}
	
		$return['status'] = 1;
		$return['tree'] = ["data"=>[$tree],'ajaxType'=>"get"];
	
    	echo json_encode($return);
    	//echo json_encode($eid);
    	exit;    
    }
}
