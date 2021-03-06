<?php echo showSectionHead($sectionHead); ?>
<form id="editWebsite">
<input type="hidden" name="sec" value="update"/>
<input type="hidden" name="oldName" value="<?=$post['oldName']?>"/>
<input type="hidden" name="id" value="<?=$post['id']?>"/>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="list">
	<tr class="listHead">
		<td class="left" width='20%'>Edit Website</td>
		<td class="right">&nbsp;</td>
	</tr>
	<?php if(!empty($isAdmin)){ ?>	
		<tr class="blue_row">
			<td class="td_left_col">User:</td>
			<td class="td_right_col">
				<select name="user_id" style="width:150px;">
					<?php foreach($userList as $userInfo){?>
						<?php if($userInfo['id'] == $post['user_id']){?>
							<option value="<?=$userInfo['id']?>" selected><?=$userInfo['username']?></option>
						<?php }else{?>
							<option value="<?=$userInfo['id']?>"><?=$userInfo['username']?></option>
						<?php }?>						
					<?php }?>
				</select>
			</td>
		</tr>
	<?php }?>
	<tr class="white_row">
		<td class="td_left_col">Name:</td>
		<td class="td_right_col"><input type="text" name="name" value="<?=$post['name']?>"><?=$errMsg['name']?></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col">Url:</td>
		<td class="td_right_col">
			<input type="text" id='weburl' name="url" value="<?=$post['url']?>" style="width:300px;">
			<a style="text-decoration: none;" href="javascript:void(0);" onclick="crawlMetaData('websites.php?sec=crawlmeta', 'crawlstats')">&#171&#171 Crawl Meta Data</a>
			<div id="crawlstats" style="float: right;padding-right:40px;"></div>
			<br><?=$errMsg['url']?>
		</td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col">Title:</td>
		<td class="td_right_col"><input type="text" name="title" id="webtitle" value="<?=$post['title']?>" style="width:400px;"></td>
	</tr>
	<tr class="blue_row">
		<td class="td_left_col">Description:</td>
		<td class="td_right_col"><textarea name="description" id="webdescription"><?=$post['description']?></textarea><?=$errMsg['description']?></td>
	</tr>
	<tr class="white_row">
		<td class="td_left_col">Keywords:</td>
		<td class="td_right_col"><textarea name="keywords" id="webkeywords"><?=$post['keywords']?></textarea><?=$errMsg['keywords']?></td>
	</tr>		
	<tr class="blue_row">
		<td class="tab_left_bot_noborder"></td>
		<td class="tab_right_bot"></td>
	</tr>
	<tr class="listBot">
		<td class="left" colspan="1"></td>
		<td class="right"></td>
	</tr>
</table>
<table width="100%" cellspacing="0" cellpadding="0" border="0" class="actionSec">
	<tr>
    	<td style="padding-top: 6px;text-align:right;">
    		<a onclick="scriptDoLoad('websites.php', 'content')" href="javascript:void(0);">
         		<img border="0" alt="" src="<?=SP_IMGPATH?>/cancel.gif"/>
         	</a>
         	<?php if(SP_DEMO){?>
	         	<a onclick="alertDemoMsg();" href="javascript:void(0);">
	         		<img border="0" alt="" src="<?=SP_IMGPATH?>/proceed.gif"/>
	         	</a>
         	<?php }else{?>         		
	         	<a onclick="confirmSubmit('websites.php', 'editWebsite', 'content')" href="javascript:void(0);">
	         		<img border="0" alt="" src="<?=SP_IMGPATH?>/proceed.gif"/>
	         	</a>
         	<?php }?>
    	</td>
	</tr>
</table>
</form>