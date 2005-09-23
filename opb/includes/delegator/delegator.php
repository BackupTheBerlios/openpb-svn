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
 * @package OPB
 */
interface IHandler
{
    /**
     * Performs the processing of supplied data.
     *
     * @param  mixed $data
     * @return mixed
     */
    public function process($data);
}


/**
 * Delegator manages groups of handlers.
 *
 * @package OPB
 */
class Delegator implements IHandler
{
    /**
     * @var array
     */
    private $handlers = array();

    /**
     *
     *
     */
    public function __construct($task_name)
    {
        $stmt = $db->prepare(
           'SELECT h.handler_class FROM opb_handlers LEFT JOIN opb_task_handlers WHERE task_name = :name'
        );
        $stmt->execute(array(':name' => $task_name));
        while ($row = $stmt->fetch(PDO_FETCH_ASSOC))
        {
            $this->handlers[] = new $row['handler_class']();
        }
    }

    /**
     *
     *
     */
    public function process($data)
    {
        foreach ($this->handlers as $handler)
        {
            $data = $handler->$method($data);
        }
        return $data;
    }
}

?>