<?php
function  get_arenas_list(){
    include (__DIR__."/arenas_lists.php");
    return $arenas;
}
function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'){

    $chars_length = (strlen($chars) - 1);
    $string = $chars{rand(0, $chars_length)};

    for ($i = 1; $i < $length; $i = strlen($string)){
        $r = $chars{rand(0, $chars_length)};
        if ($r != $string{$i - 1}) $string .=  $r;
    }
    return $string;
}
function xd_check_input($id=1){
        /*
        *On génére un hash aléatoire qui sera 
        *ajouté aux formulaires, afin d'ajouter 
        *une vérification supplémentaire
        *lors du traitement de ce dernier
        */
  /*
  *     le parametre $id permet de selectionner le type de retour
  *     0=> un input type hidden sans id
  *     1=> un input type hidden avec id
  *     2=> juste la valeur
  */
        if(!isset($_SESSION['xd_check'])){
                //le générer
                $_SESSION['xd_check']=rand_str(25);
        }
        switch($id){
            case 0:
              return "<input type=\"hidden\" name=\"xd_check\" value=\"".$_SESSION['xd_check']."\"/>";
              break;
            case 1:
              return "<input type=\"hidden\" name=\"xd_check\" id=\"xd_check\" value=\"".$_SESSION['xd_check']."\"/>";
              break;
            case 2:
              return $_SESSION['xd_check'];
              break;
            default:
              return "<input type=\"hidden\" name=\"xd_check\" id=\"xd_check\" value=\"".$_SESSION['xd_check']."\"/>";
              break;
        }
}

function get_language_array(){
  /*
  * Choisir la langue de l'utilisateur
  * en priorisant parametre GET, cookie, info du navigateur
  * Retourner l'array contenant les bonnes traductions
  */

  $langsAvailable=array('fr','en');
  $language="";
  if( isset($_GET['lang']) ){ 
  
    $language = $_GET['lang'];
    setcookie( 'lang', $language, time() + 60*60*24*30 );
  
  }elseif( isset($_COOKIE['lang']) ){
    $language=$_COOKIE['lang'];
  }else{ 
  
    if(in_array(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2),$langsAvailable)){
      $language=substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    }
    
  }
  
  if(!in_array($language,$langsAvailable)){
    $language="en";
  }
  
  include (__DIR__."/../lang/".$language.".php");
  return $lang;
}
function error($code,$message){
    switch($code){
        case 404:
            header("HTTP/1.0 404 Not Found");
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8" /><title>Page Not found</title></head><body><p>'.$message.'</p></body></html>';
            die;
         case 400:
	    header ("HTTP/1.0 400 Bad Request");
	    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8" /><title>Bad request</title></head><body><p>'.$message.'</p></body></html>';
	    die;
	  case 500:
	    header ("HTTP/1.0 500 Internal Server Error");
	    echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8" /><title>Bad request</title></head><body><p>'.$message.'</p></body></html>';
	    die;
        default:

            die;
            break;
    }
    
}
function conn_bdd(){
    require (__DIR__."/config.php");  
    $mysqlParams=array(
        'host' => 'localhost',
        'user' => '',
        'pass' => '',
        'database'=>''
    );

    if (!$linkMysql=mysqli_connect($mysqlParams['host'], $mysqlParams['user'], $mysqlParams['pass'])) {                                                                                                         
        error(500,'database connexion failed');                                                                                                                                                  
        die;                                                                                                                                                                                           
    }                                                                                                                                                                                                      
    mysqli_select_db($linkMysql,$mysqlParams['mysql_database']);
    mysqli_set_charset($linkMysql, 'utf8');  
    return $linkMysql; //does PHP can do that?

}
function save_battle($game,$bot1,$bot2,$resultat){
    //resultat: 0 match nul, 1 bot1 gagne 2 bot 2 gagne


    $lnMysql=conn_bdd();
    //chercher les id de bot 1 et bot2
    $rs=mysqli_query($lnMysql,"SELECT name,id FROM bots 
                                WHERE name='".mysqli_real_escape_string($lnMysql,$bot1)."'
                                OR name='".mysqli_real_escape_string($lnMysql,$bot2)."'");
    
    while($r=mysqli_fetch_row($rs)){
        $bots[$r[0]]=$r[1];
    }
    
    if((!isset($bots[$bot1])) OR (!isset($bots[$bot2]))){
        error (500,"database corrupt");
        die;
    }
   
    switch($resultat){
        case 0:
            $field="nulCount";
            break;
        case 1:
            $field="player1_winsCount";
            break;
        case 2:
            $field="player2_winsCount";
            break;
        default:
             error (500,"something impossible has happened");
             break;
    }
    
    mysqli_query($lnMysql,
        "INSERT INTO arena_history(game,player1_id,player2_id,".$field.") VALUES
        ('".mysqli_real_escape_string($lnMysql,$game)."',
        '".$bots[$bot1]."',
        '".$bots[$bot2]."',
        '1')
        ON DUPLICATE KEY UPDATE ".$field." = ".$field." + 1;");
        
    mysqli_close($lnMysql);
}