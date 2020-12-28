<?php
	header('Content-Type: application/json');
	require("init.php");

	// Je fais ma requete pour savoir si l'email est un client ou pas
	$res = $sql->GetOnce('email', 'Contact', "email = '".$_POST['valeurEmail']."'");

	//var_dump($res);

	// $res == NULL -> l'email entré n'existe pas dans la table.
	// $res == "" -> Etant donné que dans la table il y'a des contact qui ont un email vide, je dois bloquer les emails vides pour ne pas qu'une personne qui met un email vide soit considérée comme un client.
	if ($res == NULL || $res == "")
	{
		$tableau[] = array("rep" => "non");
	}
	else
	{
		$champsPreRemplis = $sql->REQ("SELECT Contact.nom, Clients.Nom, tel FROM Contact, Clients WHERE Contact.id_Clients = Clients.id AND email = '".$_POST['valeurEmail']."'");

		$tableau[] = array(
			"rep" => "oui",
			"nomContact" => $champsPreRemplis[0]["nom"],
			"nomClient" => $champsPreRemplis[0]["Nom"],
			"tel" => $champsPreRemplis[0]["tel"]
		);
	}

	//var_dump($tableau);
	echo json_encode($tableau);