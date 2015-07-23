<?
//editgallery.php
//Photo albums editing script

if(authCheck()){

if($do != ""){
	switch($do)
	{
		case "add":
			include("editgalleryadd.php");
			break;
		case "edit":
			include("editgalleryedit.php");
			break;
		case "rearrange":
			include("editgalleryrearrange.php");
			break;
	}
}
//if action is not set, give options
else{
	?>
    <div class="blockborder">
    <span style="font-variant:small-caps"><a href="edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['add']?></span><br /><?=$lang['animage']?></a> 
    </div>
    <div class="blockborder">
    <span style="font-variant:small-caps"><a href="edit.php?moduleid=<?=$moduleid?>&do=edit"><?=$lang['edit']?></span><br /><?=$lang['images']?></a> 
    </div>
    <div class="blocknoborder">
    <span style="font-variant:small-caps"><a href="edit.php?moduleid=<?=$moduleid?>&do=rearrange"><?=$lang['rearrange']?></span><br /><?=$lang['images']?></a> 
    </div>
    <?
}

}

?>