<?php

require_once('../inc/init.php');

$titre = "Statistiques";

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

//Salles les mieux notées 
$acclamedrooms = execRequete("SELECT titre, round(AVG(note), 1) AS note
FROM salle s
INNER JOIN avis a 
WHERE s.id_salle = a.id_salle
GROUP BY titre DESC LIMIT 5");

//Salles les commandées 
$bestrooms = execRequete("SELECT titre, count(c.id_membre) AS nbrmembre
FROM commande c
INNER JOIN salle s
INNER JOIN membre m 
INNER JOIN produit p
WHERE s.id_salle = p.id_salle AND p.id_produit = c.id_produit AND m.id_membre = c.id_membre 
GROUP BY titre ORDER BY nbrmembre ASC LIMIT 5");

//Membre qui commande le plus
$moreoftenmembers = execRequete("SELECT pseudo, email, count(c.id_commande) AS nbrcommande
FROM commande c
INNER JOIN membre m 
WHERE m.id_membre = c.id_membre 
GROUP BY pseudo, email ORDER BY nbrcommande DESC LIMIT 5");

//Membre qui commande le plus cher 
$bestmembers = execRequete("SELECT pseudo, email, round(AVG(p.prix),0) AS sommecommande
FROM commande c
INNER JOIN salle s
INNER JOIN membre m 
INNER JOIN produit p
WHERE s.id_salle = p.id_salle AND p.id_produit = c.id_produit AND m.id_membre = c.id_membre 
GROUP BY pseudo, email ASC LIMIT 5");

require_once('../inc/header.php');

?>
<h1>Statistiques</h1>
<hr>
<div class="mb-4">
    <h4>Salles les mieux notées</h4>
    <?php
    $i = 1;
    while ($acclamedroom = $acclamedrooms->fetch()) {
        echo $i . " - " . $acclamedroom['titre'] . " - Note moyenne : " . $acclamedroom['note'] . "/5<br>";
        $i++;
    }
    ?>
</div>
<div class="mb-4">
    <h4>Salles les plus commandées</h4>
    <?php
    $i = 1;
    while ($bestroom = $bestrooms->fetch()) {
        echo $i . " - " . $bestroom['titre'] . " - Réservations : " . $bestroom['nbrmembre'] . "<br>";
        $i++;
    }

    ?>
</div>
<div class="mb-4">
    <h4>Membres qui achètent le plus</h4>
    <?php
    $i = 1;
    while ($moreoftenmember = $moreoftenmembers->fetch()) {
        echo $i . " - " . $moreoftenmember['pseudo'] . " - " . $moreoftenmember['email'] . " - Nombre de commandes : " . $moreoftenmember['nbrcommande'] . "<br>";
        $i++;
    }
    ?>
</div>
<div class="mb-4">
    <h4>Membres qui achètent le plus cher</h4>
    <?php
    $i = 1;
    while ($bestmember = $bestmembers->fetch()) {
        echo $i . " - " . $bestmember['pseudo'] . " - " . $bestmember['email'] . " - Panier moyen : " . $bestmember['sommecommande'] . " &euro;<br>";
        $i++;
    }
    ?>
</div>

<?php
require_once('../inc/footer.php');
