<?php

require_once('../inc/init.php');

$titre = "Gestion des membres";

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

// Possibilité de promouvoir les membres
if (!empty($_POST) && isSuperAdmin() && isset($_POST['changestatut'])) {
    execRequete("UPDATE membre SET statut =:newstatut WHERE id_membre = :id_membre", array(
        'newstatut' => $_POST['newstatut'],
        'id_membre' => $_POST['id_membre']
    ));
    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

// Ajout d'un membre
if (!empty($_POST) && isset($_POST['newmembre'])) {

    unset($_POST['newmembre']);

    $errors = array();
    // controle des champs vides :
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) {
        $errors[] = "Il manque $nb_champs_vides information(s)";
    }

    // controle pseudo, unique et pas vide :
    if (getMembreByPseudo($_POST['pseudo'])) {
        $errors[] = 'Pseudo indisponible. Merci d\'en choisir un autre';
    }
    if (
        iconv_strlen(trim($_POST['pseudo'])) > 20 ||
        iconv_strlen(trim($_POST['pseudo'])) < 2
    ) {
        $errors[] = 'Pseudo invalide';
    }

    // controle du mot de passe :
    if (!preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@])[\w$!\-\@]{8,20}$#', $_POST['mdp'])) {
        $errors[] = 'Complexité du mot de passe non respectée';
    }

    // controle de l'email :
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format de mail invalide';
    }

    // verification du nombre d'erreurs
    if (empty($errors)) {
        //aucune erreur, je peux procéder au cryptage du mdp
        $_POST['mdp'] = password_hash($_POST['mdp'], PASSWORD_DEFAULT);
        execRequete("INSERT INTO membre VALUES(NULL, :pseudo, :mdp, :nom, :prenom, :email, :civilite, 0, NOW())", $_POST);

        header("location:" . URL . 'admin/gestion_membres.php');
        exit();
    }
}

// Suppression d'un membre
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_membre']) && is_numeric($_GET['id_membre'])) {

    //suppression du membre en base
    execRequete("DELETE FROM membre WHERE id_membre = :id_membre", array(
        'id_membre' => $_GET['id_membre']
    ));
    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

// Affichage des membres

//filtres
$colonneName = ['id_membre', 'pseudo', 'email','civilite', 'prenom', 'nom', 'date_enregistrement'];
$colonne = "statut";

if (isset($_GET['order']) && in_array($_GET['order'], $colonneName)){
    $colonne = htmlspecialchars($_GET['order']);
}

$membres = execRequete("SELECT * FROM membre ORDER BY $colonne ASC, nom, prenom");
require_once('../inc/header.php');

?>
<h1>Gestion des membres</h1>
<hr>
<table class="table table-bordered table-striped table-responsive-lg">
    <!-- entete -->
    <tr>
        <?php
        for ($i = 0; $i < $membres->columnCount(); $i++) {
            $colonne = $membres->getColumnMeta($i);
            if ($colonne['name'] != 'mdp') {
        ?>
                <th><a href="?order=<?php echo $colonne['name'] ?>"><?php echo ucfirst($colonne['name']) ?></a></th>
            <?php
            }
        }
        if (isSuperAdmin()) {
            ?>
            <th>Actions</th>
        <?php
        }

        ?>
    </tr>

    <?php
    // Données
    while ($membre = $membres->fetch()) {
    ?>
        <tr>
            <?php
            foreach ($membre as $key => $value) {
                if ($key != 'mdp') {
                    if ($key == 'statut') {
                        switch ($value) {
                            case 0:
                                $value = 'Membre';
                                break;
                            case 1:
                                $value = 'Administrateur';
                                break;
                            case 2:
                                $value = 'SuperAdministrateur';
                                break;
                        }
                    }
                    if ($key == 'civilite') {
                        switch ($value) {
                            case 'm':
                                $value = 'Homme';
                                break;
                            case 'f':
                                $value = 'Femme';
                                break;
                        }
                    }
            ?>
                    <td><?php echo $value ?></td>
            <?php
                }
            }
            ?>
            <!-- on fait passer l'action delete dans l'url -->
            <!-- la classe "confirm" est géré par notre JS -->
            <td>
                <a href="?action=delete&id_membre=<?php echo $membre['id_membre'] ?>" class="confirm"><i class="fas fa-trash"></i></a>
                <?php
                if ($membre['id_membre'] != $_SESSION['membre']['id_membre']) {
                ?>
                    <form method="post">
                        <input type="hidden" name="id_membre" value="<?php echo $membre['id_membre'] ?>">

                        <select name="newstatut" id="statutmembre">
                            <option value="0" <?php if ($membre['statut'] == 0) echo 'selected' ?>>Membre</option>
                            <option value="1" <?php if ($membre['statut'] == 1) echo 'selected' ?>>Administrateur</option>
                            <option value="2" <?php if ($membre['statut'] == 2) echo 'selected' ?>>Super Admin</option>
                        </select>

                        <button type="label" name="changestatut" class="btn btn-primary p-0 mt-2">Valider</button>
                    </form>
                <?php
                }
                ?>
            </td>
        <?php
    }
        ?>
        </tr>
</table>

<h1 class="mt-2">Ajouter un membre</h1>
<hr>

<?php
if (!empty($errors)) : ?>
    <div class="alert alert-danger">
        <?php echo implode('<br>', $errors) ?>
    </div>
<?php endif; ?>

<form method="post" class="pb-4">
    <fieldset>
        <legend>Identifiants</legend>
        <div class="form-group"><label for="pseudo">Pseudo</label>
            <input id="pseudo" name="pseudo" type="text" class="form-control 
        <?php
        
        echo (isset($_POST['newmember']) && !empty($_POST) &&
            (empty($_POST['pseudo']) ||
                iconv_strlen(trim($_POST['pseudo'])) > 20 ||
                iconv_strlen(trim($_POST['pseudo'])) < 2)) ? 'is-invalid' : '' ?>" value="<?php echo $_POST['pseudo'] ?? '' ?>">

            <div class="invalid-feedback">
                Merci de renseigner le pseudo. (2-20 caractères)
            </div>

        </div>
        <!-- Mot de passe -->
        <div class="form-group">
            <label for="mdp">Mot de passe</label>
            <input id="mdp" name="mdp" type="password" class="form-control 
        <?php
       
       // Regex Mot de passe
        echo (isset($_POST['newmember']) && !empty($_POST) && !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@])[\w$!\-\@]{8,20}$#', $_POST['mdp'])) ? 'is-invalid' : ''
        ?>">
            <div class="invalid-feedback">
                Veuillez saisir un mot de passe compris entre 8 et 20 caractères contenant au moins 1 majuscule, 1 minucscule, 1 chiffre, 1 carctère spécial ($ ! _ - @)
            </div>
        </div>
        <!-- Email -->
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="text" class="form-control <?php echo (isset($_POST['newmember']) && !empty($_POST) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" value="<?php echo $_POST['email'] ?? '' ?>">
            <div class="invalid-feedback">
                Merci de saisir une adresse mail valide
            </div>
        </div>
    </fieldset>
    <fieldset>
        <legend>Coordonnées</legend>
        <div class="form-row">
            <div class="form-group col-2">
                <label for="civilite">Civilité</label>
                <select name="civilite" id="civilite" class="form-control">
                    <option value="m">M</option>
                    <option value="f" <?php echo (isset($_POST['newmember']) && !empty($_POST) && $_POST['civilite'] == 'f') ? 'selected' : '' ?>>Mme</option>
                </select>
            </div>
            <div class="form-group col">
                <label for="nom">Nom</label>
                <input id="nom" name="nom" type="text" class="form-control" value="<?php echo $_POST['nom'] ?? '' ?>">
            </div>
            <div class="form-group col">
                <label for="prenom">Prénom</label>
                <input id="prenom" name="prenom" type="text" class="form-control" value="<?php echo $_POST['prenom'] ?? '' ?>">
            </div>
        </div>
    </fieldset>
    <button type="submit" name="newmember" class="btn btn-primary">Enregistrer</button>
</form>

<?php
require_once('../inc/footer.php');
