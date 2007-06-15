function opfConstraint(type)
{
	this.type = type;
	this.params = new Array();
	this.params = arguments;
	this.valid = true;
	this.name = '';

	if(this.params[0] == opfMapType.MAP_JS)
	{
	}	

	this.process = function(name, type, value)
	{
		this.valid = true;
		this.name = name;
		switch(this.type)
		{
			case opfMapType.MAP_TYPE:
				switch(this.params[1])
				{
					case opfMapType.TYPE_INTEGER:
						if(parseInt(value) != value)
						{
							this.setError('constraint_type', 'type_integer');
						}
						break;
					case opfMapType.TYPE_FLOAT:
						if(!preg_match('/([0-9]*?)[.,]([0-9]*?)/', value))
						{
							this.setError('constraint_type', 'type_float');
						}
						break;
					case opfMapType.TYPE_NUMERIC:
						if(!preg_match('/([0-9 -]*?)([\.,][0-9]*?)?/', value))
						{
							this.setError('constraint_type', 'type_numeric');
						}
						break;
					case opfMapType.TYPE_STRING:
						if(!(value) || ((value.length) > 256))
						{
							this.setError('constraint_type', 'type_string');
						}
						break;
					case opfMapType.TYPE_TEXT:
						if(parseInt(value) == value)
						{
							this.setError('constraint_type', 'type_text');
						}
						break;
					case opfMapType.TYPE_BOOL:
						if(!preg_match('/(0|1)/', value))
						{
							this.setError('constraint_type', 'type_bool');
						}
						break;
					case opfMapType.TYPE_CHOOSE:
						if(value == 'on' || value == 'off')
						{
							value = (value == 'on' ? 1 : 0);
						}
						else
						{
							this.setError('constraint_type', 'type_choose');
						}
						break;
					case opfMapType.TYPE_COMPARABLE:
						if(value != $(name+'2').value)
						{
							this.setError('constraint_type', 'type_comparable');
						}
				}
				break;
			case opfMapType.MAP_GT:
				if(!(value > this.params[1]))
				{
					this.setError('constraint_gt', this.params[1]);
				}
				break;
			case opfMapType.MAP_LT:
				if(!(value < this.params[1]))
				{
					this.setError('constraint_lt', this.params[1]);
				}
				break;
			case opfMapType.MAP_LEN_GT:
				if(!((value.length) > this.params[1]))
				{
					this.setError('constraint_len_gt', this.params[1]);
				}
				break;
			case opfMapType.MAP_LEN_LT:
				if(!((value.length) < this.params[1]))
				{
					this.setError('constraint_len_lt', this.params[1]);
				}
				break;
			case opfMapType.MAP_EQUAL:
				if(value != this.params[1])
				{
					this.setError('constraint_equal', this.params[1]);
				}
				break;
			case opfMapType.MAP_LEN_EQUAL:
				if((value.length) != this.params[1])
				{
					this.setError('constraint_len_equal', this.params[1]);
				}		        	
				break;
			case opfMapType.MAP_PASSWORD:
				if(value != _POST[this.params[1]])
				{
					this.setError('constraint_password', this.params[1]);
				}
				break;
			case opfMapType.MAP_MATCHTO:
				if(!value.match(this.params[1].slice(1, (this.params[1].length)-1 )))
				{
					this.setError('constraint_matchto');
				}
				break;
			case opfMapType.MAP_NOTMATCHTO:
				if(preg_match(this.params[1], value))
				{
					this.setError('constraint_notmatchto');
				}
				break;
			case opfMapType.MAP_PERMITTEDCHARS:
				for(var i = 0; i < (value.length); i++)
				{
					if(this.params[1].indexOf(value.charAt(i)) < 0)
					{
						this.setError('constraint_permittedchars', this.params[1]);
					}
				}
				break;
			case opfMapType.MAP_NOTPERMITTEDCHARS:
				for(var i = 0; i < (value.length); i++)
				{
					if(this.params[1].indexOf(value.charAt(i)) >= 0)
					{
						this.setError('constraint_permittedchars', this.params[1]);
					}
				}
				break;
			case opfMapType.MAP_SCOPE:
				if(!(value > this.params[1] && value < this.params[2]))
				{
					this.setError('constraint_scope', this.params[1], this.params[2]);
				}
				break;
		}

		return this.valid;
	}
	
 
	this.setError = function(id)
	{
		this.valid = false;
		var _args = new Array();

		for(i2=1, l2=arguments.length; i2<l2; i2++)
		{
			_args.push(arguments[i2]);
		}

		var error = new opfError(this.name);
		error.id = id;
		error.args = _args;

		this.error = error;
	}
 
	this.getError = function()
	{
		if(this.error)
		{
			return this.error;
		}
	}
}
