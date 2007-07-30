function opfAjax(form)
{
	this.form = form;
	this.valid = true;

	this.validate = function()
	{
		request = mint.Request();

		request.OnSuccess = function()
	    {
			var result = this.responseXML.getElementsByTagName('opfResult');
			if(result[0].firstChild.nodeValue == 1)
			{
				this.error.valid = true;
			}

			var results = this.responseXML.getElementsByTagName('opfValidationResult');

			for(i=0; i<results.length; i++)
			{
							this.error.valid = false;
				for(k=0; k<results[i].childNodes.length; k++)
				{
					if(results[i].childNodes[k].tagName == 'opfResult')
					{
						if(results[i].childNodes[k].firstChild.nodeValue == null || results[i].childNodes[k].firstChild.nodeValue == 0)
						{
							this.error.valid = false;
						}
					}

					if(results[i].childNodes[k].tagName == 'opfMessage')
					{
						var err = new opfError(results[i].getAttribute('item'));
						err.id = results[i].childNodes[k].firstChild.nodeValue;
						err.text = results[i].childNodes[k].firstChild.nodeValue;
						this.error.setError(err);
						this.error.render();
					}
				}
			}
		}
		
		request.error = this.form.error;
		request.AddParam('opfAjax', 1);
		this.form.DOM.id ='sendFormWithMintAjax';
		request.SendForm(this.form.DOM.id, this.form.DOM.action);

		return this.valid;
	}
}