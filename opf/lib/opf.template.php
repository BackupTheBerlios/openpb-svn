<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                          Open Power Forms                              //
  //         Copyright (c) 2005 OpenPB team, http://www.openpb.net/         //
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
				'opfForm' => OPT_MASTER,
				'/opfForm' => OPT_ENDER
			);
		} // end configure();

		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'opfForm':
							$this -> formBegin($block -> getAttributes());							
							$this -> defaultTreeProcess($block);
							break;
					case '/opfForm':
							$this -> formEnd();
							break;
				}
			}
		} // end instructionNodeProcess();
		
		public function formBegin($attributes)
		{
			$params = array(
				'name' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'method' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, 'post'),
				'action' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, '`index.php`'),
				'__UNKNOWN__' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_EXPRESSION, NULL)
			);
			$tags = $this -> compiler -> parametrize('opfForm', $attributes, $params);
			$tags['method'] = $params['method'];
			$tags['action'] = $params['action'];
			
			$this -> compiler -> out(' if(isset($this->data[\''.$params['name'].'\'])){ ');
			
			$code = '<form';
			foreach($tags as $name => $value)
			{
				if($name == 'method')
				{
					$code .= ' method="'.$value.'"';
				}
				else
				{
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

	
	class opfUrl extends optInstruction
	{
		public function configure()
		{
			return array(
				// processor name
				0 => 'opfUrl',
				// instructions
				'opfUrl' => OPT_COMMAND
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
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
			$this -> compiler -> out($code.' echo $this->context->getRouter()->createURL('.$params['file'].', $rVariables); ');
		} // end instructionNodeProcess();
	} // end opfUrl;

?>
