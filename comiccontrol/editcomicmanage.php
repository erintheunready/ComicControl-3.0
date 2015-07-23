<? 
//editcomicmanage.php
//Manage comic storylines

if(authCheck()){

//set storylineid
$storylineid = sanitizeAlphanumeric($_GET['storylineid']);
$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE id='" . $storylineid . "' LIMIT 1";
$result = $z->query($query);
if($result->num_rows == 0){
	$storylineid = 0;
}

//drop in css and javascript for drag and drop boxes ?>
<style type="text/css"><!--
	
	#boxes {
		font-family: Arial, sans-serif;
		list-style-type: none;
		margin: 0 auto;
		text-align:center;
		padding: 0px;
		width: 820px;
	}
	#boxes li {
		cursor: move;
		background: #bbbbbb;
		margin:20px;
		position: relative;
		width: 520px;
		padding: 10px;
		text-align: center;
		padding-top: 5px;
		text-align: center;
	}
	.leftboxes{
		width:600px;
		float:left;
	}
</style>
<script language="JavaScript" type="text/javascript" src="draganddrop/core.js"></script>
<script language="JavaScript" type="text/javascript" src="draganddrop/events.js"></script>
<script language="JavaScript" type="text/javascript" src="draganddrop/css.js"></script>
<script language="JavaScript" type="text/javascript" src="draganddrop/coordinates.js"></script>
<script language="JavaScript" type="text/javascript" src="draganddrop/drag.js"></script>
<script language="JavaScript" type="text/javascript" src="draganddrop/dragsort.js"></script>
<script language="JavaScript" type="text/javascript" src="draganddrop/cookies.js"></script>
<script language="JavaScript" type="text/javascript"><!--
var dragsort = ToolMan.dragsort()
var junkdrawer = ToolMan.junkdrawer()

window.onload = function() {
	junkdrawer.restoreListOrder("boxes")
	dragsort.makeListSortable(document.getElementById("boxes"),
			saveOrder)
}

function verticalOnly(item) {
	item.toolManDragGroup.verticalOnly()
}

function speak(id, what) {
	var element = document.getElementById(id);
	element.innerHTML = 'Clicked ' + what;
}

function saveOrder(item) {
	var group = item.toolManDragGroup
	//alert(group.factory);
	var list = group.element.parentNode
	var id = list.getAttribute("id")
	if (id == null) return
	group.register('dragend', function() {
		ToolMan.cookies().set("list-" + id, 
				junkdrawer.serializeList(list), 365)
	})
}

function getphotos(f){
	f.setAttribute('target','_self');
	f.setAttribute('action','edit.php?moduleid=<?=$moduleid?>&do=manage&storylineid=<?=$storylineid?>');
	var order = junkdrawer.serializeList(document.getElementById('boxes'));
	var newnums = order[0];
	for(i=1; i<order.length; i++){
		newnums += ",";
		newnums += order[i];
	}
	var neworder = document.createElement('input');
	neworder.type = "hidden";
	neworder.name = "order";
	neworder.value = newnums;
	f.appendChild(neworder);
	f.submit();
}
</script>

<?

//save storyline order if submitted
if(isset($_POST['savechanges']) && $_POST['savechanges'] != ""){
	$order = explode(",",$_POST['order']);
	$count = 0;
	foreach($order as $value){
		$query = "UPDATE cc_" . $tableprefix . "comics_storyline SET sorder='" . $count . "' WHERE id='" . $value . "'";
		$z->query($query) or die(mysqli_error($z));
		$count++;
	}
	echo '<div class="successbox">' . $lang['changessaved'] . '</div>';
}
	
//switch between functions based on manage
switch($_GET['manage'])
{

		
	//storyline addition
	case "add":
		echo '<h2>' . $lang['addstoryline'] . '</h2>';
		//add storyline if name submitted
		if(isset($_POST['storylinename']) && $_POST['storylinename'] != ""){
			$query = "SELECT * FROM cc_" . $tableprefix . "comics_storyline WHERE parent='" . $storylineid . "' AND comic='" . $moduleid . "' ORDER BY sorder DESC LIMIT 1";
			$latestchapter = fetch($query);
			$storylinename = sanitizeText($_POST['storylinename']);
			$parent = filterint($_POST['parent']);
			$query = "INSERT INTO `cc_" . $tableprefix . "comics_storyline` (`name`, `sorder`,`parent`,`comic`) VALUES ('" . $storylinename . "', '" . ($latestchapter['sorder']+1) . "','" . $parent . "','" . $module['id'] . "')";
			$result = $z->query($query);
			echo '<div class="successbox">' . $lang['storylineadded'] . '</div>';
		
			//show nav buttons
			echo '<p></p><div class="ccbuttoncont"><div class="ccbutton"><a href="' . $root . 'edit.php?moduleid=' . $module['id'] . '&do=manage&manage=add&storylineid=' . $storylineid . '">' . $lang['addanotherstoryline'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="' . $root . 'edit.php?moduleid=' . $module['id'] . '&do=manage">' . $lang['returnto'] . $lang['storylinemanagement'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="' . $root . 'edit.php?moduleid=' . $module['id'] . '">' . $lang['returnto'] . $module['title'] . '</a></div></div>';
		}
		
		//show storyline addition form
		else{
		?>
		<form method="post" action="edit.php?moduleid=<?=$moduleid?>&do=manage&manage=add&storylineid=<?=$storylineid?>" name="addstoryline">
		<p></p><div class="formline"><label><?=$lang['storylinename']?>: </label><div class="forminput"><input type="text" style="width:300px;" name="storylinename" /></div></div>
		<? //display storyline dropdown ?>
            <div class="formline"><label><?=$lang['parentstoryline']?>:</label><div class="forminput"><select name="parent" style="width:400px;"><option value="0"><?=$lang['noparent']?></option>
            <?
			$query = "SELECT * FROM cc_".  $tableprefix . "comics_storyline WHERE comic='" . $module['id'] . "' AND parent='0' ORDER BY sorder ASC";
			$result = $z->query($query);
			$count = array();
			$numrows = array();
			$parent = 0;
			$results = array();
			$rows = array();
			$temp = 0;
			while($currrow = $result->fetch_assoc()){
				$parent = $currrow['id'];
				$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE comic='" . $module['id'] . "' AND parent='" . $parent . "' ORDER BY sorder ASC";
				$results[$parent] = $z->query($query);
				$numrows[$parent] = $results[$parent]->num_rows;
				$count[$parent] = 0;
				echo '<option value="' . $currrow['id'] . '"';
				if($storylineid == $currrow['id']) echo ' SELECTED';
				echo '>' . $currrow['name'] . '</option>';
				$spacecount = 1;
				while($count[$parent] < $numrows[$parent]){
					$currspace = 0;
					$count[$parent]++;
					$currrow2 = $results[$parent] -> fetch_assoc();
					echo '<option value="' . $currrow2['id'] . '"';
					if($storylineid == $currrow['id']) echo ' SELECTED';
					echo '>';
					while($currspace < $spacecount){
						echo '&nbsp;&nbsp;';
						$currspace++;
					}
					echo $currrow2['name'];
					echo '</option>';
					$temp = $parent;
					$parent = $currrow2['id'];
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
            <p style="text-align:center"><input type="submit" name="submit" value="<?=$lang['addstoryline']?>" /></p>
		</form>
		<?
		
		//show nav buttons
		echo '<p></p><div class="ccbuttoncont"><div class="ccbutton"><a href="' . $root . 'edit.php?moduleid=' . $module['id'] . '&do=manage">' . $lang['returnto'] . $lang['storylinemanagement'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="' . $root . 'edit.php?moduleid=' . $module['id'] . '">' . $lang['returnto'] . $module['title'] . '</a></div></div>';
		}
		
		break;
		
	//storyline editing
	case "edit":
	
		//display header
		echo '<h2>' . $lang['editstoryline'] . '</h2>';
	
		//save edits if form submitted
		if(isset($_POST['storylinename']) && $_POST['storylinename'] != ""){
			$storylinename = sanitizeText($_POST['storylinename']);
			$parent = filterint($_POST['parent']);
			$query = "UPDATE `cc_" . $tableprefix . "comics_storyline` SET name='" . $storylinename . "', parent='" . $parent . "' WHERE id='" . $storylineid . "' AND comic='" . $moduleid . "'";
			$result = $z->query($query);
			echo '<div class="successbox">' . $lang['storylineedited'] . '</div>';
		}
		
		//show storyline edit form
		$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE id='" . $storylineid . "' AND comic='" . $moduleid . "'";
		$result = $z->query($query);
		$row = $result->fetch_assoc();
		?>
		<form method="post" action="edit.php?moduleid=<?=$moduleid?>&do=manage&manage=edit&storylineid=<?=$row['id']?>" name="editstoryline">
		<div class="formline"><label><?=$lang['storylinename']?>: </label><div class="forminput"><input type="text" style="width:300px;" name="storylinename" value="<?=$row['name']?>" /></div></div> 
		<? //display storyline dropdown ?>
            <div class="formline"><label><?=$lang['parentstoryline']?>:</label><div class="forminput"><select name="parent" style="width:400px;"><option value="0"><?=$lang['noparent']?></option>
            <?
			$query = "SELECT * FROM cc_".  $tableprefix . "comics_storyline WHERE comic='" . $module['id'] . "' AND parent='0' ORDER BY sorder ASC";
			$result = $z->query($query);
			$count = array();
			$numrows = array();
			$parent = 0;
			$results = array();
			$rows = array();
			$temp = 0;
			while($currrow = $result->fetch_assoc()){
				$parent = $currrow['id'];
				$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE comic='" . $module['id'] . "' AND parent='" . $parent . "' ORDER BY sorder ASC";
				$results[$parent] = $z->query($query);
				$numrows[$parent] = $results[$parent]->num_rows;
				$count[$parent] = 0;
				echo '<option value="' . $currrow['id'] . '"';
				if($currrow['id'] == $row['parent']) echo ' SELECTED';
				echo '>' . $currrow['name'] . '</option>';
				$spacecount = 1;
				while($count[$parent] < $numrows[$parent]){
					$currspace = 0;
					$count[$parent]++;
					$currrow2 = $results[$parent] -> fetch_assoc();
					echo '<option value="' . $currrow2['id'] . '"';
					if($currrow2['id'] == $row['parent']) echo ' SELECTED';
					echo '>';
					while($currspace < $spacecount){
						echo '&nbsp;&nbsp;';
						$currspace++;
					}
					echo $currrow2['name'];
					echo '</option>';
					$temp = $parent;
					$parent = $currrow2['id'];
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
            <p style="text-align:center"><input type="submit" name="submit" value="<?=$lang['editstoryline']?>" /></p></form>
		
		<?
		break;
		
	//delete storyline
	case "delete":
		//display header
		echo '<h2>' . $lang['deletestoryline'] . '</h2>';
		
		if(isset($storylineid) && $storylineid != ""){
			
			//delete storyline if yes said
			if(isset($_POST['delete']) && $_POST['delete'] != ""){
				$query = "DELETE FROM `cc_" . $tableprefix . "comics_storyline` WHERE id='" . $storylineid . "' AND comic='" . $moduleid . "'";
				$z->query($query);
				echo '<div class="successbox">' . $lang['storylinedeleted'] . '</div>';
				echo '<div class="ccbuttoncont"><div class="ccbutton"><a href="' . $root . 'edit.php?moduleid=' . $module['id'] . '&do=manage">' . $lang['returnto'] . $lang['storylinemanagement'] . '</a></div></div><div class="ccbuttoncont"><div class="ccbutton"><a href="' . $root . 'edit.php?moduleid=' . $module['id'] . '">' . $lang['returnto'] . $module['title'] . '</a></div></div>';
				
			//ask if you're deleting storyline
			}else{
				$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE id='" . $storylineid . "' AND comic='" . $moduleid . "'";
				$result = $z->query($query);
				$row = $result->fetch_assoc();
				?>
				<p style="text-align:center"><?=$lang['wanttodelete']?><?=$row['name']?>?</p>
				<form method="post" action="edit.php?moduleid=<?=$moduleid?>&do=manage&manage=delete&storylineid=<?=$row['id']?>" name="deletestoryline">
				<p style="text-align:center;"><input type="submit" name="delete" value="<?=$lang['yes']?>" /> <input type="button" value="<?=$lang['no']?>" onclick="self.location='edit.php?do=manage&moduleid=<?=$moduleid?>'" /></p>
				</form><div style="clear:both; height:20px;"></div>
				<?
			}	
		}
		break;
	}

if($_GET['manage'] != "delete" && $_GET['manage'] != "add"){
	//loop through photos to display them with edit options and rearrange
	echo '<p>' . $lang['rearrangestorylines'] . '</p>
	<form method="post" onsubmit="getphotos(this)" action="" class="leftboxes"><ul id="boxes">';
	$query = "SELECT * FROM cc_".$tableprefix . "comics_storyline WHERE parent='" . $storylineid . "' AND comic='" . $moduleid . "' ORDER BY sorder ASC";
	$result = $z->query($query);
	while($row = $result->fetch_assoc()){
		echo '<li class="box" id="' . $row['id'] . '">' .$row['name'] . ' <a href="edit.php?moduleid=' . $moduleid . '&do=manage&manage=edit&storylineid=' . $row['id'] . '">[edit]</a> <a href="edit.php?moduleid=' . $moduleid . '&do=manage&manage=delete&storylineid=' . $row['id'] . '">[delete]</a></li>';
	}
	echo '</ul><div style="clear:left"></div><br /><br /><p style="text-align:center"><input type="submit" name="savechanges" value="' . $lang['savechanges'] . '" /></p></form><form name="capform" action="edit.php?moduleid=' . $moduleid . '" method="post" style="text-align:center"></form><div style="float:left; width:200px;"><div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=manage&manage=add&storylineid=' . $storylineid . '">' . $lang['addstoryline'] . '</a></div></div>';
	if($storylineid != 0){
		echo '<div class="ccbuttoncont"><div class="ccbutton"><a href="edit.php?moduleid=' . $moduleid . '&do=manage">' . $lang['returntostoryline'] . '</a></div></div>';
	}
	echo '<div class="ccbuttoncont"><div class="ccbutton"><a href="' . $root . 'edit.php?moduleid=' . $module['id'] . '">' . $lang['returnto'] . $module['title'] . '</a></div></div>';
}

}

?>