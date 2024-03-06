<?php
session_start();
require_once("../../../database/database.php");

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

// Vérifiez si l'ID de l'athlète est fourni dans l'URL
if (!isset($_GET['id_athlete'])) {
    $_SESSION['error'] = "ID de l'athlète manquant.";
    header("Location: manage-athletes.php");
    exit();
}

$id_athlete = filter_input(INPUT_GET, 'id_athlete', FILTER_VALIDATE_INT);

// Vérifiez si l'ID de l'athlète est un entier valide
if (!$id_athlete && $id_athlete !== 0) {
    $_SESSION['error'] = "ID de l'athlète invalide.";
    header("Location: manage-athletes.php");
    exit();
}

// Vérifiez si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Assurez-vous d'obtenir des données sécurisées et filtrées
    $nomAthlete = filter_input(INPUT_POST, 'nomAthlete', FILTER_SANITIZE_STRING);
    $prenomAthlete = filter_input(INPUT_POST, 'prenomAthlete', FILTER_SANITIZE_STRING);
    $idPays = filter_input(INPUT_POST, 'idPays', FILTER_VALIDATE_INT);
    $idGenre = filter_input(INPUT_POST, 'idGenre', FILTER_VALIDATE_INT);

    // Vérifiez si le nom de l'athlète est vide
    if (empty($nomAthlete)) {
        $_SESSION['error'] = "Le nom de l'athlète ne peut pas être vide.";
        header("Location: modify-athlete.php?id_athlete=$id_athlete");
        exit();
    }

    try {
        // Vérifiez si l'athlète existe déjà
        $queryCheck = "SELECT id_athlete FROM ATHLETE WHERE nom_athlete = :nomAthlete AND prenom_athlete = :prenomAthlete AND id_pays = :idPays AND id_genre = :idGenre";
        $statementCheck = $connexion->prepare($queryCheck);
        $statementCheck->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statementCheck->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statementCheck->bindParam(":idPays", $idPays, PDO::PARAM_INT);
        $statementCheck->bindParam(":idGenre", $idGenre, PDO::PARAM_INT);
        $statementCheck->execute();
 
        if ($statementCheck->rowCount() > 0) {
            $_SESSION['error'] = "L'athlète existe déjà.";
            header("Location: modify-athlete.php?id_athlete=$id_athlete");
            exit();
        }
        
        // Requête pour mettre à jour l'athlète
        $query = "UPDATE ATHLETE SET nom_athlete = :nomAthlete, prenom_athlete = :prenomAthlete, id_pays = :idPays, id_genre = :idGenre WHERE id_athlete = :idAthlete";
        $statement = $connexion->prepare($query);
        $statement->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
        $statement->bindParam(":nomAthlete", $nomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":prenomAthlete", $prenomAthlete, PDO::PARAM_STR);
        $statement->bindParam(":idPays", $idPays, PDO::PARAM_INT);
        $statement->bindParam(":idGenre", $idGenre, PDO::PARAM_INT);

        // Exécutez la requête
        if ($statement->execute()) {
            $_SESSION['success'] = "L'Epreuve a été modifié avec succès.";
            header("Location: manage-athletes.php");
            exit();
        } else {
            $_SESSION['error'] = "Erreur lors de la modification de l'epreuve.";
            header("Location: modify-athletes.php?id_athlete=$id_athlete");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
        header("Location: modify-athletes.php?id_athlete=$id_athlete");
        exit();
    }
}

// Récupérez les informations de l'athlète pour affichage dans le formulaire
try {
    $queryAthlete = "SELECT ATHLETE.nom_athlete, ATHLETE.prenom_athlete, ATHLETE.id_pays, ATHLETE.id_genre FROM ATHLETE
    INNER JOIN PAYS ON ATHLETE.id_pays = PAYS.id_pays
    INNER JOIN GENRE ON ATHLETE.id_genre = GENRE.id_genre
    WHERE ATHLETE.id_athlete = :idAthlete";
    $statementAthlete = $connexion->prepare($queryAthlete);
    $statementAthlete->bindParam(":idAthlete", $id_athlete, PDO::PARAM_INT);
    $statementAthlete->execute();

    if ($statementAthlete->rowCount() > 0) {
        $athlete = $statementAthlete->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['error'] = "Athlète non trouvée.";
        header("Location: manage-athletes.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de base de données : " . $e->getMessage();
    header("Location: manage-athletes.php");
    exit();
}

// Récupérez la liste des lieux depuis la base de données
$queryPays = "SELECT id_pays, nom_pays FROM PAYS";
$statementPays = $connexion->prepare($queryPays);
$statementPays->execute();
$paysid = $statementPays->fetchAll(PDO::FETCH_ASSOC);

// Récupérez la liste des sports depuis la base de données
$queryGenre = "SELECT id_genre, nom_genre FROM GENRE";
$statementGenre = $connexion->prepare($queryGenre);
$statementGenre->execute();
$genres = $statementGenre->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Modifier un(e) Athlète - Jeux Olympiques 2024</title>
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
            <!-- Menu vers les pages sports, athletes, et results -->
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
        <h1>Modifier un(e) Athlète</h1>
        <?php
        if (isset($_SESSION['error'])) {
            echo '<p style="color: red;">' . $_SESSION['error'] . '</p>';
            unset($_SESSION['error']);
        }
        ?>
        <form action="modify-athlete.php?id_athlete=<?php echo $id_athlete; ?>" method="post"
            onsubmit="return confirm('Êtes-vous sûr de vouloir modifier cet(te) athlète?')">
            <label for="nomAthlete">Nom de l'Athlète :</label>
            <input oninput="this.value = this.value.toUpperCase()" type="text" name="nomAthlete" id="nomAthlete" value="<?php echo htmlspecialchars($athlete['nom_athlete']); ?>" required>

            <label for="prenomAthlete">Prenom de l'Athlète :</label>
            <input type="text" name="prenomAthlete" id="prenomAthlete" value="<?php echo htmlspecialchars($athlete['prenom_athlete']); ?>" required>

            <div class="form-input">
            <label for="idPays">Pays de l'athlète :</label>
            <select name="idPays" id="idPays" required>
                <?php
                foreach ($paysid as $pays) {
                    $selected = ($pays['id_pays'] == $athlete['id_pays']) ? 'selected' : '';
                    echo "<option value='{$pays['id_pays']}' $selected>{$pays['nom_pays']}</option>";
                }
                ?>
            </select>
            </div>

            <div class="form-input">
            <label for="idGenre">Genre de l'athlète :</label>
            <select name="idGenre" id="idGenre" required>
                <?php
                foreach ($genres as $genre) {
                    $selected = ($genre['id_genre'] == $athlete['id_genre']) ? 'selected' : '';
                    echo "<option value='{$genre['id_genre']}' $selected>{$genre['nom_genre']}</option>";
                }
                ?>
            </select>
            </div>

            <input type="submit" value="Modifier l'Athlète">
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
