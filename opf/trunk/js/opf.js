var opf = new function()
{ 
	/**
	 * Tablica zawiera wszystkie formularze dokumentu, ktore maja byz mapowane przez OPF js.
	 */
	this.forms = Array();

	this.getInstance = function()
	{
		return this;
	}

	/**
	 * Funkcja wywolywana przez zdarzenie onLoad. Przeszukuje dokument pod katem znacznika <form> i sprawdza czy
	 * jego pierwsze dziecko jest <input type="hidden" name="opfFormName" />. Jeœli tak to probuje stworzyc
	 * obiekt klasy, za pomoc¹ ktorej mozna mapowac pola formularza.
	 */
	this.Load = function()
	{
		var forms = document.getElementsByTagName('form');
		for(i=0, l=forms.length; i<l; i++)
		{
			if(forms[i].firstChild.type == 'hidden' && forms[i].firstChild.name == 'opfFormName')
			{
				try
				{
					var funcName = 'opf'+ucfirst(forms[i].firstChild.value)+'Validator()';
					var length = this.forms.length;
					this.forms[length] = eval(funcName);
					this.forms[length].DOM = forms[i];
					this.forms[length].name = forms[i].firstChild.value;
					this.forms[length].init();
				}
				catch(e)
				{
					alert(e+"\n\n");
				}
			}
		}
	}

	this.getForm = function(obj)
	{
		var name = obj.firstChild.value;
		for(i=0, l=this.forms.length; i<l; i++)
		{
			if(this.forms[i].name == name)
			{
				return this.forms[i];
			}
		}
	}

}

function Field()
{
	this.name = null;
	this.constraints = null;
	this.DOM = null;
}

function ucfirst(str)
{
   firstChar = str.substring(0,1);
   remainChar = str.substring(1);
   firstChar = firstChar.toUpperCase(); 
   remainChar = remainChar.toLowerCase();
   return firstChar + remainChar;
}


