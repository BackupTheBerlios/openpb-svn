<?php

   class OPBForumPermsDAO extends OPBDAO
   {
      public function FindAll()
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_forum_perms'
         );

         if(!$oStmt->execute())
            return false;

         return $oStmt;
      }

      public function FindByGroupID($iGroupID)
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_forum_perms
                  WHERE gid = :gid'
         );
         $oStmt->bindParam(':gid', $iGroupID, PDO_PARAM_INT);

         if(!$oStmt->execute())
            return false;

         return $oStmt;
      }

      public function FindByForumID($iForumID)
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_forum_perms
                  WHERE forum_id = :forum_id'
         );
         $oStmt->bindParam(':forum_id', $iForumID, PDO_PARAM_INT);

         if(!$oStmt->execute())
            return false;

         return $oStmt;
      }

      public function Insert($aRecord)
      {
         $oStmt = $this->db->prepare(
                  'INSERT INTO opb_forum_perms ( gid, forum_id, perms  )
                  VALUES( :gid, :forum_id, :perms  )'
         );
         $oStmt->bindParam(':gid', $aRecord['gid'], PDO_PARAM_INT);
         $oStmt->bindParam(':forum_id', $aRecord['forum_id'], PDO_PARAM_INT);
         $oStmt->bindParam(':perms', $aRecord['perms']);

         if(!$oStmt->execute())
            return false;
         return true;
      }

      public function DeleteByGroupAndForumID($iGroupID, $iForumID)
      {
         $oStmt = $this->db->prepare(
                  'DELETE FROM opb_user_param_values
                  WHERE gid = :gid AND forum_id = :forum_id'
         );
         $oStmt->bindParam(':gid', $iGroupID, PDO_PARAM_INT);
         $oStmt->bindParam(':forum_id', $iForumID, PDO_PARAM_INT);
         if(!$oStmt->execute())
            return false;
         return true;
      }
   }
?>