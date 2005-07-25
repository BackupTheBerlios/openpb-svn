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

	class opt_instruction extends opt_node
	{
		static public function configure()
		{
			return array();		
		} // end configure();
	}
	
	class opt_section extends opt_instruction
	{
		static public function configure()
		{
			return array(
				'section' => OPT_MASTER,
				'sectionelse' => OPT_ALT,
				'/section' => OPT_ENDER
			);
		} // end configure();
		
		public function process()
		{
			$sectionelse = 0;
			foreach($this -> data as $id => $group)
			{
				switch($group[2])
				{
					case 'section':
							$this -> section_begin($group);
							if(count($this->subitems[$id]) > 0)
							{
								foreach($this->subitems[$id] as $subitem)
								{
									$subitem -> process();
								}
							}
							break;
					case 'sectionelse':
							$sectionelse = 1;
							$this -> section_else();
							if(count($this->subitems[$id]) > 0)
							{
								foreach($this->subitems[$id] as $subitem)
								{
									$subitem -> process();				
								}
							}
							break;
					case '/section':
							$this -> section_end($sectionelse);
							break;				
				}			
			}		
		} // end process();
		
		private function section_begin($group)
		{
			$params = array(
				'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID)
			);
			$this -> compiler -> parametrize_error('section', $this -> compiler -> parametrize($group, $params));
			# NESTING_LEVEL
		 	$this -> compiler -> check_nesting_level('section');
		 	# /NESTING_LEVEL

		 	$this -> compiler -> nesting_level['section']++;
		 	$this -> compiler -> nesting_names['section'][$this -> compiler -> nesting_level['section']] = $params['name'];
		 	
		 	if($this -> compiler -> nesting_level['section'] == 1)
		 	{
		 		$this -> output .= '\'; if(count($this -> data[\''.$params['name'].'\']) > 0){ foreach($this -> data[\''.$params['name'].'\'] as $__'.$params['name'].'_id => &$__'.$params['name'].'_val){ '.$this -> compiler -> tpl -> capture_to.' \'';
		 	}
		 	else
		 	{
		 		$lnk = '$this -> data[\''.$params['name'].'\']';
		 		$i = 0;
		 		foreach($this -> compiler -> nesting_names['section'] as $name)
		 		{
		 			if($i < count($this -> compiler -> nesting_names['section']) - 1)
		 			{
		 				$lnk .= '[$__'.$name.'_id]';
		 			}
		 			$i++;
		 		}
	
		 		$this -> output .= '\'; if(count('.$lnk.') > 0){ foreach('.$lnk.' as $__'.$params['name'].'_id => &$__'.$params['name'].'_val){ '.$this -> compiler -> tpl -> capture_to.' \'';
		 	}
		} // end section_begin();
		
		private function section_else()
		{
			$this -> output .= '\'; } }else{ '.$this -> compiler -> tpl -> capture_to.' \'';
		
		} // end section_begin();
		
		private function section_end($sectionelse)
		{
	 		unset($this -> compiler -> nesting_names['section'][$this -> compiler -> nesting_level['section']]);
	 		$this -> compiler -> nesting_level['section']--;
			if($sectionelse == 1)
			{
				$this -> output .= '\'; } '.$this -> compiler -> tpl -> capture_to.' \'';		
			}
			else
			{
				$this -> output .= '\'; } } '.$this -> compiler -> tpl -> capture_to.' \'';
			}
		} // end section_begin();
	}
	
	class opt_include extends opt_instruction
	{
		static public function configure()
		{
			return array(
				'include' => OPT_COMMAND
			);
		} // end configure();
		
		public function process()
		{
			$params = array(
				'file' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
				'default' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL),
				'assign' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, NULL),
			);
			$this -> compiler -> parametrize_error('include', $this -> compiler -> parametrize($this->data[0], $params));
	
			if($params['default'] != NULL)
			{
				$code = ' if($this->check_existence('.$params['file'].'){ $this -> do_include('.$params['file'].', $nesting_level + 1); }else{ $this -> do_include('.$params['default'].', $nesting_level + 1); } ';
			}
			else
			{
				$code = '$this -> do_include('.$params['file'].', $nesting_level + 1);';
			}
	
			if($this -> compiler -> tpl -> conf['include_optimization'] == 1)
			{
				if(($this -> compiler -> nesting_level['section'] > 0 || $this -> compiler -> nesting_level['for'] > 0 || $this -> compiler -> nesting_level['foreach'] > 0) && preg_match('/\"([a-zA-Z0-9\-\_\.\:]+?)\"/', $params['file'], $found) && $params['default'] == NULL)
				{
					// ok, we are in the loop, so try to include the template RIGHT NOW
					$file = '';
					$res = $this -> compiler -> tpl -> get_resource_info($found[1], $file);
					$code = $this -> compiler -> parse($res -> load_source($file));
				}
			}
	
			if($params['assign'] != NULL)
			{
				$this -> output .= '\'; $this -> capture_to = \'$this->vars[\\\''.$params['assign'].'\\\']\'; '.$code.' $this -> capture_to = \''.$this -> compiler -> tpl -> capture_to.'\'; '.$this -> compiler -> tpl -> capture_to.' \'';
			}
			else
			{
				$this -> output .= '\'; '.$code.' '.$this -> compiler -> tpl -> capture_to.' \'';
			}
		} // end process();
	}
	
	class opt_var extends opt_instruction
	{
		static public function configure()
		{
			return array(
				'var' => OPT_COMMAND
			);
		} // end configure();

		public function process()
		{
			$params = array(
				'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'value' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION)
			);
			$this -> compiler  -> parametrize_error('var', $this -> compiler  -> parametrize($this->data[0], $params));
	
			$this -> output .= '\'; $this -> vars[\''.$params['name'].'\'] = '.$params['value'].'; '.$this -> compiler -> tpl -> capture_to.' \'';
		} // end process();
	}
	
	class opt_default extends opt_instruction
	{
		static public function configure()
		{
			return array(
				'default' => OPT_COMMAND
			);
		} // end configure();

		public function process()
		{
			$params = array(
				'test' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
				'alt' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
			);
			$this -> compiler -> parametrize_error('default', $this -> compiler -> parametrize($this->data[0], $params));
			
			$this -> output .= '\'.(isset('.$params['test'].') ? '.$params['test'].' : '.$params['alt'].').\'';
		} // end process();
	}

	class opt_if extends opt_instruction
	{
		static public function configure()
		{
			return array(
				'if' => OPT_MASTER,
				'elseif' => OPT_ALT,
				'else' => OPT_ALT,
				'/if' => OPT_ENDER
			);
		} // end configure();
		
		public function process()
		{
			foreach($this -> data as $id => $group)
			{
				switch($group[2])
				{
					case 'if':
							$this -> if_begin($group);
							if(count($this->subitems[$id]) > 0)
							{
								foreach($this->subitems[$id] as $subitem)
								{
									$subitem -> process();
								}
							}
							break;
					case 'elseif':
							$this -> if_elseif($group);
							if(count($this->subitems[$id]) > 0)
							{
								foreach($this->subitems[$id] as $subitem)
								{
									$subitem -> process();				
								}
							}
							break;
					case 'else':
							$this -> if_else($group);
							if(count($this->subitems[$id]) > 0)
							{
								foreach($this->subitems[$id] as $subitem)
								{
									$subitem -> process();				
								}
							}
							break;
						
					case '/if':
							$this -> if_end();
							break;
				}			
			}		
		} // end process();
		
		private function if_begin($group)
		{
			# NESTING_LEVEL
		 	$this -> compiler -> check_nesting_level('if');
		 	
		 	$this -> compiler -> nesting_level['if']++;
		 	# /NESTING_LEVEL
		 	
			if($this -> compiler->tpl->conf['xmlsyntax_mode'] == 1 || $this -> compiler->tpl->conf['strict_syntax'] == 1)
			{
				$params = array(
					'test' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION)
				);
				$this -> compiler -> parametrize_error('if', $this -> compiler -> parametrize($group, $params));
				$this -> output .= '\'; if('.$params['test'].'){ '.$this -> compiler -> tpl -> capture_to.' \'';
			}
			else
			{
				$this -> output .= '\'; if('.$this -> compiler -> compile_expression($group[4]).'){ '.$this -> compiler -> tpl -> capture_to.' \'';
			}
		} // end if_begin();
		
		private function if_elseif($group)
		{
			# NESTING_LEVEL
			if($this -> compiler -> nesting_level['if'] > 0)
			{
			# /NESTING_LEVEL
				if($this -> compiler->tpl->conf['xmlsyntax_mode'] == 1 || $this -> compiler->tpl->conf['strict_syntax'] == 1)
				{
					$params = array(
						'test' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION)
					);
					$this -> compiler -> parametrize_error('if', $this -> compiler -> parametrize($group, $params));
					$this -> output .= '\'; }elseif('.$params['test'].'){ '.$this -> compiler -> tpl -> capture_to.' \'';
				}
				else
				{
					$this -> output .= '\'; }elseif('.$this -> compiler -> compile_expression($group[4]).'){ '.$this -> compiler -> tpl -> capture_to.' \'';
				}
		 	# NESTING_LEVEL
		 	}
			else
			{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, 'ELSEIF called when not in IF.', 203);
		 	}
		 	# /NESTING_LEVEL
		} // end if_elseif();
		
		private function if_else($group)
		{
			# NESTING_LEVEL
		 	if($this -> compiler -> nesting_level['if'] > 0)
			{
			# /NESTING_LEVEL
		 		$this -> output .= '\'; }else{ '.$this -> compiler -> tpl -> capture_to.' \'';
		 	# NESTING_LEVEL
		 	}
			else
			{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, 'ELSE called when not in IF.', 204);
		 	}
		 	# /NESTING_LEVEL
		} // end if_else();
		
		private function if_end()
		{
			# NESTING_LEVEL
		 	if($this -> compiler -> nesting_level['if'] > 0)
			{
		 		$this -> compiler -> nesting_level['if']--;
		 	# /NESTING_LEVEL
		 		$this -> output .= '\'; } '.$this -> compiler -> tpl -> capture_to.' \'';
		 	# NESTING_LEVEL
		 	}
			else
			{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, '/IF called when not in IF.', 205);
		 	}
		 	# /NESTING_LEVEL
		} // end if_end();
	}
	
	class opt_capture extends opt_instruction
	{
		static public function configure()
		{
			return array(
				'capture' => OPT_MASTER,
				'/capture' => OPT_ENDER
			);
		} // end configure();
		
		public function process()
		{
			foreach($this -> data as $id => $group)
			{
				switch($group[2])
				{
					case 'capture':
							$this -> capture_begin($group);
							if(count($this->subitems[$id]) > 0)
							{
								foreach($this->subitems[$id] as $subitem)
								{
									$subitem -> process();
								}
							}
							break;
					case '/capture':
							$this -> capture_end();
							break;
				}
			}
		} // end process();
		
		private function capture_begin($group)
		{
			$params = array(
				'to' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID)
			);
			$this -> compiler -> parametrize_error('capture', $this -> compiler -> parametrize($group, $params));
			if($this -> compiler -> tpl -> capture_to == $this -> compiler -> tpl -> capture_def)
			{
				$this -> compiler -> tpl -> capture_to = '$this -> capture[\''.$params['to'].'\'] .= ';
				$this -> output .= '\'; '.$this -> compiler -> tpl -> capture_to.' \'';
			}
			else
			{
				$this -> compiler -> tpl -> error(E_USER_ERROR, 'Trying to call sub-capture command ('.$params['to'].')', 206);
			}
		} // end capture_begin();
		
		private function capture_end()
		{
			if($this -> compiler -> tpl -> capture_to != $this -> compiler -> tpl -> capture_def)
			{
				$this -> compiler -> tpl -> capture_to = $this -> compiler -> tpl -> capture_def;
				$this -> output .= '\'; '.$this -> compiler -> tpl -> capture_to.' \'';
			}
			else
			{
				$this -> compiler -> tpl -> error(E_USER_ERROR, 'Trying to call sub-capture command ('.$matches[4].')', 206);
			}
		} // end capture_end();
	}
	
	class opt_for extends opt_instruction
	{
		static public function configure()
		{
			return array(
				'for' => OPT_MASTER,
				'/for' => OPT_ENDER
			);
		} // end configure();
		
		public function process()
		{
			foreach($this -> data as $id => $group)
			{
				switch($group[2])
				{
					case 'for':
							$this -> for_begin($group);
							if(count($this->subitems[$id]) > 0)
							{
								foreach($this->subitems[$id] as $subitem)
								{
									$subitem -> process();
								}
							}
							break;
					case '/for':
							$this -> for_end();
							break;
				}
			}
		} // end process();
		
		private function for_begin($group)
		{
			$params = array(
				'begin' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'end' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ASSIGN_EXPR),
				'iterate' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ASSIGN_EXPR)
			);
			$this -> compiler -> parametrize_error('for', $this -> compiler -> parametrize($group, $params));
	
			# NESTING_LEVEL
		 	$this -> compiler -> check_nesting_level('for');
		 	
		 	$this -> compiler -> nesting_level['for']++;
		 	# /NESTING_LEVEL
	
	 		$this -> output .= '\'; for($this->vars[\''.$params['begin'].'\'] = 0; '.$params['end'].'; '.$params['iterate'].'){ '.$this -> compiler->tpl->capture_to.' \'';
		} // end for_begin();
		
		private function for_end()
		{
			# NESTING_LEVEL
		 	if($this -> compiler -> nesting_level['for'] > 0)
		 	{
		 		$this -> compiler -> nesting_level['for']--;
		 	# /NESTING_LEVEL
		 		$this -> output .= '\'; } '.$this -> compiler -> tpl -> capture_to.' \'';
		 	# NESTING_LEVEL
		 	}
		 	else
		 	{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, '/FOR called when not in FOR.', 208);
		 	}
		 	# /NESTING_LEVEL
		} // end for_end();
	}
	
	class opt_foreach extends opt_instruction
	{
		static public function configure()
		{
			return array(
				'foreach' => OPT_MASTER,
				'foreachelse' => OPT_ALT,
				'/foreach' => OPT_ENDER
			);
		} // end configure();
		
		public function process()
		{
			$foreachelse = 0;
			foreach($this -> data as $id => $group)
			{
				switch($group[2])
				{
					case 'foreach':
							$this -> foreach_begin($group);
							if(count($this->subitems[$id]) > 0)
							{
								foreach($this->subitems[$id] as $subitem)
								{
									$subitem -> process();
								}
							}
							break;
					case 'foreachelse':
							$foreachelse = 1;
							$this -> foreach_else();
							if(count($this->subitems[$id]) > 0)
							{
								foreach($this->subitems[$id] as $subitem)
								{
									$subitem -> process();				
								}
							}
							break;
					case '/foreach':
							$this -> foreach_end($foreachelse);
							break;				
				}			
			}		
		} // end process();
		
		private function foreach_begin($group)
		{
			# NESTING_LEVEL
			$this -> compiler -> check_nesting_level('foreach');
			$this -> compiler -> nesting_level['foreach']++;
			# /NESTING_LEVEL
	
			$params = array(
				'table' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
				'index' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'value' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, NULL)
			);
			$this -> compiler -> parametrize_error('for', $this -> compiler -> parametrize($group, $params));
	
			if($params['value'] == NULL)
			{
				$this -> output .= '\'; if(count('.$params['table'].') > 0){ foreach('.$params['table'].' as &$__f_'.$this -> compiler -> nesting_level['foreach'].'_val){ $this -> vars[\''.$params['id'].'\'] = &$__f_'.$this -> compiler -> nesting_level['foreach'].'_val; '.$cpl -> tpl -> capture_to.' \'';
			}
			else
			{
				$this -> output .= '\'; if(count('.$params['table'].') > 0){ foreach('.$params['table'].' as $__f_'.$this -> compiler -> nesting_level['foreach'].'_id => &$__f_'.$this -> compiler -> nesting_level['foreach'].'_val){ $this -> vars[\''.$params['index'].'\'] = $__f_'.$this -> compiler -> nesting_level['foreach'].'_id; $this -> vars[\''.$params['value'].'\'] = &$__f_'.$this -> compiler -> nesting_level['foreach'].'_val; '.$this -> compiler -> tpl -> capture_to.' \'';
			}
		} // end foreach_begin();
		
		private function foreach_else()
		{
		 	# NESTING_LEVEL
		 	if($this -> compiler -> nesting_level['foreach'] == 0)
		 	{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, 'there are no opened foreach loops! Open a loop first!', 210);
		 	}
		 	# /NESTING_LEVEL
		 	$this -> output .= '\'; } }else{ { '.$this -> compiler -> tpl -> capture_to.' \'';		
		} // end foreach_else();
		
		private function foreach_end($foreachelse)
		{
			# NESTING_LEVEL
		 	if($this -> compiler -> nesting_level['foreach'] > 0)
		 	{
		 		$this -> compiler -> nesting_level['foreach']--;
		 	# /NESTING_LEVEL
		 		if($foreachelse == 1)
		 		{
		 			$this -> output .= '\'; } '.$this -> compiler -> tpl -> capture_to.' \'';
		 		}
		 		else
		 		{
		 			$this -> output .= '\'; } } '.$this -> compiler -> tpl -> capture_to.' \'';
		 		}
		 	# NESTING_LEVEL
		 	}
		 	else
		 	{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, '/FOREACH called when not in FOREACH.', 211);
		 	}
		 	# /NESTING_LEVEL
		} // end foreach_end();
	}
	
	class opt_php extends opt_instruction
	{
		static public function configure()
		{
			return array(
				'php' => OPT_MASTER,
				'/php' => OPT_ENDER
			);
		} // end configure();
		
		public function process()
		{
			foreach($this -> data as $id => $group)
			{
				switch($group[2])
				{
					case 'php':
							$this -> for_begin($group);
							if(count($this->subitems[$id]) > 0)
							{
								if($this -> compiler -> tpl -> conf['safe_mode'] == 1)
								{
									foreach($this->subitems[$id] as $subitem)
									{
										$subitem -> process();
									}
								}
								else
								{
									foreach($this->subitems[$id] as $subitem)
									{
										if(!($subitem instanceof opt_text))
										{
											die('Bugugu');
										}
										$subitem -> process();
									}
								}
							}
							break;
					case '/php':
							$this -> for_end();
							break;
				}
			}
		} // end process();
		
		private function php_begin($group)
		{
			if($this -> compiler -> tpl -> conf['safe_mode'] == 1)
			{
				$this -> output .= $group[6];			
			}
			else
			{
				$this -> output .= '\'; ';
			}
		} // end php_begin();
		
		private function php_end()
		{
			if($this -> compiler -> tpl -> conf['safe_mode'] == 1)
			{
				$this -> output .= $group[6];			
			}
			else
			{
				$this -> output .= ' '.$this -> compiler -> tpl -> capture_to.' \'';
			}
		} // end php_end();
	}
	
	class opt_dynamic extends opt_instruction
	{
		static public function configure()
		{
			return array(
				'dynamic' => OPT_MASTER,
				'/dynamic' => OPT_ENDER
			);
		} // end configure();
		
		public function process()
		{
			foreach($this -> data as $id => $group)
			{
				switch($group[2])
				{
					case 'dynamic':
							$this -> dynamic_begin($group);
							if(count($this->subitems[$id]) > 0)
							{
								if($this -> compiler -> tpl -> conf['safe_mode'] == 1)
								{
									foreach($this->subitems[$id] as $subitem)
									{
										$subitem -> process();
									}
								}
								else
								{
									foreach($this->subitems[$id] as $subitem)
									{
										$subitem -> process();
									}
								}
							}
							break;
					case '/dynamic':
							$this -> dynamic_end();
							break;
				}
			}
		} // end process();
		
		private function dynamic_begin($group)
		{
			if($this -> compiler -> tpl -> get_status() == false)
			{
				return '';
			}
		
			if($this -> compiler -> nesting_level['dynamic'] == 0)
			{
				$this -> compiler -> nesting_level['dynamic'] = 1;
				$this -> output .= '\'; $this -> cache_output[] = ob_get_contents(); /* #@#DYNAMIC#@# */ '.$this -> compiler -> tpl -> capture_to.' \'';
			}
			else
			{
				$this -> compiler -> tpl -> error(E_USER_ERROR, 'Dynamic section already opened', 214);
			}
		} // end dynamic_begin();
		
		private function dynamic_end()
		{
			if($this -> compiler -> tpl -> get_status() == false)
			{
				return '';
			}
	
			if($this -> compiler -> nesting_level['dynamic'] == 1)
			{
				$this -> compiler -> nesting_level['dynamic'] = 0;
				$this -> output .= '\'; /* #@#END DYNAMIC#@# */ ob_start(); '.$this -> compiler -> tpl -> capture_to.' \'';
			}
			else
			{
				$this -> compiler -> tpl -> error(E_USER_ERROR, 'Dynamic section already closed', 215);
			}
		} // end dynamic_end();
	}

	class opt_component extends opt_node
	{
		private $params;

		public function __construct($name, &$output, $compiler, $type, $parent)
		{
			parent::__construct($name, $output, $compiler, $type, $parent);
			$this -> params = array();
		} // end __construct();

		public function add_param($name, $value)
		{
			$this -> params[$name] = $value;
		} // end add_param();
		
		public function process()
		{
			static $cid;
			if($cid == NULL)
			{
				$cid = 0;
			}

			// decide, whether to create the component automatically or not
			$cond_begin = 0;
			$component_link = '';
			if($this -> name == 'component')
			{
				$params = array(
					'id' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
					'datasource' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
				);
				$this -> compiler  -> parametrize_error($this -> name, $this -> compiler  -> parametrize($this->data[0], $params));
				$this -> output .= '\'; if($this -> data[\''.$params['id'].'\'] instanceof iopt_component){ ';
				$component_link = '$this -> data[\''.$params['id'].'\']';
				$cond_begin = 1;			
			}
			else
			{
				$params = array(
					'datasource' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION, NULL)
				);
				$this -> compiler  -> parametrize_error($this -> name, $this -> compiler  -> parametrize($this->data[0], $params));
				$this -> output .= '\'; $__component_'.$cid.' = new '.$this -> name.'(); 
					$__component_'.$cid.' -> set_datasource('.$params['datasource'].'); ';
				$component_link = '$__component_'.$cid;	
			}
			// add parameters
			if(count($this -> params) > 0)
			{
				foreach($this -> params as $name => $value)
				{
					$this -> output .= $component_link.' -> set(\''.$name.'\', '.$value.'); ';
				}
			}
			
			// sort the events
			$events_up = array();
			$events_mid = array();
			$events_down = array();
			
			foreach($this -> subitems[0] as $event)
			{
				if($event -> get_type() == OPT_TEXT)
				{
					// ignore
					continue;
				}
				// for debug purposes
				if($event -> get_type() != OPT_EVENT)
				{
					echo '<h1>CRITICAL BUG</h1>';
					echo 'Critical error: non-event instruction inside a component found! Please inform the developers! Add the track below!';
					echo '<pre>';
					print_r($this);
					echo '</pre>';
					die();				
				}

				switch($event -> data[0]['position'])
				{
					case 'up':
						$events_up[] = $event;
						break;
					case 'mid':
						$events_mid[] = $event;
						break;
					case 'down':
					default:
						$events_down[] = $event;				
				}			
			}
		
			// now generate the code
			// first, the "up" events go
			if(count($events_up) > 0)
			{
				foreach($events_up as $event)
				{
					$event -> process($component_link);
				}			
			}
			
			$this -> output .= ' '.$this -> compiler -> tpl -> capture_to.' '.$component_link.' -> begin($this); ';
			
			// "mid" events
			if(count($events_mid) > 0)
			{
				foreach($events_mid as $event)
				{
					$event -> process($component_link);
				}			
			}
			
			$this -> output .= ' '.$this -> compiler -> tpl -> capture_to.' '.$component_link.' -> end($this); ';
			
			// "down" events
			if(count($events_down) > 0)
			{
				foreach($events_down as $event)
				{
					$event -> process($component_link);
				}			
			}
			
			// terminate the processing
			if($cond_begin == 1)
			{
				$this -> output .= ' } '.$this -> compiler -> tpl -> capture_to.' \'';			
			}
			else
			{
				$this -> output .= ' '.$this -> compiler -> tpl -> capture_to.' \'';			
			}
		} // end process();	
	}
	
	class opt_event extends opt_node
	{	
	
		public function process($cid)
		{
			$this -> output .= ' if('.$cid.' -> '.$this->name.'($this, \''.$this->data[0]['message'].'\')) { '.($capture = $this -> compiler -> tpl -> capture_to).' \'';
			if(count($this->subitems[0]) > 0)
			{
				foreach($this->subitems[0] as $subitem)
				{
					$subitem -> process();			
				}
			}
			// be sure the capture destination change will not affect the event...
			$this -> output .= '\'; } ';
		} // end process();
	} // end opt_event;
?>
