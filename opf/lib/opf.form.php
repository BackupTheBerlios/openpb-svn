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

	define('OPF_STATE_RENDER', 1);
	define('OPF_STATE_VALIDATE', 2);
	define('OPF_STATE_STEP_VALIDATE', 3);
	
	class opfShowFormException extends Exception
	{
		private $mode;
	
		public function __construct($mode = 1)
		{
			parent::__construct();
			$this -> mode = $mode;
		} // end __construct();
		
		public function invalidData()
		{
			return $this -> mode;
		} // end invalidData();	
	} // end opfShowFormException;


	abstract class opfVirtualForm
	{
		protected $context;



	}

?>
