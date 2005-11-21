<?php
  //  --------------------------------------------------------------------  //
  //                          Open Power Board                              //
  //                        Open Power Template                             //
  //         Copyright (c) 2005 OpenPB team, http://opt.openpb.net/         //
  //  --------------------------------------------------------------------  //
  //  This program is free software; you can redistribute it and/or modify  //
  //  it under the terms of the GNU Lesser General Public License as        //
  //  published by the Free Software Foundation; either version 2.1 of the  //
  //  License, or (at your option) any later version.                       //
  //  --------------------------------------------------------------------  //
  //
  // $Id$

	// parameter flags
	define('OPT_PARAM_REQUIRED', 0);
	define('OPT_PARAM_OPTIONAL', 1);
	// parameter types
	define('OPT_PARAM_ID', 2);
	define('OPT_PARAM_EXPRESSION', 3);
	define('OPT_PARAM_ASSIGN_EXPR', 4);
	define('OPT_PARAM_STRING', 5);
	define('OPT_PARAM_NUMBER', 6);
	define('OPT_PARAM_VARIABLE', 7);

	define('OPT_ROOT', 0);
	define('OPT_TEXT', 1);
	define('OPT_INSTRUCTION', 2);
	define('OPT_EXPRESSION', 3);
	define('OPT_COMPONENT', 4);
	define('OPT_UNKNOWN', 5);

	define('OPT_MASTER', 0);
	define('OPT_ALT', 1);
	define('OPT_ENDER', 2);
	define('OPT_COMMAND', 3);
	
	interface ioptNode
	{
		public function __construct($name, $type, $parent);
		public function getName();
		public function getType();
		public function getBlockCount();	
	}
	
	class optNode implements ioptNode, IteratorAggregate
	{
		private $name;
		private $type;
		private $blocks = array();
		private $parent;
		
		private $storedBlock;
		
		public function __construct($name, $type, $parent)
		{
			$this -> name = $name;
			$this -> type = $type;
			$this -> parent = $parent;
		} // end __construct();
		
		public function addItem($item)
		{
			$this -> blocks[] = $item;		
		} // end addBlock();
		
		public function getName()
		{
			return $this -> name;
		} // end getName();
	
		public function getType()
		{
			return $this -> type;
		} // end getType();
		
		public function getParent()
		{
			return $this -> parent;
		} // end getParent();

		public function getBlockCount()
		{
			return count($this -> blocks);
		} // end getBlockCount();
		
		public function getFirstBlock()
		{
			return $this -> blocks[0];
		} // end getFirstBlock();
		
		public function storeBlock(optBlock $block)
		{
			$this -> storedBlock = $block;
		} // end storeBlock();
		
		public function restoreBlock()
		{
			return $this -> storedBlock;
		} // end restoreBlock();
		
		public function getIterator()
		{
			return new ArrayIterator($this -> blocks);		
		} // end getIterator();

		public function __toString()
		{
			return $this -> type.':'.$this -> name;
		} // end __toString();
	}
	
	class optTextNode implements ioptNode
	{
		private $name;
		private $type;
		private $text;
		private $parent;
		
		public function __construct($name, $type, $parent)
		{
			$this -> name = $name;
			$this -> type = $type;
			$this -> parent = $parent;
			$this -> text = '';
		} // end __construct();
		
		public function addItem($item)
		{
			$this -> text .= $item;	
		} // end addBlock();
		
		public function getName()
		{
			return $this -> name;
		} // end getName();
	
		public function getType()
		{
			return $this -> type;
		} // end getType();
		
		public function getParent()
		{
			return $this -> parent;
		} // end getParent();

		public function getBlockCount()
		{
			return 0;
		} // end getBlockCount();

		public function storeBlock(optBlock $block)
		{
			$this -> error(E_USER_ERROR, 'Unexpected `'.$this->getType().'`!', 113);
		} // end storeBlock();
		
		public function restoreBlock()
		{
			$this -> error(E_USER_ERROR, 'Unexpected `'.$this->getType().'`!', 113);
		} // end restoreBlock();

		public function __toString()
		{
			return $this -> text;
		} // end __toString();
	}
	
	class optBlock implements IteratorAggregate
	{
		private $name;
		private $attributes;
		private $type;
		private $nodes = array();
		
		public function __construct($name, $attributes = NULL, $type = OPT_COMMAND)
		{
			$this -> name = $name;
			$this -> attributes = $attributes;
			$this -> type = $type;
		} // end __construct();
		
		public function addNode(ioptNode $node)
		{
			$this -> nodes[] = $node;		
		} // end addBlock();
		
		public function getName()
		{
			return $this -> name;
		} // end getName();
	
		public function hasAttributes()
		{
			return $this -> attributes != NULL;
		} // end hasAttributes();
		
		public function getAttributes()
		{
			return $this -> attributes;
		} // end getAttributes();

		public function getType()
		{
			return $this -> type;
		} // end getAttributes();

		public function hasChildNodes()
		{
			return count($this -> nodes) > 0;
		} // end hasChildNodes();
		
		public function getIterator()
		{
			return new ArrayIterator($this -> nodes);		
		} // end getIterator();

		public function __toString()
		{
			return $this -> name;
		} // end __toString();
	}

	// Instruction tree classes
	require_once(OPT_DIR.'opt.instructions.php');

	// Main compiler
	final class optCompiler
	{
		public $tpl;
		public $nestingNames;
		public $nestingLevel;
		public $genericBuffer;		
		public $processors;
		public $translator;
		public $parseRun;

		public function __construct($tpl)
		{
			// Init the compiler
			if($tpl instanceof optCompiler)
			{
				$this -> tpl = $tpl -> tpl;
				$this -> nestingNames = $tpl -> nestingNames;
				$this -> nestingLevel = $tpl -> nestingLevel;
			}
			else
			{
				// let's say it's an instance of optClass or optApi
				$this -> tpl = $tpl;
			}
			
			// Register plugin instructions
			if($this -> tpl -> compileCode != '')
			{
				eval($this -> tpl -> compileCode);
			}
			# PLUGIN_AUTOLOAD
			else
			{
				if($this -> tpl -> plugins != NULL)
				{
					require($this -> tpl -> plugins.'compile.php');				
				}
			}
			# /PLUGIN_AUTOLOAD
			$this -> processors['generic'] = new optInstruction($this);
			# COMPONENTS
			$this -> processors['component'] = new optComponent($this);
			# /COMPONENTS
			// Translate the instructions
			foreach($this -> tpl -> control as $class)
			{
				$instruction = new $class($this);
				$data = $instruction -> configure();
				$this -> processors[$data[0]] = $instruction;
				
				foreach($data as $name => $type)
				{
					$this -> translator[$name] = $type;		
				}
			}
			$this -> parseRun = 0;
		} // end __construct();

		public function parse($code)
		{
			static $regex;

			if(count($this -> tpl -> codeFilters['pre']) > 0)
			{
				foreach($this -> tpl -> codeFilters['pre'] as $name)
				{
					// @ used because of stupid notice
					// "Object of class opt_template to string conversion".
					// Whatever it means, I couldn't recognize, why PHP does such things.
					$this -> code = @$name($code, $this -> tpl);
				}
			}

			if($regex == NULL)
			{
				if($this -> tpl -> xmlsyntaxMode == 1)
				{
					$regex = '\<\!\-\-.+\-\-\>|<\!\[CDATA\[|\]\]>|'.$regex;
					$this -> tpl -> delimiters[] = '\<(\/?)opt\:(.*?)()\>';
					$this -> tpl -> delimiters[] = '\<()opt\:(.*?)(\/)\>';
					$this -> tpl -> delimiters[] = 'opt\:put\=\"(.*?[^\\\\])\"';
				}
				$regex = implode('|', $this -> tpl -> delimiters);
			}

			// tokenizer
			preg_match_all('#({\*.+?\*\}|'.$regex.'|(.?))#si', $code, $result, PREG_PATTERN_ORDER);
			foreach($result as $i => &$void)
			{
				if($i != 0)
				{
					unset($result[$i]);
				}
			}
			$output = $this -> tpl -> captureTo.' \'';
			if(!$this -> parseRun)
			{				
				// register output
				foreach($this -> processors as $name => $processor)
				{
					$processor -> setOutput($output);
				}
				$this -> parseRun = 1;
			}
			else
			{
				$this -> parseRun = 2;
			}
			
			// initialize the tree
			$root = $current = new optNode(NULL, OPT_ROOT, NULL);
			$rootBlock = $currentBlock = new optBlock(NULL);
			$root -> addItem($rootBlock);
			$textAssign = 0;
			$commented = 0;
			$literal = 0;
			foreach($result[0] as $i => $item)
			{
				// comment usage
				if(strlen($item) > 1)
				{
					if(preg_match('/{\*.+?\*\}/s', trim($item))|| preg_match('/\<\!\-\-.+\-\-\>/s', $item))
					{
						continue;
					}
					// a command
					
					// literal processing
					if($literal == 1)
					{
						
						if($item != '{/literal}')
						{
							$item = str_replace(array(
								'\\',
								'\''
								),
								array(
								'\\\\',
								'\\\''
								), $item
							);
						
							$text -> addItem($item);
							$textAssign = 1;							
						}
						else
						{
							$literal = 0;
						}
						continue;
					}
					
					if($item == '{literal}' && $literal == 0)
					{
						$literal = 1;
						continue;
					}

					$textAssign = 0;

					// grep the data
					$sortMatches = array(0 => '', 1 => '', 2 => '');
					preg_match('/'.$regex.'/', $item, $matches);

					$foundCommand = 0;
					foreach($matches as $id => $val)
					{
						$val = trim($val);
						if($val != '')
						{
							if($val == '/')
							{
								if(!$foundCommand)
								{
									$sortMatches[0] = '/';
								}
								else
								{
									$sortMatches[2] = '/';
								}
							}
							elseif($id != 0 )
							{
								$sortMatches[1] = $val;
								$foundCommand = 1;
							}
						}
					}
					if(preg_match('/^(([a-zA-Z0-9\_]+)([= ]{1}(.*))?)$/', $sortMatches[1], $found))
					{
						// we have an instruction
						$realname = $found[2];
						if($sortMatches[0] == '/')
						{					
							$found[2] = '/'.$found[2];
						}
						$found[6] = $item;

						// general instructions
						if(isset($this -> translator[$found[2]]))
						{
							switch($this -> translator[$found[2]])
							{
								case OPT_COMMAND:
									$node = new optNode($found[2], OPT_INSTRUCTION, $current);
									$node -> addItem(new optBlock($found[2], $found, OPT_COMMAND));
									$currentBlock -> addNode($node);
									break;
								case OPT_MASTER:
									$current -> storeBlock($currentBlock);
									$current = new optNode($found[2], OPT_INSTRUCTION, $current);
									$currentBlock -> addNode($current);
									$currentBlock = new optBlock($found[2], $found, OPT_MASTER);
									$current -> addItem($currentBlock);
									break;
								case OPT_ALT:
									$currentBlock = new optBlock($found[2], $found, OPT_ALT);
									$current -> addItem($currentBlock);
									break;
								case OPT_ENDER:
									$currentBlock = new optBlock($found[2], $found, OPT_ENDER);
									$current -> addItem($currentBlock);
									$current = $current -> getParent();
									if(!is_object($current))
									{
										$this -> tpl -> error(E_USER_ERROR, 'Unexpected enclosing statement: `'.$found[2].'`!', 113);
									}
									$currentBlock = $current -> restoreBlock();
									break;							
							}
						}
						# COMPONENTS
						// components, and other shit
						elseif($realname == 'component' || isset($this -> tpl -> components[$realname]))
						{
							if($sortMatches[0] == '/')
							{
								$currentBlock = new optBlock($found[2], $found);
								$current -> addItem($currentBlock);
								$current = $current -> getParent();
								if(!is_object($current))
								{
									$this -> tpl -> error(E_USER_ERROR, 'Unexpected enclosing statement: `'.$found[2].'`!', 113);
								}
								$currentBlock = $current -> restoreBlock();
							}
							else
							{
								$current -> storeBlock($currentBlock);
								$current = new optNode($realname, OPT_COMPONENT, $current);
								$currentBlock -> addNode($current);
								$currentBlock = new optBlock($realname, $found);
								$current -> addItem($currentBlock);
							}
						}
						# /COMPONENTS
						else
						{
							// here come the undefined command. The instruction programmer may do with them whatever he wants
							// the compiler is going to recognize, what sort of command is it.
							$ending = substr($found[2], strlen($found[2]) - 4, 4);
							if($sortMatches[0] == '/')
							{
								// ending command, like in XML: /command
								$currentBlock = new optBlock($found[2], $found, OPT_ENDER);
								$current -> addItem($currentBlock);
								$current = $current -> getParent();
								if(!is_object($current))
								{
									$this -> tpl -> error(E_USER_ERROR, 'Unexpected enclosing statement: `'.$found[2].'`!', 113);
								}
								$currentBlock = $current -> restoreBlock();
							}
							elseif($sortMatches[2] == '/')
							{
								// standalone command, like XML: command/ 
								$node = new optNode($found[2], OPT_UNKNOWN, $current);
								$node -> addItem(new optBlock($found[2], $found, OPT_COMMAND));
								$currentBlock -> addNode($node);
							}
							elseif($ending == 'else')
							{
								// alternative command, doesn't exist in XML: commandelse
								$currentBlock = new optBlock($found[2], $found, OPT_ALT);
								$current -> addItem($currentBlock);
							}
							else
							{
								// beginning command: command
								$current -> storeBlock($currentBlock);
								$current = new optNode($realname, OPT_UNKNOWN, $current);
								$currentBlock -> addNode($current);
								$currentBlock = new optBlock($realname, $found, OPT_MASTER);
								$current -> addItem($currentBlock);
							}
						}
					}
					else
					{
						// we have an expression
						$node = new optNode(NULL, OPT_EXPRESSION, $current);
						$node -> addItem(new optBlock(NULL, $sortMatches[1]));
						$currentBlock -> addNode($node);
					}
				}
				else
				{
					// text item
					if($textAssign == 0)
					{
						$text = new optTextNode(NULL, OPT_TEXT, $current);
						$currentBlock -> addNode($text);
					}
					// character escaping
					if($item == '\'')
					{
						$item = '\\\'';
					}
					if($item == '\\')
					{
						$item = '\\\\';
					}
					$text -> addItem($item);
					$textAssign = 1;
				}
			
			}
			// execute the tree
			$this -> processors['generic'] -> nodeProcess($root);
			if($this->parseRun < 2)
			{
				$code = $output.'\';';
			}
			// apply postfilters
			if(count($this -> tpl -> codeFilters['post']) > 0)
			{
				foreach($this -> tpl -> codeFilters['post'] as $name)
				{
					$code = $name($code, $this -> tpl);
				}
			}
			$this -> parseRun--;
			return $code;
		} // end parse();

		public function compileBlock($name)
		{		
			if(is_array($name))
			{
				$cnt = count($name);
				return '$__'.$name[$cnt-2].'_val[\''.$name[$cnt-1].'\']';			
			}
			$value = substr($name, 1, strlen($name) - 1);
			if($name{0} == '#')
			{
				// configuration blocks				
				return '$this -> '.$value;
			}
			elseif($name{0} == '@')
			{
				// variables
				return '$this -> vars[\''.$value.'\']';
			}
			else
			{
				return '$this->data[\''.$name.'\']';
			}
			return FALSE;
		} // end compileBlock();

		public function compileExpression($expr, $allowAssignment=0)
		{
			/* Based on Smarty */
			preg_match_all('/(?:
        			"[^"\\\\]*(?:\\\\.[^"\\\\]*)*" |
        			`[^`\\\\]*(?:\\\\.[^`\\\\]*)*` |
					\-?0[xX][0-9a-fA-F]+			|
					[0-9]+\.?[0-9]+					|
					\$opt\.[a-zA-Z0-9\_\.]+				|
					\-\>|!==|===|==|!=|<>|<<|>>|<=|>=|\&\&|\|\||\(|\)|,|\!|\^|=|\&|\~|<|>|\||\%|\+|\-|\/	|\[|\]|\.|\:\:|
					[\$\#\@]?[a-zA-Z0-9\_\@]+		
					)/x', $expr, $match);

			$tokens = $match[0];
			$brackets = 0;
			
			// word tokens conversion table
			$wordTokens = array(
				'eq' => '==',
				'ne' => '!=',
				'neq' => '!=',
				'lt' => '<',
				'le' => '<=',
				'lte' => '<=',
				'gt' => '>',
				'ge' => '>=',
				'gte' => '>=',
				'and' => '&&',
				'or' => '||',
				'xor' => 'xor',
				'not' => '!',
				'mod' => '%',
				'div' => '/',
				'add' => '+',
				'sub' => '-',
				'mul' => '*'
			);
			
			$state = array(
				// previous token
				'prev' => NULL,
				// temporary previous token
				'pb' => 0,
				// brackets nesting
				'brackets' => 0,
				// quad brackets nesting
				'qbrackets' => 0,
				// have we opened a function?
				'function_opened' => 0,
				// quad bracket constant token conversion
				'qbc'	=> 0,
				// where has the section block begun?
				'sstart' => -1,
				// what sort of section token should be next?
				'spt' => 1,
				// section block buffer
				'sb' => '',
				// is the assignment made
				'assignment' => 0,
				// is special function "apply" began?
				'apply_began' => 0
			);
			$qbc = 0;
			foreach($tokens as $i => &$token)
			{
				if(!isset($tokens[$i]))
				{
					continue;
				}
				$state['pb'] = $token;
				/*
					expression blocks parsing
				*/
				switch($token)
				{
				// Non-word tokens
					case '!':
					case '%':
					case '!==':
					case '==':
					case '===':
					case '>':
					case '<':
					case '!=':
					case '<>':
					case '<<':
					case '>>':
					case '<=':
					case '>=':
					case '&&':
					case '||':
					case '|':
					case '^':
					case '&':
					case '~':
					case ',':
					case '+':
					case '-':
					case '*':
					case '/':
					case 'xor':
						break;
					case '.':
						$token = '';
						break;
					case '[':
						$state['qbrackets']++;
						$qbc = 1;
						break;
					case ']':
						$state['qbrackets']--;
						break;
					case ')':
						$state['brackets']--;
						if($state['function_opened'] == -1)
						{
							$state['function_opened'] = 0;
						}
						break;
					case '(':
						if($state['function_opened'] == 1)
						{
							$token = '($this ';
							$state['function_opened'] = -1;
						}
						elseif($state['function_opened'] == 2)
						{
							$token = '(';
							$state['function_opened'] = 0;
						}
						$state['brackets']++;
						break;
					case '->':
						break;
					case '.':
						unset($tokens[$i]);
						break;
					case '::':
						$token = '.';
						break;
				// Word tokens
					case 'eq':
					case 'ne':
					case 'neq':
					case 'lt':
					case 'le':
					case 'lte':
					case 'gt':
					case 'ge':
					case 'gte':
					case 'and':
					case 'or':
					case 'not':
					case 'mod':			
					case 'div':					
					case 'add':						
					case 'sub':				
					case 'mul':
						$token = $wordTokens[$token];
						break;
					// IS EXPRESSION
					case '=':
					case 'is':
						if($allowAssignment)
						{
							$state['assignment'] = 1;
							$token = '=';
						}
						else
						{
							$this -> tpl -> error(E_USER_ERROR, 'Assignment operators not allowed in '.$expr, 101);
						}
						break;
					// variables, blocks, functions etc. parsing
					default:
						if(@(trim($tokens[$i+1]) == '('))
						{
							$token = trim($token);
							if($token == 'apply' && $this -> tpl -> i18nType == 1)
							{
								if($this -> tpl -> lang['applyClass'] != NULL)
								{
									$token = $this -> tpl -> lang['applyClass'].'->apply';								
								}
								else
								{
									$token = 'opt'.$this -> tpl -> functions[$token];
								}
								$state['apply_began'] = 1;
								$state['function_opened'] = 1;
							}
							elseif(function_exists('opt'.(@$this -> tpl -> functions[$token])))
							{
								if($token == 'apply')
								{
									$token = 'optPredefApply';
									$state['apply_began'] = 1;
								}
								else
								{
									$token = 'opt'.$this -> tpl -> functions[$token];
								}
								$state['function_opened'] = 1;
							}
							elseif(isset($this -> tpl -> phpFunctions[$token]))
							{
								$token = $this -> tpl -> phpFunctions[$token];
								$state['function_opened'] = 2;
							}
							elseif(@(trim($tokens[$i-1]) == '->'))
							{
								// we have a method
								$state['function_opened'] = 2;
							}
							else
							{
								$this -> tpl -> error(E_USER_ERROR, 'Function '.$token.' not defined in expression '.$expr, 102);
							}
						}
						elseif($token{0} == '$')
						{
							$token = substr($token, 1, strlen($token) - 1);

							// is it a language block?
							if(strpos($token, '@') !== FALSE)
							{
								$ns = explode('@', $token);

								if($this -> tpl -> showWarnings == 1)
								{
									if(!isset($this -> tpl -> lang[$ns[0]][$ns[1]]))
									{
										$this -> tpl -> error(E_USER_WARNING, 'The language block {'.$name.'} does not exist.', 151);
									}
								}

								if($state['apply_began'] == 1)
								{
									$token = '\''.$ns[0].'\',\''.$ns[1].'\'';
									$state['apply_began'] = 0;								
								}
								else
								{
									// custom block
									if($this -> tpl -> i18nType == 1)
									{
										$token = sprintf($this -> tpl -> lang['template'], $ns[0], $ns[1]);									
									}
									else
									{
										$token = '$this -> lang[\''.$ns[0].'\'][\''.$ns[1].'\']';
									}
								}
							}
							// or maybe an $opt block?
							elseif(preg_match('/opt\.[a-zA-Z0-9\_\.]+/', $token))
							{
								$token = $this -> compileOpt(explode('.', $token));
							}
							else
							{
							// or sth else?							
								if(@($this -> nestingLevel['section'] > 0 && $tokens[$i+1] == '.'))
								{
									// this must be a section block
									$itr = $i+1;
									$supposed = 0;
									$section = array(0 => ltrim($token, '$'));
									while(1)
									{
										if(!isset($tokens[$itr]))
										{
											break;										
										}
										if($tokens[$itr] == '.' && $supposed == 0)
										{
											$supposed = 1;
											unset($tokens[$itr]);
										}
										elseif(preg_match('/([a-zA-Z0-9\_]+?)/', $tokens[$itr]) && $supposed == 1)
										{
											$section[] = $tokens[$itr];
											$supposed = 0;
											unset($tokens[$itr]);
										}
										else
										{
											break;
										}
										$itr++;
									}
									$token = $this -> compileBlock($section);
								}
								else
								{
									$token = '$this -> data[\''.$token.'\']';
								}
							}
						}
						elseif($token{0} == '#' || $token{0} == '@')
						{
							$data = $this -> compileBlock($token);
							if($data === FALSE)
							{
								$this -> tpl -> error(E_USER_ERROR, 'Unknown block type: '.$token, 103);
							}
							$token = $data;
						}
						else
						{
							// constant value, maybe it's a part of a table block name...
							if($state['prev'] == '.')
							{
								if((!preg_match('/^((0[xX][0-9a-fA-F]+)|[0-9 \-\.]*)$/', (string)$token)) && $token{0} != '"' && $token{strlen($token) - 1} != '"')
								{
									$token = '[\''.$token.'\']';
								}
								else
								{
									$token = '['.$token.']';
								}
							}
							elseif($state['prev'] == '->')
							{
								$token = $token;
							}
							elseif($token{0} == '`' && $token{strlen($token) - 1} == '`')
							{
								$token = '\''.str_replace('\\`', '`',trim($token, '`')).'\'';
							}					
							elseif((!preg_match('/^((0[xX][0-9a-fA-F]+)|[0-9 \-\.]*)$/', (string)$token)) && $token{0} != '"' && $token{strlen($token) - 1} != '"')
							{
								$token = '\''.$token.'\'';
							}

						}
						if($state['function_opened'] == -1)
						{
							// we have an OPT function, which passes the parser as the first parameter...
							$token = ', '.$token;
							$state['function_opened'] = 0;
						}
						
        		} // end of switch();
        		if($qbc == 1)
        		{
        			$qbc++;
        		}
        		else
        		{
        			$qbc = 0;
        		}
        		// loop end, save recent state as previous
        		$state['prev'] = $state['pb'];
        		$tokens[$i] = $token;
			}
			
			// if the section token was the last part of the block...

			if($state['brackets'] != 0)
			{
				$this -> tpl -> error(E_USER_ERROR, 'Expression syntax error in '.$expr.': brackets not closed.', 104);
			}
			if($allowAssignment)
			{
				return array(0 => implode(' ', $tokens), $state['assignment']);
			}
			return implode('', $tokens);
		} // end compileExpression();

		private function compileOpt($namespace)
		{
			switch($namespace[1])
			{
				case 'section':
					$sectionDirection = $this -> processors['section'] -> getSectionDirection($namespace[2]);
					if($sectionDirection === FALSE)
					{
						$this -> tpl -> error(E_USER_ERROR, 'Unknown OPT section in $'.implode('.', $namespace), 112);
					}
					switch($namespace[3])
					{
						case 'count':
							return 'count('.'$this -> data[\''.$namespace[2].'\'])';
						case 'id':
							return '$__'.$namespace[2].'_id';
						case 'size':
							return 'count($__'.$namespace[2].'_val)';
						case 'first':
							if($sectionDirection == 0)
							{
								return '($__'.$namespace[2].'_id == 0)';
							}
							return '($__'.$namespace[2].'_id == count($this -> data[\''.$namespace[2].'\']) - 1)';
						case 'last':
							if($sectionDirection == 0)
							{
								return '($__'.$namespace[2].'_id == count($this -> data[\''.$namespace[2].'\']) - 1)';
							}
							return '($__'.$namespace[2].'_id == 0)';
						default:
							$this -> tpl -> error(E_USER_ERROR, 'Unknown OPT section command: '.$namespace[3], 105);
					}
				case 'capture':				
					return '$this -> capture[\''.$namespace[2].'\']';
				case 'get':
					return '$_GET[\''.$namespace[2].'\']';
				case 'post':
					return '$_POST[\''.$namespace[2].'\']';
				case 'cookie':
					return '$_COOKIE[\''.$namespace[2].'\']';
				case 'session':
					return '$_SESSION[\''.$namespace[2].'\']';
				case 'server':
					return '$_SERVER[\''.$namespace[2].'\']';
				case 'env':
					return '$_ENV[\''.$namespace[2].'\']';
				case 'request':
					return '$_REQUEST[\''.$namespace[2].'\']';
				case 'now':
					return 'time()';
				case 'const':
					if(defined($namespace[2]))
					{
						return $namespace[2];
					}
					else
					{
						$this -> tpl -> error(E_USER_ERROR, 'Unknown constant: '.$namespace[2], 106);
					}
				case 'version':
					return 'OPT_VERSION';
				default:
					$this -> tpl -> error(E_USER_ERROR, 'Unknown OPT command: '.$namespace[1], 107);	
			}
		} // end compileOpt();
		
		/*
		 * INSTRUCTION WRITING TOOLS
		 */

		public function checkNestingLevel($name)
		{
			if(!isset($this -> nestingLevel[$name]))
			{
				$this -> nestingLevel[$name] = 0;
			}
		
			if($this -> nestingLevel[$name] > OPT_MAX_NESTING_LEVEL)
			{
				$this -> tpl -> error(E_USER_ERROR, 'Nesting level too deep for '.$name.' element (max level: '.OPT_MAX_NESTING_LEVEL.')', 108);
			}
		} // end checkNestingLevel();

		public function getDynamic($cpl, $code)
		{
			# OUTPUT_CACHING
			if($cpl -> tpl -> getStatus() == true)
			{
			# /OUTPUT_CACHING
				return $code;
			}
			# OUTPUT_CACHING
			return '\'; $this -> cacheOutput[] = ob_get_contents(); /* #@#DYNAMIC#@# */ '.$code.' /* #@#END DYNAMIC#@# */ ob_start(); '.$cpl -> tpl -> captureTo.' \'';
			# /OUTPUT_CACHING
		} // end getDynamic();

		public function parametrize($matches, &$config)
		{
			if(!isset($matches[4]))
			{
				$matches[4] = '';
				$matches[3] = '=';
			}

			if($matches[3]{0} == '=')
			{
				// use non-named parameter parsing
				$params = array();
				if(count($config) == 0)
				{
					// no parameters passed. Now the script wonders, why someone has called this method.
					$config = array();
					return NULL;
				}
				elseif(count($config) == 1)
				{
					// only one parameter needed, take all the string as it
					$params[0] = $matches[4];
				}
				else
				{
					// split the param string into parameters
					$quotes = 0;
					$pi = 0;
					$buffer = '';
					$test = 1;
					for($i = 0; $i < strlen($matches[4]); $i++)
					{				
						if($i - 1 >= 0)
						{
							$test = $matches[4]{$i - 1} != '\\';
						}						
						if($matches[4]{$i} == '"' && $test)
						{
							$quotes = !$quotes;
						}
						if($matches[4]{$i} == ';' && $quotes == 0)
						{
							$params[$pi] = trim($buffer);
							$buffer = '';
							$pi++;
							continue;
						}
						$buffer .= $matches[4]{$i};
					}
					if($buffer != '')
					{
						$params[$pi] = trim($buffer);
					}
				}
				$first = reset($config);
				if(count($params) == 0 && $first[0] == OPT_PARAM_OPTIONAL)
				{
					foreach($config as $name => $par)
					{
						$config[$name] = $par[2];
					}
					return NULL;
				}

				$pi = 0;
				$optional = 0;
				// process everything
				foreach($config as $name => $par)
				{
					if($par[0] == OPT_PARAM_OPTIONAL)
					{
						$optional = 1;
					}
					
					if(!isset($params[$pi]))
					{
						// parameter not set
						if($optional == 1)
						{
							// pass the default value
							$config[$name] = $par[2];
				
						}
						else
						{
							return -1;
						}		
					}
					else
					{
						if($params[$pi] == '!x')
						{
							if($optional == 0)
							{
								$this -> tpl -> error(E_USER_ERROR, 'Cannot use !x marker for a required parameter.', 112);							
							}
							// force the default value
							$config[$name] = $par[2];
							$pi++;
							continue;
						}

						// check the format of the parameter
						switch($par[1])
						{
							case OPT_PARAM_ID:
								if(preg_match('/[a-zA-Z\_]{1}[a-zA-Z0-9\_]*/', $params[$pi]))
								{
									$config[$name] = trim($params[$pi], '"');
								}
								else
								{
									return $pi+1;
								}
								break;
							case OPT_PARAM_EXPRESSION:
								$config[$name] = $this -> compileExpression($params[$pi]);
								break;
							case OPT_PARAM_ASSIGN_EXPR:
								$config[$name] = $this -> compileExpression($params[$pi], true);
								$config[$name] = $config[$name][0];
								break;
							case OPT_PARAM_STRING:
								if($params[$pi]{0} == '"' && $params[$pi]{strlen($params[$pi]) - 1} == '"')
								{
									$config[$name] = trim($params[$pi], '"');
								}
								elseif(preg_match('/[a-zA-Z\_]?[a-zA-Z0-9\_]+/', $params[$pi]))
								{
									$config[$name] = $params[$pi];
								}
								else
								{
									return $pi+1;
								}
								break;
							case OPT_PARAM_NUMBER:
								if(preg_match('/(0[xX][0-9a-fA-F]+)|([0-9]+(\.[0-9]+)?)/', $params[$pi]))
								{
									$config[$name] = $params[$pi];
								}
								else
								{
									return $pi+1;
								}
								break;
							case OPT_PARAM_VARIABLE:
								if(preg_match('/\@([a-zA-Z0-9\_]+)/', $params[$pi], $got))
								{
									$config[$name] = '$this -> vars[\''.$got[1].'\']';
								}
								else
								{
									return $pi+1;
								}
								break;
							default:
								$this -> tpl -> error(E_USER_ERROR, 'Invalid parameter type: '.$par[1].' for `'.$name.'`. Check your instruction code.', 109);		
						}			
					}
					$pi++;			
				}
			}
			else
			{
				// use named parameters
				preg_match_all('#([a-zA-Z0-9\_]+)\="((.*?)[^\\\\])"#s', $matches[4], $found);
				
				foreach($config as $name => $par)
				{
					if(($pi = array_search($name, $found[1])) !== FALSE)
					{
						// ok, the parameter is defined... try to parse it
						switch($par[1])
						{
							case OPT_PARAM_ID:
								if(preg_match('/[a-zA-Z\_]?[a-zA-Z0-9\_]+/', $found[2][$pi]))
								{
									$config[$name] = $found[2][$pi];
								}
								else
								{
									return $i;
								}
								break;
							case OPT_PARAM_EXPRESSION:
								$config[$name] =  $this -> compileExpression($found[2][$pi]);
								break;
							case OPT_PARAM_ASSIGN_EXPR:
								$config[$name] = $this -> compileExpression($found[2][$pi], true);
								$config[$name] = $config[$name][0];
								break;
							case OPT_PARAM_STRING:
								$config[$name] = preg_replace('#[^\\]\\"#is', '"', $found[2][$pi]);
								break;
							case OPT_PARAM_NUMBER:
								if(preg_match('/(0[xX][0-9a-fA-F]+)|([0-9]+(\.[0-9]+)?)/', $found[2][$pi]))
								{
									$config[$name] = $found[2][$pi];
								}
								else
								{
									return $pi;
								}
								break;
							case OPT_PARAM_VARIABLE:
								if(preg_match('/\@([a-zA-Z0-9\_]+)/', $found[2][$pi], $got))
								{
									$config[$name] = '$this -> vars[\''.$got[1].'\']';
								}
								else
								{
									return $pi;
								}
								break;
							default:
								$this -> tpl -> error(E_USER_ERROR, 'Invalid parameter type: '.$par[1].' for `'.$name.'`. Check your instruction code.', 109);		
						}				
					}
					else
					{
						// set default value
						if($par[0] == OPT_PARAM_REQUIRED)
						{
							return -1;
						}
						else
						{
							$config[$name] = $par[2];
						}
					}				
				}
			}
			return NULL;
		} // end parametrize();

		public function parametrizeError($name, $number)
		{
			if($number === NULL)
			{
				return 0;
			}
			if($number < 0)
			{
				$this -> tpl -> error(E_USER_ERROR, 'Wrong parameter count for `'.$name.'` instruction!', 110);
			}
			else
			{
				$this -> tpl -> error(E_USER_ERROR, 'Invalid parameter #'.($number+1).' in `'.$name.'` instruction!', 111);
			}
		} // end parametrizeError();
	}
?>
