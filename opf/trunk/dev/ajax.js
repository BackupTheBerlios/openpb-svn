function DoCheck()
{
	document.getElementById("message").innerHTML = '';
	advAJAX.assign(document.getElementById("form1"), {
		parameters:
		{
			"opfAjax": "1"
		},
		onLoading: function(obj)
		{
			createMessage("message", "Form validation in progress...");
		},
		onComplete: function(obj)
		{
			document.getElementById("message").style.display = 'none';
		},
		onSuccess: function(obj)
		{
			var xmlDoc = obj.responseXML;
			var generalResult = xmlDoc.getElementsByTagName('opfResult');
			
			var opfResult = xmlDoc.getElementsByTagName('opfValidationResult');
			var result;
			var message;
			
			if(generalResult.length > 0)
			{
				if(generalResult.item(0).textContent == 1)
				{
					createMessage("message", "The data are correct. Thank you for your submission!");
				}
				else
				{
					resetErrorList();
					var err = 0;
					for(var i = 0; i < opfResult.length; i++)
					{
						result = opfResult.item(i).getElementsByTagName('opfResult');
						if(result.item(0).textContent == 0)
						{
							if(err == 0)
							{
								resetErrorList();
							}
							message = opfResult.item(i).getElementsByTagName('opfMessage');
							addError(opfResult.item(i).getAttribute('item'), message.item(0).textContent);
							err = 1;			
						}
					}
				}
			}
			else
			{
				createMessage("message", "Invalid source! Check your output syntax.");
			}
		},
		onError: function(obj)
		{
			createMessage("message", "An error occured during the transmission.");
		}
	});
}

function createMessage(id, content)
{
	resetErrorList();
	var el = document.getElementById(id);
	
	// Usun stare te te.
	resetChildren(el);
	
	var m = document.createElement('p');
	el.style.display = 'block';
	m.appendChild(document.createTextNode(content));
	el.appendChild(m);
} // end createMessage();

function resetChildren(node)
{
	var i = 0;
	for(i = 0; i < node.childNodes.length; i++)
	{
		node.removeChild(node.childNodes.item(i));
	}
} // end resetChildren();

function resetErrorList()
{
	var errorList = document.getElementById('errorList');
			
	if(errorList != null)
	{
		resetChildren(errorList);	
	}
	else
	{
		var m = document.createElement('ul');
		m.setAttribute('id', 'errorList');
		var el = document.getElementById('results');
		el.appendChild(m);
	}
} // end resetErrorList();

function addError(field, error)
{
	var errorList = document.getElementById('errorList');

	var m = document.createElement('li');
	m.appendChild(document.createTextNode(field+": "+error));
	
	errorList.appendChild(m);
} // end addError();
