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
        public function execute()
        {
            $tpl = opbTemplate::getInstance();

            $db_cfg = $this->config->get('OPD');
            $this->db = new OPD(
                $db_cfg['dsn'],
                $db_cfg['user'],
                $db_cfg['password']
            );
            
            $this -> session = new opbSession();
			$this -> auth = new Authorization();
		    /* PRZYKLADOWE GRUPBY *wyciagane z bazy danych */
		    // --- GRUPY
		    $Modzi = array(
		        'global' => array( "read" => 0, "write" => 0 ),
		        
		        'local' => array(
		            1 => array( "read" => 1, "write" => 1 ),
		            4 => array( "read" => 1, "write" => 0 ),
		            0 => array( "read" => 1),
		            'users' => array( "read" => 1 )
		        )
		    );
		    
		    $Admin = array(
		        'global' => array( 'read' => 1, 'write' => 1 ),
		        'local' => array(
		            1 => array( "read" => 0, "write" => 0 ),
		            5 => array( "read" => 1, "write" => 0 ),
		        )
		    );
			/* Ustawienie grup jakie ma uzytkownik */
		    $this -> auth -> setGroups( array( $Modzi, $Admin ) );
		    
		    /* Wymieszanie praw */
		    $this -> auth -> PermsCombine();
			/* Tutaj powinnien byc user i w jakis sposob pobierane aktualne ID forum */
			
			
			
			
			
			
			// NR FORUM (id FORUM)
			$module = $this->getRequestModule();
			$moduleID = $this->getRequestForumID();
			//$Mid   = 2;     // ID MODULU
		    // CO NAM trzeba :) czyli jaka akcja
		    $Prawa = 'read'; // PRAWO jakie potrzebne jest
		    
			//if($this -> request -> map('act', OPB_GET, MAP_STRING) && 
			if(!$this->auth->tp( $module, $Prawa ))
	        {
				// temporary
				$module = 'index';
	        }
	        /*
	        else
	        {
		        $module = 'index';
	        }
	        //*/
	        // it should use some DAO engine
	        require(OPB_INC.'dao/'.'DAO.class.php');
	        require(OPB_INC.'dao/'.'AreasDAO.class.php');
	        $areas = new areasValuesDAO();
	        if ($moduleID !== false)
	        {
	       	 	$this->config->addConfig(2, $areas ->byForumID($moduleID));
	        }
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
			if(is_file(OPB_MOD . $module . '.php'))
			{
				require(OPB_MOD . $module . '.php');
			}
			else
			{
				throw new FileNotFoundException();
			}
            if(class_exists('opb' . $module))
            {
                $classname = 'opb' . $module;
                return new $classname();
            }
        } // end moduleFactory();
        
        public function loadLibrary($lib)
        {
            require(OPB_INC.'lib.'.$lib.'.php');
        } // end loadLibrary();
        
        public function getRequestModule()
        {
            if ($this -> request -> map('forum', OPB_GET, MAP_INTEGER)){
            	return 'forum';
            }elseif ($this -> request -> map('forum', OPB_GET, MAP_STRING)){
            	return $this -> request -> forum;
            }else{
            	return 'index';	
            }
        } // end getRequestModule();
        
        public function getRequestForumID()
        {
            if ($this -> request -> map('forum', OPB_GET, MAP_INTEGER)){
            	return $this -> request -> forum;
            }else{
            	return false;	
            }
        } // end getRequestPerm();
    }

?>
