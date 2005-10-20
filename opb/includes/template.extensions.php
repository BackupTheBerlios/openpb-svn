<?php
// +----------------------------------------------------------------------+
// | Open Power Board                                                     |
// | Copyright (c) 2005 OpenPB team, http://www.openpb.net/               |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// +----------------------------------------------------------------------+
//
// $Id$
    /**
     *
     *
     */
    function optPostfilterTemplate($code, optClass $opt)
    {
        return '$i18n = opbLanguage::getInstance(); $opb = OPB::getInstance(); '.$code;
    } // end opt_postfilter_template();

    /**
     *
     *
     */
    class opbTemplate extends optClass
    {
        static private $instance;
        
        private $templateInfo;
		
        public function __construct()
        {
            $this->control['url'] = 'opt_url';
			
            // Here we add additional functions, control instructions etc.
            $opb = OPB::getInstance();
            
            // Template initialization
            $conf = $opb->config->get('OPT');
            $this -> root = OPB_TPL.'devView/';
            $this -> plugins = OPB_INC.'opt/plugins/';
            $this -> cache = OPB_TPL_CACHE;
            
            $this->setCustomI18N('$i18n->put(\'%s\', \'%s\')', '$i18n', 'Template');
			
            $this->assign('address', $opb -> config -> get('MAIN', 'address'));
            
            $templateInfo = @file_get_contents($this -> root.'templateInfo.ini');
            
            if($templateInfo != NULL)
            {
            	$this -> templateInfo = unserialize($templateInfo);
            }
        } // end __construct();
		
        static public function getInstance()
        {
            if(self::$instance == NULL)
            {
                self::$instance = new opbTemplate;
            }
            return self::$instance;
        } // end getInstance();
        
        public function getInfo($name)
        {
			if(!isset($this -> templateInfo[$name]))
			{
				return NULL;
			}
			return str_replace(
				array(
					'&lt;',
					'&gt;',
					'&quot;',
					'&amp;'
				
				),
				array(
					'<',
					'>',
					'"',
					'&'
				)			
			,$this -> templateInfo[$name]);        
        } // end getInfo();
        
        public function buildTemplateInfo($tpl, $data)
        {
        	if(is_array($data))
        	{
        		// make sure the data will not break the serialization
        		foreach($data as &$value)
        		{
        			$value = htmlspecialchars($value);        		
        		}        	
        	
        		file_put_contents(OPB_TPL.$tpl.'/templateinfo.ini', serialize($data));
				return 1;        	
        	}
			return 0;   
        } // end buildTemplateInfo();

    } // end opbTemplate;
    
?>
