function opfForm()
{
	this.DOM = null;
	this._init = false;
	this.fields = new Array();
	this.name = '';
	this.error = new opfErrors(this);
	this.ajax = new opfAjax(this);
	this.valid = true;

	this.init = function()
	{
		if(!this._init)
		{
			this.addEvent('submit');
			this._init = true;
		}
	}

	this.map = function(name, constraints)
	{
		var field = new Field;
		field["name"] = name;
		field["constraints"] = constraints;
				
		field["constraints"].addObserver(this.error);
		this.fields[this.fields.length] = field;
	}

	this.validate = function()
	{
		this.valid = true;
		this.error.errors = new Array();

		for(i=0; i<this.fields.length; i++)
		{
			var field = eval('this.DOM.'+this.fields[i]["name"]);

			this.fields[i]["constraints"].errors = new Array();
			this.fields[i]["constraints"].setForm(this);
			this.fields[i]["constraints"].process(this.fields[i]["name"], null, field.value);
			this.fields[i]["constraints"].updateErrors();

			if(!this.fields[i]["constraints"].isValid())
			{
				this.valid = false;
			}
 		}

		if(this.valid)
		{
			this.ajax.validate();
		}
	}

	this.validateField = function(field)
	{
		this.error.clearField(field.name);
		for(i=0; i<this.fields.length; i++)
		{ 
			if(this.fields[i]["name"] == field.name)
			{
				this.fields[i]["constraints"].errors = new Array();
				this.fields[i]["constraints"].process(this.fields[i]["name"], null, field.value);
				this.fields[i]["constraints"].updateErrors();
			}
		}
	}

	this.setEvent = function(field, type)
	{
		if(type & opfMapType.JS_FOCUS)
		{
			opfEvent.addEvent($(field), 'focus', this.eventHandler);
		}
		if(type & opfMapType.JS_BLUR)
		{
			opfEvent.addEvent($(field), 'blur', this.eventHandler);
		}
		if(type & opfMapType.JS_KEYPRESS)
		{
			opfEvent.addEvent($(field), 'keypress', this.eventHandler);
		}
		if(type & opfMapType.JS_KEYUP)
		{
			opfEvent.addEvent($(field), 'keyup', this.eventHandler);
		}
		if(type & opfMapType.JS_KEYDOWN)
		{
			opfEvent.addEvent($(field), 'keydown', this.eventHandler);
		}
	}

	this.addEvent = function(type)
	{
		opfEvent.addEvent(this.DOM, type, this.eventHandler);
	}

	this.eventHandler = function(e)
	{
		var opfEH = new opfEventHandler(e);
		if(this.nodeName == 'form')
		{
			opfEH.Form = this;
		}
		else
		{
			opfEH.Form = this.form;
		}
		opfEH.load();
	}
}