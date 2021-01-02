<?php
error_reporting(0);
date_default_timezone_set('PRC'); 
#定义WEB物理路径
define('JDir' , dirname(__DIR__));

#等级
$jlevel=3;
#日志功能
$log_status=1;
#waf功能
$waf_status=1;
#定义日志路径(默认为web路径下jlog.txt)
$log_path=JDir.'/jlog.txt';

function JLog(){
	global $log_path;
	$ip=$_SERVER['REMOTE_ADDR'];
	$getdata=$_SERVER['REQUEST_URI'];
	$postdata=file_get_contents('php://input');
	$cookiedata=$_SERVER['HTTP_COOKIE'];
	$path=$_SERVER['PHP_SELF'];
	$times=@date('Y-m-d H:i:s');
	$data="$ip - - $times ";

	if($getdata)$data.='URL '.$getdata;

	if($postdata)$data.=' POST '.$postdata;
	
	if($cookiedata)$data.=' 		COOKIE '.$cookiedata;
	
	if(!empty($_FILES)){
		foreach($_FILES as $k=>$v){
			$data.=' UploadFile '.trim($_FILES[$k]['name']).' ';
		}
	}
	file_put_contents($log_path,"$data\n",FILE_APPEND);
}

function addslashes_deep($string){
	global $jlevel;
	if (empty($string)){
		return $string;
	}elseif($jlevel>=2){
		#等级二替换规则可自行修改
		$math='/select\b|insert\b|flag\b|union\b|<\?\b|\?>|update\b|drop\b|and\b|delete\b|dumpfile\b|outfile\b|load_file|rename\b|`|\.\/|floor\(|extractvalue|updatexml|name_const|multipoint\(|base64_decode|eval\(|assert\(|file_put_contents|fwrite|curl|system|passthru|exec|system|chroot|scandir|chgrp|chown|shell_exec|proc_open|proc_get_status|popen|ini_alter|ini_restorei/i';
		return is_array($string) ? array_map('addslashes_deep', $string) : preg_replace($math,'_',$string);
	}elseif($jlevel>=4){
		return is_array($string) ? array_map('addslashes_deep', $string) : preg_replace('/[^\w]/','',$string);
	}else{
		return is_array($string) ? array_map('addslashes_deep', $string) : htmlspecialchars(addslashes(stripslashes($string)));
	}
}

function JWaf(){
	global $jlevel;
	if($jlevel>=1){
		if(!empty($_GET)) $_GET = addslashes_deep($_GET);
		if(!empty($_POST)) $_POST = addslashes_deep($_POST);
		if(!empty($_REQUEST)) $_REQUEST = addslashes_deep($_REQUEST);
		if(!empty($_COOKIE)) $_COOKIE = addslashes_deep($_COOKIE);
		if(!empty($_SERVER)) $_SERVER = addslashes_deep($_SERVER);
	}
	
	if($jlevel>=3){
		if(!empty($_FILES)) {
			foreach($_FILES as $k=>$v){
				#修改上传文件名防止sql注入
				$_FILES[$k]['name']=mt_rand().'.jpg';
				#修改临时文件名为空使其上传失败
				$_FILES[$k]['tmp_name']='';
			}
		}
	}
}

#调用日志
if($log_status) JLog();

if($waf_status){
	#等级四
	if($jlevel>=4){
		foreach(get_defined_vars() as $k=>$v)
		{
			$$k=addslashes_deep($$k);
		}
	}
	JWaf();
}
?>
