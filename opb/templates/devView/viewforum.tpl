<div id="wrapper">
	<div class="category">
			<h1><a href="{url file="`index.php`"}">{$f_title}</a></h1>
			
			{section=topics}
			<div class="forumrow">
				<div class="forumtitle">
					<span class="titlelink"><a href="{url file="`index.php`" act="ViewTopic" tid="$topics.id"}">{$topics.title}</a></span><br />
					<span class="desc">{$topics.description}</span>
				</div>
				<div class="forumactivity">
					{$viewforum@last_post}: (wczoraj)<br />
						<a href="#">zimaq</a>
				</div>
				<div class="forumstat">
					{$viewforum@replies}<strong>3</strong><br />
					{$viewforum@views}<strong>244</strong>
				</div>
			</div>
			{/section}	
	</div>
	<div class="buttons">
		<a href="{url file="`index.php`" act="NewTopic" tid="$f_id"}">{$viewforum@new_topic}</a> | <a href="{url file="`index.php`" act="NewTopic" add="Poll" tid="$f_id"}">{$viewforum@new_poll}</a>
	</div>	
</div>
