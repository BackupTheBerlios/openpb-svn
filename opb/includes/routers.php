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
    class opbDefaultRouter implements iOpbRouter
    {
        public function __construct()
        {
        
        } // end __construct();
        
        public function handleData($name)
        {
            if(isset($_GET[$name]))
            {
                return $_GET[$name];
            }
            return NULL;   
        } // end handleData();

        public function createURL($file, $variables)
        {
            $url = $file;
            if(($i = count($variables)) > 0)
            {
                $url .= '?';
                foreach($variables as $name => $value)
                {
                    $i--;
                    $url .= $name.'='.$value;
                    if($i > 0)
                    { 
                        $url .= '&amp;';
                    }
                }
            }
            return $url;
        } // end createURL();
    } // end opbDefaultRouter;
    
    /**
     *
     *
     */
    class opbNiceRouter implements iOpbRouter
    {
        private $dataBuffer;

        public function __construct()
        {
            if(!empty($_SERVER['PATH_INFO']))
            {
                $data = explode('/', substr($_SERVER['PATH_INFO'], 1));
                $namebuffer = '';
                foreach($data as $i => $item)
                {
                    if($i % 2 == 0)
                    {
                        $namebuffer = $item;
                    }
                    else
                    {
                        $this -> dataBuffer[$namebuffer] = $item;
                    }            
                }
            }
        } // end __construct();

        public function handleData($name)
        {
            if(isset($this -> dataBuffer[$name]))
            {
                return $this -> dataBuffer[$name];
            }
            return NULL;   
        } // end handleData();

        public function createURL($file, $variables)
        {
            $url = $file;
            if(($i = count($variables)) > 0)
            {
                $url .= '/';
                foreach($variables as $name => $value)
                {
                    $i--;
                    $url .= $name.'/'.$value;
                    if($i > 0)
                    {
                        $url .= '/';
                    }
                }
            }
            return $url;
        } // end createURL();

    } // end opbNiceRouter;

    class opbValueRouter implements iOpbRouter
    {
        private $dataBuffer;
        private $i = 0;
        private $page;
        private $goto;

        public function __construct()
        {
            if(!empty($_SERVER['PATH_INFO']))
            {
                $dataBuffer = explode('/', substr($_SERVER['PATH_INFO'], 1));
                // handle "from" and "goto" parameters for pagination system

                foreach($dataBuffer as $id => &$value)
                {
                    if(preg_match('/(p|g)([0-9]+)/', $value, $found))
                    {
                        if($found[1] == 'p')
                        {
                            $this -> page = $found[2];
                        }
                        else
                        {
                            $this -> goto = $found[2];
                        }
                    }
					else
					{
						$this -> dataBuffer[] = $value;
					}              
                }
            }
        } // end __construct();

        public function handleData($name)
        {
            switch($name)
            {
                case 'p':
                    return $this -> page;
                case 'goto':
                    return $this -> goto;
                default:
                    if(isset($this -> dataBuffer[$this->i]))
                    {
                        return $this -> dataBuffer[$this->i++];
                    }
                    $this -> i++;
                    return NULL;            
            }
        } // end handleData();

        public function createURL($file, $variables)
        {
            $url = $file;
            if(($i = count($variables)) > 0)
            {
                $url .= '/';
                foreach($variables as $name => $value)
                {
                    $i--;
                    if($name == 'p')
                    {
                    	$url .= 'p'.$value;
                    }
                    else
                    {
                    $url .= $value;
                    }
					if($i > 0)
                    {
                        $url .= '/';
                    }
                }
            }
            return $url;
        } // end createURL();
    } // end opbValueRouter;

?>
