<?php

require_once('inc/init.php');



if (isset($_GET['id_produit'])) {
    $_SESSION['id_produit'] = $_GET['id_produit'];
}

//Déconnexion
if (isset($_GET['action']) && $_GET['action'] == 'deconnexion') {

    //destruction de la variable membre
    unset($_SESSION['membre']);
    header('location:' . URL);
    exit();
}

// si le formulaire est posté 
if (!empty($_POST)) {
    if (empty($_POST['pseudo']) || empty($_POST['mdp'])) {
        $errors[] = 'Merci de remplir tous les champs';
    }

    if (empty($errors)) {
        if ($membre = getMembreByPseudo($_POST['pseudo'])) {
            //Controle le mdp
            $infos = $membre->fetch();
            if (password_verify($_POST['mdp'], $infos['mdp'])) {
                // OK 
                $_SESSION['membre'] = $infos;

                // si l'utilisateur consultait un produit, il est redirigé sur la fiche pour pouvoir le réserver
                if (isset($_SESSION['id_produit'])) {
                    header('location:' . URL . 'fiche.php?id_produit=' . $_SESSION['id_produit']);
                    exit();
                }
                header('location:' . URL . 'compte.php');
                exit();
            } else {
                $errors[] = 'Erreur sur les identifiants';
            }
        } else {
            $errors[] = 'Erreur sur les identifiants';
        }
    }
}


$titre = 'Connexion';
require_once('inc/header.php');

?>
<h1 class="mt-2">Connexion</h1>
<hr>

<?php if (!empty($errors)) : ?>
    <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
<?php endif; ?>

<form method="post" class="pb-4">
    <div class="form-group">
        <label for="pseudo">Pseudo</label>
        <input type="text" class="form-control <?php echo (!empty($_POST) && empty(trim($_POST['pseudo']))) ? 'is-invalid' : '' ?>" id="pseudo" name="pseudo" value="<?php echo $_POST['pseudo'] ?? '' ?>">
        <div class="invalid-feedback">
            Merci de remplir ce champ
        </div>
    </div>
    <div class="form-group">
        <label for="mdp">Mot de passe</label>
        <input type="password" class="form-control <?php echo (!empty($_POST) && empty(trim($_POST['mdp']))) ? 'is-invalid' : '' ?>" id="mdp" name="mdp">
        <div class="invalid-feedback">
            Merci d'indiquer votre mot de passe
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Se connecter</button>
</form>

<?php
require_once('inc/footer.php');
