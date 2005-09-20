<?php

   class OPBUsersToGroupsDAO extends OPBDAO
   {
      public function FindUsersByGroupID($iGroupID)
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_users_to_groups
                  WHERE gid = :gid'
         );
         $oStmt->bindParam(':gid', $iGroupID, PDO_PARAM_INT);

         if(!$oStmt->execute())
            return false;

         return $oStmt;
      }

      public function FindGroupsByUserID($iUserID)
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_users_to_groups
                  WHERE uid = :uid'
         );
         $oStmt->bindParam(':uid', $iUserID, PDO_PARAM_INT);

         if(!$oStmt->execute())
            return false;

         return $oStmt;
      }

      public function Insert($aRecord)
      {
         $oStmt = $this->db->prepare(
                  'INSERT INTO opb_users_to_groups ( gid, uid, priority  )
                  VALUES( :gid, :uid, :priority  )'
         );
         $oStmt->bindParam(':gid', $aRecord['gid'], PDO_PARAM_INT);
         $oStmt->bindParam(':uid', $aRecord['uid'], PDO_PARAM_INT);
         $oStmt->bindParam(':priority', $aRecord['priority'], PDO_PARAM_INT);

         if(!$oStmt->execute())
            return false;
         return true;
      }

      public function DeleteByGroupAndUserID($iGroupID, $iUserID)
      {
         $oStmt = $this->db->prepare(
                  'DELETE FROM opb_user_param_values
                  WHERE gid = :gid AND uid = :uid'
         );
         $oStmt->bindParam(':gid', $iGroupID, PDO_PARAM_INT);
         $oStmt->bindParam(':uid', $iUserID, PDO_PARAM_INT);
         if(!$oStmt->execute())
            return false;
         return true;
      }
   }
?>