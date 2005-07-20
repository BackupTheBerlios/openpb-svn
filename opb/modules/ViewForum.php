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

	class opbViewForum extends opbModule
	{
		public function run()
		{
			$opb = OPB::getInstance();
			$tpl = opbTemplate::getInstance();
			$tpl -> http_headers(OPT_HTML);
			$opb -> lang -> load('viewforum');
			
			$api = api::getInstance();
			$api -> import('forum');
			if($opb -> request -> map('fid', OPB_GET, MAP_REQUIRED | MAP_INTEGER | MAP_GT, 0))
			{
				$forum = new apiForum($opb -> request -> fid);
				
				$tpl -> assign('f_id', $opb -> request -> fid);
				$tpl -> assign('f_title', $forum -> title);
				
				$topics = array();
				foreach($forum as $topic)
				{
					$topics[] = array(
						'id' => $topic->id,
						'title' => $topic->title,
						'description' => $topic->description,
						'author_id' => $topic->author_id,
						'author_name' => $topic -> starter_name			
					);		
				}
				$tpl -> assign('topics', $topics);
				$tpl -> assign('fid', $opb -> request -> fid);
				
				$display = new opbDisplayBoard('viewforummodule', 'viewforum.tpl');
				$this -> display($display);	
			}
		} // end run();	
	}
	
?>