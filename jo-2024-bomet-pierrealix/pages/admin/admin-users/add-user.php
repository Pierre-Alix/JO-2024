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
    $nomUser = filter_input(INPUT_POST, 'nomUser', FILTER_SANITIZE_STRING);
    $prenomUser = filter_input(INPUT_POST, 'prenomUser', FILTER_SANITIZE_STRING);
    $loginUser = filter_input(INPUT_POST, 'loginUser', FILTER_SANITIZE_STRING);
    $pwUser = password_hash($_POST['pwUser'], PASSWORD_DEFAULT); // Hache le mot de passe

    // Vérifiez si le nom de l'utilisateur est vide
    if (empty($nomUser)) {
        $_SESSION['error'] = "Le nom de l'utilisateur ne peut pas être vide.";
        header("Location: add-user.php");
        exit();
    }

    // Vérifiez si le prenom de l'utilisateur est vide
    if (empty($prenomUser)) {
        $_SESSION['error'] = "Le prénom de l'utilisateur ne peut pas être vide.";
        header("Location: add-user.php");
        exit();
    }

    // Vérifiez si le login de l'utilisateur est vide
    if (empty($loginUser)) {
        $_SESSION['error'] = "Le login de l'utilisateur ne peut pas être vide.";
        header("Location: add-user.php");
        exit();
    }

    // Vérifiez si le nom de l'utilisateur est vide
    if (empty($pwUser)) {
        $_SESSION['error'] = "Le mot de passe de l'utilisateur ne peut pas être vide.";
        header("Location: add-user.php");
        exit();
    }

    try {
        // Vérifiez si l'utilisateur existe déjà
        $queryCheck = "SELECT id_utilisateur FROM UTILISATEUR WHERE login = :loginUser AND nom_utilisateur = :nomUser AND prenom_utilisateur = :prenomUser";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":loginUser", $loginUser, PDO::PARAM_STR);
        $statementCheck->bindParam(":nomUser", $nomUser, PDO::PARAM_STR);
        $statementCheck->bindParam(":prenomUser", $prenomUser, PDO::PARAM_STR);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'utilisateur existe déjà.";
            header("Location: add-user.php");
            exit();
        } else {
            // Requête pour ajouter un utilisateur
            $query = "INSERT INTO UTILISATEUR (prenom_utilisateur, nom_utilisateur, login, password) VALUES (:prenomUser, :nomUser, :loginUser, :pwUser)";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":nomUser", $nomUser, PDO::PARAM_STR);
            $statement->bindParam(":prenomUser", $prenomUser, PDO::PARAM_STR);
            $statement->bindParam(":loginUser", $loginUser, PDO::PARAM_STR);
            $statement->bindParam(":pwUser", $pwUser, PDO::PARAM_STR);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'utilisateur a été ajouté avec succès.";
                header("Location: manage-user.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de l'ajout de l'utilisateur.";
                header("Location: add-user.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: add-user.php");
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
    <title>Ajouter un Utilisateur - Jeux Olympiques 2024</title>
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
                <li><a href="../admin-places/manage-places.php">Gestion Lieux</a></li>
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
        <h1>Ajouter un utilisateur</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="add-user.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir ajouter cet utilisateur?')">
            <label for="nomUser">Nom de l'utilisateur :</label>
            <input type="text" name="nomUser" id="nomUser" required>
            <label for="prenomUser">Prénom de l'utilisateur :</label>
            <input type="text" name="prenomUser" id="prenomUser" required>
            <label for="loginUser">Login :</label>
            <input type="text" name="loginUser" id="loginUser" required>
            <label for="pwUser">Mot de Passe :</label>
            <input type="password" name="pwUser" id="pwUser" required>
            <input type="submit" value="Ajouter l'Utilisateur">
            
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-user.php">Retour à la gestion des utilisateurs</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
