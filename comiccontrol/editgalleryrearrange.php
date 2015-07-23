<?
//editgalleryedit.php
//image editing script

if(authCheck()){

//get gallery info
$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE id='" . $moduleid . "' LIMIT 1";
$gallery = fetch($query);

//display header
echo '<h2>' . $lang['rearrangegalleryimages'] . '</h2>';

//drag and drop styles
?>
<style type="text/css"><!--
	
	#boxes {
		font-family: Arial, sans-serif;
		list-style-type: none;
		margin: 0 auto;
		text-align:center;
		padding: 0px;
		width: 642px;
	}
	#boxes li {
		cursor: move;
		position: relative;
		float: left;
		margin: 15px;
		width: 130px;
		height: 130px;
		text-align: center;
		padding-top: 5px;
		text-align: center;
	}
	td{
		border-bottom: 1px solid black;
	}
</style>

<? //drag and drop js ?>
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
	    f.setAttribute('action','edit.php?moduleid=<?=$moduleid?>&do=rearrange');
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

//rearrange photos if form submitted
if(isset($_POST['savechanges'])){
	
	$order = explode(",",$_POST['order']);
	$count = 1;
	foreach($order as $value){
		$query = "UPDATE cc_".$tableprefix . "galleries SET porder='" . $count . "' WHERE id='" . $value . "' AND gallery='" . $module['id'] . "'";
		$z->query($query);
		$count++;
	}
	echo '<div class="successbox">' . $lang['changessaved'] . '</div>';
	
}

//get all images in gallery
$query = "SELECT * FROM cc_" . $tableprefix . "galleries WHERE gallery='" . $module['id'] . "' ORDER BY porder ASC";
$result = $z->query($query);

//if no images, throw error
if($result->num_rows == 0){
	echo '<p>' . $lang['noimages'] . '</p>';
}

//otherwise, output images for rearranging
else{
	?>
	<p><?=$lang['canrearrange'];?></p><div class="formbox"><form method="post" onsubmit="getphotos(this)" action=""><ul id="boxes">
	<?
	$result = $z->query($query);
	while($row = $result->fetch_assoc()){
		echo '<li class="box" id="' . $row['id'] . '"><img src="../uploads/' . $row['thumbname'] . '" /></li>';
	}
	echo '</ul><div style="clear:left"></div><br /><br /><p style="text-align:center"><input type="submit" name="savechanges" value="Save Changes" /></p></form></div>';
}

?>
<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=edit"><?=$lang['editanimage']?></a></div></div>
<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>&do=add"><?=$lang['addanimage']?></a></div></div>
<div class="ccbuttoncont"><div class="ccbutton"><a href="<?=$root?>edit.php?moduleid=<?=$moduleid?>"><?=$lang['returnto'];?><?=$module['title']?></a></div></div>
<?
}
?>