<?
if(authCheck()){
ini_set("memory_limit","512M");
set_time_limit(60);
		// make a note of the current working directory, relative to root.
$directory_self = str_replace(basename($_SERVER['PHP_SELF']), '', $_SERVER['PHP_SELF']);

// make a note of the directory that will recieve the uploaded file
$uploadsDirectory = '../uploads/';

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
$realname = substr($_FILES[$fieldname]['name'],0,(strrpos($_FILES[$fieldname]['name'],"."))) . ".png";
$now = time();
while(file_exists($uploadFilename = $uploadsDirectory.$now.'-'.$realname))
{
    $now++;
}
$imgname=$now.'-'.$realname;
$sourceImg = @imagecreatefromstring(@file_get_contents($_FILES[$fieldname]['tmp_name']));
if ($sourceImg === false)
{
  throw new Exception("{$source}: Invalid image.");
}
$width = imagesx($sourceImg);
$height = imagesy($sourceImg);
$targetImg = imagecreatetruecolor($width, $height);
imagecopy($targetImg, $sourceImg, 0, 0, 0, 0, $width, $height);
imagepng($targetImg, $uploadFilename);
$x = imagesx($sourceImg);
$y = imagesy($sourceImg);
$w = 120;
$h = 120;
if($x > $w) {
	if($y > $h){
		if(($x/$y)<=($w/$h)){
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
$now = time();
while(file_exists($uploadFilenameThumb = $uploadsDirectory.$now.'-thumb-'.$realname))
{
    $now++;
}
$thumbname=$now.'-thumb-'.$realname;
echo $thumbname;
$targetImg = @imagecreatetruecolor($w, $h);
imagecopyresampled($targetImg, $sourceImg, 0, 0, 0, 0, $w, $h,$x,$y);
imagedestroy($sourceImg);
imagepng($targetImg, $uploadFilenameThumb);
imagedestroy($targetImg); 
}

	
?>