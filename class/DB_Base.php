<?php
////////////////////////////////////////
//DB基底クラス
//PDO接続
////////////////////////////////////////

class DB_Base
{
	protected static $instance = null;

	protected static $pdo = null;

	//「''」が必要なデータ型
	private $STR_DATE_TYPE = array(
		'date',
		'datetime',
		'timestamp',
		'time',
		'year',
		'char',
		'varchar',
		'tinytext',
		'text',
		'mediumtext',
		'longtext',
		'nchar',
		'enum');

	//エラーフラグ
	public $ERROR = TRUE;
	//エラーメッセージ
	public $ERROR_MSG = '初期処理未実行';

	protected $table_name;

	protected $table_model;

	protected $table_model_name = null;

	function __construct()
	{
		if(is_null(self::$pdo)){
			self::db_connect();
		}
	}

/*
	public static function getDBConnection()
    {
    	if(is_null(self::$instance)){
    		self::db_connect();
    		self::$instance = array();
    	}
        $key = get_called_class();
        if(!isset(self::$instance[$key]))
        {
            self::$instance[$key] = new static();
        }
        return self::$instance[$key];
    }
*/

	private static function db_connect(){

		$dsn = array(
			"dsn"      => _DB_TYPE.':dbname='._DB_NAME.';host='._DB_HOST,
			"phptype"  => _DB_TYPE,
			"username" => _DB_USER,
			"password" => _DB_PASS,
			"hostspec" => _DB_HOST,
			"database" => _DB_NAME,
		);

		self::$pdo = new PDO($dsn['dsn'], $dsn['username'], $dsn['password']);
		//静的プレースホルダ
		//self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		//フェッチモードを指定
		self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

	}

	// トランザクション処理---------
	public function beginTransaction(){
		self::$pdo->beginTransaction();
	}

	public function commit(){
		self::$pdo->commit();
	}

	public function rollBack(){
		self::$pdo->rollBack();
	}
	//------------------------------

	public function query($sql, $data = null){

		if(is_null($data)){
			$res = self::$pdo->query($sql);

		}elseif(!is_null($sql) and is_array($data)){
			$res = self::$pdo->prepare($sql);
			$i = 1;
			foreach($data as $value){
				if(gettype($value) === 'string'){
					$res->bindValue($i, $value, PDO::PARAM_STR);
				}else{
					$res->bindValue($i, $value, PDO::PARAM_INT);
				}
				$i++;
			}
			$res->execute();

		}else{
			return False;
		}

		$this->setFetchMode($res);
		$result = $res->fetchAll();

		if(empty($result)){
			return False;
		}

		return $result;
	}

	//insert,update,delete文
	public function execute($sql, $data = null){

		if(is_null($data)){
			return self::$pdo->exec($sql);

		}elseif(!is_null($sql) and is_array($data)){
			$res = self::$pdo->prepare($sql);
			$i = 1;
			foreach($data as $value){
				if(gettype($value) === 'string'){
					$res->bindValue($i, $value, PDO::PARAM_STR);
				}else{
					$res->bindValue($i, $value, PDO::PARAM_INT);
				}
				$i++;
			}
			$res->execute();

		}else{
			return False;
		}

		$result = $res->rowCount();

		if(empty($result)){
			return False;
		}

		return $result;
	}

	public function exec($sql){

		//処理件数を返す
		return self::$pdo->exec($sql);
	}

	//-------------------------------------------------
	//find([テーブル名]) 全件検索
	//find([テーブル名], $id) where id=$id
	//-------------------------------------------------
	public function find($table_name, $id = null, $orderby = null, $da = 'desc'){
		if(is_null($id)){
			return $this->fetchAll($table_name);
		}
		if(!is_numeric($id)){
			return false;
		}

		$sql = 'SELECT * FROM '.$table_name.' WHERE id = '.$id;
		if(!is_null($orderby)){
			$sql.= ' ORDER BY '.$orderby.' '.$da;
		}

		$res = self::$pdo->query($sql);

		$this->setFetchMode($res);
		return $res->fetchAll();
	}

	//---------------------------------------------------
	//insert($data)
	//$dataはテーブルモデルクラス
	//$data->name $data->note
	//idがセットされていたらupdate処理
	//---------------------------------------------------
	/* 2015/01/02変更
	public function insert($data){
		if(isset($data->id)){
			return $this->update($data);
		}

		$sql = '';
		$calumn = array();
		$values = array();
		//$schema = $this->table_model->_schema;
		$schema = call_user_func(array($this->table_model_name, 'getSchema'));
		foreach($schema as $culumn_name => $var_type){
			if(isset($data->$culumn_name) and $culumn_name !== 'id'){
				$calumn[] = $culumn_name;
				//if($var_type === 'dateTime'){
				//	$values[] = "'".$data->$culumn_name['date']."'";
				if($var_type === 'string' or $var_type === 'dateTime'){
					$values[] = "'".$data->$culumn_name."'";
				}else{
					$values[] = $data->$culumn_name;
				}
			}
		}
		$sql = 'INSERT INTO '.$this->table_name.' ( '.implode(', ', $calumn).' )';
		$sql .= ' VALUES ( '.implode(', ', $values).' )';

		$res = $this->pdo->query($sql);

		//失敗時False
		return $res;
	}
	*/

	//$data insert配列  $data = array('column_name' => value)
	public function insert($table_name, $data){
		if(isset($data['id'])){
			return $this->update($table_name, $data);
		}

		$sql = '';
		$calumn = array();
		$values = array();
		$binder = array();
		/*
		//$schema = call_user_func(array($this->table_model_name, 'getSchema'));
		foreach($schema as $culumn_name => $var_type){
			if(isset($data->$culumn_name) and $culumn_name !== 'id'){
				$calumn[] = $culumn_name;
				$values[] = $data->$culumn_name;
				$binder[] = '?';
			}
		}
		*/
		foreach($data as $culumn_name => $value){
			$calumn[] = $culumn_name;
			$values[] = $value;
			$binder[] = '?';
		}

		$sql = 'INSERT INTO '.$table_name.' ( '.implode(', ', $calumn).' )';
		$sql .= ' VALUES ( '.implode(', ', $binder).' )';

		$res = self::$pdo->prepare($sql);
		$i = 1;
		foreach($values as $value){
			if(gettype($value) === 'string'){
				$res->bindValue($i, $value, PDO::PARAM_STR);
			}else{
				$res->bindValue($i, $value, PDO::PARAM_INT);
			}
			$i++;
		}
		$flag = $res->execute();

		//失敗時False
		return $flag;
	}
	//---------------------------------------------------
	//update($data)
	//$dataはテーブルモデルクラス
	//$data->name $data->note
	//idがセットされていなかったらinsert処理
	//---------------------------------------------------
	/*2015/01/02変更
	public function update($data){
		if(!isset($data->id)){
			return $this->insert($data);
		}

		$sql = '';
		$sets = array();
		//$schema = $this->table_model->_schema;
		$schema = call_user_func(array($this->table_model_name, 'getSchema'));
		foreach($schema as $culumn_name => $var_type){
			if(isset($data->$culumn_name) and $culumn_name !== 'id'){
				if($var_type === 'string' or $var_type === 'dateTime'){
					$sets[] = $culumn_name." = '".$data->$culumn_name."'";
				}else{
					$sets[] = $culumn_name.' = '.$data->$culumn_name;
				}
			}
		}
		$sql = 'UPDATE '.$this->table_name.' SET '.implode(', ', $sets).' WHERE id = '.$data->id;

		$this->sql_log($sql);
		$res = $this->pdo->query($sql);

		//失敗時False
		return $res;
	}
	*/

	//$data update配列  $data = array('column_name' => value)
	public function update($table_name, $data){
		if(!isset($data['id'])){
			return $this->insert($data);
		}

		$sql = '';
		$sets = array();
		$values = array();
		/*
		//$schema = $this->table_model->_schema;
		//$schema = call_user_func(array($this->table_model_name, 'getSchema'));
		foreach($schema as $culumn_name => $var_type){
			if(isset($data->$culumn_name) and $culumn_name !== 'id'){
				$sets[] = $culumn_name.' = ?';
				$values[] = $data->$culumn_name;
			}elseif($culumn_name ==='update_date'){
				$sets[] = $culumn_name.' = ?';
				$values[] = date("Y-m-d H:i:s");
			}
		}
		*/
		foreach($data as $culumn_name => $value){
			if($culumn_name !== 'id'){
				$sets[] = $culumn_name.' = ?';
				$values[] = $value;
			}
		}
		$sql = 'UPDATE '.$table_name.' SET '.implode(', ', $sets).' WHERE id = '.$data['id'];

		$res = self::$pdo->prepare($sql);
		$i = 1;
		foreach($values as $value){
			if(gettype($value) === 'string'){
				$res->bindValue($i, $value, PDO::PARAM_STR);
			}else{
				$res->bindValue($i, $value, PDO::PARAM_INT);
			}
			$i++;
		}
		$flag = $res->execute();

		//失敗時False
		return $flag;
	}

	//全件取得
	public function fetchAll($table_name, $orderby = null, $da = 'desc') {

		$sql = 'SELECT * FROM '.$table_name;
		if(!is_null($orderby)){
			$sql.= ' ORDER BY '.$orderby.' '.$da;
		}

		$res = self::$pdo->query($sql);

		$this->setFetchMode($res);
		return $res->fetchAll();
	}
	
	//エラー情報取得
	public function getErrorInfo(){
		
		return self::$pdo->errorInfo();
	}

	private function setFetchMode(&$resObj)
	{
		if(is_null($this->table_model_name)){
			//匿名クラス
			$resObj->setFetchMode(PDO::FETCH_OBJ);
		}else{
			//テーブルモデルクラス
			$resObj->setFetchMode(PDO::FETCH_CLASS, $this->table_model_name);
		}
	}

}
