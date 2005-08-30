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

    // TODO: add exception handling
    require('common.php');
	
    try
    {
        $main = OPB::getInstance();

        if(!$main -> request -> map('act', OPB_GET, MAP_STRING))
        {
            $act = 'index';	
        }
        else
        {
	        // temporary
            $act = $main -> request -> act;
        }

        $main -> execute($act);
    }
    catch (OPBException $e)
    {
        echo $e;
    }
    catch (PDOException $e)
    {
        echo $e;
    }
    
?>
