<?php

	class opt_url extends opt_instruction
	{
		static public function configure()
		{
			return array(
				'url' => OPT_COMMAND
			);
		} // end configure();
		
		public function process()
		{
			// Custom parameter format
			preg_match_all('#([a-zA-Z0-9\_]+)\="((.*?)[^\\\\])"#s', $this -> data[0][4], $found);
			
			$this -> output .= '\'; $rVariables = array(';
			// Build code
			$filename = NULL;
			foreach($found[1] as $i => $name)
			{
				if($name != 'file')
				{
					$this -> output .= '\''.$name.'\' => '.$this -> compiler -> compile_expression($found[2][$i]).',';
				}
				else
				{
					$filename = $this -> compiler -> compile_expression($found[2][$i]);
				}			
			}
			$this -> output .= '); ';
			
			if($filename == NULL)
			{
				$this -> compiler -> tpl -> error(E_USER_ERROR, '"File" parameter not defined in URL.', 401);
			}
			$this -> output .= $this -> compiler -> tpl -> capture_to.' $opb->router->createURL('.$filename.', $rVariables).\'';
		} // end process();
	}
	
?>
