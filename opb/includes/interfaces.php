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
    interface iOpbDisplay
    {
        public function display();
    }

    /**
     *
     *
     */
    interface iOpbI18N
    {
        public function setLocale();
        public function date($timestamp = NULL);
        public function numberFormat($number);
    }
	
    /**
     *
     *
     */
    interface iOpbRouter
    {
        public function __construct();
        public function createURL($file, $variables);	
    }
    
?>