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
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_VALIDATE_INT);
    $nomEpreuve = filter_input(INPUT_POST, 'nomEpreuve',  FILTER_VALIDATE_INT);
    $resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);

    // Vérifiez si des champs obligatoires sont vides
    if (empty($nomAthlete) || empty($nomEpreuve) || empty($resultat)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
        header("Location: add-result.php");
        exit();
    }

    try {
        // Requête pour ajouter un résultat
        $query = "INSERT INTO PARTICIPER (id_athlete, id_epreuve, resultat) VALUES (:nomAthlete, :nomEpreuve, :resultat)";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_INT);
        $statement->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_INT);
        $statement->bindParam(":resultat", $resultat, PDO::PARAM_STR);


        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le résultat a été ajoutée avec succès.";
            header("Location: manage-results.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout du résultat.";
            header("Location: add-result.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-result.php");
        exit();
    }
}

// Récupérez la liste des athlètes pour le menu déroulant
try {
    $queryAthletes = "SELECT id_athlete, nom_athlete, prenom_athlete FROM ATHLETE";
    $statementAthletes = $connexion->prepare($queryAthletes);
    $statementAthletes->execute();
    $athletes = $statementAthletes->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: add-result.php");
    exit();
}

// Récupérez la liste des résultats pour le menu déroulant
try {
    $queryEpreuves = "SELECT id_epreuve, nom_epreuve FROM EPREUVE";
    $statementEpreuves = $connexion->prepare($queryEpreuves);
    $statementEpreuves->execute();
    $epreuves = $statementEpreuves->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: add-result.php");
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
    <title>Ajouter un Athlète - Jeux Olympiques 2024</title>
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
                <li><a href="../admin-events/manage-events.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-genders.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter un Résultat</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-result.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter ce résultat?')">
        <div class="form-input">
            <label for="nomAthlete">Athlète :</label>
            <select name="nomAthlete" id="nomAthlete" required>
                <?php
                foreach ($athletes as $athlete) {
                    echo "<option value='{$athlete['id_athlete']}'>{$athlete['nom_athlete']} {$athlete['prenom_athlete']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="form-input">
            <label for="nomEpreuve">Epreuves :</label>
            <select name="nomEpreuve" id="nomEpreuve" required>
                <?php
                foreach ($epreuves as $epreuve) {
                    echo "<option value='{$epreuve['id_epreuve']}'>{$epreuve['nom_epreuve']}</option>";
                }
                ?>
            </select>
        </div>

            <label for="resultat">Résultats :</label>
            <input type="text" name="resultat" id="resultat" required>

            <input type="submit" value="Ajouter le Résultat">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-results.php">Retour à la gestion des résultats</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>
