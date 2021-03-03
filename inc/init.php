<?php

date_default_timezone_set('Europe/Paris');

session_start();

try {
    $pdo = new PDO(
        'mysql:host=localhost;charset=utf8;dbname=room', 
        'root', 
        'root', 
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        )
    );
} catch (PDOException $e) {
    echo $e->getMessage() . ' <br> ' . $e->getFile() . '<br>Ligne:' . $e->getLine() . '<br>';
    die("Site indisponible. Contactez l'administrateur");
}


//Constante de site
define('URL', '/ifocop/projet_php_pierre_duda/');

//inclusion du fichier de functions 
require_once('functions.php');