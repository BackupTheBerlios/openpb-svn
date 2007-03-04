<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                          Open Power Forms                              //
  //         Copyright (c) 2007 OpenPB team, http://www.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //

    class opfI18n implements ioptI18n
    {
		private $langdata;
		private $modified;
		private $tpl;
		private $path;
		
		public function __construct($path)
		{
			$this -> setPath($path);
		} // end __construct();
		
		public function setPath($path)
		{
			$this -> path = $path;
		} // end setPath();

		public function loadGroup($id)
		{
			if(is_file($this->path.'%%'.$id.'.php'))
			{
				$this -> langdata[$id] = parse_ini_file($this->path.'%%'.$id.'.php');
			}
		} // end loadFile();
		
		public function setOptInstance(optClass $tpl)
		{
			$this -> tpl = $tpl;
		} // end setOptInstance();

		public function put($group, $id)
		{
			if(isset($this -> modified[$group][$id]))
			{
				return $this -> modified[$group][$id];
			}
			elseif(isset($this -> langdata[$group][$id]))
			{
				return $this -> langdata[$group][$id];
			}
			return NULL;
		} // end put();

		public function apply($group, $id)
		{
			$args = func_get_args();
			unset($args[0]);
			unset($args[1]);
			$this -> modified[$group][$id] = vsprintf($this -> langdata[$group][$id], $args);
		} // end apply();

		public function putApply($group, $id)
		{
			$args = func_get_args();
			if(is_array($args[2]))
			{
				unset($args[0]);
				unset($args[1]);
				return vsprintf($this -> langdata[$group][$id], $args[2]);
			}
		} // end putApply();

	} // end i18n;

?>
