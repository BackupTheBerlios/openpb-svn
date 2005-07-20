<?php
// +----------------------------------------------------------------------+
// | Open Power Board                                                     |
// | Copyright (c) 2005 OpenPB team, http://www.openpb.net/               |
// +----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify |
// | it under the terms of the GNU General Public License as published by |
// | the Free Software Foundation; either version 2 of the License, or    |
// | (at your option) any later version.                                  |
// +----------------------------------------------------------------------+
//
// $Id$

/**
 * Session Class
 * 
 * @package OPB
 */
class opbSession 
{
    /**
     * @var string $sessionId
     */
    protected $sessionId = '';
    
    /**
     * User name
     * 
     * @var string
     */
    protected $sessionUser = '';
    
    /**
     * User ID
     *
     * @var int
     */
    protected $sessionUserId = '';
    
    /**
     * @var string
     */
    protected $sessionIp = '';
    
    /**
     * @var string $sessionBrowser
     */
    protected $sessionBrowser = '';
    
    /**
     * Session start time
     * 
     * @var int
     */
    protected $sessionTime = 0;
    
    /**
     * Session duration time
     * 
     * @var int
     */
    protected $sessionDuration = 360;
    
    /**
     * 0 - user, 1 - bot, 2 - spam
     *
     * @var int
     */
    protected $sessionType = 0;
    
    /**
     * Search bor name (of $sessionType = 0 )
     * 
     * @var string
     */
    protected $sessionBotName = '';

    /**
     * Czy user ma slad po sessji (cookie, get), jej id
     * 
     * @access protected 
     * @author hwao 
     * @var bool $sessionExists
     */ 
    // protected $sessionExists = false;
    
    /**
     * Czy ma zwracac w url'u
     * 
     * @var bool 
     */
    protected $sessionReturnInUrl = true;
    
	/**
	 * @var OPD
	 */
    protected $db = false;
	    
    /**
     * @var opbRequest
     */
    protected $request = false;
    
    /**
     * Klucz do $_COOKIE gdzie trzymane jest id sessji (sta�a)
     * 
     * @var string
     */
    const keyCookie = 'OPB_SessionId';
    
    /**
     * Klucz do $_GET gdzie trzymane jest id sessji (sta�a)
     * 
     * @var string Session::keyGet
     */
    const keyGet = 'sessionId';
    
    /**
     * Dlugosc klucza id sessji (sta�a)
     * 
     * @var string Session::keyLen
     */
    const keyLen = 32;

    /**
     * Class
     * 
     * @return void 
     */
    public function __construct()
    {
        $opb = OPB::getInstance();

        $this->request = $opb->request;
        $this->db      = $opb->db;
        
        $this->sessionIp      = $this->getIp();
        $this->sessionBrowser = $this->getBrowser();
        $this->sessionTime    = time();
        $this->sessionId      = $this->getSessionId();
		
        $restore = $this->restoreSession();
		if ($restore === false)
		{
            // new session
            $this->sessionId          = $this->genSessionId();
			$this->sessionReturnInUrl = false;
            
			$this->request->removeCookie(self::keyCookie);
            
            $this->sessionType = $this->testWebBot();
            if ($this->sessionType == 2) 
			{
			    echo('HTTP/1.0 404 Not Found');
			}
			
            $this->createSession();
        } 
        else 
        {
            // Stara sessja
			if ($restore['session_type'] == 2) 
			{
			    echo("HTTP/1.0 404 Not Found");
			}
            
            $this->sessionUser = $restore['session_user'];
            $this->sessionUserId   = $restore['session_user_id']; 
        } 
        $this->request->updateCookie(self::keyCookie, $this->sessionId, 3600); 
    } 
    
    /**
     * Restores session from database (if session exists).
     * 
     * @author Bora 
     * @return void 
     */
    protected function restoreSession()
    {
        if (!$this->sessionId) 
        {
            return false;
        }

        $stmt = $this->db->prepare(
            'SELECT * FROM opb_session
             WHERE session_id = :sid 
               AND session_time > :stime 
               AND session_browser = :sbrowser 
               AND session_ip = :sIP'
        );
        
        $stime = time() - $this->sessionDuration;
        
        $stmt->bindParam(':sid', $this->sessionId);
        $stmt->bindParam(':stime', $stime, PDO_PARAM_INT);
        $stmt->bindParam(':sbrowser', $this->sessionBrowser);
        $stmt->bindParam(':sIP', $this->sessionIp);
            
        $stmt->execute();

        if (!$stmt->rowCount())
        {
            return false;
        } 
        
        return $stmt->fetch(PDO_FETCH_ASSOC);
    } 

    /**
     * createSession()
     * 
     * Sessja nie istnieje musi zostac stworzona kod wydobywa informacje jakie trzeba
     * nastepnie formatuje podane query zapisuje dane do bazy a informacje jakie uzyskal
     * upowszechnia (czytam koncowke komentarza z Session::loadSession)
     * 
     * @return void 
     */
    protected function createSession()
    {
        $stmt = $this->db->prepare(
            'INSERT INTO opb_session 
                 (session_id, session_type, session_time, session_browser, session_ip) 
             VALUES (:sid, :stype, :stime, :sbrowser, :sIP'
        );
        
        $stmt->bindParam(':sid', $this->sessionId);
        $stmt->bindParam(':stype', $this->sessionType);
        $stmt->bindParam(':stime', time(), PDO_PARAM_INT);
        $stmt->bindParam(':sbrowser', $this->sessionBrowser);
        $stmt->bindParam(':sIP', $this->sessionIp);
        
        $stmt->execute();
    } 

    /**
     * writeSession()
     * 
     * Przedłużenie sesji.
     * 
     * @todo Dodac multizapytania pod bazy to zezwalajace
     * @return void 
     */
    public function writeSession()
    {
        $stmt = $this->db->prepare(
            'UPDATE opb_session 
             SET session_time = :stime, 
                 session_user = :suser, 
                 session_user_id = :suserid 
             WHERE session_id = :sid'
        );
        
        $stmt->bindParam(':stime', time(), PDO_PARAM_INT);
        $stmt->bindParam(':suser', $this->sessionUser);
        $stmt->bindParam(':suserid', $this->sessionUserId);
        $stmt->bindParam(':sid', $this->sessionId);    
    } 
    
    public function removeOld()
    {
        $stmt = $this->db->prepare(
            'DELETE FROM opb_session WHERE session_time < :stime'
        );
        $stime = time() - $this->sessionDuration;
        $stmt->execute(array(':stime' => $stime)); 
    }

    /**
     * testWebBot()
     * 
     * Probuje rozpoznac czy dana sessja to bedzie uzytkownik (czlowiek)
     * czy tez jakis internetowy bot...
     * 
     * @author hwao 
     * @access protected 
     * @return int 
     */
    protected function testWebBot()
    {
        // Sciezka
        $arrBotList = parse_ini_file('./settings/bots.ini.php', true); 
        
        foreach($arrBotList['robots'] as $k => $v) 
        {
            // preg_match jest tutaj zbedne skorro wystarczy zwylke strstr
            // var_dump($k, $v, stristr($k, $this->sessionBrowser));
            if (stristr($k, $this->sessionBrowser)) 
            {
                $this->sessionBotName = $k; 
                //echo "bot $v<br>\n";
                return 1;
            } 
        } 
        
		foreach($arrBotList['spam'] as $k => $v) 
		{
            // preg_match jest tutaj zb�dne skorro wystarczy zwylke strstr
            // var_dump($k, $v, stristr($k, $this->sessionBrowser));
            if (stristr($k, $this->sessionBrowser)) 
            {
                $this->sessionBotName = $k; 
                //echo "spambot $v<br>\n";
                return 2;
            } 
        } 
        
        //echo "niebot<br>\n";
        return 0;
    }

    /**
     * getSessionId()
     * 
     * Szuka Session Id w Cookie i get (jak znajdzie to je zwraca co nie znaczy
     * ze sessja istnieje (moze byc "urojona" przez uzytkownika, albo juz wygasla)
     * jezeli nie zajaduje to odsyla do generatora id sessji genSessionId )
     * 
     * @author hwao 
     * @todo -c"Session" Implement Session.wykorzystac request Zyx'a
     * @access protected 
     * @return string |false
     */
    protected function getSessionId()
    {
        /*
		if (isSet($_COOKIE[self::keyCookie])) {
            if (strlen($_COOKIE[self::keyCookie]) == self::keyLen) {
                // $this->sessionExists = true;
                $this->sessionReturnInUrl = false;
                return $_COOKIE[self::keyCookie];
            } 
        } 

        if (isSet($_GET[self::keyGet])) {
            if (strlen($_GET[self::keyGet]) == self::keyLen) {
                // $this->sessionExists = true;
                return $_GET[self::keyGet];
            } 
        } 
		*/
        
        $keyCookie = self::keyCookie;
        $keyGet    = self::keyCookie;
        
        if ($this->request->map(self::keyCookie, OPB_COOKIE, MAP_TEXT)) 
        {
            $this->sessionReturnInUrl = true;
            return $this->request->$keyCookie;
        }
        
        if ($this->request->map($keyGet, OPB_GET, MAP_TEXT)) 
        {
            return $this->request->$keyCookie;
        } 
        if ($this->request->getType === OPB_POST) 
        {
            if ($this->request->map($keyGet, OPB_POST, MAP_TEXT)) 
            {
                return $this->request->$keyCookie;
            } 
        } 
        return false;
    } 

    /**
     * Generuje id Sessji mozna dodac sprawdzenie czy nie istnieje juz takie w bazie
     *
     * @return string 
     */
    protected function genSessionId()
    {
        $md = md5(uniqid(rand(), true));
        return $md;
    } 
    
    /**
     * Zwraca identyfikator sesji
     * 
     * @return string 
     */
    public function getSid()
    {
        if (!$this->sessionReturnInUrl) 
        {
            return $this->sessionId;
        } 
        else 
        {
            return false;
        } 
    }
    
    /**
     * Returns user IP
     *
     * @return string
     */
    protected function getIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     *
     *
     * @return string
     */
    protected function getBrowser()
    {
        return substr($_SERVER['HTTP_USER_AGENT'], 0, 92);
    }
} 

?>