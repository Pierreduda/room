<?php
require_once('inc/init.php');

$titre = "Accueil";

require_once('inc/header.php');

$categories = execRequete("SELECT DISTINCT categorie FROM 
produit 
INNER JOIN salle ON produit.id_salle = salle.id_salle");

$capacites = execRequete("SELECT DISTINCT capacite FROM 
produit 
INNER JOIN salle ON produit.id_salle = salle.id_salle ORDER BY capacite ASC");

$villes = execRequete("SELECT DISTINCT ville FROM 
produit 
INNER JOIN salle ON produit.id_salle = salle.id_salle");

$prixlist1 = execRequete("SELECT DISTINCT prix FROM 
produit 
INNER JOIN salle ON produit.id_salle = salle.id_salle ORDER BY prix ASC");
$prixmin = $prixlist1->fetch();

$prixlist2 = execRequete("SELECT DISTINCT prix FROM 
produit 
INNER JOIN salle ON produit.id_salle = salle.id_salle ORDER BY prix DESC");
$prixmax = $prixlist2->fetch();
?>

<div class="row">
    <div class="col_md-3">
        <p class="lead pt-3">Catégorie</p>
        <div class="list-group mb-4">

            <a href="<?php echo URL ?>" class="list-group-item">Toutes</a>
            <?php

            while ($categorie = $categories->fetch()) {
            ?>
                <button class="btn btn-primary mb-1 filtre" name="categorie" value="<?php echo $categorie['categorie'] ?>"><?php echo $categorie['categorie'] ?></button>

            <?php
            }
            ?>
        </div>
        <p class="lead pt-3">Ville</p>
        <div class="list-group">
            <a href="<?php echo URL ?>" class="list-group-item">Toutes</a>
            <?php
            while ($ville = $villes->fetch()) {
            ?>
                <button class="btn btn-primary mb-1 filtre" name="ville" value="<?php echo $ville['ville'] ?>"><?php echo $ville['ville'] ?></button>
            <?php
            }
            ?>
        </div>
        <p class="lead pt-3">Capacité</p>

        <div class="list-group">
            <select name="capacite" id="capacite">
                <?php
                while ($capacite = $capacites->fetch()) {
                ?>
                    <option>
                        <?php
                        echo $capacite['capacite'];
                        ?>
                    </option>
                <?php
                }
                ?>
            </select>
            <?php
            ?>
        </div>

        <p class="lead pt-3">Prix</p>

        <div class="list-group">
            <label for="prix">(maximum)</label>
            <input type="range" name="prix" id="prix" step="50" min="<?php echo $prixmin['prix'] ?>" max="<?php echo $prixmax['prix'] ?>">
            <p id="prix-select"></p>
            <?php
            ?>
        </div>

        <p class="lead pt-3">Période</p>
        <div id="sandbox" class="list-group">
            <div class="d-flex flex-column date input-daterange">
                <div class="col-md-12 p-0">
                    <div class="form-group">
                        <input type="text" name="date_arrivee" class="form-control datepicker-input">
                        <span class="form-label">Date d'arrivée</span>
                    </div>
                </div>

                <div class="col-md-12 p-0">
                    <div class="form-group">
                        <input type="text" name="date_depart" class="form-control datepicker-input">
                        <span class="form-label">Date de départ</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
    <div id="produits" class="col-md-9 ml-4">
        <?php require_once('./inc/ajax.php') ?>
    </div>
</div>

<?php
require_once('inc/footer.php');
