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
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_STRING);
    $prenomAthlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_STRING);
    $idPays = filter_input(INPUT_POST, 'idPays', FILTER_VALIDATE_INT);
    $idGenre = filter_input(INPUT_POST, 'idGenre', FILTER_VALIDATE_INT);

    // Vérifiez si des champs obligatoires sont vides
    if (empty($nomAthlete) || empty($prenomAthlete) || empty($idPays) || empty($idGenre)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
        header("Location: add-athlete.php");
        exit();
    }

    try {
            // Vérifiez si l'athlete existe déjà
            $queryCheck = "SELECT id_athlete FROM ATHLETE WHERE nom_athlete = :nomAthlete AND prenom_athlete = :prenomAthlete AND id_pays = :idPays AND id_genre = :idGenre";
            $statementCheck = $connexion->prepare($queryCheck);
            $statementCheck->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
            $statementCheck->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
            $statementCheck->bindParam(":idPays", $idPays, PDO::PARAM_INT);
            $statementCheck->bindParam(":idGenre", $idGenre, PDO::PARAM_INT);
            $statementCheck->execute();
    
            if ($statementCheck->rowCount() > 0) {
                $_SESSION['error'] = "Cet athlète existe déjà.";
                header("Location: add-athlete.php");
                exit();
            } else {

        // Requête pour ajouter un(e) athlète
        $query = "INSERT INTO ATHLETE (nom_athlete, prenom_athlete, id_pays, id_genre) VALUES (:nomAthlete, :prenomAthlete, :idPays, :idGenre)";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":idPays", $idPays, PDO::PARAM_INT);
        $statement->bindParam(":idGenre", $idGenre, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'athlète a été ajoutée avec succès.";
            header("Location: manage-athletes.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de l'ajout de l'athlète.";
            header("Location: add-athlete.php");
            exit();
        }
    }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-athlete.php");
        exit();
    }
}

// Récupérez la liste des pays pour le menu déroulant
try {
    $queryPays = "SELECT id_pays, nom_pays FROM PAYS";
    $statementPays = $connexion->prepare($queryPays);
    $statementPays->execute();
    $paysid = $statementPays->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: add-athlete.php");
    exit();
}

// Récupérez la liste des sports pour le menu déroulant
try {
    $queryGenres = "SELECT id_genre, nom_genre FROM GENRE";
    $statementGenres = $connexion->prepare($queryGenres);
    $statementGenres->execute();
    $genres = $statementGenres->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: add-athlete.php");
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
    <title>Ajouter un(e) Athlète - Jeux Olympiques 2024</title>
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
                <li><a href="manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Ajouter un(e) Athlète</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-athlete.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet athlète?')">
            <label for="nomAthlete">Nom de l'Athlète :</label>
            <input oninput="this.value = this.value.toUpperCase()" type="text" name="nomAthlete" id="nomAthlete" required>

            <label for="prenomAthlete">Prénom de l'athlète :</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete" required>

            <div class="form-input">
            <label for="idPays">Pays de l'athlète :</label>
            <select name="idPays" id="idPays" required>
                <?php
                foreach ($paysid as $pays) {
                    echo "<option value='{$pays['id_pays']}'>{$pays['nom_pays']}</option>";
                }
                ?>
            </select>
            </div>

            <div class="form-input">
            <label for="idGenre">Nom du Sport :</label>
            <select name="idGenre" id="idGenre" required>
                <?php
                foreach ($genres as $genre) {
                    echo "<option value='{$genre['id_genre']}'>{$genre['nom_genre']}</option>";
                }
                ?>
            </select>
            </div>

            <input type="submit" value="Ajouter l'Athlète">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-athletes.php">Retour à la gestion des athlètes</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>

</body>

</html>
