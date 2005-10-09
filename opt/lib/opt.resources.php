<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                        Open Power Template                             //
  //         Copyright (c) 2005 OpenPB team, http://opt.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //
  //
  // $Id$

	interface ioptResource
	{
		public function __construct(optClass $tpl);
		public function isModified($name);
		public function templateExists($name);
		public function loadSource($name);
		public function loadCode($name);
		public function lockCode($name);
		public function saveCode($code);
		public function compileCacheReset($filename);
		public function setTestStatus($status);
	}

	class optResourceFiles implements ioptResource
	{
		private $tpl;
		private $status;

		public function __construct(optClass $tpl)
		{
			$this -> tpl = $tpl;
			$this -> connection = NULL;
			$this -> status = 1; // do the file existence checking
		} // end __construct();

		public function setTestStatus($status)
		{
			$this -> status = $status;
		} // end setTestStatus();

		public function isModified($name)
		{
			$cname = $this -> tpl -> compile.$this->parseName($name).'.php';
			if($this -> status == 1)
			{
				if(!file_exists($cname))
				{
					return 1;
				}
			}
			return (filemtime($cname) < filemtime($this -> tpl -> root.$name));		
		} // end isModified();

		public function loadSource($name)
		{
			if($this -> status == 1)
			{
				if(!file_exists($this -> tpl -> root.$name))
				{
					$this -> tpl -> error(E_USER_ERROR, '`'.$name.'` not found in '.$this->tpl->root.' directory.', 9);
				}
			}		
			return file_get_contents($this -> tpl -> root.$name);
		} // end loadSource();

		public function loadCode($name)
		{
			return file_get_contents($this -> tpl -> compile.$this->parseName($name).'.php');
		} // end loadCode();

		public function lockCode($name)
		{
			$this -> handler = fopen($this -> tpl -> compile.$this->parseName($name).'.php', 'w');
			flock($this -> handler, LOCK_EX);
		} // end lockCode();

		public function saveCode($code)
		{
			fwrite($this -> handler, $code);
			flock($this -> handler, LOCK_UN);
			fclose($this -> handler);
		} // end saveCode();

		private function parseName($name)
		{
			return '%%'.str_replace('/', '_', $name);
		} // end parseName();

		public function compileCacheReset($filename)
		{
			if($filename == NULL)
			{
				$dir = opendir($this -> tpl -> compile);
				while($f = readdir($dir))
				{
					if(is_file($this -> tpl -> compile.$f))
					{
						unlink($this -> tpl -> compile.$f);
					}
				}
				closedir($dir);
				return 1;
			}
			elseif(file_exists($this -> tpl -> compile.$this->parse_name($filename).'.php'))
			{

				unlink($this -> tpl -> compile.$this->parse_name($filename).'.php');
				return 1;				
			}
			return 0;
		} // end compileCacheReset();

		public function templateExists($name)
		{
			return file_exists($this -> tpl -> root.$name);	
		} // end templateExists();
	}
?>
