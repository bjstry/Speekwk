<?php
/*
 * 核心类Speek文件
 * 
**/
class Speek{
	public static function Run(){
		spl_autoload_register('Speek::AutoLoad');   //注册自动加载类函数
		Speek::LoadConf();                          //加载核心配置、用户配置和公共函数库
		Speek::CreateDir();                         //创建必要目录
		set_error_handler('Speek::GetError');       //注册错误处理函数
		Speek::GetCm();                             //定位控制器
		Speek::CreateFile($_GET['c'],$_GET['m']);   //创建初始化类文件
		Speek::LoadFile($_GET['c'],$_GET['m']);     //启动控制器，控制器接管程序
	}
	//-----自动加载类方法
	static function AutoLoad($c){
		include_once SYS_CORE.$c.CEXT;	
	}
	//-----错误捕捉函数，页面输出和文件输出
	public static function GetError($errno,$errstr,$errfile,$errline,$errcont){
		$err = date("Y-m-d H:i:s ").$errline." 行 [$errno] $errstr 在文件 $errfile 中\r\n";
		error_log($err,3,C('PRJ_LOG').'error.log');
		$err_echo = date("Y-m-d H:i:s ").$errline."行 [$errno] $errstr 在文件 $errfile 中<br />";
		if(C('DEBUG'))
			echo $err_echo;
		if($errno == 256 || $errno == 4096){
			echo $err_echo;
			exit();
		}
	}
	//-----创建相关目录
	private static function CreateDir(){
		if(!file_exists(PRJ)) mkdir(PRJ);
		if(!file_exists(C('PRJ_CDIR'))) mkdir(C('PRJ_CDIR'));
		if(!file_exists(C('PRJ_MDIR'))) mkdir(C('PRJ_MDIR'));
		if(!file_exists(C('PRJ_VDIR'))) mkdir(C('PRJ_VDIR'));
		if(!file_exists(C('PRJ_VDIR').C('DT_THEME'))) mkdir(C('PRJ_VDIR').C('DT_THEME'));
		if(!file_exists(C('PRJ_VCDIR'))) mkdir(C('PRJ_VCDIR'));
		if(!file_exists(C('PRJ_VCACHE'))) mkdir(C('PRJ_VCACHE'));
		if(!file_exists(C('PRJ_COM'))) mkdir(C('PRJ_COM'));
		if(!file_exists(C('PRJ_CONF'))) mkdir(C('PRJ_CONF'));
		if(!file_exists(C('PRJ_LOG'))) mkdir(C('PRJ_LOG'));
		defined('THEME') or define('THEME',_P_.'/'.C('DT_THEME'));
	}
	//-----定位控制器
	private static function GetCm(){
		$path = null;
		if(C('DT_URLTYPE')==1){
			$_GET['c'] = !empty($_GET['c'])?$_GET['c']:C('DT_CONTROLLER');
			$_GET['m'] = !empty($_GET['m'])?$_GET['m']:C('DT_ACTION');
		}else if(C('DT_URLTYPE')==2){
			if(empty($_SERVER['PATH_INFO'])){
				$_GET['c'] = C('DT_CONTROLLER');
				$_GET['m'] = C('DT_ACTION');
			}else{
				$path = explode('/',trim($_SERVER['PATH_INFO']));
				$_GET['c'] = !empty($path[1])?$path[1]:'';
				$_GET['m'] = !empty($path[2])?$path[2]:'';
			}
		}else if(C('DT_URLTYPE')==3){
			if(!empty($_SERVER['PATH_INFO'])){
				$path = explode('/',trim($_SERVER['PATH_INFO']));
				$c = !empty($path[1])?$path[1]:'';
				$m = !empty($path[2])?$path[2]:'';
			}
			$_GET['c'] = !empty($_GET['c'])?$_GET['c']:C('DT_CONTROLLER');
			$_GET['m'] = !empty($_GET['m'])?$_GET['c']:C('DT_ACTION');
			$_GET['c'] = !empty($c)?$c:$_GET['c'];
			$_GET['m'] = !empty($m)?$m:$_GET['m'];
			
		}
		$_GET['c'] = ucwords($_GET['c']);
		$_GET['m'] = ucwords($_GET['m']);
		$method = explode(C('DT_V_EXT'),$_GET['m']);
		if(count($method)>1){
			$_GET['m']=$method[0];
		}
	 	$path_len = count($path);
		for($i=0;$i<$path_len;$i+=2){
			if($i>3){
				$_GET[$path[$i-1]] = $path[$i];
			}
		}
	}
	//-----加载主配置文件、用户配置文件和用户公共函数库
	private static function LoadConf(){
		if(is_file(SYS_CONF.'Config'.EXT)){
			C(include(SYS_CONF.'Config'.EXT));
			if(is_file(C('PRJ_CONF').'Config'.EXT)){
				C(array_change_key_case(include(C('PRJ_CONF').'Config'.EXT),CASE_UPPER)+include(SYS_CONF.'Config'.EXT));
			}else{
				C(include(SYS_CONF.'Config'.EXT));
			}
		}
		if(is_file(C('PRJ_COM').'functions'.EXT)){
			include C('PRJ_COM').'functions'.EXT;
		}
		C('REWRITE')?define('R',ROOT):define('R',URL);
	}
	//-----创建初始化文件
	private static function CreateFile($c,$m){
		$cfile = C('PRJ_CDIR').C('DT_CONTROLLER').C('DT_C_NAME').CEXT;
		$vfile = C('PRJ_VDIR').C('DT_THEME').'/'.C('DT_CONTROLLER').C('DT_ACTION').C('DT_V_EXT');
		if(!file_exists($cfile)){
			$file = fopen($cfile,'w');
			$content = "<?php\nclass ".ucfirst($c).C('DT_C_NAME')." extends ".C('DT_C_NAME')."{\n\tpublic function ".ucfirst($m)."(){\n\t\t".'$this->display'."();\n\t}\n}";
			fwrite($file,$content);
			fclose($file);
		}
		if(!file_exists($vfile)){
			$file = fopen($vfile,'w');
			$content = "<p>Now, It's your turn to change the world.</p>";
			fwrite($file,$content);
			fclose($file);
		}
	}
	//-----加载控制器、控制器接管程序
	private static function LoadFile($c,$m){
		$method=null;
		$cfile = C('PRJ_CDIR').$c.C('DT_C_NAME').CEXT;
		if(!is_file($cfile)){
			exit($c.' 控制器不存在！');
		}else{
			include_once $cfile;
		}
		$class = $c.C('DT_C_NAME');
		if(!class_exists($class)){
			exit('控制器未定义！');
		}
		$pram = new $class;
		if(!method_exists($pram,$m)){
			exit('接口未定义！');
		}
		if(is_callable('globeinit')){
			globeinit();
		}
		$pram->$m();
	}
}
?>
