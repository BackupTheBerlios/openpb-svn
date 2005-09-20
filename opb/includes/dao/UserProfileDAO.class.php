<?php

   class OPBUserProfileDAO extends OPBDAO
   {
      public function FindByUserID($iUserID)
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_user_profile
                  WHERE uid = :uid'
         );
         $oStmt->bindParam(':uid', $iUserID, PDO_PARAM_INT);
         if(!$oStmt->execute())
            return false;

         return $oStmt->fetch(PDO_FETCH_ASSOC);
      }

      public function Insert($aProfile)
      {
         $oStmt = $this->db->prepare(
                  'INSERT INTO opb_user_profile ( uid, last_modification, avatar_location, avatar_type, signature, notes, about, messengers, website, location, hobbys )
                  VALUES( NULL, :upid, :nick, :pass, :email, :joined, :last_visit, :last_activity, :login_key, :active )'
         );
         $oStmt->bindParam(':last_modification', $aProfile['last_modification'], PDO_PARAM_INT);
         $oStmt->bindParam(':avatar_location', $aProfile['avatar_location']);
         $oStmt->bindParam(':avatar_type', $aProfile['avatar_type'], PDO_PARAM_INT);
         $oStmt->bindParam(':signature', $aProfile['signature']);
         $oStmt->bindParam(':notes', $aProfile['notes']);
         $oStmt->bindParam(':about', $aProfile['about']);
         $oStmt->bindParam(':messengers', $aProfile['messengers']);
         $oStmt->bindParam(':location', $aProfile['location']);
         $oStmt->bindParam(':hobbys', $aProfile['hobbys']);

         if(!$oStmt->execute())
            return false;
         return true;
      }

      public function Update($aProfile)
      {
         $oStmt = $this->db->prepare(
                  'UPDATE opb_user_profile SET
                      last_modification = :last_modification,
                      avatar_location = :avatar_location,
                      avatar_type = :avatar_type,
                      signature = :signature,
                      notes = :notes,
                      messengers = :messengers,
                      location = :location,
                      hobbys = :hobbys
                  WHERE uid = :uid'
         );
         $oStmt->bindParam(':last_modification', $aProfile['last_modification'], PDO_PARAM_INT);
         $oStmt->bindParam(':avatar_location', $aProfile['avatar_location']);
         $oStmt->bindParam(':avatar_type', $aProfile['avatar_type'], PDO_PARAM_INT);
         $oStmt->bindParam(':signature', $aProfile['signature']);
         $oStmt->bindParam(':notes', $aProfile['notes']);
         $oStmt->bindParam(':messengers', $aProfile['messengers']);
         $oStmt->bindParam(':location', $aProfile['location']);
         $oStmt->bindParam(':hobbys', $aProfile['hobbys']);
         $oStmt->bindParam(':uid', $aProfile['uid'], PDO_PARAM_INT);

         if(!$oStmt->execute())
            return false;
         return true;
      }

      public function DeleteByUserID($iUserID)
      {
         $oStmt = $this->db->prepare(
                  'DELETE FROM opb_user_profile
                  WHERE uid = :uid'
         );
         $oStmt->bindParam(':uid', $iUserID, PDO_PARAM_INT);
         if(!$oStmt->execute())
            return false;
         return true;
      }
   }
?>