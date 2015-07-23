<?

if(authCheck()){

ini_set("memory_limit","512M");
set_time_limit(60);
		// make a note of the current working directory, relative to root.
$directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);


//error handler
function error($error, $location, $seconds = 5)
{
	echo '<script type="text/javascript" lang="javascript">alert("'.$error.'");window.location="edit.php?do=add";</script>';
}



// make a note of the location of the upload form in case we need it
$uploadForm = $root . "edit.php?do=add";

// make a note of the location of the success page
$uploadSuccess = $root . "edit.php?do=add&upload=success";


// Now let's deal with the upload

// possible PHP upload errors
$errors = array(1 => 'php.ini max file size exceeded',
                2 => 'html form max file size exceeded',
                3 => 'file upload was only partial',
                4 => 'no file was attached');

// check the upload form was actually submitted else print the form
isset($_POST['submit'])
    or error('the upload form is neaded', $uploadForm);

// check for PHP's built-in uploading errors
($_FILES[$fieldname]['error'] == 0)
    or error($errors[$_FILES[$fieldname]['error']], $uploadForm);
    
// check that the file we are working on really was the subject of an HTTP upload
is_uploaded_file($_FILES[$fieldname]['tmp_name'])
    or error('not an HTTP upload', $uploadForm);
    
// validation... since this is an image upload script we should run a check  
// to make sure the uploaded file is in fact an image. Here is a simple check:
// getimagesize() returns false if the file tested is not an image.
getimagesize($_FILES[$fieldname]['tmp_name'])
    or error('only image uploads are allowed', $uploadForm);
    


// make a unique filename for the uploaded file and check it is not already
// taken... if it is already taken keep trying until we find a vacant one
// sample filename: 1140732936-filename.jpg
$now = time();

// make a note of the directory that will recieve the uploaded file
$uploadsDirectory = '../comics/';
while(file_exists($uploadFilename = $uploadsDirectory.$now.'-'.$_FILES[$fieldname]['name']))
{
    $now++;
}

if($usemaxwidth == "yes"){
	$w = $maxwidth;
	$source = @imagecreatefromstring(
	@file_get_contents($_FILES[$fieldname]['tmp_name']))
	or die('Not a valid image format.');
	$x = imagesx($source);
	$y = imagesy($source);
	$h = $y;
	if($x > $w) {
		$h = ($w/$x) * $y;
	}else{
		$w=$x;
	}
	$slate = @imagecreatetruecolor($w, $h)
	or die("Image too large");
	imagecopyresampled($slate, $source, 0, 0, 0, 0, $w, $h, $x, $y);
	@imagejpeg($slate, $uploadFilename, 85)
	or error('receiving directory insufficient permission', $uploadForm);
	imagedestroy($slate);
	imagedestroy($source);
	$uploadReg = $now.'-'.$_FILES[$fieldname]['name'];
}else{
	copy($_FILES[$fieldname]['tmp_name'], $uploadFilename);
	$uploadReg = $now.'-'.$_FILES[$fieldname]['name'];
}


// make a note of the directory that will recieve the uploaded file
$uploadsDirectory = '../comicsthumbs/';
while(file_exists($uploadFilename = $uploadsDirectory.$now.'-'.$_FILES[$fieldname]['name']))
{
    $now++;
}
$uploadThumb = $now.'-'.$_FILES[$fieldname]['name'];

$w = $thumbwidth;
$h = $thumbheight;

$source = @imagecreatefromstring(
@file_get_contents($_FILES[$fieldname]['tmp_name']))
or die('Not a valid image format.');
$x = imagesx($source);
$y = imagesy($source);
if($x > $w) {
	if($y > $h){
		if(($x/$y)<=($w/$h)){
			$w = round(($h / $y) * $x);
			$h = round(($h / $y) * $y);
		}else{
			$h = round(($w/$x)*$y);
			$w = round(($w/$x)*$x);
		}
	}else{
		$h = round(($w/$x)*$y);
	}
}else{
	if($y > $h){
		$w = round(($h / $y) * $x);
	}else{
		$w = $x;
		$h = $y;
	}
}
$slate = @imagecreatetruecolor($w, $h)
or die("Image too large");
imagecopyresampled($slate, $source, 0, 0, 0, 0, $w, $h, $x, $y);
@imagejpeg($slate, $uploadFilename, 85)
or error('receiving directory insufficient permission', $uploadForm);
imagedestroy($slate);
imagedestroy($source);


// make a note of the directory that will recieve the uploaded file
$uploadsDirectory = '../comicshighres/';
while(file_exists($uploadFilename = $uploadsDirectory.$now.'-'.$_FILES[$fieldname]['name']))
{
    $now++;
}
move_uploaded_file($_FILES[$fieldname]['tmp_name'], $uploadFilename);
$uploadHighres = $now.'-'.$_FILES[$fieldname]['name'];

}
	
?>