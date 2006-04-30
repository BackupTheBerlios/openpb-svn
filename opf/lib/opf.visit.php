<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                          Open Power Forms                              //
  //         Copyright (c) 2005 OpenPB team, http://www.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //

	define('OPF_STANDARD_AJAX', 1);
	define('OPF_SELECTIVE_AJAX', 2);

	define('OPF_CONTROL_COOKIE', 'ap4ic8sdmhjk39');
	define('OPF_CONTROL_COOKIE_TIME', 1800);

	class opfVisit
	{
		public
			$ip,
			$host,
			$port,
			$currentAddress,
			$currentFile,
			$currentParams,
			$referer,
			$userAgent,
			$browser,
			$os,
			$settings,
			$requestMethod,
			$secure = false,
			$cookiesEnabled,
			$ajax,				// This flag is set by HTTP Context
			$ajaxMode,			// This flag is set by HTTP Context
			$ajaxControl;		// This flag is set by HTTP Context

		public function __construct()
		{
			// Check if cookies available
			if(!isset($_COOKIE[OPF_CONTROL_COOKIE]))
			{
				$this -> cookiesEnabled = false;
			}
			else
			{
				$this -> cookiesEnabled = true;
			}
			setcookie(OPF_CONTROL_COOKIE, '1', time() + OPF_CONTROL_COOKIE_TIME);

			// Get IP and similar stuff
			$this -> ip = $_SERVER['REMOTE_ADDR'];
			if(!isset($_SERVER['REMOTE_HOST']))
			{
				$this -> host = @gethostbyaddr($_SERVER['REMOTE_ADDR']);
			}
			else
			{
				$this -> host = $_SERVER['REMOTE_HOST'];
			}
			
			// Address building
			$this -> currentAddress = '';
			switch(true)
			{
				case (strpos($_SERVER['SERVER_PROTOCOL'], 'HTTP') !== FALSE):
					$this -> currentAddress = $this -> currentFile = 'http://';
					break;
				case (strpos($_SERVER['SERVER_PROTOCOL'], 'HTTPS') !== FALSE):
					$this -> currentAddress = $this -> currentFile = 'https://';
					break;
				case (strpos($_SERVER['SERVER_PROTOCOL'], 'WAP') !== FALSE):
					$this -> currentAddress = $this -> currentFile = 'wap://';
					break;
			}
			$this -> currentAddress .= $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$this -> currentFile .= $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
			$this -> currentParams = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : $_SERVER['QUERY_STRING'];
			if(isset($_SERVER['HTTP_REFERER']))
			{
				$this -> referer = $_SERVER['HTTP_REFERER'];
			}
			$this -> settings = array(
				'richedit' => 0,
				'xhtml' => 0,
				'dom' => 0
			);
			$this -> getSoftwareInfo();
			
			switch($_SERVER['REQUEST_METHOD'])
			{
				case 'POST':
					$this -> requestMethod = OPF_POST; break;
				case 'GET':
					$this -> requestMethod = OPF_GET; break;
			}
			
			$this -> port = $_SERVER['SERVER_PORT'];
			if($this -> port == '443')
			{
				$this -> secure = true;
			}
		} // end __construct();

		public function friendly($name)
		{
			return htmlspecialchars($this -> {$name}['name']).' '.htmlspecialchars($this -> {$name}['version']);
		} // end friendlyString();
		
		private function getSoftwareInfo()
		{
			$arr = $this -> detectBrowser($_SERVER['HTTP_USER_AGENT']);
			
			$this -> browser = array(
				'name' => $arr[0],
				'code' => $arr[1],
				'version' => $arr[2],
			);
			
			$this -> os = array(
				'name' => $arr[3],
				'code' => $arr[4],
				'version' => $arr[5]			
			);			
		} // getSoftwareInfo();
		
		private function detectBrowser($ua)
		{	
			$browserName = $browserCode = $browserVer = $osName = $osCode = $osVer = '';
			
			$ua = preg_replace('/FunWebProducts/i', '', $ua);
			switch(true)
			{
				case (preg_match('#Opera[ /]([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Opera';
					$browserCode = 'opera';
					$browserVer = $matches[1];
					if(strpos($ua, 'Windows') !== FALSE)
					{
						list($osName, $osCode, $osVer) = $this->WindowsDetectOs($ua);
					}
					else
					{
						list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					}
					if($browserVer == '9.0')
					{
						$this -> settings['richedit'] = 1;
					}
					$this -> settings['dom'] = 1;
					$this -> settings['xhtml'] = 1;
					$this -> settings['gzip'] = 1;
					break;
				case (preg_match('#(Firefox|Phoenix|Firebird)/([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Firefox';
					$browserCode = 'firefox';
					$browserVer = $matches[2];
					if(strpos($ua, 'Windows') !== FALSE)
					{
						list($osName, $osCode, $osVer) = $this->WindowsDetectOs($ua);
					}
					else
					{
						list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					}
					$this -> settings['richedit'] = 1;
					$this -> settings['dom'] = 1;
					$this -> settings['xhtml'] = 1;
					$this -> settings['gzip'] = 1;
					break;
				case (preg_match('#MSIE ([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Internet Explorer';
					$browserCode = 'ie';
					$browserVer = $matches[1];
					list($osName, $osCode, $osVer) = $this->WindowsDetectOs($ua);
					$this -> settings['richedit'] = 1;
					$this -> settings['dom'] = 1;
					$this -> settings['xhtml'] = 0;
					$this -> settings['gzip'] = 0;
					break;
				case (preg_match('#Galeon/([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Galeon';
					$browserCode = 'galeon';
					$browserVer = $matches[1];
					list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					break;
				case (preg_match('#Safari/([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Safari';
					$browserCode = 'safari';
					$browserVer = $matches[1];
					$osName = 'MacOS';
					$osCode = 'macos';
					$osVer = 'X';
					break;
				case (preg_match('#(Camino|Chimera)[ /]([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Camino';
					$browserCode = 'camino';
					$browserVer = $matches[2];
					$osName = 'MacOS';
					$osCode = 'macos';
					$osVer = 'X';
					break;
				case (preg_match('#Shiira[ /]([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Shiira';
					$browserCode = 'shiira';
					$browserVer = $matches[2];
					$osName = 'MacOS';
					$osCode = 'macos';
					$osVer = 'X';
					break;
				case (preg_match('#Dillo[ /]([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Dillo';
					$browserCode = 'dillo';
					$browserVer = $matches[1];
					break;
				case (preg_match('#Epiphany/([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Epiphany';
					$browserCode = 'epiphany';
					$browserVer = $matches[1];
					list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					break;

				case (preg_match('#iCab/([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'iCab';
					$browserCode = 'icab';
					$browserVer = $matches[1];
					$osName = 'MacOS';
					$osCode = 'macos';
					if(preg_match('#Mac OS X#i', $ua))
					{
						$osVer = 'X';
					}
					break;
				case (preg_match('#K-Meleon/([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'K-Meleon';
					$browserCode = 'kmeleon';
					$browserVer = $matches[1];
					if(strpos($ua, 'Windows') !== FALSE)
					{
						list($osName, $osCode, $osVer) = $this->WindowsDetectOs($ua);
					}
					else
					{
						list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					}
					break;
				case (preg_match('#Lynx/([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Lynx';
					$browserCode = 'lynx';
					$browserVer = $matches[1];
					list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					break;
				case (preg_match('#Links \\(([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Links';
					$browserCode = 'lynx';
					$browserVer = $matches[1];
					list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					break;
				case (preg_match('#ELinks[/ ]([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'ELinks';
					$browserCode = 'lynx';
					$browserVer = $matches[1];
					list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					break;
				case (preg_match('#ELinks \\(([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'ELinks';
					$browserCode = 'lynx';
					$browserVer = $matches[1];
					list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					break;
				case (preg_match('#Konqueror/([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Konqueror';
					$browserCode = 'konqueror';
					$browserVer = $matches[1];
					list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					break;
				case (preg_match('#NetPositive/([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'NetPositive';
					$browserCode = 'netpositive';
					$browserVer = $matches[1];
					$osName = 'BeOS';
					$osCode = 'beos';
					break;
				case (preg_match('#OmniWeb#i', $ua, $matches)):
					$browserName = 'OmniWeb';
					$browserCode = 'omniweb';
					$osName = 'MacOS';
					$osCode = 'macos';
					$osVer = 'X';
					break;
				case (preg_match('#Netscape[0-9]?/([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Netscape';
					$browserCode = 'netscape';
					$browserVer = $matches[1];
					if(strpos($ua, 'Windows') !== FALSE)
					{
						list($osName, $osCode, $osVer) = $this->WindowsDetectOs($ua);
					}
					else
					{
						list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					}
					break;
				case (preg_match('#^Mozilla/5.0#i', $ua) && preg_match('#rv:([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Mozilla';
					$browserCode = 'mozilla';
					$browserVer = $matches[1];
					if(strpos($ua, 'Windows') !== FALSE)
					{
						list($osName, $osCode, $osVer) = $this->WindowsDetectOs($ua);
					}
					else
					{
						list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					}
					break;
				case (preg_match('#^Mozilla/([a-zA-Z0-9.]+)#i', $ua, $matches)):
					$browserName = 'Mozilla';
					$browserCode = 'mozilla';
					$browserVer = $matches[1];
					if(strpos($ua, 'Win') !== FALSE)
					{
						list($osName, $osCode, $osVer) = $this->WindowsDetectOs($ua);
					}
					else
					{
						list($osName, $osCode, $osVer) = $this->UnixDetectOs($ua);
					}
					break;
				// SEARCH ENGINES
				case (preg_match('#Googlebot[ \/]([a-zA-Z0-9\.]+)#i', $ua, $matches)):
					$browserName = 'Google';
					$browserCode = 'googlebot';
					$browserVer = $matches[1];
					$osName = 'Search engine';
					$osCode = 'webcrawler';
					$osVer = '';
					break;
				case (preg_match('#Scooter[ \/]([a-zA-Z0-9\.]+)#i', $ua, $matches)):
					$browserName = 'Altavista';
					$browserCode = 'scooter';
					$browserVer = $matches[1];
					$osName = 'Search engine';
					$osCode = 'webcrawler';
					$osVer = '';
					break;
				case (preg_match('#MSNBOT[ \/]([a-zA-Z0-9\.]+)#i', $ua, $matches)):
					$browserName = 'MSN Search';
					$browserCode = 'msnbot';
					$browserVer = $matches[1];
					$osName = 'Search engine';
					$osCode = 'webcrawler';
					$osVer = '';
					break;
				case (preg_match('#Lycos_Spider_\(T\-Rex\)#i', $ua, $matches)):
					$browserName = 'Lycos';
					$browserCode = 'lycos_spider';
					$browserVer = '';
					$osName = 'Search engine';
					$osCode = 'webcrawler';
					$osVer = '';
					break;
				case (preg_match('#archive\.org_bot#i', $ua, $matches)):
					$browserName = 'Archive.org';
					$browserCode = 'archive.org_bot';
					$browserVer = '';
					$osName = 'Search engine';
					$osCode = 'webcrawler';
					$osVer = '';
					break;
			}
			
			return array($browserName, $browserCode, $browserVer, $osName, $osCode, $osVer);
		} // end detectBrowser();
		
		private function windowsDetectOs($ua)
		{
			$osName = $osCode = $osVer = '';
			switch(true)
			{
				case (preg_match('/Windows 95/i', $ua) || preg_match('/Win95/', $ua)):
					$osName = 'Windows';
					$osCode = 'windows';
					$osVer = '95';
					break;
				case (preg_match('/Windows NT 5.0/i', $ua) || preg_match('/Windows 2000/i', $ua)):
					$osName = 'Windows';
					$osCode = 'windows';
					$osVer = '2000';
					break;
				case (preg_match('/Win 9x 4.90/i', $ua) || preg_match('/Windows ME/i', $ua)):
					$osName = 'Windows';
					$osCode = 'windows';
					$osVer = 'ME';
					break;
				case (preg_match('/Windows.98/i', $ua) || preg_match('/Win98/i', $ua)):
					$osName = 'Windows';
					$osCode = 'windows';
					$osVer = '98';
					break;
				case (preg_match('/Windows (NT 5\.1|XP)/i', $ua)):
					$osName = 'Windows';
					$osCode = 'windows';
					$osVer = 'XP';
					break;
				case (preg_match('/Windows NT 5.2/i', $ua)):
					$osName = 'Windows';
					$osCode = 'windows';
					if (preg_match('/Win64/i', $ua))
					{
						$osVer = 'XP 64 bit';
					}
					else
					{
						$osVer = 'Server 2003';
					}
					break;
				case (preg_match('/Mac_PowerPC/i', $ua)):
					$osName = 'MacOS';
					$osCode = 'macos';
					break;
				case (preg_match('/Windows NT 4.0/i', $ua) || preg_match('/WinNT4.0/i', $ua)):
					$osName = 'Windows';
					$osCode = 'windows';
					$osVer = 'NT 4.0';
					break;
				case (preg_match('/Windows NT/i', $ua) || preg_match('/WinNT/i', $ua)):
					$osName = 'Windows';
					$osCode = 'windows';
					$osVer = 'NT';
					break;
				case (preg_match('/Windows CE/i', $ua)):
					$osName = 'Windows';
					$osCode = 'windows';
					$osVer = 'CE';
					if(preg_match('/PPC/i', $ua))
					{
						$osName = 'Microsoft PocketPC';
						$osCode = 'windows';
						$osVer = '';
					}
					
					if(preg_match('/smartphone/i', $ua))
					{
						$osName = 'Microsoft Smartphone';
						$osCode = 'windows';
						$osVer = '';
					}
					break;
			}
			
			return array($osName, $osCode, $osVer);
		} // end windowsDetectOs();
		
		private function unixDetectOs($ua)
		{
			$osName = $osCode = $osVer = '';
			switch(true)
			{
				case (preg_match('/Linux/i', $ua)):
					$osName = 'Linux';
					$osCode = 'linux';
					switch(true)
					{
						case (preg_match('#Debian#i', $ua)):
							$osCode = 'debian';
							$osName = 'Debian GNU/Linux';
							break;
						case (preg_match('#Mandrake#i', $ua)):
							$osCode = 'mandrake';
							$osName = 'Mandrake Linux';
							break;
						case (preg_match('#SuSE#i', $ua)):
							$osCode = 'suse';
							$osName = 'SuSE Linux';
							break;
						case (preg_match('#Novell#i', $ua)):
							$osCode = 'novell';
							$osName = 'Novell Linux';
							break;
						case (preg_match('#Ubuntu#i', $ua)):
							$osCode = 'ubuntu';
							$osName = 'Ubuntu Linux';
							break;
						case (preg_match('#Red ?Hat#i', $ua)):
							$osCode = 'redhat';
							$osName = 'RedHat Linux';
							break;
						case (preg_match('#Gentoo#i', $ua)):
							$osCode = 'gentoo';
							$osName = 'Gentoo Linux';
							break;
						case (preg_match('#Fedora#i', $ua)):
							$osCode = 'fedora';
							$osName = 'Fedora Linux';
							break;
						case (preg_match('#MEPIS#i', $ua)):
							$osName = 'MEPIS Linux';
							break;
						case (preg_match('#Knoppix#i', $ua)):
							$osName = 'Knoppix Linux';
							break;
						case (preg_match('#Slackware#i', $ua)):
							$osCode = 'slackware';
							$osName = 'Slackware Linux';
							break;
						case (preg_match('#Xandros#i', $ua)):
							$osName = 'Xandros Linux';
							break;
						case (preg_match('#Kanotix#i', $ua)):
							$osName = 'Kanotix Linux';
							break;
					}
					break;
				case preg_match('/FreeBSD/i', $ua):
					$osName = 'FreeBSD';
					$osCode = 'freebsd';
					break;
				case preg_match('/NetBSD/i', $ua):
					$osName = 'NetBSD';
					$osCode = 'netbsd';
					break;
				case preg_match('/OpenBSD/i', $ua):
					$osName = 'OpenBSD';
					$osCode = 'openbsd';
					break;
				case preg_match('/IRIX/i', $ua):
					$osName = 'SGI IRIX';
					$osCode = 'sgi';
					break;
				case preg_match('/SunOS/i', $ua):
					$osName = 'Solaris';
					$osCode = 'sun';
					break;
				case preg_match('/Mac OS X/i', $ua):
					$osName = 'Mac OS';
					$osCode = 'macos';
					$osVer = 'X';
					break;
				case preg_match('/Macintosh/i', $ua):
					$osName = 'Mac OS';
					$osCode = 'macos';
					break;
				case preg_match('/Unix/i', $ua):
					$osName = 'UNIX';
					$osCode = 'unix';
					break;
			}
			
			return array($osName, $osCode, $osVer);
		} // end unixDetectOs();
	} // end opfVisit;

?>
