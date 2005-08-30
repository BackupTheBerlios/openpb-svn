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
/*
	interface iopt_resource
	{
		public function __construct(opt_template $tpl);
		public function is_modified($name);
		public function template_exists($name);
		public function load_source($name);
		public function load_code($name);
		public function save_code($name, $code);
		public function compile_cache_reset($filename);
		public function set_tests_status($status);
	}
*/
	class opt_resource_files //implements iopt_resource
	{
		private $tpl;
		private $status;

		public function __construct(opt_template $tpl)
		{
			$this -> tpl = $tpl;
			$this -> connection = NULL;
			$this -> status = 1; // do the file existence checking
		} // end __construct();

		public function set_tests_status($status)
		{
			$this -> status = $status;
		} // end set_tests_status();

		public function is_modified($name)
		{
			$cname = $this -> tpl -> conf['compile'].$this->parse_name($name).'.php';
			if($this -> status == 1)
			{
				if(!file_exists($cname))
				{
					return 1;
				}
			}
			return (filemtime($cname) < filemtime($this -> tpl -> conf['root'].$name));		
		} // end is_modified();

		public function load_source($name)
		{
			if($this -> status == 1)
			{
				if(!file_exists($this -> tpl -> conf['root'].$name))
				{
					$this -> tpl -> error(E_USER_ERROR, '`'.$name.'` not found in '.$this->tpl->conf['root'].' directory.', 5);
				}
			}		
			return file_get_contents($this -> tpl -> conf['root'].$name);		
		} // end load_source();

		public function load_code($name)
		{
			return file_get_contents($this -> tpl -> conf['compile'].$this->parse_name($name).'.php');
		} // end load_code();

		public function save_code($name, $code)
		{
			file_put_contents($this -> tpl -> conf['compile'].$this->parse_name($name).'.php', $code);		
		} // end save_code();

		private function parse_name($name)
		{
			return '%%'.str_replace('/', '_', $name);
		} // end parse_name();

		public function compile_cache_reset($filename)
		{
			if($filename == NULL)
			{
				$dir = opendir($this -> tpl -> conf['compile']);
				while($f = readdir($dir))
				{
					if(is_file($this -> tpl -> conf['compile'].$f))
					{
						unlink($this -> tpl -> conf['compile'].$f);
					}
				}
				closedir($dir);
				return 1;
			}
			elseif(file_exists($this -> tpl -> conf['compile'].$this->parse_name($filename).'.php'))
			{

				unlink($this -> tpl -> conf['compile'].$this->parse_name($filename).'.php');
				return 1;				
			}
			return 0;
		} // end compile_cache_reset();

		public function template_exists($name)
		{
			return file_exists($this -> tpl -> conf['root'].$name);	
		} // end template_exists();
	}
?>
