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

	final class opbDaoManager
	{		
		private static $aServices = array();
		private static $oInstance = null;
		
		private $aRegisterServices = array();
		
		public static function getInstance()
		{
			if( self::$oInstance == null )
			{
				$OPB = OPB::getInstance();
				$sCalssName = __CLASS__;
				self::$oInstance = new $sCalssName( $OPB->services );
			}
			return self::$oInstance;
		}
		
		public function __construct( $aServices )
		{
			// add DAO services for use
			self::$aServices = (array)$aServices;
		} // end __construct();
		
		public function load( $sPackage )
		{
			$aInf = explode( '.', $sPackage, 2 );
			if( in_array( $aInf[0], $this->aRegisterServices ) == true )
			{
				return true;
			}
			if( isset( self::$aServices[$aInf[0]] ) )
			{
				$this->aRegisterServices[] = $aInf[0];
				// load file for current service
				if( !defined( 'OPB_AUTOLOADER_WORK' ) || OPB_AUTOLOADER_WORK == false )
				{
					include_once( OPB_DAO . $aInf[0] . '.dao.php' );
					include_once( OPB_DAO . 'managers/' . $aInf[0] . 'Manager.dao.php' );
				}
				// load subservices
				if( $aInf[1] == 'all' )
				{
					foreach( new ArrayObject( self::$aServices[$aInf[0]] ) as $sSubservice )
					{
						if( !$this->load( $sSubservice . '.all' ) )
						{
							return false;
						}
					}
				}
				elseif( $aInf[1] == 'none' )
				{
					return true;
				}
				else
				{
					if( !$this->load( $aInf[1] . '.none' ) )
					{
						return false;
					}
				}
			}
			else
			{
				return false;
			}
		} // end load();			
	}
	
	interface opbDaoService
	{
		public function __get( $sProperty );
		public function __set( $sProperty, $mValue );
		public function clear();
	}
	
	interface opbDaoServiceManager
	{
		public static function create( opbDaoService $oItem );
		public static function edit( opbDaoService $oItem );
		public static function move( opbDaoService $oItem );
		public static function remove( opbDaoService $oItem );
		public function clear();
	}
	
?>