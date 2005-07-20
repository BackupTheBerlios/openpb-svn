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

    define('OPB_DIR',  dirname(__FILE__) . '/');
    define('OPB_INC',  OPB_DIR . 'includes/');
    define('OPB_DAO',  OPB_INC . 'dao/');
    define('OPD_PATH', OPB_INC . 'opd/');
    define('OPB_MOD',  OPB_DIR . 'modules/');
    define('OPB_TPL',  OPB_DIR . 'templates/');
    define('OPB_TPL_CACHE', OPB_DIR . 'data/cache/opt/');
    define('OPB_LNG',  OPB_DIR . 'lang/');

    require(OPB_INC . 'opd/OPD.php');
    require(OPB_INC . 'opt/opt.class.php');
    require(OPB_INC . 'dao/common.php');
	
    require(OPB_INC . 'interfaces.php');
    require(OPB_INC . 'session.php');
    require(OPB_INC . 'functions.php');
    require(OPB_INC . 'display.php');
    require(OPB_INC . 'error.php');
    require(OPB_INC . 'routers.php');
    require(OPB_INC . 'template.extensions.php');
    require(OPB_INC . 'request.php');
    require(OPB_INC . 'classes.php');
    require(OPB_INC . 'main.php');
	
?>