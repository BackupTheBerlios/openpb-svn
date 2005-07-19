<?php

        class OPB
        {
                public $config;
                public $request;
                public $lang;
                public $db;
                public $router;
                public $session;
                public $auth;
                static private $instance;

                private function __construct()
                {
                        $this -> config = new opbConfig('config.ini.php');
                        $this -> request = new opbRequest;

                        // Router selection
                        switch($this -> config -> router)
                        {
                                case 'default':
                                        $this -> router = new opbDefaultRouter;
                                        break;
                                case 'nice':
                                        $this -> router = new opbNiceRouter;
                                        break;
                                default:
                                        $this -> router = new opbDefaultRouter;
                        }
                } // end __construct();

            public static function getInstance()
            {
                if (self::$instance == null)
                        {
                    self::$instance = new OPB();
                }
                return self::$instance;
            } // end getInstance();

            public function execute($module)
            {
                    $tpl = opbTemplate::getInstance();

                    $this -> db = new opd($this->config->getOpdConfig());
                    $sql = opd::getInstance();

                    $this -> session = new opbSession;
                    //$this -> auth = new opbAuth;

                    // Tu kod wybierajacy szablon, jezyk itd. w zaleznosci od ustawien w configu/u usera
                    // To drugie, jak sesje beda poprawnie dzialaly
                    $lang = $this -> config -> language;
                    if($lang == NULL)
                    {
                            $lang = 'en';
                    }

                    if(is_file(OPB_LNG.$lang.'/language.php'))
                    {
                            require(OPB_LNG.$lang.'/language.php');
                            $this -> lang = opbLanguage::getInstance();
                    }
                    elseif($lang != 'en')
                    {
                            if(is_file(OPB_LNG.'en/language.php'))
                            {
                                    require(OPB_LNG.'en/language.php');
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

            public function moduleFactory($module)
            {
                    require(OPB_MOD.$module.'.php');
                    if(class_exists('opb'.$module))
                    {
                            $classname = 'opb'.$module;
                            return new $classname();
                    }
            } // end moduleFactory();
        }

?>