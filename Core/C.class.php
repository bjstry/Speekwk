<?php
/*
 * 控制器基类
 */
class C{
	protected $view = null;
	protected $_arrinit;
	//protected $model = null;
	public function __construct(){
		$this->view = new V();
		if(empty($this->_arrinit)){
			$this->Speekinit();
		}else{
			if(!in_array($_GET['m'],$this->_arrinit)){
				$this->Speekinit();
			}
		}
	}
	public function Speekinit(){
		//-----预加载函数
	}
	//-----模版参数传递函数
	protected function assign($a=null,$b=null){
		if(is_array($a)){
			if($b==null){
				//echo 'ok';
				$this->view->assign($a);
			}else{
				exit('参数非法,多余的参数:'.$b);
			}
		}else{
			if($a==null){
				exit('参数非法:'.$a.' 不能为空');
			}else{
				$this->view->assign($a,$b);
			}
		}
	}
	//-----模版调用函数，自动插入头文件和foot文件
	protected function display($a=null,$b=null,$c=null){
		if(is_file(C('PRJ_VDIR').C('DT_THEME').'/theme/head'.C('DT_V_EXT')))
			//echo C('PRJ_VDIR').C('DT_THEME').'/theme/head'.C('DT_V_EXT');
			$this->view->display(C('PRJ_VDIR').C('DT_THEME').'/theme/head'.C('DT_V_EXT'));
		if(is_file(C('PRJ_VDIR').C('DT_THEME').'/theme/'.$_GET['c'].C('DT_V_EXT')))
				if(empty($b)){
					$this->view->display(C('PRJ_VDIR').C('DT_THEME').'/theme/'.$_GET['c'].C('DT_V_EXT'));
				}
		$type = empty($a)?1:0;
		if($type == 1){
			$err = is_file(C('PRJ_VDIR').C('DT_THEME').'/'.$_GET['c'].$_GET['m'].C('DT_V_EXT'))?$this->view->display(C('PRJ_VDIR').C('DT_THEME').'/'.$_GET['c'].$_GET['m'].C('DT_V_EXT'),$b,$c):1;
			if($err==1){
				exit(C('PRJ_VDIR').C('DT_THEME').'/'.$_GET['c'].$_GET['m'].C('DT_V_EXT').'- 模板文件不存在!');
			}
		}else{
			$err = is_file(C('PRJ_VDIR').C('DT_THEME').'/'.$a.C('DT_V_EXT'))?$this->view->display(C('PRJ_VDIR').C('DT_THEME').'/'.$a.C('DT_V_EXT'),$b,$c):1;
			if($err==1){
				exit(C('PRJ_VDIR').C('DT_THEME').'/'.$a.C('DT_V_EXT').'- 模板文件不存在!');
			}
		}
		if(is_file(C('PRJ_VDIR').C('DT_THEME').'/theme/footer'.C('DT_V_EXT')))
			//echo C('PRJ_VDIR').C('DT_THEME').'/theme/footer'.C('DT_V_EXT');
			$this->view->display(C('PRJ_VDIR').C('DT_THEME').'/theme/footer'.C('DT_V_EXT'));
	}
	//-----调转函数
	protected function url($a=null,$b=null){
		if(is_null($b)){
			echo "<script>alert('$a')</script>";
			echo "<script>history.go(-1)</script>";
		}else{
			echo "<script>alert('$a')</script>";
			echo "<script>location.href='$_SERVER[SCRIPT_NAME]$b'</script>";
		}
	}
}
?>
