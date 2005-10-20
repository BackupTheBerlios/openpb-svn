<?php
  
/**
 * 
 *
 * @version $Id$
 * @copyright 2005 
 */

  
   #zarzadzanie aranami
   class areasDAO extends OPBDAO
   {
      
      public function insert($aRecord)
      {
         if(!is_array($aRecord['conf']))
            return false;     
         $conf = serialize($aRecord['conf']);                                   
         
         $oStmt = $this->db->prepare(
                "INSERT INTO `opb_areas` (`name` , `desc` , `poz` , `conf` ) 
                VALUES (:name, :des, :poz, :conf);");
         $oStmt->bindParam(":name",$aRecord['name']);
         $oStmt->bindParam(":des",$aRecord['des']);
         $oStmt->bindParam(":poz",$aRecord['poz'],PDO_PARAM_INT);
         $oStmt->bindParam(":conf",$conf);
         
         if(!$oStmt->execute())
            return false;
         return true;
      }
             
      public function delete($iAreaID)
      {
         $oStmt = $this->db->prepare(
                "DELETE FROM `opb_areas` 
                WHERE `id` = :id");
         $oStmt->bindParam(":id",$iAreaID,PDO_PARAM_INT);
         
         if(!$oStmt->execute())
            return false;
         return true;          
      }
      
      public function update($aRecord)
      {
         if(!is_array($aRecord['conf']))
            return false;     
         $conf = serialize($aRecord['conf']);                                   
         
         $oStmt = $this->db->prepare(
                "UPDATE `opb_areas` 
                SET `name` =  :name, `desc` = :des , `poz` = :poz , `conf` = :conf
                WHERE `id` = :id;");
         $oStmt->bindParam(":id",$aRecord['id'],PDO_PARAM_INT);
         $oStmt->bindParam(":name",$aRecord['name']);
         $oStmt->bindParam(":des",$aRecord['des']);
         $oStmt->bindParam(":poz",$aRecord['poz'],PDO_PARAM_INT);
         $oStmt->bindParam(":conf",$conf);
         
         if(!$oStmt->execute())
            return false;
         return true;
      }
      
      #szuka areny przez id forum
      public function findByForumID($iForumID)
      {
         $oStmt = $this->db->prepare(
                "SELECT `area_id`
                FROM `opb_forums`
                WHERE `id` = :id");    
         $oStmt->bindParam(":id",$iForumID,PDO_PARAM_INT);
         
         if(!$oStmt->execute())
            return false;
         return $oStmt->fetch(PDO_FETCH_ASSOC);
      }
          
      /**
      I don't know if it is good idea to place singing method in areasDAO,
      but there is no DAO for forum and something has to support signing 
      area to forum.
      
      To unsign area from forum type 0 as $iAreaID
       */
      public function assignArea($iForumID,$iAreaID)
      {
         $oStmt = $this->db->prepare(
                "UPDATE `opb_forums`
                SET `area_id` = :aid
                WHERE `id` = :fid;");  
         $oStmt->bindParam(":aid",$iAreaID,PDO_PARAM_INT); 
         $oStmt->bindParam(":fid",$iForumID,PDO_PARAM_INT);  
         
         if(!$oStmt->execute())
            return false;
         return true;       
      }
   }


   #wszystko co zwiazane z pobieraniem danych
   class areasValuesDAO extends OPBDAO
   { 
      public function byForumID($iForumID)
      {
      }
      
      public function byAreaID($iAreaID)
      {
      }
   }

?>