<?php

require_once('../inc/init.php');

$titre = "Gestion des commandes";

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}


// Suppression d'une commande 
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_commande']) && is_numeric($_GET['id_commande'])) {
    $produitupdates = execRequete("SELECT id_produit 
    FROM commande
    WHERE id_commande = :id_commande
    ", array(
        'id_commande' => $_GET['id_commande']
    ));
    $produitupdate = $produitupdates->fetch();

    execRequete("UPDATE produit SET etat = 'libre' WHERE id_produit = :id_produit", array(
        "id_produit" => $produitupdate['id_produit']
    ));

    execRequete("DELETE FROM commande WHERE id_commande = :id_commande", array(
        'id_commande' => $_GET['id_commande']
    ));
    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

// Affichage des commandes
$commandes = execRequete("SELECT id_commande, m.id_membre, m.email, p.id_produit, s.id_salle, titre, date_arrivee, date_depart, prix, c.date_enregistrement FROM commande c
INNER JOIN membre m 
INNER JOIN salle s
INNER JOIN produit p
WHERE m.id_membre = c.id_membre AND p.id_produit = c.id_produit AND p.id_salle = s.id_salle 
ORDER BY id_commande DESC");

require_once('../inc/header.php');

?>
<h1>Gestion des commandes</h1>
<hr>
<table class="table table-bordered table-striped table-responsive-lg">
    <!-- entete -->
    <tr>
        <?php
        for ($i = 0; $i < $commandes->columnCount(); $i++) {
            $colonne = $commandes->getColumnMeta($i);
            if ($colonne['name'] != 'email' && $colonne['name'] != 'id_salle' && $colonne['name'] != 'titre' && $colonne['name'] != 'date_arrivee' && $colonne['name'] != 'date_depart') {
        ?>
                <th><?php echo ucfirst($colonne['name']) ?></th>
            <?php
            }
        }

        if (isSuperAdmin()) {
            ?>
            <th colspan="2">Actions</th>
        <?php
        }

        ?>
    </tr>


    <?php
    // Données
    while ($commande = $commandes->fetch()) {
    ?>
        <tr>
            <?php
            foreach ($commande as $key => $value) {
                if ($key != 'email' && $key != 'id_salle' && $key != 'titre' && $key != 'date_arrivee' && $key != 'date_depart') {
            ?>
                    <td>
                        <?php switch ($key) {
                            case 'id_membre':
                                $value = $value . " - " . $commande['email'];
                                echo $value;
                                break;
                            case 'id_produit':
                                $value = $value . " - " . $commande['titre'] . "<br>" .  date('d/m/Y', strtotime($commande['date_arrivee'])) . " au " . date('d/m/Y', strtotime($commande['date_depart']));
                                echo $value;
                                break;
                            case 'prix':
                                echo $value . " &euro;";
                                break;
                            case 'date_enregistrement':
                                echo date('d/m/Y H:i', strtotime($value));
                                break;
                            default:
                                echo $value;
                                break;
                        }
                        ?>
                    </td>

            <?php
                }
            } ?>
            <td>
                <a href="?action=delete&id_commande=<?php echo $commande['id_commande'] ?>" class="confirm"><i class="fas fa-trash"></i></a>
            </td>
        <?php
    }
        ?>
        </tr>
</table>



<?php

require_once('../inc/footer.php');
