{include file="header.tpl"}
{include file="menu.tpl"}

<div id="overview">
	<div class="recent-post">
	  <h2>Recent Posts</h2>
	  <table>
	    {foreach from=$recent_entries item=entry}
	    <tr class="row">
	      <td>
		<div class="entry_title">
		  <a href="{$baseurl}/post.php?blogid={$entry->entryId}">{$entry->title}</a>
		</div>
	      </td>
	      <td class="del">
		<a href="{$baseurl}/admin.php?mode=delete_entry&amp;blogid={$entry->entryId}" onclick="return confirm('Are you sure want to delete this entry?\nTitle: {$entry->title}');">X</a>
	      </td>
	      <td class="ping">
		<a href="{$baseurl}/sendping.php?blogid={$entry->entryId}">Ping</a>
	      </td>
	    </tr>
	    {/foreach}
	  </table>
	</div>
	<div class="recent-comment">		
	  <h2>Recent Comments</h2>
	  <table>
	    {foreach from=$recent_comments item=comment}
	    <tr class="row">
	      <td class="name">
		{$comment->name}
	      </td>
	      <td>
		<div class="entry_title">
		  <a href="{$comment->getHref()}">{$comment->getBody()|strip_tags:false}</a>
		</div>
	    </td>
	      <td class="del">
		<a href="{$baseurl}/admin.php?mode=delete&amp;file={$comment->filename}" onclick="return confirm('Are you sure want to delete this comment?\nAuthor: {$comment->name}');">X</a>
	      </td>
	    </tr>
	    {/foreach}
	  </table>
	</div>
	<div class="recent-trackback">
	  <h2>Recent Trackback</h2>
	  <table>
	    {foreach from=$recent_trackbacks item=trackback}
	    <tr class="row">
	      <td>
		<div class="entry_title">
		  <a href="{$trackback->getHref()}">{$trackback->url}</a>
		</div>
	      </td>
	      <td class="del">
		<a href="{$baseurl}/admin.php?mode=delete&amp;file={$trackback->filename}" onclick="return confirm('Are you sure want to delete this trackback?\nURL: {$trackback->url}');">X</a>
	      </td>
	    </tr>
	    {/foreach}
	  </table>
	</div>
	<div class="blog-stat">
	  <h2>Blog Stat</h2>
	  <table>
	    <tr>
	      <td>Entry Count:</td>
	      <td><b>{$entry_count}</b></td>
	    </tr>
	    <tr>
	      <td><a href="{$baseurl}/admin.php?mode=clear_cache">Clear cache</a></td>
	      <td></td>
	    </tr>
	  </table>
	</div>
</div>

{include file="footer.tpl"}