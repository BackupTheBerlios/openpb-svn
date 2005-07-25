<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                        Open Power Template                             //
  //         Copyright (c) 2005 OpenPB team, http://opt.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //
  //
  // $Id$

	function opt_prefilter_cw(&$text, opt_template $opt)
	{
		$regex = array(
			'/[\s]+/',
			'/\> \</'		
		);
		$replacements = array(
			' ',
			''
		);
		return preg_replace($regex, $replacements, $text);
	} // end opt_prefilter_cw();
	
	function opt_postfilter_optimize($code, opt_template $opt)
	{
		$patterns = array(
			'/echo \'\s+\'\./',
			'/\.\'\s+\'\./',
			'/echo \'\s*\';/' 
		);
		$replacements = array(
			'echo ',
			'.\' \'.',
			''
		);
		return preg_replace($patterns, $replacements, $code);	
	} // opt_postfilter_optimize();
?>
