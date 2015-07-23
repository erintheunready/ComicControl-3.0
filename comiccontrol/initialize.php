<?
//initialize.php
//Initialization script: create sanitization functions and assign configuration variables
//Includes editing and deleting comics as well as displays comics that can be edited in nested list format

$preview = false;

//DATA MANAGEMENT AND AUXILIARY FUNCTIONS

//sanitize() - standard input sanitization function for mysql
function sanitize($input){
	global $z;
	$input = mysqli_real_escape_string($z,$input);
	return $input;
}

//filterint() - filter integers
function filterint($input){
	$input = filter_var($input,FILTER_SANITIZE_NUMBER_INT);
	return $input;
}

//sanitizeText() - sanitize text that has to be outputted as html
function sanitizeText($input){
	global $z;
	$input = str_replace("'","&#39;",$input);
	$input = str_replace("’","&#39;",$input);
	$input = str_replace("â€™","&#39;",$input);
	$input = str_replace('"',"&quot;",$input);
	$input = mysqli_real_escape_string($z,$input);
	return $input;
}

//sanitizeAlphanumeric() - remove all except alphanumeric for ULTIMATE SANITIZATION
function sanitizeAlphanumeric($input){
	$sanitized = preg_replace("/[^A-Za-z0-9 ]/", '', $input);
	return $sanitized;
}

//sanitizeSlug() - remove all except alphanumeric and hyphens
function sanitizeSlug($input){
	return preg_replace('/[^A-Za-z0-9\-]/', '', $input);
}

//fetch() - get one result from query
function fetch($query){
	global $z;
	
	$result = $z->query($query);
	$row = $result->fetch_assoc();
	return $row;
}

//isMobile() - check if user is mobile user
function isMobile(){
	global $_SERVER;
	
	$useragent=$_SERVER['HTTP_USER_AGENT'];
	if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))){
		return true;
	}else{
		return false;
	}
	
}

//log in user
function authCheck(){
	global $tableprefix;
	global $z;
	global $_COOKIE;
	global $_SERVER;	
	
	if(isset($_POST['username']))
	{
		$password = sanitize($_POST['password']);
		$username = sanitize($_POST['username']);
		$query = "SELECT * FROM cc_" . $tableprefix . "users WHERE username='" . $username . "' LIMIT 1";
		$result = $z->query($query);
		if($result->num_rows == 1){
			$userinfo = $result->fetch_assoc();
			$query="SELECT * FROM cc_" . $tableprefix . "users WHERE username='".$username."' AND password='" . md5($password . $userinfo['salt']) . "' LIMIT 1";
			$result = $z->query($query);
			if($result->num_rows == 1){
				$userinfo = $result->fetch_assoc();
				$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
				$randstr = '';
				for ($i = 0; $i < 32; $i++) {
				  $randstr .= $characters[rand(0, strlen($characters) - 1)];
				}
				$query = "UPDATE cc_" . $tableprefix . "users SET loginhash='" . sha1($userinfo['username'] . $userinfo['salt'] . $randstr) . "' WHERE id='" . $userinfo['id'] . "' LIMIT 1";
				$z->query($query);
				setcookie('loginhash', $randstr, time() + (432000), "/", $_SERVER['HTTP_HOST']);
				setcookie('username', $username, time() + (432000), "/", $_SERVER['HTTP_HOST']);
				setcookie('hashtime', time(), time() + (432000), "/", $_SERVER['HTTP_HOST']);
				return true;
			}
		}
	}
	else if(isset($_COOKIE['username']) && isset($_COOKIE['loginhash']) && isset($_COOKIE['hashtime'])){
		$loginhash = sanitize($_COOKIE['loginhash']);
		$username = sanitize($_COOKIE['username']);
		$query = "SELECT * FROM cc_" . $tableprefix . "users WHERE username='" . $username . "' LIMIT 1";
		$result = $z->query($query);
		if($result->num_rows == 1){
			$userinfo = $result->fetch_assoc();
			$query = "SELECT * FROM cc_" . $tableprefix . "users WHERE username='" . $userinfo['username'] . "' AND loginhash='" . sha1($userinfo['username'] . $userinfo['salt'] . $loginhash) . "' LIMIT 1";
			$result = $z->query($query);
			if($result->num_rows == 1){
				if((time() - $_COOKIE['hashtime']) > 3600){
					$characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
					$randstr = '';
					for ($i = 0; $i < 32; $i++) {
					  $randstr .= $characters[rand(0, strlen($characters) - 1)];
					}
					$query = "UPDATE cc_" . $tableprefix . "users SET loginhash='" . sha1($userinfo['username'] . $userinfo['salt'] . $randstr) . "' WHERE id='" . $userinfo['id'] . "' LIMIT 1";
					$z->query($query);
					setcookie('loginhash', $randstr, time() + (432000), "/", $_SERVER['HTTP_HOST']);
					setcookie('username', $username, time() + (432000), "/", $_SERVER['HTTP_HOST']);
					setcookie('hashtime', time(), time() + (432000), "/", $_SERVER['HTTP_HOST']);
				}
				return true;
			}
		}
	}
	return false;
}


//QUERY FUNCTION
function fetchoption($optionname){
	global $z;
	global $tableprefix;
	
	$optionname=sanitizeAlphanumeric($optionname);
	
	$query = "SELECT * FROM cc_" . $tableprefix . "options WHERE optionname='" . $optionname . "' LIMIT 1";
	
	$result = $z->query($query);
	$row = $result->fetch_assoc();
	return $row['optionvalue'];
	
}

//SITE INFO
$sitetitle = fetchoption("sitetitle");
$disqusname = fetchoption("disqusname");
if($_GET['id'] > 0 && $_GET['id'] < 73){ $disqusname = "works-in-progress"; }
$root = fetchoption("root");
$siteroot = fetchoption("siteroot");
$relativeroot = fetchoption("relativeroot");

//TIMEZONE
$timezone = fetchoption("timezone");
$timezoneshort = fetchoption("timezoneshort");

date_default_timezone_set($timezone);

//OPTIONS
$dateformat = fetchoption("dateformat");
$timeformat = fetchoption("timeformat");
$navaux = fetchoption("navaux");
$previewpage = fetchoption("preview");
$language = fetchoption("language");
$navorder = fetchoption("navorder");
$maxwidth = fetchoption("maxwidth");
$usemaxwidth = fetchoption("usemaxwidth");
$thumbwidth = fetchoption("thumbwidth");
$thumbheight = fetchoption("thumbheight");

//SLUG PARSING
$url = "$_SERVER[REQUEST_URI]";
$url = substr($url,(strlen($relativeroot)+1));
$slug = preg_replace('/[^a-zA-Z0-9\-\/.?=]/', '', $url);
$slugarr = explode("/",$slug);
foreach($slugarr as $key=>$value){
	if(strpos($value,"?") > -1) {
		$str = substr($value,0,strpos($value,"?"));
		$slugarr[$key] = $str;
	}
}

//MATCH TO OLD MODULES
$query = "SELECT * FROM cc_" . $tableprefix . "modules";
$result = $z->query($query);
while($row = $result->fetch_assoc()){
	if($slugarr[0] == ""){
		$slugarr[0] = "index.php";
	}
	if($slugarr[0] == $row['filename']){
		$slugarr[0] = $row['slug'];
		if($row['type'] == "comic"){
			$id = preg_replace('[\D]', '', $_GET['id']);
			if($id != ""){
				$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $row['id'] . "' AND id='" . $id . "' AND publishtime < " . time() . " LIMIT 1";
				$result = $z->query($query);
				if($result->num_rows > 0){
					$row = $result->fetch_assoc();
					$slugarr[1] = $row['slug'];
				}
			}
		}
	}
}

//SET MODULE
$query = "SELECT * FROM cc_" . $tableprefix . "modules WHERE slug='" . $slugarr[0] . "' LIMIT 1";
$moduleinfo = fetch($query);


//SET LANGUAGE VARIABLES
include('languages/' . $language . '.php');
if($moduleinfo != "") include('languages/user-' . $moduleinfo['language'] . '.php');

//BUILD PREVIEW BAR IF LOGGED IN

if(authCheck()){
	$previewbar='<style>
		html{
			width:100% !important;
			margin-top:40px !important;
		}
		.cc-previewbar{
			width:100%;
			height:40px;
			background:#000;
			color:#fff;
			font-family:Georgia, "Times New Roman", Times, serif;
			position:fixed;
			top:0;
			z-index:100;
		}
		.cc-leftside{
			float:left;
			width:20%;
			font-size:20px;
			padding:10px;
		}
		.cc-rightside{
			float:right;
			width:75%;
			font-size:14px;
			text-align:right;
			padding:10px;
		}
		.cc-previewbar a{
			color:#fff;
			text-decoration:none;
		}
	</style>
	<div class="cc-previewbar"><div class="cc-leftside"><a href="' . $root . '">ComicControl.</a></div><div class="cc-rightside">';
	
	
	switch($moduleinfo['type']){
		case "comic":
			$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $moduleinfo['id'] . "' AND slug='" . $slugarr[1] . "' LIMIT 1";
			$result = $z->query($query);
			if($result->num_rows > 0){
				$comicinfo = $result->fetch_assoc();
				if($comicinfo['publishtime'] > time()){
					$previewbar .= $lang['previewbar'] . " - ";
				}else{
					$preview = false;
				}
				$previewbar .= $comicinfo['comicname'] .' - <a href="' . $root . 'edit.php?moduleid=' . $moduleinfo['id'] . '&do=add">' . $lang['addanothercomic'] . '</a> | <a href="' . $root . 'edit.php?moduleid=' . $moduleinfo['id'] . '&do=edit&comicid=' . $comicinfo['id'] . '&edit=edit">' . $lang['editthiscomic'] . '</a> | ';
			}else{
				$previewbar .= '<a href="' . $root . 'edit.php?moduleid=' . $moduleinfo['id'] . '&do=add">' . $lang['addanothercomic'] . '</a> | ';
			}
			$previewbar .= '<a href="' . $root . 'edit.php?moduleid=' . $moduleinfo['id'] . '">' . $lang['returnto'] . $moduleinfo['title'] . '</a>';
			$previewbar .= '</div></div>';
			break;
		case "blog":
			$query = "SELECT * FROM cc_" . $tableprefix . "blogs WHERE blog='" . $moduleinfo['id'] . "' AND slug='" . $slugarr[1] . "' LIMIT 1";
			$result = $z->query($query);
			if($result->num_rows > 0){
				$bloginfo = $result->fetch_assoc();
				if($bloginfo['publishtime'] > time()){
					$previewbar .= $lang['previewbar'] . " - ";
				}
				$previewbar .= $bloginfo['title'] . ' - ';
			}
			$previewbar .= '<a href="' . $root . 'edit.php?moduleid=' . $moduleinfo['id'] . '&action=add">' . $lang['addanotherpost'] . '</a> | ';
			if($bloginfo != "") $previewbar .= '<a href="' . $root . 'edit.php?moduleid=' . $moduleinfo['id'] . '&action=ed&blogid=' . $bloginfo['id'] . '">' . $lang['editthispost'] . '</a> | ';
			$previewbar .= '<a href="' . $root . 'edit.php?moduleid=' . $moduleinfo['id'] . '">' . $lang['returnto'] . $moduleinfo['title'] . '</a></div></div>';
			break;
		case "gallery":
			$prevmid = $moduleinfo['title'] . ' - <a href="' . $root . 'edit.php?moduleid=' . $moduleinfo['id'] . '">' . $lang['addanotherimage'] . '</a> | <a href="' . $root . 'edit.php?moduleid=' . $moduleinfo['id'] . '">' . $lang['returnto'] . $moduleinfo['title'] . '</a>';
			$previewbar .= $prevmid;
			$previewbar .= '</div></div>';
			break;
		case "page":
			$prevmid = $moduleinfo['title'] . ' - <a href="' . $root . 'edit.php?moduleid=' . $moduleinfo['id'] . '">' . $lang['editthispage'] . '</a>';
			$previewbar .= $prevmid;
			$previewbar .= '</div></div>';
			break;
		default:
			$previewbar .= '</div></div>';
			break;
	}
}else{
	if($moduleinfo['type'] == "comic" && $slugarr[1] != "" && $slugarr[1] != "archive"){
		$query = "SELECT * FROM cc_" . $tableprefix . "comics WHERE comic='" . $moduleinfo['id'] . "' AND slug='" . $slugarr[1] . "' LIMIT 1";
		$result = $z->query($query);
		if($result->num_rows <= 0){
			header("HTTP/1.0 404 Not Found");
			http_response_code(404);
			include('404.php');
			die();
		}
	}
}
?>