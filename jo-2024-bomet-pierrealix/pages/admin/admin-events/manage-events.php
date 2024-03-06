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
    <title>Liste des Épreuves - Jeux Olympiques 2024</title>
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
        <h1>Liste des Épreuves</h1>
        <div class="action-buttons">
            <button onclick="openAddEventForm()">Ajouter une épreuve</button>
            <!-- Autres boutons... -->
        </div>
        <!-- Tableau des épreuves -->
        <?php
        require_once("../../../database/database.php");

        try {
            // Requête pour récupérer la liste des épreuves depuis la base de données
            $query = "SELECT EPREUVE.*, SPORT.nom_sport, LIEU.nom_lieu FROM EPREUVE
                      INNER JOIN SPORT ON EPREUVE.id_sport = SPORT.id_sport
                      INNER JOIN LIEU ON EPREUVE.id_lieu = LIEU.id_lieu
                      ORDER BY date_epreuve, heure_epreuve";
            $statement = $connexion->prepare($query);
            $statement->execute();

            // Vérifier s'il y a des résultats
            if ($statement->rowCount() > 0) {
                echo "<table><tr><th>Nom de l'Épreuve</th><th>Date de l'Épreuve</th><th>Heure de l'Épreuve</th>
                      <th>Lieu de l'Épreuve</th><th>Nom du Sport</th><th>Modifier</th><th>Supprimer</th></tr>";

                // Afficher les données dans un tableau
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    // Assainir les données avant de les afficher
                    echo "<td>" . htmlspecialchars($row['nom_epreuve']) . "</td>";
                    // Changement de format de date
                    $dateEpreuve = new DateTime($row['date_epreuve']);
                    echo "<td>" . $dateEpreuve->format('d/m/Y') . "</td>";
                    echo "<td>" . htmlspecialchars($row['heure_epreuve']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_lieu']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['nom_sport']) . "</td>";
                    echo "<td><button onclick='openModifyEventForm({$row['id_epreuve']})'>Modifier</button></td>";
                    echo "<td><button onclick='deleteEventConfirmation({$row['id_epreuve']})'>Supprimer</button></td>";
                    echo "</tr>";
                }

                echo "</table>";
            } else {
                echo "<p>Aucune épreuve trouvée.</p>";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
        // Afficher les erreurs en PHP
        // (fonctionne à condition d’avoir activé l’option en local)
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
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
        function openAddEventForm() {
            // Ouvrir une fenêtre pop-up avec le formulaire de modification
            // L'URL contient un paramètre "id"
            window.location.href = 'add-event.php';
        }

        function openModifyEventForm(id_epreuve) {
            // Ajoutez ici le code pour afficher un formulaire stylisé pour modifier une épreuve
            // alert(id_epreuve);
            var link = 'modify-event.php?id_epreuve=' + id_epreuve;
            console.log(link);
            window.location.href = link;
        }

        function deleteEventConfirmation(id_epreuve) {
            // Ajoutez ici le code pour afficher une fenêtre de confirmation pour supprimer une épreuve
            if (confirm("Êtes-vous sûr de vouloir supprimer cette épreuve?")) {
                // Ajoutez ici le code pour la suppression de l'épreuve
                // alert(id_epreuve);
                window.location.href = 'delete-event.php?id_epreuve=' + id_epreuve;
            }
        }
    </script>
</body>

</html>
