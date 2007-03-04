<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                          Open Power Forms                              //
  //         Copyright (c) 2007 OpenPB team, http://www.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //

	class opfException extends Exception
	{
		private $opf;
	
		public function __construct($message = null, $code = 0, opfClass $opf)
		{
			$this -> message = $message;
			$this -> code = $code;
			$this -> opf = $opf;		
		} // end __construct();
		
		public function getOpf()
		{
			return $this -> opf;
		} // end getOpf();	
	}	
	
	function opfErrorHandler(opfException $exc)
	{
		echo '<div class="opf"><b>Open Power Forms error #'.$exc->getCode().': </b> '.$exc->getMessage().'</div>';
		
		echo '<div class="opfDebug"><h3>Open Power Forms error</h3><p><table style="width: 50%; border: 1px solid #000000;">';
		echo '<tr>
			<td style="width: 30%; background: #DDDDDD; font-weight: bold;">Title</td>
			<td style="width: 30%; background: #DDDDDD; font-weight: bold;">Value</td>
		</tr>';
		echo '<tr>
			<td>Code</td>
			<td>'.$exc->getCode().'</td>
		</tr>';
		echo '<tr>
			<td>Message</td>
			<td>'.$exc->getMessage().'</td>
		</tr>';
		echo '<tr>
			<td>Request method</td>
			<td>'.($opf -> getVisit() -> requestMethod == OPF_POST ? 'POST' : 'GET').'</td>
		</tr>';
		echo '<tr>
			<td>Request address</td>
			<td>'.$opf -> getVisit() -> currentPath.'</td>
		</tr>';
		echo '<tr>
			<td>Cookies enabled</td>
			<td>'.$opf -> getVisit() -> cookiesEnabled.'</td>
		</tr>';
		echo '</table></p><p>Open Power Forms '.OPF_VERSION.'</p></div>';
	} // end opfErrorHandler();

?>
