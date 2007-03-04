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
				$this -> design = $this -> opf->design;
			}
			else
			{
				$this->opf->error(OPF_E_NOT_IN_FORM, 'Using an OPF component when not in a form!');
			}
		} // end setOptInstance();

		public function set($name, $value)
		{
			if($name == 'name')
			{
				$this -> name = $value;
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
				$class = $this -> design -> getClass($this -> componentName, false);
			}
			else
			{
				$class = $this -> design -> getClass($this -> componentName, true);
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
	}
	
	class opfInput extends opfComponent
	{
		protected $componentName = 'input';
		
		public function begin()
		{
			$this -> getClass();
			$this -> tags['value'] = $this->form->getValue($this->name);
		
			echo '<input type="text"'.generateTagElementList($this -> tags).' />';
		} // end begin();
	
	}
	
	class opfLabel extends opfComponent
	{
		protected $componentName = 'label';
		
		public function begin()
		{
			$this -> getClass();
			$this -> tags['value'] = $this->form->getValue($this->name);
		
			echo $this -> tags['value'].' <input type="hidden"'.generateTagElementList($this -> tags).' />';
		} // end begin();	
	}
	
	class opfPassword extends opfComponent
	{
		protected $componentName = 'password';
	
		public function begin()
		{
			$this -> getClass();
			echo '<input type="password"'.generateTagElementList($this -> tags).' />';
		} // end begin();	
	}
	
	class opfRetypePassword extends opfComponent
	{
		protected $componentName = 'retype password';
	
		public function begin()
		{
			$this -> getClass();
			echo '<input type="password"'.generateTagElementList($this -> tags).' />';
		} // end begin();	
	}
	
	class opfTextarea extends opfComponent
	{
		protected $componentName = 'textarea';
	
		public function begin()
		{
			$this -> getClass();
			echo '<textarea'.generateTagElementList($this -> tags).'>'.$this->form->getValue($this->name).'</textarea>';
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
				echo '<input type="radio" '.generateTagElementList($this -> tags).' checked="checked"/>';
			}
			else
			{
				echo '<input type="radio" '.generateTagElementList($this -> tags).'/>';
			}
			echo '</label>';
			
			$this -> componentName = 'questionNo';
			$this -> getClass();
			$this -> tags['value'] = 0;
			echo '<label>';
			if($value == 0)
			{		
				echo '<input type="radio" '.generateTagElementList($this -> tags).' checked="checked"/>';
			}
			else
			{
				echo '<input type="radio" '.generateTagElementList($this -> tags).'/>';
			}
			echo $this->form->i18n->put($this->context->i18nGroup, 'text_no').'</label>';
		} // end begin();
	
	}
	
	class opfCheckQuestion extends opfComponent
	{
		protected $componentName = 'checkQuestion';
	
		public function begin()
		{
			$this -> getClass();
			if($this->form->getValue($this->name) == 1)
			{
				echo '<input type="checkbox"'.generateTagElementList($this -> tags).' checked="checked" />';
			}
			else
			{
				echo '<input type="checkbox"'.generateTagElementList($this -> tags).' />';
			}
		} // end begin();	
	}
	
	class opfSelect extends opfComponent
	{
		protected $componentName = 'select';
		
		public function begin()
		{
			$this -> getClass();

			echo '<select '.generateTagElementList($this -> tags).'>';
			$this -> displayGroup($this->form->getValue($this->name.'Values', true), $this->form->getValue($this->name));
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
			$values = $this->form->getValue($this->name.'Values', true);
			$selected = $this->form->getValue($this->name);
			
			$this -> componentName = 'radio';
			$this -> getClass();
			
			foreach($values as $id => $value)
			{
				$this -> tags['value'] = $id;
				if($selected == $id)
				{
					$this -> tags['checked'] = 'checked';
					echo '<li><label><input type="radio" '.generateTagElementList($this -> tags).'/> '.htmlspecialchars($value).'</label></li>';
					unset($this -> tags['checked']);
				}
				else
				{
					echo '<li><label><input type="radio" '.generateTagElementList($this -> tags).'/> '.htmlspecialchars($value).'</label></li>';
				}						
			}
			echo '</ul>';
		} // end begin();
	}
	
	class opfFile extends opfComponent
	{
		protected $componentName = 'input';
		
		public function begin()
		{
			$this -> getClass();
			echo '<input type="file"'.generateTagElementList($this -> tags).' />';
		} // end begin();
	
	}

?>
