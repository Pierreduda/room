<?php

require_once('../inc/init.php');

$titre = "Gestion des avis";

if (!isAdmin()) {
    header('location:' . URL . 'connexion.php');
    exit();
}

// Suppression d'un avis 
if (isset($_GET['action']) && $_GET['action'] == 'delete' && !empty($_GET['id_avis']) && is_numeric($_GET['id_avis'])) {

    execRequete("DELETE FROM avis WHERE id_avis = :id_avis", array(
        'id_avis' => $_GET['id_avis']
    ));
    header('location:' . $_SERVER['PHP_SELF']);
    exit();
}

// Affichage des avis
$avislist = execRequete("SELECT id_avis, m.id_membre, m.email, s.id_salle, titre, commentaire, note, a.date_enregistrement FROM avis a
INNER JOIN membre m 
INNER JOIN salle s
WHERE m.id_membre = a.id_membre AND s.id_salle = a.id_salle 
ORDER BY id_avis DESC");

require_once('../inc/header.php');

?>
<h1>Gestion des avis</h1>
<hr>
<table class="table table-bordered table-striped table-responsive-lg">
    <!-- entete -->
    <tr>
        <?php
        for ($i = 0; $i < $avislist->columnCount(); $i++) {
            $colonne = $avislist->getColumnMeta($i);
            if ($colonne['name'] != 'email' && $colonne['name'] != 'titre' && $colonne['name'] != 'date_arrivee' && $colonne['name'] != 'date_depart') {
        ?>
                <th><?php echo ucfirst($colonne['name']) ?></th>
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
    while ($avis = $avislist->fetch()) {
    ?>
        <tr>
            <?php
            foreach ($avis as $key => $value) {
                if ($key != 'email' && $key != 'email' && $key != 'titre' && $key != 'date_arrivee' && $key != 'date_depart') {
            ?>
                    <td>
                        <?php switch ($key) {
                            case 'id_membre':
                                $value = $value . " - " . $avis['email'];
                                echo $value;
                                break;
                            case 'id_salle':
                                $value = $value . " - " . $avis['titre'];
                                echo $value;
                                break;
                            case 'note':
                                for($i = 0; $i < $value; $i++){
                                    ?> 
                                    &#9733;
                                    <?php 
                                }
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
                <a href="?action=delete&id_avis=<?php echo $avis['id_avis'] ?>" class="confirm"><i class="fas fa-trash"></i></a>
            </td>
        <?php
    }
        ?>
        </tr>
</table>

<?php
require_once('../inc/footer.php');
