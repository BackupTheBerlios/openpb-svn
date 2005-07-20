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

    class opbLanguage extends opbBaseLanguage implements iOpbI18N
    {
        static private $instance;
		
        static public function getInstance()
        {
            if(self::$instance == NULL)
            {
                self::$instance = new opbLanguage;
            }
            return self::$instance;
        } // end getInstance();	
		
        private function __construct()
        {
            $this -> directory = 'en';
        } // end __construct();

        public function setLocale()
        {
            setlocale(LC_ALL, 'en', 'en_EN');
        } // end setLocale();

        public function date($timestamp = NULL)
        {
            if($timestamp == NULL)
            {
                return date('F j, Y, g:i a');
            }
            return date('F j, Y, g:i a', $timestamp);
        } // end date();

        public function numberFormat($number)
        {
            return number_format($number);
        } // end numberFormat();
    }
    
?>