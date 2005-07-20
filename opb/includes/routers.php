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
                        $_GET[$namebuffer] = $item;
                    }			
                }
            }
        } // end __construct();

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

?>