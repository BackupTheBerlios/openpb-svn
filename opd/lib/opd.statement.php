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

	interface iopdStatement
	{	
		public function bindColumn($column, &$param, $type = NULL);
		public function bindParam($parameter, &$variable, $dataType = NULL, $length = NULL, $driverOptions = NULL);
		public function bindValue($parameter, $value, $dataType = NULL);
		public function closeCursor();
		public function columnCount();
		public function errorCode();
		public function errorInfo();
		public function execute($inputParameters = array());
		public function fetch($fetchStyle = PDO::FETCH_BOTH, $orientation = PDO::FETCH_ORI_NEXT, $offset = NULL);
		public function fetchAll($fetchStyle = PDO::FETCH_BOTH, $columnIndex = 0);
		public function fetchColumn($columnNumber = 0);
		public function getAttribute($attribute);
		public function getColumnMeta($column);
		public function nextRowset();
		public function rowCount();
		public function setAttribute($attribute, $value);
		public function setFetchMode($mode, $className = NULL);		
	}
	
	class opdStatement implements iopdStatement, Iterator
	{
		private $stmt;
		private $opd;
		private $items;
		private $query;
		
		private $buffer;
		private $i;
		
		public function __construct(opdClass $opd, PDOStatement $stmt, $query = NULL)
		{
			$this -> stmt = $stmt;
			$this -> opd = $opd;
			$this -> query = $query;
		} // end __construct();

		public function bindColumn($column, &$param, $type = NULL)
		{
			if($type == NULL)
			{
				return $this -> stmt -> bindColumn($column, $param);
			}
			return $this -> stmt -> bindColumn($column, $param, $type);
		} // end bindColumn();

		public function bindParam($parameter, &$variable, $dataType = NULL, $length = NULL, $driverOptions = NULL)
		{
			if($dataType == NULL)
			{
				return $this -> stmt -> bindParam($parameter, $variable);
			}
			elseif($length == NULL)
			{
				return $this -> stmt -> bindParam($parameter, $variable, $dataType);
			}
			elseif($driverOptions == NULL)
			{
				return $this -> stmt -> bindParam($parameter, $variable, $dataType, $length);
			}
			return $this -> stmt -> bindParam($parameter, $variable, $dataType, $length, $driverOptions);
		} // end bindParam();

		public function bindValue($parameter, $value, $dataType = NULL)
		{
			if($dataType == NULL)
			{
				return $this -> stmt -> bindValue($parameter, $value);
			}
			return $this -> stmt -> bindValue($parameter, $value, $dataType);
		} // end bindValue();

		public function closeCursor()
		{
			$this -> opd -> endDebugDefinition($this -> items - 1);
			return $this -> stmt -> closeCursor();
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
			$this -> opd -> incCounter();
			if($inputParameters == NULL)
			{
				return $this -> stmt -> execute();
			}
			return $this -> stmt -> execute($inputParameters);
		} // end execute();

		public function fetch($fetchStyle = PDO::FETCH_BOTH, $orientation = PDO::FETCH_ORI_NEXT, $offset = NULL)
		{
			$this -> items++;
			if($offset == NULL)
			{
				return $this -> stmt -> fetch($fetchStyle, $orientation);
			}
			return $this -> stmt -> fetch($fetchStyle, $orientation, $offset);
		} // end fetch();

		public function fetchAll($fetchStyle = PDO::FETCH_BOTH, $columnIndex = 0)
		{
			$data = $this -> stmt -> fetchAll($fetchStyle, $columnIndex);
			$this -> items = count($data);
			return $data;
		} // end fetchAll();

		public function fetchColumn($columnNumber = 0)
		{
			$this -> items++;
			return $this -> stmt -> fetchColumn($columnIndex);
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
			$this -> items++;
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

		public function setFetchMode($mode, $className = NULL, $args = array())
		{
			if($mode == PDO::FETCH_CLASS)
			{
				return $this -> stmt -> setFetchMode($mode, $className, $args);
			}
			return $this -> stmt -> setFetchMode($mode);
		} // end setFetchMode();
		
		/*
		 *	ITERATOR INTERFACE IMPLEMENTATION
		 */
		 
		public function current()
		{
			return $this -> buffer;		
		} // end current();
		
		public function key()
		{
			return $this -> i;		
		} // end key();

		public function valid()
		{
			if($this -> buffer = $this -> stmt -> fetch())
			{
				return true;
			}
			$this -> items = $this -> i - 1;
			$this -> stmt -> closeCursor();
			return false;
		} // end valid();
		
		public function next()
		{
			$this -> i++;
		} // end next();

		public function rewind()
		{
			$this -> buffer = array();
			$this -> i = 0;		
		} // end rewind();
	}

	class opdCachedStatement implements iopdStatement, Iterator
	{
		protected $stmt;
		protected $opd;

		protected $cache;
		protected $cacheId;
		protected $cacheDir;
		protected $data;
		protected $i;

		public function __construct(opdClass $opd, $cacheStatus, $param2 = NULL, $param3 = NULL)
		{
			$this -> opd = $opd;
			$this -> cache = $cacheStatus;
			$this -> cacheDir = $this -> opd -> getCacheDirectory();
			if($this -> cache)
			{				
				$this -> cacheId = $param2;
				if($this -> cacheId != NULL)
				{
					$this -> opd -> startTimer();
					$this -> data = unserialize(file_get_contents($this->cacheDir.'%%'.$this->cacheId.'.php'));
					$this -> opd -> endTimer();
				}
			}
			else
			{
				$this -> cacheId = $param3;
				$this -> stmt = $param2;
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
			$this -> opd -> endDebugDefinition($this -> i);
			if(!$this -> cache)
			{
				file_put_contents($this->cacheDir.'%%'.$this->cacheId.'.php', serialize($this->data));
				return $this -> stmt -> closeCursor();
			}
			return 1;
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
				return $this -> data = $this -> stmt -> fetchAll($fetchStyle, $columnIndex);			
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
				return $this -> data[$this->i++] = $this -> stmt -> fetchColumn($columnIndex);			
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
		
		public function setCache($id)
		{
			$this -> cacheId = $id;
		} // end setCache();
		
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
	}
	
	class opdPreparedCacheStatement extends opdCachedStatement
	{
		private $j;
		private $cacheIds;	
		
		public function __construct(opdClass $opd, Array $itemList, $stmt, $query)
		{
			$this -> query = $query;
			$this -> opd = $opd;
			$this -> cacheDir = $this -> opd -> getCacheDirectory();
			$this -> cacheIds = $itemList;
			$this -> stmt = $stmt;
			// set the cursor at the starting position
			$this -> i = 0;
			$this -> j = 0;
			
			$this -> cache = $this -> cacheIds[$this->j]['test'];
		} // end __construct();
		
		public function execute($inputParameters = NULL)
		{
			if(!isset($this -> cacheIds[$this->j]['test']))
			{
				return false;			
			}
			$this -> opd -> beginDebugDefinition($this -> query, $this -> cacheIds[$this->j]['test']);
			$this -> i = 0;
			if($this -> cacheIds[$this->j]['test'] == true)
			{
				$this -> cache = true;
				$this -> cacheId = $this -> cacheIds[$this->j]['id'];
				$this -> opd -> startTimer();
				$this -> data = unserialize(file_get_contents($this->cacheDir.'%%'.$this->cacheId.'.php'));
				$this -> opd -> endTimer();		
			}
			else
			{
				$this -> cache = false;
				$this -> cacheId = $this -> cacheIds[$this->j]['id'];
				$this -> data = array();

				$this -> opd -> incCounter();
				if($inputParameters == NULL)
				{
					$this -> opd -> startTimer();
					$result = $this -> stmt -> execute();
					$this -> opd -> endTimer();
				}
				else
				{
					$this -> opd -> startTimer();
					$result = $this -> stmt -> execute($inputParameters);
					$this -> opd -> endTimer();
				}
				return $result;
			}
		} // end execute();

		public function bindColumn($column, &$param, $type = NULL)
		{
			if(!$this -> cache)
			{
				if($type == NULL)
				{
					return $this -> stmt -> bindColumn($column, $param);
				}
				return $this -> stmt -> bindColumn($column, $param, $type);
			}
			return true;
		} // end bindColumn();

		public function bindParam($parameter, &$variable, $dataType = NULL, $length = NULL, $driverOptions = NULL)
		{
			if(!$this -> cache)
			{
				if($dataType == NULL)
				{
					return $this -> stmt -> bindParam($parameter, $variable);
				}
				elseif($length == NULL)
				{
					return $this -> stmt -> bindParam($parameter, $variable, $dataType);
				}
				elseif($driverOptions == NULL)
				{
					return $this -> stmt -> bindParam($parameter, $variable, $dataType, $length);
				}
				return $this -> stmt -> bindParam($parameter, $variable, $dataType, $length, $driverOptions);
			}
			return true;
		} // end bindParam();

		public function bindValue($parameter, $value, $dataType = NULL)
		{
			if(!$this -> cache)
			{
				if($dataType == NULL)
				{
					return $this -> stmt -> bindValue($parameter, $value);
				}
				return $this -> stmt -> bindValue($parameter, $value, $dataType);
			}
			return true;
		} // end bindValue();
		
		public function closeCursor()
		{
			if($this -> cacheId == false)
			{
				$this -> opd -> endDebugDefinition($this -> i);
				return $this -> stmt -> closeCursor();
			}
			$result = parent::closeCursor();
			$this -> j++;
			if(isset($this -> cacheIds[$this->j]))
			{
				$this -> cache = $this -> cacheIds[$this->j]['test'];
			}
			return $result;
		} // end closeCursor();
		
		public function fetch($fetchStyle = PDO::FETCH_ASSOC, $orientation = PDO::FETCH_ORI_NEXT, $offset = NULL)
		{
			if($this -> cacheId == false)
			{
				return $this -> stmt -> fetch($fetchStyle, $orientation, $offset);			
			}
			return parent::fetch($fetchStyle, $orientation, $offset);
		} // end fetch();
		
		public function fetchAll($fetchStyle = PDO::FETCH_BOTH, $columnIndex = 0)
		{
			if($this -> cacheId == false)
			{
				return $this -> stmt -> fetch($fetchStyle, $columnIndex);			
			}
			return parent::fetchAll($fetchStyle, $columnIndex);
		} // end fetchAll();

		public function fetchColumn($columnNumber = 1)
		{
			if($this -> cacheId == false)
			{
				return $this -> stmt -> fetch($columnNumber);			
			}
			return parent::fetchColumn($columnNumber);
		} // end fetchColumn();
	}

?>
