<?
//editpage.php
//Page editing script
if(authCheck()){
//If submitted, input information to table
if(isset($_POST['submit']))
{
	$sValue = sanitize( $_POST['contentarea'] ) ;
	$query="UPDATE cc_".$tableprefix . "pages SET content='". $sValue ."' WHERE id='". $moduleid . "'";
	$z->query($query);
	echo '<div class="successbox">' . $lang['pageedited'] . '</div>';
}

//Display editor
$query="SELECT * FROM cc_".$tableprefix . "pages WHERE id='".$moduleid."'";
$result=$z->query($query);
$page=$result->fetch_assoc();
echo '<p>' . $lang['editthispagetext'];
echo '<form method="post" action="edit.php?moduleid=' . $moduleid . '">';
echo '<textarea name="contentarea">' . $page['content'] . '</textarea>';
echo '<br /><br /><input name="submit" value="' . $lang['submit'] . '" type="submit" /></form>';
}
?>