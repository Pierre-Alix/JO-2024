<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'épreuve est fourni dans l'URL
if (!isset($_GET['id_epreuve'])) {
    $_SESSION['error'] = "ID de l'épreuve manquant.";
    header("Location: modify-result.php");
    exit();
}

$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'épreuve est un entier valide
if (!$id_epreuve && $id_epreuve !== 0) {
    $_SESSION['error'] = "ID de l'épreuve invalide.";
    header("Location: modify-result.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $resultat = filter_input(INPUT_POST, 'resultat', FILTER_SANITIZE_STRING);
    $new_id_epreuve = filter_input(INPUT_POST, 'id_epreuve', FILTER_VALIDATE_INT);

    try {
        // Requête pour mettre à jour le résultat et l'ID de l'épreuve
        $query = "UPDATE PARTICIPER SET resultat = :resultat, id_epreuve = :new_id_epreuve WHERE id_epreuve = :id_epreuve";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":resultat", $resultat, PDO::PARAM_STR);
        $statement->bindParam(":new_id_epreuve", $new_id_epreuve, PDO::PARAM_INT);
        $statement->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "Le résultat de l'épreuve a été modifié avec succès.";
            header("Location: manage-results.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification du résultat de l'épreuve.";
            header("Location: modify-result.php?id_epreuve=$id_epreuve");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-result.php?id_epreuve=$id_epreuve");
        exit();
    }
}

// Récupérez les informations de l'épreuve pour affichage dans le formulaire
try {
    $queryEvent = "SELECT * FROM PARTICIPER WHERE id_epreuve = :id_epreuve";
    $statementEvent = $connexion->prepare($queryEvent);
    $statementEvent->bindParam(":id_epreuve", $id_epreuve, PDO::PARAM_INT);
    $statementEvent->execute();

    if ($statementEvent->rowCount() > 0) {
        $event = $statementEvent->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Épreuve non trouvée.";
        header("Location: modify-result.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: modify-result.php");
    exit();
}

// Récupérer la liste des épreuves pour le menu déroulant
try {
    $queryEpreuves = "SELECT id_epreuve, nom_epreuve FROM EPREUVE";
    $statementEpreuves = $connexion->prepare($queryEpreuves);
    $statementEpreuves->execute();
    $epreuves = $statementEpreuves->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: modify-result.php");
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
    <title>Modifier un Résultat - Jeux Olympiques 2024</title>
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
                <li><a href="modify-result.php">Gestion Calendrier</a></li>
                <li><a href="../admin-countries/manage-countries.php">Gestion Pays</a></li>
                <li><a href="../admin-gender/manage-genders.php">Gestion Genres</a></li>
                <li><a href="../admin-athletes/manage-athletes.php">Gestion Athlètes</a></li>
                <li><a href="../admin-results/manage-results.php">Gestion Résultats</a></li>
                <li><a href="../../logout.php">Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h1>Modifier un Résultat</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-result.php?id_epreuve=<?php echo $id_epreuve; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier ce résultat?')">
            <div class="form-input">
            <label for="id_epreuve">Épreuve :</label>
            <select name="id_epreuve" id="id_epreuve">
                <?php foreach ($epreuves as $epreuve) : ?>
                    <option value="<?php echo $epreuve['id_epreuve']; ?>" <?php if ($epreuve['id_epreuve'] == $event['id_epreuve']) echo "selected"; ?>>
                        <?php echo htmlspecialchars($epreuve['nom_epreuve']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            </div>
            <div class="form-input">
            <label for="resultat">Résultat :</label>
            <input type="text" name="resultat" id="resultat"
                value="<?php echo htmlspecialchars($event['resultat']); ?>" required>
            </div>
            <input type="submit" value="Modifier le Résultat">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="modify-result.php">Retour à la gestion des épreuves</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
