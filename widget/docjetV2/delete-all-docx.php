<?php
/*
$folder_path = './docs';

//Splitting the file to get the file name without extension
$file_name_ = explode('.',$file_name_);
$file_name_ = $file_name_[0];

$dir = new RecursiveDirectoryIterator($folder_path, RecursiveDirectoryIterator::SKIP_DOTS);
$it = new RecursiveIteratorIterator($dir);

//looping for all files in given directory
foreach($it as $file) {
	$info = pathinfo($file);
	if($info['extension'] != "pdf") {
		unlink($info["dirname"]."/".$info["basename"]);
	}
}
*/
?>