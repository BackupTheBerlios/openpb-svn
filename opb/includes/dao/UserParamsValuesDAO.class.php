<?php

   class OPBUsersParamsValuesDAO extends OPBDAO
   {
      public function FindParamsByUserID($iUserID)
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_user_param_values
                  WHERE uid = :uid'
         );
         $oStmt->bindParam(':uid', $iUserID, PDO_PARAM_INT);

         if(!$oStmt->execute())
            return false;

         return $oStmt;  
      }

      public function Insert($aParam)
      {
         $oStmt = $this->db->prepare(
                  'INSERT INTO opb_user_param_values ( param_id, uid, value )
                  VALUES( :param_id, :uid, :value )'
         );
         $oStmt->bindParam(':param_id', $aParam['param_id'], PDO_PARAM_INT);
         $oStmt->bindParam(':uid', $aParam['uid'], PDO_PARAM_INT);
         $oStmt->bindParam(':value', $aParam['value']);

         if(!$oStmt->execute())
            return false;
         return true;
      }

      public function Update($aParam)
      {
         $oStmt = $this->db->prepare(
                  'UPDATE opb_user_param_values SET
                      value = :value
                  WHERE param_id = :param_id AND uid = :uid'
         );
         $oStmt->bindParam(':value', $aParam['value']);
         $oStmt->bindParam(':param_id', $aParam['param_id'], PDO_PARAM_INT);
         $oStmt->bindParam(':uid', $aParam['uid'], PDO_PARAM_INT);

         if(!$oStmt->execute())
            return false;
         return true;
      }

      public function DeleteByUserAndParamID($iParamID, $iUserID)
      {
         $oStmt = $this->db->prepare(
                  'DELETE FROM opb_user_param_values
                  WHERE param_id = :param_id AND uid = :uid'
         );
         $oStmt->bindParam(':param_id', $iParamID, PDO_PARAM_INT);
         $oStmt->bindParam(':uid', $iUserID, PDO_PARAM_INT);
         if(!$oStmt->execute())
            return false;
         return true;
      }
   }
?>