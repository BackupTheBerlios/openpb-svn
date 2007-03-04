<html> 
<head> 
  <title>Test optional forms 1</title> 
</head> 
<body>
<h3>Optional forms</h3>
<p>OPF can also handle forms that are not necessary to fill in, or that may use the data from the URL address.
They are unsually used in some statistic applications, where we can select the exact date we want to see. This
example shows, how to handle such form with OPF.</p>
<p>Note that this special form does not report the error, if it is filled incorrectly!</p>
<hr/>
{* The form *}
<opf:form method="post" action="`test_opt_forms_1.php`" name="calendarForm">
<table class="form" width="100%">
  <tr>
  <td>Select the date</td>
  <td><opt:opfSelect name="day"><opt:load event="aMessage"/></opt:opfSelect><opt:opfSelect name="month"><opt:load event="aMessage"/></opt:opfSelect><opt:opfSelect name="year"><opt:load event="aMessage"/></opt:opfSelect></td> 
 </tr>
 <tr>
  <td></td>
  <td><input type="submit" value="OK"/></td> 
 </tr>
</table>
</opf:form>

{if test="$date"}
<p>You chose to see the events from <a href="test_opt_forms_1.php?day={$day}&month={$month}&year={$year}">{$month}.{$day}.{$year}</a></p>
{else}
<p>You chose to see the events from all the time.</p>
{/if}
</body>
</html>
