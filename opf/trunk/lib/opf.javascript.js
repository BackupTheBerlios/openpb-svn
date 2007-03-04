/*
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
 */
 
/*	sprintf() implementation is public domain. Sources found at
	http://jan.moesen.nu/code/javascript/sprintf-and-printf-in-javascript/ */
function sprintf()
{
	if (!arguments || arguments.length < 1 || !RegExp)
	{
		return;
	}
	var str = arguments[0];
	var re = /([^%]*)%('.|0|\x20)?(-)?(\d+)?(\.\d+)?(%|b|c|d|u|f|o|s|x|X)(.*)/;
	var a = b = [], numSubstitutions = 0, numMatches = 0;
	while (a = re.exec(str))
	{
		var leftpart = a[1], pPad = a[2], pJustify = a[3], pMinLength = a[4];
		var pPrecision = a[5], pType = a[6], rightPart = a[7];
				
		//alert(a + '\n' + [a[0], leftpart, pPad, pJustify, pMinLength, pPrecision);
		numMatches++;
		if (pType == '%')
		{
			subst = '%';
		}
		else
		{
			numSubstitutions++;
			if (numSubstitutions >= arguments.length)
			{
				alert('Error! Not enough function arguments (' + (arguments.length - 1) + ', excluding the string)\nfor the number of substitution parameters in string (' + numSubstitutions + ' so far).');
			}
			var param = arguments[numSubstitutions];
			var pad = '';
			       if (pPad && pPad.substr(0,1) == "'") pad = leftpart.substr(1,1);
			  else if (pPad) pad = pPad;
			var justifyRight = true;
			       if (pJustify && pJustify === "-") justifyRight = false;
			var minLength = -1;
			       if (pMinLength) minLength = parseInt(pMinLength);
			var precision = -1;
			       if (pPrecision && pType == 'f') precision = parseInt(pPrecision.substring(1));
			var subst = param;
			       if (pType == 'b') subst = parseInt(param).toString(2);
			  else if (pType == 'c') subst = String.fromCharCode(parseInt(param));
			  else if (pType == 'd') subst = parseInt(param) ? parseInt(param) : 0;
			  else if (pType == 'u') subst = Math.abs(param);
			  else if (pType == 'f') subst = (precision > -1) ? Math.round(parseFloat(param) * Math.pow(10, precision)) / Math.pow(10, precision): parseFloat(param);
			  else if (pType == 'o') subst = parseInt(param).toString(8);
			  else if (pType == 's') subst = param;
			  else if (pType == 'x') subst = ('' + parseInt(param).toString(16)).toLowerCase();
			  else if (pType == 'X') subst = ('' + parseInt(param).toString(16)).toUpperCase();
		}
		str = leftpart + subst + rightPart;
	}
	return str;
}
 
function opfValidator()
{
	var obj = new Object;
	obj._errorMessages = new Array;
	obj.validFields = 0;
	obj.invalidFields = 0;
	obj.errors = new Array();
	
	obj.form = '';
	obj.formLoaded = false;
	obj.current = '';
	
	
	obj.addForm = function(fname)
	{
		if(typeof document.forms[fname] == 'object')
		{
			obj.form = document.forms[fname];
			obj.formLoaded = true;
		}
	}
	
	obj.addErrorMessage = function(cc, message)
	{
		obj._errorMessages[cc] = message;	
	}
	
	obj.beginValidation = function(field)
	{
		if(obj.formLoaded)
		{
			if(typeof obj.form.elements[field] == 'object')
			{
				obj.current = obj.form.elements[field];
			}
		}
	}
	
	obj.constraint = function(cc)
	{
	
	
	
	}
	
	obj._greatherThan = function(value, arg)
	{
		if(value > arg)
		{
			return true;
		}
		return false;
	}

	obj._lowerThan = function(value, arg)
	{
		if(value < arg)
		{
			return true;
		}
		return false;
	}
	
	obj._lengthGreatherThan = function(value, arg)
	{
		if(typeof value == 'string')
		{
			if(value.length > arg)
			{
				return true;
			}
		}
		return false;
	}

	obj._lengthLowerThan = function(value, arg)
	{
		if(typeof value == 'string')
		{
			if(value.length < arg)
			{
				return true;
			}
		}
		return false;
	}
	
	obj._equal = function(value, arg)
	{
		if(value == arg)
		{
			return true;
		}
		return false;
	}

	obj._lengthEqual = function(value, arg)
	{
		if(typeof value == 'string')
		{
			if(value.length == arg)
			{
				return true;
			}
		}
		return false;
	}
}gfhfghfgh
