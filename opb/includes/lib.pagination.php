<?php
// +----------------------------------------------------------------------+
// | Open Power Board                                                     |
// | Copyright (c) 2005 OpenPB team, http://www.openpb.net/               |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// +----------------------------------------------------------------------+
//
// $Id$

	class opbPagination implements iOpbLimiter
	{
		// the number of items
		private $items;
		// items per page
		private $ipp;		
		// router data
		private $routerFile;
		private $routerVars;
		// current page
		private $current;
		// the number of pages
		private $pages;
		
		// working buffer
		private $buffer;
		
		// engine references
		private $main;
		private $tpl;
		
		
		public function __construct($ipp, $items, $routerFile, $routerVars)
		{
			$this -> ipp = $ipp;
			$this -> items = $items;
			$this -> routerFile = $routerFile;
			$this -> routerVars = $routerVars;
			
			$this -> main = OPB::getInstance();
			
			// get the number of active page
			if(!$this -> main -> request -> map('p', OPB_GET, MAP_INTEGER | MAP_GT, 0))
			{
				$this -> current = 1;			
			}
			else
			{
				$this -> current = $this -> main -> request -> p;
			}
			
			// do some important calculation about the number of pages..
			$mod = $this -> items % $this -> ipp;
			$this -> pages = ($this -> items - $mod) / $this -> ipp;
			if($mod > 0)
			{
				$this -> pages++;
			}
			
			if($this -> current > $this -> pages)
			{
				$this -> pages = $this -> current;
			}
		} // end __construct();
		
		public function getLimitClause()
		{
			return 'LIMIT '.(($this -> current - 1) * $this -> ipp).', '.$this->ipp;		
		} // end getLimitClause();
		
		public function getCurrent()
		{
			return $this -> current;		
		} // end getCurrent();
		
		public function getTotal()
		{
			return $this -> pages;		
		} // end getTotal();
		
		public function getLinks()
		{
			if(!is_object($this -> tpl))
			{
				$this -> tpl = opbTemplate::getInstance();
			}
		
			// ok, we write those page links that must be shown to a special buffer
			$this -> buffer = array();
			if($this -> pages > 8)
			{
				$this -> writeToBuffer(1);
				$buf[1] = 1;
				for($i = $this -> current - 3; $i < $this -> current + 4; $i++)
				{
					$this -> writeToBuffer($i);	
				}
				$this -> writeToBuffer($this -> pages);
			}
			else
			{
				for($i = 1; $i <= $this -> pages; $i++)
				{
					$buf[$i] = 1;
				}
			}
			
			$begin = '';
			$end = '';
			if($this -> pages > 1)
			{
				if($this -> current == 1)
				{
					$end = $this -> printSpecial('pageSystemNextItem', $this -> current + 1);			
				}
				elseif($this -> current == $this -> pages)
				{
					$begin = $this -> printSpecial('pageSystemPreviousItem', $this -> current - 1);
				}
				else
				{
					$begin = $this -> printSpecial('pageSystemPreviousItem', $this -> current - 1);
					$end = $this -> printSpecial('pageSystemNextItem', $this -> current + 1);
				}			
			}
			
			$j = 0;
			$html = $begin;
			foreach($this -> buffer as $page => $void)
			{
				$j++;
				$html .= $this -> printBufferItem($page);
				if(!isset($this -> buffer[$page+1]) && $j < count($this -> buffer))
				{
					$html .= $this -> tpl -> getInfo('pageSystemFarItems');
				}
			}

			return $html.$end;
		} // end getLinks();
		
		private function writeToBuffer($page)
		{
			// make sure this position exists
		    if($page >= 1 && $page <= $this -> pages)
			{
				$this -> buffer[$page] = 1;
		    }
		} // end writeToBuffer();
		
		private function printBufferItem($i)
		{
			$handle = 'pageSystemItem';
			if($i == $this -> current)
			{
				$handle = 'pageSystemCurrentItem';			
			}
			$this -> routerVars['p'] = $i;
			return sprintf($this -> tpl -> getInfo($handle), $this -> main -> router -> createURL($this -> routerFile, $this -> routerVars), $i);
		} // end printBufferItem();
		
		private function printSpecial($symbol, $page)
		{
			$this -> routerVars['p'] = $page;
			return sprintf($this -> tpl -> getInfo($symbol), $this -> main -> router -> createURL($this -> routerFile, $this -> routerVars));
		} // end printSpecial();	
	}
?>
