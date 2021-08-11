<?php

class N_parent extends CSPJ{
    public $parent;
    public $type;
    public $sender;
    public $message;
    function __construct($arr_N_Parent){
            $this->parent = $arr_N_Parent;
            $this->sender = $this->parent["sender"];
            $this->message = $this->parent["message"];
            $this->type =(($this->parent["sender"] == "top")?"root":"subNode");
    }
}//EO class N_parent
class CSPJ{
    private $commentsJsonFileName = "cspj_comments_default.json";
    private $commentSender = "cspj_sender_default";
    private $arr_N = [null];
    private $fileLength = null;
    
    function __construct($commentsJsonFileName){
        $this->commentsJsonFileName = $commentsJsonFileName;
        
        if(!file_exists($this->commentsJsonFileName) || 
            file_get_contents($this->commentsJsonFileName, true)==""
        ){ 
            file_put_contents($this->commentsJsonFileName, "[]");   
        }
        $inp = file_get_contents($this->commentsJsonFileName, true);
        $this->arr_N = json_decode($inp, true) ;
    }
    
    public function loadAllComment( $arr_pathNum, $loadChildren, $commentsDisplayCallback){
            $arr_N = $this->arr_N;
            //default default parent
            $arr_N_Parent = array("sender"=>"post", "message"=>"comment");
            
            //convert arr_pathNum elements into mutlidim keys
            foreach( $arr_pathNum as $key =>$val){
                $bb = $arr_N[$val]["subNode"];
                $arr_N_Parent = $arr_N[$val]; //watch for parent
                $arr_N = $bb; 
            }
            
            
            
            $fileLength = $this->fileLength = count($arr_N);
            for($N_index=$fileLength-1; $N_index>-1; $N_index--){
                  $N_sender = "".$arr_N[$N_index]["sender"];
                  $N_message = "".$arr_N[$N_index]["message"];
                  $N_timestamp =
                  (array_key_exists("timestamp", $arr_N[$N_index])?("".$arr_N[$N_index]["timestamp"]):"null_date");
                  $N_extras =
                  (array_key_exists("extras", $arr_N[$N_index])?("".$arr_N[$N_index]["extras"]):"null_extras");
                  $N_uniqueTok =
                  (array_key_exists("uniqueTok", $arr_N[$N_index])?("".$arr_N[$N_index]["uniqueTok"]):"null_uniqueTok");
                  $att = "".$N_index;

                  //push pathIndex and send display callback
                  $N_pathId = $arr_pathNum;
                  $N_pathId[] = $N_index;
                  $N_parent = new N_parent($arr_N_Parent);
                  $commentsDisplayCallback($N_sender, $N_message, $N_timestamp, $N_extras, $N_uniqueTok, $N_pathId, $N_parent);
                  
                  //load internals;
                if($loadChildren) {
                $this->loadAllComment($N_pathId, $loadChildren,$commentsDisplayCallback );
                }
            }//EO for
            unset($arr_N);
    }//EO loadAllCommentents
    public function sendComment($commentSender, $message, $extras, $arr_pathNum){
            ///generateds
            $timestamp = (new DateTime())->format("D, M j 'y - h:i:s");
            $uniqueTok = getRandomToken();
            $this->commentSender = $commentSender;
            $arr_messageData = array("sender"=>$this->commentSender, "message"=>$message, "uniqueTok"=>$uniqueTok ,"timestamp"=>$timestamp ,"extras"=>$extras,"subNode"=>[]);
            
            $arr_N = $this->arr_N;
            //editing internal multidimension with $arr_pathNum elements
            $appendToPath = &$arr_N;
            foreach( $arr_pathNum as $val ){
                $appendToPath = &$appendToPath[$val]["subNode"];
            }
            array_push( $appendToPath, $arr_messageData);
            
            file_put_contents($this->commentsJsonFileName,
            json_encode($arr_N));   
            
            /*
            print "<pre>";
            print_r(  $arr_N);
            print "</pre>";
            */
    }//EO sendComm











}//EO class
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
    
    //handle ajax
if(isset($_POST["new"])){   
    
    //#making relative url 
    header("Content-Type: application/json");
    $saveToUrl = $_POST["new"];
    $saveToUrlPath = parse_url($saveToUrl)["path"];
    $thisUrlPath = $_SERVER["PHP_SELF"];
    $saveToUrlPathArr = explode("/", $saveToUrlPath);
    $thisUrlPathArr = explode("/", $thisUrlPath);
    $diff = count($saveToUrlPathArr)-count($thisUrlPathArr);
        $arr_newPath = [];
        for($i=0; count($saveToUrlPathArr)>$i; $i++){ 
           if( isset($thisUrlPathArr[$i]) && $saveToUrlPathArr[$i] === $thisUrlPathArr[$i]){            }
           else{   $arr_newPath[] = $saveToUrlPathArr[$i];    }
           if( isset($thisUrlPathArr[$i]) && $saveToUrlPathArr[$i] != $thisUrlPathArr[$i]){   
               array_unshift($arr_newPath, "..");  
           }
        }
              array_shift($arr_newPath);
    $newPath = implode("/", $arr_newPath);
    
    
if(isset($_POST["loadAllComment"])){
    
    #json type
    $argumentsInJson = json_decode($_POST["loadAllComment"]);
    if($argumentsInJson){
    $loadInd = []; 
    $loadChildren = true;
        if(is_object($argumentsInJson)){            
            $loadInd = $argumentsInJson->loadInd;
            $loadChildren = $argumentsInJson->loadChildren;
        }
        if(is_array($argumentsInJson)){            
            $loadInd = $argumentsInJson[0];
            $loadChildren = $argumentsInJson[1];
        }
        //print_r($argumentsInJson);
    }
    else{
    $argumentsInArray = $_POST["loadAllComment"];
    $loadInd = $argumentsInArray[0];
    $loadChildren = $argumentsInArray[1];
    echo gettype($argumentsInArray)."\n";
    print_r($argumentsInArray);
    }
    
    
    
    header("Content-Type: application/json");
    
    $cspj = new CSPJ($newPath);
    //$cspj ->sendComment($commentSender, "sending comment", []); 
    $resultsInArray = [];
    $cspj ->loadAllComment( [], true, function($N_sender, $N_message,$N_timestamp, $N_extras, $N_uniqueTok, $N_pathId, $N_parent) use(&$resultsInArray){
         //return result to request.
         $resultsInArray[] = array("sender"=>$N_sender, "message"=>$N_message, "timestamp"=>$N_timestamp, "uniqueTok"=>$N_uniqueTok, "extras"=>$N_extras, "pathInd"=>$N_pathId, "parent"=>$N_parent);
    });
    $resultsInJson = json_encode($resultsInArray);
    print_r($resultsInJson);
    
    
}//EO isset

if(isset($_POST["sendComment"])){
    $arguments = $_POST["sendComment"];
    #json type
    $argumentsInJson = json_decode($_POST["sendComment"]);
    if($argumentsInJson){
    $sender = "sender";
    $message = "commentBody";
    $extras = json_encode([]);
    $sendInd = [];
        if(is_object($argumentsInJson)){            
            $sender =  $argumentsInJson->sender;
            $message = $argumentsInJson->body;
            $sendInd = $argumentsInJson->sendInd;
            $extras = $argumentsInJson->extras;
            
        }
        if(is_array($argumentsInJson)){            
            $sender = $argumentsInJson[0];
            $message = $argumentsInJson[1];
            $sendInd = $argumentsInJson[2];
        }
        
    }
    else{
    $argumentsInArray = $_POST["sendComment"];
    $loadInd = $argumentsInArray[0];
    $loadChildren = $argumentsInArray[1];
    }
    
    
    
    //header("Content-Type: application/json");
    
    $cspj = new CSPJ($newPath);
    $cspj ->sendComment($sender, $message, $extras,$sendInd); 
}
   
   
   
   

}//EO isset $_POST["New"];


     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     








//functions
function getRandomToken(){
    mt_srand( (double) microtime()  * 100000000);
    $randToken = "";
    $pool = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"."abcdefghijklmnopqrstuvwxyz";
    for($i=0; 8>$i; $i++){
        $randToken.= $pool[mt_rand(0, strlen($pool)-1)];
    }
    return($randToken);
}






     
     
     
     
     
     
     
     
     
     


?>