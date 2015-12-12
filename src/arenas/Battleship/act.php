<?php
$bots=get_Bots_Array('Battleship');

switch ($_POST['act']){
    case "initGame":
      //verifier parametres POST       
	$postParamsWanted=array(
	      // key,min,max
	  array('bot1',1,999),
	  array('bot2',1,999),
	  array('gridWidth',1,100),
	  array('gridHeight',1,100),
	  array('ship1',1,10),
	  array('ship2',1,10),
	  array('ship3',1,10),
	  array('ship4',1,10),
	  array('ship5',1,10),
	  array('ship6',1,10)
	);
  
      foreach($postParamsWanted as $p){
	if(!isset($_POST[$p[0]])){
	  error (500,'missing parameter');
	  die;
	}else{
	  $value=$_POST[$p[0]];
	}
	if (
		  (!is_numeric($value))
	      OR  ($value < $p[1])
	      OR  ($value > $p[2])
	    )
	{
	      error(500,'wrong parameters');
	      die;
	}
	$postValues[$p[0]]=$value;
		
      }
      //check if bots exists
      $bot1Exists = false;
      $bot2Exists = false;
      foreach($bots as $bot){
	if($bot['id'] == $_POST['bot1']){
	  $bot1 = $bot;
	  $bot1Exists =true;
	}
	if($bot['id'] == $_POST['bot2']){
	  $bot2 = $bot;
	  $bot2Exists =true;
	}
	if ($bot1Exists && $bot2Exists){
	  break;
	} 
      }
      if ((!$bot1Exists) OR (!$bot2Exists)){
	error (500,"missing parameter";
      }
      
      //vars checked, lets init the initGame 
	
	$_SESSION['matchId']=get_unique_id();
	
	
	
	// get_IA_Response($iaUrl,$postParams)
	//array à envoyer au bot 1
	
	$bot1ParamsToSend=array(
            'game'      => 'Battleship',
            'act'       => 'init',
            'match_id'  => $_SESSION['matchId']."-1",
            'opponent'  => $bot2['name'],
            'width'     => $postValues['gridWidth'],
            'height'    => $postValues['height'],
            'ship1'     => $postValues['ship1'],
            'ship2'     => $postValues['ship2']
            'ship3'     => $postValues['ship3']
            'ship4'     => $postValues['ship4']
            'ship5'     => $postValues['ship5']
            'ship6'     => $postValues['ship6']
            
	);
	
	$anwserPlayer1 = get_IA_Response($bot1['url'],$bot1ParamsToSend);
        echo $anwserPlayer1; die;
    
        break;
    default:
        break;
}