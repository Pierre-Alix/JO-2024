<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'utilisateur est fourni dans l'URL
if (!isset($_GET['id_utilisateur'])) {
    $_SESSION['error'] = "ID de l'utilisateur manquant.";
    header("Location: manage-user.php");
    exit();
}

$id_utilisateur = filter_input(INPUT_GET, 'id_utilisateur', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'utilisateur est un entier valide
if (!$id_utilisateur && $id_utilisateur !== 0) {
    $_SESSION['error'] = "ID de l'utilisateur invalide.";
    header("Location: manage-user.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomUser = filter_input(INPUT_POST, 'nomUser', FILTER_SANITIZE_STRING);
    $prenomUser = filter_input(INPUT_POST, 'prenomUser', FILTER_SANITIZE_STRING);

    // Vérifiez si le nom de l'utilisateur est vide
    if (empty($nomUser) || empty($prenomUser)) {
        $_SESSION['error'] = "Le nom et prénom de l'utilisateur ne peuvent pas être vides.";
        header("Location: modify-user.php?id_utilisateur=$id_utilisateur");
        exit();
    }

    try {
        // Vérifiez si l'utilisateur existe déjà
        $queryCheck = "SELECT id_utilisateur FROM UTILISATEUR WHERE nom_utilisateur = :nomUser AND prenom_utilisateur = :prenomUser AND id_utilisateur <> :idUser";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomUser", $nomUser, PDO::PARAM_STR);
        $statementCheck->bindParam(":prenomUser", $prenomUser, PDO::PARAM_STR);
        $statementCheck->bindParam(":idUser", $id_utilisateur, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'utilisateur existe déjà.";
            header("Location: modify-user.php?id_utilisateur=$id_utilisateur");
            exit();
        }

        // Requête pour mettre à jour l'utilisateur
        $query = "UPDATE UTILISATEUR SET nom_utilisateur = :nomUser, prenom_utilisateur = :prenomUser WHERE id_utilisateur = :idUser";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":nomUser", $nomUser, PDO::PARAM_STR);
        $statement->bindParam(":prenomUser", $prenomUser, PDO::PARAM_STR);
        $statement->bindParam(":idUser", $id_utilisateur, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'utilisateur a été modifié avec succès.";
            header("Location: manage-user.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'utilisateur.";
            header("Location: modify-user.php?id_utilisateur=$id_utilisateur");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-user.php?id_utilisateur=$id_utilisateur");
        exit();
    }
}

// Récupérez les informations de l'utilisateur pour affichage dans le formulaire
try {
    $queryUser = "SELECT nom_utilisateur, prenom_utilisateur FROM UTILISATEUR WHERE id_utilisateur = :idUser";
    $statementUser = $connexion->prepare($queryUser);
    $statementUser->bindParam(":idUser", $id_utilisateur, PDO::PARAM_INT);
    $statementUser->execute();

    if ($statementUser->rowCount() > 0) {
        $user = $statementUser->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Utilisateur non trouvé.";
        header("Location: manage-user.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-user.php");
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
    <title>Modifier un Utilisateur - Jeux Olympiques 2024</title>
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
        <h1>Modifier un Utilisateur</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-user.php?id_utilisateur=<?php echo $id_utilisateur; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet utilisateur?')">
            <label for="prenomUser">Prénom de l'utilisateur :</label>
            <input type="text" name="prenomUser" id="prenomUser"
                value="<?php echo htmlspecialchars($user['prenom_utilisateur']); ?>" required>
            <label for="nomUser">Nom de l'utilisateur :</label>
            <input type="text" name="nomUser" id="nomUser"
                value="<?php echo htmlspecialchars($user['nom_utilisateur']); ?>" required>
            <input type="submit" value="Modifier l'Utilisateur">
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
