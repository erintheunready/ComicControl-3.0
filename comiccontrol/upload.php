<?
if(authCheck()){
ini_set("memory_limit","512M");
set_time_limit(60);
		// make a note of the current working directory, relative to root.
$directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);

// make a note of the directory that will recieve the uploaded file
$uploadsDirectory = '../uploads/';

// Now let's deal with the upload
    
// check that the file we are working on really was the subject of an HTTP upload
is_uploaded_file($_FILES[$fieldname]['tmp_name'])
    or die("Not an uploaded file");
    
// validation... since this is an image upload script we should run a check  
// to make sure the uploaded file is in fact an image. Here is a simple check:
// getimagesize() returns false if the file tested is not an image.
getimagesize($_FILES[$fieldname]['tmp_name'])
    or die("Not an image");
    
// make a unique filename for the uploaded file and check it is not already
// taken... if it is already taken keep trying until we find a vacant one
// sample filename: 1140732936-filename.jpg
$now = time();
while(file_exists($uploadFilename = $uploadsDirectory.$now.'-'.$_FILES[$fieldname]['name']))
{
    $now++;
}

$w = 1000;
$h = 10000;

$source = @imagecreatefromstring(
@file_get_contents($_FILES[$fieldname]['tmp_name']))
or die('Not a valid image format.');
$x = imagesx($source);
$y = imagesy($source);
if($x > $w) {
	if($y > $h){
		if(($x/$y)<=($w/h)){
			$w = round(($h / $y) * $x);
		}else{
			$h = round(($w/$x)*$y);
			$w = $x;
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

//do it again for thumb
$now2 = time();
while(file_exists($uploadFilename = $uploadsDirectory.$now2.'-thumb-'.$_FILES[$fieldname]['name']))
{
    $now2++;
}
$w = 120;
$h = 120;

// now let's move the file to its final location and allocate the new filename to it

$source = @imagecreatefromstring(
@file_get_contents($_FILES[$fieldname]['tmp_name']))
or die('Not a valid image format.');
$x = imagesx($source);
$y = imagesy($source);
if($w && ($x < $y)) $w = round(($h / $y) * $x);
else $h = round(($w / $x) * $y);
$slate = @imagecreatetruecolor($w, $h)
or error('Image too large.', $uploadForm);
imagecopyresampled($slate, $source, 0, 0, 0, 0, $w, $h, $x, $y);
@imagejpeg($slate, $uploadFilename, 85)
or error('receiving directory insufficient permission', $uploadForm);
imagedestroy($slate);
imagedestroy($source);
}

	
?>