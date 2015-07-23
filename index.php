<? 
//header('X-Frame-Options: sameorigin');
include('comiccontrol/dbconfig.php');
include('comiccontrol/initialize.php');
include('comiccontrol/functions.php');
include('custom.php');


//include template
if($moduleinfo['customtemplate'] != ""){
	include("templates/" . $moduleinfo['customtemplate']);
}else{
	
	switch($moduleinfo['type']){
		case "comic":
			include("templates/comicpage.php");
			break;
		case "blog":
			include("templates/blogpage.php");
			break;
		case "gallery":
			include("templates/gallerypage.php");
			break;
		case "page":
			include("templates/page.php");
			break;
		case "archive":
			include("templates/archive.php");
			break;
	}

}

?>