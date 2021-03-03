<?php
require_once('inc/init.php');

$titre = "Profil";

// si je ne suis pas connecté je suis redirigé vers la page de connexion
if (!isConnected()) {
    header('location:' . URL . 'connexion.php');
    exit();
}
if (isset($_POST['modifcoord'])) {

    // on retire de post le bouton qui nous a servi à identifier le formulaire sur lequel on travaille
    unset($_POST['modifcoord']);

    // Formulaire de mise à jour des données utilisateur
    $errorscoord = array();
    // controle des champs vides :
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) {
        $errorscoord[] = "Il manque $nb_champs_vides information(s)";
    }

    // controle de l'email :
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errorscoord[] = 'Format de mail invalide';
    }

    if (empty($errorscoord)) {

        $_POST['id_membre'] = $_SESSION['membre']['id_membre'];

        execRequete("UPDATE membre 
        SET civilite = :civilite,
        nom = :nom,
        prenom = :prenom,
        email = :email
        WHERE id_membre = :id_membre", $_POST);

        //on met en même temps les données de session à jour sinon l'utilisateur devra se déco/reconnecter
        $_SESSION['membre']['civilite'] = $_POST['civilite'];
        $_SESSION['membre']['nom'] = $_POST['nom'];
        $_SESSION['membre']['prenom'] = $_POST['prenom'];
        $_SESSION['membre']['email'] = $_POST['email'];

        $_SESSION['message'] = 'Coordonnées mises à jour';
        // Pour éviter qu'un F5 malencontreux qui réinsererait une deuxième fois à la bdd
        header('location:' . $_SERVER['PHP_SELF']);
        exit();
    }
}
if (isset($_POST['modifmdp'])) {
    // Formulaire de changement de mot de passe
    // on retire de post le bouton qui nous a servi à identifier le formulaire sur lequel on travaille
    unset($_POST['modifmdp']);

    // Formulaire de mise à jour des données utilisateur
    $errorsmdp = array();
    // controle des champs vides :
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) {
        $errorsmdp[] = "Il manque $nb_champs_vides information(s)";
    }
    //Controle avant insertion en BDD
    // Verification du mot de passe actuel
    if (!empty($_POST['mdp']) && !password_verify($_POST['mdp'], $_SESSION['membre']['mdp'])) {
        $errorsmdp[] = 'Mot de passe actuel incorrect';
    }
    // verification du la validité du nouveau mot de passe 
    if (!empty($_POST['newmdp']) && !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@])[\w$!\-\@]{8,20}$#', $_POST['newmdp'])) {
        $errorsmdp[] = 'Le nouveau mot de passe doit être compris entre 8 et 20 caractères comprenant au moins 1 majuscule, 1 minuscule, 1 chiffre et 1 caractère spécial : $ ! _ - @';
    }

    if (!empty($_POST['confirm']) && $_POST['newmdp'] !== $_POST['confirm']) {
        $errorsmdp[] = 'La confirmation ne concorde pas avec le nouveau mot de passe';
    }

    if (!empty($_POST['mdp']) && $_POST['newmdp'] === $_POST['mdp']) {
        $errorsmdp[] = "Le nouveau mot de passe doit être différent de l'actuel";
    }

    // si tout est ok, on insert dans la bdd
    if (empty($errorsmdp)) {
        $newmdp = password_hash($_POST['newmdp'], PASSWORD_DEFAULT);
        execRequete("UPDATE membre SET mdp = :mdp WHERE id_membre = :id_membre", array(
            'mdp' => $newmdp,
            'id_membre' => $_SESSION['membre']['id_membre']
        ));
        $_SESSION['membre']['mdp'] = $newmdp;

        $_SESSION['message2'] = 'Mot de passe changé avec succès';

        header('location:' . $_SERVER['PHP_SELF']);
        exit();
    }
}

//Insertion et mise à jour du produit lors de la réservation sur la fiche produit
if (isset($_POST['id_produit'])) {
    execRequete("UPDATE produit SET etat = 'reservation' WHERE id_produit = :id_produit", array(
        'id_produit' => $_POST['id_produit']
    ));
    execRequete("INSERT INTO commande VALUES (NULL, :id_membre, :id_produit, NOW())", array(
        'id_membre' => $_SESSION['membre']['id_membre'],
        'id_produit' => $_POST['id_produit']
    ));
    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

require_once('inc/header.php');
?>
<div class="row">
    <div class="col_md_6">
        <form method="post">
            <h2>Identifiants</h2>
            <p>Pseudo : <strong><?php echo $_SESSION['membre']['pseudo'] ?></strong></p>

            <?php if (!empty($errorscoord)) : ?>
                <div class="alert alert-danger"><?php echo implode('<br>', $errorscoord) ?></div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['message'])) : ?>
                <div class="alert alert-danger"><?php echo $_SESSION['message'] ?></div>
            <?php endif; ?>
            <?php
            unset($_SESSION['message'])
            ?>

            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="text" class="form-control 
                <?php echo (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" value="<?php echo $_POST['email'] ?? $_SESSION['membre']['email'] ?>">
                <div class="invalid-feedback">
                    Merci de saisir une adresse mail valide
                </div>
            </div>
            <h2>Coordonnées</h2>
            <hr>
            <div class="form-row">
                <div class="form-group col-2">
                    <label for="civilite">Civilité</label>
                    <select name="civilite" id="civilite" class="form-control">
                        <option value="m">Mr</option>
                        <option value="f" <?php echo ((!empty($_POST['civilite']) && $_POST['civilite'] == 'f') || $_SESSION['membre']['civilite'] == 'f') ? 'selected' : '' ?>>Mme</option>
                    </select>
                </div>
                <div class="form-group col">
                    <label for="nom">Nom</label>
                    <input id="nom" name="nom" type="text" class="form-control" value="<?php echo $_POST['email'] ?? $_SESSION['membre']['nom'] ?>">
                </div>
                <div class="form-group col">
                    <label for="prenom">Prénom</label>
                    <input id="prenom" name="prenom" type="text" class="form-control" value="<?php echo $_POST['prenom'] ?? $_SESSION['membre']['prenom'] ?>">
                </div>
            </div>

            <button type="submit" name="modifcoord" class="btn btn-primary">Mettre à jour</button>
        </form>
    </div>
    <div class="col_md_6 mx-auto">
        <h2>Changer le mot de passe</h2>
        <hr>

        <?php if (!empty($errorsmdp)) : ?>
            <div class="alert alert-danger"><?php echo implode('<br>', $errorsmdp) ?></div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['message2'])) : ?>
            <div class="alert alert-info"><?php echo $_SESSION['message2'] ?></div>
        <?php endif; ?>
        <?php
        unset($_SESSION['message2'])
        ?>
        <form method="post">
            <div class="form-group">
                <label for="mdp">Mot de passe actuel</label>
                <input id="mdp" name="mdp" type="password" class="form-control">
            </div>
            <div class="form-group">
                <label for="newmdp">Nouveau mot de passe</label>
                <input id="newmdp" name="newmdp" type="password" class="form-control">
            </div>
            <div class="form-group">
                <label for="confirm">Confirmation</label>
                <input id="confirm" name="confirm" type="password" class="form-control">
            </div>
            <button type="submit" name="modifmdp" class="btn btn-primary">Changer le mot de passe</button>
        </form>

    </div>
</div>
<h2 class="mt-4">Vos commandes</h2>
<?php
$commandes = execRequete("SELECT c.date_enregistrement, c.id_commande, titre, photo, date_arrivee, date_depart, prix, capacite, categorie, ville, pays
FROM commande c
INNER JOIN produit p
INNER JOIN salle s
INNER JOIN membre m
WHERE p.id_salle = s.id_salle AND p.id_produit = c.id_produit AND c.id_membre = m.id_membre AND m.id_membre = :id_membre", array(
    'id_membre' => $_SESSION['membre']['id_membre']
));
if ($commandes->rowCount() == 0) {
?>
    <div class="alert alert-info">Il n'y a pas encore de commande enregistrés</div>
<?php
} else {
?>
    <table class="table table-bordered table-striped table-responsive-lg">
        <tr>
            <?php
            //entêtes de colonne
            for ($i = 0; $i < $commandes->columnCount(); $i++) {
                $colonne = $commandes->getColumnMeta($i);
            ?>
                <th><?php
                    switch ($colonne['name']) {
                        case 'id_commande':
                            $colonne['name'] = "N° commande";
                            break;
                        case 'photo':
                            $colonne['name'] = "Aperçu";
                            break;
                        case 'date_arrivee':
                            $colonne['name'] = "Date d'arrivée";
                            break;
                        case 'date_depart':
                            $colonne['name'] = "Date de départ";
                            break;
                        case 'date_enregistrement':
                            $colonne['name'] = "Date de la commande";
                            break;
                        default:
                            $colonne['name'] = $colonne['name'];
                            break;
                    }
                    echo ucfirst($colonne['name']) ?></th>
            <?php
            }
            ?>
        </tr>
        <?php
        // Données 
        while ($ligne = $commandes->fetch()) {
        ?>
            <tr>
                <?php
                foreach ($ligne as $key => $value) {

                    switch ($key) {
                        case 'photo':
                            if ($key == 'photo' && !empty($value)) {
                                $value = '<a href="' . URL . 'photos/' . $ligne['photo'] . '" data-lightbox="' . $ligne['photo'] . '"><img class="img-fluid " src="' . URL . 'photos/' . $ligne['photo'] . '" alt="' . $ligne['titre'] . '"></a>';
                            }
                            break;
                        case 'prix':
                            $value = number_format($value, 2, ',', '&nbsp;') . '&euro;';
                            break;
                        case 'date_arrivee':
                            $value = date('d/m/Y H:i', strtotime($value));
                            break;
                        case 'date_depart':
                            $value = date('d/m/Y H:i', strtotime($value));
                            break;
                        case 'date_enregistrement':
                            $value = date('d/m/Y H:i', strtotime($value));
                            break;
                    }
                ?>
                    <td><?php echo $value ?></td>
                <?php
                }
                ?>
            </tr>
        <?php
        }
        ?>
    </table>
<?php
}
?>

<?php
require_once('inc/footer.php');
