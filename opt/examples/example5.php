<?php
	define('OPT_DIR', '../lib/');
	require('../lib/opt.class.php');
  
	class i18n{
		private $data;
		private $replacements;
		static $instance;
  	
		private function __construct()
		{
			$this -> data = array(
				'global' => 
				array(
					'text1' => 'This is text one',
					'text2' => 'This is text two',
					'text3' => 'This is text three',
					'date' => 'Today is %s, good day for fishing'		
				)
			);
		} // end __construct();
		
		static public function getInstance()
		{
			if(!is_object(self::$instance))
			{
				self::$instance = new i18n;
			}
			return self::$instance;
		} // end getInstance();  
  
	 	public function put($group, $text_id)
	 	{
			if(isset($this->replacements[$group][$text_id]))
			{
				return $this->replacements[$group][$text_id];
			}
			return $this->data[$group][$text_id]; 	
		} // end put();
		
		public function apply(optClass $tpl, $group, $text_id)
		{
			$args = func_get_args();
			unset($args[0]);
			unset($args[1]);
			unset($args[2]);
			$this -> replacements[$group][$text_id] = vsprintf($this -> data[$group][$text_id], $args);
		} // end apply();  
	}
	
	function optPostfilterI18n($code, optClass $opt)
	{
		// pass the instance of i18n system to the processed template
		return '$i18n = i18n::getInstance(); '.$code;
	} // end optPostfilterI18n();
 
	try{ 
		$tpl = new optClass; 
		$tpl -> root = './templates/';
		$tpl -> compile = './templates_c/';
		$tpl -> gzipCompression = 1;
		$tpl -> httpHeaders(OPT_HTML); 
    
		// init default i18n system:
		// 1. language block pattern
		// 2. object for dedicated "apply" function
		// 3. postfilter name
		$tpl -> setCustomI18n('$i18n->put(\'%s\',\'%s\')', '$i18n', 'I18n');

		$tpl -> assign('current_date', date('d.m.Y')); 
		$tpl -> parse('example5.tpl'); 
	}catch(optException $exception){ 
		optErrorHandler($exception); 
	}
?>
