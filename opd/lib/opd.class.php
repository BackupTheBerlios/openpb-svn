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
  // $Id$ $Author$ $Date$ $Revision$

	if(!defined('OPD_DIR'))
	{
		define('OPD_DIR', './');
	}
	define('OPD_VERSION', '0.3');
	define('OPD_CACHE_PREPARE', true);
	
	require(OPD_DIR.'opd.statement.php');

	function opdErrorHandler(PDOException $exc)
	{
		echo '<br/><b>Open Power Driver internal error #'.$exc->getCode().': </b> '.$exc->getMessage().'<br/>';
	}

	class opdClass
	{
		public $lastQuery;
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

		// PDO
		private $pdo;
		
		// Cache
		private $cacheDir;
		private $cache;
		private $cacheId;
		private $cacheIds = array();

		public function __construct($dsn, $user, $password, $driverOpts = array())
		{
			$this -> pdo = new PDO($this -> dsn = $dsn, $user, $password, $driverOpts);
			$this -> pdo -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this -> queryCount = 0;
			$this -> i = 0;
		} // end __construct();

		public function __destruct()
		{
			if($this -> debugConsole)
			{
				$config = array(
					'Open Power Driver version' => OPD_VERSION,
					'DSN' => $this -> dsn,
					'Requested queries' => $this -> counterRequested,
					'Executed queries' => $this -> counterExecuted,
					'Total database time' => $this -> counterTime.' s',
					'Executed queries time' => $this -> counterTimeExecuted.' s'
				);
			
				eval($this->consoleCode);
				echo '<script language="JavaScript">
				opd_console = window.open("","OPD debug console","width=680,height=350,resizable,scrollbars=yes");
				'.$debugCode.'</script>';
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
			return $this -> pdo -> beginTransaction();
		} // end beginTransaction();

		public function commit()
		{
			return $this -> pdo -> commit();
		} // end commit();

		public function errorCode()
		{
			return $this -> pdo -> errorCode();
		} // end errorCode();

		public function errorInfo()
		{
			return $this -> pdo -> errorInfo();
		} // end errorInfo();

		public function exec($statement)
		{
			$this -> beginDebugDefinition($statement);
			$this -> startTimer(false, false);
			$result = $this -> pdo -> exec($statement);
			$this -> endTimer();
			$this -> endDebugDefinition($result);
			return $result;
		} // end exec();

		public function getAttribute($attribute)
		{
			return $this -> pdo -> getAttribute($attribute);
		} // end getAttribute();	

		public function getAvailableDrivers()
		{
			return $this -> pdo -> getAvailableDrivers();
		} // end getAvailableDrivers();

		public function lastInsertId($sequence = NULL)
		{
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
	
				$result = $this -> pdo -> prepare($statement, $options);
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
		
					$result = $this -> pdo -> prepare($statement, $options);
				}
				$this -> cacheIds = array();
				$this -> cachePeroids = array();
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
				$this -> startTimer(true, false);
				$result = $this -> pdo -> query($statement);
				$this -> endTimer();

				$result -> setFetchMode($fetchMode);
				return new opdCachedStatement($this, false, $result, $this->cacheId);
			}
			else
			{
				$this -> startTimer(false, false);
				$result = $this -> pdo -> query($statement);
				$this -> endTimer();
	
				$result -> setFetchMode($fetchMode);
				return new opdStatement($this, $result);
			}
		} // end query();

		public function quote($string, $parameterType = PDO::PARAM_STR)
		{
			return $this -> pdo -> quote($string, $parameterType);
		} // end quote();

		public function rollBack()
		{
			return $this -> pdo -> rollBack();
		} // end rollBack();

		public function setAttribute($name, $value)
		{
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
