<?php
require_once('../inc/init.php');

$titre = "Gestion des salles";

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

//Suppression de salle 
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_salle']) && is_numeric($_GET['id_salle'])) {
    $salle_asup = execRequete("SELECT photo FROM salle WHERE id_salle = :id_salle", array(
        'id_salle' => $_GET['id_salle']
    ));
    // récupération de la salle en BDD pour obtenir le nom du fichier photo
    if ($salle_asup->rowCount() == 1) {
        $infos = $salle_asup->fetch();
        $photo = $infos['photo'];

        //suppression du fichier physique
        if (isset($_POST['photo_actuelle']) && file_exists($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $_POST['photo_actuelle'])) {
            unlink($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $_POST['photo_actuelle']);
        }

        //suppression du salle en base
        execRequete("DELETE FROM salle WHERE id_salle = :id_salle", array(
            'id_salle' => $_GET['id_salle']
        ));
        header('location:' . $_SERVER['PHP_SELF']);
        exit();
    }
}

//Traitement du formulaire
if (!empty($_POST)) {

    // controles
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = htmlspecialchars(trim($value));
        if ($_POST[$key] == '') $nb_champs_vides++;
    }

    if (empty($_FILES['photo']['name'])) {

        // si je suis sur un salle en édition, j'ai une photo actuelle, donc je ne considère pas que c'est un champ vide
        if (empty($_POST['photo_actuelle'])) {
            $nb_champs_vides++;
        }
    } else {
        $mimeAutorises = array('image/jpeg', 'image/png', 'image/webp');

        if (!in_array($_FILES['photo']['type'], $mimeAutorises)) {
            $errors[] = 'Format incorrect : ' . $_FILES['photo']['type'] . '<br>Fichiers, JPEG, PNG et WEBP seulement';
        }
    }

    if ($nb_champs_vides > 0) {
        $errors[] = "Il manque $nb_champs_vides information(s)";
    }
    if (empty($errors)) {

        if (!empty($_FILES['photo']['name'])) {

            // si j'ai déjà une photo qui existe
            if (isset($_POST['photo_actuelle']) && file_exists($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $_POST['photo_actuelle'])) {

                //suppression du fichier
                unlink($_SERVER['DOCUMENT_ROOT'] . URL . 'photos/' . $_POST['photo_actuelle']);
            }

            // Gérer la photo (copie physique du fichier)
            $nomPhotoBDD = str_replace(' ', '_', $_POST['titre']) . '_' . $_FILES['photo']['name'];
            $dossierPhotos = $_SERVER['DOCUMENT_ROOT'] . URL . 'photos/';
            
            // On deplace le fichier temporaire vers le dossier photos sous un nom unique composé de la référence et du nom original du fichier
            move_uploaded_file($_FILES['photo']['tmp_name'], $dossierPhotos . $nomPhotoBDD);
        } else {
            $nomPhotoBDD = $_POST['photo_actuelle'];
        }

        // on retire $_POST['photo_actuelle'] sinon on aura un marqueur en trop 
        unset($_POST['photo_actuelle']);
        $_POST['photo'] = $nomPhotoBDD;

        if (isset($_POST['id_salle'])) {

            //Update
            execRequete("UPDATE salle SET titre = :titre, description = :description, photo = :photo, pays = :pays, ville = :ville, adresse = :adresse, cp= :cp, capacite = :capacite, categorie= :categorie WHERE id_salle = :id_salle", $_POST);
        } else {

            // Insertion en BDD
            execRequete("INSERT INTO salle VALUES (NULL, :titre, :description, :photo, :pays, :ville, :adresse, :cp, :capacite, :categorie)", $_POST);

            header('location:' . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
require_once('../inc/header.php');
?>

<h1>Gestion des salles</h1>
<hr>
<ul class="nav nav-tabs nav-justified">
    <li class="nav-item">
        <a class="nav-link <?php echo (!isset($_GET['action'])
                                ||
                                (isset($_GET['action']) && $_GET['action'] == 'affichage')) ? 'active' : '' ?>" 
                                href="?action=affichage">Affichage des salles</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) ? 'active' : '' ?>" href="?action=ajout">Ajouter/Editer un salle</a>
    </li>
</ul>
<?php

if (!isset($_GET['action']) || (isset($_GET['action']) && $_GET['action'] == 'affichage')) {

    // filtres d'affichage
    $colonneName = ['id_salle', 'titre', 'description', 'photo', 'pays', 'ville', 'cp', 'capacite', 'categorie'];
    $colonne = "id_salle";
    if (isset($_GET['order']) && in_array($_GET['order'], $colonneName)) {
        $colonne = htmlspecialchars($_GET['order']);
    }

    //Affichage des salles
    $resultat = execRequete("SELECT * FROM salle ORDER BY $colonne");
    if ($resultat->rowCount() == 0) {
?>
        <div class="alert alert-info">Il n'y a pas encore de salle enregistrés</div>
    <?php
    } else {
    ?>
        <table class="table table-bordered table-striped table-responsive-lg mt-4">
            <tr>
                <?php
                //entêtes de colonne
                for ($i = 0; $i < $resultat->columnCount(); $i++) {
                    $colonne = $resultat->getColumnMeta($i);
                ?>
                    <th><a href="?order=<?php echo $colonne['name'] ?>"><?php echo ucfirst($colonne['name']) ?></a></th>
                <?php
                }
                ?>
                <th>Actions</th>
            </tr>
            <?php
            // Les données 
            while ($ligne = $resultat->fetch()) {
            ?>
                <tr>
                    <?php
                    foreach ($ligne as $key => $value) {
                        switch ($key) {
                            case 'photo':
                                if ($key == 'photo' && !empty($value)) {
                                    $value = '<a href="'.URL . 'photos/' . $value .'" data-lightbox="'.$ligne['titre'].'"><img class="img-fluid" src="' . URL . 'photos/' . $value . '" alt="' . $ligne['titre'] . '"></a>';
                                }
                                break;
                            case 'categorie':
                                if ($key == 'categorie') {
                                    $categories = array(
                                        'réunion' => 'Réunion',
                                        'bureau' => 'Bureau',
                                        'formation' => 'Formation',
                                    );
                                    $value = $categories[$value];
                                }
                                break;

                                // pour la description si il y a plus de 30 caractères on met '...', si on veut voir le reste on va dans l'éditeur
                            case 'description':
                                $extrait = (iconv_strlen($value) > 30) ? substr($value, 0, 30) : $value;
                                // Pour éviter de couper un mot
                                if ($extrait != $value) {
                                    $lastSpace = strrpos($extrait, '');
                                    $value =  substr($extrait, 0, $lastSpace) . '...';
                                }
                                break;
                        }
                    ?>
                        <td><?php echo $value ?></td>
                    <?php
                    }
                    ?>
                    <!-- on remplace "ajout" par "edit" dans le GET avec l'id du salle pour afficher le même formulaire mais en remplissant tous les champs avec les donnees de la salle à editer -->
                    <td><a href="?action=edit&id_salle=<?php echo $ligne['id_salle'] ?>"><i class="fas fa-search"></i></a>
                        <a href="?action=edit&id_salle=<?php echo $ligne['id_salle'] ?>"><i class="fas fa-pencil-alt"></i></a>

                        <!-- on fait passer l'action delete dans l'url -->
                        <!-- la classe "confirm" est géré par notre JS -->
                        <a href="?action=delete&id_salle=<?php echo $ligne['id_salle'] ?>" class="confirm"><i class="fas fa-trash"></i></a>
                    </td>

                </tr>
            <?php
            }
            ?>
        </table>

    <?php
    }
}
// Affichage de l'onglet ajout/edition d'un salle
if (isset($_GET['action']) && ($_GET['action'] == 'ajout' || $_GET['action'] == 'edit')) {

    // Cas d'un formulaire d'edition d'un salle existant
    if ($_GET['action'] == 'edit' && !empty($_GET['id_salle']) && is_numeric($_GET['id_salle'])) {
        $resultat = execRequete('SELECT * FROM salle WHERE id_salle = :id_salle', array(
            'id_salle' => $_GET['id_salle']
        ));
        $salle_actuelle = $resultat->fetch();
    }
    ?>

    <!-- Formulaire d'ajout / edition de salle -->
    <?php if (!empty($errors)) : ?>
        <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="py-5">

        <?php if (!empty($salle_actuelle['id_salle'])) : ?>
            <input type="hidden" name="id_salle" value="<?php echo $salle_actuelle['id_salle'] ?>">
        <?php endif; ?>

        <div class="form-group">
            <label for="titre">Titre</label>
            <input id="titre" name="titre" type="text" class="form-control" value="<?php echo $_POST['titre'] ?? $salle_actuelle['titre'] ?? '' ?>">
        </div>
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" type="text" class="form-control" rows="7"><?php echo $_POST['description'] ?? $salle_actuelle['description'] ?? '' ?></textarea>
        </div>
        <div class="form-row mb-4">
            <div class="form group col-5">
                <label for="capacite">Capacité</label>
                <select id="capacite" name="capacite" type="text" class="form-control">
                    <?php
                    $capacites = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 15, 20, 25, 30, 35);
                    foreach ($capacites as $capacite) {
                    ?>
                        <option <?php echo (
                                    (isset($_POST['capacite']) && $_POST['capacite'] == $capacite)
                                    ||
                                    (isset($salle_actuelle['capacite']) && $salle_actuelle['capacite'] == $capacite)) ? 'selected' : '' ?>><?php echo $capacite ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
            <div class="form group col-5 ">
                <label for="categorie">Catégorie</label>
                <select id="categorie" name="categorie" class="form-control">

                    <option <?php echo ((isset($_POST['capacite']) && $_POST['capacite'] == $capacite)
                                ||
                                (isset($salle_actuelle['capacite']) && $salle_actuelle['capacite'] == 'réunion'))
                                ? 'selected' : '' ?> value="réunion">Réunion</option>

                    <option <?php echo ((isset($_POST['capacite']) && $_POST['capacite'] == $capacite)
                                ||
                                (isset($salle_actuelle['capacite']) && $salle_actuelle['capacite'] == 'bureau')) ?
                                "selected" : "" ?> value="bureau">Bureau</option>

                    <option <?php echo ((isset($_POST['capacite']) && $_POST['capacite'] == $capacite)
                                ||
                                (isset($salle_actuelle['capacite']) && $salle_actuelle['capacite'] == 'formation')) ?
                                "selected" : "" ?> value="formation">Formation</option>
                </select>
            </div>
        </div>

        <!-- IMAGE -->
        <div class="form-group">
            <label for="photo"><i class="fas fa-camera-retro iconePhoto"></i></label>
            <input id="photo" name="photo" type="file" class="form-control d-none" accept="image/jpeg, image/png, image/webp">

            <!-- Aperçu de l'image -->
            <div id="preview">
                <?php
                if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($salle_actuelle['photo'])) {
                ?>
                    <img src="<?php echo URL . 'photos/' . $salle_actuelle['photo'] ?>" alt="<?php echo $salle_actuelle['titre'] ?>" class="img-fluid vignette" id="placeholder">
                <?php
                } else {
                ?>
                    <img src="<?php echo URL . 'img/placeholder.png' ?>" alt="placeholder" class="img-fluid vignette" id="placeholder">
                <?php
                }
                ?>
            </div>

            <!-- affichage d'un champ caché qui mémorise le nom de l'image que j'ai déjà en base de donnée (pour un salle en édition) -->
            <?php
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && !empty($salle_actuelle['photo'])) {
            ?>
                <input type="hidden" name="photo_actuelle" value="<?php echo $salle_actuelle['photo'] ?>">
            <?php
            }
            ?>
        </div>

        <!-- COORDONNEES -->
        <div class="form-group">
            <label for="pays">Pays</label>
            <select id="pays" name="pays" type="text" class="form-control">
                <?php
                $selectpays = array('France', 'Allemagne', 'Belgique', 'Angleterre', 'Italie');
                foreach ($selectpays as $pays) {
                ?>
                    <option <?php echo (
                                (isset($_POST['pays']) && $_POST['pays'] == $pays)
                                ||
                                (isset($salle_actuelle['pays']) && $salle_actuelle['pays'] == $pays)) ? 'selected' : '' ?>><?php echo $pays ?></option>
                <?php
                }
                ?>
            </select>
        </div>

        <div class="form-group">
            <label for="adresse">Adresse</label>
            <input id="adresse" name="adresse" type="text" class="form-control" value="<?php echo $_POST['adresse'] ?? $salle_actuelle['adresse'] ?? '' ?>">
        </div>
        <div class="form-row">
            <div class="form-group col-4">
                <label for="cp">Code Postal</label>
                <input id="cp" name="cp" type="text" class="form-control 
                <?php
                echo (!empty($_POST['cp']) &&
                    !preg_match('#^[0-9]{5}$#', $_POST['cp'])) ? 'is-invalid' : '' ?>" value="<?php echo $_POST['cp'] ?? $salle_actuelle['cp'] ?? '' ?>">

                <div class="invalid-feedback">
                    Merci de renseigner un code postal français valide (5 chiffres)
                </div>
            </div>
            <div class="form-group col-8">
                <label for="ville">Ville</label>
                <input id="ville" name="ville" type="text" class="form-control" value="<?php echo $_POST['ville'] ?? $salle_actuelle['ville'] ?? '' ?>">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </form>
<?php
}

require_once('../inc/footer.php');
?>