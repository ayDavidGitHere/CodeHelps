
<?php


class GENHelp{
    
    
static function removeDir($dir){
 $count = 0;
 // ensure that $dir ends with a slash so that we can concatenate it with the filenames directly
 $dir = rtrim($dir, "/\\") . "/";
 // use dir() to list files
 $list = dir($dir);
 // store the next file name to $file. if $file is false, that's all -- end the loop.
 while(($file = $list->read()) !== false) {
 if($file === "." || $file === "..") continue;
 if(is_file($dir . $file)) {
 unlink($dir . $file);
 $count++;
 } elseif(is_dir($dir . $file)) {
 $count += GENHelp::removeDir($dir . $file);
 }
 }
 // finally, safe to delete directory!
 rmdir($dir);
 return $count;
}//EO removeDir
 
static function copyDir($src, $dest){
 $count = 0;
 // ensure that $src and $dest end with a slash so that we can concatenate it with the filenames directly
 $src = rtrim($src, "/\\") . "/";
 $dest = rtrim($dest, "/\\") . "/";
 // use dir() to list files
 $list = dir($src);
 // create $dest if it does not already exist
 @mkdir($dest);
 // store the next file name to $file. if $file is false, that's all -- end the loop.
 while(($file = $list->read()) !== false) {
 if($file === "." || $file === "..") continue;
 if(is_file($src . $file)) {
 copy($src . $file, $dest . $file);
 $count++;
 } elseif(is_dir($src . $file)) {
 $count += GENHelp::copyDir($src . $file, $dest . $file);
 }
 }
 return $count;
}//EO copyDir
 

static function loadSvg($svgUrl, $attributes=" ", $returnType=false){
 $svgFile = file_get_contents($svgUrl); 
 $returnString = ( str_replace('xmlns="http://www.w3.org/2000/svg"', ' '.$attributes, $svgFile) ); 
 if(!$returnType){print_r($returnString);}
 if($returnType){return $returnString;}
}//EO loadSvg
 
 
static function getFolderName($url){
 $urlSpl = explode("/", $url);
 return $urlSpl[count($urlSpl)-1];
}
static function getRelativePath($compUrl, $baseUrlPath){
 $compUrlPath = parse_url($compUrl)["path"];
 //$baseUrlPath = $_SERVER["PHP_SELF"];
 $compUrlPathArr = explode("/", $compUrlPath);
 $baseUrlPathArr = explode("/", $baseUrlPath);
 $diff = count($compUrlPathArr)-count($baseUrlPathArr);
 $arr_newPath = [];
 for($i=0; count($compUrlPathArr)>$i; $i++){ 
    if(isset($baseUrlPathArr[$i])&&
    $compUrlPathArr[$i]===$baseUrlPathArr[$i]){}
    else{   $arr_newPath[] = $compUrlPathArr[$i];    }
    if(isset($baseUrlPathArr[$i])&&
    $compUrlPathArr[$i]!=$baseUrlPathArr[$i]){
        array_unshift($arr_newPath, "..");  
    }
 }
 array_shift($arr_newPath);
 $newPath = implode("/", $arr_newPath);
 return $newPath;
}
static function createQueryString($type, $table_name, $PARAMS){
 $columns = ""; $values = ""; $values_and = "";
 foreach($PARAMS as $key => $value) { 
     $columns .= "`$key`,";
     $values .= "'$value',";
     $values_and .= "'$value' AND";
 }
 $columns = trim($columns, ","); 
 $values = trim($values, ","); 
 $values_and = trim($values_and, "AND");
 
 $query_insert = "INSERT INTO $table_name ($columns) VALUES($values);";
 $query_select = "SELECT * FROM $table_name WHERE $columns=$values_and;";
 $query = "INSERT";
 if($type=="INSERT")$query = $query_insert;
 if($type=="SELECT")$query = $query_select;
 return $query;
}
static function createPdoQueryString($type, $table_name, $PARAMS){
 $columns = ""; $columns_placeholder = ""; $values = ""; 
 foreach($PARAMS as $key => $value) { 
     $columns .= "`$key`,";
     $columns_placeholder .= ":$key,";
     $values .= "`".$value."`,";
 }
 $columns = trim($columns, ",");
 $columns_placeholder = trim($columns_placeholder, ",");
 $values = trim($values, ","); //`".$table_name."`
 $prepare_insert = "INSERT INTO 
 ".$table_name." (".$columns.") VALUES(".$columns_placeholder.");";
 $prepare = $prepare_insert;
 $query = ["prepare"=>$prepare];
 return $query;
}
static function getRandomToken($length){
 mt_srand( (double) microtime()  * 100000000);
 $randToken = "";
 $alphabets_Upper = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
 $alphabets_Lower = "abcdefghijklmnopqrstuvwxyz";
 $pool = $alphabets_Upper.$alphabets_Lower;
 for($i=0; $length>$i; $i++){
    $randToken.= $pool[mt_rand(0, strlen($pool)-1)];
 }
 return($randToken);
}
static function uploadFile($FileKey, $imageFolder, $imageName){
 $Response = ["status"=>"", "error"=>[], "imageProps"=>""];
 $imageFile = null;
 if(isset($_FILES[$FileKey])){$imageFile = $_FILES[$FileKey];}
 else{    $Response["status"] = "error";    
          $Response["error"][] = "File with this key does not exist";
          exit();
 }
 $imageProps = new StdClass();
 if ($imageFile["error"] == UPLOAD_ERR_OK){
     try{
     $illegal = array_merge(array_map('chr', range(0,31)), ["<", ">", ":", '"', "/", "\\", "|", "?","*", " "]);
     $filename = str_replace($illegal, "-", $imageFile['name']);
     $pathinfo = pathinfo($filename);
     $imageExtension=$pathinfo['extension']?$pathinfo['extension']:'';
     $filename = $pathinfo['filename'] ? $pathinfo['filename']:'';
     if(!empty($imageExtension) && !empty($filename)){
     } 
     else {    throw new Exception("filename or extension error"); }
         
     //basename($imageFile["name"]);
     if(!file_exists($imageFolder)) mkdir($imageFolder);
     $imageUrl = "$imageFolder/$imageName.$imageExtension"; 
     move_uploaded_file($imageFile["tmp_name"], $imageUrl);
     $imageProps->imageUrl = $imageUrl;
     $imageProps->imageName = $imageName;
     $imageProps->imageExtension = $imageExtension;
     $Response["imageProps"] = $imageProps;
     $Response["status"] = "ok";
     $Response["error"] = [];
     }catch(Exception $e){
     $Response["status"] = "error";
     $Response["error"][] = $e;
     }
 }
 else switch ($imageFile["error"]){
      case UPLOAD_ERR_INI_SIZE:
      $Response["error"][] = "Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.";
      break;
      case UPLOAD_ERR_FORM_SIZE:
      $Response["error"][] = "Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
      break;
      case UPLOAD_ERR_PARTIAL:
      $Response["error"][] = "Value: 3; The uploaded file was only partially uploaded.";
      break;
      case UPLOAD_ERR_NO_FILE:
      $Response["error"][] = "Value: 4; No file was uploaded.";
      break;
      case UPLOAD_ERR_NO_TMP_DIR:
      $Response["error"][] = "Value: 6; Missing a temporary folder. Introduced in PHP 5.0.3.";
      break;
      case UPLOAD_ERR_CANT_WRITE:
      $Response["error"][] = "Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0.";
      break;
      case UPLOAD_ERR_EXTENSION:
      $Response["error"][] = "Value: 8; A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensionswith phpinfo() may help. Introduced in PHP 5.2.0.";
      break;
      default:
      $Response["error"][] = "An unknown error has occurred.";
          break;
 }
 
 //--? validating mime type;
 return $Response;
}//EO uploadFile
 
 
 
 
 
 
 
}//EO GENHelp


?>