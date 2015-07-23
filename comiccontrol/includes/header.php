<? 
include('dbconfig.php'); 
include('initialize.php');
include('functions.php');
if(!authCheck()) header("Location:" . $root . "login.php");
//set $_GET variables

$moduleid = sanitizeAlphanumeric($_GET['moduleid']);
$do = sanitizeAlphanumeric($_GET['do']);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ComicControl.</title>
<link rel="stylesheet" type="text/css" href="<?=$root?>comiccontrol.css" />
<script type="text/javascript" src="<?=$root?>includes/jquery.js"></script>
<script type="text/javascript" src="<?=$root?>tinymce/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript">
tinymce.init({
    selector: "textarea", 
    menubar: "tools table format view insert edit",
    plugins: "image link table textcolor media jbimages code",
		height : 400,
    menu : {
        edit   : {title : 'Edit'  , items : 'code | undo redo | cut copy paste pastetext | selectall'},
        insert : {title : 'Insert', items : 'link media image jbimages | hr'},
        view   : {title : 'View'  , items : 'visualaid'},
        format : {title : 'Format', items : 'bold italic underline strikethrough superscript subscript | formats | removeformat'},
        table  : {title : 'Table' , items : 'inserttable tableprops deletetable | cell row column'},
        tools  : {title : 'Tools' , items : 'spellchecker code'}
    },
	relative_urls: false
 });
</script>
</head>
<body>
<div id="header"><a href="index.php">ComicControl.</a><a id="logout" href="login.php?action=logout">Logout</div></div>
<div id="menu">
<ul>
<?
	$break = Explode('/', $_SERVER['PHP_SELF']);
	$pfile = $break[count($break) - 1]; 
	$query="SELECT * FROM cc_" . $tableprefix . "modules";
	$result=$z->query($query);
	while($row = $result->fetch_assoc())
	{
		if($row['type'] != "custom"){
			if(isset($_GET['id']) && $_GET['id'] == $row['id'])
			{	echo '<li class="selected"><a href="edit.php?moduleid='.$row['id'].'">'.$row['title'].'</a></li>';	}
			else
			{	echo '<li><a href="edit.php?moduleid='.$row['id'].'">'.$row['title'].'</a></li>';	}
		}
	}
	echo '<li';
	if($pfile == "imageupload.php") echo ' class="selected"';
	echo '><a href="imageupload.php">Image Upload</a></li>';
	$filearr = explode("/",dirname(__FILE__));
	$inputfilename = "/" . $filearr[1] . "/" . $filearr[2] . "/inputaddata.php";
	if(file_exists($inputfilename)){
		echo '<li><a href="viewadstats.php">' . $lang['adstats'] . '</a></li>';
	}
?>
</ul>
</div>
<div id="content">