<?php echo showSectionHead($sectionHead); ?>
<form id='search_form'>
<table width="60%" border="0" cellspacing="0" cellpadding="0" class="search">
	<tr>				
		<th>Website: </th>
		<td>
			<textarea name="website_urls" cols="150" rows="8"></textarea>
		</td>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<td style="padding-left: 9px;">
			<a href="javascript:void(0);" onclick="scriptDoLoadPost('rank.php', 'search_form', 'subcontent', '&sec=quickrank')"><img alt="" src="<?=SP_IMGPATH?>/proceed.gif"></a>
		</td>
	</tr>
</table>
</form>
<div id='subcontent'>
	<p class='note'>Enter URL's <b>One per line</b>. Click on <b>Proceed</b> to check Google and Alexa rank.</p>
</div>