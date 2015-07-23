<?
//editcomicedit.php
//Comic editing script: edit a comic page
//Includes editing and deleting comics as well as displays comics that can be edited in nested list format

if(authCheck()){

//show header
echo '<h2>' . $lang['editcomicheader'] . '</h2>';

//Functions for editing single comic -- add, edit, delete
if(isset($_GET['comicid'])  && $_GET['comicid'] != ""){

	//sanitize getcomicid
	$getcomicid = filterint($_GET['comicid']);


	//EDIT COMIC PAGE
	
	//SUBMIT COMIC FORM
	if(isset($_POST['comictitle']) && $_POST['comictitle'] != ""){
		
		//get comic info
		$query = "SELECT * FROM `cc_" . $tableprefix . "comics` WHERE comic='" . $module['id'] . "' AND id='" . $getcomicid . "' LIMIT 1";
		$result = $z->query($query);
		$comic = $result->fetch_assoc();
		
		//upload images
		ini_set('upload_tmp_dir','/tmp/');
		$fieldname = "comicfile";
		if($_FILES[$fieldname]['tmp_name'] != "" && getimagesize($_FILES[$fieldname]['tmp_name']))
		{
			include('comicupload.php');
			$imgname = $uploadReg;
			$comichighres = $uploadHighres;
			$comicthumb = $uploadThumb;
		}else{
			$imgname = $comic['imgname'];
			$comichighres = $comic['comichighres'];
			$comicthumb = $comic['comicthumb'];
		}
		
		//sanitize and assign submitted comic fields
		$theDate = $_POST['publishyear'] . "-" . $_POST['publishmonth'] . "-" . $_POST['publishday'] . " " . $_POST['publishhour'] . ":" . $_POST['publishminute'];
		$publishtime = strtotime($theDate);
		$comicname = sanitizeText($_POST['comictitle']);
		$hovertext = sanitizeText($_POST['hovertext']);
		$newstitle = sanitizeText($_POST['newstitle']);
		$newscontent = sanitize($_POST['newscontent']);
		$transcript = sanitize($_POST['transcript']);
		$storyline = filterint($_POST['storyline']);
		
		//create slug
		$slug = preg_replace("/[^A-Za-z0-9\- ]/", "", $comicname);
		$slug = str_replace(" ","-",$slug);
		$slug = str_replace("--","-",$slug);
		$slug = strtolower($slug);
		$slugfinal = $slug;
		$slugcount = 2;
		$query = "SELECT * FROM `cc_" . $tableprefix . "comics` WHERE comic='" . $module['id'] . "' AND slug='" . $slugfinal . "' AND id!='" . $comic['id'] . "' LIMIT 1";
		$result = $z->query($query);
		while($result->num_rows > 0 || $slugfinal == "archive" || $slugfinal == "search"){
			$slugfinal = $slug . "-" . $slugcount;
			$slugcount++;
			$query = "SELECT * FROM `cc_" . $tableprefix . "comics` WHERE comic='" . $module['id'] . "' AND slug='" . $slugfinal . "' LIMIT 1";
			$result = $z->query($query);
		}
		
		//get image info
		if($imgname != ""){
			$imginfo = getimagesize("../comicshighres/" . $imgname);
			$width=$imginfo[0];
			$height=$imginfo[1];
			$mime=$imginfo['mime'];
		}else{
			$width = 0;
			$height = 0;
			$mime = "";
		}
		
		//update the table
		$query = "UPDATE cc_".$tableprefix . "comics SET imgname='" . $imgname . "',comichighres='" . $comichighres . "',comicthumb='" . $comicthumb . "',publishtime='" . $publishtime . "',comicname='" . $comicname . "',newstitle='" . $newstitle . "',newscontent='" . $newscontent . "',transcript='" . $transcript . "',storyline='" . $storyline . "',hovertext='" . $hovertext . "',slug='" . $slugfinal . "',width='" . $width . "',height='" . $height . "',mime='" . $mime . "' WHERE comic='" . $module['id'] . "' AND id='" . $comic['id'] . "'";
		$z->query($query) or die(mysqli_error($z));
		
		//delete existing tags and insert new ones
		$query = "DELETE FROM cc_".$tableprefix . "comics_tags WHERE comicid='" . $comic['id'] . "' AND comic='" . $module['id'] . "'";
		$z->query($query);
		$tags = str_replace(", ",",",$_POST['tags']);
		$tags = explode(",",$tags);
		foreach($tags as $value){
			if($value != ""){
				$value = sanitizeText($value);
				$value = trim($value);
				$query = "INSERT INTO cc_".$tableprefix . "comics_tags(comic,comicid,tag,publishtime) VALUES('" . $comic['comic'] . "','" . $comic['id'] . "','" . $value . "','" . $publishtime . "')";
				$z->query($query);
			}
		}
		
		//success message
		echo '<div class="successbox">' . $lang['editsuccess'] . '</div><div class="ccbuttoncont"><div class="ccbutton"><a href="' . $siteroot . $module['slug'] . '/' . $slugfinal . '/">' . $lang['clicktopreview'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=add">' . $lang['addanothercomic'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=edit&comicid=' . $comic['id'] . '&edit=edit">' . $lang['editthiscomic'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=edit">' . $lang['editanothercomic'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '">' . $lang['returnto'] . $module['title'] . '</a></div></div>';
	}
	
	
	//SHOW COMIC EDIT FORM
	else{
		if(isset($_GET['edit']) && $_GET['edit'] == "edit"){
		
			//get comic info
			$query = "SELECT * FROM cc_".$tableprefix . "comics WHERE comic='" . $module['id'] . "' AND id='" . $getcomicid . "' LIMIT 1";
			$result = $z->query($query);
			$comic = $result->fetch_assoc();
			
			 //display preview button and comic image ?>
           	<p><a href="<?=$siteroot?><?=$module['slug']?>/<?=$comic['slug']?>" class="ccbutton" style="width:300px;"><?=$lang['clicktopreview']?></a></p>
			<form name="editcomic" action="edit.php?moduleid=<?=$moduleid?>&do=edit&comicid=<?=$comic['id']?>" method="post" enctype="multipart/form-data" onsubmit="loading()">
            <p style="text-align:center"><?=$lang['currentcomicimage']?></p><p style="text-align:center"><img src="../comics/<?=$comic['imgname']?>" /><br /><br /></p>
            
            <? //display form box ?>
            <div class="formbox"><div class="formline"><label><?=$lang['newfile']?></label><div class="forminput"><input type="file" name="comicfile" id="comicfile" style="width:400px" /></div><br /></div>
            
            <? //display time dropdowns ?>
            <? // set number of days in month with javascript ?>
            <script>
			function daysInMonth(month,year) {
				return new Date(year, month, 0).getDate();
			}
			function updateDays(){
				var f = document.forms[0];
				if(f.publishyear.value != "" && f.publishmonth.value != ""){
					var codes = "";
					var addit = "";
					var numdays = daysInMonth(f.publishmonth.value,f.publishyear.value);
					for(i=1;i<=numdays;i++){
						addit = '<option value="' + i + '">' + i + '</option>';
						codes += addit;
					}
					f.publishday.innerHTML = codes;
				}
			}
			</script>
            
            <div class="formline"><div class="forminput"><?=$lang['publishdate']?>:</div><div class="forminput">
            <select name="publishyear" onChange="updateDays()">
            <?
                $year = 1996;
                while($year != (date('Y',time())+4)){
					echo '<option value="' . $year . '"';
                    if(date('Y',$comic['publishtime']) == sprintf('%02d', $year)) echo ' SELECTED';
                    echo '>' . $year . '</option>';
                    $year++;
                }
            ?>
            </select>
            <select name="publishmonth" onChange="updateDays()">
            <?
                $month = 1;
                while($month < 13){
					echo '<option value="' . $month . '"';
                    if(date('n',$comic['publishtime']) == $month) echo ' SELECTED';
                    echo '>' . date('F', mktime(0, 0, 0, $month, 10)) . '</option>';
                    $month++;
                }
            ?>
            </select>
            <select name="publishday">
            <?
                $day = 1;
				$numdays = date("t",$comic['publishtime']);
                while($day <= $numdays){
                    echo '<option value="' . $day . '"';
                    if(date('j',$comic['publishtime']) == $day) echo ' SELECTED';
                    echo '>' . $day . '</option>';
                    $day++;
                }
            ?>
            </select></div>
			<div class="forminput">&nbsp;<?=$lang['publishtime']?> (<?=$timezoneshort?>): </div><div class="forminput">
            <select name="publishhour">
            <?
                $hour = 0;
                while($hour != 24){
                    echo '<option value="' . $hour . '"';
                    if(date('H',$comic['publishtime']) == sprintf('%02d', $hour)) echo ' SELECTED';
                    echo '>' . sprintf('%02d', $hour) . '</option>';
                    $hour++;
                }
            ?>
            </select>:
            <select name="publishminute">
            <?
                $minute = 0;
                while($minute != 60){
                    echo '<option value="' . $minute . '"';
                    if(date('i',$comic['publishtime']) == sprintf('%02d', $minute)) echo ' SELECTED';
                    echo '>' . sprintf('%02d', $minute) . '</option>';
                    $minute++;
                }
            ?>
            </select>
            </div>
            </div>
           
            
            <? //display rest of form box ?>
            <div class="formline"><label><?=$lang['comictitle']?>:</label><div class="forminput"><input type="text" name="comictitle" style="width:400px;" value="<?=$comic['comicname']?>" /></div></div>
            <div class="formline"><label><?=$lang['newstitle']?>:</label><div class="forminput"><input type="text" name="newstitle" style="width:400px;" value="<?=$comic['newstitle']?>" /></div></div>
            
            <? //display storyline dropdown ?>
            <div class="formline"><label><?=$lang['storyline']?>:</label><div class="forminput"><select name="storyline" style="width:400px;">
            <?
			$query = "SELECT * FROM cc_".  $tableprefix . "comics_storyline WHERE comic='" . $module['id'] . "' AND parent='0' ORDER BY sorder ASC";
			$result = $z->query($query);
			$count = array();
			$numrows = array();
			$parent = 0;
			$results = array();
			$rows = array();
			$temp = 0;
			while($row = $result->fetch_assoc()){
				$parent = $row['id'];
				$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE comic='" . $module['id'] . "' AND parent='" . $parent . "' ORDER BY sorder ASC";
				$results[$parent] = $z->query($query);
				$numrows[$parent] = $results[$parent]->num_rows;
				$count[$parent] = 0;
				echo '<option value="' . $row['id'] . '"';
				if($row['id'] == $comic['storyline']) echo ' SELECTED';
				echo '>' . $row['name'] . '</option>';
				$spacecount = 1;
				while($count[$parent] < $numrows[$parent]){
					$currspace = 0;
					$count[$parent]++;
					$row2 = $results[$parent] -> fetch_assoc();
					echo '<option value="' . $row2['id'] . '"';
					if($row2['id'] == $comic['storyline']) echo ' SELECTED';
					echo '>';
					while($currspace < $spacecount){
						echo '&nbsp;&nbsp;';
						$currspace++;
					}
					echo $row2['name'];
					echo '</option>';
					$temp = $parent;
					$parent = $row2['id'];
					$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE comic='" . $module['id'] . "' AND parent='" . $parent . "'";
					$results[$parent] = $z->query($query);
					$numrows[$parent] = $results[$parent]->num_rows;
					if($numrows[$parent] == 0){
						$parent = $temp;
					}else{
						$count[$parent] = 0;
						$spacecount++;
					}
					if($count[$parent] == $numrows[$parent]){
						$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE comic='" . $module['id'] . "' AND id='" . $parent . "'";
						$tempresult = $z->query($query);
						$temprow =  $tempresult -> fetch_assoc();
						$parent = $temprow['parent'];
						$spacecount--;
					}
				}
			}
			?>
            </select></div></div>
            
            <? //display rest of form box ?>
            <div class="formline"><label><?=$lang['hovertext']?>:</label><div class="forminput"><input type="text" name="hovertext" style="width:400px;" value="<?=$comic['hovertext']?>" /></div></div>
        	<div class="formline"><label><?=$lang['tagssepbycomma']?>:</label><div class="forminput"><input type="text" name="tags" style="width:400px;" value="<?
				$query = "SELECT * FROM cc_" . $tableprefix . "comics_tags WHERE comic='" . $module['id'] . "' AND comicid='" . $comic['id'] . "'";
				$result = $z->query($query);
				while($row = $result->fetch_assoc()){
					echo $row['tag'] . ",";
				}
			?>" /></div></div>
            <p><?=$lang['newscontent']?>:<br /><br />
			<textarea name="newscontent"><?=$comic['newscontent'];?></textarea></p>
            <p><?=$lang['transcript']?>:<br /><br />
			<textarea name="transcript"><?=$comic['transcript'];?></textarea></p>
            <p style="text-align:center;"><input type="submit" id="submitted" name="submit" value="<?=$lang['submit']?>" /><br /></p>
            </div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>&do=edit"><?=$lang['editanothercomic']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['addanothercomic']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>"><?=$lang['returnto']?><?=$module['title']?></a></div></div>
			</form>
			<?
		}else 
		
		
		
		
		//DELETE COMIC
		if(isset($_GET['edit']) && $_GET['edit'] == "delete"){
			if(isset($getcomicid) && $getcomicid != ""){
			
				$query = "SELECT * FROM `cc_" . $tableprefix . "comics` WHERE comic='" . $module['id'] . "' AND id='" . $getcomicid . "' LIMIT 1";
				$result = $z->query($query);
				$comic = $result->fetch_assoc();
					
				//COMIC DELETED
				if(isset($_POST['delete']) && $_POST['delete'] != ""){
					$query = "DELETE FROM `cc_" . $tableprefix . "comics` WHERE comic='" . $module['id'] . "' AND id='" . $comic['id'] . "'";
					$result = $z->query($query);
					$query = "DELETE FROM cc_" . $tableprefix . "comics_tags WHERE comic='" . $module['id'] . "' AND comicid='" . $comic['id'] . "'";
					$z->query($query);
					echo '<div class="successbox">' . $lang['deletesuccess'] . '</div>';
				}
				//ASK TO DELETE COMIC
				else{
					?>
                    <p style="text-align:center"><?=$lang['asktodelete']?><?=$comic['comicname']?>?</p>
					<form method="post" action="edit.php?moduleid=<?=$moduleid?>&do=edit&edit=delete&comicid=<?=$getcomicid?>" name="deletecomic">
					<p style="text-align:center"><input type="submit" name="delete" value="Yes" /> <input type="button" value="No" onclick="self.location='edit.php?moduleid=<?=$moduleid?>&do=edit'" /></p>
                    <p><br /></p>
					</form>
					<?
				}	
                echo '<div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=edit">' . $lang['editanothercomic'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=add">' . $lang['addanothercomic'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '">' . $lang['returnto'] . $module['title'] . '</a></div></div>';
			}
		}
	}
	
	
	
	
//DISPLAY COMICS FOR EDITING
}else{

	//FUNCTION TO DISPLAY COMICS IN STORYLINE
	function listPages($thiscomic){
		global $module;
		global $tableprefix;
		global $siteroot;
		global $lang;
		global $z;
		$query = "SELECT * FROM cc_".$tableprefix . "comics WHERE comic='" . $module['id'] . "' AND storyline='" . $thiscomic . "' ORDER BY publishtime ASC";
		$result2 = $z->query($query);
		while($page = $result2->fetch_assoc()){
			echo '<li>' . $page['comicname'] . ' <a href="edit.php?moduleid=' . $module['id'] . '&do=edit&comicid=' . $page['id'] . '&edit=edit">' . $lang['edit'] . '</a> | <a href="edit.php?moduleid=' . $module['id'] . '&do=edit&comicid=' . $page['id'] . '&edit=delete">' . $lang['delete'] . '</a> | <a href="' . $siteroot . $module['slug'] . '/' . $page['slug'] . '">' . $lang['preview'] . '</a></li>';
		}
	}

	//DISPLAY COMICS FOR EDITING
	echo '<ul>';
	$count = array();
	$numstories = array();
	$parent = 0;
	$substories = array();
	$parentstory = array();
	$rows = array();
	$temp = 0;
	$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE comic='" . $module['id'] . "' AND parent='" . $parent . "' ORDER BY sorder ASC";
	$substories[$parent] = $z->query($query);
	$numstories[$parent] = $substories[$parent]->num_rows;
	$count[$parent] = 0;
	$currid=0;
	$ended = false;
	while($row = $substories[$currid]->fetch_assoc()){
		echo '<li><a style="cursor:pointer" onclick="$(\'#storyline' . $row['id'] . '\').slideToggle(\'fast\');">' . $row['name'] . '</a><br /><ul id="storyline' . $row['id'] . '" style="display:none">';
		$currid = $row['id'];
		$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE comic='" . $module['id'] . "' AND parent='" . $currid . "' ORDER BY sorder ASC";
		if(!array_key_exists($currid,$substories)) $substories[$currid] = $z->query($query);
		if(!array_key_exists($currid,$numstories)) $numstories[$currid] = $substories[$currid]->num_rows;
		if(!array_key_exists($currid,$parentstory)) $parentstory[$currid] = $row['parent'];
		if(!array_key_exists($currid,$count)) $count[$currid] = 0;
		while($count[$currid] == $numstories[$currid]){
			listPages($currid);
			echo '</ul>';
			$currid = $parentstory[$currid];
			$ended = true;
		}
		$count[$currid] ++;
		$ended = false;
	}
	$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE comic='" . $module['id'] . "'";
	$result = $z->query($query);
	$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $module['id'] . "'";
	while($row = $result->fetch_assoc()){
		$query .= " AND storyline!='" . $row['id'] . "'";
	}
	echo '<li><a style="cursor:pointer" onclick="$(\'#uncategorized\').slideToggle(\'fast\');">' . $lang['uncategorized'] . '<br /><ul id="uncategorized" style="display:none">';
	$result = $z->query($query);
	while($row = $result->fetch_assoc()){
		echo '<li>' . $row['comicname'] . ' <a href="edit.php?moduleid=' . $module['id'] . '&do=edit&comicid=' . $row['id'] . '&edit=edit">' . $lang['edit'] . '</a> | <a href="edit.php?moduleid=' . $module['id'] . '&do=edit&comicid=' . $row['id'] . '&edit=delete">' . $lang['delete'] . '</a> | <a href="' . $siteroot . $module['slug'] . '/' . $row['comicname'] . '">' . $lang['preview'] . '</a></li>';
	}
	echo '</ul>';
}

}
?>