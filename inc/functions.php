<?php


function isConnected()
{
    return isset($_SESSION['membre']);
}

function isAdmin()
{
    return (isConnected() && $_SESSION['membre']['statut'] >= 1);
}
function isSuperAdmin()
{
    return (isConnected() && $_SESSION['membre']['statut'] == 2);
}



function execRequete($requete, $params = array())
{
    global $pdo;
    $r = $pdo->prepare($requete);
    if (!empty($params)) {
        foreach ($params as $key => $value) {
            $params[$key] = htmlspecialchars($value, ENT_QUOTES);
        }
    }
    $r->execute($params);
    if (!empty($r->errorInfo()[2])) {
        die('Erreur rencontrée, merci de contacter l\'administrateur');
    }
    return $r;
}

function getMembreByPseudo($pseudo)
{
    $resultat = execRequete("SELECT * FROM membre WHERE pseudo = :pseudo", array('pseudo' => $pseudo));
    if ($resultat->rowCount() > 0) {
        return $resultat;
    } else {
        return false;
    }
}