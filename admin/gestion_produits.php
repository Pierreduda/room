<?php
require_once('../inc/init.php');

$titre = "Gestion des produits";

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

//Suppression de produit 
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {

    execRequete("DELETE FROM produit WHERE id_produit = :id_produit", array(
        'id_produit' => $_GET['id_produit']
    ));
    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

//Traitement du formulaire
if (!empty($_POST)) {

    // controles
    $nb_champs_vides = 0;

    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars(trim($value));
        if ($_POST[$key] == '') $nb_champs_vides++;
    }

    // Traitement Datetime pour insertion en BDD
    if (isset($_POST) && (!empty($_POST['date_begin']) && !empty($_POST['date_end']) && !empty($_POST['time_begin']) && !empty($_POST['time_end']))) {
        $date = explode("/", $_POST['date_begin']);
        $time = $_POST['time_begin'];
        $date_arrivee = "$date[2]-$date[1]-$date[0] $time:00"; //YYYY-MM-DD hh:mm:ss

        $date = explode("/", $_POST['date_end']);
        $time = $_POST['time_end'];
        $date_depart = "$date[2]-$date[1]-$date[0] $time:00";
    } else {
        $nb_champs_vides++;
    }

    if ($nb_champs_vides > 0) {
        $errors[] = "Il manque $nb_champs_vides information(s)";
    }

    if (empty($errors)) {

        if (isset($_POST['id_produit'])) {

            //Update
            execRequete("UPDATE produit SET id_salle = :id_salle, date_arrivee = :date_arrivee, date_depart = :date_depart, prix = :prix, etat = :etat WHERE id_produit = :id_produit", array(
                ':id_produit' => $_POST['id_produit'],
                ':id_salle' => $_POST['id_salle'],
                ':date_arrivee' => $date_arrivee,
                ':date_depart' => $date_depart,
                ':prix' => $_POST['prix'],
                ':etat' => $_POST['etat']
            ));
        } else {
            // Insertion en BDD
            execRequete("INSERT INTO produit VALUES (NULL, :id_salle, :date_arrivee, :date_depart, :prix, :etat)", array(
                ':id_salle' => $_POST['id_salle'],
                ':date_arrivee' => $date_arrivee,
                ':date_depart' => $date_depart,
                ':prix' => $_POST['prix'],
                ':etat' => $_POST['etat']
            ));
            header('location:' . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
require_once('../inc/header.php');
?>

<h1>Gestion des produits</h1>
<hr>
<?php

// filtres pour l'affichage des résultats
$colonneName = ['id_produit', 'id_salle', 'date_arrivee', 'date_depart', 'prix', 'etat'];
$colonne = "id_salle";
$orderBy = "ORDER BY";
if (isset($_GET['order']) && in_array($_GET['order'], $colonneName)) {
    $colonne = htmlspecialchars($_GET['order']);
}

//Affichage
$resultat = execRequete('SELECT id_produit, id_salle, DATE_FORMAT(date_arrivee, "%d/%m/%Y %H:%i"), DATE_FORMAT(date_depart, "%d/%m/%Y %H:%i"), prix, etat FROM produit ' . $orderBy . ' ' . $colonne);
if ($resultat->rowCount() == 0) {
?>
    <div class="alert alert-info">Il n'y a pas encore de produit enregistrés</div>
<?php
} else {
?>
    <table class="table table-bordered table-striped table-responsive-lg">
        <tr>
            <?php
            //entêtes de colonne
            for ($i = 0; $i < $resultat->columnCount(); $i++) {
                $colonne = $resultat->getColumnMeta($i);
            ?>
                <th>
                    <!-- On change le format du nom de la colonne pour qu'il passe dans le get et soit conforme à la requete filtrant l'affichage des resultats -->
                    <h5 class="text-center"><a href="?order=<?php
                                                            switch ($colonne['name']) {
                                                                case 'DATE_FORMAT(date_arrivee, "%d/%m/%Y %H:%i")':
                                                                    $colonne['name'] = "date_arrivee";
                                                                    break;
                                                                case 'DATE_FORMAT(date_depart, "%d/%m/%Y %H:%i")':
                                                                    $colonne['name'] = "date_depart";
                                                                    break;
                                                                default:
                                                                    $colonne['name'] = $colonne['name'];
                                                                    break;
                                                            }
                                                            echo $colonne['name'] ?>">
                            <?php
                            // on rechange le format pour qu'il apparaisse correctement dans les entetes de colonne
                            switch ($colonne['name']) {
                                case 'date_arrivee':
                                    $colonne['name'] = "Date d'arrivée";
                                    break;
                                case 'date_depart':
                                    $colonne['name'] = "Date de départ";
                                    break;
                                default:
                                    $colonne['name'] = $colonne['name'];
                                    break;
                            }
                            echo ucfirst($colonne['name']) ?></a></5>
                </th>
            <?php
            }
            ?>
            <th>Actions</th>
        </tr>
        <?php
        // Données 
        while ($ligne = $resultat->fetch()) {
        ?>
            <tr>
                <?php
                foreach ($ligne as $key => $value) {

                    switch ($key) {
                        case 'id_salle':
                            if ($key == 'id_salle' && !empty($value)) {
                                $salles = execRequete("SELECT id_salle, titre, photo FROM salle WHERE id_salle = $value");
                                while ($salle_affichee = $salles->fetch()) {
                                    $value = '
                                    <h5 class="text-center">' . $salle_affichee['id_salle'] . " - " . $salle_affichee['titre'] . '</h5>
                                    <div class="mx-auto"><a href="'.URL . 'photos/' . $salle_affichee['photo'] .'" data-lightbox="'.$salle_affichee['photo'].'"><img class="img-fluid" src="' . URL . 'photos/' . $salle_affichee['photo'] . '" alt="' . $salle_affichee['titre'] . '"></a></div>';
                                }
                            }
                            break;
                        case 'etat':
                            if ($key == 'etat') {
                                $etats = array(
                                    'libre' => 'Libre',
                                    'reservation' => 'Réservée'
                                );
                                $value = $etats[$value];
                            }
                            break;

                        case 'prix':
                            if ($key == 'prix') {
                                $value = number_format($value, 2, ',', '&nbsp;') . '&euro;';
                            }
                            break;
                    }
                ?>
                    <td><?php echo $value ?></td>
                <?php
                }
                ?>
                <!-- ACTIONS -->
                <td><a href="?action=edit&id_produit=<?php echo $ligne['id_produit'] ?>"><i class="fas fa-pencil-alt"></i></a>
                <!-- on fait passer l'action delete dans l'url -->
                <!-- la classe "confirm" est géré par JS -->
                <a href="?action=delete&id_produit=<?php echo $ligne['id_produit'] ?>" class="confirm"><i class="fas fa-trash"></i></a></td>
            </tr>
        <?php
        }
        ?>
    </table>

<?php
}

// Cas d'un formulaire d'edition d'un produit existant
if (isset($_GET['action']) && ($_GET['action'] == 'edit' && !empty($_GET['id_produit']) && is_numeric($_GET['id_produit']))) {
    $resultat = execRequete('SELECT * FROM produit WHERE id_produit = :id_produit', array(
        'id_produit' => $_GET['id_produit']
    ));
    $produit_actuel = $resultat->fetch();

    // Traitement Datetime pour l'affichage en cas d'édition format
    if (!empty($produit_actuel['date_arrivee'])) {
        $date_edit = explode(" ", $produit_actuel['date_arrivee']);
        $time_edit = explode(":", end($date_edit));
        $time_begin_edit = "$time_edit[0]:$time_edit[1]";
        $date_begin_edit = implode("/", array_reverse(explode("-", $date_edit[0])));
    }
    if (!empty($produit_actuel['date_depart'])) {
        $date_edit = explode(" ", $produit_actuel['date_depart']);
        $time_edit = explode(":", end($date_edit));
        $time_end_edit = "$time_edit[0]:$time_edit[1]";
        $date_end_edit = implode("/", array_reverse(explode("-", $date_edit[0])));
    }
}

// Formulaire d'ajout / edition d'un produit
?>
<?php if (!empty($errors)) : ?>
    <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="py-5">

    <!-- Ce qu'on envoie dans l'url si on veut update -->
    <?php if (!empty($produit_actuel['id_produit'])) : ?>
        <input type="hidden" name="id_produit" value="<?php echo $produit_actuel['id_produit'] ?>">
    <?php endif; ?>

    <!-- DATE PICKER -->
    <div id="sandbox">
        <div class="d-flex flex-row date input-daterange">
            <div class="col-md-6 pl-0">
                <div class="form-group">
                    <input type="text" name="date_begin" class="form-control datepicker-input" value="<?php echo (isset($produit_actuel)) ? $date_begin_edit : '' ?>" required>
                    <span class="form-label">Date d'arrivée</span>
                </div>
            </div>

            <div class="col-md-6 pr-0">
                <div class="form-group">
                    <input type="text" name="date_end" class="form-control datepicker-input" value="<?php echo (isset($produit_actuel)) ? $date_end_edit : '' ?>" required>
                    <span class="form-label">Date de départ</span>
                </div>
            </div>
        </div>
        <!-- TIME PICKER -->
        <div class="form-row">
            <div class="col-md-6 pl-0">
                <div class="form-group">
                    <input type="time" name="time_begin" class="form-control" value="<?php echo (isset($produit_actuel)) ? $time_begin_edit : '' ?>" required>
                    <span class="form-label">Date entrée</span>
                </div>
            </div>
            <div class="col-md-6 pl-0">
                <div class="form-group">
                    <input type="time" name="time_end" class="form-control" value="<?php echo (isset($produit_actuel)) ? $time_end_edit : '' ?>" required>
                    <span class="form-label">Date entrée</span>
                </div>
            </div>
        </div>
        <!-- ETAT -->
        <div class="form-row">
            <div class="form group col-6 ">
                <label for="etat">Etat</label>
                <select id="etat" name="etat" class="form-control">
                    <option <?php echo (isset($produit_actuel['etat']) && $produit_actuel['etat'] == 'libre')
                                ? 'selected' : '' ?> value="libre">Libre</option>

                    <option <?php echo (isset($produit_actuel['etat']) && $produit_actuel['etat'] == 'reservation') ?
                                "selected" : "" ?> value="reservation">Réservée</option>
                </select>
            </div>
            <!-- PRIX -->
            <div class="form-group col-6">
                <label for="prix">Tarif</label>
                <input id="prix" name="prix" type="text" class="form-control" value="<?php echo $_POST['prix'] ?? $produit_actuel['prix'] ?? '' ?>">
            </div>
        </div>


    </div>

    <!-- CHOIX SALLE -->
    <div class="form-group">
        <?php
        $resultat = execRequete("SELECT * FROM salle");
        $salles = $resultat->fetchAll();
        ?>
        <label for="id_salle">salle</label>
        <select id="id_salle" name="id_salle" type="text" class="form-control">
            <?php
            foreach ($salles as $salle) {
            ?>
                <option value="<?php echo $salle['id_salle'] ?>" <?php echo (
                                                                        (isset($_POST['salle']) && $_POST['salle'] == $salle['id_salle'])
                                                                        ||
                                                                        (isset($produit_actuel['salle']) && $produit_actuel['salle'] == $salle['id_salle'])) ? 'selected' : '' ?>><?php echo $salle['id_salle'] . " - " . $salle['titre'] ?>
                </option>
            <?php
            }
            ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Enregistrer</button>
</form>

<?php
require_once('../inc/footer.php');
?>