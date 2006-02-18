$debugCode = 'opd_console.document.write(\'<html>\');
opd_console.document.write(\'<head>\');
opd_console.document.write(\'<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />\');
opd_console.document.write(\'<title>OPD Debug Console</title>\');
opd_console.document.write(\'<style>\');
opd_console.document.write(\'body{\');
opd_console.document.write(\'	background: #ffffff;\');
opd_console.document.write(\'	font-family: Verdana, Arial, Tahoma, Helvetica;\');
opd_console.document.write(\'	font-size: 11px;\');
opd_console.document.write(\'}\');
opd_console.document.write(\'table#info{\');
opd_console.document.write(\'	width: 100%;\');
opd_console.document.write(\'	padding: 0;\');
opd_console.document.write(\'	margin: 0;\');
opd_console.document.write(\'	border-spacing: 0;\');
opd_console.document.write(\'	border: 1px #333333 solid;\');
opd_console.document.write(\'}\');
opd_console.document.write(\'table#info td.field{\');
opd_console.document.write(\'	margin: 0;\');
opd_console.document.write(\'	width: 30%;\');
opd_console.document.write(\'	color: #474747;\');
opd_console.document.write(\'	border-width: 1px 0 1px 0;\');
opd_console.document.write(\'	border-style: solid;\');
opd_console.document.write(\'	border-color: #ffffff #ffffff #b2b2b2 #ffffff;\');
opd_console.document.write(\'	background-color: #dadada;\');
opd_console.document.write(\'	font-size: 11px;\');
opd_console.document.write(\'}\');
opd_console.document.write(\'table#info td.value{\');
opd_console.document.write(\'	margin: 0;\');
opd_console.document.write(\'	width: 70%;\');
opd_console.document.write(\'	border-width: 1px;\');
opd_console.document.write(\'	border-color: #ffffff #e4e4e4 #e4e4e4 #ffffff;\');
opd_console.document.write(\'	border-style: solid;\');
opd_console.document.write(\'	background-color: #efefef;\');
opd_console.document.write(\'	font-size: 11px;\');
opd_console.document.write(\'}\');
opd_console.document.write(\'table#queries{\');
opd_console.document.write(\'	width: 100%;\');
opd_console.document.write(\'	padding: 0;\');
opd_console.document.write(\'	margin: 0;\');
opd_console.document.write(\'	margin-top: 4px;\');
opd_console.document.write(\'	border-spacing: 0;\');
opd_console.document.write(\'	border: 1px #333333 solid;\');
opd_console.document.write(\'}\');
opd_console.document.write(\'table#queries thead td{\');
opd_console.document.write(\'	text-align: left;\');
opd_console.document.write(\'	padding: 3px 3px 3px 12px;\');
opd_console.document.write(\'	font-size: 12px;\');
opd_console.document.write(\'	color: #474747;\');
opd_console.document.write(\'	border-width: 1px 0 1px 0;\');
opd_console.document.write(\'	border-style: solid;\');
opd_console.document.write(\'	border-color: #ffffff #ffffff #b2b2b2 #ffffff;\');
opd_console.document.write(\'	background-color: #dadada;\');
opd_console.document.write(\'	font-weight: bold;\');
opd_console.document.write(\'}\');
opd_console.document.write(\'table#queries tbody td{\');
opd_console.document.write(\'	background-color: #f5f5f5;\');
opd_console.document.write(\'	border-width: 0 1px 1px 0;\');
opd_console.document.write(\'	border-style: solid;\');
opd_console.document.write(\'	border-bottom-color: #d2d2d2;\');
opd_console.document.write(\'	border-right-color: #d2d2d2;\');
opd_console.document.write(\'	font-size: 10px;\');
opd_console.document.write(\'	margin-top: 3px;\');
opd_console.document.write(\'}\');
opd_console.document.write(\'table#queries tbody tr.cached td{\');
opd_console.document.write(\'	background: #ededff;\');
opd_console.document.write(\'}\');
opd_console.document.write(\'</style>\');
opd_console.document.write(\'</head>\');
opd_console.document.write(\'<body>\');
opd_console.document.write(\'<h1>OPD Debug Console</h1>\');
opd_console.document.write(\'<table id="info">\');
';

	foreach($config as $name => $value)
	{
		$debugCode .= 'opd_console.document.write(\'<tr>\');
opd_console.document.write(\'<td class="field">'.$name.'</td>\');
opd_console.document.write(\'<td class="value">'.$value.'</td>\');
opd_console.document.write(\'</tr>\');';
	}

$debugCode .= '
opd_console.document.write(\'</table>\');
opd_console.document.write(\'<table id="queries">\');
opd_console.document.write(\'<thead>\');
opd_console.document.write(\'<tr>\');
opd_console.document.write(\' <td>Query</td>\');
opd_console.document.write(\' <td>Cache</td>\');
opd_console.document.write(\' <td>Result</td>\');
opd_console.document.write(\' <td>Execution time</td>\');
opd_console.document.write(\'</tr>\');
opd_console.document.write(\'</thead>\');
opd_console.document.write(\'<tbody>\');
';

	foreach($this -> queryMonitor as $data)
	{
		if($data['cached'] == true)
		{
			$debugCode .= 'opd_console.document.write(\'<tr class="cached">\');';		
		}
		else
		{
			$debugCode .= 'opd_console.document.write(\'<tr>\');';
		}
		$debugCode .= 'opd_console.document.write(\' <td>'.addslashes($data['query']).'</td>\');
opd_console.document.write(\' <td>'.$data['cache'].'</td>\');
opd_console.document.write(\' <td>'.$data['result'].'</td>\');
opd_console.document.write(\' <td>'.$data['execution'].' s</td>\');
opd_console.document.write(\'</tr>\');';	
	}
$debugCode .= '
opd_console.document.write(\'</tbody>\');
opd_console.document.write(\'</table>\');
opd_console.document.write(\'\');
opd_console.document.write(\'</body>\');
opd_console.document.write(\'</html>\');';
