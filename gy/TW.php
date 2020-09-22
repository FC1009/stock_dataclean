<?php
set_time_limit(100);
include('../database/db_admin.php');
 
?>
<html>
    <head>
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
   </head>
   <body>
<?php                     


$link = new DB;
$sql ="select * from TW_CodeState Where CheckTime  < NOW() - INTERVAL 2 HOUR"; //HOUR    MINUTE
$link->query($sql);  
$num=$link->num_rows() ;
$link->close(); 
if ($num > 0) {
    $output = file_get_contents("http://www.alphago.url.tw/system/GetTW_STock.php");
    
   $sql="Update TW_CodeList"; 
   $sql.=" inner join TW_RawDataOrder on TW_CodeList.Code= TW_RawDataOrder.Code"; 
   $sql.=" inner join TW_RawData on TW_RawData.Code=TW_RawDataOrder.Code And TW_RawData.Date=TW_RawDataOrder.Date";
   $sql.=" Set TW_CodeList.PER =Round(TW_RawData.ClosePrice/TW_CodeList.EPS,2)";
   $sql.=" Where TW_RawDataOrder.RowID=1";
 
    $link = new DB;
    $link->query($sql); 
 
    $link->close();
    

 
 

 
  $output =file_get_contents("https://www.opview.com.tw/listed-and-otc-companies");
  $NewString = explode ( '"產業別"}">產業別</td>', $output);
  
   for ($i=1;$i < count($NewString);$i++){
      
      $NewString_Detail = explode ( '"上市"}">上市</td>',$NewString[$i]);
  
      for ($j=0;$j < count($NewString_Detail);$j++){
      
             if ($NewString_Detail[$j]!=""){
               $Code="";
               $CodeName="";
               $CodeType="";
                       $Element = "";
                       if ( strpos($NewString_Detail[$j],"上櫃")   ) {
                             $NewString_Detail_Temp = explode ( '上櫃',$NewString_Detail[$j]);
                            //echo $NewString_Detail_Temp[0]."<BR><BR>";
                            $Element=$NewString_Detail_Temp[0];
                      } else if ( strpos($NewString_Detail[$j],"</tbody>")   ) {
                            $NewString_Detail_Temp = explode ( '</tbody>',$NewString_Detail[$j]);
                            //echo $NewString_Detail_Temp[0]."<BR><BR>";
                             $Element=$NewString_Detail_Temp[0];
                       } else {
                            //echo $NewString_Detail[$j]."<BR><BR>";
                             $Element=$NewString_Detail[$j];
                       }
                       
                      $Elements= explode ( '</td>', $Element);
                      for ($k=0;$k < count($Elements);$k++){
                       
                          $Element_Temp= explode ( '}">', $Elements[$k]);
                          if ($k==0){
                                $Code =$Element_Temp[1];
                          } else if ($k==1) {
                                $CodeName =$Element_Temp[1];
                          } else if ($k==2) {
                                $CodeType =$Element_Temp[1];
                               
                          }
                           
                      } 
                       
                      if ($Code!=""){
                      
                          $link = new DB;
                          $sql ="Select * from TW_CodeList Where Code ='".$Code."'";
                          $link->query($sql);  
                          $Code_num=$link->num_rows() ;
                          $link->close(); 
                          if ($Code_num > 0 ){
                             $sql ="Update TW_CodeList set CodeType='".$CodeType."',CompanyName='".$CodeName."',CheckDateTime = NOW() Where Code='".$Code."'";
                          } else {
                             $sql ="Insert into TW_CodeList (Code,CodeType,CompanyName,CheckDateTime) values ('".$Code."','".$CodeType."','".$CodeName."',NOW())";
                          }
                          $link = new DB;
                          
                          $link->query($sql);  
                           
                          $link->close();  
      
                      }
                  
             }
  
      }             
     
   }
   
   
    $now_time_h=date("H");
    $timestmp=date("Y-m-d H:i:s");
    $sql="Update TW_CodeState set CheckTime ='".$timestmp."'";
    $link = new DB;
    $link->query($sql); 
    $link->close();
    
} else {
  
        $sql="SELECT Code FROM TW_CodeList Order By CheckDateTime limit 1";
        $link = new DB;
		    $link->query($sql); 
    
      	while($rs=$link->next_record()){
          $Code=$rs['Code'];
        }
        $link->close();

  
        
        $link = new DB;		   
        $sql ="Delete from TW_RawData Where Code='".$Code."'";
			  $link->query($sql);
        $sql ="Delete from TW_His_Data Where Code='".$Code."'";
			  $link->query($sql);
        $sql ="Delete from TW_VolTemp Where Code='".$Code."'";
			  $link->query($sql);
        
        $link->close();  
                
        //讀取資料
        $output =file_get_contents("https://finance.yahoo.com/quote/".$Code.".TW/history?p=".$Code.".TW");
        $NewString = explode ( '"HistoricalPriceStore":{"prices":[', $output);
        $NewString = explode ( '],"isPending"', $NewString[1]);
			  //$lines = explode(PHP_EOL, $NewString[0]);
        $lines = explode("},{", $NewString[0]);
				$isStart = 0; 
				$now_date = "";                                             
        
					for ($i=0;$i < count($lines);$i++){
               $datatemp = str_replace("}","",str_replace("{","", $lines[$i]));        
               $pos = strpos($datatemp, "amount");  
               $posdate = strpos($datatemp, "date");    
                if (!$pos && $posdate){  
                        
                    //echo   $datatemp."<BR>";
                    //"date":1535004901,"open":4140,"high":4220,"low":4120,"close":4205,"volume":1334600,"adjclose":4205
                    $dataelemtnt  = explode ( ',', $datatemp);
                    for ($j=0;$j < count($dataelemtnt);$j++) {
                       $element = explode ( ':', $dataelemtnt[$j]);
                       if ($element[0]=='"date"') {
                          $Data_date =  date("Y-m-d", $element[1] ) ;
                       }
                        if ($element[0]=='"open"') {
                          $Data_open = $element[1] ;
                       }
                       if ($element[0]=='"high"') {
                          $Data_high =  $element[1] ;
                       }
                       if ($element[0]=='"low"') {
                          $Data_low =  $element[1] ;
                       }
                       if ($element[0]=='"close"') {
                          $Data_close = $element[1] ;
                       }
                       if ($element[0]=='"volume"') {
                          $Data_volume = $element[1] ;
                       }
                    }
                    
                   $link = new DB;
					  			 $sql ="select * from TW_RawData Where Code='".$Code."' And Date='".$Data_date."'";
					  			 $link->query($sql);  
					  		   $num=$link->num_rows() ;
					  			  if ($num == 0 &&  $Data_volume!= NULL && $Data_close!=NULL && $Data_low!=NULL && $Data_high!=NULL && $Data_open!=NULL) {
      					      $sql ="insert into TW_RawData (Code,Date,ClosePrice,HighPrice,LowPrice,OpenPrice,Volume) value ('".$Code."','".$Data_date."',".$Data_close.",".$Data_high.",".$Data_low.",".$Data_open.",".$Data_volume.")" ; 
					    		    $link->query($sql);  
					    		    $link->close();
       
					         }
 
               }
                
					}
          
        $link = new DB;
        $sql="DELETE From TW_RawDataOrder where Code='".$Code."'";
        $link->query($sql);
        $sql="Insert into TW_RawDataOrder SELECT a.Code, a.Date, ( ";
        $sql.=" SELECT count(*) from TW_RawData b where a.Date <= b.Date AND a.Code = b.Code ";
        $sql.=" ) AS row_number FROM TW_RawData a  ";
        $sql.=" where a.Code='".$Code."'";
        $link->query($sql);
        $link->close(); 
        
          
           //HIS_DAta
				 $link = new DB;
				  $sql ="Insert into TW_His_Data SELECT A.Code,A.Date,A.ClosePrice,Avg(C.ClosePrice) as M_Bound,";
					$sql.=" Avg(C.ClosePrice)+2*STD(C.ClosePrice) as U_Bound,";
					$sql.=" Avg(C.ClosePrice)-2*STD(C.ClosePrice) as L_Bound";
					$sql.=" FROM TW_RawData A ";
					$sql.=" Inner Join TW_RawDataOrder B on A.Code=B.Code  And A.Date=B.Date";
					$sql.=" Inner Join TW_RawDataOrder D on D.Code=B.Code  And  B.RowID > D.RowID-75 And D.RowID >= B.RowID";
					$sql.=" Inner join TW_RawData C on D.Code=C.Code And C.Date=D.Date";
					$sql.=" WHERE  A.Code='".$Code."'";
         // $sql.=" And A.Date Between CURDATE()-100 And CURDATE()";
					$sql.=" And Not EXISTS(Select his.Code From TW_His_Data his where his.Code=A.Code And his.Date=A.Date)";
					$sql.=" GROUP By  A.Code,A.Date,A.ClosePrice";
				 $link->query($sql);
				 $link->close();
				 
				 //VolTemp
				 $link = new DB;
					$sql=" Insert into TW_VolTemp SELECT A.Code,A.Date,A.OpenPrice,A.ClosePrice,Avg(C.ClosePrice) as Avg75Price,";
					$sql.=" Avg(C.Volume) as Avg75Vol,";
					$sql.=" A.Volume,Round(A.Volume/Avg(C.Volume)*100,2) as VolR,Avg(F.ClosePrice) as Avg25Price";
					$sql.=" FROM TW_RawData A ";
					$sql.=" Inner Join TW_RawDataOrder B on A.Code=B.Code And A.Date=B.Date";
					$sql.=" Inner Join TW_RawDataOrder D on D.Code=B.Code  And  B.RowID > D.RowID-75 And D.RowID >= B.RowID";
               $sql.=" Inner join TW_RawData C on D.Code=C.Code And C.Date=D.Date";
               $sql.=" Inner Join TW_RawDataOrder E on E.Code=B.Code  And  B.RowID > E.RowID-25 And E.RowID >= B.RowID";
               $sql.=" Inner join TW_RawData F on F.Code=E.Code And F.Date=E.Date";
					$sql.=" WHERE  A.Code='".$Code."'";
        //  $sql.=" And A.Date Between CURDATE()-100 And CURDATE()";
					$sql.=" And Not EXISTS(Select vol.Code From TW_VolTemp vol Where A.Code=vol.Code And A.Date=vol.Date)";
					$sql.=" GROUP By  A.Code,A.Date,A.OpenPrice,A.ClosePrice,A.Volume"; 
				 
				 $link->query($sql);
				 $link->close();
         
         
             
        $output = file_get_contents("http://www.alphago.url.tw/system/GetTW_STock_Info.php?Code=".$Code);
        
        $output2 = file_get_contents("http://www.alphago.url.tw/system/GetTW_STock_Chip.php?Code=".$Code); 
        
        $sql="Update TW_CodeList set CheckDateTime=NOW() Where Code='".$Code."'";
        $link = new DB;
		    $link->query($sql); 
     
        $link->close();
        
        echo $Code;
}

  
                                      
?>
         </body>
</html>
 