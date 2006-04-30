<?php
	// Additional code file for the examples

    class i18n implements ioptI18n
    {
		private $langdata;
		private $modified;
		private $tpl;

		public function loadGroup($id)
		{
			if(is_file('%%'.$id.'.php'))
			{
				$this -> langdata[$id] = parse_ini_file('%%'.$id.'.php');
			}
		} // end loadFile();
		
		public function setOptInstance(optClass $tpl)
		{
			$this -> tpl = $tpl;
		} // end setOptInstance();

		public function put($group, $id)
		{
			if(isset($this -> modified[$group][$id]))
			{
				return $this -> modified[$group][$id];
			}
			elseif(isset($this -> langdata[$group][$id]))
			{
				return $this -> langdata[$group][$id];
			}
			return NULL;
		} // end put();

		public function apply($group, $id)
		{
			$args = func_get_args();
			unset($args[0]);
			unset($args[1]);
			$this -> modified[$group][$id] = vsprintf($this -> langdata[$group][$id], $args);
		} // end apply();

		public function putApply($group, $id)
		{
			$args = func_get_args();
			if(is_array($args[2]))
			{
				unset($args[0]);
				unset($args[1]);
				return vsprintf($this -> langdata[$group][$id], $args[2]);
			}
		} // end putApply();

	} // end i18n;
	
	function showMessage($message, $example)
	{
		global $context;
		
		$context -> getResponse() -> assign('message', $message);
		$context -> getResponse() -> assign('example', $example);
		$context -> getResponse() -> parse('message.tpl');
		die();
	} // end showMessage();

?>
