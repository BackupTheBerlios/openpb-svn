<?php

	class optCompilerTester extends optInstruction
	{
		private $level = 0;
		public function configure()
		{
			$this -> sectionDirection = array();
			return array(
				// processor name
				0 => 'compiler',
				// instructions
				'compiler' => OPT_MASTER,
				'/compiler' => OPT_ENDER
			);
		} // end configure();
		
		public function instructionNodeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				switch($block -> getName())
				{
					case 'compiler':
							$this -> output .= "BEGIN COMPILER SESSION\r\n";
							foreach($block as $subNode)
							{
								$this -> hardcoreTreeProcess($subNode);
							}
							break;
					case '/compiler':
							$this -> output .= "END COMPILER SESSION";
							break;
				}
			}
		} // end process();

		public function hardcoreTreeProcess(ioptNode $node)
		{
			foreach($node as $block)
			{
				$attributes = $block->getAttributes();
				if(!isset($attributes[3]))
				{
					$attributes[3] = '';
				}
				switch($block -> getType())
				{
					case OPT_MASTER:
						$this -> output .= str_repeat('.',$this->level).'MASTER: '.$block->getName()." (".$attributes[3].")(".$node->getName().")\r\n";
						$this -> level++;
						break;
					case OPT_ENDER:
						$this -> level--;
						$this -> output .= str_repeat('.',$this->level).'ENDER: '.$block->getName()." (".$attributes[3].")(".$node->getName().")\r\n";
						break;
					case OPT_COMMAND:
						$this -> output .= str_repeat('.',$this->level).'CMD: '.$block->getName()." (".$attributes[3].")(".$node->getName().")\r\n";
						break;
					case OPT_ALT:
						$this -> output .= str_repeat('.',$this->level-1).'ALT: '.$block->getName()." (".$attributes[3].")(".$node->getName().")\r\n";
						break;				
				}
				foreach($block as $subNode)
				{
					if($node -> getType() != OPT_TEXT)
					{
						$this -> hardcoreTreeProcess($subNode);
					}
				}
			}
		} // end defaultTreeProcess();	
	}

	class optTestParser extends optApi
	{
		public function __construct()
		{
			$this -> control = array(0 => 'optCompilerTester');
			$this -> compiler = new optCompiler($this);
		} // end __construct();
	
		public function codeParse($code)
		{
			$this -> captureTo = 'echo';
			$this -> captureDef = 'echo';
			return $this -> compiler -> parse($code);
		} // end doParse();
	
		protected function doInclude($name, $nestingLevel)
		{
			// actually do nothing at the moment
		} // end doInclude();
	}

	class optCompilerTest extends PHPUnit_TestCase
	{
		private $opt;
		
		public function __construct($name)
		{
			$this -> PHPUnit_TestCase($name);
		} // end __construct();
		
		public function setUp()
		{
			$this -> opt = new optTestParser;
		} // end setUp();
		
		public function tearDown()
		{
			unset($this -> opt);		
		} // end tearDown();
		
		public function testExpressionStrings()
		{
			$this -> assertEquals('"A string"', $this->opt->compiler->compileExpression('"A string"'));		
		} // end testExpressionStrings();
		
		public function testExpressionEscapedStrings()
		{
			$this -> assertEquals('"A \"string"', $this->opt->compiler->compileExpression('"A \"string"'));		
		} // end testExpressionEscapedStrings();
		
		public function testExpressionRAStrings()
		{
			$this -> assertEquals('\'A string\'', $this->opt->compiler->compileExpression('`A string`'));		
		} // end testExpressionRAStrings();
		
		public function testExpressionRAEscapedStrings()
		{
			$this -> assertEquals('\'A "string\'', $this->opt->compiler->compileExpression('`A "string`'));		
		} // end testExpressionRAEscapedStrings();
		
		public function testExpressionRAEscapedStringsRA()
		{
			$this -> assertEquals('\'A `string\'', $this->opt->compiler->compileExpression('`A \`string`'));		
		} // end testExpressionRAEscapedStrings();
		
		public function testExpressionNonOperatorStrings()
		{
			$this -> assertEquals('\'edit\'', $this->opt->compiler->compileExpression('edit'));		
		} // end testExpressionNonOperatorStrings();
		
		public function testExpressionOperatorStrings()
		{
			$this -> assertEquals('+', $this->opt->compiler->compileExpression('add'));		
		} // end testExpressionOperatorStrings();
		
		public function testExpressionNumbers()
		{
			$this -> assertEquals('12345', $this->opt->compiler->compileExpression('12345'));		
		} // end testExpressionNumbers();
		
		public function testExpressionFloatNumbers()
		{
			$this -> assertEquals('12345.67', $this->opt->compiler->compileExpression('12345.67'));		
		} // end testExpressionFloatNumbers();
		
		public function testExpressionHexadecimalNumbers()
		{
			$this -> assertEquals('0x54A6B', $this->opt->compiler->compileExpression('0x54A6B'));		
		} // end testExpressionHexadecimalNumbers();
		
		public function testExpressionLostBracketTest()
		{
			try
			{
				$this->opt->compiler->compileExpression('($a + ($b - $c) * $d');
			}
			catch(optException $exception)
			{
				return 1;
			}
			$this -> fail('Lost bracket exception not returned!');
		} // end testExpressionLostBracketTest();
		
		public function testExpressionTablePHPSyntax()
		{
			$this -> assertEquals('$this -> data[\'block\'][5][$this -> data[\'b\']]', $this->opt->compiler->compileExpression('$block[5][$b]'));		
		} // end testExpressionTablePHPSyntax();
		
		public function testExpressionTableAlternativeSyntax()
		{
			$this -> assertEquals('$this -> data[\'block\'][5][$this -> data[\'b\']]', $this->opt->compiler->compileExpression('$block.5.[$b]'));		
		} // end testExpressionTablePHPSyntax();
		
		public function testExpressionSectionSyntax()
		{
			$this -> opt -> compiler -> nestingLevel['section'] = 1;
			$this -> assertEquals('$__section_val[\'block\']', $this->opt->compiler->compileExpression('$section.block'));		
		} // end testExpressionTablePHPSyntax();
		
		public function testParametrizeNoParamsNoMatches()
		{
			$params = array();
			
			$matches = array();

			$parsingResult = $this->opt->compiler->parametrize($matches, $params);
			$this -> assertTrue($parsingResult == NULL && count($params) == 0);		
		} // end testParametrizeNoParamsNoMatches();
		
		public function testParametrizeNoParamsYesMatchesUnnamed()
		{
			$params = array();
			
			$matches = array(
				3 => '=blablabla; trelele;',
				4 => 'blablabla; trelele;'			
			);
			$parsingResult = $this->opt->compiler->parametrize($matches, $params);
			$this -> assertTrue($parsingResult == NULL && count($params) == 0);		
		} // end testParametrizeNoParamsYesMatchesUnnamed();
		
		public function testParametrizeNoParamsYesMatchesNamed()
		{
			$params = array();
			
			$matches = array(
				3 => ' param1="blablabla" param2="trelele"',
				4 => 'param1="blablabla" param2="trelele"'			
			);
			$parsingResult = $this->opt->compiler->parametrize($matches, $params);
			$this -> assertTrue($parsingResult == NULL && count($params) == 0);		
		} // end testParametrizeNoParamsYesMatchesNamed();
		
		public function testParametrizeYesOptionalParamsNoMatches()
		{
			$params = array(
				'param1' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, 'abc'),
				'param2' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, 'bcd')
			);
			
			$matches = array();
			$parsingResult = $this->opt->compiler->parametrize($matches, $params);
			$this -> assertTrue($parsingResult == NULL && $params == array('param1' => 'abc', 'param2' => 'bcd'));	
		} // end testParametrizeYesOptionalParamsNoMatches();
		
		public function testParametrizeYesRequiredParamsNoMatches()
		{
			$params = array(
				'param1' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID)
			);
			
			$matches = array();
			$parsingResult = $this->opt->compiler->parametrize($matches, $params);
			$this -> assertEquals(1, $parsingResult);	
		} // end testParametrizeYesOptionalParamsNoMatches();
		
		public function testParametrizeYesRequiredParamsYesMatchesUnnamed()
		{
			$params = array(
				'param1' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'param2' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID)
			);
			
			$matches = array(
				3 => '=abc; bcd',
				4 => 'abc; bcd'
			);
			$parsingResult = $this->opt->compiler->parametrize($matches, $params);
			$this -> assertTrue($parsingResult == NULL && $params == array('param1' => 'abc', 'param2' => 'bcd'));		
		} // end testParametrizeYesRequiredParamsYesMatchesUnnamed();
		
		public function testParametrizeYesRequiredParamsYesMatchesNamed()
		{
			$params = array(
				'param1' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'param2' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID)
			);
			
			$matches = array(
				3 => ' param1="abc" param2="bcd"',
				4 => 'param1="abc" param2="bcd"'	
			);
			$parsingResult = $this->opt->compiler->parametrize($matches, $params);
			$this -> assertTrue($parsingResult == NULL && $params == array('param1' => 'abc', 'param2' => 'bcd'));		
		} // end testParametrizeYesRequiredParamsYesMatchesNamed();
		
		public function testParametrizeYesRequiredAndOptionalParamsYesMatchesUnnamed()
		{
			$params = array(
				'param1' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'param2' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, 'bcd')
			);
			
			$matches = array(
				3 => '=abc; def',
				4 => 'abc; def'			
			);
			$parsingResult = $this->opt->compiler->parametrize($matches, $params);
			$this -> assertTrue($parsingResult == NULL && $params == array('param1' => 'abc', 'param2' => 'def'));		
		} // end testParametrizeYesRequiredAndRequiredParamsYesMatchesUnnamed();

		public function testParametrizeYesRequiredAndOptionalParamsYesIncompleteMatchesUnnamed()
		{
			$params = array(
				'param1' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'param2' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, 'bcd')
			);
			
			$matches = array(
				3 => '=abc',
				4 => 'abc'			
			);
			$parsingResult = $this->opt->compiler->parametrize($matches, $params);
			$this -> assertTrue($parsingResult == NULL && $params == array('param1' => 'abc', 'param2' => 'bcd'));		
		} // end testParametrizeYesRequiredAndRequiredParamsYesIncompleteMatchesUnnamed();
		
		public function testParametrizeOptionalJump()
		{
			$params = array(
				'param1' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
				'param2' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, 'bcd'),
				'param3' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, 'cde')
			);
			
			$matches = array(
				3 => '=abc; !x; def',
				4 => 'abc; !x; def'	
			);
			$parsingResult = $this->opt->compiler->parametrize($matches, $params);
			$this -> assertTrue($parsingResult == NULL && $params == array('param1' => 'abc', 'param2' => 'bcd', 'param3' => 'def'));		
		} // end testParametrizeOptionalJump();
		
		public function testParametrizeOptionalJumpAtRequired()
		{
			try{
				$params = array(
					'param1' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
					'param2' => array(OPT_PARAM_REQUIRED, OPT_PARAM_ID),
					'param3' => array(OPT_PARAM_OPTIONAL, OPT_PARAM_ID, 'cde')
				);
				
				$matches = array(
					3 => '=abc; !x; def',
					4 => 'abc; !x; def'	
				);
				$parsingResult = $this->opt->compiler->parametrize($matches, $params);
			}
			catch(optException $exception)
			{
				if($exception -> getCode() == 112)
				{
					return 1;
				}
			}
			$this -> fail('Invalid marker exception not returned!');
		} // end testParametrizeOptionalJump();
		
		public function testCompilerSimple()
		{
$template = '{compiler}
{ava}
ppp
{avaelse}
ppp
{/ava}
{/compiler}';
$result = 'echo \'BEGIN COMPILER SESSION
MASTER: ava ()(ava)
ALT: avaelse ()(ava)
ENDER: /ava ()(ava)
END COMPILER SESSION\';';
			$this -> assertEquals($result, $this->opt->compiler->parse($template));		
		} // end testCompilerSimple();
		
		public function testCompilerCommands()
		{
$template = '{compiler}
{permate/}
{/compiler}';
$result = 'echo \'BEGIN COMPILER SESSION
CMD: permate ()(permate)
END COMPILER SESSION\';';
			$this -> assertEquals($result, $this->opt->compiler->parse($template));		
		} // end testCompilerCommands();
		
		public function testCompilerMegadeath()
		{
$template = '{compiler}
{sect1=test}
	{sect2=hope}
		{thereishope=miracle/}
	{sect2else}
		{thereisnohope/}
	{/sect2}
{/sect1}
{/compiler}';
$result = 'echo \'BEGIN COMPILER SESSION
MASTER: sect1 (=test)(sect1)
.MASTER: sect2 (=hope)(sect2)
..CMD: thereishope (=miracle)(thereishope)
.ALT: sect2else ()(sect2)
..CMD: thereisnohope ()(thereisnohope)
.ENDER: /sect2 ()(sect2)
ENDER: /sect1 ()(sect1)
END COMPILER SESSION\';';
			$this -> assertEquals($result, $this->opt->compiler->parse($template));		
		} // end testCompilerMegadeath();

	}

?>
