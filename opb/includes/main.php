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

    set_magic_quotes_runtime(0);

    define('DEBUG_MODE', true);

    if (DEBUG_MODE)
    {
        @set_time_limit(0);
        error_reporting(E_ALL | E_STRICT);
        @ini_set('display_errors', 1);
    }

    /**
     * 
     *
     */
    class OPB
    {
        /**
         * @var
         */
        public $config;
        
         /**
         * @var
         */
        public $services;
        
        /**
         * @var
         */
        public $request;
        
        /**
         * @var
         */
        public $lang;
        
        /**
         * @var OPD
         */
        public $db;
        
        /**
         * @var
         */
        public $router;
        
        /**
         * @var
         */
        public $session;
        
        /**
         * @var
         */
        public $auth;
        
        /**
         * @var OPB
         */
        private static $instance;

        /**
         *
         *
         * @return void
         */
        private function __construct()
        {
            $this -> config  = new opbConfig('config.ini.php');
            $this -> request = new opbRequest;
            
            // added for DAO system, replace it on xml config
            $this -> services = array(
				'forum' => array(
					'topic',
				),
				'topic' => array(
					'post'
				),
				'post'  => array(
					'user', 'poll'
				),
				'user'  => array(),
				'poll'  => array(),
			);
            
            // Router selection
            switch($this->config->get('MAIN', 'router'))
            {
                case 'default':
                    $this->router = new opbDefaultRouter;
                    break;
                case 'nice':
                    $this->router = new opbNiceRouter;
                    break;
                case 'value':
                	$this->router = new opbValueRouter;
                	break;
                default:
                    $this->router = new opbDefaultRouter;
            }
        } // end __construct();

        /**
         *
         *
         * @return OPB
         */
        public static function getInstance()
        {
            if (self::$instance == null)
            {
                self::$instance = new OPB();
            }
            return self::$instance;
        } // end getInstance();

        /**
         *
         *
         * @return void
         */
        public function execute($module)
        {
            $tpl = opbTemplate::getInstance();

            $db_cfg = $this->config->get('OPD');
            $this->db = new OPD(
                $db_cfg['dsn'],
                $db_cfg['user'],
                $db_cfg['password']
            );
            
            $this -> session = new opbSession();
            //$this -> auth = new opbAuth;

            // Tu kod wybierajacy szablon, jezyk itd. w zaleznosci od ustawien w configu/u usera
            // To drugie, jak sesje beda poprawnie dzialaly
            $lang = $this -> config -> get('MAIN', 'language');
            if($lang == NULL)
            {
                $lang = 'en';
            }

            if(is_file(OPB_LNG . $lang . '/language.php'))
            {
                require(OPB_LNG . $lang . '/language.php');
                $this -> lang = opbLanguage::getInstance();
            }
            elseif($lang != 'en')
            {
                if(is_file(OPB_LNG . 'en/language.php'))
                {
                    require(OPB_LNG . 'en/language.php');
                    $this -> lang = opbLanguage::getInstance();
                }
                else
                {
                    throw new FileNotFoundException();
                }
            }
            $this -> lang -> load('global');

            $module = $this -> moduleFactory($module);
            $module -> run();
        } // end execute();

        /**
         *
         *
         * @return 
         */
        public function moduleFactory($module)
        {
            require(OPB_MOD . $module . '.php');
            if(class_exists('opb' . $module))
            {
                $classname = 'opb' . $module;
                return new $classname();
            }
        } // end moduleFactory();
    }

?>
