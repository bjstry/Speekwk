<?php
/*
 * 系统公共函数库，全局调用
 */
 
 //-----saveConfig 方法 保存配置值到配置文件中
function saveConfig($file=null){
	if(!empty($file)){
		if(file_put_contents($file,"<?php \nreturn ".var_export(array_change_key_case(C(),CASE_UPPER),true).";\n?>")){
			//...
		}else{
			exit('操作失败!1');
		}
	}else{
		if(file_put_contents(C('PRJ_CONF').'Config.php',"<?php \nreturn ".var_export(array_change_key_case(C(),CASE_UPPER),true).";\n?>")){
			//echo '保存成功！';
		}else{
			exit('操作失败!');
		}
	}
}
 //-----C方法 获取和设置配置
function C($name=null,$value=null){
	static $_conf = array();
	if(empty($name)){
		return $_conf;
	}
	if(is_string($name)){
		if(!strpos($name,'.')){
			//$name=strtolower($name);
			if(is_null($value))
				return isset($_conf[$name])?$_conf[$name]:null;
			$_conf[$name]=$value;
			//echo $_conf[$name];
			return;
		}
		$name = explode('.',$name);
		$name[0]=strtolower($name[0]);
		if(is_null($value))
			return isset($_conf[$name[0]][$name[1]])?$_conf[$name[0]][$name[1]]:null;
		$_conf[$name[0]][$name[1]]=$value;
		return;
	}
	if(is_array($name)){
		return $_conf = array_change_key_case($name,CASE_UPPER) + $_conf;
	}
	return null;
}

//-----M方法:生成带数据库操作的空模块
function M($a){
	$obj = new M();
	$obj->init($a);
	return $obj;
}

//-----D方法:模块加载
function D($a,$b=null){
	if(empty($a)){
		echo '模块名未定义！';
		exit();
	}
	$modelfile = C('PRJ_MDIR').ucwords($a).C('DT_M_NAME').CEXT;
	if(!is_file($modelfile)){
		echo '模块:'.$a.'-不存在！';
		exit();
	}else{
		include_once $modelfile;
	}
	$class = $a.C('DT_M_NAME');
	if(!class_exists($class)){
		die('模块:'.$a.'-未定义!');
		exit();
	}
	if($b == null){
		$obj = new $class;
	}else{
		$obj = new $class($b);
	}
	$obj->init($a);
	return $obj;
}

//-----session方法：设置或获取session值
function session($a=null,$b=null){
	//如果第二个参数为空则表示取值
	if(is_null($b)){
		$resut = isset($_SESSION[$a])?$_SESSION[$a]:null;
		return $resut;
	}

	//如果第二参数有非空值则设置值
	$_SESSION[$a] = $b;

	//如果第二参数位Null,则注销该session值
	if($b == 'null')
		unset($_SESSION[$a]);

	//如果第一个参数如clean,第二个参数为null则注销整个session
	if($a == 'clean' and $b == 'all')
		session_destroy();
}
class SpeekFrameWorkSqlite3DB extends SQLite3{
	function __construct($name){
		 $this->open(C('PRJ_COM').$name.'.db');
	}
}
function SQ($name){
	$db = new SpeekFrameWorkSqlite3DB($name);	
	return $db;
}
function VerifySession($a,$b){
	$return = false;
	if(isset($_SESSION[$a])){
		if($a == $b){
			$return = true;
		}else{
			$return = false;
		}
	}
	return $return;
}
function Redircet($text='非法访问！',$type='danger',$url='/index.php'){
	echo "<script>UIkit.notify({message:'".$text."',status:'".$type."',timeout:2000,pos:'top-center'});setTimeout(\"location.href='".$url."'\",2000);</script>";
}
function getFieldValue($table,$key,$rekey){
	$tablerow = M($table);
	$return = $tablerow->where("$key[0]='$key[1]'")->find($rekey);
	return $return[$rekey];
}
?>
