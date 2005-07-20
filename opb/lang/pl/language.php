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

    class OpbLanguage extends OpbBaseLanguage implements iOpbI18N
    {
        public function __construct()
        {
            $this -> directory = 'pl';
        } // end __construct();

        public function setLocale()
        {
            setlocale(LC_ALL, 'pl_PL.UTF-8', 'pl.UTF-8', 'Polish_Poland'); 	
        } // end setLocale();

        public function date($timestamp = NULL)
        {
            if($timestamp == NULL)
            {
                return date('d.m.Y, H:i');			
            }
            return date('d.m.Y, H:i', $timestamp);		
        } // end date();
        
        public function numberFormat($number)
        {
            return number_format($number, 2, ',', ' ');		
        } // end numberFormat();
    }
    
?>