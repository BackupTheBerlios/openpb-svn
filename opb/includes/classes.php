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
        
	define('CONF_GLOBAL', 3);
	define('CONF_AREA', 2);
	define('CONF_FORUM', 1);
	define('CONF_USER', 0);

	class opbConfig
	{
		private $config;

		public function __construct($config_file)
		{
		    $config_file = OPB_DIR . 'settings/' . $config_file;
			if(file_exists($config_file))
			{
				$this -> config[3] = parse_ini_file($config_file, true);			
			}
			else
			{
				throw new FileNotFoundException();
			}
		} // end __construct();

		public function addConfig($level, $data)
		{
			$this -> config[$level] = $data;		
		} // end addConfig();

        public function get($section, $key = false)
        {
            $ret = NULL;
            foreach ($this->config as $id => $config)
            {
                if (isset($config[$section]))
                {
                    $ret = &$config[$section];
                }
            }
            if($key != false)
            {
                return $ret[$key];
            }
            return $ret;
        }

	} // end opbConfig;
	
	
    define('OPB_MOD_MASTER', 0);
    define('OPB_MOD_SLAVE',  1);

    /**
     *
     *
     *
     */
    abstract class opbModule
    {
        private $workmode;

        public function __construct($workmode = OPB_MOD_MASTER)
        {
            $this -> workmode = $workmode;
        } // end __construct();

        public function authRequest($flags, opbAuthController $controller)
        {

        } // end authRequest();

        abstract public function run();

        public function display(iOpbDisplay $displaySrc)
        {
            if($displaySrc instanceof opbDisplayBoard)
            {
                if($this -> workmode == OPB_MOD_MASTER)
                {
                    $displaySrc -> display();
                }
                else
                {
                    $tpl = opbTemplate::getInstance();
                    $tpl -> assign($displaySrc -> getHandle(), $displaySrc -> getTemplate());
                }
            }
                        /*
                        elseif($displaySrc instanceof opbDisplayMessage)
                        {
                                $displaySrc -> display();
                        }
                        elseif($displaySrc instanceof opbDisplayAuthorizer)
                        {
                                if($this -> workmode == OPB_MOD_MASTER)
                                {
                                        $displaySrc -> display();
                                }
                                else
                                {
                                        $tpl -> opbTemplate::getInstance();
                                        $tpl -> assign($displaySrc -> getHandle(), $displaySrc -> getTemplate());
                                }
                        }
                        elseif($displaySrc instanceof opbDisplayModerator)
                        {
                                if($this -> workmode == OPB_MOD_MASTER)
                                {
                                        $displaySrc -> display();
                                }
                                else
                                {
                                        $tpl -> opbTemplate::getInstance();
                                        $tpl -> assign($displaySrc -> getHandle(), $displaySrc -> getTemplate());
                                }
                        }
                        elseif($displaySrc instanceof opbDisplayAdministrator)
                        {
                                if($this -> workmode == OPB_MOD_MASTER)
                                {
                                        $displaySrc -> display();
                                }
                                else
                                {
                                        $tpl -> opbTemplate::getInstance();
                                        $tpl -> assign($displaySrc -> getHandle(), $displaySrc -> getTemplate());
                                }
                        }
                        */
        } // end display();
    }

        abstract class opbBaseLanguage
        {
                public $directory;
                private $langdata;
                private $modified;

                public function load($id)
                {
                        if(is_file(OPB_LNG.$this->directory.'/'.$id.'.ini.php'))
                        {
                                $this -> langdata[$id] = parse_ini_file(OPB_LNG.$this->directory.'/'.$id.'.ini.php');
                        }
                        else
                        {
                                throw new FileNotFoundException();
                        }
                } // end load();

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

                public function apply(opt_template $tpl, $group, $id)
                {
                        $args = func_get_args();
                        unset($args[0]);
                        unset($args[1]);
                        unset($args[2]);
                        $this -> modified[$group][$id] = vsprintf($this -> langdata[$group][$id], $args);
                } // end apply();
        }

        define('ACCESS_ALL', 0);
        define('ACCESS_USERS', 1);
        define('ACCESS_MODS', 2);
        define('ACCESS_ADMINS', 3);

        class opbAuth
        {
                private $type;
                private $auth;
                public $groups;
                public $mainGroup;
                public $user;

                public function __construct()
                {
                        $this -> groups = array();
                        $opb = OPB::getInstance();
                        $sql = opd::getInstance();
                        /*
                        if($opb -> session -> sessionUserId == 0)
                        {
                                $this -> mainGroup = $sql -> FetchAll('SELECT * FROM opb_groups WHERE type = \''.GROUP_GUEST.'\' LIMIT 1');
                                $this -> auth = unserialize($this -> groups[0]['access']);
                                $this -> user = new stdClass;
                                $this -> type = 0;
                        }
                        else
                        {
                                $this -> user = new apiUser($opb -> session -> sessionUserId);




                        }
                        */
                } // end __construct();
        }
        
?>