<?
//editcomicadd.php
//Comic add script: add a comic page

//ADD COMIC PAGE

if(authCheck()){

//show header
echo '<h2>' . $lang['addcomicheader'] . '</h2>';

//SUBMIT COMIC FORM
if(isset($_POST['comictitle']) && $_POST['comictitle'] != ""){
	
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
		$imgname = "";
	}
	
	//sanitize and assign submitted comic fields
	$theDate = $_POST['publishyear'] . "-" . $_POST['publishmonth'] . "-" . $_POST['publishday'] . " " . $_POST['publishhour'] . ":" . $_POST['publishminute'];
	$publishtime = strtotime($theDate);
	$comicname = sanitizeText($_POST['comictitle']);
	$hovertext = sanitizeText($_POST['hovertext']);
	$newstitle = sanitizeText($_POST['newstitle']);
	$newscontent = sanitize( $_POST['newscontent'] ) ;
	$transcript = sanitize( $_POST['transcript'] ) ;
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
	$query = "INSERT INTO cc_".$tableprefix . "comics (comic,imgname,comichighres,comicthumb,publishtime,comicname,newstitle,newscontent,transcript,storyline,slug,hovertext,width,height,mime,commentid) VALUES('" . $module['id'] . "','" . $imgname . "','" . $comichighres . "','" . $comicthumb . "','" . $publishtime . "','" . $comicname . "','" . $newstitle . "','" . $newscontent . "','" . $transcript . "','" . $storyline . "','" . $slugfinal . "','" . $hovertext . "','" . $width . "','" . $height . "','" . $mime . "','')";
	$z->query($query) or die(mysqli_error($z));
	$newid = $z->insert_id;
	$commentid = $module['slug'] . "-" . $newid;
	$query = "UPDATE cc_" . $tableprefix . "comics SET commentid='" . $commentid . "' WHERE id='" . $newid . "'";
	$z->query($query);
	
	//insert tags
	$tags = str_replace(", ",",",$_POST['tags']);
	$tags = explode(",",$tags);
	foreach($tags as $value){
		if($value != ""){
			$value = sanitizeText($value);
			$value = trim($value);
			$query = "INSERT INTO cc_".$tableprefix . "comics_tags(comic,comicid,tag,publishtime) VALUES('" . $module['id'] . "','" . $newid . "','" . $value . "','" . $publishtime . "')";
			$z->query($query);
		}
	}
	
	//success message
	echo '<div class="successbox">' . $lang['addsuccess'] . '</div><div class="ccbuttoncont"><div class="ccbutton"><a href="' . $siteroot . $module['slug'] . '/' . $slugfinal . '/">' . $lang['clicktopreview'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=add">' . $lang['addanothercomic'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=edit&comicid=' . $newid . '&edit=edit">' . $lang['editthiscomic'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=edit">' . $lang['editanothercomic'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '">' . $lang['returnto'] . $module['title'] . '</a></div></div>';
}


//SHOW COMIC ADD FORM
else{

	//display form box ?>
    <form name="addcomic" action="edit.php?moduleid=<?=$moduleid?>&do=add" method="post" enctype="multipart/form-data" onsubmit="loading()">
	<div class="formbox"><div class="formline"><label><?=$lang['comicfile']?></label><div class="forminput"><input type="file" name="comicfile" id="comicfile" style="width:400px" /></div><br /></div>
	
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
			if(date('Y',time()) == sprintf('%02d', $year)) echo ' SELECTED';
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
			if(date('n',time()) == $month) echo ' SELECTED';
			echo '>' . date('F', mktime(0, 0, 0, $month, 10)) . '</option>';
			$month++;
		}
	?>
	</select>
	<select name="publishday">
	<?
		$day = 1;
		$numdays = date("t",time());
		while($day <= $numdays){
			echo '<option value="' . $day . '"';
			if(date('j',time()) == $day) echo ' SELECTED';
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
			if(date('H',time()) == sprintf('%02d', $hour)) echo ' SELECTED';
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
			if(date('i',time()) == sprintf('%02d', $minute)) echo ' SELECTED';
			echo '>' . sprintf('%02d', $minute) . '</option>';
			$minute++;
		}
	?>
	</select>
	</div>
	</div>
   
	
	<? //display rest of form box ?>
	<div class="formline"><label><?=$lang['comictitle']?>:</label><div class="forminput"><input type="text" name="comictitle" style="width:400px;" /></div></div>
	<div class="formline"><label><?=$lang['newstitle']?>:</label><div class="forminput"><input type="text" name="newstitle" style="width:400px;" /></div></div>
	
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
		echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
		$spacecount = 1;
		while($count[$parent] < $numrows[$parent]){
			$currspace = 0;
			$count[$parent]++;
			$row2 = $results[$parent] -> fetch_assoc();
			echo '<option value="' . $row2['id'] . '">';
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
	<div class="formline"><label><?=$lang['hovertext']?>:</label><div class="forminput"><input type="text" name="hovertext" style="width:400px;" /></div></div>
	<div class="formline"><label><?=$lang['tagssepbycomma']?>:</label><div class="forminput"><input type="text" name="tags" style="width:400px;" /></div></div>
	<p><?=$lang['newscontent']?>:<br /><br />
	<textarea name="newscontent"></textarea>
	<p><?=$lang['transcript']?>:<br /><br />
	<textarea name="transcript"></textarea>
	<p style="text-align:center;"><input type="submit" id="submitted" name="submit" value="<?=$lang['submit']?>" /><br /></p>
	</div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>&do=edit"><?=$lang['editanothercomic']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['addanothercomic']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>"><?=$lang['returnto']?><?=$module['title']?></a></div></div>
	</form>
	<?
}

}

?>