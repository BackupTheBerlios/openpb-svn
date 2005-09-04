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

	class opbIndex extends opbModule
	{
		public function run()
		{
			$opb = OPB::getInstance();
			$opb -> lang -> load('index');
			
			$tpl = opbTemplate::getInstance();
			$tpl -> http_headers(OPT_HTML);
			
			$opb -> loadLibrary('pagination');
			
			$ps = new opbPagination(10, 2734, 'http://localhost/opb/opb/index.php', array());
			
			$tpl -> assign('page', $ps -> getCurrent());
			$tpl -> assign('total', $ps -> getTotal());
			$tpl -> assign('pages', $ps -> getLinks());
			
/*			$api = api::getInstance();
			$api -> import('forum');

			$forum_manager = new apiForumManager;
			$categories = array();
			$cid = -1;
			$forums = array();
			foreach($forum_manager as $forum)
			{
				if($forum -> level == 0)
				{
					$cid++;
					$categories[$cid] = array(
						'id' => $forum -> id,
						'title' => $forum -> title				
					);			
				}
				else
				{
					$forums[$cid][] = array(
						'id' => $forum -> id,
						'title' => $forum -> title,
						'description' => $forum -> description,
						'topics' => $forum -> topics,
						'posts' => $forum -> posts				
					);
				}
			}
			$tpl -> assign('categories', $categories);
			$tpl -> assign('forums', $forums);
*/
			$display = new opbDisplayBoard('indexmodule', 'index.tpl');
			$this -> display($display);
		} // end run();	
	}
?>
