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

	class textInputComponent implements iopt_component
	{
		protected $name;
		protected $value;
		protected $message;

		public function __construct()
		{
			$this -> name = NULL;
			$this -> value = NULL;
			$this -> message = NULL;		
		} // end __construct();

		public function set($name, $value)
		{
			switch($name)
			{
				case 'name':
					$this -> name = $value;
					break;
				case 'value':
					$this -> value = $value;
					break;
				case 'message':
					$this -> message = $value;
					break;		
			}
		} // end set();

		public function set_datasource(&$source)
		{
			if(is_array($source))
			{
				if(isset($source['name']))
				{
					$this -> name = $source['name'];
				}
				if(isset($source['value']))
				{
					$this -> value = $source['value'];
				}
				if(isset($source['message']))
				{
					$this -> name = $source['message'];
				}
			}
		} // end set_datasource();

		public function begin(opt_template $tpl)
		{
			$code = '<input type="text" name="'.$this->name.'"';
			if($this -> value != NULL)
			{
				$code .= ' value="'.htmlspecialchars($this->value).'"';
			}
			return $code . ' />';
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
	
	class textLabelComponent extends textinputComponent implements iopt_component
	{
		public function begin(opt_template $tpl)
		{
			$code = '<input type="hidden" name="'.$this->name.'"';
			if($this -> value != NULL)
			{
				$code .= ' value="'.htmlspecialchars($this->value).'"';
			}
			$code .= ' />';
			if($this -> value != NULL)
			{
				return $code.'<span class="label">'.htmlspecialchars($this->value).'</span>';
			}
			return $code;
		} // end begin();
	}
	
	class formActionsComponent implements iopt_component
	{
		private $buttons;

		public function __construct()
		{
			$this -> buttons = array();
		} // end __construct();

		public function set($name, $value)
		{
		} // end set();
		
		public function push($name, $value, $type = 'submit')
		{
			$this -> buttons[] = array(
				'name' => $name,
				'value' => $value,
				'type' => $type
			);		
		} // end set_datasource();

		public function set_datasource(&$source)
		{
			$this -> buttons = $source;
		} // end set_datasource();

		public function begin(opt_template $tpl)
		{
			$code = '';
			foreach($this -> buttons as $button)
			{
				$code .= '<input type="'.$button['type'].'"'.($button['name'] != NULL ? ' name="'.$button['name'].'"' : '').' value="'.$button['value'].'"/>';			
			}
			return $code;
		} // end begin();

		public function end(opt_template $tpl)
		{
			return '';		
		} // end end();
	}
?>
