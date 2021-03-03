<?php
require_once('inc/init.php');

$titre = "Inscription";

if (!empty($_POST)) {

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

        //autoconnexion au compte après inscription
        $_SESSION['membre'] = getMembreByPseudo($_POST['pseudo'])->fetch();

        header("location:" . URL . 'index.php');
        exit();
    }
}

require_once('inc/header.php');
?>

<h1 class="mt-2">Inscription</h1>
<hr>

<?php // message d'erreur pour l'utilisateur pour afficher les erreurs
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
            // trim() retire les espaces en début et fin de chaîne
            echo (!empty($_POST) &&
                (empty($_POST['pseudo']) ||
                    iconv_strlen(trim($_POST['pseudo'])) > 20 ||
                    iconv_strlen(trim($_POST['pseudo'])) < 2)) ? 'is-invalid' : '' ?>" value="<?php echo $_POST['pseudo'] ?? '' ?>">

            <div class="invalid-feedback">
                Merci de renseigner le pseudo. (2-20 caractères)
            </div>
            <!-- ternaire contracté -->
        </div>
        <div class="form-group">
            <label for="mdp">Mot de passe</label>
            <input id="mdp" name="mdp" type="password" class="form-control 
            <?php

            // preg_match vérifie le match avec le regex
            echo (!empty($_POST) && !preg_match('#^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[$!\-\_\@])[\w$!\-\@]{8,20}$#', $_POST['mdp'])) ? 'is-invalid' : ''
            ?>">
            <div class="invalid-feedback">
                Veuillez saisir un mot de passe compris entre 8 et 20 caractères contenant au moins 1 majuscule, 1 minucscule, 1 chiffre, 1 carctère spécial ($ ! _ - @)
            </div>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="text" class="form-control <?php echo (!empty($_POST) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" value="<?php echo $_POST['email'] ?? '' ?>">
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
                    <option value="f" <?php echo (!empty($_POST) && $_POST['civilite'] == 'f') ? 'selected' : '' ?>>Mme</option>
                </select>
            </div>
            <div class="form-group col">
                <label for="nom">Nom</label>
                <input id="nom" name="nom" type="text" class="form-control" value="<?php echo $_POST['email'] ?? '' ?>">
            </div>
            <div class="form-group col">
                <label for="prenom">Prénom</label>
                <input id="prenom" name="prenom" type="text" class="form-control" value="<?php echo $_POST['prenom'] ?? '' ?>">
            </div>
        </div>
    </fieldset>

    <button type="submit" class="btn btn-primary">S'inscrire</button>
</form>


<?php
require_once('inc/footer.php');
