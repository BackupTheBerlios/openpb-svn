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
					$this -> output .= $node->__toString();
					break;
				case OPT_EXPRESSION:
					$result = $this -> compiler -> compileExpression($node->getFirstBlock()->getAttributes(), 1);
					if($result[1] == 1)
					{
						// we have an assignment, so we must build different code
						$this -> output .= '\'; '.$result[0].'; '.$this -> compiler -> tpl -> captureTo.' \'';
					}
					else
					{		
						$this -> output .= '\'.(string)('.$result[0].').\'';
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
			
			if(is_null($state))
			{
				$this -> output .= '\'; if(($__'.$name.'_cnt = count('.$link.')) > 0){ '.$this -> compiler -> tpl -> captureTo.' \'';
			}
			else
			{
				if($this -> compiler -> tpl -> statePriority == OPT_PRIORITY_NORMAL)
				{
					$this -> output .= '\'; if(($__'.$name.'_cnt = count('.$link.')) > 0 && '.$state.'){ '.$this -> compiler -> tpl -> captureTo.' \'';
				}
				else
				{
					$this -> output .= '\'; if('.$state.'){ if(($__'.$name.'_cnt = count('.$link.')) > 0){ '.$this -> compiler -> tpl -> captureTo.' \'';
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
		} // end showAction();
		
		public function showElse()
		{
			if($this->sections[$this->nesting]['show'] == true)
			{
				$this -> output .= '\'; } else { '.$this -> compiler -> tpl -> captureTo.' \'';			
			}		
		} // end showElse();
		
		public function showEnd()
		{
			if($this->sections[$this->nesting]['show'] == true)
			{
				if(!is_null($this->sections[$this->nesting]['state']) && $this -> compiler -> tpl -> statePriority == OPT_PRIORITY_HIGH)
				{
					$this -> output .= '\'; } } '.$this -> compiler -> tpl -> captureTo.' \'';
				}
				else
				{
					$this -> output .= '\'; } '.$this -> compiler -> tpl -> captureTo.' \'';	
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
				$this -> output .= '\'; for($__'.$name.'_id = $__'.$name.'_cnt - 1; $__'.$name.'_id >= 0; $__'.$name.'_id--){ $__'.$name.'_val = &'.$this->sections[$this->nesting]['link'].'[$__'.$name.'_id]; '.$this -> compiler -> tpl -> captureTo.' \'';		
			}			
			else
			{
				$this -> output .= '\'; foreach('.$this->sections[$this->nesting]['link'].' as $__'.$name.'_id => &$__'.$name.'_val){ '.$this -> compiler -> tpl -> captureTo.' \'';
			}
			$this -> nesting++;
		} // end sectionBegin();
		
		public function sectionElse()
		{
			if($this->sections[$this->nesting-1]['show'] == false)
			{
				$this->sections[$this->nesting-1]['else'] = true;
				$this -> output .= '\'; } } else { '.$this -> compiler -> tpl -> captureTo.' \'';			
			}
		} // end sectionElse();
		
		public function sectionEnd()
		{
			$this -> nesting--;
			if($this->sections[$this->nesting]['show'] == true)
			{				
				$this -> output .= '\'; } '.$this -> compiler -> tpl -> captureTo.' \'';			
			}
			else
			{
				if($this->sections[$this->nesting]['else'] == true)
				{
					$this -> output .= '\'; } '.$this -> compiler -> tpl -> captureTo.' \'';
				}
				else
				{
					$this -> output .= '\'; } } '.$this -> compiler -> tpl -> captureTo.' \'';
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

			if($params['default'] != NULL)
			{
				$code = ' if($this->checkExistence('.$params['file'].')){ $this -> doInclude('.$params['file'].', $nestingLevel + 1); }else{ $this -> doInclude('.$params['default'].', $nestingLevel + 1); } ';
			}
			else
			{
				$code = '$this -> doInclude('.$params['file'].', $nestingLevel + 1);';
			}
	
			if($params['assign'] != NULL)
			{
				$this -> output .= '\'; $this -> captureTo = \'$this->vars[\\\''.$params['assign'].'\\\'].=\'; '.$code.' $this -> captureTo = \''.$this -> compiler -> tpl -> captureTo.'\'; '.$this -> compiler -> tpl -> captureTo.' \'';
			}
			else
			{
				$this -> output .= '\'; '.$code.' '.$this -> compiler -> tpl -> captureTo.' \'';
			}
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
			$res = $this -> compiler -> tpl -> getResourceInfo($params['file'], $file);
			if($params['assign'] != NULL)
			{	
				$captureBuffer = $this -> compiler -> tpl -> captureTo;
				$this -> compiler -> tpl -> captureTo = '$this -> vars[\''.$params['assign'].'\'] .= ';
				$this -> output .= '\'; '.$this -> compiler -> tpl -> captureTo.' \'';
			}
			$this -> compiler -> parse($res -> loadSource($file));
			if($params['assign'] != NULL)
			{
				$this -> compiler -> tpl -> captureTo = $captureBuffer;
				$this -> output .= '\'; '.$this -> compiler -> tpl -> captureTo.' \'';
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
	
			$this -> output .= '\'; $this -> vars[\''.$params['name'].'\'] = '.$params['value'].'; '.$this -> compiler -> tpl -> captureTo.' \'';
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
			
			$this -> output .= '\'.(isset('.$params['test'].') ? '.$params['test'].' : '.$params['alt'].').\'';
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
		 	$this -> compiler -> checkNestingLevel($this -> nesting);
		 	
		 	$this -> nesting++;
		 	# /nestingLevel
		 	
			if($this -> compiler -> tpl->xmlsyntaxMode == 1 || $this -> compiler -> tpl -> strictSyntax == 1)
			{
				$params = array(
					'test' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION)
				);
				$this -> compiler -> parametrize('if', $group, $params);
				$this -> output .= '\'; if('.$params['test'].'){ '.$this -> compiler -> tpl -> captureTo.' \'';
			}
			else
			{
				$this -> output .= '\'; if('.$this -> compiler -> compileExpression($group[4]).'){ '.$this -> compiler -> tpl -> captureTo.' \'';
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
					$this -> output .= '\'; }elseif('.$params['test'].'){ '.$this -> compiler -> tpl -> captureTo.' \'';
				}
				else
				{
					$this -> output .= '\'; }elseif('.$this -> compiler -> compileExpression($group[4]).'){ '.$this -> compiler -> tpl -> captureTo.' \'';
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
		 		$this -> output .= '\'; }else{ '.$this -> compiler -> tpl -> captureTo.' \'';
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
		 		$this -> output .= '\'; } '.$this -> compiler -> tpl -> captureTo.' \'';
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
			if($this -> compiler -> tpl -> captureTo == $this -> compiler -> tpl -> captureDef)
			{
				$this -> compiler -> tpl -> captureTo = '$this -> capture[\''.$params['to'].'\'] .= ';
				$this -> output .= '\'; '.$this -> compiler -> tpl -> captureTo.' \'';
			}
			else
			{
				$this -> compiler -> tpl -> error(E_USER_ERROR, 'Trying to call sub-capture command ('.$params['to'].')', 204);
			}
		} // end captureBegin();
		
		private function captureEnd()
		{
			if($this -> compiler -> tpl -> captureTo != $this -> compiler -> tpl -> captureDef)
			{
				$this -> compiler -> tpl -> captureTo = $this -> compiler -> tpl -> captureDef;
				$this -> output .= '\'; '.$this -> compiler -> tpl -> captureTo.' \'';
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
		 	$this -> compiler -> checkNestingLevel($this -> nesting);
		 	
		 	$this -> nesting++;
		 	# /nestingLevel
	
	 		$this -> output .= '\'; for('.$params['begin'].'; '.$params['end'].'; '.$params['iterate'].'){ '.$this -> compiler->tpl->captureTo.' \'';
		} // end forBegin();
		
		private function forEnd()
		{
			# nestingLevel
		 	if($this -> nesting > 0)
		 	{
		 		$this -> nesting--;
		 	# /nestingLevel
		 		$this -> output .= '\'; } '.$this -> compiler -> tpl -> captureTo.' \'';
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
			$this -> compiler -> checkNestingLevel($this -> nesting);
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
				$this -> output .= '\'; if(count('.$params['table'].') > 0){ foreach('.$params['table'].' as &$__f_'.$this -> nesting.'_val){ $this -> vars[\''.$params['id'].'\'] = &$__f_'.$this -> nesting.'_val; '.$cpl -> tpl -> captureTo.' \'';
			}
			else
			{
				$this -> output .= '\'; if(count('.$params['table'].') > 0){ foreach('.$params['table'].' as $__f_'.$this -> nesting.'_id => &$__f_'.$this -> nesting.'_val){ $this -> vars[\''.$params['index'].'\'] = $__f_'.$this -> nesting.'_id; $this -> vars[\''.$params['value'].'\'] = &$__f_'.$this -> nesting.'_val; '.$this -> compiler -> tpl -> captureTo.' \'';
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
		 	$this -> output .= '\'; } }else{ { '.$this -> compiler -> tpl -> captureTo.' \'';		
		} // end foreachElse();
		
		private function foreachEnd()
		{
			# nestingLevel
		 	if($this -> nesting > 0)
		 	{
		 		$this -> nesting--;
		 	# /nestingLevel
	 			$this -> output .= '\'; } } '.$this -> compiler -> tpl -> captureTo.' \'';
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
		
			if($this -> active = 0)
			{
				$this -> active = 1;
				$this -> output .= '\'; $this -> cacheOutput[] = ob_get_contents(); /* #@#DYNAMIC#@# */ '.$this -> compiler -> tpl -> captureTo.' \'';
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
				$this -> output .= '\'; /* #@#END DYNAMIC#@# */ ob_start(); '.$this -> compiler -> tpl -> captureTo.' \'';
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
				$this -> output .= '\'; if('.$params['id'].' instanceof ioptComponent){ ';
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
				$this -> output .= '\'; $__component_'.$cid.' = new '.$block -> getName().'(); ';
				if($params['datasource'] != NULL)
				{
						$this -> output .= ' $__component_'.$cid.' -> setDatasource('.$params['datasource'].'); ';
				}
				$componentLink = '$__component_'.$cid;	
			}
			$this -> output .= $componentLink.' -> setOptInstance($this); ';

			foreach($args as $name => $value)
			{
				$this -> output .= $componentLink.' -> set(\''.$name.'\', '.$value.'); ';
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
						$this -> output .= $componentLink.' -> set(\''.$params['name'].'\', '.$params['value'].'); ';
						break;
					case 'listItem':
						// list items
						$params = array(
							'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
							'value' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
							'selected' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, 0)
						);
						$this -> compiler -> parametrize('component list element', $node -> getFirstBlock()->getAttributes(), $params);
						$this -> output .= $componentLink.' -> push(\''.$params['name'].'\', '.$params['value'].', '.$params['selected'].'); ';
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
			
			// ok, now we put the events in the correct order
			foreach($events[0] as $name => $nodeData)
			{
				$this -> compileEvent($componentLink, $nodeData);
			}
			$this -> output .= ' '.$this -> compiler -> tpl -> captureTo.' '.$componentLink.' -> begin(); ';
			foreach($events[1] as $name => $nodeData)
			{
				$this -> compileEvent($componentLink, $nodeData);
			}
			$this -> output .= ' '.$this -> compiler -> tpl -> captureTo.' '.$componentLink.' -> end(); ';
			foreach($events[2] as $name => $nodeData)
			{
				$this -> compileEvent($componentLink, $nodeData);
			}

			// terminate the processing
			if($condBegin == 1)
			{
				$this -> output .= ' } '.$this -> compiler -> tpl -> captureTo.' \'';			
			}
			else
			{
				$this -> output .= ' '.$this -> compiler -> tpl -> captureTo.' \'';			
			}
		} // end instructionNodeProcess();
		
		private function compileEvent($componentId, $eventNode)
		{
			$node = $eventNode[0];
			$message = $eventNode[1];
			$this -> output .= ' if('.$componentId.' -> '.$node->getName().'(\''.$eventNode[1].'\')) { '.($capture = $this -> compiler -> tpl -> captureTo).' \'';
			foreach($node as $block)
			{
				$this -> defaultTreeProcess($block);
			}
			$this -> output .= '\'; } ';
		} // end compileEvent();

	}
	# /COMPONENTS
?>
