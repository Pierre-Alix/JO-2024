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
    header("Location: manage-events.php");
    exit();
}

$id_epreuve = filter_input(INPUT_GET, 'id_epreuve', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'épreuve est un entier valide
if (!$id_epreuve && $id_epreuve !== 0) {
    $_SESSION['error'] = "ID de l'épreuve invalide.";
    header("Location: manage-events.php");
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

    // Vérifiez si le nom de l'épreuve est vide
    if (empty($nomEpreuve)) {
        $_SESSION['error'] = "Le nom de l'épreuve ne peut pas être vide.";
        header("Location: modify-event.php?id_epreuve=$id_epreuve");
        exit();
    }

    try {
        // Vérifiez si l'épreuve existe déjà
        $queryCheck = "SELECT id_epreuve FROM EPREUVE WHERE nom_epreuve = :nomEpreuve AND id_epreuve != :idEpreuve";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
        $statementCheck->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);
        $statementCheck->execute();

        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'épreuve existe déjà.";
            header("Location: modify-event.php?id_epreuve=$id_epreuve");
            exit();
        } else {
            // Requête pour mettre à jour l'épreuve
            $query = "UPDATE EPREUVE SET nom_epreuve = :nomEpreuve, date_epreuve = :dateEpreuve, heure_epreuve = :heureEpreuve, id_lieu = :idLieu, id_sport = :idSport WHERE id_epreuve = :idEpreuve";
            $statement = $connexion->prepare($query);
            $statement->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);
            $statement->bindParam(":nomEpreuve", $nomEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":dateEpreuve", $dateEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":heureEpreuve", $heureEpreuve, PDO::PARAM_STR);
            $statement->bindParam(":idLieu", $idLieu, PDO::PARAM_INT);
            $statement->bindParam(":idSport", $idSport, PDO::PARAM_INT);

            // Exécutez la requête
            if ($statement->execute()) {
                $_SESSION['success'] = "L'épreuve a été modifiée avec succès.";
                header("Location: manage-events.php");
                exit();
            } else {
                $_SESSION['error'] = "Erreur lors de la modification de l'épreuve.";
                header("Location: modify-event.php?id_epreuve=$id_epreuve");
                exit();
            }
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-event.php?id_epreuve=$id_epreuve");
        exit();
    }
}

// Récupérez les informations de l'épreuve pour affichage dans le formulaire
try {
    $queryEpreuve = "SELECT EPREUVE.nom_epreuve, EPREUVE.date_epreuve, EPREUVE.heure_epreuve, EPREUVE.id_lieu, LIEU.nom_lieu, SPORT.id_sport FROM EPREUVE
    INNER JOIN LIEU ON EPREUVE.id_lieu = LIEU.id_lieu
    INNER JOIN SPORT ON EPREUVE.id_sport = SPORT.id_sport
    WHERE EPREUVE.id_epreuve = :idEpreuve";
    $statementEpreuve = $connexion->prepare($queryEpreuve);
    $statementEpreuve->bindParam(":idEpreuve", $id_epreuve, PDO::PARAM_INT);
    $statementEpreuve->execute();

    if ($statementEpreuve->rowCount() > 0) {
        $epreuve = $statementEpreuve->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Épreuve non trouvée.";
        header("Location: manage-events.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-events.php");
    exit();
}

// Récupérez la liste des lieux depuis la base de données
$queryLieux = "SELECT id_lieu, nom_lieu FROM LIEU";
$statementLieux = $connexion->prepare($queryLieux);
$statementLieux->execute();
$lieux = $statementLieux->fetchAll(PDO::FETCH_ASSOC);

// Récupérez la liste des sports depuis la base de données
$querySports = "SELECT id_sport, nom_sport FROM SPORT";
$statementSports = $connexion->prepare($querySports);
$statementSports->execute();
$sports = $statementSports->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Modifier une Épreuve - Jeux Olympiques 2024</title>
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
        <h1>Modifier une Épreuve</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-event.php?id_epreuve=<?php echo $id_epreuve; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cette épreuve?')">
            <label for="nomEpreuve">Nom de l'Épreuve :</label>
            <input type="text" name="nomEpreuve" id="nomEpreuve" value="<?php echo htmlspecialchars($epreuve['nom_epreuve']); ?>" required>

            <label for="dateEpreuve">Date de l'Épreuve :</label>
            <input type="date" name="dateEpreuve" id="dateEpreuve" value="<?php echo htmlspecialchars($epreuve['date_epreuve']); ?>" required>

            <label for="heureEpreuve">Heure de l'Épreuve :</label>
            <input type="time" name="heureEpreuve" id="heureEpreuve" value="<?php echo htmlspecialchars($epreuve['heure_epreuve']); ?>" required>

            <div class="form-input">
            <label for="idLieu">Lieu de l'Épreuve :</label>
            <select name="idLieu" id="idLieu" required>
                <?php
                foreach ($lieux as $lieu) {
                    $selected = ($lieu['id_lieu'] == $epreuve['id_lieu']) ? 'selected' : '';
                    echo "<option value='{$lieu['id_lieu']}' $selected>{$lieu['nom_lieu']}</option>";
                }
                ?>
            </select>
            </div>

            <div class="form-input">
            <label for="idSport">Nom du Sport :</label>
            <select name="idSport" id="idSport" required>
                <?php
                foreach ($sports as $sport) {
                    $selected = ($sport['id_sport'] == $epreuve['id_sport']) ? 'selected' : '';
                    echo "<option value='{$sport['id_sport']}' $selected>{$sport['nom_sport']}</option>";
                }
                ?>
            </select>
            </div>

            <input type="submit" value="Modifier l'Épreuve">
        </form>
        <p class="paragraph-link">
            <a class="link-home" href="manage-events.php">Retour à la gestion des épreuves</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
</body>

</html>
