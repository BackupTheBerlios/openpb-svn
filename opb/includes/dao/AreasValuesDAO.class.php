<?
   class areasValuesDAO extends OPBDAO
   { 
      public function byForumID($iForumID)
      {
         $oStmt = $this->db->prepare(
                  "SELECT f.title,f.id,f.area_id,a.id,a.conf
                  FROM opb_forums as f LEFT JOIN opb_areas as a
                  ON f.area_id=a.id WHERE f.id = :fid"
         );
         $oStmt->bindParam(':fid', $iForumID, PDO_PARAM_INT);    
              
         if(!$oStmt->execute())
            return false;
         return $oStmt->fetch(PDO_FETCH_ASSOC);
      }
      
      public function byAreaID($iAreaID)
      {
         $oStmt = $this->db->prepare(
                  'SELECT `conf`
                  FROM `opb_areas`
                  WHERE `id` = :aid'
         );
         $oStmt->bindParam(':aid', $iAreaID, PDO_PARAM_INT);
         
         if(!$oStmt->execute())
            return false;
         return $oStmt->fetch(PDO_FETCH_ASSOC);
      }
      
      public function allValues($iAreaID)
      {
         $oStmt = $this->db->prepare(
                  'SELECT *
                  FROM `opb_areas`
                  WHERE `id` = :aid'
         );
         $oStmt->bindParam(':aid', $iAreaID, PDO_PARAM_INT);
         
         if(!$oStmt->execute())
            return false;
         return $oStmt->fetch(PDO_FETCH_ASSOC);
      }
   }
?>
