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
					'file' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
					'__UNKNOWN__' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
				);
				$variables = $this -> compiler -> parametrize('opfFormUrl', $urlBlock -> getAttributes(), $urlParams);
				
				$code = ' $rVariables = array(';
				// Build code
				foreach($variables as $name => $value)
				{
					$code .= '\''.$name.'\' => '.$value.',';
				}
				$code .= '); ';
				$action = '$this->opf->router->createURL('.$urlParams['file'].', $rVariables);';
			}
			// /do przerobki
						
			$this -> compiler -> out(' if(isset($this->data[\''.$params['name'].'\'])){ '.$code);
			
			$code = '<form';
			foreach($tags as $name => $value)
			{
				switch($name)
				{
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
			$code .= '><input type="hidden" name="opfFormName" value="'.$params['name'].'"/>';
			$this -> compiler -> out($code, true);
			
			$this -> compiler -> out(' global $formName; $formName = \''.$params['name'].'\'; if($this->data[\''.$params['name'].'\']->step !== NULL){
				echo \'<input type="hidden" name="opfStep" value="\'.($this->data[\''.$params['name'].'\']->step+1).\'"/>\';
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
			// do przerobki!

			$block = $node -> getFirstBlock();
			$params = array(
				'file' => array(OPT_PARAM_REQUIRED, OPT_PARAM_EXPRESSION),
				'__UNKNOWN__' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
			);
			$variables = $this -> compiler -> parametrize('opfUrl', $block -> getAttributes(), $params);
			
			$code = ' $rVariables = array(';
			// Build code
			foreach($variables as $name => $value)
			{
				$code .= '\''.$name.'\' => '.$value.',';
			}
			$code .= '); ';
			$this -> compiler -> out($code.' echo $this->opf->router->createURL('.$params['file'].', $rVariables); ');
		} // end instructionNodeProcess();
	} // end opfUrl;
	
	

?>
