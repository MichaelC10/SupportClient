<?php
#C_Biblio
#C_Clients
#C_Clients2Bibilo
#C_Fichiers
#C_Maquette
#C_Picto
class sql{

	static $connections = array();

	public $db 			= 'default';
	public $debug 		= 0;

	public $Nom2laBase;

	private $isConected;

	//private $host = "localhost";
	private $host ;
	private $user ;
	private $pass ;
	private $base ;

	private $link;
	//public $arbo;
	//public $champs	= 'id';

	function __construct($host, $base, $user, $pass){
		$this->host=$host;
    $this->base=$base;
    if(defined("PASSCRYPT")){
      $this->pass=decrypter($pass);
      $this->user=decrypter($user);
    }else{
      $this->pass=$pass;
      $this->user=$user;
    }


		$this->isConected = $this->connection();
		//print "J'ai chargé la connection";
	}


	/**
	  		CONNECTIONS
	 **/
	private function connection(){
		$this->link=mysqli_connect(
				$this->host,
				$this->user,
				$this->pass
		);
		mysqli_select_db($this->link, $this->base);
		mysqli_query($this->link, "SET NAMES 'utf8'");

		$this->Nom2laBase=$this->base;

		return true;
	}



	/**
			EXECUTE REQUETE IMBRIQUEE/JOINTURE
	*@param 	=> requete
	*@return 	=> array();
	**/
	public function REQ($SQL){
		$req = mysqli_query($this->link, $SQL);
			if($req!==false){
				//if(mysqli_num_rows($req)>0){
					while($row = mysqli_fetch_assoc($req)){
						$result[] = $row;
					}
				/*} else {
						/$result = null;
				}*/
			} else {
				if($this->debug > 0 || TRACE){
					$result = 'synthaxe invalide '.$SQL;
					echo $result;
				} else {
					$result = null;
				}
			}
		if(@$result){
			mysqli_free_result($req);
		}else{
			if(TRACE){
			print '<pre>'; print_r($SQL); print '</pre>';
			}
		}
		return @$result;
	}

	/**
	*
			CONSTRUCTION D UNE REQUETE
	*
	*@param 1 = $table 	=> nom table SQL,
	*@param 2 = $champs => optionnel SI vide = *, OU array() ou str,
	*@param 3 = $where 	=> optionnel SI vide ='', OU array() ou str(WHERE id=1),
	*$Where = array(
	*				'WHERE'		=>conditions 1,
	*				'AND'  		=>conditions 2,
	*				'ORDER BY' 	=> condition 3
	*		);
	*@return null si vide OU array[champs][value];
	*******************************************************************/


	 /**
	 		REQUETE DELETE
	 *@param
	 *@return
	 */
	 public function DELETE($table,$where=null){
	 		$SQL =  "DELETE FROM ";
	 		$SQL .= $table;
	 		if(!is_null($where)){
	 			$SQL.= ' WHERE '.$where;
	 		}
	 		#print $SQL;
	 		mysqli_query($this->link, $SQL) or die(mysqli_error($this->link) ."<br/>". $SQL);
	 		if(mysqli_affected_rows($this->link)!=0){
	 			return true;
	 		}
	 		return false;
	}

	 /**
	 		SCHEMA
	 **/


	/**
		Get Champs pour formulaire
	*	Utilise GetComumns & GetInfoSchemaChamps
	*	@param  : la table, la langue, les champs a ranger dans le tableau manuel
	*	@return : retourne un tableau contenant 2 tableaux :
	*				1er  tableau = input à traiter manuellement,
	*				2ème tableau = input à traiter en boucle
	*
	*	Format des tableaux :
	*	[0] => Array (
    *       [nom] => sys
    *       [name] => id
    *       [type] => int
    *       [limit] => 10
    *   )
    *
    * 	   [nom] est le commentaire de la table
    *	si [nom] == sys alors -> input manuel
    *	si [nom] != sys alors -> input boucle
    *
    *	la langue (param2) sert à faire des opérations sur le commentaire
    *	et obtenir le nom du champs dans la langue demandée
	*/
	function getMyInputs($table,$lang,$echappe){
		$champs	= $this->GetColumns($table);
		foreach($champs as $k=>$v) :
			$unChamps = $this->GetInfoSchemaChamps('ctr__PRODUIT__txt',$v);
			#if($unChamps[0]['nom']=='sys' || $unChamps[0]['nom']=='lang'){
			$unChamps[0]['trad'] = $this->getMyInputsTrad($unChamps[0]['nom'],$lang);
			if(in_array($unChamps[0]['name'], $echappe)){
			 	$inputManuel[]  	= $unChamps[0];
			} else {
				$inputAuto[] 		= $unChamps[0];
			}
		endforeach;
		$inputs[0] 	= $inputManuel;
		$inputs[1] 	= $inputAuto;
		#print '<pre>'; print_r($inputs); print '</pre>';
		return $inputs;
	}
	function getMyInputsTrad($comment,$lang){
		#$unChamps[0]['nom'] = $comment
		$return = '';
		if(strpos($comment,',')!=false){
			$chpsTrad = array();
			$cleanChamps = explode(',', $comment);
			#print '<pre>'; print_r($cleanChamps); print '</pre>';
			foreach($cleanChamps as $K=>$V){
				$cherche  = explode(':', $V);
				if(in_array($lang, $cherche)){
					$chpsTrad[$cherche[0]]=$cherche[1];
					$return = $chpsTrad[$cherche[0]];
					#print '<pre>'; print_r($chpsTrad); print '</pre>';
					#$inputAuto[] = $unChamps[0];
				}
			}
		} else {
			$return	= $comment;
		}
		return $return;
	}


	function GetColumns($Table){
        $sql="SELECT COLUMN_NAME FROM information_schema.columns WHERE TABLE_NAME = '".$Table."' AND TABLE_SCHEMA= '".$this->base. "' ";
        //print $sql;
        $rq=mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));
        $result=array();
        while($verifExe=mysqli_fetch_array($rq)){

            $result[]=$verifExe['COLUMN_NAME'];
        }
        return $result;
    }

    function showTables(){
    	$req = mysqli_query($this->link, "SHOW TABLES FROM ".$this->base);
    	while ($row = mysqli_fetch_row($req)) {
   			$tables[] = $row[0];
		}
		return $tables;
    }

  	function GetInfoSchema($champ, $Table){
        $sql="SELECT DATA_TYPE, COLUMN_DEFAULT, EXTRA FROM information_schema.columns WHERE TABLE_NAME = '".$Table."' AND COLUMN_NAME='".$champ."' AND TABLE_SCHEMA= '".$this->base. "' ";
        $rq=mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));

        $result=array();
        $verifExe=mysqli_fetch_array($rq);

        $result[0]=$verifExe['DATA_TYPE'];
        $result[1]=$verifExe['COLUMN_DEFAULT'];
        $result[2]=$verifExe['EXTRA'];
        //print '<pre>'; print_r($result); print '</pre>';
        return $result;
    }

    function GetInfoSchemaTable($Table){

        $sql = "SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION
        		FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_NAME = '".$Table."'
				AND TABLE_SCHEMA = '".$this->base."' ";
        $req = mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));

        $result 	 = array();
        $resultClean = array();

        while($row=mysqli_fetch_array($req)){
        	$result[]=$row;
        }
        foreach($result as $k => $v){
        	$info = array();
        	$info['nom']   = $v[0];
        	$info['type']  = $v[1];
        	$info['limit'] = !empty($v[2]) ? $v[2] : $v[3];
        	$resultClean[$k] = $info;
        }
        return $result;
    }
    function is_table($table){
    	$sql ="SELECT 1
		FROM INFORMATION_SCHEMA.TABLES
		WHERE TABLE_TYPE='BASE TABLE'
		AND TABLE_NAME='$table'";
		$rq=mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));
        mysqli_data_seek($rq,0);
        $row= mysqli_fetch_row($rq);
        mysqli_free_result($rq);
        return $row[0];

    }
    function GetInfoSchemaChamps($Table, $champs){

    	if(!is_array($champs)){
    		if(false !== strpos($champs, ',')){
    				$chps = explode(',',$champs);
    		} else {
    				$chps[0] = $champs;
    		}
    	} else {
    			$chps = $champs;
    	}

    	$result = array();

    	foreach($chps as $k=>$v){
    		$sql = "SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, NUMERIC_PRECISION, COLUMN_COMMENT
        		FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_NAME = '".$Table."'
				AND COLUMN_NAME = '".$v."'
				AND TABLE_SCHEMA = '".$this->base."' ";
        	$req = mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));
        	$res = array();
        	while($row = mysqli_fetch_array($req)){
        		$res=$row;
        	}

	        	$info = array();

        		$info['nom']   = !empty($res[4]) ? $res[4] : 'COLUMN_COMMENT vide';
        		$info['name']  = $res[0];
        		$info['type']  = $res[1];
        		$info['limit'] = !empty($res[2]) ? $res[2] : $res[3];
        		$result[] = $info;

    	}
        return $result;
    }


    function GetTypeChamps($champ, $Table){
        $sql="SELECT DATA_TYPEFROM information_schema.columns WHERE TABLE_NAME = '".$Table."' AND COLUMN_NAME='".$champ."' AND TABLE_SCHEMA= '".$this->base. "' ";
        $rq=mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));

        $result=array();
        $verifExe=mysqli_fetch_array($rq);

        $result[0]=$verifExe['DATA_TYPE'];
        //print '<pre>'; print_r($result); print '</pre>';
        return $result;
    }


    function GetOnce($champ, $Table, $condition=""){

        ($condition!=="") ? $WHERE=' WHERE '.$condition:$WHERE="";

        $sql="SELECT $champ FROM  ".$Table." ".$WHERE;



        $rq=mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));
        mysqli_data_seek($rq,0);
        $row= mysqli_fetch_row($rq);
        mysqli_free_result($rq);
        return $row[0];
    }


    function Getsql($sql, $Champs){
        $rq=mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));
        $result=array();

        $i=0;
        while($All=mysqli_fetch_array($rq)){
            $ligne=array();
            foreach ($Champs as $champ){
                  $ligne[$champ]=$All[$champ];
            }
            $result[$i]=$ligne;
        $i++;}
        return $result;

    }

  function GetAll($Table, $condition="", $order=''){

        ($condition!=="") ? $WHERE=' WHERE '.$condition:$WHERE="";

        ($order!=="") ? $ORDER=' ORDER BY '.$order:$ORDER="";

        $sql="SELECT * FROM ".$Table." ".$WHERE." ".$ORDER;
        $rq=mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));
        $result=array();

        $ChampS=$this->GetColumns($Table);
        while($All=mysqli_fetch_array($rq)){

            $resultChamps=array();
            foreach($ChampS as $c){
                $resultChamps[$c]=$All[$c];
            }

            $result[]=$resultChamps;
        }
        return $result;
    }


    function InsertSimple($infos, $Table, $die=true){

        $insert="";
        $Parant1='';
        foreach($infos as $i=>$v){
            $ChekField=$this->GetInfoSchema($i, $Table);
            $Type=$ChekField[0];
            $default=$ChekField[1];
            $Extra=$ChekField[2];


            if($Type=='varchar' || $Type=='text' || $Type=="datetime" || $Type=="date" || $Type=="time" || $Type="longtext"){
                $insert.= "'".$v."', " ;
            }else{
                if($Extra=='auto_increment'){
                    $insert.='NULL, ';
                }else{
                    !empty($v)? $insert.= $v.", " : $insert.="'.$default.', " ;
                }
            }
            $Parant1.="`".$i."`, ";
        }


        $sql="INSERT INTO ".$Table." (".substr($Parant1, 0, -2).") VALUES (".substr($insert, 0, -2).")";
        if($die){
        	return $rq=mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));
    	}else{
    		return $rq=mysqli_query($this->link, $sql);
    	}
    }

    function UpdateSimple($infos, $Table, $cond=""){

        $update="";
        foreach($infos as $i=>$v){
            $ChekField=$this->GetInfoSchema($i, $Table);
            $Type=$ChekField[0];
            $default=$ChekField[1];
            $Extra=$ChekField[2];


            if($Type=='varchar' || $Type=='text' || $Type=="datetime" || $Type=="date" || $Type=="time" || $Type="longtext"){
                $update.= "`".$i."`='".$v."', " ;
            }else{
                if($Extra=='auto_increment'){
                    $update.='NULL, ';
                }else{
                    !empty($v)? $update.= "`".$i."`=".$v.", " : $update.="`".$i."`='.$default.', " ;
                }
            }

        }

        (!empty($cond))?$where=" WHERE ".$cond:$where='';
        $sql="UPDATE ".$Table." SET ".substr($update, 0, -2)." ".$where;
        return $rq=mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));
    }

    function MaxID($Table){
    	$sql="SELECT MAX(id) FROM `".$Table."`";
    	$rq=mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));
    	mysqli_data_seek($rq,0);
        $row= mysqli_fetch_row($rq);
        mysqli_free_result($rq);
        return $row[0];
    }


    function Max($champs, $Table){
    	$sql="SELECT MAX($champs) FROM `".$Table."`";
    	$rq=mysqli_query($this->link, $sql) or die($sql.' - '.mysqli_error($this->link));
    	mysqli_data_seek($rq,0);
        $row= mysqli_fetch_row($rq);
        mysqli_free_result($rq);
        return $row[0];
    }

    function close(){
    	mysqli_close($this->link);
    }
}
?>
