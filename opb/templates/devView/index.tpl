
<div id="wrapper">

	<!-- cat1 start -->
	{section=categories} 
	<div class="category">
		<h1>{$categories.title}</h1>
		{section=forums}
		<div class="forumrow">
			<div class="forumtitle">
				<span class="titlelink"><a href="{url file="`index.php`" act="ViewForum" fid="$forums.id"}">{$forums.title}</a></span><br />
				<span class="desc">{$forums.description}</span>
			</div>
			<div class="forumactivity">
				{$index@last_post}(wczoraj)<br />
					<a href="#">zimaq</a>
			</div>
			<div class="forumstat">
				{$index@topics}<strong>{$forums.topics}</strong><br />
				{$index@posts}<strong>{$forums.posts}</strong>
			</div>
		</div>
		{/section}
	</div>
	{/section}
</div>

<p>Strona {$page} z {$total}</p>
<p>{$pages}</p>
