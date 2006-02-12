<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                          Open Power Forms                              //
  //         Copyright (c) 2005 OpenPB team, http://www.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //

	class opfException extends Exception{}
	
	
	function opfErrorHandler(opfException $exc)
	{
		echo '<p><b>Open Power Forms error #'.$exc->getCode().': </b> '.$exc->getMessage().'</p>';
	} // end opfErrorHandler();

?>
