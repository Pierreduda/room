<?php
require_once('inc/init.php');

$titre = "Accueil";

//Affichage de la fiche
if (!empty($_GET['id_produit']) && is_numeric($_GET['id_produit'])) {
    $produit = execRequete("SELECT * FROM produit INNER JOIN salle on produit.id_salle = salle.id_salle WHERE id_produit = :id_produit", array(
        'id_produit' => $_GET['id_produit']
    ));
    if ($produit->rowCount() == 0) {
        $errors[] = 'Référence inexistante <a href="' . URL . '">Revenir à la boutique</a>';
    } else {
        $infos = $produit->fetch();
        $titre .= ': ' . $infos['titre'];
    }
} else {
    header('location:' . URL);
    exit();
}

if (isset($_POST['commentaire']) && !empty($_POST['commentaire'])) {
    execRequete("INSERT INTO avis VALUES (NULL, :id_membre, :id_salle, :commentaire, :note, NOW())", array(
        'id_membre' => $_SESSION['membre']['id_membre'],
        'id_salle' => $infos['id_salle'],
        'commentaire' => $_POST['commentaire'],
        'note' => $_POST['note']
    ));
}

//affichage des commentaires
$commentaires = execRequete("SELECT * FROM avis a
INNER JOIN salle s
INNER JOIN membre m
WHERE m.id_membre = a.id_membre AND s.id_salle = a.id_salle AND s.id_salle = :id_salle ", array(
    'id_salle' => $infos['id_salle']
));

require_once('inc/header.php');

if (!empty($errors)) : ?>
    <div class="alert alert-danger"><?php echo implode('<br>', $errors) ?></div>
<?php endif; ?>
<section>
    <div class="row mb-4">
        <div class="col">
            <?php
            if (!empty($infos)) : ?>
                <h1 class="page-header text-left"><?php echo $infos['titre'] ?></h1>
                <div class="row">
                    <div class="col-md-7">
                        <a href="<?php echo URL . 'photos/' . $infos['photo'] ?>" data-lightbox="<?php echo URL . 'photos/' . $infos['photo'] ?>"><img src="<?php echo URL . 'photos/' . $infos['photo'] ?>" alt="<?php echo $info['titre'] ?>" class="img-fluid"></a>
                    </div>
                    <div class="col-md-5">

                        <?php if ($infos['etat'] == 'libre') :

                            if (!isConnected()) { ?>
                                <div class="form-row">
                                    <div class="form_group col-10">
                                        <form action="connexion.php" method='get'>
                                            <input type="hidden" name="id_produit" value="<?php echo $infos['id_produit'] ?>">
                                            <button type="submit" name="connexion" class="btn btn-primary">Connexion</button>
                                        </form>
                                    </div>
                                </div>
                            <?php
                            } else { ?>
                                <form action="compte.php" method="POST" class="mb-3">
                                    <input type="hidden" name="id_produit" value="<?php echo $infos['id_produit'] ?>">
                                    <div class="form-row">
                                        <div class="form_group col-10">
                                            <button type="submit" name="reserver" class="btn btn-primary">Réserver</button>
                                        </div>

                                    </div>
                                </form>
                                <p class="text-success">Salle disponible</p>
                            <?php } ?>
                        <?php else : ?>
                            <p class="alert alert-warning">Cette salle n'est plus disponible pour cette période</p>
                        <?php endif; ?>
                        <h2>Description</h2>
                        <div class="description">
                            <p><?php echo $infos['description'] ?></p>
                        </div>
                        <h2>Détails</h2>
                        <ul>
                            <li>Catégorie : <?php echo $infos['categorie'] ?></li>
                            <li class="mb-3">Capacite : <?php echo $infos['capacite'] ?></li>
                            <li>Date d'arrivée : <?php echo date('d/m/Y H:i', strtotime($infos['date_arrivee'])) ?></li>
                            <li class="mb-3">Date de Départ : <?php echo date('d/m/Y H:i', strtotime($infos['date_depart'])) ?></li>
                            <li>Adresse : <?php echo $infos['adresse'] ?></li>
                            <li>Code Postal : <?php echo $infos['cp'] ?></li>
                            <li>Ville : <?php echo $infos['ville'] ?></li>
                            <li>Pays : <?php echo $infos['pays'] ?></li>
                        </ul>
                        <p class="lead">Prix : <?php echo number_format($infos['prix'], 2, ',', '&nbsp;') ?>&euro;</p>

                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>
</section>
<!-- Section commentaire -->
<section>
    <div class="row">
        <div class="col-9">
            <?php
            if ($commentaires->rowCount() == 0) {
                echo '<div class="alert alert-info"> Aucun avis n\'a encore été laissé sur cet salle </div>';
            } else {
                while ($commentaire = $commentaires->fetch()) {
            ?>
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-4"><?php echo $commentaire['pseudo'] ?>
                                <?php
                                switch ($commentaire['note']) {
                                    case '5':
                                        echo '&#9733; &#9733; &#9733; &#9733; &#9733;';
                                        break;
                                    case '4':
                                        echo '&#9733; &#9733; &#9733; &#9733;';
                                        break;
                                    case '3':
                                        echo '&#9733; &#9733; &#9733;';
                                        break;
                                    case '2':
                                        echo '&#9733; &#9733;';
                                        break;
                                    case '1':
                                        echo '&#9733;';
                                        break;
                                }
                                ?></h5>

                        </div>
                        <div class="col-12">
                            <p>
                                <?php echo $commentaire['commentaire'] ?>
                            </p>
                        </div>
                        <div class="col-12">
                            <p>
                                Publié le :
                                <?php echo date('d/m/Y H:i', strtotime($commentaire['date_enregistrement'])) ?>
                            </p>
                        </div>
                    </div>
                <?php
                }
            }

            if (isConnected()) {
                ?>
                <hr>
                <form method="post" class="form-group">
                    <h5 class="mb-4">Partagez votre expérience</h5>
                    <select class="form-group" name="note">
                        <option value="5"> &#9733; &#9733; &#9733; &#9733; &#9733; </option>
                        <option value="4"> &#9733; &#9733; &#9733; &#9733; </option>
                        <option value="3"> &#9733; &#9733; &#9733; </option>
                        <option value="2"> &#9733; &#9733; </option>
                        <option value="1"> &#9733; </option>
                    </select>
                    <textarea name="commentaire" class="form-control mb-2"></textarea>
                    <button type="submit" name="commentaire_submit" class="btn btn-primary">Envoyer</button>
                </form>
            <?php
            } else {
            ?>
                <div class="form-row">
                    <div class="form_group col-10">
                        <form action="connexion.php">
                            <button type="submit" name="connexion" class="btn btn-primary">Connexion</button>
                        </form>
                    </div>
                </div>
            <?php
            } ?>
        </div>
    </div>
</section>

<?php
require_once('inc/footer.php');
