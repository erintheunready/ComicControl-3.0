<?
//editblog.php
//Blog editing script
//Handles adding, editing, and deleting blog posts

if(authCheck()){

//A post is selected for editing/action
if($do != "")
{
	//Figure out type of action enacted
	switch($do)
	{
		//Deletion of post script
		case "del":
		
			echo '<h2>' . $lang['deleteblogpost'] . '</h2>';
		
			if(isset($_GET['blogid'])){
				$blogid = filterint($_GET['blogid']);
				
				//delete if form properly submitted, return to blog if not
				if(isset($_POST['q']) && $_POST['q'] != "")
				{
					$query="DELETE FROM cc_" . $tableprefix . "blogs WHERE id='". $blogid ."' LIMIT 1";
					$z->query($query);
					echo '<div class="successbox">' . $lang['postdeleted'] . '</div><div ><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=add">' . $lang['addnewpost'] . '</div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '">'  . $lang['returnto'] . $module['title'] . '</a></div></div>';
				}
				
				//otherwise ask if you want to delete post
				else{
					echo '<p style="text-align:center">' . $lang['wanttodeletepost'] . '</p><form method="post" do="edit.php?moduleid=' . $moduleid . '&do=del&blogid=' . $blogid . '"><p style="text-align:center"><input type="submit" name="q" value="' . $lang['yes'] . '" /><input type="button" value="' . $lang['no'] . '"  onclick="self.location=\'edit.php?moduleid=' . $moduleid . '\'" /></p>
                    <p><br /></p></form>';
				}
			}
			
			//Handle case for no post selected
			else{
				echo '<p>' . $lang['nopostselected'] . '</p><div ><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=add">' . $lang['addnewpost'] . '</div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '">'  . $lang['returnto'] . $module['title'] . '</a></div></div>';
			}
			break;
		
		//Post editing script
		case "ed":
		
			//display header
			echo '<h2>' . $lang['editblogpost'] . '</h2>';
		
			//check if blog post selected
			if(isset($_GET['blogid'])){
			
				//sanitize blog id
				$blogid = filterint($_GET['blogid']);
				
				
				//get comic info
				$query = "SELECT * FROM `cc_" . $tableprefix . "blogs` WHERE blog='" . $module['id'] . "' AND id='" . $blogid . "' LIMIT 1";
				$result = $z->query($query);
				$blogpost = $result->fetch_assoc();
					
				//If submitted, change info for post
				if(isset($_POST['submit']))
				{
					$sValue = sanitize( $_POST['content'] ) ;
					$title = sanitizeText( $_POST['title'] ) ;
					$theDate = $_POST['publishyear'] . "-" . $_POST['publishmonth'] . "-" . $_POST['publishday'] . " " . $_POST['publishhour'] . ":" . $_POST['publishminute'];
					$publishtime = strtotime($theDate);
					
					//create slug
					$slug = preg_replace("/[^A-Za-z0-9 ]/", "", $title);
					$slug = str_replace(" ","-",$slug);
					$slug = str_replace("--","-",$slug);
					$slug = strtolower($slug);
					$slugfinal = $slug;
					$slugcount = 2;
					$query = "SELECT * FROM `cc_" . $tableprefix . "blogs` WHERE blog='" . $module['id'] . "' AND slug='" . $slugfinal . "' AND id!='" . $blogpost['id'] . "' LIMIT 1";
					$result = $z->query($query);
					while($result->num_rows > 0 || $slugfinal=="page" || $slugfinal=="search"){
						$slugfinal = $slug . "-" . $slugcount;
						$slugcount++;
						$query = "SELECT * FROM `cc_" . $tableprefix . "blogs` WHERE blog='" . $module['id'] . "' AND slug='" . $slugfinal . "' LIMIT 1";
						$result = $z->query($query);
					}
					
					$query="UPDATE cc_" . $tableprefix . "blogs SET content='". $sValue ."',title='". $title . "',publishtime='" . $publishtime . "',slug='" . $slugfinal . "' WHERE id='". $blogid . "'";
					$result=$z->query($query);
					
					//delete existing tags and insert new ones
					$query = "DELETE FROM cc_".$tableprefix . "blogs_tags WHERE blogid='" . $blogpost['id'] . "' AND blog='" . $module['id'] . "'";
					$z->query($query);
					$tags = str_replace(", ",",",$_POST['tags']);
					$tags = explode(",",$tags);
					foreach($tags as $value){
						if($value != ""){
							$value = sanitizeText($value);
							$value = trim($value);
							$query = "INSERT INTO cc_".$tableprefix . "blogs_tags(blog,blogid,tag,publishtime) VALUES('" . $blogpost['blog'] . "','" . $blogid . "','" . $value . "','" . $publishtime . "')";
							$z->query($query);
						}
					}
					
					echo '<div class="successbox">' . $lang['postedited'] . '</div>';
					?>
                    <div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$siteroot?><?=$module['slug']?>/<?=$blogpost['slug']?>"><?=$lang['clicktopreview']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['addnewpost']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>&do=ed&blogid=<?=$blogid?>"><?=$lang['editthispost']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>"><?=$lang['returnto']?><?=$module['title']?></a></div></div>
                    <?
				}
				
				
				
				//SHOW BLOG POST EDIT FORM
				else{					
					//get blog info
					$query = "SELECT * FROM cc_".$tableprefix . "blogs WHERE blog='" . $module['id'] . "' AND id='" . $blogid . "' LIMIT 1";
					$result = $z->query($query);
					$blogpost = $result->fetch_assoc();
					
					//display form box ?>
					<form name="editpost" do="edit.php?moduleid=<?=$moduleid?>&do=ed&blogid=<?=$blogpost['id']?>" method="post"><div class="formbox">
					
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
							if(date('Y',$blogpost['publishtime']) == sprintf('%02d', $year)) echo ' SELECTED';
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
							if(date('n',$blogpost['publishtime']) == $month) echo ' SELECTED';
							echo '>' . date('F', mktime(0, 0, 0, $month, 10)) . '</option>';
							$month++;
						}
					?>
					</select>
					<select name="publishday">
					<?
						$day = 1;
						$numdays = date("t",$blogpost['publishtime']);
						while($day <= $numdays){
							echo '<option value="' . $day . '"';
							if(date('j',$blogpost['publishtime']) == $day) echo ' SELECTED';
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
							if(date('H',$blogpost['publishtime']) == sprintf('%02d', $hour)) echo ' SELECTED';
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
							if(date('i',$blogpost['publishtime']) == sprintf('%02d', $minute)) echo ' SELECTED';
							echo '>' . sprintf('%02d', $minute) . '</option>';
							$minute++;
						}
					?>
					</select>
					</div>
					</div>
				   
					
					<? //display rest of form box ?>
					<div class="formline"><label><?=$lang['title']?>:</label><div class="forminput"><input type="text" name="title" style="width:400px;" value="<?=$blogpost['title']?>" /></div></div>
					<div class="formline"><label><?=$lang['tagssepbycomma']?>:</label><div class="forminput"><input type="text" name="tags" style="width:400px;" value="<?
						$query = "SELECT * FROM cc_" . $tableprefix . "blogs_tags WHERE blog='" . $module['id'] . "' AND blogid='" . $blogpost['id'] . "'";
						$result = $z->query($query);
						while($row = $result->fetch_assoc()){
							echo $row['tag'] . ",";
						}
					?>" /></div></div>
					<p><?=$lang['content']?>:<br /><br />
					<textarea name="content"><?=$blogpost['content'];?></textarea>
					<p style="text-align:center;"><input type="submit" id="submitted" name="submit" value="<?=$lang['submit']?>" /><br /></p>
					</div><div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$siteroot?><?=$module['slug']?>/<?=$blogpost['slug']?>"><?=$lang['clicktopreview']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['addnewpost']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>"><?=$lang['returnto']?><?=$module['title']?></a></div></div>
					</form>
					<?
				}
			}
			break;
		
		//Post addition script
		case "add":
		
			//display header
			echo '<h2>' . $lang['addnewblogpost'] . '</h2>';
			
			//If submitted, add post to database
				if(isset($_POST['submit']))
				{
					$sValue = sanitize( $_POST['content'] ) ;
					$title = sanitizeText( $_POST['title'] ) ;
					$theDate = $_POST['publishyear'] . "-" . $_POST['publishmonth'] . "-" . $_POST['publishday'] . " " . $_POST['publishhour'] . ":" . $_POST['publishminute'];
					$publishtime = strtotime($theDate);
					
					//create slug
					$slug = preg_replace("/[^A-Za-z0-9 ]/", "", $title);
					$slug = str_replace(" ","-",$slug);
					$slug = str_replace("--","-",$slug);
					$slug = strtolower($slug);
					$slugfinal = $slug;
					$slugcount = 2;
					$query = "SELECT * FROM `cc_" . $tableprefix . "blogs` WHERE blog='" . $module['id'] . "' AND slug='" . $slugfinal . "' AND id!='" . $blogpost['id'] . "' LIMIT 1";
					$result = $z->query($query);
					while($result->num_rows > 0 || $slugfinal == "page" || $slugfinal=="search"){
						$slugfinal = $slug . "-" . $slugcount;
						$slugcount++;
						$query = "SELECT * FROM `cc_" . $tableprefix . "blogs` WHERE blog='" . $module['id'] . "' AND slug='" . $slugfinal . "' LIMIT 1";
						$result = $z->query($query);
					}
					
					$query="INSERT INTO cc_" . $tableprefix . "blogs(blog,content,title,publishtime,slug) VALUES('" . $module['id'] . "','" . $sValue . "','" . $title . "','" . $publishtime . "','" . $slug . "')";
					$result=$z->query($query);
					$newid=$z->insert_id;
					
					//create comment id and update table
					$commentid = $module['slug'] . '-' . $newid;
					$query="UPDATE cc_" . $tableprefix . "blogs SET commentid='" . $commentid . "' WHERE id='" . $newid . "'";
					$z->query($query);
					
					//insert new tags
					$tags = str_replace(", ",",",$_POST['tags']);
					$tags = explode(",",$tags);
					foreach($tags as $value){
						if($value != ""){
							$value = sanitizeText($value);
							$value = trim($value);
							$query = "INSERT INTO cc_".$tableprefix . "blogs_tags(blog,blogid,tag,publishtime) VALUES('" . $module['id'] . "','" . $newid . "','" . $value . "','" . $publishtime . "')";
							$z->query($query);
						}
					}
					
					echo '<div class="successbox">' . $lang['postadded'] . '</div>';
					?>
                    <div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$siteroot?><?=$module['slug']?>/<?=$slugfinal?>"><?=$lang['clicktopreview']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['addnewpost']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>&do=ed&blogid=<?=$newid?>"><?=$lang['editthispost']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>"><?=$lang['returnto']?><?=$module['title']?></a></div></div>
                    <?
				}
				
				
				
				//SHOW BLOG POST ADD FORM
				else{					
					
					//display form box ?>
					<form name="addpost" do="edit.php?moduleid=<?=$moduleid?>&do=add" method="post"><div class="formbox">
					
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
					<div class="formline"><label><?=$lang['title']?>:</label><div class="forminput"><input type="text" name="title" style="width:400px;" /></div></div>
					<div class="formline"><label><?=$lang['tagssepbycomma']?>:</label><div class="forminput"><input type="text" name="tags" style="width:400px;" /></div></div>
					<p><?=$lang['content']?>:<br /><br />
					<textarea name="content"></textarea>
					<p style="text-align:center;"><input type="submit" id="submitted" name="submit" value="<?=$lang['submit']?>" /><br /></p>
					</div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['addnewpost']?></a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=<?=$moduleid?>"><?=$lang['returnto']?><?=$module['title']?></a></div></div>
					</form>
					<?
			}
			break;
	}//switch
}//action

//If no action selected, display list of blog posts
else{
	//Select blog posts to show (1-20)
	if(isset($_GET['dp'])) $dp=filterint($_GET['dp']);
	else $dp=1;
	$ds =(($dp-1)*20);
	$query="SELECT * FROM cc_" . $tableprefix . "blogs WHERE blog='" . $moduleid . "' ORDER BY publishtime DESC LIMIT ". $ds .",20";
	$result=$z->query($query);
	$numposts=$result->num_rows;
	
	//Handle case for no posts
	if($numposts == 0) echo '<p>' . $lang['noblogposts'] . '</p>';
	
	//Display list of posts
	else{
	
		echo '<p>' . $lang['postlist'] . '</p><table border="0">';
		while($row=$result->fetch_assoc()){
			echo '<tr><td>' . date("Y-m-d g:i:s",$row['publishtime']) . '</td><td width="470px">' . $row['title'] . '</td><td><a href="' . $siteroot . $module['slug'] .'/'.$row['slug'].'">' .$lang['preview']. '</a></td><td><a href="?moduleid=' . $moduleid . '&do=ed&blogid=' . $row['id'] . '">Edit</a></td><td><a href="?moduleid=' . $moduleid . '&do=del&blogid=' . $row['id'] . '">Delete</a></td></tr>';
		}
		echo '</table>';
		
		//Prev/next page
		$query="SELECT * FROM cc_" . $tableprefix . "blogs WHERE blog='" . $moduleid . "'";
		$result=$z->query($query);
		$numposts=$result->num_rows;
		$pagecount = ($numposts/20);
		if($numposts%20!=0 && $numposts > 20) $pagecount++;
		if($pagecount > 1)
		{
			$count = 1;
			echo '<div class="dashtextwrap">' . $lang['page'];
			while($count <= $pagecount){ 
				echo '&nbsp;&nbsp;&nbsp;'; 
				if($count!=$dp) echo '<a href="edit.php?moduleid=' . $moduleid . '&dp=' . $count . '">';
				else echo '<span style="font-weight:bold;">';
				echo $count . ' ';
				if($count!=$dp) echo '</a>';
				else echo '</span>';
				$count++; 
			}
			echo '</div>';
		}
	}
	
	//Add post link
	echo '<div style="height:15px;"></div><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=add">' . $lang['addnewpost'] . '</a></div></div>';
}//no action
	
}	
	
?>