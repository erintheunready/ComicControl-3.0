<?
//edit.php
//Initializes edit page script and directs to proper edit script based on page type

//invoke header
include('includes/header.php'); 

//module is not selected
if($moduleid == "")
{
	echo $lang['pagetoedit'];
}

//module is selected
else
{
	
	//include proper script based on module type
	$query="SELECT * FROM cc_".$tableprefix."modules where id='".$moduleid."' LIMIT 1";
	$module = fetch($query);
	echo '<h1><a href="edit.php?moduleid=' . $module['id'] . '">' . $module['title'] . '</a></h1><div class="line"></div>';
	switch($module['type'])
	{
		case "page":
			include("editpage.php");
			break;
		case "gallery":
			include("editgallery.php");
			break;
		case "comic":
			include("editcomic.php");
			break;
		case "blog":
			include("editblog.php");
			break;
	}
}
include('includes/footer.php'); ?>