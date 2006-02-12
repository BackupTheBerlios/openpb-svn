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
	define('OPD_VERSION', '0.2');
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
		
		private $queryMonitor;
		private $i;

		private $pdo;
		private $queryCount;

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
				$config = array('DSN' => $this -> dsn, 'Debug console' => (int)$this -> debugConsole);
			
				echo '<script language="JavaScript">
				opd_console = window.open("","OPD debug console","width=680,height=350,resizable,scrollbars=yes");
				opd_console.document.write("<HTML><TITLE>OPD debug console</TITLE><BODY bgcolor=#ffffff><h1>OPD DEBUG CONSOLE</h1>");
				opd_console.document.write(\'<table border="0" width="100%">\');
				';
				foreach($config as $id => $val)
				{
					echo '
						opd_console.document.write(\'<tr><td width="25%" bgcolor="#DDDDDD"><b>'.$id.'</b></td>\'); 
						opd_console.document.write(\'<td width="75%" bgcolor="#EEEEEE">'.$val.'</td></tr>\');
					';
				}
				echo '
				opd_console.document.write(\'</table><table border="0" width="100%"><tr><td width="64%" bgcolor="#CCCCCC"><b>Query</b></td>\'); 
				opd_console.document.write(\'<td width="8%" bgcolor="#CCCCCC"><b>Result</b></td>\');
				opd_console.document.write(\'<td width="8%" bgcolor="#CCCCCC"><b>Cache</b></td>\');
				opd_console.document.write(\'<td width="20%" bgcolor="#CCCCCC"><b>Execution time</b></td></tr>\');
				';
				if(count($this -> queryMonitor) > 0)
				{
					foreach($this -> queryMonitor as $queryInfo)
					{
						echo '
							opd_console.document.write(\'<tr><td width="64%" bgcolor="#EEEEEE">'.addslashes($queryInfo['query']).'</td>\'); 
							opd_console.document.write(\'<td width="8%" bgcolor="#EEEEEE"><b>'.$queryInfo['result'].'</b></td>\');
							opd_console.document.write(\'<td width="8%" bgcolor="#EEEEEE">'.$queryInfo['cache'].'</td>\');
							opd_console.document.write(\'<td width="20%" bgcolor="#EEEEEE">'.$queryInfo['execution'].' s</td></tr>\');
						';
					}
				}
				echo '
				opd_console.document.write(\'</table>\');
				</script>
				';
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
			$this -> beginDebugDefinition($statement, false);
			$this -> startTimer();
			$result = $this -> pdo -> exec($statement);
			$this -> endTimer();
			$this -> endDebugDefinition($result);
			$this -> lastQuery = $statement;
			$this -> incCounter();
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
				$this -> lastQuery = $statement;
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
					$this -> lastQuery = $statement;
				}
				$this -> cacheIds = array();
				$this -> cachePeroids = array();
				return new opdPreparedCacheStatement($this, $cacheTests, $result, $statement);
			}
		} // end prepare();

		public function query($statement, $fetchMode = PDO::FETCH_ASSOC)
		{
			$this -> beginDebugDefinition($statement, $this -> cache);
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
				$this -> startTimer();
				$result = $this -> pdo -> query($statement);
				$this -> endTimer();
				$this -> lastQuery = $statement;
				$this -> incCounter();

				$result -> setFetchMode($fetchMode);
				return new opdCachedStatement($this, false, $result, $this->cacheId);
			}
			else
			{
				$this -> startTimer();
				$result = $this -> pdo -> query($statement);
				$this -> endTimer();
				$this -> lastQuery = $statement;
				$this -> incCounter();
	
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
			$this -> incCounter();
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

		public function incCounter()
		{
			$this -> queryCount++;
		} // end incCounter();
		
		public function getCounter()
		{
			return $this -> queryCount;
		} // end getCounter();
		
		// --------------------
		// Debug console methods
		// --------------------
		
		public function beginDebugDefinition($query, $cache)
		{
			if($this -> debugConsole)
			{
				$this -> queryMonitor[$this->i] = array(
					'query' => $query,
					'result' => '',
					'cache' => ($cache == true ? 'Yes' : 'No'),
					'execution' => 0		
				);
			}
		} // end beginDebugDefinition();
		
		public function startTimer()
		{
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
