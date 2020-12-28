<?php
header('Content-Type: application/json');
require("init.php");

foreach ($_FILES['file']['name'] as $position => $osef)
{
	// Attention à la boucle : pour ne pas qu'un fichier soit affecté par l'erreur d'un précédent fichier.
	$erreur = null;

	// test
	if ($_FILES['file']['error'][$position] == UPLOAD_ERR_NO_FILE)
	{
		$erreur = "Aucun fichier n'a été téléchargé.";
	}

	// test
	if ($_FILES['file']['error'][$position] == UPLOAD_ERR_INI_SIZE)
	{
		$erreur = "Le fichier ".$_FILES['file']['name'][$position]." dépasse la taille maximale autorisée";
	}

	// test
	if ($_FILES['file']['error'][$position] == UPLOAD_ERR_PARTIAL)
	{
		$erreur = "Le fichier ".$_FILES['file']['name'][$position]." a été transféré partiellement";
	}

	$extensionsPermises = array(".png", ".pdf", ".jpeg", ".jpg", ".gif", ".txt", ".xls", ".docx", ".doc", ".csv", ".pptx", ".pages");
	$extensionFichier = strtolower(strrchr($_FILES['file']['name'][$position], '.'));
	//var_dump($extensionFichier);
	// checking de l'extension du fichier
	if (!in_array($extensionFichier, $extensionsPermises))
	{
		$erreur = $_FILES['file']['name'][$position]." : l'extension ".$extensionFichier." n'est pas autorisée.";	
	}
				
	$tailleMax = 32000000; // en octet
	
	$tailleFichier = filesize($_FILES['file']['tmp_name'][$position]);
	//var_dump($tailleFichier);

	// checking de la taille du fichier
	if ($tailleFichier > $tailleMax)
	{
		$erreur = "Le fichier ne peut pas dépasser 32 Mo !";
	}

	// avant de déplacer le fichier, on le renomme en lui donnant un nom unique
	$nomSansExtension = basename($_FILES['file']['name'][$position], $extensionFichier);
	date_default_timezone_set('Europe/Paris');
	$nomFichier = date("d-m-Y_H:i:sP").uniqid($nomSansExtension, true).$extensionFichier;

	if (!isset($erreur))
	{
		//déplacement du fichier depuis le dossier temporaire vers un dossier du site
		if (move_uploaded_file($_FILES['file']['tmp_name'][$position], "uploads/".$nomFichier))
		{
			//echo "L'upload du fichier ".$_FILES['file']['name'][$position]." a été réalisé avec succès.\n";
				
			$dataFichier = array(
				"chemin" => "uploads/".$nomFichier,
				"id_demande" => 0
			);

			$sql->InsertSimple($dataFichier, "fichier");

			// Je récupère l'id du fichier qui vient d'etre insert en bdd, via max(id)
			$maxID = $sql->MaxID("fichier");
				
			switch ($extensionFichier)
			{
				case ".png":
					$classIcon = "fa fa-file-image-o fa-3x";
					break;
					
				case ".pdf":
					$classIcon = "fa fa-file-pdf-o fa-3x";
					break;

				case ".jpeg":
					$classIcon = "fa fa-file-image-o fa-3x";
					break;

				case ".jpg":
					$classIcon = "fa fa-file-image-o fa-3x";
					break;

				case ".gif":
					$classIcon = "fa fa-file-image-o fa-3x";
					break;

				case ".txt":
					$classIcon = "fa fa-file-text-o fa-3x";
					break;

				case ".xls":
					$classIcon = "fa fa-file-excel-o fa-3x";
					break;

				case ".docx":
					$classIcon = "fa fa-file-word-o fa-3x";
					break;

				case ".doc":
					$classIcon = "fa fa-file-text-o fa-3x";
					break;

				case ".csv":
					$classIcon = "fa fa-file-o fa-3x";
					break;

				case ".pptx":
					$classIcon = "fa fa-file-powerpoint-o fa-3x";
					break;

				case ".pages":
					$classIcon = "fa fa-file-text-o fa-3x";
					break;
			}

			$uploaded[] = array(
				'name' => $_FILES['file']['name'][$position],
				'id_fichier' => $maxID,
				'icon' => $classIcon
			);

			// J'echo si c'est le dernier fichier, sinon ca fera une erreur JSON
			if ($position == sizeof($_FILES['file']['name']) - 1)
			{
				echo json_encode($uploaded);
			}
				
		}
		else
		{
			$erreur = "Erreur de l'upload du fichier ".$_FILES['file']['name'][$position];
				
			$uploaded[] = array(
				"erreur" => $erreur
			);

			// J'echo si c'est le dernier fichier, sinon ca fera une erreur JSON
			if ($position == sizeof($_FILES['file']['name']) - 1)
			{
				echo json_encode($uploaded);
			}
				
		}

	}
	else
	{
		$uploaded[] = array(
			"erreur" => $erreur
		);

		// J'echo si c'est le dernier fichier, sinon ca fera une erreur JSON
		if ($position == sizeof($_FILES['file']['name']) - 1)
		{
			echo json_encode($uploaded);
		}
	}
	
}
	






