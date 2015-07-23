<?
//editgalleryadd.php
//image upload script
if(authCheck()){
//get gallery info
$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $moduleid . "'";
$gallery = fetch($query);

//display header
echo '<h2>' . $lang['addtogallery'] . '</h2>';

//upload images
if(isset($_POST['submit'])){
	
	//upload images
	ini_set('upload_tmp_dir','/tmp/');
	$files = array();
	$fieldname = "image";
	if($_FILES[$fieldname]['tmp_name'] != "")
	{
		include('upload.php');
		$imgname = $now.'-'.$_FILES[$fieldname]['name'];
		$thumbname = $now2.'-thumb-'.$_FILES[$fieldname]['name'];
		$query = "SELECT * FROM cc_".$tableprefix . "galleries WHERE gallery='" . $gallery['id'] . "'";
		$result = $z->query($query);
		$caption = sanitizeText($_POST['caption']);
		$count = $result->num_rows;
		$query = "INSERT INTO cc_".$tableprefix . "galleries(gallery,imgname,thumbname,caption,porder) VALUES('" . $gallery['id'] . "','$imgname','$thumbname','$caption','" . ($count+1) . "')";
		$result = $z->query($query);
		$newimg = $z->insert_id;
		?>
        <div class="successbox"><?=$lang['imageaddsuccess']?></div>
        <div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=edit&imageid=<?=$newimg?>"><?=$lang['editthisimage'];?></a></div></div>
        <div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['addanotherimage'];?></a></div></div>
        <div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=rearrange"><?=$lang['rearrangeimages'];?></a></div></div>
        <?
	}
}//end image upload

//display upload form if image not submitted
else{
?>
    <? //display form box ?>
    <form name="addimage" action="edit.php?moduleid=<?=$moduleid?>&do=add" method="post" enctype="multipart/form-data" onsubmit="loading()">    
    <div class="formbox">
    	<div class="formline"><label><?=$lang['imagefile']?>:</label><div class="forminput"><input type="file" name="image" style="width:400px" /></div></div>
        <p style="text-align:center;"><?=$lang['caption']?>:<br /><br /><textarea name="caption" style="width:400px"></textarea></p>
       	<p><input type="submit" name="submit" value="<?=$lang['submit']?>" /></p>
    </div>
<?
}
?>
<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>"><?=$lang['returnto'];?><?=$module['title']?></a></div></div>
<?
}
?>