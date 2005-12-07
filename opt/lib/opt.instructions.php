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
				if(!isset($this -> compiler -> processors[$node -> getName()]))
				{
					return 0;
				}
				
				// pass the execution to the instruction processor
				$this -> compiler -> processors[$node -> getName()] -> instructionNodeProcess($node);
				return 1;
			}	
			return 0;
		} // end instructionProcess();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			
		} // end instructionNodeProcess();
	}
	
	class optSection extends optInstruction
	{
		private $sectionDirection;
	
		public function configure()
		{
			$this -> compiler -> nestingNames['section'] = array();
			$this -> compiler -> nestingLevel['section'] = 0;
			$this -> sectionDirection = array();
			return array(
				// processor name
				0 => 'section',
				// instructions
				'section' => OPT_MASTER,
				'sectionelse' => OPT_ALT,
				'/section' => OPT_ENDER
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			$sectionelse = 0;
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'section':
							$this -> sectionBegin($block -> getAttributes());
							$this -> defaultTreeProcess($block);
							break;
					case 'sectionelse':
							$sectionelse = 1;
							$this -> sectionElse();
							$this -> defaultTreeProcess($block);
							break;
					case '/section':
							$this -> sectionEnd($sectionelse);
							break;
				}
			}
		} // end process();
		
		private function sectionBegin($attr)
		{
			$params = array(
				'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'reversed' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, '')
			);
			$this -> compiler -> parametrizeError('section', $this -> compiler -> parametrize($attr, $params));
			# NESTING_LEVEL
		 	$this -> compiler -> checkNestingLevel('section');
		 	# /NESTING_LEVEL

		 	$this -> compiler -> nestingLevel['section']++;
		 	$this -> compiler -> nestingNames['section'][$this -> compiler -> nestingLevel['section']] = $params['name'];
		 	$this -> sectionDirection[$this -> compiler -> nestingLevel['section']] = ($params['reversed'] == 'reversed' ? 1 : 0);
		 	
		 	// check whether we started "show" instruction
		 	$id = array_search($params['name'], $this -> compiler -> nestingNames['show']);
		 	$count = 1;
			if($id !== FALSE)
			{
				$count = 0;		
			}
		 	if($params['reversed'] == '')
		 	{
		 		// normal direction, use "foreach" to simulate a section
			 	if($this -> compiler -> nestingLevel['section'] == 1)
			 	{
			 		$this -> output .= '\'; '.($count == 1 ? 'if(count($this -> data[\''.$params['name'].'\']) > 0){' : '').' foreach($this -> data[\''.$params['name'].'\'] as $__'.$params['name'].'_id => &$__'.$params['name'].'_val){ '.$this -> compiler -> tpl -> captureTo.' \'';
			 	}
			 	else
			 	{
			 		$lnk = '$this -> data[\''.$params['name'].'\']';
			 		$i = 0;
			 		foreach($this -> compiler -> nestingNames['section'] as $name)
			 		{
			 			if($i < count($this -> compiler -> nestingNames['section']) - 1)
			 			{
			 				$lnk .= '[$__'.$name.'_id]';
			 			}
			 			$i++;
			 		}
		
			 		$this -> output .= '\'; '.($count == 1 ? 'if(count('.$lnk.') > 0){ ' : '').' foreach('.$lnk.' as $__'.$params['name'].'_id => &$__'.$params['name'].'_val){ '.$this -> compiler -> tpl -> captureTo.' \'';
			 	}
		 	}
		 	else
		 	{
		 		// reversed direction, use "for" to simulate a section
		 		// normal direction, use "foreach" to simulate a section
			 	if($this -> compiler -> nestingLevel['section'] == 1)
			 	{
			 		$this -> output .= '\'; '.($count == 1 ? 'if(($__'.$params['name'].'_cnt = count($this -> data[\''.$params['name'].'\'])) > 0){' : '').' for($__'.$params['name'].'_id = $__'.$params['name'].'_cnt - 1; $__'.$params['name'].'_id >= 0; $__'.$params['name'].'_id--){ $__'.$params['name'].'_val = &$this -> data[\''.$params['name'].'\'][$__'.$params['name'].'_id]; '.$this -> compiler -> tpl -> captureTo.' \'';
			 	}
			 	else
			 	{
			 		$lnk = '$this -> data[\''.$params['name'].'\']';
			 		$i = 0;
			 		foreach($this -> compiler -> nestingNames['section'] as $name)
			 		{
			 			if($i < count($this -> compiler -> nestingNames['section']) - 1)
			 			{
			 				$lnk .= '[$__'.$name.'_id]';
			 			}
			 			$i++;
			 		}
		
			 		$this -> output .= '\'; '.($count == 1 ? 'if(($__'.$params['name'].'_cnt = count('.$lnk.')) > 0){' : '').' for($__'.$params['name'].'_id = $__'.$params['name'].'_cnt - 1; $__'.$params['name'].'_id >= 0; $__'.$params['name'].'_id--){ $__'.$params['name'].'_val = &'.$lnk.'[$__'.$params['name'].'_id]; '.$this -> compiler -> tpl -> captureTo.' \'';
			 	}
		 	}
		} // end sectionBegin();
		
		private function sectionElse()
		{
		 	$id = array_search($this -> compiler -> nestingNames['section'][$this -> compiler -> nestingLevel['section']], $this -> compiler -> nestingNames['show']);
			if($id !== FALSE)
			{
				$this -> compiler -> tpl -> error(E_USER_ERROR, 'SECTIONELSE not available inside a SHOW+SECTION structure.', 211);	
			}
			$this -> output .= '\'; } }else{ '.$this -> compiler -> tpl -> captureTo.' \'';
		
		} // end sectionElse();
		
		private function sectionEnd($sectionelse)
		{
		 	$id = array_search($this -> compiler -> nestingNames['section'][$this -> compiler -> nestingLevel['section']], $this -> compiler -> nestingNames['show']);
		
	 		unset($this -> compiler -> nesting_names['section'][$this -> compiler -> nestingLevel['section']]);
	 		unset($this -> sectionDirection[$this -> compiler -> nestingLevel['section']]);
	 		$this -> compiler -> nestingLevel['section']--;
	 				
			if($sectionelse == 1 || $id !== FALSE)
			{
				$this -> output .= '\'; } '.$this -> compiler -> tpl -> captureTo.' \'';		
			}
			else
			{
				$this -> output .= '\'; } } '.$this -> compiler -> tpl -> captureTo.' \'';
			}
		} // end sectionEnd();
		
		public function getSectionDirection($name)
		{
			$id = array_search($name, $this -> compiler -> nestingNames['section']);
			if($id !== FALSE)
			{
				return $this -> sectionDirection[$id];			
			}
			return FALSE;
		} // end getSectionDirection();
	}
	
	class optShow extends optInstruction
	{
	
		public function configure()
		{
			$this -> compiler -> nestingNames['show'] = array();
			$this -> compiler -> nestingLevel['show'] = 0;
			$this -> sectionDirection = array();
			return array(
				// processor name
				0 => 'show',
				// instructions
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
				}
			}
		} // end process();
		
		private function showBegin($attr)
		{
			$params = array(
				'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'state' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, '')
			);
			$this -> compiler -> parametrizeError('show', $this -> compiler -> parametrize($attr, $params));
			# NESTING_LEVEL
		 	$this -> compiler -> checkNestingLevel('show');
		 	# /NESTING_LEVEL

		 	$this -> compiler -> nestingLevel['show']++;
		 	$this -> compiler -> nestingNames['show'][$this -> compiler -> nestingLevel['show']] = $params['name'];
		 	
		 	$condition = '';
		 	if($params['state'] != '')
		 	{
		 		$condition = ' && '.$params['state'].' == 1';
		 	}		 	

		 	if($this -> compiler -> nestingLevel['section'] == 0)
		 	{
		 		$this -> output .= '\'; if(count($this -> data[\''.$params['name'].'\']) > 0'.$condition.'){ '.$this -> compiler -> tpl -> captureTo.' \'';
		 	}
		 	else
		 	{
		 		$lnk = '$this -> data[\''.$params['name'].'\']';
		 		$i = 0;
		 		foreach($this -> compiler -> nestingNames['section'] as $name)
		 		{
		 			if($i <= count($this -> compiler -> nestingNames['section']) - 1)
		 			{
		 				$lnk .= '[$__'.$name.'_id]';
		 			}
		 			$i++;
		 		}
	
		 		$this -> output .= '\'; if(count('.$lnk.') > 0'.$condition.'){ '.$this -> compiler -> tpl -> captureTo.' \'';
		 	}
		} // end showBegin();
		
		private function showElse()
		{
			$this -> output .= '\'; }else{ '.$this -> compiler -> tpl -> captureTo.' \'';
		
		} // end showElse();
		
		private function showEnd()
		{
	 		unset($this -> compiler -> nesting_names['show'][$this -> compiler -> nestingLevel['show']]);
	 		$this -> compiler -> nestingLevel['show']--;
			$this -> output .= '\'; } '.$this -> compiler -> tpl -> captureTo.' \'';		
		} // end showEnd();
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
			);
			$this -> compiler -> parametrizeError('include', $this -> compiler -> parametrize($block -> getAttributes(), $params));
	
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
			$this -> compiler -> parametrizeError('place', $this -> compiler -> parametrize($block->getAttributes(), $params));
	
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
			$this -> compiler  -> parametrizeError('var', $this -> compiler  -> parametrize($block -> getAttributes(), $params));
	
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
			$this -> compiler -> parametrizeError('default', $this -> compiler -> parametrize($block -> getAttributes(), $params));
			
			$this -> output .= '\'.(isset('.$params['test'].') ? '.$params['test'].' : '.$params['alt'].').\'';
		} // end process();
	}

	class optIf extends optInstruction
	{
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
		 	$this -> compiler -> checkNestingLevel('if');
		 	
		 	$this -> compiler -> nestingLevel['if']++;
		 	# /nestingLevel
		 	
			if($this -> compiler -> tpl->xmlsyntaxMode == 1 || $this -> compiler -> tpl -> strictSyntax == 1)
			{
				$params = array(
					'test' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION)
				);
				$this -> compiler -> parametrizeError('if', $this -> compiler -> parametrize($group, $params));
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
			if($this -> compiler -> nestingLevel['if'] > 0)
			{
			# /nestingLevel
				if($this -> compiler->tpl->xmlsyntaxMode == 1 || $this -> compiler->tpl->strictSyntax == 1)
				{
					$params = array(
						'test' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION)
					);
					$this -> compiler -> parametrizeError('if', $this -> compiler -> parametrize($group, $params));
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
		 	if($this -> compiler -> nestingLevel['if'] > 0)
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
		 	if($this -> compiler -> nestingLevel['if'] > 0)
			{
		 		$this -> compiler -> nestingLevel['if']--;
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
			$this -> compiler -> parametrizeError('capture', $this -> compiler -> parametrize($group, $params));
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
	}
	
	class optFor extends optInstruction
	{
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
			$this -> compiler -> parametrizeError('for', $this -> compiler -> parametrize($group, $params));
	
			# nestingLevel
		 	$this -> compiler -> checkNestingLevel('for');
		 	
		 	$this -> compiler -> nestingLevel['for']++;
		 	# /nestingLevel
	
	 		$this -> output .= '\'; for('.$params['begin'].'; '.$params['end'].'; '.$params['iterate'].'){ '.$this -> compiler->tpl->captureTo.' \'';
		} // end forBegin();
		
		private function forEnd()
		{
			# nestingLevel
		 	if($this -> compiler -> nestingLevel['for'] > 0)
		 	{
		 		$this -> compiler -> nestingLevel['for']--;
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
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			$foreachelse = 0;
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'foreach':
							$this -> foreachBegin($block -> getAttributes());
							$this -> defaultTreeProcess($block);
							break;
					case 'foreachelse':
							$foreachelse = 1;
							$this -> foreachElse();
							$this -> defaultTreeProcess($block);
							break;
					case '/foreach':
							$this -> foreachEnd($foreachelse);
							break;				
				}			
			}		
		} // end process();
		
		private function foreachBegin($group)
		{
			# nestingLevel
			$this -> compiler -> checkNestingLevel('foreach');
			$this -> compiler -> nestingLevel['foreach']++;
			# /nestingLevel
	
			$params = array(
				'table' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
				'index' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'value' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, NULL)
			);
			$this -> compiler -> parametrizeError('for', $this -> compiler -> parametrize($group, $params));
	
			if($params['value'] == NULL)
			{
				$this -> output .= '\'; if(count('.$params['table'].') > 0){ foreach('.$params['table'].' as &$__f_'.$this -> compiler -> nestingLevel['foreach'].'_val){ $this -> vars[\''.$params['id'].'\'] = &$__f_'.$this -> compiler -> nestingLevel['foreach'].'_val; '.$cpl -> tpl -> captureTo.' \'';
			}
			else
			{
				$this -> output .= '\'; if(count('.$params['table'].') > 0){ foreach('.$params['table'].' as $__f_'.$this -> compiler -> nestingLevel['foreach'].'_id => &$__f_'.$this -> compiler -> nestingLevel['foreach'].'_val){ $this -> vars[\''.$params['index'].'\'] = $__f_'.$this -> compiler -> nestingLevel['foreach'].'_id; $this -> vars[\''.$params['value'].'\'] = &$__f_'.$this -> compiler -> nestingLevel['foreach'].'_val; '.$this -> compiler -> tpl -> captureTo.' \'';
			}
		} // end foreachBegin();
		
		private function foreachElse()
		{
		 	# nestingLevel
		 	if($this -> compiler -> nestingLevel['foreach'] == 0)
		 	{
		 		$this -> compiler -> tpl -> error(E_USER_ERROR, 'FOREACHELSE called when not in FOREACH.', 209);
		 	}
		 	# /nestingLevel
		 	$this -> output .= '\'; } }else{ { '.$this -> compiler -> tpl -> captureTo.' \'';		
		} // end foreachElse();
		
		private function foreachEnd($foreachelse)
		{
			# nestingLevel
		 	if($this -> compiler -> nestingLevel['foreach'] > 0)
		 	{
		 		$this -> compiler -> nestingLevel['foreach']--;
		 	# /nestingLevel
		 		if($foreachelse == 1)
		 		{
		 			$this -> output .= '\'; } '.$this -> compiler -> tpl -> captureTo.' \'';
		 		}
		 		else
		 		{
		 			$this -> output .= '\'; } } '.$this -> compiler -> tpl -> captureTo.' \'';
		 		}
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
		public function configure()
		{
			$this -> compiler -> nestingLevel['dynamic'] = 0;
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
		
			if($this -> compiler -> nestingLevel['dynamic'] == 0)
			{
				$this -> compiler -> nestingLevel['dynamic'] = 1;
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
	
			if($this -> compiler -> nestingLevel['dynamic'] == 1)
			{
				$this -> compiler -> nestingLevel['dynamic'] = 0;
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
							$this -> compiler -> genericBuffer['bind'][$this->getName($block->getAttributes())] = $block;
							break;
				}
			}
		} // end instructionNodeProcess();
		
		public function getName($attributes)
		{
			$params = array(
				'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID)
			);
			$this -> compiler -> parametrizeError('opfBind', $this -> compiler -> parametrize($attributes, $params));
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
			$this -> compiler -> parametrizeError('insert', $this -> compiler -> parametrize($block -> getAttributes(), $params));
			
			if(isset($this -> compiler -> genericBuffer['bind'][$params['name']]))
			{
				$this -> defaultTreeProcess($this -> compiler -> genericBuffer['bind'][$params['name']]);
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
					'id' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
					'datasource' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
				);
				$this -> compiler -> parametrizeError($node -> getName(), $this -> compiler -> parametrize($block->getAttributes(), $params));
				$this -> output .= '\'; if($this -> data[\''.$params['id'].'\'] instanceof ioptComponent){ ';
				$componentLink = '$this -> data[\''.$params['id'].'\']';
				$condBegin = 1;
			}
			else
			{
				$params = array(
					'name' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, NULL),
					'datasource' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
				);
				$this -> compiler -> parametrizeError($block -> getName(), $this -> compiler -> parametrize($block->getAttributes(), $params));
				if($params['name'] != NULL)
				{
					$this -> output .= '\'; $__component_'.$cid.' = new '.$block -> getName().'('.$params['name'].'); ';
				}
				else
				{
					$this -> output .= '\'; $__component_'.$cid.' = new '.$block -> getName().'(); ';
				}
				if($params['datasource'] != NULL)
				{
						$this -> output .= ' $__component_'.$cid.' -> setDatasource('.$params['datasource'].'); ';
				}
				$componentLink = '$__component_'.$cid;	
			}
			$this -> output .= $componentLink.' -> setOptInstance($this); ';
			
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
						$this -> compiler -> parametrizeError('component parameter', $this -> compiler -> parametrize($node -> getFirstBlock()->getAttributes(), $params));
						$this -> output .= $componentLink.' -> set(\''.$params['name'].'\', '.$params['value'].'); ';
						break;
					case 'listItem':
						// list items
						$params = array(
							'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
							'value' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
							'selected' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, 0)
						);
						$this -> compiler -> parametrizeError('component list element', $this -> compiler -> parametrize($node -> getFirstBlock()->getAttributes(), $params));
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
							$this -> compiler -> parametrizeError('component event', $this -> compiler -> parametrize($node->getFirstBlock()->getAttributes(), $params));
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
