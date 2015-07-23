<?
//editgalleryedit.php
//image editing script

if(authCheck()){
//get gallery info
$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $moduleid . "' LIMIT 1";
$gallery = fetch($query);

//sanitize get variables
$imageid = filterint($_GET['imageid']);
$edit = sanitizeAlphanumeric($_GET['edit']);

//perform action based on $edit
if($edit != ""){
	switch($edit){
	
		//edit selected image
		case "edit":
		
			//display header
			echo '<h2>' . $lang['editgalleryimage'] . '</h2>';
		
			//get image info
			$query = "SELECT * FROM cc_" . $tableprefix . "galleries WHERE id='" . $imageid . "' AND gallery='" . $moduleid . "' LIMIT 1";
			$result = $z->query($query);
			
			if($result->num_rows == 1){
				$image = $result->fetch_assoc();
				
				
				//edit image if submitted
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
					}else{
						$imgname=$image['imgname'];
						$thumbname = $image['thumbname'];
					}
					$caption = sanitizeText($_POST['caption']);
					$query = "UPDATE cc_" . $tableprefix . "galleries SET imgname='" . $imgname . "',thumbname='" . $thumbname . "',caption='" . $caption . "' WHERE id='" . $image['id'] . "'";
					$result = $z->query($query);
					?>
					<div class="successbox"><?=$lang['imageeditsuccess']?></div>
					<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=edit&edit=edit&imageid=<?=$image['id']?>"><?=$lang['editthisimage'];?></a></div></div>
					<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=edit"><?=$lang['editanotherimage'];?></a></div></div>
					<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['addanotherimage'];?></a></div></div>
					<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=rearrange"><?=$lang['rearrangeimages'];?></a></div></div>
					<?
				}//end image upload
				
				//display upload form if image not submitted
				else{
				?>
					<? //display form box ?>
					<p style="text-align:center"><?=$lang['currentimage']?>:</p><p style="text-align:center"><img src="../uploads/<?=$image['imgname']?>" /><br /><br /></p>
					<form name="editimage" action="edit.php?moduleid=<?=$moduleid?>&do=edit&edit=edit&imageid=<?=$image['id']?>" method="post" enctype="multipart/form-data" onsubmit="loading()">    
					<div class="formbox">
						<div class="formline"><label><?=$lang['newimage']?>:</label><div class="forminput"><input type="file" name="image" style="width:400px" /></div></div>
						<p style="text-align:center;"><?=$lang['caption']?>:<br /><br /><textarea name="caption" style="width:400px"><?=$image['caption']?></textarea></p>
						<p><input type="submit" name="submit" value="<?=$lang['submit']?>" /></p>
					</div>
					<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=edit"><?=$lang['editanotherimage'];?></a></div></div>
				<?
				}
			}
			break;
		
		//delete image
		case "delete":
		
			//display header
			echo '<h2>' . $lang['deletegalleryimage'] . '</h2>';
		
			//check if id is actually selected
			if(isset($imageid) && $imageid != ""){
			
				$query = "SELECT * FROM `cc_" . $tableprefix . "galleries` WHERE gallery='" . $module['id'] . "' AND id='" . $imageid . "' LIMIT 1";
				$result = $z->query($query);
				$image = $result->fetch_assoc();
					
				//IMAGE DELETED
				if(isset($_POST['delete']) && $_POST['delete'] != ""){
					$query = "DELETE FROM `cc_" . $tableprefix . "galleries` WHERE gallery='" . $module['id'] . "' AND id='" . $image['id'] . "'";
					$result = $z->query($query);
					echo '<div class="successbox">' . $lang['imagedeleted'] . '</div>';
				}
				//ASK TO DELETE COMIC
				else{
					?>
					<p style="text-align:center"><?=$lang['asktodelete']?><?=$lang['thisimage']?>?</p>
					<form method="post" action="edit.php?moduleid=<?=$moduleid?>&do=edit&edit=delete&imageid=<?=$imageid?>" name="deleteimage">
					<p style="text-align:center"><input type="submit" name="delete" value="Yes" /> <input type="button" value="No" onclick="self.location='edit.php?moduleid=<?=$moduleid?>&do=edit'" /></p>
					<p><br /></p>
					</form>
					<?
				}	
				echo '<div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=edit">' . $lang['editanotherimage'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=add">' . $lang['addanotherimage'] . '</a></div></div>';
			}
			break;	
	}
}

//if no $edit set, display images to edit
else{

		
	//display header
	echo '<h2>' . $lang['editgalleryimage'] . '</h2>';
			
	//select photos for editing
	$query = "SELECT * FROM cc_".$tableprefix . "galleries WHERE gallery='" . $module['id'] . "' ORDER BY porder ASC";
	$result = $z->query($query);
	
	//throw error if no images
	if($result->num_rows == 0){
		echo '<p>' . $lang['noimages'] . '</p>';
	}
	
	//loop through photos to display them with edit options and rearrange
	else{
		echo '<p>' . $lang['selectimageedit'] . '</p><table><tr><td>' . $lang['thumbnail'] . '</td><td>' . $lang['caption'] . '</td><td></td></tr>';
		while($row=$result->fetch_assoc()){
			echo '<tr><td width="120px" style="text-align:center"><img src="../uploads/' . $row['thumbname'] . '" /></td><td width="590px">' . $row['caption'] . '</td><td><a href="' . $root . 'edit.php?moduleid=' . $module['id'] . '&do=edit&edit=edit&imageid=' . $row['id'] . '">' . $lang['edit'] . '</a> | <a href="' . $root . 'edit.php?moduleid=' . $module['id'] . '&do=edit&edit=delete&imageid=' . $row['id'] . '">' . $lang['delete'] . '</a></td></tr>';
		}
		echo '</table>';
		echo '<div style="clear:both; height:20px;"></div>';
		?> <div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['addanimage']?></a></div></div>
					<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=rearrange"><?=$lang['rearrangeimages'];?></a></div></div> <?
	}

}
		?>
<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>"><?=$lang['returnto'];?><?=$module['title']?></a></div></div>
<?
}
?>