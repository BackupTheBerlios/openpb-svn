<?php

   class OPBUsersDAO extends OPBDAO
   {
      public function FindByID($iID)
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_users
                  WHERE uid = :uid'
         );
         $oStmt->bindParam(':uid', $iID, PDO_PARAM_INT);
         if(!$oStmt->execute())
            return false;

         return $oStmt->fetch(PDO_FETCH_ASSOC);
      }

      public function FindByNick($sNick)
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_users
                  WHERE nick = :nick'
         );
         $oStmt->bindParam(':nick', $sNick);
         if(!$oStmt->execute())
            return false;

         return $oStmt->fetch(PDO_FETCH_ASSOC);
      }

      public function FindByLogin($sLogin)
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_users
                  WHERE login = :login'
         );
         $oStmt->bindParam(':login', $sLogin);
         if(!$oStmt->execute())
            return false;

         return $oStmt->fetch(PDO_FETCH_ASSOC);
      }

      public function FindAll()
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_users'
         );
         if(!$oStmt->execute())
            return false;
         return $oStmt;
      }

      public function FindAllActive()
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_users
                  WHERE active = 1'
         );
         if(!$oStmt->execute())
            return false;

         return $oStmt;
      }

      public function FindAllDeactive()
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_users
                  WHERE active = 0'
         );
         if(!$oStmt->execute())
            return false;

         return $oStmt;
      }

      public function GetUserRolesByForumID($iUserID, $iForumID)
      {
         $oStmt = $this->db->prepare(
                  'SELECT gr.gid, gr.access, fo.forum_id, fo.perms
                  FROM opb_groups AS gr, opb_forum_perms AS fo
                  WHERE gr.gid = fo.gid IN(
                     SELECT gid
                     FROM opb_users_to_groups
                     WHERE uid = :uid )
                  HAVING fo.forum_id = :fid
                  ORDER BY gr.gid'
         );
         $oStmt->bindParam(':uid', $iUserID, PDO_PARAM_INT);
         $oStmt->bindParam(':fid', $iForumID, PDO_PARAM_INT);
         if(!$oStmt->execute())
            return false;
         return $oStmt->fetch(PDO_FETCH_ASSOC);
      }

      public function Insert($aUser)
      {
         $aUser['pass'] = sha1($aUser['pass']);
         $oItem->login_key = sha1($aUser['pass'] . $aUser['email'] . time());

         $oStmt = $this->db->prepare(
                  'INSERT INTO opb_users ( uid, user_profile_uid, nick, pass, email, joined, last_visit, last_activity, login_key, active )
                  VALUES( NULL, :upid, :nick, :pass, :email, :joined, :last_visit, :last_activity, :login_key, :active )'
         );
         $oStmt->bindParam(':upid', $aUser['user_profile_uid'], PDO_PARAM_INT);
         $oStmt->bindParam(':nick', $aUser['nick']);
         $oStmt->bindParam(':pass', $aUser['pass']);
         $oStmt->bindParam(':email', $aUser['email']);
         $oStmt->bindParam(':joined', $aUser['joined'], PDO_PARAM_INT);
         $oStmt->bindParam(':last_visit', $aUser['last_visit'], PDO_PARAM_INT);
         $oStmt->bindParam(':last_activity', $aUser['last_activity'], PDO_PARAM_INT);
         $oStmt->bindParam(':login_key', $aUser['login_key']);
         $oStmt->bindParam(':active', $aUser['acitve'], PDO_PARAM_INT);

         if(!$oStmt->execute())
            return false;
         return true;
      }

      public function Update($aUser)
      {
         $oStmt = $this->db->prepare(
                  'UPDATE opb_users SET
                      user_profile_uid = :user_profile_uid,
                      nick = :nick,
                      pass = :pass,
                      email = :email,
                      joined = :joined,
                      last_visit = :last_visit,
                      last_activity = :last_activity,
                      login_key = :login_key,
                      active = :active
                  WHERE uid = :uid'
         );
         $oStmt->bindParam(':upid', $aUser['user_profile_uid'], PDO_PARAM_INT);
         $oStmt->bindParam(':nick', $aUser['nick']);
         $oStmt->bindParam(':pass', $aUser['pass']);
         $oStmt->bindParam(':email', $aUser['email']);
         $oStmt->bindParam(':joined', $aUser['joined'], PDO_PARAM_INT);
         $oStmt->bindParam(':last_visit', $aUser['last_visit'], PDO_PARAM_INT);
         $oStmt->bindParam(':last_activity', $aUser['last_activity'], PDO_PARAM_INT);
         $oStmt->bindParam(':login_key', $aUser['login_key']);
         $oStmt->bindParam(':active', $aUser['acitve'], PDO_PARAM_INT);
         $oStmt->bindParam(':uid', $aUser['uid'], PDO_PARAM_INT);

         if(!$oStmt->execute())
            return false;
         return true;
      }

      public function DeleteByID($iID)
      {
         $oStmt = $this->db->prepare(
                  'DELETE FROM opb_users
                  WHERE uid = :uid'
         );
         $oStmt->bindParam(':uid', $iID, PDO_PARAM_INT);
         if(!$oStmt->execute())
            return false;
         return true;
      }
   }
?>