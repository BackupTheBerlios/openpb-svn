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
 * Open Power Driver
 *
 *
 */
class OPD extends PDO
{
    /**
     * @var OPD
     */
    private static $instance = null;
    
    /**
     *
     *
     * @return OPD
     */
    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            throw new Exception('OPD not initialized!');
        }
        
        return self::$instance;
    }
    
    /**
     *
     *
     * @return OPDStatement
     */
    public function query($sql, $fetch_mode = PDO_FETCH_ASSOC)
    {
        $stmt = parent::prepare($sql, array(PDO_ATTR_STATEMENT_CLASS => array('OPDStatement')));
        $stmt->setFetchMode($fetch_mode);
        $stmt->execute();
        return $stmt;
    }
    
    /**
     *
     *
     * @return OPDStatement
     */
    public function prepare($sql)
    {
        return parent::prepare($sql, array(PDO_ATTR_STATEMENT_CLASS => array('OPDStatement')));
    }
}

/**
 *
 *
 *
 *
 */
class OPDStatement extends PDOStatement
{

}

?>