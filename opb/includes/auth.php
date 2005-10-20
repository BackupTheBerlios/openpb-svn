<?php


    /**
     * OpenPowerBoard
     *
     * Autoryzacja
     */
    
    class Authorization {
        /**
         * Tablica uzytkownika z jego prawami
         *
         * @var array
         */
        protected $aGroup = array();
        /**
         * Przygotowana tablica do uzywania w autoryzacji
         *
         * @var array
         */
        protected $aCombinePerms = array();
        /**
         * Czy zosta³a wygenerowana tablica z prawami do uzytkownika
         *
         * @var bool
         */
        protected $bCombineParms = false;
        
        /**
         * Konstruktor
         *
         */
        public function __construct() {}
        
        /**
         * Przygotowuje podane prawa do uzywania przez autoryzacje
         *
         */
        public function PermsCombine() {
            $aCombinePerms = array(
                'global' => array(),
                'local'  => array()
            );
            
            //print_r( $this->aGroup );

            
            foreach( $this->aGroup As $k => $aAuth ) {
                // GLOBALNE
                foreach( $aAuth['global'] As $sPerm => $iStatus ) {
                    if( array_key_exists( $sPerm, $aCombinePerms['global'] ) ) {
                        if( $iStatus == 1 ) {
                            $aCombinePerms['global'][$sPerm] = 1;
                        }
                        else {
                            if( $aCombinePerms['global'][$sPerm] == 1 ) {
                                $aCombinePerms['global'][$sPerm] = 1;
                            }
                            else {
                                $aCombinePerms['global'][$sPerm] = 0;
                            }
                        }
                    }
                    else {
                        $aCombinePerms['global'][$sPerm] = $iStatus;
                    }
                }
                
                //print_r( $aAuth['local'] );
                // LOKALNE
                foreach( $aAuth['local'] As $iForumId => $aPerms ) {
                    /*
                    if( array_key_exists( $iForumId, $aCombinePerms['local'] ) ) {
                        foreach( $aPerms As $sPerms => $iStatus ) {

                        }
                    }
                    else {
                        // nie istniej mozna je zapisac
                        $aCombinePerms['local'][$iForumId] = $aPerms;
                    }
                    */
                    if( array_key_exists( $iForumId, $aCombinePerms['local'] ) ) {
                        
                        foreach( $aPerms As $sPermName => $iPermStatus ) {
                            if( $iPermStatus == true ) {
                                $aCombinePerms['local'][$iForumId][$sPermName] = 1;
                            }
                        }
                        //print_r( $iForumId );
                        //print_r( $aPerms );
                    }
                    else {
                        $aCombinePerms['local'][$iForumId] = $aPerms;
                    }

                }
            }
            
            $this->bCombinePerms = true;
            $this->aCombinePerms = & $aCombinePerms;
        }
        
        /**
         * Zwraca przygotowana tablice do
         *
         * @return array
         */
        
        public function getCombinePerms() {
            if( $this->bCombinePerms === false ) {
                $this->PermsCombine();
            }
            
            return $this->aCombinePerms;
        }
        
        /**
         * Ustawienie tablicy z autoryzacja
         *
         * @param array
         */
        public function setCombinePerms( $aCombinePerms ) {
            $this->aCombinePerms = $aCombinePerms;
        }   
        
        /**
         * Sprawdza autoryzacje
         *
         * Brak pomyslu na nazwe metody dlatego taka (testPerms) - skrot
         *
         * @param int $iModuleId
         * @param string $sAccess
         * @return bool
         */
        public function tp( $iModuleId, $sAccess ) {
            if( @$this->aCombinePerms['global'][$sAccess] == 1 ) {
                return true;
            }
            
            if( @$this->aCombinePerms['local'][$iModuleId][$sAccess] == 1 ) {
                return true;
            }

            return false;
        }
        
        /**
         * Ustawienie grup usera
         *
         * @param array $aGroups
         */
        public function setGroups( $aGroups ) {
            // array_change_key_case
            $this->aGroup = $aGroups;
        }
    }
    
    /*
   /* SPOSOB UZYCIA *
    
    
    echo '<pre>';
    
    /* NOWY OBIEKT *
    $auth = new Authorization();
    
    
    /* PRZYKLADOWE GRUPBY *wyciagane z bazy danych *
    // --- GRUPY
    $Modzi = array(
        'global' => array( "read" => 0, "write" => 0 ),
        
        'local' => array(
            1 => array( "read" => 1, "write" => 1 ),
            4 => array( "read" => 1, "write" => 0 ),
        )
    );
    
    $Admin = array(
        'global' => array( 'read' => 1, 'write' => 1 ),
        'local' => array(
            1 => array( "read" => 0, "write" => 0 ),
            5 => array( "read" => 1, "write" => 0 ),
        )
    );
    
    
    /* Ustawienie grup jakie ma uzytkownik *
    $auth->setGroups( array( $Modzi, $Admin ) );
    
    /** Wymieszanie praw *
    $auth->PermsCombine();
    
    /**  Zwraca "wymieszane" prawa, mozna to dac do cache dzieki temu autoryzacja znacznie zyska na peredkosci *
    //$auth->getCombinePerms();
    
    // NR FORUM (id FORUM)
    $Mid   = 2;     // ID MODULU
    // CO NAM trzeba :) czyli jaka akcja
    $Prawa = 'read'; // PRAWO jakie potrzebne jest
    
    
    // SPRAWDZENIE CZY JEST OK
    if( $auth->tp( $Mid, $Prawa ) ) {
        echo 'ma';
    }
    else {
        echo 'nie ma';
    }
	//*/

?> 