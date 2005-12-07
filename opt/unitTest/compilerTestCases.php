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
			$this -> functions['checkrole'] = 'checkrole';
			$this -> functions['menuperms'] = 'menuperms';
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
			try
			{
				$this -> assertEquals('"A string"', $this->opt->compiler->compileExpression('"A string"'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}	
		} // end testExpressionStrings();
		
		public function testExpressionEscapedStrings()
		{
			try
			{
				$this -> assertEquals('"A \"string"', $this->opt->compiler->compileExpression('"A \"string"'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}			
		} // end testExpressionEscapedStrings();
		
		public function testExpressionRAStrings()
		{
			try
			{
				$this -> assertEquals('\'A string\'', $this->opt->compiler->compileExpression('`A string`'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}			
		} // end testExpressionRAStrings();
		
		public function testExpressionRAEscapedStrings()
		{
			try
			{
				$this -> assertEquals('\'A "string\'', $this->opt->compiler->compileExpression('`A "string`'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testExpressionRAEscapedStrings();
		
		public function testExpressionRAEscapedStringsRA()
		{
			try
			{
				$this -> assertEquals('\'A `string\'', $this->opt->compiler->compileExpression('`A \`string`'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}			
		} // end testExpressionRAEscapedStrings();
		
		public function testExpressionNonOperatorStrings()
		{
			try
			{
				$this -> assertEquals('\'edit\'', $this->opt->compiler->compileExpression('edit'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testExpressionNonOperatorStrings();
		
		public function testExpressionOperatorStrings()
		{
			try
			{
				$this -> assertEquals('5+3', $this->opt->compiler->compileExpression('5 add 3'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}	
		} // end testExpressionOperatorStrings();
		
		public function testExpressionNumbers()
		{
			try
			{
				$this -> assertEquals('12345', $this->opt->compiler->compileExpression('12345'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}	
		} // end testExpressionNumbers();
		
		public function testExpressionFloatNumbers()
		{
			try
			{
				$this -> assertEquals('12345.67', $this->opt->compiler->compileExpression('12345.67'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}	
		} // end testExpressionFloatNumbers();
		
		public function testExpressionHexadecimalNumbers()
		{
			try
			{
				$this -> assertEquals('0x54A6B', $this->opt->compiler->compileExpression('0x54A6B'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}	
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
		
		public function testExpressionNullFunction()
		{
			try
			{
				$this -> assertEquals('optcheckrole($this)', $this->opt->compiler->compileExpression('checkrole()'));
			}
			catch(optException $exception)
			{
				optErrorHandler($exception);
				$this -> fail('Exception returned');
			}			
		} // end testExpressionNullFunction();

		public function testExpressionFunctionWithParams()
		{
			try
			{
				$this -> assertEquals('optcheckrole($this,$this->data[\'a\'],$this->data[\'b\'])', $this->opt->compiler->compileExpression('checkrole($a, $b)'));
			}
			catch(optException $exception)
			{
				optErrorHandler($exception);
				$this -> fail('Exception returned');
			}
		} // end testExpressionFunctionWithParams();

		public function testExpressionNullMethod()
		{
			try
			{
				$this -> assertEquals('$this->data[\'a\']->checkrole()', $this->opt->compiler->compileExpression('$a->checkrole()'));
			}
			catch(optException $exception)
			{
				optErrorHandler($exception);
				$this -> fail('Exception returned');
			}			
		} // end testExpressionNullMethod();

		public function testExpressionMethodWithParams()
		{
			try
			{
				$this -> assertEquals('$this->data[\'a\']->checkrole($this->data[\'a\'],$this->data[\'b\'])', $this->opt->compiler->compileExpression('$a->checkrole($a, $b)'));
			}
			catch(optException $exception)
			{
				optErrorHandler($exception);
				$this -> fail('Exception returned');
			}
		} // end testExpressionMethodWithParams();
		
		public function testExpressionTablePHPSyntax()
		{
			try
			{
				$this -> assertEquals('$this->data[\'block\'][5][$this->data[\'b\']]', $this->opt->compiler->compileExpression('$block[5][$b]'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}			
		} // end testExpressionTablePHPSyntax();
		
		public function testExpressionTableAlternativeSyntax()
		{
			try
			{
				$this -> assertEquals('$this->data[\'block\'][5][$this->data[\'b\']]', $this->opt->compiler->compileExpression('$block.5[$b]'));		
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}	
		} // end testExpressionTablePHPSyntax();
		
		public function testExpressionSectionSyntax()
		{
			try
			{
				$this -> opt -> compiler -> nestingLevel['section'] = 1;
				$this -> assertEquals('$__section_val[\'block\']', $this->opt->compiler->compileExpression('$section.block'));		
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}	
		} // end testExpressionTablePHPSyntax();
		
		public function testExpressionAssignmentBasic()
		{
			try
			{
				$result = $this->opt->compiler->compileExpression('$a = 17', 1);
				$this -> assertEquals('$this->data[\'a\']=17', $result[0]);
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testExpressionAssignmentBasic();
		
		public function testExpressionMultiAssignment()
		{
			try
			{
				$result = $this->opt->compiler->compileExpression('$a = $b = $c = 17', 1);
				$this -> assertEquals('$this->data[\'a\']=$this->data[\'b\']=$this->data[\'c\']=17', $result[0]);
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testExpressionMultiAssignment();
		
		public function testExpressionExtendedAssignment()
		{
			try
			{
				$result = $this->opt->compiler->compileExpression('$a[$b + $c] = 17', 1);
				$this -> assertEquals('$this->data[\'a\'][$this->data[\'b\']+$this->data[\'c\']]=17', $result[0]);
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testExpressionExtendedAssignment();
		
		public function testExpressionInvalidAssignment()
		{
			try
			{
				$this->opt->compiler->compileExpression('$b + $c = 17', 1);
			}
			catch(optException $exc)
			{
				return 1;
			}
			$this -> fail('Invalid assignment exception not returned!');
		} // end testExpressionInvalidAssignment();

		public function testRealExpression1()
		{
			// Expression sent by eXtreme (http://exsite.edigo.pl)
			try
			{
				$this -> opt -> compiler -> nestingLevel['section'] = 1;
				$this -> assertEquals('!$__Posts_val[\'is_topic_start\']&&((optcheckrole($this,"board_delete_own_posts")&&$this->'.
'vars[\'timeFromPosting\']<=2&&$__Posts_val[\'user_id\']==$this->'.
'data[\'UserData\'][\'id\']&&!$__Posts_val[\'is_moderated\'])||(optcheckrole($this,'.
'"board_delete_all_time_own_posts")&&$__Posts_val[\'user_id\']==$this->'.
'data[\'UserData\'][\'id\']&&!$__Posts_val[\'is_moderated\'])||optcheckrole($this,"board_can_moderate"))',
					$this->opt->compiler->compileExpression('not $Posts.is_topic_start && ((checkrole("board_delete_own_posts") && @timeFromPosting <= 2 && $Posts.user_id == $UserData[id] && not $Posts.is_moderated) || (checkrole("board_delete_all_time_own_posts") && $Posts.user_id == $UserData[id] && not $Posts.is_moderated) || checkrole("board_can_moderate"))'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testRealExpression1();

		public function testRealExpression2()
		{
			// Expression sent by eXtreme (http://exsite.edigo.pl)
			try
			{
				$this -> opt -> compiler -> nestingLevel['section'] = 1;
				$this -> assertEquals('$this->data[\'ReadTopics\'][$__Topics_val[\'id\']]&&$this->'.
'data[\'ReadTopics\'][$__Topics_val[\'id\']][\'content\']==$this->data[\'Forum\'][\'id\'].":1"',
					$this->opt->compiler->compileExpression('$ReadTopics[$Topics.id] && $ReadTopics[$Topics.id][content] == $Forum[id]::":1"'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testRealExpression2();

		public function testRealExpression3()
		{
			// Expression sent by eXtreme (http://exsite.edigo.pl)
			try
			{
				$this -> opt -> compiler -> nestingLevel['section'] = 1;
				$this -> assertEquals('(optcheckrole($this,"board_edit_own_posts")&&$this->'.
'vars[\'timeFromPosting\']<=5&&$__Posts_val[\'user_id\']==$this->'.
'data[\'UserData\'][\'id\']&&!$__Posts_val[\'is_moderated\'])||(optcheckrole($this,'.
'"board_edit_all_time_own_posts")&&$__Posts_val[\'user_id\']==$this->'.
'data[\'UserData\'][\'id\']&&!$__Posts_val[\'is_moderated\'])||optcheckrole($this,"board_can_moderate")',
					$this->opt->compiler->compileExpression('(checkrole("board_edit_own_posts") && @timeFromPosting <= 5 && $Posts.user_id == $UserData[id] && not $Posts.is_moderated) || (checkrole("board_edit_all_time_own_posts") && $Posts.user_id == $UserData[id] && not $Posts.is_moderated) || checkrole("board_can_moderate")'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testRealExpression3();
		
		public function testRealExpression4()
		{
			// Expression sent by eXtreme (http://exsite.edigo.pl)
			try
			{
				$this -> assertEquals('($this->vars[\'Mval\']->positions->item&&($this->'.
'vars[\'Mval\']->positions[\'show\']==\'yes\'||($this->vars[\'Mval\']->'.
'positions[\'show\']==\'selected\'&&$this->data[\'ExpandMenuId\']==$this->'.
'vars[\'Mval\'][\'id\']))&&(!$this->vars[\'Mval\']->positions[\'logged_in\']||($this->'.
'vars[\'Mval\']->positions[\'logged_in\']=="no"&&$this->data[\'UserNotLoggedIn\'])||($this->'.
'vars[\'Mval\']->positions[\'logged_in\']=="yes"&&$this->data[\'UserLoggedIn\'])||$this->'.
'vars[\'Mval\']->positions[\'logged_in\']=="all"))&&((!$this->vars[\'Mval\']->positions->'.
'checkperms)||($this->vars[\'Mval\']->positions->checkperms&&optmenuperms($this,$this->'.
'vars[\'Mval\']->positions->checkperms)))',
					$this->opt->compiler->compileExpression('(@Mval->positions->item && (@Mval->positions[show] == \'yes\' || (@Mval->positions[show] == \'selected\' && $ExpandMenuId == @Mval[id])) && (not @Mval->positions[logged_in] || (@Mval->positions[logged_in] == "no" && $UserNotLoggedIn) || (@Mval->positions[logged_in] == "yes" && $UserLoggedIn) || @Mval->positions[logged_in] == "all")) && ((not @Mval->positions->checkperms) || (@Mval->positions->checkperms && menuperms(@Mval->positions->checkperms)))'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testRealExpression4();
		
		public function testRealExpression5()
		{
			// Expression sent by Denver
			try
			{
				$this -> opt -> compiler -> nestingLevel['section'] = 1;
				$this -> assertEquals('$this->vars[\'prank\']==$__ranks_val[\'id\']',
					$this->opt->compiler->compileExpression('@prank==$ranks.id'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testRealExpression5();
		
		public function testRealExpression6()
		{
			// Expression sent by Denver
			try
			{
				$this -> opt -> compiler -> nestingLevel['section'] = 1;
				$this -> assertEquals('$__ranks_val[\'id\']==$this->vars[\'prank\']',
					$this->opt->compiler->compileExpression('$ranks.id==@prank'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testRealExpression6();


		public function testRealExpression7()
		{
			// Expression sent by eXtreme (http://exsite.edigo.pl)
			try
			{
				$this -> opt -> compiler -> nestingLevel['section'] = 1;
				$this -> assertEquals('($this->vars[\'gmttime\']-$__Posts_val[\'date\'])/60',
					$this->opt->compiler->compileExpression('(@gmttime-$Posts.date)/60'));
			}
			catch(optException $exc)
			{
				optErrorHandler($exc);
				$this -> fail('Exception returned');
			}		
		} // end testRealExpression7();

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
		
		public function testCompilerInvalidTree()
		{
$template = '{sect1=test}
	{sect2=hope}
		{thereishope=miracle/}
	{/sect2}
	{/sect1}
	{/alaa}
';

			try
			{
				$parsingResult = $this->opt->compiler->parse($template);
			}
			catch(optException $exception)
			{
				if($exception -> getCode() == 113)
				{
					return 1;
				}
			}
			$this -> fail('Exception not returned!');	
		} // end testCompilerInvalidTree();

	}

?>
