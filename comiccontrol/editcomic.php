<?
//edit/comic.php
//Comic editing script: switch page based on $do

if(authCheck()){

//if action is set, include that action
if($do != ""){
	switch($do)
	{
		case "add":
			include("editcomicadd.php");
			break;
		case "edit":
			include("editcomicedit.php");
			break;
		case "manage":
			include("editcomicmanage.php");
			break;
	}
}
//if action is not set, give options
else{
	?>
    <div class="blockborder">
    <span style="font-variant:small-caps"><a href="edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['add']?></span><br /><?=$lang['acomic']?></a> 
    </div>
    <div class="blockborder">
    <span style="font-variant:small-caps"><a href="edit.php?moduleid=<?=$moduleid?>&do=edit"><?=$lang['edit']?></span><br /><?=$lang['comicslow']?></a> 
    </div>
    <div class="blocknoborder">
    <span style="font-variant:small-caps"><a href="edit.php?moduleid=<?=$moduleid?>&do=manage"><?=$lang['manage']?></span><br /><?=$lang['storylineslow']?></a> 
    </div>
    <?
}

}
?>