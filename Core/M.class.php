<?php
/*
 * 模块基类
 */
class M{
	protected $PDO;
	protected $table = null;    //存储表名
	protected $where = null;    //存储定义条件
	protected $limit = null;    //存储定义条件
	protected $order = null;    //存储定义排序
	protected $primary = null;  //存储定义排序
	protected $_verarr;         //存储自动验证数组
	protected $orderType = 'desc';  //排序类型
	public $_data;           //数据库结构映射

	//-----初始化数据库-----//
	public function init($a){
		if(is_array($a)){
			//print_r($a);
			//-----使用数组自定义连接数据库
			$this->table = !empty($a['table'])?C('DB_PREFIX').strtolower($a['table']):'';
			$this->connect($a['host'],$a['dbname'],$a['user'],$a['pass']);
		}else{
			//-----调用配置文件连接数据库
			$this->table = C('DB_PREFIX').strtolower($a);
			$this->connect(C('DB_HOST'),C('DB_NAME'),C('DB_USER'),C('DB_PASS'));
			#mysql_select_db(C('DB_NAME')) or die('选择数据库失败！ - '.mysql_error());
			$redatare = $this->query("desc ".$this->table);
			while($redata = $redatare->fetch()){
				$this->_data['field'][] = $redata['Field'];
				$this->_data['type'][] = $redata['Type'];
				$this->_data['extra'][] = $redata['Extra'];
			}
		}
		$this->getId();
	}
	//---测试函数：输出主键---//
	public function sayId(){
		echo $this->primary;
	}
	//---代替自带mysql_query---//
	public function query($sql){
		return $this->PDO->query($sql);
	}
	//---获取主键---//
	protected function getId(){
		$query = $this->query("desc $this->table");
		while($row = $query->fetchAll()){
			if($row['Key']=='PRI'){
				$this->primary = $row['Field'];
			}
		}
	}
	//---调用配置文件连接数据库---//
	protected function connect($servername,$dbname,$username,$password){
		try {
			$this->PDO = new PDO("mysql:host=$servername;dbname=$dbname;port=3306", $username, $password);
		}catch(PDOException $e){
			echo $e->getMessage();
		}
		#mysql_connect(C('DB_HOST'),C('DB_USER'),C('DB_PASS')) or die('连接数据库失败！ - '.mysql_error());
		$this->query('set names '.C('DT_CHARSET'));
	}

	//---追加处理条件---//
	public function where($a=null){
		$next;
		if(is_array($a)){
			$next = 'array';
		}else{
			$next = ' where '.$a;
		}
		$this->where = $next;
		$obj = $this;
		return $obj;
	}
	//---追加设置table---//
	public function table($a=null){
		$this->table = C('DB_PREFIX').strtolower($a);
		$obj = $this;
		return $obj;
	}
	//---设置排序类型---//
	public function orderType($a=null){
		if(!$a==null){
			$this->orderType = 'asc';
		}
		$obj = $this;
		return $obj;
	}
	//---追加排序条件---//
	public function order($a=null){
		if($a == null){
			$a = $this->primary;
		}
		if(is_array($a)){
			$this->orderType = $a[1];
			$next = " order by ".$a[0]." ".$this->orderType;
			$this->order = $next;
			$obj = $this;
		}else{
			$next = " order by ".$a." ".$this->orderType;
			$this->order = $next;
			$obj = $this;
		}
		return $obj;
	}

	//---单条查询---//
	public function find($a=null){
		$sql=null;
		$row=null;
		if(is_null($a)){
			$a = ' * ';
		}else{
			$a = ' `'.$a.'` ';
		}
		if(!is_null($this->where))
			$sql = $this->where;
		if(!is_null($this->order))
			$sql.=$this->order;
		$sqltrl = 'select'.$a.'from '.$this->table.$sql.' limit 1';
		$query = $this->query($sqltrl);
		if(empty($query)){
			die('Find error - '.mysqli_error().'<br>SQL : '.$sqltrl);
		}else{
			$row = $query->fetch();
		}
		//if(empty($row))
			//echo '<br>SQL : '.$sqltrl;
		return $row;
	}
	//---查询所有---//
	public function findAll(){
		return $this->order()->select();
	}	
	//---多条查询函数---//
	public function select($a=null,$b=null){
		//--$a 查询数量，默认查询所有--//
		//--$b 查询子段，默认为*通配符--//
		$sql=null;  //查询语句
		$num=null;  //查询数量
		$row=null;  //存储查询结果的二维数组
		$num = !is_null($a)?' limit '.$a:'';
		if(is_null($b)){
			$b = ' * ';
		}else{
			$b = ' `'.$b.'` ';
		}
		//--获取查询条件--//
		if(!is_null($this->where))
			$sql = $this->where;
		//--获取查询排序--//
		if(!is_null($this->order))
			$sql.=$this->order;
		$sql = 'select'.$b.'from '.$this->table.$sql.$num;
		$query = $this->query($sql) or die('Select error - '.mysql_error().'<br>SQL : '.$sql);
		while($srow = $query->fetch()){
			$row[]=$srow;
		}
		return $row; //返回查询结果二维数组
	}
	//---插入函数---//
	public function insert($a=null,$b=null){
		$sql=null;
		if(is_null($b)){
			$sql = "insert into `$this->table` values ($a)";
		}else{
			$sql = "insert into `$this->table` ($a) values ($b)";
		}
		$this->verify();
		$query = $this->query($sql) or die('Insert error - '.mysql_error().'<br>SQL : '.$sql);
		return $query->lastInsertId();
	}
	//---更新函数---//
	public function update($a=null){
		$where = $this->where;
		$sql = "update `$this->table` set $a$where";
		$query = $this->query($sql) or die('Update error - '.mysql_error().'<br>SQL : '.$sql);
		return $query;
	}
	//---删除函数---//
	public function delete(){
		$where = $this->where;
		$sql = "delete from `$this->table` $where";
		$query = $this->query($sql) or die('Delete error - '.mysql_error().'<br>SQL : '.$sql);
		return $query;
	}
	//---自动验证函数---//
	protected function verify(){
		foreach($this->_verarr as $arr){
			switch($arr[1]){
				case 1:
					if($_POST[$arr[0]] == null){
						die($arr[2]);
					}
					break;
				case 2:
					if($_POST[$arr[0]] < 0){
						die($arr[2]);
					}
			}
		}
	}
	//---统计数据条数---//
	public function count($where=null){
		if(!$where==null){
			$this->where = " where ".$where;
		}
		$row = null;
		$row = $this->query("select count(*) from $this->table".$this->where)->fetch();
		return $row[0];
	}
	//---SESSION会话验证---//
	public function Session_Verify(){
		if(false){
		}else{
			die('非法会话！');
		}
	}

	//--分页函数--//
	/*
		$count 数据总条数
		$nums  单页数据条数
		$key   分页GET变量名
		$val   分页GET值，当前页
		$url   当前页面地址或者分页页面地址
	**/
	public function pageint($count=null,$nums=null,$key=null,$val=null,$pgkey=null){
		if($count == null){
			$count = $this->count();
		}
		if($nums == null){
			$nums = C('PAGE_NUM');
		}
		if($key == null){
			$key = 'page';
		}
		if($val == null){
			if(empty($_GET[$key])){
				$_GET[$key] = 1;
			}
			$val = $_GET[$key];
		}
		if($pgkey == null){
			$pgkey = 'active';
		}
		$result;  //返回数组
		$pgstatus; //当前页状态
		//获取分页数
		$pages = ceil($count/$nums);
		//获取前置后置状态
		if($val == 1 ){
			$result['pre']['url'] = '';
			$result['nex']['url'] = '';
			if($val < $pages){
				$result['nex']['url'] = $val + 1;
			}
		}else{
			$result['pre']['url'] = $val - 1;
			$result['nex']['url'] = '';
			if($val < $pages){
				$result['nex']['url'] = $val + 1;
			}
		}
		//获取分页主体
		for($i=1;$i<=$pages;$i++){
			if($pages > 5){
				if($i <= $val+2 and $i >= $val-2){
					if($i == $val){
						$pgstatus = $pgkey;
					}else{
						$pgstatus = '';
					}
					$result['con'][] = array($pgstatus,$i);
				}
			}else{
				if($i == $val){
					$pgstatus = $pgkey;
				}else{
					$pgstatus = '';
				}
				$result['con'][] = array($pgstatus,$i);	
			}
		}

		//返回分页数组
		return $result;
	}
	public function page($count=null,$nums=null,$key=null,$val=null,$url=null){
		//总条数，为空则自动统计数据库内总条数
		if($count == null){
			//$jin = $this->fetch($this->query("select count(*) from $this->table".$this->where));
			//$count = $jin[0];
			$count = $this->count();

		}
		//单页条数为空则调用配置文件 默认为15 
		if($nums==null){
			$nums=C('PAGE_NUM');
		}
		//分页GET变量 默认为pid
		if($key==null){
			$key = 'pid';
		}
		//当前页码，默认自动从GET获取
		if($val==null){
			$val = $_GET['pid'];
		}
		//分页URL地址，默认自动获取
		$url = empty($url)?$_SERVER['SCRIPT_NAME'].'/'.$_GET['c'].'/'.$_GET['m'].'':$url; //获取url
		$rtarr;           //返回数组
		$nowpage = 1;     //默认当前页
		$leftstatus = 0;  //上一页是否激活
		$rightstatus = 1; //下一页是否激活
		$pagenums = 4;    //最多显示页码数
		$pages = ceil($count/$nums); //总页数
		$content = null;  //页码部分
		$activeclass = 'uk-active'; //选中页css类
		if($val > 1 && $val < $pages+1){
			$nowpage = $val;
			$leftstatus = 1;
			if($val == $pages){
				$rightstatus = 0;
			}
		}
		$leftout = !$leftstatus?"class='uk-disabled'":"";	
		$rightout = !$rightstatus?"class='uk-disabled'":"";	
		$rows = $this->select(($nowpage-1)*$nums.",$nums");
		for($i=1;$i<=$pages;$i++){
			if($pages <= $pagenums){
				if($i == $nowpage){
					$content.="<li class='$activeclass'><span>$i</span></li>";
				}else{
					$content.="<li><a href='$url/$key/$i'>$i</a></li>";
				}
			}else{
				if($i == $nowpage-1 or $i == $nowpage+1){
					$content.="<li><a href='$url/$key/$i'>$i</a></li>";
				}else if($i == $nowpage){
					$content.="<li class='$activeclass'><span>$i</span></li>";
				}else if($i == $pages){
					$content.="<li><span>...</span></li><li><a href='$url/$key/$i'>$i</a></li>";
				}
			}
		}
		if($nowpage == 1){
			$pageout = "
			<ul class='uk-pagination'>
				<li $leftout><span><i class='uk-icon-angle-double-left'></i></span></li>
				".$content."
				<li $rightout><a href='$url/$key/".($nowpage+1)."'><i class='uk-icon-angle-double-right'></i></a></li>
				<li><a href='$url'>返回</a></li>
			</ul>";
		}else if($nowpage == $pages){
			$pageout = "
			<ul class='uk-pagination'>
				<li $leftout><a href='$url/$key/".($nowpage-1)."'><i class='uk-icon-angle-double-left'></i></a></li>
				".$content."
				<li $rightout><span><i class='uk-icon-angle-double-right'></i></span></li>
				<li><a href='$url'>返回</a></li>
			</ul>";
		}else{
			$pageout = "
			<ul class='uk-pagination'>
				<li $leftout><a href='$url/$key/".($nowpage-1)."'><i class='uk-icon-angle-double-left'></i></a></li>
				".$content."
				<li $rightout><a href='$url/$key/".($nowpage+1)."'><i class='uk-icon-angle-double-right'></i></a></li>
				<li><a href='$url'>返回</a></li>
			</ul>";
		}
		$rtarr = array($rows,$pageout);
		return $rtarr;
	}
	public function Login($arr=null){
		if($arr == null){
			$arr = $_POST;
		}
	}
}
?>
