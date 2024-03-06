<?php
session_start();

// Vérifiez si l'utilisateur est connecté
if (!isset($_SESSION['login'])) {
    header('Location: ../../../index.php');
    exit();
}

$login = $_SESSION['login'];
$nom_utilisateur = $_SESSION['prenom_utilisateur'];
$prenom_utilisateur = $_SESSION['nom_utilisateur'];
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
    <title>Liste des Résultats - Jeux Olympiques 2024</title>
    <style>
        /* Ajoutez votre style CSS ici */
        .action-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .action-buttons button {
            background-color: #1b1b1b;
            color: #d7c378;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .action-buttons button:hover {
            background-color: #d7c378;
            color: #1b1b1b;
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
        <h1>Liste des Résultats</h1>
        <div class="action-buttons">
            <button onclick="openAddResultForm()">Ajouter un résultat</button>
            <!-- Autres boutons... -->
        </div>
        <!-- Tableau des résultats -->
        <?php
        require_once("../../../database/database.php");

        try {
            // Requête pour récupérer la liste des résultats depuis la base de données
            $query = "SELECT PARTICIPER.*, ATHLETE.nom_athlete, ATHLETE.prenom_athlete, EPREUVE.nom_epreuve 
                      FROM PARTICIPER
                      INNER JOIN ATHLETE ON PARTICIPER.id_athlete = ATHLETE.id_athlete
                      INNER JOIN EPREUVE ON PARTICIPER.id_epreuve = EPREUVE.id_epreuve
                      ORDER BY nom_athlete";

            $statement = $connexion->prepare($query);
            $statement->execute();

            // Afficher les données dans un tableau
            if ($statement->rowCount() > 0) {
                echo "<table><tr><th>Athlète</th><th>Epreuves</th><th>Résultats</th><th>Modifier</th><th>Supprimer</th></tr>";

                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['nom_athlete']) . " " . htmlspecialchars($row['prenom_athlete']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_epreuve']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['resultat']) . "</td>";
                    echo "<td><button onclick='openModifyResultForm({$row['id_athlete']}, {$row['id_epreuve']})'>Modifier</button></td>";
                    echo "<td><button onclick='deleteResultConfirmation({$row['id_athlete']})'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucun athlète trouvé.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
        ?>
        <p class="paragraph-link">
            <a class="link-home" href="../admin.php">Accueil administration</a>
        </p>
    </main>
    <footer>
        <figure>
            <img src="../../../img/logo-jo-2024.png" alt="logo jeux olympiques 2024">
        </figure>
    </footer>
    <script>
        function openAddResultForm() {
            window.location.href = 'add-result.php';
        }

        function openModifyResultForm(id_athlete, id_epreuve) {
            window.location.href = 'modify-result.php?id_athlete=' + id_athlete + '&id_epreuve=' + id_epreuve;
        }

        function deleteResultConfirmation(id_athlete) {
            if (confirm("Êtes-vous sûr de vouloir supprimer ce résultat?")) {
                window.location.href = 'delete-result.php?id_athlete=' + id_athlete;
            }
        }
    </script>
</body>

</html>
