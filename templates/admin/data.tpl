{include file="header.tpl"}
{include file="menu.tpl"}

<h2>import from soojung data file</h2>
<form action="" method="post">
<input type="file">
<input type="submit" value="import!">
</form>
<br />

<h2>import from tattertools</h2>
<form action="tt_convert.php" method="post">
Input tettertools database info <br />
DB server: <input type="text" name="db_server"> <br />
DB username: <input type="text" name="db_user"> <br />
DB password: <input type="text" name="db_pass"> <br />
DB name: <input type="text" name="db_name"> <br />
<input type="hidden" name="mode" value="convert">
<input type="submit" value="import!">
</form>

<h2>export to soojung data file</h2>
<a href="{$baseurl}/admin.php?mode=export">export</a>

{include file="footer.tpl"}