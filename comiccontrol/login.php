<?
//initialize needed configuration
include("dbconfig.php");
include('initialize.php');

//logout
if($_GET['action'] == "logout"){ 
					setcookie('loginhash', "", time() - 3600, "/", $_SERVER['HTTP_HOST']);
					setcookie('username', "", time() - 3600, "/", $_SERVER['HTTP_HOST']); $logout = true; }

//go to index.php if not logging out and authorization does not check out
if(authCheck() && !$logout)  header("Location:" . $root . "index.php");
if(isset($_POST['submit'])) $submitted = true; else $submitted = false;

//display login page
 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>ComicControl.</title>
<link rel="stylesheet" type="text/css" href="comiccontrol.css" />
</head>
<body>
<div id="loginwrapper">
<div id="loginheader"></div>
<?
if($logout){
	echo '<p class="successbox">' . $lang['loggedout'] . '</p>';
}
if($submitted){
	echo '<p class="errorbox">' . $lang['loginincorrect'] . '</p>';
}
?>
<p><?=$lang['pleaselogin'];?></p>
<form method="post" action="login.php"><br />
<label for="username"><?=$lang['username']?>: </label><input name="username" type="text" size="30" /><br /><br />
<label for="password"><?=$lang['password']?>: </label><input name="password" type="password" size="30" /><br /><br />
<input name="submit" type="submit" value="<?=$lang['submit']?>" />
</form>
<div id="loginfooter"></div>
</div>