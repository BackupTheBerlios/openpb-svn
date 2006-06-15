<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                          Open Power Driver                             //
  //         Copyright (c) 2005 OpenPB team, http://www.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //
  //
  // $Id$

	if(!defined('OPD_DIR'))
	{
		define('OPD_DIR', './');
	}
	define('OPD_VERSION', '0.5');
	define('OPD_CACHE_PREPARE', true);
	
	require(OPD_DIR.'opd.statement.php');

	function opdErrorHandler(PDOException $exc)
	{
		echo '<br/><b>Open Power Driver internal error #'.$exc->getCode().': </b> '.$exc->getMessage().'<br/>
			Query used: <i>'.opdClass::$lastQuery.'</i><br/>';
	}

	class opdClass
	{
		static public $lastQuery;
		public $dsn;
		public $debugConsole;
		
		// Debug etc.
		private $queryMonitor;
		private $consoleCode;
		private $i;
		
		private $counterExecuted = 0;
		private $counterRequested = 0;
		private $counterTime = 0;
		private $counterTimeExecuted = 0;
		private $transactions = 0;
		private $transactionsCommit = 0;
		private $transactionsRollback = 0;

		// PDO
		private $pdo;
		
		// Connection
		private $user;
		private $password;
		private $driverOpts;
		private $connected;
		
		// Cache
		private $cacheDir;
		private $cache;
		private $cacheId;
		private $cacheIds = array();

		public function __construct($dsn, $user, $password, $driverOpts = array())
		{
			$this -> dsn = $dsn;
			$this -> user = $user;
			$this -> password = $password;
			$this -> driverOpts = $driverOpts;
			$this -> queryCount = 0;
			$this -> i = 0;
		} // end __construct();
		
		private function makeConnection()
		{
			if(is_null($this -> connected))
			{
				$this -> connected = true;
				$this -> pdo = new PDO($this -> dsn, $this -> user, $this -> password, $this -> driverOpts);
				$this -> pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}	
		} // end makeConnection();

		public function __destruct()
		{
			if($this -> debugConsole)
			{
				if($this -> transactionsCommit + $this -> transactionsRollback != $this -> transactions)
				{
					// If any transaction closed automatically
					$this -> transactionsCommit = $this -> transactions - $this -> transactionsRollback;
				}
			
				$config = array(
					'Open Power Driver version' => OPD_VERSION,
					'DSN' => $this -> dsn,
					'Database connection' => ($this -> connected ? 'Yes' : 'No'),
					'Requested queries' => $this -> counterRequested,
					'Executed queries' => $this -> counterExecuted,
					'Total database time' => $this -> counterTime.' s',
					'Executed queries time' => $this -> counterTimeExecuted.' s',
					'Transactions opened' => $this -> transactions,
					'Commited transactions' => $this -> transactionsCommit,
					'Rolled back transactions' => $this -> transactionsRollback
				);

				eval($this->consoleCode);
				if(isset($debugCode))
				{
					echo '<script language="JavaScript">
					opd_console = window.open("","OPD debug console","width=680,height=350,resizable,scrollbars=yes");
					'.$debugCode.'</script>';
				}
			}
		} // end __destruct();
		
		static public function create($config)
		{
			if(is_string($config))
			{
				$config = parse_ini_file($config);		
			}
			
			if(!is_array($config))
			{
				throw new Exception('Invalid Open Power Driver configuration: no configuration array.');		
			}
			
			$opd = new opdClass($config['dsn'], $config['user'], $config['password']);
			if(isset($config['cache']))
			{
				$opd -> setCacheDirectory($config['cache']);
			}
			if(isset($config['debugConsole']))
			{
				$opd -> debugConsole = $config['debugConsole'];
			}
			return $opd;
		} // end create();

		public function beginTransaction()
		{
			$this -> transactions++;
			$this -> makeConnection();
			return $this -> pdo -> beginTransaction();
		} // end beginTransaction();

		public function commit()
		{
			$this -> transactionsCommit++;
			$this -> makeConnection();
			return $this -> pdo -> commit();
		} // end commit();

		public function errorCode()
		{
			$this -> makeConnection();
			return $this -> pdo -> errorCode();
		} // end errorCode();

		public function errorInfo()
		{
			$this -> makeConnection();
			return $this -> pdo -> errorInfo();
		} // end errorInfo();

		public function exec($statement, $id = NULL)
		{
			if(!is_null($id))
			{
				$stmt = $this -> prepare($statement);
				$stmt -> bindValue(':id', $id, PDO::PARAM_INT);
				return $stmt -> execute();			
			}		
			
			$this -> makeConnection();		
			$this -> beginDebugDefinition($statement);
			$this -> startTimer(false, false);
			$result = $this -> pdo -> exec($statement);
			$this -> endTimer();
			opdClass::$lastQuery = $statement;
			$this -> endDebugDefinition($result);
			return $result;
		} // end exec();

		public function getAttribute($attribute)
		{
			$this -> makeConnection();
			return $this -> pdo -> getAttribute($attribute);
		} // end getAttribute();	

		public function getAvailableDrivers()
		{
			$this -> makeConnection();
			return $this -> pdo -> getAvailableDrivers();
		} // end getAvailableDrivers();

		public function lastInsertId($sequence = NULL)
		{
			$this -> makeConnection();
			if($sequence == NULL)
			{
				return $this -> pdo -> lastInsertId();
			}
			return $this -> pdo -> lastInsertId($sequence);
		} // end lastInsertId();

		public function prepare($statement, $options = array())
		{
			if($this -> cache == false)
			{
				if(count($options) == 0)
				{
					$options = array(PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY);
				}
				$this -> makeConnection();
				$result = $this -> pdo -> prepare($statement, $options);
				opdClass::$lastQuery = $statement;
				return new opdStatement($this, $result, $statement);
			}
			else
			{
				$cacheTests = array();
				$needsQuery = 0;
				$result = NULL;
				$time = time();
				if(count($this -> cacheIds) > 0)
				{
					foreach($this -> cacheIds as $idx => $id)
					{
						if($id == false)
						{
							// This instance must not be cached 
							$cacheTests[] = array(
								'id' => false,
								'test' => false						
							);
							$needsQuery = 1;
						}
						else
						{
							// This instance should be cached
							if(!is_null($this -> cachePeroids[$idx]))
							{
								$test = (@filemtime($this->cacheDir.'%%'.$id.'.php') + $this -> cachePeroids[$idx] > $time);
							}
							else
							{
								$test = file_exists($this->cacheDir.'%%'.$id.'.php');	
							}						
							$cacheTests[] = array(
								'id' => $id,
								'test' => $test						
							);
							if(!$test)
							{
								$needsQuery = 1;
							}
						}
					}			
				}
				if($needsQuery)
				{
					if(count($options) == 0)
					{
						$options = array(PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY);
					}
					$this -> makeConnection();
					$result = $this -> pdo -> prepare($statement, $options);
					opdClass::$lastQuery = $statement;
				}
				$this -> cacheIds = array();
				$this -> cachePeroids = array();
				$this -> cache = false;
				return new opdPreparedCacheStatement($this, $cacheTests, $result, $statement);
			}
		} // end prepare();

		public function query($statement, $fetchMode = PDO::FETCH_ASSOC)
		{
			$this -> beginDebugDefinition($statement);
			if($this -> cache)
			{
				$this -> cache = false;
				if(!is_null($this -> cachePeroid))
				{
					if(@filemtime($this->cacheDir.'%%'.$this->cacheId.'.php') + $this -> cachePeroid > time())
					{
						$this -> cachePeroid = NULL;
						return new opdCachedStatement($this, true, $this->cacheId);
					}
					$this -> cachePeroid = NULL;
				}
				else
				{
					if(file_exists($this->cacheDir.'%%'.$this->cacheId.'.php'))
					{
						return new opdCachedStatement($this, true, $this->cacheId);
					}
				}
				$this -> makeConnection();
				$this -> startTimer(true, false);
				$result = $this -> pdo -> query($statement);
				$this -> endTimer();
				opdClass::$lastQuery = $statement;

				$result -> setFetchMode($fetchMode);
				return new opdCachedStatement($this, false, $result, $this->cacheId);
			}
			else
			{
				$this -> cache = false;
				$this -> makeConnection();
				$this -> startTimer(false, false);
				$result = $this -> pdo -> query($statement);
				$this -> endTimer();
				opdClass::$lastQuery = $statement;
	
				$result -> setFetchMode($fetchMode);
				return new opdStatement($this, $result);
			}
		} // end query();

		public function quote($string, $parameterType = PDO::PARAM_STR)
		{
			$this -> makeConnection();
			return $this -> pdo -> quote($string, $parameterType);
		} // end quote();

		public function rollBack()
		{
			$this -> transactionsRollback++;
			$this -> makeConnection();
			return $this -> pdo -> rollBack();
		} // end rollBack();

		public function setAttribute($name, $value)
		{
			$this -> makeConnection();
			return $this -> pdo -> setAttribute($name, $value);
		} // end setAttribute();
		
		// --------------------
		// OPD-specific methods
		// --------------------
		
		public function get($query)
		{
			$stmt = $this -> query($query, PDO::FETCH_NUM);
			if($row = $stmt -> fetch())
			{
				$stmt -> closeCursor();
				return $row[0];
			}
			$stmt -> closeCursor();
			return NULL;
		} // end get();
		
		public function getId($query, $id)
		{
			$stmt = $this -> prepare($query);
			$stmt -> bindValue(':id', $id, PDO::PARAM_INT);
			$stmt -> execute();
			if($row = $stmt -> fetch(PDO::FETCH_NUM))
			{
				$stmt -> closeCursor();
				return $row[0];
			}
			$stmt -> closeCursor();
			return NULL;
		} // end getId();

		public function setCacheDirectory($dir)
		{
			$this -> cacheDir = $dir;
		} // end setCacheDirectory();

		public function getCacheDirectory()
		{
			return $this -> cacheDir;
		} // end getCacheDirectory();

		public function setCache($id, $prepare = false)
		{
			$this -> cache = true;
			$this -> cacheId = $id;
			$this -> cachePeroid = NULL;
			if($prepare == true)
			{
				$this -> cacheIds[] = $id;
				$this -> cachePeroids[] = NULL;
			}
		} // end setCache();

		public function setCacheExpire($peroid, $id, $prepare = false)
		{
			$this -> cache = true;
			$this -> cacheId = $id;
			$this -> cachePeroid = $peroid;
			if($prepare == true)
			{
				$this -> cacheIds[] = $id;
				$this -> cachePeroids[] = $peroid;
			}
		} // end setCacheExpire();

		public function clearCache($name)
		{
			if(file_exists($this -> cacheDir.'%%'.$name.'.php'))
			{
				unlink($this -> cacheDir.'%%'.$name.'.php');
				return true;
			}
			return false;
		} // end clearCache();

		public function clearCacheGroup($name)
		{
			$list = glob($this -> cacheDir.'%%'.$name.'.php', GLOB_BRACE);
			if(is_array($list))
			{
				foreach($list as $file)
				{
					unlink($file);
				}
				return true;
			}
			return false;
		} // end clearCacheGroup();
		
		public function getCounter()
		{
			return $this -> counterExecuted;
		} // end getCounter();
		
		// --------------------
		// Debug console methods
		// --------------------
		
		public function beginDebugDefinition($query)
		{
			if($this -> debugConsole)
			{
				if(is_null($this -> consoleCode))
				{
					$this -> consoleCode = file_get_contents(OPD_DIR.'opd.debug.php');	
				}
			
				$this -> queryMonitor[$this->i] = array(
					'query' => $query,
					'result' => '',
					'cache' => 0,
					'cached' => 0,
					'execution' => 0
				);
			}
		} // end beginDebugDefinition();
		
		public function startTimer($cacheEnabled, $cached)
		{
			$this -> counterRequested++;
			if(!$cached)
			{
				$this -> counterExecuted++;
			}
			$this -> queryMonitor[$this->i]['cache'] = $cacheEnabled == true ? 'Yes' : 'No';
			$this -> queryMonitor[$this->i]['cached'] = $cached;
			if($this -> debugConsole)
			{
				$this -> time = microtime(true);
			}
		} // end startTimer();

		public function endTimer()
		{
			if($this -> debugConsole)
			{
				$this -> queryMonitor[$this->i]['execution'] = round(microtime(true) - $this -> time, 6);
				$this -> counterTime += $this -> queryMonitor[$this->i]['execution'];
				if(!$this -> queryMonitor[$this->i]['cached'])
				{
					$this -> counterTimeExecuted += $this -> queryMonitor[$this->i]['execution'];
				}
			}
		} // end endTimer();
		
		public function endDebugDefinition($result)
		{
			if($this -> debugConsole)
			{
				$this -> queryMonitor[$this -> i]['result'] = $result;
				$this -> i++;
			}
		} // end endDebugDefinition();
	}

?>
