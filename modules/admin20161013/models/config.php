<?php
namespace app\modules\admin\controllers;
use Yii;
use yii\web\Controller;
use Rest;
use Client;
//$url="http://139.129.54.167";
//$url="http://192.168.139.54";
$url="http://182.92.96.36";
$api_key = "36116967d1ab95321b89df8223929b14207b72b1";
$elggclient = new Client($url."/elgg/services/api/");
$token= $elggclient->post('/rest/json/?method=auth.gettoken',array("name"=>"buliping","password"=>"123456","flag"=>"1","api_key"=>$api_key));
$token=$token['result']['auth_token'];
