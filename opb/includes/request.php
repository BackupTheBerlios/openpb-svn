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

    define('OPB_POST',   0);
    define('OPB_GET',    1);
    define('OPB_COOKIE', 2);
    
    // data type flags
    define('MAP_REQUIRED',  1);
    define('MAP_INTEGER',   2);
    define('MAP_FLOAT',     4);
    define('MAP_NUMERIC',   8);
    define('MAP_BOOL',     16);
    define('MAP_STRING',   32);
    define('MAP_TEXT',     64);
    define('MAP_CHOOSE',  128);
    define('MAP_DEFAULT', 256);
    define('MAP_PATTERN', 512);

    // additional flags
    define('MAP_GT',        1024); // greather than
    define('MAP_LT',        2048); // lower than
    define('MAP_SCOPE',     4096);
    define('MAP_COMPARE',   8192);
    define('MAP_LENGTH',   16384);
    define('MAP_PASSWORD', 32768);
    define('MAP_BASE64',   65536);
    
    /**
     * Function for stripping magic quotes.
     *
     * @param array to remove quotes from e.g. $_GET, $_POST
     * @return void
     */
    function remove_slashes(&$var)
    { 
        if (is_array($var))
        {
            foreach ($var as $key => $value)
            {
                $this->remove_slashes($var[$key]);       
            }
        }
        else
        {
            $var = stripslashes($var);
        }
    }
  
    // Strip quotes if magic quotes are on
    if (get_magic_quotes_gpc())
    {
        remove_slashes($_GET);
        remove_slashes($_POST);
        remove_slashes($_REQUEST);
        remove_slashes($_FILES);
        remove_slashes($_COOKIES);
    }
    
    /**
     *
     *
     */
    class opbRequest
    {
        private $type;
        public $data;
        private $ok;
        public $defaultTrim;
        
        public function __construct()
        {
            if($_SERVER['REQUEST_METHOD'] == 'POST')
            {
                $this -> type = OPB_POST;
            }
            else
            {
                $this -> type = OPB_GET;
            }
            $this -> defaultTrim = 1;
            $this -> data = array();
            $this -> ok = 1;
        } // end __construct();
        
        public function __get($name)
        {
            if(isset($this -> data[$name]))
            {
                return $this -> data[$name];
            }
            return NULL;
        } // end __get();
        
        public function isOK()
        {
            return $this -> ok;
        } // end isOK();
        
        public function getType()
        {
            return $this -> type;
        } // end isOK();
        
        public function map($name, $method_id, $flags)
        {
            switch($method_id)
            {
                case OPB_POST:
                        $method = &$_POST;
                        break;
                case OPB_GET:
                        $method = &$_GET;
                        break;
                case OPB_COOKIE:
                        $method = &$_COOKIE;
                        break;
                default:
                        return 0;
            }

            $arg = func_get_args();
            $ai = 3;

            // if MAP_REQUIRED and the field doesn't exist, terminate
            if(!isset($method[$name]) && $flags & MAP_REQUIRED)
            {
                $this -> ok = 0;
                return 0;
            }

            if(isset($method[$name]))
            {
                
                // ok, map the field
                
                if($this -> defaultTrim == 1)
                {
                    $method[$name] = trim($method[$name]);
                }
                
                if($flags & MAP_BASE64)
                {
                    $method[$name] = base64_decode($method[$name]);
                }

                $extype = 0;
                if(!$this -> extractType($flags, $method[$name], $extype))
                {
                    $this -> ok = 0;
                    return 0;
                }
                if($extype == MAP_PATTERN)
                {
                    if(!preg_match($arg[$ai], $method[$name]))
                    {
                        $this -> ok = 0;
                        return 0;
                    }
                    $ai++;        
                }
                
                if($flags & MAP_SCOPE)
                {
                    if($extype == MAP_STRING || $extype == MAP_STRING)
                    {
                        if(!($arg[$ai] < strlen($method[$name]) && strlen($method[$name]) < $arg[$ai+1]))
                        {
                            $this -> ok = 0;
                            return 0;
                        }
                    }
                    else
                    {
                        if(!($arg[$ai] < $method[$name] && $method[$name] < $arg[$ai+1]))
                        {
                            $this -> ok = 0;
                            return 0;
                        }
                    }
                    $ai += 2;                
                }
                elseif($flags & MAP_GT)
                {
                    if($extype == MAP_STRING || $extype == MAP_TEXT)
                    {
                        if(!($arg[$ai] < strlen($method[$name])))
                        {
                            $this -> ok = 0;
                            return 0;
                        }
                    }
                    else
                    {
                        if(!($arg[$ai] < $method[$name]))
                        {
                            $this -> ok = 0;
                            return 0;
                        }
                    }
                    $ai++;
                }
                elseif($flags & MAP_LT)
                {
                    if($extype == MAP_STRING || $extype == MAP_TEXT)
                    {
                        if(!($arg[$ai] > strlen($method[$name])))
                        {
                            $this -> ok = 0;
                            return 0;
                        }
                    }
                    else
                    {
                        if(!($arg[$ai] > $method[$name]))
                        {
                            $this -> ok = 0;
                            return 0;
                        }
                    }
                    $ai++;
                }
                
                if($flags & MAP_PASSWORD && $extype != MAP_TEXT)
                {
                    if($method[$name] != $method[$arg[$ai]])
                    {
                        $this -> ok = 0;
                        return 0;
                    }
                    $ai++;
                }
                if($flags & MAP_COMPARE && $extype != MAP_TEXT && $extype != MAP_DEFAULT)
                {
                    if($method[$name] != $arg[$ai])
                    {
                        $this -> ok = 0;
                        return 0;
                    }
                    $ai++;
                }
                elseif($flags & MAP_LENGTH && ($extype == MAP_TEXT || $extype != MAP_STRING))
                {
                    if(strlen($method[$name]) != $arg[$ai])
                    {
                        $this -> ok = 0;
                        return 0;
                    }
                    $ai++;
                }            
            }
            
            // if we are here, everything is all right
            switch($method_id)
            {
                case OPB_POST:
                        if (isset($_POST[$name])) 
                        {
							$this -> data[$name] = $_POST[$name];
						}
						else
						{
							$this -> data[$name] = NULL;
							$this -> ok = 0;
            				return 0;
						}
                        break;
                case OPB_GET:
                        if (isset($_GET[$name])) 
                        {
							$this -> data[$name] = $_GET[$name];
						}
						else
						{
							$this -> data[$name] = NULL;
							$this -> ok = 0;
            				return 0;
						}
                        break;
                case OPB_COOKIE:
						if (isset($_COOKIE[$name])) 
						{
							$this -> data[$name] = $_COOKIE[$name];
						}
						else
						{
							$this -> data[$name] = NULL;
							$this -> ok = 0;
            				return 0;
						}
                        break;
            }
            $this -> ok = $this -> ok && 1;

            return 1;
        } // end map();

        private function extractType($mapping, &$value, &$extracted_type)
        {
            if($mapping & MAP_INTEGER)
            {
                $extracted_type = MAP_INTEGER;
                return ctype_digit($value);
            }
            elseif($mapping & MAP_FLOAT)
            {
                $extracted_type = MAP_FLOAT;
                return preg_match('/([0-9]*?)[.,]([0-9]*?)/', $value);
            }
            elseif($mapping & MAP_NUMERIC)
            {
                $extracted_type = MAP_NUMERIC;
                return preg_match('/([0-9 -]*?)([.,][0-9]*?)?/', $value);
            }
            elseif($mapping & MAP_STRING)
            {
                if(!ctype_digit($value))
                {
                    $extracted_type = MAP_STRING;
                    return (strlen($value) < 256);
                }
            }
            elseif($mapping & MAP_TEXT)
            {
                if(!ctype_digit($value))
                {
                    $extracted_type = MAP_TEXT;
                    return 1;
                }
            }
            elseif($mapping & MAP_BOOL)
            {
                $extracted_type = MAP_BOOL;
                return preg_match('/(0|1)/', $value);
            }
            elseif($mapping & MAP_PATTERN)
            {
                $extracted_type = MAP_PATTERN;
                return 1;
            }
            elseif($mapping & MAP_CHOOSE)
            {
                $extracted_type = MAP_CHOOSE;
                if($value == 'on' || $value == 'off')
                {
                    $value = ($value == 'on' ? 1 : 0);
                    return 1;
                }
            }
            elseif($mapping & MAP_DEFAULT)
            {
                $extracted_type = MAP_DEFAULT;
                return 1;
            }
            $extracted_type = MAP_DEFAULT;
            return 0;
        } // end extractType();

        public function createCookie($name, $value, $time = NULL)
        {
            if(!headers_sent())
            {
                if($time != NULL)
                {
                    setcookie($name, $value, $time);
                }
                else
                {
                    setcookie($name, $value);
                }
                $_COOKIE[$name] = $value;
                return 1;
            }
            return 0;
        } // end createCookie();

        public function updateCookie($name, $value, $peroid)
        {
            if(!headers_sent())
            {
                if(isset($_COOKIE[$name]))
                {
                    setcookie($name, $_COOKIE[$name], time() + $peroid);
                }
                else
                {
                    setcookie($name, $value, time() + $peroid);
                }
                return 1;
            }
            return 0;
        } // end updateCookie();
        
        public function removeCookie($name)
        {
            if(!headers_sent())
            {
                setcookie($name, '', 0);
                unset($_COOKIE[$name]);
                return 1;
            }
            return 0;
        } // end removeCookie();
    }
    
?>