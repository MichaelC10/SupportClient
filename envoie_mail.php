<?php
	require("classes/PHPMailer-master/src/PHPMailer.php");
	require("classes/PHPMailer-master/src/SMTP.php");
	require("classes/PHPMailer-master/src/Exception.php");
	
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	// Dans ce fichier j'ai accès au $_POST (donc à tous les champs du formulaire, y compris l'id du fichier qui est dans le input hidden)
	// Et j'ai aussi accès à toute les variables du fichier parent

	//var_dump($id_demande);
	//var_dump($_POST);
	//var_dump($_POST["id_fichier"]);

	if(!isset($_POST["id_fichier"]))
	{
		$nbFichier = 0;
		$stringChemins = "";
	}
	else
	{
		// On récupère le nombre de fichiers pour l'afficher dans le corps du mail
		$nbFichier = sizeof($_POST["id_fichier"]);

		// On récupère les chemins des fichiers
		$lesFichiers = $sql->REQ("SELECT chemin FROM fichier WHERE id_demande = ".$id_demande);
	
		// Je mets les chemins des fichiers dans une string pour l'afficher dans le corps du mail
		$stringChemins = "<br>";

		foreach ($lesFichiers as $ligne => $fichier)
		{
			$stringChemins = $stringChemins.$fichier["chemin"]."<br>";
		}

	}	
	

	// Instantiation and passing `true` enables exceptions
	$mail = new PHPMailer(true);

	try{
		// Server settings
		$mail->SMTPDebug = SMTP::DEBUG_SERVER;
		$mail->isSMTP();
		$mail->Host = 'mail.infomaniak.com';
		$mail->SMTPAuth = true;
		$mail->Username = 'example@gmail.com';
		$mail->Password = 'KoPmsQG6Bnhq5';
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Port = 587;
		$mail->CharSet = "UTF-8";

		// Destinataires
		$mail->setFrom('someone@gmail.com');
		$mail->addAddress('destinataire@gmail.com');

		// Content
		$mail->isHTML(true);
		$mail->Subject = 'Demande client';
		$mail->Body = 'Id_demande : '.$id_demande.'<br>'.'Ticket : '.$ticket.'<br>'.'Nom : '.$_POST['nom'].'<br>'.'Entreprise : '.$_POST['entreprise'].'<br>'.'Email : '.$_POST['email'].'<br>'.'Téléphone : '.$_POST['tel'].'<br>'.'Id_Contact : '.$id_Contact.'<br>'.'Sujet : '.$_POST['sujet'].'<br>'.'Message : '.$_POST['message'].'<br>'.'Urgent : '.$_POST['urgent'].'<br>'.'Nombre de fichier : '.$nbFichier.'<br>'.'Chemin(s) : '.$stringChemins.'<br>';

		$mail->send();

		echo 'Un message a été envoyé';

	}catch(Exception $e){
		echo "Le message n'a pas pu être envoyé. Erreur de messagerie: {$mail->ErrorInfo}";
	}

	

