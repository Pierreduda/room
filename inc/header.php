<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room | <?php echo $titre ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" integrity="sha384-B0vP5xmATw1+K9KRQjQERJvTumQW0nPEzvF6L/Z6nronJ3oUOFUFpCjEUQouq2+l" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css" />
    <link href="<?php echo URL ?>/node_modules/lightbox2/src/css/lightbox.css" rel="stylesheet" />
    <link rel="stylesheet" href="<?php echo URL ?>/node_modules/vanillajs-datepicker/dist/css/datepicker-foundation.min.css">
    <link rel="stylesheet" href="<?php echo URL ?>inc/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpJ8jgGtD7zP9yug3goQfGII0yAns" crossorigin="anonymous"></script>
    <script src="<?php echo URL ?>inc/js/functions.js"></script>



</head>

<body>
    <header>
        <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
            <a class="navbar-brand" href="<?php echo URL ?>">Room</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item <?php echo ($titre == 'Accueil') ? 'active' : '' ?>">
                        <a class="nav-link " href="<?php echo URL ?>">Nos salles<span class="sr-only">(current)</span></a>
                    </li>
                    <!-- Visiteurs -->
                    <?php if (!isConnected()) : ?>
                        <li class="nav-item <?php echo ($titre == 'Inscription') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>inscription.php">Inscription</a>
                        </li>
                        <li class="nav-item <?php echo ($titre == 'Connexion') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>connexion.php">Connexion</a>
                        </li>
                        <li class="nav-item <?php echo ($titre == 'Contact') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>contact.php">Contact</a>
                        </li>
                    <?php endif; ?>
                    <!-- Membre connecté -->
                    <?php if (isConnected()) : ?>
                        <li class="nav-item <?php echo ($titre == 'Compte') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?php echo URL ?>compte.php">Mon compte</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo URL ?>connexion.php?action=deconnexion">Déconnexion</a>
                            <!-- cette page permet aussi la déconnexion gracde au action=deconnexion -->
                        </li>
                    <?php endif; ?>
                    <!-- Administrateur -->
                    <?php if (isAdmin() || isSuperAdmin()) : ?>
                        <li class="nav-item dropdown">
                            <a id="menuAdmin" class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">Admin</a>
                            <!-- cette page permet aussi la déconnexion gracde au action=deconnexion -->
                            <div class="dropdown-menu" aria-labelledby="menuAdmin">
                                <a href="<?php echo URL ?>admin/gestion_salles.php" class="dropdown-item">Gestion des salles</a>
                                <a href="<?php echo URL ?>admin/gestion_produits.php" class="dropdown-item">Gestion des produits</a>
                                <a href="<?php echo URL ?>admin/gestion_membres.php" class="dropdown-item">Gestion des membres</a>
                                <a href="<?php echo URL ?>admin/gestion_commandes.php" class="dropdown-item">Gestion des commandes</a>
                                <a href="<?php echo URL ?>admin/gestion_avis.php" class="dropdown-item">Gestion des avis</a>
                                <a href="<?php echo URL ?>admin/statistiques.php" class="dropdown-item">Statistiques</a>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">