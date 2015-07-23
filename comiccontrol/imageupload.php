<?
//imageupload.php
//store, upload, and manage files<?

//invoke header
include('includes/header.php'); 

//sanitize get variables
$imageid = filterint($_GET['imageid']);

echo '<h2>' . $lang['imageupload'] . '</h2>';

//perform action based on $do
if($do != ""){
	switch($do){
	
		//add image
		case "add":
			//upload image if form submitted
			if(isset($_POST['submit'])){
				//upload image
				ini_set('upload_tmp_dir','/tmp/');
				$files = array();
				$fieldname = "image";
				if($_FILES[$fieldname]['tmp_name'] != "")
				{
					include('mediaupload.php');
					echo $thumbname;
					$query = "INSERT INTO cc_".$tableprefix . "images(thumbname,imgname) VALUES('" . $thumbname . "','" . $imgname . "')";
					$result = $z->query($query);
					$newimg = $z->insert_id;
					?>
					<div class="successbox"><?=$lang['imageaddsuccess']?></div>
					<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>imageupload.php?do=add"><?=$lang['addanotherimage'];?></a></div></div>
					<?
				}
			}//end image upload
			
			//display upload form if image not submitted
			else{
			?>
				<? //display form box ?>
				<form name="addimage" action="imageupload.php?do=add" method="post" enctype="multipart/form-data" onsubmit="loading()">    
				<div class="formbox">
					<div class="formline"><label><?=$lang['imagefile']?>:</label><div class="forminput"><input type="file" name="image" style="width:400px" /></div></div>
					<p><input type="submit" name="submit" value="<?=$lang['submit']?>" /></p>
				</div>
			<?
			}
			?>
			<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>imageupload.php"><?=$lang['returnto'];?><?=$lang['imageupload']?></a></div></div>
            <?
			break;
			
			
		//delete image
		case "delete":
		
			//check if id is actually selected
			if(isset($imageid) && $imageid != ""){
			
				$query = "SELECT * FROM `cc_" . $tableprefix . "images` WHERE id='" . $imageid . "' LIMIT 1";
				$result = $z->query($query);
				$image = $result->fetch_assoc();
					
				//IMAGE DELETED
				if(isset($_POST['delete']) && $_POST['delete'] != ""){
					$query = "DELETE FROM `cc_" . $tableprefix . "images` WHERE id='" . $image['id'] . "'";
					$result = $z->query($query);
					unlink("../uploads/" . $image['imgname']);
					unlink("../uploads/" . $image['thumbname']);
					echo '<div class="successbox">' . $lang['imagedeleted'] . '</div>';
				}
				//ASK TO DELETE IMAGE
				else{
					?>
					<p style="text-align:center"><?=$lang['asktodelete']?><?=$lang['thisimage']?>?</p>
					<form method="post" action="imageupload.php?do=delete&imageid=<?=$imageid?>" name="deleteimage">
					<p style="text-align:center"><input type="submit" name="delete" value="<?=$lang['yes']?>" /> <input type="button" value="<?=$lang['no']?>" onclick="self.location='imageupload.php'" /></p>
					<p><br /></p>
					</form>
					<?
				}	
				echo '<div class="ccbuttoncont"><div class="ccbutton"><a href="imageupload.php?do=add">' . $lang['addanotherimage'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="imageupload.php">' . $lang['returnto'] . $lang['imageupload'] . '</a></div></div>';
			}
			break;	
	}
}

//if no $do set, display image to edit
else{
	//show all uploaded images
	$query = "SELECT * FROM cc_".$tableprefix . "images";
	$result = $z->query($query);
	
	//throw error if no image
	if($result->num_rows == 0){
		echo '<p>' . $lang['noimageuploads'] . '</p>';
	}
	
	//loop through photos to display them with edit options and rearrange
	else{
		echo '<table><tr><td>' . $lang['thumbnail'] . '</td><td>' . $lang['permalink'] . '</td><td></td></tr>';
		while($row=$result->fetch_assoc()){
			echo '<tr><td width="120px" style="text-align:center"><img src="../uploads/' . $row['thumbname'] . '" /></td><td width="590px">' . $siteroot . "uploads/" . $row['imgname'] . '</td><td><a href="' . $root . 'imageupload.php?do=delete&imageid=' . $row['id'] . '">' . $lang['delete'] . '</a></td></tr>';
		}
		echo '</table>';
		echo '<div style="clear:both; height:20px;"></div>';
	}
		?> <div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>imageupload.php?do=add"><?=$lang['addanimage']?></a></div></div> <?

}

include('includes/footer.php'); ?>
