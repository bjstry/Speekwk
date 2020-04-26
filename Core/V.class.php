<?php
/*
 * 视图基类，继承smaarty
 */
class V extends Smarty{
	//-----初始化相关设置
	function __construct(){
		parent::__construct();
		$this->caching = C('DT_CACHE');
		$this->template_dir = C('PRJ_VDIR');
		$this->compile_dir = C('PRJ_VCDIR');
		$this->cache_dir = C('PRJ_VCACHE');
		$this->left_delimiter = C('DT_V_LEFT');
		$this->right_delimiter = C('DT_V_RIGHT');
	}
}
?>
