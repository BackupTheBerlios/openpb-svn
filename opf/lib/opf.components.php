<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                          Open Power Forms                              //
  //         Copyright (c) 2005 OpenPB team, http://www.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //

	class opfComponent implements ioptComponent
	{
		public $name;
		public $response;
		public $context;
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
				$this -> response = $tpl;
				$this -> context = $tpl -> getContext();
				
				// Set the OPF form
				if(isset($this -> response -> data[$formName]))
				{
					$this -> form = $this -> response -> data[$formName];
				}
				else
				{
					throw new opfException('No form block defined for templates: '.$formName.'!');
				}
				$this -> design = $this -> response -> data['opfDesign'];
			}
			else
			{
				throw new opfException('Using an OPF component when not in a form!');
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
				$this -> response -> vars[$msg] = $this -> form -> errorMessages[$this->name];
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
			echo '<label>'.$this->form->i18n->put($this->context->i18nGroup, 'text_yes');
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
?>
