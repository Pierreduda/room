<?php
require_once('init.php');
// récupération du post 
$filtres = $_POST;
$whereclause = '';
$args = array();
$i = 0;
// on extrait les infos du post pour construire la clause WHERE et ses paramètres
if (!empty($filtres)) {
    count($filtres);
    foreach ($filtres as $key => $values) {
            switch ($key) {
                case 'date_arrivee':
                    $whereclause .= "AND $key >= :$key ";
                    break;
                case 'date_depart':
                    $whereclause .= "AND $key <= :$key ";
                    break;
                case 'capacite':
                    $whereclause .= "AND $key >= :$key ";
                    break;
                case 'prix':
                    $whereclause .= "AND $key <= :$key ";
                    break;
                default:
                    $whereclause .= "AND $key = :$key ";
                    break;
            }
        foreach ($values as $value) {
            $args[$key] = $value;
        }
        $i++;
    }
}

$produits = execRequete("SELECT * 
FROM produit p
INNER JOIN salle s ON p.id_salle = s.id_salle 
WHERE date_arrivee > CURDATE() AND etat = 'libre' $whereclause", $args);
?>
<?php
if ($produits->rowCount() == 0 && !empty($where)) { ?>
    <div class="alert alert-info">Aucun produit ne correspond à votre recherche.</div>
<?php
} elseif ($produits->rowCount() == 0) {
?>
    <div class="alert alert-info">Aucun produit ne correspond à votre recherche.</div>
<?php
} else {
?>
    <div class="row">
        <?php
        while ($produit = $produits->fetch()) :
        ?>  
            <div class="col-md-6 p-1">
                <div class="border">
                    <div class="thumbnail">
                        <a href="fiche.php?id_produit=<?php echo $produit['id_produit'] ?>">
                            <img src="<?php echo URL . 'photos/' . $produit['photo'] ?>" alt="<?php echo $produit['titre'] ?>" class="img-fluid">
                        </a>
                    </div>
                    <div class="caption">
                        <h4 class="float-right"><?php echo number_format($produit['prix'], 2, ',', '&nbsp;') ?>&euro;</h4>
                        <h4><a href="fiche.php?id_produit=<?php echo $produit['id_produit'] ?>"><?php echo $produit['titre'] ?></a></h4>
                        <h5 class="float-right"><?php echo date('d/m/Y H:i', strtotime($produit['date_depart'])) ?></h5>
                        <h5 ><?php echo date('d/m/Y H:i', strtotime($produit['date_arrivee'])) ?></h5>
                    </div>
                </div>
            </div>
        <?php
        endwhile;
        ?>
    </div>
<?php
}
?>