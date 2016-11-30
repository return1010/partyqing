<?php
namespace app\modules\admin\controllers;
use Yii;
use app\modules\admin\models\IctWebService;
use app\modules\admin\models\AuthToken;


class TriptipController extends \yii\web\Controller
{
	public $enableCsrfValidation = false;//yii默认表单csrf验证，如果post不带改参数会报错！
    public $layout  = false;
//    public $layout  = 'announce';
//    public $layout  = 'layout';

    
    public function actionIndex()
    {    
    	$auth = new AuthToken();
    	$auth->authTokenSession();
    	//$childuid=\yii::$app->request->get('cid');  //孩子
    	
    	$connection = Yii::$app->db;
    	$connection->open();  //初始化数据库
    	header("Content-type:text/html;charset=utf-8");
    	$sq0="SELECT * FROM djbusiness_state";
    	$command = $connection->createCommand($sq0);
    	$state = $command->queryAll();
    	$count=count($state);
    	\Yii::$app->session['public_count']=$count;
        return $this->render('index',[
        		'list' => $state,
        		'count' =>$count,
        		//'pic' => $path,
        ]);
    }
    public function actionSearch(){
    
       	$searchtitle=\yii::$app->request->get('searchtitle');
    	\Yii::$app->session['public_count']=25;
    
    	$connection = Yii::$app->db;
    	$connection->open();  //初始化数据库
       	 
     
    	$sq0="SELECT * FROM djbusiness_state WHERE statename like '%".$searchtitle."%'";
    	$command = $connection->createCommand($sq0);
    	$state = $command->queryAll();
    	$count=count($state);
    	
    	\Yii::$app->session['search_count']=$count;
    	//file_put_contents("log.log","num:". $sq2."\n", FILE_APPEND);
 
    	echo json_encode($state);
    	exit();
    }
    public function actionGetdata(){
    	 
    	//$uid =\Yii::$app->session['user.uid'];
    	//\Yii::$app->session['public_count']="5";
    	$searchcontent=\yii::$app->request->get('searchcontent');   	 
    	$connection = Yii::$app->db;
    	$connection->open();  //初始化数据库

    	$public_count=\Yii::$app->session['public_count'];
    	 
    	if(strlen($searchcontent)>0){
    		$search_count=\Yii::$app->session['search_count'];
    		$sq0="SELECT * FROM djbusiness_state WHERE statename like '%".$searchcontent."%' LIMIT ".$search_count.",25";   		 
    		$search_count=$search_count+25;
    		\Yii::$app->session['search_count']=$search_count;
    		 
    	}else{
    		$public_count=\Yii::$app->session['public_count'];
    		$sq0="SELECT * FROM djbusiness_state LIMIT ".$public_count.",25";    		
    		$public_count=$public_count+25;
    		\Yii::$app->session['public_count']=$public_count;
    	}
    
    	$command = $connection->createCommand($sq0);
    	$news = $command->queryAll();
    	//print_r($course);
    	//file_put_contents("D:\\wt1.txt","sum:".$sql."\n", FILE_APPEND);
    	\Yii::$app->session['searchcontent']=$searchcontent;
    	$count=count($news);
    	 
    	echo json_encode($news);
    	exit();
    }
}
