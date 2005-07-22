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

	interface iopt_component
	{
		public function __construct($name = '');
		public function set($name, $value);
		public function set_datasource(&$source);
		public function begin(opt_template $tpl);
		public function end(opt_template $tpl);
	}

	class selectComponent implements iopt_component
	{
		private $_list;
		private $name;
		private $message;

		public function __construct()
		{
			$this -> _list = array();
			$this -> message = NULL;		
		} // end __construct();
		
		public function set($name, $value)
		{
			if($name == 'name')
			{
				$this -> name = $value;
			}
			if($name == 'message')
			{
				$this -> message = $value;
			}
			if($name == 'selected')
			{
				foreach($this -> _list as $i => &$item)
				{
					if($item['value'] == $value)
					{
						$item['selected'] = true;
					}				
				}			
			}
		} // end set();
		
		public function push($value, $desc, $selected = false)
		{
			$this -> _list[] = array(
				'value' => $value,
				'desc' => $desc,
				'selected' => $selected		
			);
		} // end push();

		public function set_datasource(&$source)
		{
			$this -> _list = $source;		
		} // end set_datasource();

		public function begin(opt_template $tpl)
		{
			$code = '<select name="'.$this->name.'">';
			$selected = 0;
			foreach($this -> _list as $item)
			{
				if($item['selected'] == 1 && $selected == 0)
				{
					$code .= '<option value="'.$item['value'].'" selected="selected">'.$item['desc'].'</option>';
					$selected = 1;
				}
				else
				{
					$code .= '<option value="'.$item['value'].'">'.$item['desc'].'</option>';
				}		
			}
			$code .= '</select>';
			return $code;
		} // end begin();
		
		public function onmessage(opt_template $tpl, $pass_to)
		{
			if($this -> message == NULL)
			{
				return 0;
			}
			$tpl -> vars[$pass_to] = $this -> message;
			return 1;		
		} // end onmessage();

		public function end(opt_template $tpl)
		{
			return '';		
		} // end end();
	}
?>
