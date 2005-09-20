<?php

   class OPBUsersProfilesDAO extends OPBDAO
   {
      public function FindAll()
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM opb_user_param_names'
         );
         if(!$oStmt->execute())
            return false;

         return $oStmt;  
      }

      public function Insert($aParam)
      {
         $oStmt = $this->db->prepare(
                  'INSERT INTO opb_user_param_names ( id, list_order, name_source, name_value, description_value, value_type, default_value )
                  VALUES( NULL, :list_order, :name_source, :name_value, :description_value, :value_type, :default_value )'
         );
         $oStmt->bindParam(':list_order', $aParam['avatar_location'], PDO_PARAM_INT);
         $oStmt->bindParam(':name_source', $aParam['avatar_type'], PDO_PARAM_INT);
         $oStmt->bindParam(':name_value', $aParam['signature']);
         $oStmt->bindParam(':description_value', $aParam['notes']);
         $oStmt->bindParam(':value_type', $aParam['about'], PDO_PARAM_INT);
         $oStmt->bindParam(':default_value', $aParam['messengers']);

         if(!$oStmt->execute())
            return false;
         return true;
      }

      public function Update($aParam)
      {
         $oStmt = $this->db->prepare(
                  'UPDATE opb_user_param_names SET
                      list_order = :list_order,
                      name_source = :name_source,
                      name_value = :name_value,
                      description_value = :description_value,
                      value_type = :value_type,
                      default_value = :default_value
                  WHERE id = :id'
         );
         $oStmt->bindParam(':list_order', $aParam['list_order'], PDO_PARAM_INT);
         $oStmt->bindParam(':name_source', $aParam['name_source'], PDO_PARAM_INT);
         $oStmt->bindParam(':name_value', $aParam['name_value']);
         $oStmt->bindParam(':description_value', $aParam['description_value']);
         $oStmt->bindParam(':value_type', $aParam['value_type'], PDO_PARAM_INT);
         $oStmt->bindParam(':default_value', $aParam['default_value']);
         $oStmt->bindParam(':id', $aParam['id'], PDO_PARAM_INT);

         if(!$oStmt->execute())
            return false;
         return true;
      }

      public function DeleteByID($iID)
      {
         $oStmt = $this->db->prepare(
                  'DELETE FROM opb_user_param_names
                  WHERE id = :id'
         );
         $oStmt->bindParam(':id', $iID, PDO_PARAM_INT);
         if(!$oStmt->execute())
            return false;
         return true;
      }
   }
?>