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

	class opfForm extends optInstruction
	{
		public function configure()
		{
			return array(
				// processor name
				0 => 'opfForm',
				// instructions
				'opf:form' => OPT_MASTER,
				'/opf:form' => OPT_ENDER,
				'opf:classfor' => OPT_ATTRIBUTE
			);
		} // end configure();

		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'opf:form':
							$this -> formBegin($block);							
							$this -> defaultTreeProcess($block);
							break;
					case '/opf:form':
							$this -> formEnd();
							break;
				}
			}
		} // end instructionNodeProcess();
		
		public function formBegin($block)
		{
			$params = array(
				'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'method' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, 'post'),
				'action' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, '`index.php`'),
				'display' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, 'true'),
				'__UNKNOWN__' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
			);
			$tags = $this -> compiler -> parametrize('opfForm', $block -> getAttributes(), $params);
			$tags['method'] = $params['method'];
			$tags['action'] = $params['action'];
			$code = '';
			
			// do przerobki
			$urlNode = $block -> getElementByTagName('opf:formUrl');
			if(is_object($urlNode))
			{
				$urlBlock = $urlNode -> getFirstBlock();
				$urlParams = array(
					'_load' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL),
					'__UNKNOWN__' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
				);
				$variables = $this -> compiler -> parametrize('opf:formUrl', $urlBlock -> getAttributes(), $urlParams);
				
				$code = ' $rVariables = array(';
				// Build code
				foreach($variables as $name => $value)
				{
					$code .= '\''.$name.'\' => '.$value.',';
				}
				$code .= '); ';
				if(!is_null($params['_load']))
				{
					$action = '(is_array('.$params['_load'].') ? $this->opf->router->createURL($rVariables + '.$params['_load'].') : $this->opf->router->createURL($rVariables)); ';
				}
				else
				{
					$action = '$this->opf->router->createURL($rVariables);';
				}
			}
			// /do przerobki
						
			$this -> compiler -> out(' if(isset($this->data[\''.$params['name'].'\']) && '.$params['display'].'){ '.$code);
			
			$code = '<form';
			foreach($tags as $name => $value)
			{
				switch($name)
				{
					case 'display':
						break;
					case 'method':
						$code .= ' method="'.$value.'"';
						break;
					case 'action':
						if(isset($action))
						{
							$value = $action;
						}
					default:
						$code .= ' '.$name.'="<'.'?php echo '.$value.' ?'.'>"';
				}
			}
			$code .= '><input type="hidden" name="<?=$this->opf->prefix?>FormName" value="'.$params['name'].'"/>';
			$this -> compiler -> out($code, true);
			
			$this -> compiler -> out(' global $formName; $formName = \''.$params['name'].'\'; if($this->data[\''.$params['name'].'\']->step !== NULL){
				echo \'<input type="hidden" name="\'.$this->opf->prefix.\'Step" value="\'.($this->data[\''.$params['name'].'\']->step+1).\'"/>\';
			}
			if(count($this->data[\''.$params['name'].'\']->items) > 0){
				foreach($this->data[\''.$params['name'].'\']->items as $itemName => $itemValue){
					echo \'<input type="hidden" name="\'.$itemName.\'" value="\'.$itemValue."\\"/>\\n";				
				}
			} ');
		} // end formBegin();

		public function formEnd()
		{
			$this -> compiler -> out('</form>', true);
			$this -> compiler -> out(' } ');
		} // end formEnd();
		
		public function processAttribute(optBlock $block)
		{
			if($block -> getName() == 'opf:classfor')
			{
				$this -> compiler -> out(' echo $this->data[$formName]->getClass(\''.$block -> getAttributes().'\'); ');
			}
		} // end processAttribute();
	} // end opfForm;
	
	class opfJavascript extends optInstruction
	{
		public function configure()
		{
			return array(
				// processor name
				0 => 'opfJavascript',
				// instructions
				'opf:javascript' => OPT_COMMAND
			);
		} // end configure();

		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'opf:javascript':
							$this -> generateJavascript($block);							
							break;
				}
			}
		} // end instructionNodeProcess();
		
		public function generateJavascript($block)
		{
			// do przerobki!
			$params = array(
				'form' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
			);
			$this -> compiler -> parametrize('opfJavascript', $block -> getAttributes(), $params);
			$this -> compiler -> out('
		<script type="text/javascript">
		
		function opf'.ucfirst($params['form']).'Validator(__opf)
		{
			if(!__opf.languageInitialized)
			{
				__opf.addErrorMessage(\'gt\', \''.$this->tpl->i18n->put('opf', 'constraint_gt').'\');
				__opf.addErrorMessage(\'lt\', \''.$this->tpl->i18n->put('opf', 'constraint_lt').'\');
				__opf.addErrorMessage(\'len_gt\', \''.$this->tpl->i18n->put('opf', 'constraint_len_gt').'\');
				__opf.addErrorMessage(\'len_lt\', \''.$this->tpl->i18n->put('opf', 'constraint_len_lt').'\');
				__opf.addErrorMessage(\'equal\', \''.$this->tpl->i18n->put('opf', 'constraint_equal').'\');
				__opf.addErrorMessage(\'len_equal\', \''.$this->tpl->i18n->put('opf', 'constraint_len_equal').'\');
				__opf.addErrorMessage(\'matchto\', \''.$this->tpl->i18n->put('opf', 'constraint_matchto').'\');
				__opf.addErrorMessage(\'scope\', \''.$this->tpl->i18n->put('opf', 'constraint_scope').'\');
				__opf.addErrorMessage(\'permittedchars\', \''.$this->tpl->i18n->put('opf', 'constraint_permittedchars').'\');
			}
			__opf.addForm(\''.$params['form'].'\');
			alert(\'Tralala\');
		',true);
			$this -> compiler -> out(' echo $this->data[\''.$params['form'].'\']->generateJavascript(); ');
			$this -> compiler -> out('
			if(__opf.valid == 1)
			{
				return 1;
			}
			return 0;
		}

		</script>
			', true);
		} // end generateJavascript();
	} // end opfJavascript;

	
	class opfUrl extends optInstruction
	{
		public function configure()
		{
			return array(
				// processor name
				0 => 'opfUrl',
				// instructions
				'opf:url' => OPT_COMMAND
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			$block = $node -> getFirstBlock();
			$params = array(
				'_load' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL),
				'_capture' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, NULL),
				'__UNKNOWN__' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
			);
			$variables = $this -> compiler -> parametrize('opf:url', $block -> getAttributes(), $params);

			$code = ' $rVariables = array(';
			// Build code
			foreach($variables as $name => $value)
			{
				$code .= '\''.$name.'\' => '.$value.',';
			}
			$code .= '); ';
			if(!is_null($params['_capture']))
			{	
				$dest = '$this->capture[\''.$params['_capture'].'\'] = ';
			}
			else
			{
				$dest = ' echo ';
			}
			if(!is_null($params['_load']))
			{
				$this -> compiler -> out($code.' '.$dest.'(is_array('.$params['_load'].') ? $this->opf->router->createURL($rVariables + '.$params['_load'].') : $this->opf->router->createURL($rVariables)); ');
			}
			else
			{
				$this -> compiler -> out($code.' '.$dest.'$this->opf->router->createURL($rVariables); ');
			}
		} // end instructionNodeProcess();
	} // end opfUrl;
	
	class opfCall extends optInstruction
	{
		public function configure()
		{
			return array(
				// processor name
				0 => 'opfCall',
				// instructions
				'opf:call' => OPT_COMMAND
			);
		} // end configure();

		public function instructionNodeProcess(ioptNode $node)
		{
			$block = $node -> getFirstBlock();
			$params = array(
				'event' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'for' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
			);
			
			$variables = $this -> compiler -> parametrize('opf:call', $block -> getAttributes(), $params);
			if(isset($this -> compiler -> genericBuffer['bindEvent'][$params['event']]))
			{
				$info = $this -> compiler -> genericBuffer['bindEvent'][$params['event']];

				$this -> compiler -> out('if(isset($this -> data[$formName] -> errorMessages[\''.$params['for'].'\']))
				{ $this->vars[\''.$info['message'].'\'] = $this -> data[$formName] -> errorMessages[\''.$params['for'].'\']; ');
				foreach($info['tree'] as $block)
				{
					$this -> defaultTreeProcess($block);
				}
				$this -> compiler -> out(' } ');
			}
		} // end instructionNodeProcess();
	} // end opfCall;

?>
