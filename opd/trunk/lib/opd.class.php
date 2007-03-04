<?php
	if(!defined('OPD_DIR'))
	{
		define('OPD_DIR', './');
	}

	define('OPD_VERSION', '1.0.0-dev');
	
	function opdErrorHandler(PDOException $exc)
	{
		echo '<br/><b>Open Power Driver internal error #'.$exc->getCode().': </b> '.$exc->getMessage().'<br/>
			Query used: <i>'.opdClass::$lastQuery.'</i><br/>';
	}

	class opdClass extends PDO
	{
		public $dsn;
		public $_debugConsole = false;
		public $debugShowQuery = true;
		public $persistent = false;
		public $cacheDirectory = '';
		public $charset = 'latin-2';
		private $user;
		private $password;
		private $attributes = array();

		private $connected = false;

		// Debug data
		private $i = 0;
		private $queries = array();
		private $counterExecuted = 0;
		private $counterRequested = 0;
		private $counterTime = 0;
		private $counterTimeExecuted = 0;
		private $transactions = 0;
		private $transactionsCommit = 0;
		private $transactionsRollback = 0;
		
		static public $lastQuery;

		private $consoleCode;

		public function __construct($dsn, $user, $password)
		{
			$this -> dsn = $dsn;
			$this -> user = $user;
			$this -> password = $password;
		} // end __construct();

		public function beginTransaction()
		{
			if(!$this -> connected)
			{
				$this -> makeConnection();
			}
			$this -> transactions++;
			return parent::beginTransaction();
		} // end beginTransaction();

		public function commit()
		{
			if($this -> connected)
			{
				$this -> transactionsCommit++;
				return parent::commit();
			}
			return false;
		} // end commit();

		public function errorCode()
		{
			if($this -> connected)
			{
				return parent::errorCode();
			}
			return 0;
		} // end errorCode();


		public function exec($query, $id = NULL)
		{
			if(!$this -> connected)
			{
				$this -> makeConnection();
			}
			if(!is_null($id))
			{
				if(is_integer($id))
				{
					$this -> startDebug($query);
					$stmt = parent::prepare($query);
					$stmt -> bindValue(':id', $id, PDO::PARAM_INT);
					$result = $stmt -> _rawExecute();
					$this -> endDebug();
				}
				else
				{
					$this -> startDebug($query);
					$stmt = parent::prepare($query);
					$stmt -> bindValue(':name', $id, PDO::PARAM_STR);
					$result = $stmt -> _rawExecute();
					$this -> endDebug();
				}
			}
			else
			{
				$this -> startDebug($query);
				$result = parent::exec($query);
				$this -> endDebug();
			}
			return $result;
		} // end exec();

		public function getAttribute($attribute)
		{
			if(!$this -> connected)
			{
				$this -> makeConnection();
			}

			return parent::getAttribute($attribute);
		} // end getAttribute();

		public function lastInsertId($name = NULL)
		{
			if($this -> connected)
			{
				return parent::lastInsertId($name);
			}
			return NULL;
		} // end lastInsertId();

		public function prepare($query, $driverOpts = array())
		{
			if(!$this -> connected)
			{
				$this -> makeConnection();				
			}
			
			self::$lastQuery = $query;
			$result = parent::prepare($query, $driverOpts);
			$result -> passData($query);
			return $result;
		} // end prepare();

		public function query($query, $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $cache = false)
		{
			if(!$this -> connected)
			{
				$this -> makeConnection();
			}
			$this -> startDebug($query, $cache);
			$result = parent::query($query, $arg1, $arg2, $arg3);
			$this -> endDebug();
			return $result;
		} // end query();

		public function quote($string, $hint = NULL)
		{
			if(!$this -> connected)
			{
				$this -> makeConnection();
			}
			return parent::quote($string, $hint);
		} // end quote();

		public function rollBack()
		{
			if($this -> connected)
			{
				$this -> transactionsRollback++;
				return parent::rollBack();
			}
			return false;
		} // end rollBack();

		public function setAttribute($name, $value)
		{
			if(!$this -> connected)
			{
				$this -> attributes[$name] = $value;
			}
			else
			{
				return parent::setAttribute($name, $value);
			}
		} // end setAttribute();

		public function get($query, $id = NULL)
		{
			if(!is_null($id))
			{
				$stmt = $this -> prepare($query);
				if(is_string($id))
				{
					$stmt -> bindValue(':id', $id, PDO::PARAM_STR);
				}
				else
				{
					$stmt -> bindValue(':id', $id, PDO::PARAM_INT);
				}
				$stmt -> execute();
				if($row = $stmt -> fetch(PDO::FETCH_NUM))
				{
					$stmt -> closeCursor();
					return $row[0];
				}
				$stmt -> closeCursor();
				return NULL;
			}
			$stmt = $this -> query($query);
			if($row = $stmt -> fetch(PDO::FETCH_NUM))
			{
				$stmt -> closeCursor();
				return $row[0];
			}
			$stmt -> closeCursor();
			return NULL;
		} // end get();

		/*
		 * Internal methods
		 */

		public function _rawQuery($query)
		{
			return parent::query($query);
		} // end _rawQuery();

		public function _rawPrepare($query)
		{
			return parent::prepare($query);
		} // end _rawPrepare();

		public function _rawExec($query)
		{
			return parent::exec($query);
		} // end _rawExec();

		private function makeConnection()
		{
			$attr = array();

			if($this->persistent)
			{
				$attr[PDO::ATTR_PERSISTENT_CONNECTION] = true;
			}
			parent::__construct($this->dsn,$this->user,$this->password, $attr);
			$this -> connected = true;
			parent::setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
			foreach($this -> attributes as $name => $value)
			{
				parent::setAttribute($name, $value);
			}

			// This may not be overwritten...
			parent::setAttribute(PDO::ATTR_STATEMENT_CLASS, array('opdStatement', array($this)));

			if(strpos($this -> dsn, 'mysql') !== false)
			{
				$this -> _rawExec('SET NAMES `'.$this->charset.'`');				
			}
		} // end makeConnection();

		public function startDebug($query, $cache = false, $cached = false)
		{
			self::$lastQuery = $query;

			$this -> queries[$i] = array('query' => $query, 'result' => 0, 'cache' => $cache, 'cached' => $cached, 'time' => 0);
			if($this -> _debugConsole)
			{
				if(is_null($this -> consoleCode))
				{
					$this -> consoleCode = file_get_contents(OPD_DIR.'opd.debug.php');	
				}
				$this -> queries[$i]['time'] = microtime(true);
		/*		$dbg = debug_backtrace();
				foreach($dbg as $items)
				{
					echo '<i>'.$items['file'].':'.$items['line'].'; '.$items['class'].':'.$items['function'].'</i><br/>';
				}
				echo '<b>Start: '.$this->queries[$i]['time'].'</b><br/>';*/
			}
		} // end startDebug();

		public function endDebug()
		{
			if($this -> _debugConsole)
			{
				$this -> queries[$i]['time'] = microtime(true) - $this -> queries[$i]['time'];
		//		echo '<b>End: '.microtime(true).'</b><br/>';

			}
			$i++;
		} // end endDebug();

		public function __set($name, $value)
		{
			if($name == 'debugConsole')
			{
				$this -> _debugConsole = $value;
				$this -> consoleCode = file_get_contents(OPD_DIR.'opd.debug.php');
			}
		} // end __set();

		public function __get($name)
		{
			if($name == 'debugConsole')
			{
				return $this -> _debugConsole;
			}
		} // end __get();

		public function __destruct()
		{
			if($this -> debugConsole)
			{
				$config = array(
					'Open Power Driver version' => OPD_VERSION,
					'DSN' => $this -> dsn,
					'Database connection' => ($this->connected ? 'Yes' : 'No'),
					'Client version' => $this->getAttribute(PDO::ATTR_CLIENT_VERSION),
					'Server version' => $this->getAttribute(PDO::ATTR_SERVER_VERSION),
					'Connection status' => $this->getAttribute(PDO::ATTR_CONNECTION_STATUS)
				);

				eval($this->consoleCode);

				if($debugCode)
				{
					echo '<script type="text/javascript">
						opd_console = window.open("", "OPD Debug Console", "width=680,height=400,resizeable,scrollbars=yes");
						'.$debugCode.'</script>';
				}
			}
		} // end __destruct();
	}

	class opdStatement extends PDOStatement
	{
		private $opd;
		private $query;

		protected function __construct($opd)
		{
			$this -> opd = $opd;
		} // end __construct();

		public function passData($query)
		{
			$this -> query = $query;
		} // end passData();

		public function execute()
		{
			$this -> opd -> startDebug($this->query);
			$result = parent::execute();
			$this -> opd -> endDebug();
			return $result;
		} // end execute();

		public function _rawExecute()
		{
			return parent::execute();
		} // end _rawExecute();
	} // end opdStatement;

	class opdCachedStatement implements Iterator
	{
		protected $stmt;
		protected $opd;
		protected $guard;

		protected $cache;
		protected $cacheId;
		protected $cacheDir;
		protected $data;
		protected $i;

		public function __construct(opdGuardian $guard, $query, $cacheStatus, $param2 = NULL, $param3 = NULL)
		{
			$this -> guard = $guard;
			$this -> opd = $guard -> opd;
			$this -> cache = $cacheStatus;
			$this -> cacheDir = $this -> opd -> cacheDirectory;
			if($this -> cache)
			{	
				$this -> cacheId = $param2;
				if($this -> cacheId != NULL)
				{
					$this -> opd -> startDebug($query, true, true);
					$this -> data = unserialize(file_get_contents($this->cacheDir.'%%'.$this->cacheId.'.php'));
					$this -> opd -> endDebug();
				}
			}
			else
			{
				$this -> cacheId = $param2;
				$this -> stmt = $param3;
			}
			// set the cursor at the starting position
			$this -> i = 0;
		} // end __construct();

		public function bindColumn($column, &$param, $type = NULL)
		{
			return false;
		} // end bindColumn();

		public function bindParam($parameter, &$variable, $dataType = NULL, $length = NULL, $driverOptions = NULL)
		{
			return false;
		} // end bindParam();

		public function bindValue($parameter, $value, $dataType = NULL)
		{
			return false;
		} // end bindValue();

		public function closeCursor()
		{
			if(!$this -> cache)
			{
				file_put_contents($this->cacheDir.'%%'.$this->cacheId.'.php', serialize($this->data));
				return $this -> stmt -> closeCursor();
			}
			return true;
		} // end closeCursor();

		public function columnCount()
		{
			return $this -> stmt -> columnCount();
		} // end columnCount();

		public function errorCode()
		{
			return $this -> stmt -> errorCode();
		} // end errorCode();

		public function errorInfo()
		{
			return $this -> stmt -> errorInfo();
		} // end errorInfo();

		public function execute($inputParameters = NULL)
		{
			return false;
		} // end execute();

		public function fetch($fetchStyle = PDO::FETCH_ASSOC, $orientation = PDO::FETCH_ORI_NEXT, $offset = NULL)
		{
			if(!$this -> cache)
			{
				if($offset == NULL)
				{
					if($data = $this -> stmt -> fetch($fetchStyle, $orientation))
					{
						$this -> data[$this->i] = $data;
						$this -> i++;
						return $data;
					}
				}
				else
				{
					if($data = $this -> stmt -> fetch($fetchStyle, $orientation, $offset))
					{
						$this -> data[$this->i] = $data;
						$this -> i++;
						return $data;
					}
				}
			}
			else
			{
				if(isset($this->data[$this->i]))
				{
					return $this->data[$this->i++];
				}				
			}
		} // end fetch();

		public function fetchAll($fetchStyle = PDO::FETCH_BOTH, $columnIndex = 0)
		{
			if(!$this -> cache)
			{
				if($fetchStyle == PDO::FETCH_COLUMN)
				{
					return $this -> data = $this -> stmt -> fetchAll($fetchStyle, $columnIndex);
				}
				else
				{
					return $this -> data = $this -> stmt -> fetchAll($fetchStyle);
				}
			}
			else
			{
				return $this -> data;
			}
		} // end fetchAll();

		public function fetchColumn($columnNumber = 1)
		{
			if(!$this -> cache)
			{
				return $this -> data[$this->i++] = $this -> stmt -> fetchColumn($columnNumber);			
			}
			else
			{
				return $this -> data[$this->i++];
			}
		} // end fetchColumn();

		public function getAttribute($attribute)
		{
			return $this -> stmt -> getAttribute($attribute);
		} // end getAttribute();

		public function getColumnMeta($column)
		{
			return $this -> stmt -> getColumnMeta($column);
		} // end getColumnMeta();

		public function nextRowset()
		{
			return $this -> stmt -> nextRowset();
		} // end nextRowset();

		public function rowCount()
		{
			return $this -> stmt -> rowCount();
		} // end rowCount();

		public function setAttribute($attribute, $value)
		{
			return $this -> stmt -> setAttribute($attribute, $value);
		} // end setAttribute();

		public function setFetchMode($mode, $className = NULL)
		{
			if($this -> cache)
			{
				return 1;
			}
			if($mode == PDO::FETCH_CLASS)
			{
				return $this -> stmt -> setFetchMode($mode, $className);
			}
			return $this -> stmt -> setFetchMode($mode);
		} // end setFetchMode();
		
		/*
		 *	ITERATOR INTERFACE IMPLEMENTATION
		 */
		 
		public function current()
		{
			return $this -> data[$this->i-1];		
		} // end current();
		
		public function key()
		{
			return $this -> i - 1;		
		} // end key();

		public function valid()
		{
			if($this -> fetch())
			{
				return true;
			}
			$this -> closeCursor();
			return false;
		} // end valid();
		
		public function next()
		{
		} // end next();

		public function rewind()
		{
		} // end rewind();	
	} // end opdCachedStatement;
	
	class opdPreparedCacheStatement extends opdCachedStatement
	{
		private $status = 0;
		private $columnBindings = array();
		private $valueBindings = array();
		private $paramBindings = array();
		private $closed = true;

		public function __construct(opdGuardian $guard, $query)
		{
			$this -> query = $query;
			$this -> guard = $guard;
			$this -> opd = $guard -> opd;
			$this -> cacheDir = $this -> opd -> cacheDirectory;
			$this -> stmt = NULL;
			// set the cursor at the starting position
		} // end __construct();
		
		public function execute($inputParameters = NULL)
		{
			if(!$this -> closed)
			{
				// Error here!!!
			}
			$this -> status = 0;
			$cache = false;
			$cached = false;
			$cacheInfo = $this -> guard -> getCacheInfo();
			if(!is_null($cacheInfo))
			{
				$this -> status = 1;
				$this -> cacheId = $cacheInfo[0];
				$this -> i = 0;
				$cache = true;
				$cached = false;
				if($cacheInfo[1] > 0)
				{
					if(@filemtime($this->cacheDir.'%%'.$cacheInfo[0].'.php') + $cacheInfo[1] > time())
					{
						$this -> status = 2;
						$cached = true;
					}
				}
				else
				{
					if(file_exists($this->cacheDir.'%%'.$cacheInfo[0].'.php'))
					{
						$this -> status = 2;
						$cached = true;
					}
				}
			}

			switch($this -> status)
			{
				case 0:
				case 1:
					if(!is_object($this -> stmt))
					{
						$this -> stmt = $this -> opd -> prepare($this -> query);
					}
					foreach($this -> valueBindings as &$item)
					{
						$this -> stmt -> bindValue($item[0], $item[1], $item[2]);
					}
					foreach($this -> paramBindings as &$item)
					{
						$this -> stmt -> bindParam($item[0], $item[1], $item[2], $item[3], $item[4]);
					}
					foreach($this -> columnBindings as &$item)
					{
						$this -> stmt -> bindColumn($item[0], $item[1], $item[2]);
					}
					$this -> opd -> startDebug($this->query, $cache, $cached);
					$this -> stmt -> _rawExecute();
					$this -> opd -> endDebug();
					break;
				case 2:
					$this -> opd -> startDebug($this->query, $cache, $cached);
					$this -> data = unserialize(file_get_contents($this->cacheDir.'%%'.$this->cacheId.'.php'));
					$this -> opd -> endDebug();
					$this -> cache = true;
			}
			$this -> valueBindings = array();
			$this -> paramBindings = array();
			$this -> columnBindings = array();
			$this -> closed = false;
		} // end execute();

		public function bindColumn($column, &$param, $type = NULL)
		{
			$this -> columnBindings[] = array(0 => $column, &$param, $type);
		} // end bindColumn();

		public function bindParam($parameter, &$variable, $dataType = NULL, $length = NULL, $driverOptions = NULL)
		{
			$this -> paramBindings[] = array(0 => $parameter, &$variable, $dataType, $length, $driverOptions);
		} // end bindParam();

		public function bindValue($parameter, $value, $dataType = NULL)
		{
			$this -> valueBindings[] = array(0 => $parameter, $value, $dataType);
			return true;
		} // end bindValue();
		
		public function closeCursor()
		{
			switch($this -> status)
			{
				case 0:
					$this -> stmt -> closeCursor();
					break;
				case 1:
					$this -> stmt -> closeCursor();
					file_put_contents($this->cacheDir.'%%'.$this->cacheId.'.php', serialize($this->data));
			}
			$this -> closed = true;
		} // end closeCursor();
	} // end opdPreparedCacheStatement;

	class opdGuardian
	{
		public $opd;
		private $name;
		private $lockData = false;
		private $locked;

		private $cacheInfo = array();

		public function __construct(opdClass $opd, $name = '')
		{
			$this -> opd = $opd;
			$this -> name = $name;
		} // end __construct();

		public function lock($lock)
		{
			$this -> lockData = $lock;
			$this -> locked = false;
		} // end lock();


		public function addLock($table)
		{
			if(is_array($this -> lockData) && !$this -> locked)
			{
				$this -> lockData[] = $table;
			}
		} // end addLock();

		public function unlock()
		{
			if($this -> locked)
			{
				$this -> opd -> _rawExec('UNLOCK TABLES');
				return true;
			}
			return false;
		} // end unlock();

		public function query($query, $arg1 = NULL, $arg2 = NULL, $arg3 = NULL)
		{
			$cacheInfo = array_pop($this -> cacheInfo);
			if(!is_null($cacheInfo))
			{
				if($cacheInfo[1] > 0)
				{
					if(@filemtime($this->opd->cacheDirectory.'%%'.$cacheInfo[0].'.php') + $cacheInfo[1] > time())
					{
						return new opdCachedStatement($this, $query, true, $cacheInfo[0]);
					}
				}
				else
				{
					if(file_exists($this->opd->cacheDirectory.'%%'.$cacheInfo[0].'.php'))
					{
						return new opdCachedStatement($this, $query, true, $cacheInfo[0]);
					}
				}

				$this -> tryLock();
				return new opdCachedStatement($this, $query, false, $cacheInfo[0],
					$this -> opd -> query($query,$arg1,$arg2,$arg3, true));
			}

			$this -> tryLock();
			return $this -> opd -> query($query, $arg1, $arg2, $arg3);
		} // end query();

		public function prepare($query, $driverOpts = array(), $cached = true)
		{
			if(sizeof($this -> cacheInfo) > 0 && $cached)
			{
				return new opdPreparedCacheStatement($this, $query);
			}

			$this -> tryLock();
			return $this -> opd -> prepare($query);
		} // end prepare();

		public function exec($query, $id = NULL)
		{
			$this -> tryLock();

			return $this -> opd -> exec($query, $id);
		} // end exec();

		public function get($query, $id = NULL)
		{
			$this -> tryLock();

			return $this -> opd -> get($query, $id);
		} // end get();

		public function setCache($id, $expire = 0)
		{
			if(!is_null($id))
			{
				$this -> cacheInfo[] = array(0 => $id, $expire);
			}
			else
			{
				$this -> cacheInfo[] = NULL;
			}
		} // end setCache();

		public function __destruct()
		{
			$this -> unlock();
		} // end destruct();

		public function tryLock()
		{
			if($this -> lockData !== false && $this -> locked = 0)
			{
				if(is_array($this -> lockData))
				{
					$this -> lockData = implode(', ', $this -> lockData);
				}

				$this -> opd -> _rawExec('LOCK TABLES '.$this->lockData);
				$this -> locked = true;
			}
		} // end tryLock();

		public function getCacheInfo()
		{
			return array_pop($this -> cacheInfo);
		} // end getCacheInfo();
	}
?>
