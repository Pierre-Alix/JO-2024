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
    $nomEpreuve = filter_input(INPUT_POST, 'nomEpreuve', FILTER_SANITIZE_STRING);
    $dateEpreuve = filter_input(INPUT_POST, 'dateEpreuve', FILTER_SANITIZE_STRING);
    $heureEpreuve = filter_input(INPUT_POST, 'heureEpreuve', FILTER_SANITIZE_STRING);
    $idLieu = filter_input(INPUT_POST, 'idLieu', FILTER_VALIDATE_INT);
    $idSport = filter_input(INPUT_POST, 'idSport', FILTER_VALIDATE_INT);

    // Vérifiez si des champs obligatoires sont vides
    if (empty($nomEpreuve) || empty($dateEpreuve) || empty($heureEpreuve) || !$idLieu || !$idSport) {
        $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
        header("Location: add-event.php");
        exit();
    }


    try {
         // Vérifiez si l'épreuve existe déjà
         $queryCheck = "SELECT id_epreuve FROM EPREUVE WHERE nom_epreuve = :nomEpreuve AND date_epreuve = :dateEpreuve AND heure_epreuve = :heureEpreuve AND id_lieu = :idLieu AND id_sport = :idSport";
         $statementCheck = $connexion->prepare($queryCheck);
         $statementCheck->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
         $statementCheck->bindParam(":dateEpreuve", $dateEpreuve, PDO::PARAM_STR);
         $statementCheck->bindParam(":heureEpreuve", $heureEpreuve, PDO::PARAM_STR);
         $statementCheck->bindParam(":idLieu", $idLieu, PDO::PARAM_INT);
         $statementCheck->bindParam(":idSport", $idSport, PDO::PARAM_INT);
         $statementCheck->execute();
 
         if ($statementCheck->rowCount() > 0) {
             $_SESSION['error'] = "Cette épreuve existe déjà.";
             header("Location: add-event.php");
             exit();
         } else {

        // Requête pour ajouter une épreuve
        $query = "INSERT INTO EPREUVE (nom_epreuve, date_epreuve, heure_epreuve, id_lieu, id_sport) VALUES (:nomEpreuve, :dateEpreuve, :heureEpreuve, :idLieu, :idSport)";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
        $statement->bindParam(":dateEpreuve", $dateEpreuve, PDO::PARAM_STR);
        $statement->bindParam(":heureEpreuve", $heureEpreuve, PDO::PARAM_STR);
        $statement->bindParam(":idLieu", $idLieu, PDO::PARAM_INT);
        $statement->bindParam(":idSport", $idSport, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'épreuve a été ajoutée avec succès.";
            header("Location: manage-events.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'épreuve.";
            header("Location: add-event.php");
            exit();
        }
    }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-event.php");
        exit();
    }

}

// Récupérez la liste des lieux pour le menu déroulant
try {
    $queryLieux = "SELECT id_lieu, nom_lieu FROM LIEU";
    $statementLieux = $connexion->prepare($queryLieux);
    $statementLieux->execute();
    $lieux = $statementLieux->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: add-event.php");
    exit();
}

// Récupérez la liste des sports pour le menu déroulant
try {
    $querySports = "SELECT id_sport, nom_sport FROM SPORT";
    $statementSports = $connexion->prepare($querySports);
    $statementSports->execute();
    $sports = $statementSports->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: add-event.php");
    exit();
}
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
    <title>Ajouter une Épreuve - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
        .form-input {
            margin-bottom: 20px;
        }

        .form-input label {
            display: block;
            margin-bottom: 5px;
        }

        .form-input select,
        .form-input input[type="text"] {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .form-input input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }

        .form-input input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body>
    <header>
        <nav>
            <!-- Menu vers les pages sports, events, et results -->
            <ul class="menu">
                <li><a href="../admin.php">Accueil Administration</a></li>
                <li><a href="../admin-sports/manage-sports.php">Gestion Sports</a></li>
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
                <li><a href="manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-genders.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter une Épreuve</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-event.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cette épreuve?')">
            <label for="nomEpreuve">Nom de l'Épreuve :</label>
            <input type="text" name="nomEpreuve" id="nomEpreuve" required>

            <label for="dateEpreuve">Date de l'Épreuve :</label>
            <input type="date" name="dateEpreuve" id="dateEpreuve" required>

            <label for="heureEpreuve">Heure de l'Épreuve :</label>
            <input type="time" name="heureEpreuve" id="heureEpreuve" required>

            <div class="form-input">
            <label for="idLieu">Lieu de l'Épreuve :</label>
            <select name="idLieu" id="idLieu" required>
                <?php
                foreach ($lieux as $lieu) {
                    echo "<option value='{$lieu['id_lieu']}'>{$lieu['nom_lieu']}</option>";
                }
                ?>
            </select>
            </div>

            <div class="form-input">
            <label for="idSport">Nom du Sport :</label>
            <select name="idSport" id="idSport" required>
                <?php
                foreach ($sports as $sport) {
                    echo "<option value='{$sport['id_sport']}'>{$sport['nom_sport']}</option>";
                }
                ?>
            </select>
            </div>

            <input type="submit" value="Ajouter l'Épreuve">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-events.php">Retour à la gestion du calendrier</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>
