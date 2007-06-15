function opfEventHandler(event)
{
	this.event = event;
	this.Form = null;

	this.load = function()
	{
		switch(this.event.type)
		{
			case 'submit':
				var form = opf.getInstance().getForm(this.event.target);
				form.validate();
				break;
			case 'keydown':
				var form = opf.getInstance().getForm(this.Form);
				form.validateField(this.event.target);
				this.event.preventDefault = function()
				{
					return false;
				}
				break;
			case 'keyup':
				var form = opf.getInstance().getForm(this.Form);
				form.validateField(this.event.target);
				this.event.preventDefault = function()
				{
					return true;
				}
				break;
			case 'keypress':
				var form = opf.getInstance().getForm(this.Form);
				form.validateField(this.event.target);
				this.event.preventDefault = function()
				{
					return true;
				}
				break;
			case 'blur':
				//alert('blur for: '+this.event.target.name);
				var form = opf.getInstance().getForm(this.Form);
				form.validateField(this.event.target);
				this.event.preventDefault = function()
				{
					return true;
				}
				break;
			default:
				//alert('nie ma takiego zdarzenia: '+event.type);
		}
		form.error.render();
		(this.event.preventDefault) ? this.event.preventDefault() : (this.event.returnValue = false);
	}
}



var opfEvent = new function()
{
	this.addEvent = function(obj, type, fn) 
	{
		if(obj.addEventListener)
		{
			obj.addEventListener(type, fn, false);
		} 
		else if(obj.attachEvent) 
		{
			obj["e"+type+fn] = fn;
			obj[type+fn] = function() {obj["e"+type+fn](window.event); }
			obj.attachEvent("on"+type, obj[type+fn]);
		}
	}
}