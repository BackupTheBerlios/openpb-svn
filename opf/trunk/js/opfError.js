function opfErrors(form)
{
	this.form = form;
	this.errors = new Array();

	this.setError = function(error)
	{
		var push = true;
		for(i4=0, l4=this.errors.length; i4<l4; i4++)
		{
			if(this.errors[i4].field == error.field && (this.errors[i4].id == error.id))
			{
				push = false;
			}
		}

		if(!this.errors.length > 0 || push)
		{
			this.errors.push(error);
		}
	}

	this.getErrors = function()
	{
		return this.errors;
	}

	this.update = function(errors)
	{
		for(i2=0, l2=errors.length; i2<l2; i2++)
		{
			var id = errors[i2].id;
			var args = '';
			for(i3=0, l3=errors[i2].args.length; i3<l3; i3++)
			{
				if(isNaN(errors[i2].args[i3]))
				{
					args += '\''+eval('opfGetError.'+errors[i2].args[i3])+'\'';
				}
				else
				{
					args += errors[i2].args[i3];
				}
				
				if(i3+1 < l3)
				{
					args += ', ';
				}
				else
				{
					args += ' ';
				}
			}
			if(args)
			{
				errors[i2].text = eval('sprintf(opfGetError.'+id+', '+args+');');
			}
			else
			{
				errors[i2].text = eval('sprintf(opfGetError.'+id+');');
			}
			this.setError(errors[i2]);
		}
	}

	this.clearField = function(field)
	{
		for(i=0, l=this.errors.length; i<l; i++)
		{
			if(this.errors[i] && this.errors[i].field == field)
			{
				this.errors.splice(i, 1);
				this.clearField(field);
			}
		}
	}

	this.render = function()
	{
		var place = $('errors');
		var texts = new Array();
		var errors = this.getErrors();
		for(i5=0, l5=errors.length; i5<l5; i5++)
		{
			texts[texts.length] = errors[i5].field+': '+errors[i5].text+"<br/>";
		}
		place.innerHTML = texts;
	}
}

function opfError(field)
{
	this.field = field ? field : null;
	this.error = null;
	this.id = null;
	this.args = null;
	this.text = null;
}

var opfGetError = new function()
{
	this.constraint_type = "Invalid data type. Required type: %s."
	this.constraint_gt = "The specified number must be greater than %d."
	this.constraint_lt = "The specified number must be lower than %d."
	this.constraint_len_gt = "The specified text must be longer than %d characters."
	this.constraint_len_lt = "The specified text must be shorter than %d characters."
	this.constraint_matchto = "The specified value does not match to the pattern."
	this.constraint_scope = "The specified value does not belong to the scope from %d to %d."
	this.constraint_permittedchars = "The value may only contain the specified characters: %s"
 
	this.invaliduser = "Invalid login and/or password."
 
	this.type_integer = "integer"
	this.type_float = "floating point number"
	this.type_numeric = "number"
	this.type_string = "string"
	this.type_text = "text"
	this.type_bool = "boolean value (1 or 0)"
	this.type_choose = "selection value"
	this.type_comparable = ""
}



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