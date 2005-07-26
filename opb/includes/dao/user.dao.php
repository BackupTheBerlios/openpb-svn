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

	define( 'GROUP_BANNED', 0 );
	define( 'GROUP_PENDING', 1 );
	define( 'GROUP_GUEST', 2 );
	define( 'GROUP_USER', 3 );
	define( 'GROUP_NORMAL', 4 );
	define( 'GROUP_MODERATOR', 5 );
	define( 'GROUP_ADMINISTRATOR', 6 );
	
	define( 'USER_MODE_BASIC', 0 );
	define( 'USER_MODE_STD', 1 );
	define( 'USER_MODE_REG', 2 );
	define( 'USER_GUEST_ID', 1 );
	
	final class opbDaoUser implements opbDaoService, IteratorAggregate 
	{
		private $aProfile = array();
		
		public function __construct( $iUserId, $iMode, $aUserData = null )
		{
			if( isset( $aUserData ) ) {
				$this->aProfile = (array)$aUserData;
				return true;
			}
			$oDatabase = OPD::getInstance();
			// todo: table name as constant and rebulid users table
			switch( $iMode ) 
			{
				case USER_MODE_BASIC:
					$oStmt = $oDatabase->prepare( 
						'SELECT uid, nick, email 
						FROM opb_users 
						WHERE uid = :uid'
					);
					break;
				case USER_MODE_STD:
					$oStmt = $oDatabase->prepare( 
						'SELECT * 
						FROM opb_users 
						WHERE uid = :uid'
					);
					break;
				case USER_MODE_REG:
					$oStmt = $oDatabase->prepare( 
						'SELECT uid, nick, pass, email, joined, last_visit, last_activity, login_key, active 
						FROM opb_users 
						WHERE uid = :uid'
					);
					break;
			}
			$oStmt->bindParam( ':uid', $iUserId, PDO_PARAM_INT );
			if( !$oStmt->execute() )
        	{
	            return false;
        	}	
			$this->aProfile = $oStmt->fetch( PDO_FETCH_ASSOC );
		} // end __construct();
		
		public function __get( $sProperty )
		{
			if( isset( $this->aProfile[$sProperty] ) )
			{
				return $this->aProfile[$sProperty];
			}
			return null;
		} // end __get();
		
		public function __set( $sProperty, $mValue )
		{
			if( isset( $this->aProfile[$sProperty] ) )
			{
				// todo: better control data
				$this->aProfile[$sProperty] = addslashes( $mValue );
				return true;
			}
			return false;
		} // end __set();
		
		public function clear()
		{
			$this->aProfile = array();
		} // end clear();
		
		public function getUserRoles( $iForumID )
		{
			$oDatabase = OPD::getInstance();
			$oStmt = $oDatabase->prepare(
				'SELECT gr.gid, gr.access, fo.forum_id, fo.perms 
				FROM opb_groups AS gr, opb_forum_perms AS fo 
				WHERE gr.gid = fo.gid IN( 
					SELECT gid 
					FROM opb_users_to_groups 
					WHERE uid = :uid ) 
				HAVING fo.forum_id = :fid 
				ORDER BY gr.gid'
			);
			$oStmt->bindParam( ':uid', $this->aProfile['uid'], PDO_PARAM_INT );
			$oStmt->bindParam( ':fid', $iForumID, PDO_PARAM_INT );
			if( !$oStmt->execute() )
        	{
	            return false;
        	}			
			return $oStmt->fetch( PDO_FETCH_ASSOC );
		} // end getUserRoles();
		
		public function getIterator()
		{
			return new ArrayObject( $this->aProfile );
		} // end getInterator();
	}
		
?>