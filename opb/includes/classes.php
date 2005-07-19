<?php
        define('OPB_MOD_MASTER', 0);
        define('OPB_MOD_SLAVE', 1);

        define('CONF_GLOBAL', 3);
        define('CONF_AREA', 2);
        define('CONF_FORUM', 1);
        define('CONF_USER', 0);

        class opbConfig
        {
                private $config;

                public function __construct($config_file)
                {
                        if(file_exists($config_file))
                        {
                                $this -> config[3] = parse_ini_file($config_file, true);
                        }
                        else
                        {
                                throw new FileNotFoundExcpetion();
                        }
                } // end __construct();

                public function addConfig($level, $data)
                {
                        $this -> config[$level] = $data;
                } // end addConfig();

                public function __get($name)
                {
                        foreach($this -> config as $id => &$config)
                        {
                                if(isset($config[$name]))
                                {
                                        return $config[$name];
                                }
                        }
                        throw new InternalException(1);
                } // end __get();

                public function getOpdConfig(){
                        $opd_config = array();
                        foreach($this -> config[CONF_GLOBAL] as $key => $value) {
                                if(substr($key, 0, 4) == 'opd:') {
                                        $opd_key = explode(':', $key);
                                        //var_dump($opd_key);
                                        $opd_config[$opd_key['1']] = $value;
                                }
                        }
                        return $opd_config;
                } // end getOpdConfig();

        } // end opbConfig;

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

        class opbSession {
            /**
            * Id Sessji
            *
            * @access protected
            * @author hwao
            * @var string $sessionId
            */
            protected $sessionId = '';
            /**
            * Nazwa u�ytkownika
            *
            * @access protected
            * @author hwao
            * @var string User
            */
            protected $sessionUser = '';
            /**
            * Id u�ytkownika
            *
            * @access protected
            * @author Bora
            * @var int Id
            */
            protected $sessionUserId = '';
            /**
            * Ip u�ytkownika
            *
            * @access protected
            * @author hwao
            * @var string $sessionIp
            */
            protected $sessionIp = '';
            /**
            * User Agent uzytkownika
            *
            * @access protected
            * @author hwao
            * @var string $sessionBrowser
            */
            protected $sessionBrowser = '';
            /**
            * Czas zaczecia sessji
            *
            * @access protected
            * @author hwao
            * @var int $sessionTime
            */
            protected $sessionTime = 0;
            /**
            * Czas trwania sesji
            *
            * @access protected
            * @author Bora
            * @var int $sessionDuration
            */
            protected $sessionDuration = 360;
            /**
            * 0 - user, 1 - bot, 2 - spam
            *
            * @access protected
            * @author hwao
            * @var int $sessionType
            */
            protected $sessionType = 0;
            /**
            * Nazwa bota wyszukiwarki (o ile $sessionType = 0 )
            *
            * @access protected
            * @author hwao
            * @var string $sessionBotName
            */
            protected $sessionBotName = '';

            /**
            * Czy user ma slad po sessji (cookie, get), jej id
            *
            * @access protected
            * @author hwao
            * @var bool $sessionExists
            */
            // protected $sessionExists = false;
            /**
            * Czy ma zwracac w url'u
            *
            * @access protected
            * @author Bora
            * @var bool
            */
            protected $sessionReturnInUrl = true;
            /**
            * Uchwyt do db
            *
            * @access protected
            * @author Bora
            * @var object
            */
            protected $db = false;
            /**
            * Uchwyt do request
            *
            * @access protected
            * @author Bora
            * @var object
            */
            protected $request = false;
            /**
            * Klucz do $_COOKIE gdzie trzymane jest id sessji (sta�a)
            *
            * @author hwao
            * @var string Session::keyCookie
            */
            const keyCookie = 'OPB_SessionId';
            /**
            * Klucz do $_GET gdzie trzymane jest id sessji (sta�a)
            *
            * @author hwao
            * @var string Session::keyGet
            */
            const keyGet = 'sessionId';
            /**
            * Dlugosc klucza id sessji (sta�a)
            *
            * @author hwao
            * @var string Session::keyLen
            */
            const keyLen = 32;

            /**
            * Konstrukotr
            *
            * Nie wiem czy nie "wywalic" tego z konstrukotra poza obiekt
            *
            * @access public
            * @author hwao
            * @param void $
            * @return void
            */
            public function __construct()
            {
                $opb = OPB::getInstance();
                $this->request = $opb->request;
                /**
                *  Dawny sterownik SQL, OPD
                */
                //$this->db = opd::getInstance();

                /**
                *  Nowy sterownik, PDO
                */
                $dsn = 'mysql:dbname=openpb;host=localhost';
                $user = 'root';
                $password = '****';
                $this->db = new PDO($dsn, $user, $password);

                $this->sessionIp = $this->getIp();
                $this->sessionBrowser = $this->getBrowser();
                $this->sessionTime = time();
                $this->sessionId = $this->getSessionId();
                        $restore = $this->restoreSession();
                if (!$restore)
                        {
                    /**
                    * * Nowa sessja
                    */
                    debug::add_log(DEBUG_LEVEL_INFO, 'session::sessionExist', false);
                    $this->sessionId = $this->genSessionId();
                                $this->sessionReturnInUrl = false;
                    $this->request->removeCookie(self::keyCookie);
                    $this->sessionType = $this->testWebBot();
                                if ($this->sessionType == 2)
                                {
                                    echo("HTTP/1.0 404 Not Found");
                                }
                    $this->createSession();
                }
                        else
                        {
                    /**
                    * * Stara sessja
                    */
                                if ($restore['session_type'] == 2)
                                {
                                    echo("HTTP/1.0 404 Not Found");
                                }
                    debug::add_log(DEBUG_LEVEL_INFO, 'session::sessionExist', true);
                    $this->sessionUser = $restore['session_user'];
                    $this->sessionUserId = $restore['session_user_id'];
                }
                $this->request->updateCookie(opbSession::keyCookie, $this->sessionId, 3600);
            }
            /**
            * restoreSession()
            *
            * Sprawdzanie czy sesja istnieje w DB jednoczesnie wczytujac ja.
            *
            * @author Bora
            * @access protected
            * @return void
            */
            protected function restoreSession()
            {
                if(!$this->sessionId)
                        {
                    debug::add_log(DEBUG_LEVEL_INFO, 'session::restoreSession', false);
                    return false;
                }
                        $rs = $this->db->Query("SELECT * FROM opb_session WHERE session_id = '" . $this->sessionId . "' AND session_time > '" . (time() - $this->sessionDuration) . "' AND session_browser = '" . $this->sessionBrowser . "' AND session_ip = '" . $this->sessionIp . "'");
                if($fields = $rs->fetch())
                        {
                    debug::add_log(DEBUG_LEVEL_INFO, 'session::restoreSession', true);
                }
                        else
                        {
                    debug::add_log(DEBUG_LEVEL_INFO, 'session::restoreSession', false);
                }
                return $fields;
            }

            /**
            * createSession()
            *
            * Sessja nie istnieje musi zostac stowrzona kod wydobywa informacje jakie trzeba
            * nastepnie formatuje podane zapytanie zapisuje dane do bazy a informacje jakie uzyskal
            * upowszechnia (czytam koncowke komentarza z Session::loadSession)
            *
            * @author hwao
            * @access protected
            * @return void
            */
            protected function createSession()
            {
                /**
                * * Stworzenie nowej sessji
                */
                        $this->db->Exec("INSERT INTO opb_session (session_id, session_type, session_time, session_browser, session_ip) VALUES ('" . $this->sessionId . "', '" . $this->sessionType . "', '" . time() . "','" . $this->sessionBrowser . "', '" . $this->sessionIp . "')");
            }

            /**
            * writeSession()
            *
            * Przedluznie sesji.
            *
            * @todo Dodac multizapytania pod bazy to zezwalajace
            * @author Bora
            * @access public
            * @return void
            */
            public function writeSession()
            {
                /**
                * * Stworzenie nowej sessji
                */
                        $rs = $this->db->Exec("UPDATE opb_session SET session_time = '" . time() . "', session_user = '" . $this->sessionUser . "', session_user_id = '" . $this->sessionUserId . "' WHERE session_id = '" . $this->sessionId . "'");
                        $this->db->Exec("DELETE FROM opb_session WHERE session_time < '" . (time() - $this->sessionDuration) . "'");
            }

            /**
            * testWebBot()
            *
            * Proboje rozpoznac czy dana sessja to bedzie uzytkownik (czlowiek)
            * czy tez jakis internetowy bot...
            *
            * @author hwao
            * @access protected
            * @return int
            */
            protected function testWebBot()
            {
                /**
                * * Sciezka
                */
                $arrBotList = parse_ini_file('./bots.ini.php', true);
                foreach($arrBotList['robots'] As $k => $v)
                        {
                    // preg_match jest tutaj zb�dne skorro wystarczy zwylke strstr
                    // var_dump($k, $v, stristr($k, $this->sessionBrowser));
                    if (stristr($k, $this->sessionBrowser))
                                {
                        $this->sessionBotName = $k;
                        //echo "bot $v<br>\n";
                        return 1;
                        break;
                    }
                }
                        foreach($arrBotList['spam'] As $k => $v)
                        {
                    // preg_match jest tutaj zbedne skorro wystarczy zwylke strstr
                    // var_dump($k, $v, stristr($k, $this->sessionBrowser));
                    if (stristr($k, $this->sessionBrowser))
                                {
                        $this->sessionBotName = $k;
                        //echo "spambot $v<br>\n";
                        return 2;
                        break;
                    }
                }
                return 0;
            }

            /**
            * getIp()
            *
            * Zwraca Ip uzytkownika
            *
            * @todo trzeba dodac obsluge proxy (dopisac tutaj)
            * @author hwao
            * @access protected
            * @return string
            */
            protected function getIp()
            {
                return $_SERVER['REMOTE_ADDR'];
            }

            /**
            * getBrowser
            *
            * Wydobywa User Agenta uzytkownika
            *
            * @access protected
            * @author hwao
            * @return string
            */
            protected function getBrowser()
            {
                return substr($_SERVER['HTTP_USER_AGENT'], 0, 92);
            }

            /**
            * getSessionId()
            *
            * Szuka Session Id w Cookie i get (jak znajdzie to je zwraca co nie znaczy
            * ze sessja istnieje (moze byc "urojona" przez uzytkownika, albo juz wygasla)
            * jezeli nie zajaduje to odsyla do generatora id sessji genSessionId )
            *
            * @author hwao
            * @access protected
            * @return string |false
            */
            protected function getSessionId()
            {
                $keyCookie = self::keyCookie;
                $keyGet = self::keyCookie;
                if($this->request->map(self::keyCookie, OPB_COOKIE, MAP_TEXT))
                        {
                    debug::add_log(DEBUG_LEVEL_INFO, 'session::method:', 'cookie');
                    $this->sessionReturnInUrl = true;
                    return $this->request->$keyCookie;
                }
                if($this->request->map($keyGet, OPB_GET, MAP_TEXT))
                        {
                    debug::add_log(DEBUG_LEVEL_INFO, 'session::method:', 'get');
                    return $this->request->$keyCookie;
                }
                if($this->request->getType === OPB_POST)
                        {
                    debug::add_log(DEBUG_LEVEL_INFO, 'session::method:', 'post');
                    if ($this->request->map($keyGet, OPB_POST, MAP_TEXT)) {
                        return $this->request->$keyCookie;
                    }
                }
                // $this->sessionExists = false;
                debug::add_log(DEBUG_LEVEL_INFO, 'session::method:', 'puste');
                return false;
            }

            /**
            * genSessionId()
            *
            * Generuje id Sessji mozna dodac sprawdzenie czy nie istnieje juz takie w bazie
            *
            * @author hwao
            * @access protected
            * @return string
            */
            protected function genSessionId()
            {
                $md = md5(uniqid(rand(), true));
                debug::add_log(DEBUG_LEVEL_INFO, 'session::genSessionId:', $md);
                return $md;
            }
            /**
            * Session::GetSid()
            *
            * Zwraca identyfikator sesji
            *
            * @author Bora
            * @return string
            */
            public function GetSid()
            {
                if (!$this->sessionReturnInUrl) {
                    return $this->sessionId;
                } else {
                    return false;
                }
            }
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