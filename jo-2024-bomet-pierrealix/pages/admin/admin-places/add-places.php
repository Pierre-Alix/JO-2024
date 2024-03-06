<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomLieu = filter_input(INPUT_POST, 'nomLieu', FILTER_SANITIZE_STRING);
    $adresseLieu = filter_input(INPUT_POST, 'adresseLieu', FILTER_SANITIZE_STRING);
    $villeLieu = filter_input(INPUT_POST, 'villeLieu', FILTER_SANITIZE_STRING);
    $cpLieu = filter_input(INPUT_POST, 'cpLieu', FILTER_SANITIZE_STRING);

    // Vérifiez si le nom du lieu est vide
    if (empty($nomLieu)) {
        $_SESSION['error'] = "Le nom du lieu ne peut pas être vide.";
        header("Location: add-places.php");
        exit();
    }

    try {
        // Vérifiez si le lieu existe déjà
        $queryCheck = "SELECT id_lieu FROM LIEU WHERE nom_lieu = :nomLieu AND adresse_lieu = :adresseLieu AND cp_lieu = :cpLieu AND ville_lieu = :villeLieu";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
        $statementCheck->bindParam(":adresseLieu", $adresseLieu, PDO::PARAM_STR);
        $statementCheck->bindParam(":cpLieu", $cpLieu, PDO::PARAM_STR);
        $statementCheck->bindParam(":villeLieu", $villeLieu, PDO::PARAM_STR);
        $statementCheck->execute();

    if ($statementCheck->rowCount() > 0) {
        $_SESSION['error'] = "Le lieu existe déjà.";
        header("Location: add-places.php");
        exit();
    } else {
        
        // Requête pour ajouter un lieu
        $query = "INSERT INTO LIEU (nom_lieu, adresse_lieu, ville_lieu, cp_lieu) VALUES (:nomLieu, :adresseLieu, :villeLieu, :cpLieu)";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomLieu", $nomLieu, PDO::PARAM_STR);
        $statement->bindParam(":adresseLieu", $adresseLieu, PDO::PARAM_STR);
        $statement->bindParam(":villeLieu", $villeLieu, PDO::PARAM_STR);
        $statement->bindParam(":cpLieu", $cpLieu, PDO::PARAM_STR);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le lieu a été ajouté avec succès.";
            header("Location: manage-places.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du lieu.";
            header("Location: add-places.php");
            exit();
        }
    }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
            header("Location: add-places.php");
            exit();
        }
}
// Afficher les erreurs en PHP
// (fonctionne à condition d’avoir activé l’option en local)
error_reporting(E_ALL);
ini_set("display_errors", 1);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/normalize.css">
    <link rel="stylesheet" href="../../../css/styles-computer.css">
    <link rel="stylesheet" href="../../../css/styles-responsive.css">
    <link rel="shortcut icon" href="../../../img/favicon-jo-2024.ico" type="image/x-icon">
    <title>Ajouter un Lieu - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
    </style>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="manage-places.php">Gestion Lieux</a></li>
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-genders.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter un Lieu</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-places.php" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce lieu?')"">
            <label for=" nomLieu">Nom du Lieu :</label>
            <input type="text" name="nomLieu" id="nomLieu" required>
            <label for=" adresseLieu">Adresse du Lieu :</label>
            <input type="text" name="adresseLieu" id="adresseLieu" required>
            <label for=" villeLieu">Ville du Lieu :</label>
            <input type="text" name="villeLieu" id="villeLieu" required>
            <label for=" cpLieu">Code Postal du Lieu :</label>
            <input type="text" name="cpLieu" id="cpLieu" required>
            <input type="submit" value="Ajouter le Lieu">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-places.php">Retour à la gestion des lieux</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>