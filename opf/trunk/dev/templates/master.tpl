<opt:bindEvent id="aMessage" name="onMessage" message="msg" position="down">
<ul>
{foreach=@msg; id; simpleMessage}
<li><span class="error">{@simpleMessage}</span></li>
{/foreach}
</ul>
</opt:bindEvent>
