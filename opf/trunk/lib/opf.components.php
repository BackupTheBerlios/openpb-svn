<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                          Open Power Forms                              //
  //         Copyright (c) 2007 OpenPB team, http://www.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //

	class opfComponent implements ioptComponent
	{
		public $name;
		public $tpl;
		public $opf;
		public $form;
		public $design;
		
		protected $componentName = 'base';
		protected $tags;
		protected $refresh = true;
		
		protected $cssClass = NULL;
		protected $invalidCssClass = NULL;
		
		public function __construct($name = '')
		{
			$this -> name = $name;
		} // end __construct();

		public function setOptInstance(optClass $tpl)
		{
			global $formName;
			if(isset($formName))
			{
				$this -> tpl = $tpl;
				$this -> opf = $tpl -> opf;
				// Set the OPF form
				if(isset($this -> tpl -> data[$formName]))
				{
					$this -> form = $this -> tpl -> data[$formName];
				}
				else
				{
					$this -> opf -> error(OPF_E_NO_FORM_TAG, 'No form block defined for templates: '.$formName.'!');
				}
				$this -> design = $this -> opf -> design;
			}
			else
			{
				$this->opf->error(OPF_E_NOT_IN_FORM, 'Using an OPF component when not in a form!');
			}
		} // end setOptInstance();

		public function set($name, $value)
		{
			switch($name)
			{
				case 'name':
					$this -> name = $value;
					break;
				case 'refresh':
					$this -> refresh = (bool)$value;
					return;
				case 'class':
					$this -> cssClass = $value;
					return;
				case 'invalidClass':
					$this -> invalidCssClass = $value;
					return;				
			}
			$this -> tags[$name] = $value;
		} // end set();

		public function push($name, $value, $selected = false)
		{
		
		} // end push();

		public function setDatasource(&$source)
		{
		
		} // end setDatasource();

		public function begin()
		{	

		} // end begin();

		public function end()
		{
		
		} // end end();
		
		public function onMessage($msg)
		{
			if(isset($this -> form -> errorMessages[$this->name]))
			{
				$this -> tpl -> vars[$msg] = $this -> form -> errorMessages[$this->name];
				return true;
			}
			return false;
		} // end onMessage();

		protected function getClass($return = false)
		{
			if(isset($this -> form -> errorMessages[$this -> name]))
			{
				// There are bugs
				if(!is_null($this -> invalidCssClass))
				{
					$class = $this -> invalidCssClass;
				}
				else
				{
					$class = $this -> design -> getClass($this -> componentName, $this->name, false);
				}
			}
			else
			{
				// Valid field
				if(!is_null($this -> cssClass))
				{
					$class = $this -> cssClass;
				}
				else
				{
					$class = $this -> design -> getClass($this -> componentName, $this->name, true);
				}
			}
			
			if($class !== false && !$return)
			{
				$this -> tags['class'] = $class;
			}
			elseif($class !== false && $return)
			{
				return $class;
			}
			elseif($return)
			{
				return '';
			}
		} // end getClass();
		
		protected function getValue($return = false)
		{
			if(!$return)
			{
				if($this -> refresh)
				{
					$this -> tags['value'] = $this->form->getValue($this->name);
				}
			}
			else
			{
				if($this -> refresh)
				{
					return $this->form->getValue($this->name);
				}
				return '';
			}
		} // end getValue();
		
		protected function generateTags($list)
		{
			$code = '';
			foreach($list as $name => $value)
			{
				$code .= ' '.$name.'="'.htmlspecialchars($value).'"';			
			}
			return $code;
		} // end generateTags();
	}
	
	class opfInput extends opfComponent
	{
		protected $componentName = 'input';
		
		public function begin()
		{
			$this -> getClass();
			$this -> getValue();
		
			echo '<input type="text"'.$this->generateTags($this -> tags).' />';
		} // end begin();
	
	}
	
	class opfLabel extends opfComponent
	{
		protected $componentName = 'label';
		
		public function begin()
		{
			$this -> getClass();
			$this -> getValue();
		
			echo $this -> tags['value'].' <input type="hidden"'.$this->generateTags($this -> tags).' />';
		} // end begin();	
	}
	
	class opfPassword extends opfComponent
	{
		protected $componentName = 'password';
	
		public function begin()
		{
			$this -> getClass();
			echo '<input type="password"'.$this->generateTags($this -> tags).' />';
		} // end begin();	
	}
	
	class opfRetypePassword extends opfComponent
	{
		protected $componentName = 'retype password';
	
		public function begin()
		{
			$this -> getClass();
			echo '<input type="password"'.$this->generateTags($this -> tags).' />';
		} // end begin();	
	}
	
	class opfTextarea extends opfComponent
	{
		protected $componentName = 'textarea';
	
		public function begin()
		{
			$this -> getClass();
			echo '<textarea'.$this->generateTags($this -> tags).'>'.$this->getValue(true).'</textarea>';
		} // end begin();
	
	}
	
	class opfQuestion extends opfComponent
	{
		protected $componentName = 'questionYes';
	
		public function begin()
		{
			$value = $this->form->getValue($this->name);
		
			$this -> getClass();
			$this -> tags['value'] = 1;
			echo '<label>'.$this->form->i18n->put($this->opf->i18nGroup, 'text_yes');
			if($value == 1)
			{
				echo '<input type="radio" '.$this->generateTags($this -> tags).' checked="checked"/>';
			}
			else
			{
				echo '<input type="radio" '.$this->generateTags($this -> tags).'/>';
			}
			echo '</label>';
			
			$this -> componentName = 'questionNo';
			$this -> getClass();
			$this -> tags['value'] = 0;
			echo '<label>';
			if($value == 0)
			{		
				echo '<input type="radio" '.$this->generateTags($this -> tags).' checked="checked"/>';
			}
			else
			{
				echo '<input type="radio" '.$this->generateTags($this -> tags).'/>';
			}
			echo $this->form->i18n->put($this->context->i18nGroup, 'text_no').'</label>';
			$this -> componentName = 'questionYes';
		} // end begin();
	
	}
	
	class opfCheckQuestion extends opfComponent
	{
		protected $componentName = 'checkQuestion';
	
		public function begin()
		{
			$this -> getClass();
			if($this->getValue(true) == 1)
			{
				echo '<input type="checkbox"'.$this->generateTags($this -> tags).' checked="checked" />';
			}
			else
			{
				echo '<input type="checkbox"'.$this->generateTags($this -> tags).' />';
			}
		} // end begin();	
	}
	
	class opfSelect extends opfComponent
	{
		protected $componentName = 'select';
		
		public function begin()
		{
			$this -> getClass();

			echo '<select '.$this->generateTags($this -> tags).'>';
			$this -> displayGroup($this->form->getValue($this->name.'Values', true), $this->getValue(true));
			echo '</select>';
		} // end begin();
		
		private function displayGroup($values, $selected)
		{
			if(!is_array($values))
			{
				echo '<option>Warning: value list is not an array</option>';
				return 0;			
			}
			foreach($values as $id => $value)
			{
				if(is_array($value))
				{
					echo '<optgroup label="'.$id.'">';
					$this -> displayGroup($value, $selected);
					echo '</optgroup>';
				}
				if($selected == $id)
				{
					echo '<option value="'.$id.'" selected="selected">'.htmlspecialchars($value).'</option>';
				}
				else
				{
					echo '<option value="'.$id.'">'.htmlspecialchars($value).'</option>';
				}			
			}
		} // end displayGroup();
	}
	
	class opfRadio extends opfComponent
	{
		protected $componentName = 'radio';
		
		public function begin()
		{
			$this -> componentName = 'radioList';
			$class = $this -> getClass(true);
			if($class != '')
			{
				echo '<ul class="'.$class.'">';
			}
			else
			{
				echo '<ul>';
			}
			$values = $this -> form -> getValue($this->name.'Values', true);
			$selected = $this -> getValue(true);
			
			$this -> componentName = 'radio';
			$this -> getClass();
			
			foreach($values as $id => $value)
			{
				$this -> tags['value'] = $id;
				if($selected == $id)
				{
					$this -> tags['checked'] = 'checked';
					echo '<li><label><input type="radio" '.$this->generateTags($this -> tags).'/> '.htmlspecialchars($value).'</label></li>';
					unset($this -> tags['checked']);
				}
				else
				{
					echo '<li><label><input type="radio" '.$this->generateTags($this -> tags).'/> '.htmlspecialchars($value).'</label></li>';
				}						
			}
			echo '</ul>';
		} // end begin();
	}
	
	class opfFile extends opfComponent
	{
		protected $componentName = 'file';
		
		public function begin()
		{
			$this -> getClass();
			echo '<input type="file"'.$this->generateTags($this -> tags).' />';
		} // end begin();
	
	}

?>
