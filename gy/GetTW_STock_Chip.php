<?php
header('Content-type: text/html; charset=big5');
set_time_limit(60);
include('../database/db_admin.php');

$Code=$_GET['Code'];
if ($Code == "" ){
  die();	
}
  
?>

<?php
   $opts = array(
    'http'=>array(
        'method'=>"GET",
        'header'=>"User-Agent: Mozilla/5.0\n"
    )
);
$context = stream_context_create($opts);
  /*
  $link = new DB;
  
  $sql = "Update TW_CodeList Set CodeType='".$Type."',Market='".$Market."',EPS='".$EPS."',ROE='".$ROE."',ROA='".$ROA."'  Where Code='".$Code."'";
 
  $link->query($sql); 
  $link->close(); 
    */

    //LP FI
 $EndDate =date("Y-m-d");
 $StartDate = date("Y-m-d" , mktime(0,0,0,date("m")-3,date("d"),date("Y")) );
  
 $output =file_get_contents("http://jsjustweb.jihsun.com.tw/z/zc/zcl/zcl.djhtm?a=".$Code."&c=".$StartDate."&d=".$EndDate ,false,$context);
 $NewString = explode ( '<tr id="oScrollMenu" align="center">', $output); 
 $NewString = explode ( '<tr id="oScrollFoot">', $NewString[2]);
 $NewString = explode ( '<tr>', $NewString[0]);
 
 
    for ($i=1;$i < count($NewString);$i++){
   
    
        $Temp =  explode ( 'nowrap>', str_replace("</td>","", $NewString[$i]) );   
          
        $Date = $Temp[1];
        $Date =  explode ( '<td', $Date ); 
        $Date=$Date[0];
        $Date=explode ( '/', $Date );   
        $Year=$Date[0]+1911 ;                      
        $Month=$Date[1];
        $Day=$Date[2];
        $Date=$Year.'-'.$Month.'-'.$Day;
         
        $Temp_sub =explode ( ' ', $Temp[10]); 
        $Temp_sub =explode ( '%', $Temp_sub[0]); 
        $FI = $Temp_sub[0];
        
        $Temp_sub =explode ( ' ', $Temp[11]); 
        $Temp_sub =explode ( '%', $Temp_sub[0]); 
        $LP = $Temp_sub[0];

        $sql="Update TW_RawData Set LP='".$LP."',FI='".$FI."' Where Code='".$Code."' And Date='".$Date."'";
        $link = new DB;
		    $link->query($sql); 
        $link->close();
         
   }
 
     
     
  
               
?>
 
 