<?php
	require("contact.html");
	require("init.php");

	if (isset($_POST["submit"]))
	{	
		// on récupère l'id_Contact de l'email saisi
		$id_Contact = $sql->GetOnce('id', 'Contact', "email = '".$_POST['email']."'");

		// si le contact n'existe pas, on die (le script s'arrete la)
		if ($id_Contact == null)
		{
			die("Ce formulaire est dédié à nos clients. Merci de votre compréhension.");
		}

		// On insert la demande dans la bdd (table demande)
		$ticket = uniqid();

		if($_POST['urgent'] == "non")
		{
			$urgent = 0;
		}
		else
		{
			$urgent = 1;
		}
		
		date_default_timezone_set('Europe/Paris');

		$dataDemande = array(
			"ticket" => $ticket, 
			"urgent" => $urgent, 
			"sujet" => str_replace("'", "’", $_POST['sujet']),
			"message" => str_replace("'", "’", $_POST['message']),
			"tel" => $_POST['tel'],
			"id_Contact" => $id_Contact,
			"dateDemande" => date("Y-m-d H:i:s")
		);

		$sql->InsertSimple($dataDemande, "demande");

		$id_demande = $sql->MaxID("demande");
		
		$tab = array(
			"id_demande" => $id_demande
		);
		
		if(isset($_POST['id_fichier']))
		{
			foreach ($_POST['id_fichier'] as $fich)
			{
				$sql->UpdateSimple($tab, "fichier", "id = ".$fich);
				//$sql->REQ("UPDATE fichier SET id_demande = ".$id_demande." WHERE id = ".$fich);
			}
		}
		
		// Fichier envoie du mail
		require("envoie_mail.php");
		
	}

?>