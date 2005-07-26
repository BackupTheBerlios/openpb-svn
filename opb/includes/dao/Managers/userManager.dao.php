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

	final class opbDaoUserManager implements opbDaoServiceManager, Iterator
	{
		private $aUsers;
		private $iKey;
		private $bValid;
		
		public static function create( opbDaoService $oItem )
		{
			// not good work, execudetd generate sql errors, todo: repair
			$oDatabase = OPD::getInstance();
			$oItem->uid = null;
			$oItem->pass = sha1( $oItem->pass );
			$oItem->login_key = sha1( $oItem->pass . $oItem->email . time() );
			
			$oStmt = $oDatabase->prepare(
				'INSERT INTO opb_users ( uid, user_profile_uid, nick, pass, email, joined, last_visit, last_activity, login_key, active ) 
				VALUES( :uid, :upid, :nick, :pass, :email, :joined, :last_visit, :last_activity, :login_key, :active )'
			);
			$oStmt->bindParam( ':uid', $oItem->uid, PDO_PARAM_NULL );
			$oStmt->bindParam( ':upid', $oItem->user_profile_uid, PDO_PARAM_INT );
			$oStmt->bindParam( ':nick', $oItem->nick );
			$oStmt->bindParam( ':pass', $oItem->pass );
			$oStmt->bindParam( ':email', $oItem->email );
			$oStmt->bindParam( ':joined', $oItem->joined, PDO_PARAM_INT );
			$oStmt->bindParam( ':last_visit', $oItem->last_visit, PDO_PARAM_INT );
			$oStmt->bindParam( ':last_activity', $oItem->last_activity, PDO_PARAM_INT );
			$oStmt->bindParam( ':login_key', $oItem->login_key );
			$oStmt->bindParam( ':active', $oItem->acitve, PDO_PARAM_INT );
			
			if( !$oStmt->execute() )
        	{
	            return false;
        	}
			else
			{
				if( $oItem->acitve == 0 )
				{
					//send mail to user, not implement jet.
					return true;
				}
				return true;
			}
		} // end create();
		
		public static function edit( opbDaoService $oItem )
		{
			$oDatabase = OPD::getInstance();
			$oItem->uid;
			$sQuery = 'UPDATE opb_users SET ';
			foreach( $oItem as $sProperty => $mValue )
			{
				if( $sProperty !== 'uid' )
				{
					$sQuery .= "$sProperty = '$mValue', ";
				}
			}
			$sQuery .= "WHERE uid = $oItem->uid";
			$sQuery = str_replace( ', W', ' W', $sQuery );
			$oStmt = $oDatabase->prepare( $sQuery );
			
			if( !$oStmt->execute() )
        	{
	            return false;
        	}
			return true;
		} // end edit();
		
		public static function move( opbDaoService $oItem )
		{
			// we can move users?
			// not implement jet.
		} // end move();
		
		public static function remove( opbDaoService $oItem )
		{
			$oDatabase = OPD::getInstance();
			// todo: delete from other tables and table name as constant
			$oStmt = $oDatabase->prepare(
				'DELETE FROM opb_users 
				WHERE uid = :uid'
			);
			$oStmt->bindParam( ':uid', $oItem->uid, PDO_PARAM_INT );
			
			if( !$oStmt->execute() )
        	{
	            return false;
        	}
        	unset( $oItem );
			return true;
		} // end remove();
		
		public function clear()
		{
			$this->aUsers = array();
		} // end clear();
		
		public function __construct()
		{
			$oDatabase = OPD::getInstance();
			// todo: table name as constant
			$oStmt = $oDatabase->prepare(
				'SELECT * FROM opb_users'
			);

			if( !$oStmt->execute() )
        	{
	            return false;
        	}
			$this->aUsers = $oStmt->fetchAll( PDO_FETCH_ASSOC );
		} // end __construct();
		
		public function rewind()
		{
			$this->iKey = 0;
			$this->bValid = isset( $this->aUsers[$this->iKey] ) != false ? true : false;
		} // end rewind();

		public function current()
		{
			return $this->aUsers[$this->iKey];
		} // end current();
		
		public function key()
		{
			return $this->iKey;	
		} // end key();
		
		public function next()
		{
			$this->iKey++;
			$this->bValid = isset( $this->aUsers[$this->iKey] ) != false ? true : false;
		} // end next();
	
		public function valid()
		{
			return $this->bValid;	
		} // end valid();	
	}

?>