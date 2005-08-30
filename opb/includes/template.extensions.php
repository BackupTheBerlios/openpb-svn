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
    function opt_postfilter_template($code, opt_template $opt)
    {
        return '$i18n = opbLanguage::getInstance(); $opb = OPB::getInstance(); '.$code;
    } // end opt_postfilter_template();

    /**
     *
     *
     */
    class opbTemplate extends opt_template
    {
        static private $instance;
		
        public function __construct()
        {
            parent::__construct();
            $this->control['url'] = 'opt_url';
			
            // Here we add additional functions, control instructions etc.
            $opb = OPB::getInstance();
            
            // Template initialization
            $this -> conf = $opb->config->get('OPT');
            $this -> conf['root'] = OPB_TPL.'devView/';
            $this -> conf['plugins'] = OPB_INC.'opt/plugins/';
            $this -> conf['cache'] = OPB_TPL_CACHE;
            
            $this->set_custom_i18n('$i18n->put(\'%s\', \'%s\')', '$i18n', 'template');
            $this->init();
			
            $this->assign('address', $opb -> config -> get('MAIN', 'address'));
        } // end __construct();
		
        static public function getInstance()
        {
            if(self::$instance == NULL)
            {
                self::$instance = new opbTemplate;
            }
            return self::$instance;
        } // end getInstance();	
    } // end opbTemplate;
    
?>
