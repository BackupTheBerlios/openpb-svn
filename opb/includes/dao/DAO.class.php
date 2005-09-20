<?php

   abstract class OPBDAO
   {
      protected $db = null;

      public function __construct()
      {
         $this->db = OPB::getInstance()->db;
      }
   }

?>