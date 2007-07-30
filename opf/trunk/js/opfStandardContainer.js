function opfStandardContainer()
{
	this.constraintList = arguments;
	this.form = null;
	this.valid = true;
	this.i = 0;
	this.errors = new Array();
	this.observers = new Array();
	this.name = null;
	
	this.process = function(name, type, value)
	{
		if(!this.name)
		{
			this.name = name;
		}

		this.valid = true;
		for(k=0; k<this.constraintList.length; k++)
		{
			this.constraintList[k].error = new Array();
			if(!this.constraintList[k].process(name, type, value))
			{
				this.valid = false;
				this.errors[this.i] = this.constraintList[k].getError();
				this.i++;
			}
		}

		this.i = 0;
		return true;
	}
 
	this.isValid = function()
	{
		return this.valid;
	}

	this.setForm = function(form)
	{
		this.form = form;
	}

	this.addObserver = function(observer)
	{
		this.observers.push(observer);
	}

	this.updateErrors = function()
	{
		var len=this.observers.length;
		for(var i=0; i<len; i++)
		{
			this.observers[i].update(this.errors);
		}
	}
}