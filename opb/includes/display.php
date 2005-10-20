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
    class opbDisplayBoard implements iOpbDisplay
	{
        private $handle;
        private $template;
        
        public function __construct($handle, $template)
        {
            $this -> handle = $handle;
            $this -> template = $template;
        } // end __construct();

        public function getHandle()
        {
            return $this -> handle;
        } // end getHandle();

        public function getTemplate()
        {
            return $this -> template;
        } // end getHandle();

        public function display()
        {
            $tpl = opbTemplate::getInstance();
            $tpl -> parse('overall_header.tpl');
            $tpl -> parse($this -> template);
            $tpl -> parse('overall_footer.tpl');		
        } // end display();	
    }

?>
