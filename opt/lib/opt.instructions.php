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

	class optInstruction
	{
		protected $compiler;
		protected $output;
		
		public function __construct(optCompiler $compiler)
		{
			$this -> compiler = $compiler;
		} // end __construct();
		
		public function setOutput(&$output)
		{
			$this -> output = &$output;
		} // end setOutput();

		public function configure()
		{
			return array();
		} // end configure();
		
		public function defaultTreeProcess(optBlock $block)
		{
			if($block -> hasChildNodes())
			{
				foreach($block as $node)
				{
					$this -> nodeProcess($node);
				}
			}
		} // end defaultTreeProcess();
		
		public function nodeProcess(ioptNode $node)
		{
			switch($node -> getType())
			{
				case OPT_ROOT:
					$this -> defaultTreeProcess($node -> getFirstBlock());
					break;
				case OPT_TEXT:
					$this -> compiler -> out($node->__toString(), true);
					break;
				case OPT_EXPRESSION:
					$result = $this -> compiler -> compileExpression($node->getFirstBlock()->getAttributes(), 1);
					if($result[1] == 1)
					{
						// we have an assignment, so we must build different code
						$this -> compiler -> out($result[0].';');
					}
					else
					{		
						$this -> compiler -> out('echo '.$result[0].';');
					}
					break;
				case OPT_INSTRUCTION:
					$this -> instructionProcess($node);
					break;
				case OPT_COMPONENT:
					$this -> compiler -> processors['component'] -> instructionNodeProcess($node);
					break;
			}
		} // end nodeProcess();
		
		public function instructionProcess(ioptNode $node)
		{
			if($node -> getType() == OPT_INSTRUCTION)
			{
				// is there any processor for this instruction?
				if(!isset($this -> compiler -> mapper[$node -> getName()]))
				{
					return 0;
				}
				
				// pass the execution to the instruction processor
				$this -> compiler -> mapper[$node -> getName()] -> instructionNodeProcess($node);
				return 1;
			}	
			return 0;
		} // end instructionProcess();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			
		} // end instructionNodeProcess();
		
		public function processOpt($namespace)
		{
			return 'true';
		} // end processOpt();
	}
	
	class optSection extends optInstruction
	{
		private $sections = array(0 => array());
		public $nesting = 0;
	
		public function configure()
		{
			return array(
				// processor name
				0 => 'section',
				// instructions
				'section' => OPT_MASTER,
				'sectionelse' => OPT_ALT,
				'/section' => OPT_ENDER,
				'show' => OPT_MASTER,
				'showelse' => OPT_ALT,
				'/show' => OPT_ENDER
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{				
				switch($block -> getName())
				{
					case 'show':
							$this -> showBegin($block -> getAttributes());
							$this -> defaultTreeProcess($block);
							break;
					case 'showelse':
							$this -> showElse();
							$this -> defaultTreeProcess($block);
							break;
					case '/show':
							$this -> showEnd();
							break;
					case 'section':
							$this -> sectionBegin($block -> getAttributes());
							$this -> defaultTreeProcess($block);
							break;
					case 'sectionelse':
							$this -> sectionElse();
							$this -> defaultTreeProcess($block);
							break;
					case '/section':
							$this -> sectionEnd();
							break;
				}
			}
		} // end process();
		
		public function showBegin($paramStr)
		{
			$params = array(
				'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'order' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_STRING, NULL),
				'state' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL),
				'datasource' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
			);
			$this -> compiler -> parametrize('show', $paramStr, $params);
			$this -> showAction($params['name'], $params['order'], $params['state'], $params['datasource'], true);
		} // end showBegin();
		
		private function showAction($name, $order, $state, $datasource, $show)
		{
			$link = '';
			$syntax = $this -> getLink($name, $datasource, $link); 
			$output = '';
			if(is_null($state))
			{
				$output .= ' if(($__'.$name.'_cnt = count('.$link.')) > 0){ ';
			}
			else
			{
				if($this -> compiler -> tpl -> statePriority == OPT_PRIORITY_NORMAL)
				{
					$output .= ' if(($__'.$name.'_cnt = count('.$link.')) > 0 && '.$state.'){ ';
				}
				else
				{
					$output .= ' if('.$state.'){ if(($__'.$name.'_cnt = count('.$link.')) > 0){ ';
				}
			}

			$this -> sections[$this -> nesting] = array(
				'name' => $name,
				'order' => $order,
				'state' => $state,
				'link' => $link,
				'show' => $show,
				'else' => false
			);
			$this -> compiler -> out($output);
		} // end showAction();
		
		public function showElse()
		{
			if($this->sections[$this->nesting]['show'] == true)
			{
				$this -> compiler -> out(' } else { ');	
			}		
		} // end showElse();
		
		public function showEnd()
		{
			if($this->sections[$this->nesting]['show'] == true)
			{
				if(!is_null($this->sections[$this->nesting]['state']) && $this -> compiler -> tpl -> statePriority == OPT_PRIORITY_HIGH)
				{
					$this -> compiler -> out(' } } ');	
				}
				else
				{
					$this -> compiler -> out(' } ');	
				}
			}
			unset($this -> sections[$this -> nesting]);
		} // end showEnd();
		
		public function sectionBegin($paramStr)
		{
			if(@$this->sections[$this->nesting]['show'] != true)
			{
				$params = array(
					'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
					'order' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_STRING, NULL),
					'state' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL),
					'datasource' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
				);
				$this -> compiler -> parametrize('section', $paramStr, $params);
				$this -> showAction($params['name'], $params['order'], $params['state'], $params['datasource'], false);			
			}
			$name = $this->sections[$this->nesting]['name'];

			if($this->sections[$this->nesting]['order'] == 'reversed')
			{
				$this -> compiler -> out(' for($__'.$name.'_id = $__'.$name.'_cnt - 1; $__'.$name.'_id >= 0; $__'.$name.'_id--){ $__'.$name.'_val = &'.$this->sections[$this->nesting]['link'].'[$__'.$name.'_id]; ');		
			}			
			else
			{
				$this -> compiler -> out(' foreach('.$this->sections[$this->nesting]['link'].' as $__'.$name.'_id => &$__'.$name.'_val){ ');
			}
			$this -> nesting++;
		} // end sectionBegin();
		
		public function sectionElse()
		{
			if($this->sections[$this->nesting-1]['show'] == false)
			{
				$this->sections[$this->nesting-1]['else'] = true;
				$this -> compiler -> out(' } } else { ');		
			}
		} // end sectionElse();
		
		public function sectionEnd()
		{
			$this -> nesting--;
			if($this->sections[$this->nesting]['show'] == true)
			{				
				$this -> compiler -> out(' } ');			
			}
			else
			{
				if($this->sections[$this->nesting]['else'] == true)
				{
					$this -> compiler -> out(' } ');
				}
				else
				{
					$this -> compiler -> out(' } } ');
				}
				unset($this -> sections[$this -> nesting]);
			}
		} // end sectionEnd();

		private function getLink($name, $datasource, &$link)
		{
			if($this -> compiler -> tpl -> sectionStructure == OPT_SECTION_MULTI)
			{
				if(is_null($datasource))
				{
					$syntax = OPT_SECTION_MULTI;
					if($this -> nesting == 0)
					{
						$link = '$this -> data[\''.$name.'\']'; 
					}
					else
					{
						$link = '$this -> data[\''.$name.'\']';
						foreach($this -> sections as $item)
						{
							$link .= '[$__'.$item['name'].'_id]';
						}
					}
				}
				else
				{
					$syntax = OPT_SECTION_SINGLE;
					$link = $datasource;
				}
			}
			else
			{
				if(is_null($datasource))
				{
					$syntax = OPT_SECTION_SINGLE;
					if($this -> nesting == 0)
					{
						$link = '$this -> data[\''.$name.'\']'; 
					}
					else
					{
						$link = '$__'.$this->sections[$this->nesting-1]['name'].'_val[\''.$name.'\']';
					}
				}
				else
				{
					$syntax = OPT_SECTION_MULTI;
					$link = $datasource;
					if($this -> nesting == 0)
					{
						foreach($this -> sections as $item)
						{
							$link .= '[$__'.$item['name'].'_id]';
						}
					}
				}
			}
			return $syntax;
		} // end getLink();
		
		public function processOpt($namespace)
		{
			switch($namespace[3])
			{
				case 'count':
					return '$__'.$namespace[2].'_cnt';
				case 'id':
					return '$__'.$namespace[2].'_id';
				case 'size':
					return 'count($__'.$namespace[2].'_val)';
				case 'first':
					foreach($this -> sections as $id => &$void)
					{
						if($void['name'] == $namespace[2]);
						$sid = $id;
					}
					if($this -> sections[$sid]['order']=='reversed')
					{
						return '($__'.$namespace[2].'_id == $__'.$namespace[2].'_cnt - 1)';
					}
					return '($__'.$namespace[2].'_id == 0)';					
				case 'last':
					foreach($this -> sections as $id => &$void)
					{
						if($void['name'] == $namespace[2]);
						$sid = $id;
					}
					if($this -> sections[$sid]['order']=='reversed')
					{
						return '($__'.$namespace[2].'_id == 0)';
					}					
					return '($__'.$namespace[2].'_id == $__'.$namespace[2].'_cnt - 1)';
				default:
					$this -> tpl -> error(E_USER_ERROR, 'Unknown OPT section command: '.$namespace[3], 105);
			}
		} // end processOpt();
	}

	class optInclude extends optInstruction
	{
		public function configure()
		{
			return array(
				// processor name
				0 => 'include',
				// instructions
				'include' => OPT_COMMAND
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			$block = $node -> getFirstBlock();
			$params = array(
				'file' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
				'default' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL),
				'assign' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, NULL),
				'__UNKNOWN__' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
			);
			$variables = $this -> compiler -> parametrize('include', $block -> getAttributes(), $params);
			$code = '';
			foreach($variables as $name => $variable)
			{
				$code .= ' $this -> vars[\''.$name.'\'] = '.$variable.'; ';		
			}
			if($params['default'] != NULL)
			{
				$code .= ' if(!$this -> doInclude('.$params['file'].')){ $this -> doInclude('.$params['default'].'); } ';
			}
			else
			{
				$code .= '$this -> doInclude('.$params['file'].');';
			}
			$this -> compiler -> out($code);
		} // end instructionNodeProcess();
	}

	class optPlace extends optInstruction
	{
		public function configure()
		{
			return array(
				// processor name
				0 => 'place',
				// instructions
				'place' => OPT_COMMAND
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			$block = $node -> getFirstBlock();
			$params = array(
				'file' => array(OPT_PARAM_REQUIRED, OPT_PARAM_STRING),
				'assign' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, NULL),
			);
			$this -> compiler -> parametrize('place', $block->getAttributes(), $params);
	
			$file = '';
			if($params['assign'] != NULL)
			{	
				$this -> compiler -> out(' ob_start(); ');
			}
			$this -> compiler -> out($this -> compiler -> tpl -> getTemplate($params['file']), true);
			if($params['assign'] != NULL)
			{
				$this -> compiler -> out(' $this -> vars[\''.$params['assign'].'\'] .= ob_end_flush(); ');
			}
		} // end instructionNodeProcess();
	}
	
	class optVar extends optInstruction
	{
		public function configure()
		{
			return array(
				// processor name
				0 => 'var',
				// instructions
				'var' => OPT_COMMAND
			);
		} // end configure();

		public function instructionNodeProcess(ioptNode $node)
		{
			$block = $node -> getFirstBlock();
			$params = array(
				'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'value' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION)
			);
			$this -> compiler  -> parametrize('var', $block -> getAttributes(), $params);
	
			$this -> compiler -> out('$this -> vars[\''.$params['name'].'\'] = '.$params['value'].'; ');
		} // end instructionNodeProcess();
	}
	
	class optDefault extends optInstruction
	{
		public function configure()
		{
			return array(
				// processor name
				0 => 'default',
				// instructions
				'default' => OPT_COMMAND
			);
		} // end configure();

		public function instructionNodeProcess(ioptNode $node)
		{
			$block = $node -> getFirstBlock();
			$params = array(
				'test' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
				'alt' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
			);
			$this -> compiler -> parametrize('default', $block -> getAttributes(), $params);
			
			$this -> compiler -> out(' echo (isset('.$params['test'].') ? '.$params['test'].' : '.$params['alt'].'); ');
		} // end process();
	}

	class optIf extends optInstruction
	{
		private $nesting = 0;
	
		public function configure()
		{
			return array(
				// processor name
				0 => 'if',
				// instructions
				'if' => OPT_MASTER,
				'elseif' => OPT_ALT,
				'else' => OPT_ALT,
				'/if' => OPT_ENDER
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'if':
							$this -> ifBegin($block -> getAttributes());
							$this -> defaultTreeProcess($block);
							break;
					case 'elseif':
							$this -> ifElseif($block -> getAttributes());
							$this -> defaultTreeProcess($block);
							break;
					case 'else':
							$this -> ifElse($block -> getAttributes());
							$this -> defaultTreeProcess($block);
							break;
						
					case '/if':
							$this -> ifEnd();
							break;
				}			
			}		
		} // end process();
		
		private function ifBegin($group)
		{
			# nestingLevel	 	
		 	$this -> nesting++;
		 	# /nestingLevel
		 	
			if($this -> compiler -> tpl->xmlsyntaxMode == 1 || $this -> compiler -> tpl -> strictSyntax == 1)
			{
				$params = array(
					'test' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION)
				);
				$this -> compiler -> parametrize('if', $group, $params);
				$this -> compiler -> out(' if('.$params['test'].'){ ');
			}
			else
			{
				$this -> compiler -> out('; if('.$this -> compiler -> compileExpression($group[4]).'){ ');
			}
		} // end ifBegin();
		
		private function ifElseif($group)
		{
			# nestingLevel
			if($this -> nesting > 0)
			{
			# /nestingLevel
				if($this -> compiler->tpl->xmlsyntaxMode == 1 || $this -> compiler->tpl->strictSyntax == 1)
				{
					$params = array(
						'test' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION)
					);
					$this -> compiler -> parametrize('elseif', $group, $params);
					$this -> compiler -> out('; }elseif('.$params['test'].'){ ');
				}
				else
				{
					$this -> compiler -> out(' }elseif('.$this -> compiler -> compileExpression($group[4]).'){ ');
				}
		 	# nestingLevel
		 	}
			else
			{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, 'ELSEIF called when not in IF.', 201);
		 	}
		 	# /nestingLevel
		} // end ifElseif();
		
		private function ifElse($group)
		{
			# nestingLevel
		 	if($this -> nesting > 0)
			{
			# /nestingLevel
		 		$this -> compiler -> out(' }else{ ');
		 	# nestingLevel
		 	}
			else
			{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, 'ELSE called when not in IF.', 202);
		 	}
		 	# /nestingLevel
		} // end ifElse();
		
		private function ifEnd()
		{
			# nestingLevel
		 	if($this -> nesting > 0)
			{
		 		$this -> nesting--;
		 	# /nestingLevel
		 		$this -> compiler -> out(' } ');
		 	# nestingLevel
		 	}
			else
			{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, '/IF called when not in IF.', 203);
		 	}
		 	# /nestingLevel
		} // end ifEnd();
	}
	
	class optCapture extends optInstruction
	{
		private $active = false;
		private $name = '';
	
		public function configure()
		{
			return array(
				// processor name
				0 => 'capture',
				// instructions
				'capture' => OPT_MASTER,
				'/capture' => OPT_ENDER
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'capture':
							$this -> captureBegin($block -> getAttributes());
							$this -> defaultTreeProcess($block);
							break;
					case '/capture':
							$this -> captureEnd();
							break;
				}
			}
		} // end process();
		
		private function captureBegin($group)
		{
			$params = array(
				'to' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID)
			);
			$this -> compiler -> parametrize('capture', $group, $params);
			if(!$this -> active)
			{
				$this -> active = true;
				$this -> name = $params['to'];
				$this -> compiler -> out(' ob_start(); ');
			}
			else
			{
				$this -> compiler -> tpl -> error(E_USER_ERROR, 'Trying to call sub-capture command ('.$params['to'].')', 204);
			}
		} // end captureBegin();
		
		private function captureEnd()
		{
			if($this -> active)
			{
				$this -> active = false;
				$this -> compiler -> out(' $this -> capture[\''.$this->name.'\'] = ob_end_flush(); ');
			}
			else
			{
				$this -> compiler -> tpl -> error(E_USER_ERROR, 'Trying to call sub-capture command ('.$matches[4].')', 205);
			}
		} // end captureEnd();
		
		public function processOpt($namespace)
		{
			return '$this -> capture[\''.$namespace[2].'\']';
		} // end processOpt();
	}
	
	class optFor extends optInstruction
	{
		private $nesting;
	
		public function configure()
		{
			return array(
				// processor name
				0 => 'for',
				// instructions
				'for' => OPT_MASTER,
				'/for' => OPT_ENDER
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'for':
							$this -> forBegin($block -> getAttributes());
							$this -> defaultTreeProcess($block);
							break;
					case '/for':
							$this -> forEnd();
							break;
				}
			}
		} // end process();
		
		private function forBegin($group)
		{
			$params = array(
				'begin' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ASSIGN_EXPR),
				'end' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ASSIGN_EXPR),
				'iterate' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ASSIGN_EXPR)
			);
			$this -> compiler -> parametrize('for', $group, $params);
	
			# nestingLevel
		 	
		 	$this -> nesting++;
		 	# /nestingLevel
	
	 		$this -> compiler -> out(' for('.$params['begin'].'; '.$params['end'].'; '.$params['iterate'].'){ ');
		} // end forBegin();
		
		private function forEnd()
		{
			# nestingLevel
		 	if($this -> nesting > 0)
		 	{
		 		$this -> nesting--;
		 	# /nestingLevel
		 		$this -> compiler -> out(' } ');
		 	# nestingLevel
		 	}
		 	else
		 	{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, '/FOR called when not in FOR.', 206);
		 	}
		 	# /nestingLevel
		} // end forEnd();
	}
	
	class optForeach extends optInstruction
	{
		private $nesting;
		private $else;
	
		public function configure()
		{
			return array(
				// processor name
				0 => 'foreach',
				// instructions
				'foreach' => OPT_MASTER,
				'foreachelse' => OPT_ALT,
				'/foreach' => OPT_ENDER
			);
			$this -> nesting = 0;
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'foreach':
							$this -> foreachBegin($block -> getAttributes());
							$this -> defaultTreeProcess($block);
							break;
					case 'foreachelse':
							$this -> foreachElse();
							$this -> defaultTreeProcess($block);
							break;
					case '/foreach':
							$this -> foreachEnd();
							break;				
				}			
			}		
		} // end process();
		
		private function foreachBegin($group)
		{
			# nestingLevel
			$this -> nesting++;
			# /nestingLevel
	
			$params = array(
				'table' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
				'index' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'value' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, NULL)
			);
			$this -> compiler -> parametrize('foreach', $group, $params);
	
			if($params['value'] == NULL)
			{
				$this -> compiler -> out(' if(count('.$params['table'].') > 0){ foreach('.$params['table'].' as &$__f_'.$this -> nesting.'_val){ $this -> vars[\''.$params['id'].'\'] = &$__f_'.$this -> nesting.'_val; ');
			}
			else
			{
				$this -> compiler -> out(' if(count('.$params['table'].') > 0){ foreach('.$params['table'].' as $__f_'.$this -> nesting.'_id => &$__f_'.$this -> nesting.'_val){ $this -> vars[\''.$params['index'].'\'] = $__f_'.$this -> nesting.'_id; $this -> vars[\''.$params['value'].'\'] = &$__f_'.$this -> nesting.'_val; ');
			}
		} // end foreachBegin();
		
		private function foreachElse()
		{
		 	# nestingLevel
		 	if($this -> nesting == 0)
		 	{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, 'FOREACHELSE called when not in FOREACH.', 209);
		 	}
		 	# /nestingLevel
		 	$this -> compiler -> out(' } }else{ { ');		
		} // end foreachElse();
		
		private function foreachEnd()
		{
			# nestingLevel
		 	if($this -> nesting > 0)
		 	{
		 		$this -> nesting--;
		 	# /nestingLevel
	 			$this -> compiler -> out(' } } ');
		 	# nestingLevel
		 	}
		 	else
		 	{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, '/FOREACH called when not in FOREACH.', 207);
		 	}
		 	# /nestingLevel
		} // end foreachEnd();
	}
	
	class optPhp extends optInstruction
	{
		public function configure()
		{
			return array(
				// processor name
				0 => 'php',
				// instructions
				'php' => OPT_MASTER,
				'/php' => OPT_ENDER
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'php':
							$this -> phpBegin($block -> getAttributes());
							if($block -> hasChildNodes())
							{
								if($this -> compiler -> tpl -> safeMode == 1)
								{
									foreach($block as $subnode)
									{
										$this -> nodeProcess($subnode);
									}
								}
								else
								{
									foreach($block as $subnode)
									{
										if($subnode -> getType() != OPT_TEXT)
										{
											$this -> compiler -> tpl -> error(E_USER_ERROR, 'Invalid node type '.$subnode->getType().' inside {php} tag! OPT_TEXT required.', 208);
										}
										$this -> nodeProcess($subnode);
									}
								}
							}
							break;
					case '/php':
							$this -> phpEnd();
							break;
				}
			}
		} // end process();
		
		private function phpBegin($group)
		{
			if($this -> compiler -> tpl -> safeMode == 1)
			{
				$this -> output .= $group[6];			
			}
			else
			{
				$this -> output .= '\'; ';
			}
		} // end phpBegin();
		
		private function phpEnd()
		{
			if($this -> compiler -> tpl -> safeMode == 1)
			{
				$this -> output .= $group[6];			
			}
			else
			{
				$this -> output .= ' '.$this -> compiler -> tpl -> captureTo.' \'';
			}
		} // end phpEnd();
	}
	
	class optDynamic extends optInstruction
	{
		private $active = 0;
	
		public function configure()
		{
			return array(
				// processor name
				0 => 'dynamic',
				// instructions
				'dynamic' => OPT_MASTER,
				'/dynamic' => OPT_ENDER
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'dynamic':
							$this -> dynamicBegin($block->getAttributes());
							$this -> defaultTreeProcess($block);
							break;
					case '/dynamic':
							$this -> dynamicEnd();
							break;
				}
			}
		} // end process();
		
		private function dynamicBegin($group)
		{
			if($this -> compiler -> tpl -> getStatus() == false)
			{
				return '';
			}
		
			if($this -> active == 0)
			{
				$this -> active = 1;
				$this -> compiler -> dynamic(true);
			}
			else
			{
				$this -> compiler -> tpl -> error(E_USER_WARNING, 'Dynamic section already opened.', 301);
			}
		} // end dynamicBegin();
		
		private function dynamicEnd()
		{
			if($this -> compiler -> tpl -> getStatus() == false)
			{
				return '';
			}
	
			if($this -> active == 1)
			{
				$this -> active = 0;
				$this -> compiler -> dynamic(false);
			}
			else
			{
				$this -> compiler -> tpl -> error(E_USER_WARNING, 'Dynamic section already closed.', 302);
			}
		} // end dynamicEnd();
	}
	
	class optBind extends optInstruction
	{
		public $buffer;
	
		public function configure()
		{
			$this -> compiler -> genericBuffer['bind'] = array();
			return array(
				// processor name
				0 => 'bind',
				// instructions
				'bind' => OPT_MASTER,
				'/bind' => OPT_ENDER
			);
		} // end configure();

		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'bind':
							$this -> buffer[$this->getName($block->getAttributes())] = $block;
							break;
				}
			}
		} // end instructionNodeProcess();
		
		public function getName($attributes)
		{
			$params = array(
				'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID)
			);
			$this -> compiler -> parametrize('bind', $attributes, $params);
			return $params['name'];
		} // end getName();
	} // end optBind;
	
	class optInsert extends optInstruction
	{
		public function configure()
		{
			return array(
				// processor name
				0 => 'insert',
				// instructions
				'insert' => OPT_COMMAND
			);
		} // end configure();

		public function instructionNodeProcess(ioptNode $node)
		{
			$block = $node -> getFirstBlock();
			$params = array(
				'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID)
			);
			$this -> compiler -> parametrize('insert', $block -> getAttributes(), $params);
			
			if(isset($this -> compiler -> processors['bind'] -> buffer[$params['name']]))
			{
				$this -> defaultTreeProcess($this -> compiler -> processors['bind'] -> buffer[$params['name']]);
			}
			else
			{
				$this -> compiler -> tpl -> error(E_USER_ERROR, 'Unknown bind identifier: `'.$params['name'].'`.', 209);
			}
		} // end instructionNodeProcess();
	} // end optInsert;

	class optBindEvent extends optInstruction
	{
		public $buffer = array();
	
		public function configure()
		{
			return array(
				// processor name
				0 => 'bindEvent',
				// instructions
				'bindEvent' => OPT_MASTER,
				'/bindEvent' => OPT_ENDER
			);
		} // end configure();

		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'bindEvent':
						$params = array(
							'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
							'type' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
							'message' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
							'position' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, 0)
						);
						$this -> compiler -> parametrize('bind', $block -> getAttributes(), $params);
						$this -> buffer[$params['name']] = array(
							'type' => $params['type'],
							'message' => $params['message'],
							'position' => $params['position'],
							'tree' => $node						
						);
						break;
				}
			}
		} // end instructionNodeProcess();
	} // end optBindEvent;
	
	# COMPONENTS
	class optComponent extends optInstruction
	{
		public function configure()
		{

		} // end configure();

		public function instructionNodeProcess(ioptNode $node)
		{
			static $cid;
			if($cid == NULL)
			{
				$cid = 0;
			}

			// we always use the first block in this case
			$block = $node -> getFirstBlock();
			
			$condBegin = 0;
			$componentLink = '';

			// do we have an undefined component?
			if($block -> getName() == 'component')
			{				
				$params = array(
					'id' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
					'datasource' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL),
					'__UNKNOWN__' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
				);
				$args = $this -> compiler -> parametrize($node -> getName(), $block->getAttributes(), $params);
				$code =' if('.$params['id'].' instanceof ioptComponent){ ';
				$componentLink = $params['id'];
				$condBegin = 1;
			}
			else
			{
				$params = array(
					'datasource' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL),
					'__UNKNOWN__' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
				);
				$args = $this -> compiler -> parametrize($block -> getName(), $block->getAttributes(), $params);
				if(isset($args['name']))
				{
					$code = ' $__component_'.$cid.' = new '.$block -> getName().'('.$args['name'].'); ';
				}
				else
				{
					$code = ' $__component_'.$cid.' = new '.$block -> getName().'(); ';
				}
				if($params['datasource'] != NULL)
				{
						$code .= ' $__component_'.$cid.' -> setDatasource('.$params['datasource'].'); ';
				}
				$componentLink = '$__component_'.$cid;	
			}
			$code .= $componentLink.' -> setOptInstance($this); ';

			foreach($args as $name => $value)
			{
				$code .= $componentLink.' -> set(\''.$name.'\', '.$value.'); ';
			}

			// let's see, what do we have inside the block
			
			// event table
			$events = array(0 => array(), array(), array());
			
			foreach($block as $node)
			{
				switch($node -> getName())
				{
					case 'param':
						// parameters
						$params = array(
							'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
							'value' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION)
						);
						$this -> compiler -> parametrize('component parameter', $node -> getFirstBlock()->getAttributes(), $params);
						$code .= $componentLink.' -> set(\''.$params['name'].'\', '.$params['value'].'); ';
						break;
					case 'listItem':
						// list items
						$params = array(
							'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
							'value' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
							'selected' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, 0)
						);
						$this -> compiler -> parametrize('component list element', $node -> getFirstBlock()->getAttributes(), $params);
						$code .= $componentLink.' -> push(\''.$params['name'].'\', '.$params['value'].', '.$params['selected'].'); ';
						break;
					case 'load':
						$params = array('event' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID));
						$this -> compiler -> parametrize('event loader', $node->getFirstBlock()->getAttributes(), $params);
						if(isset($this -> compiler -> processors['bindEvent'] -> buffer[$params['event']]))
						{
							$info = $this -> compiler -> processors['bindEvent'] -> buffer[$params['event']];
							switch($info['position'])
							{
								case 'up':
									$events[0][$info['type']] = array(0 => $info['tree'], $info['message']);
									break;
								case 'mid':
									$events[1][$info['type']] = array(0 => $info['tree'], $info['message']);
									break;
								case 'down':
								default:
									$events[2][$info['type']] = array(0 => $info['tree'], $info['message']);
									break;
							}
						}
						break;
					default:
						if($node -> getType() == OPT_UNKNOWN)
						{
							// events
							$params = array(
								'message' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
								'position' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, 0)
							);
							$this -> compiler -> parametrize('component event', $node->getFirstBlock()->getAttributes(), $params);
							switch($params['position'])
							{
								case 'up':
									$events[0][$node -> getName()] = array(0 => $node, $params['message']);
									break;
								case 'mid':
									$events[1][$node -> getName()] = array(0 => $node, $params['message']);
									break;
								case 'down':
								default:
									$events[2][$node -> getName()] = array(0 => $node, $params['message']);
									break;
							}
						}
				}
			}
			$this -> compiler -> out($code);
			// ok, now we put the events in the correct order
			foreach($events[0] as $name => $nodeData)
			{
				$this -> compileEvent($name, $componentLink, $nodeData);
			}
			$this -> compiler -> out(' '.$componentLink.' -> begin(); ');
			foreach($events[1] as $name => $nodeData)
			{
				$this -> compileEvent($name, $componentLink, $nodeData);
			}
			$this -> compiler -> out(' '.$componentLink.' -> end(); ');
			foreach($events[2] as $name => $nodeData)
			{
				$this -> compileEvent($name, $componentLink, $nodeData);
			}

			// terminate the processing
			if($condBegin == 1)
			{
				$this -> compiler -> out(' } ');		
			}
		} // end instructionNodeProcess();
		
		private function compileEvent($name, $componentId, $eventNode)
		{
			$this -> compiler -> out(' if('.$componentId.' -> '.$name.'(\''.$eventNode[1].'\')) { ');
			foreach($eventNode[0] as $block)
			{
				$this -> defaultTreeProcess($block);
			}
			$this -> compiler -> out(' } ');
		} // end compileEvent();

	}
	# /COMPONENTS
?>
