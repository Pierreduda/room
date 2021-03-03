<?php
require_once('inc/init.php');

$titre = "Contact";

if (!empty($_POST)) {

    $errors = array();
    // controle des champs vides :
    $nb_champs_vides = 0;
    foreach ($_POST as $key => $value) {
        $_POST[$key] = trim($value);
        if (empty($_POST[$key])) $nb_champs_vides++;
    }
    if ($nb_champs_vides > 0) {
        $errors[] = "Il manque $nb_champs_vides information(s)";
    }

    // controle de l'email :
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format de mail invalide';
    } else {
        $useremail = htmlspecialchars($_POST['email']);
        $userlastname = htmlspecialchars($_POST['nom']);
        $userfirstname = htmlspecialchars($_POST['prenom']);
        $usermessage = nl2br(htmlspecialchars($_POST['message']));
        $adminemail = 'contact@theroom.com';
    }

    if (empty($errors)) {
        $header = "MIME-Version: 1.0\r\n";
        $header .= 'From:"theroom.fr"<no-reply@theroom.fr>' . "\n";
        $header .= 'Content-Type:text/html; charset="utf-8"' . "\n";
        $header .= 'Content-Transfer-Encoding: 8bit';
        $message = '
                <html>
                <head>
                <title>Email de contact - The Room</title>
                <meta charset="utf-8" />
                </head>
                <body>
                <font color="#303030";>
                    <div align="center">
                    <table width="600px">
                        <tr>
                        <td>
                            
                            <div align="center">Bonjour <b>' . $userlastname . '</b>,</div>
                            Nous vous remerçions pour votre message ainsi que pour l\'attention que vous portez à The Room, nous vous répondrons dans les plus brefs délais à l\'adresse que vous nous avez communiqué : <b>' . $useremail . '</b>
                            A bientôt sur <a href="theroom.fr">The Room</a> !
                            
                        </td>
                        </tr>
                        <tr>
                        <td align="center">
                            <font size="2">
                            Ceci est un email automatique, merci de ne pas y répondre
                            </font>
                        </td>
                        </tr>
                    </table>
                    </div>
                </font>
                </body>
                </html>
                ';
        mail($useremail, "Merci pour votre message - theroom.fr", $message, $header);

        $message = '
                    <html>
                    <head>
                    <title>Email de contact - The Room</title>
                    <meta charset="utf-8" />
                    </head>
                    <body>
                    <font color="#303030";>
                        <div align="center">
                        <table width="600px">
                            <tr>
                            <td>
                                
                                <div align="center">Bonjour,</div>
                                Vous avez reçu une demande de contact de la part de Mme/Mr ' . $userlastname . ' ' . $userfirstname . ' : <b>' . $useremail . '</b><br>
                                <div>' . $usermessage . '</div>
                                <br>
                                A bientôt sur <a href="theroom.fr">The Room</a> !
                                
                            </td>
                            </tr>
                            <tr>
                            <td align="center">
                                <font size="2">
                                Ceci est un email automatique, merci de ne pas y répondre
                                </font>
                            </td>
                            </tr>
                        </table>
                        </div>
                    </font>
                    </body>
                    </html>
                    ';
        mail($adminemail, "Demande de contact - theroom.fr", $message, $header);
        header('Location:' .URL);
        exit();
    }
}

require_once('inc/header.php');
?>
<section>
    <h1 class="mt-2">Nous contacter</h1>
    <hr>

    <?php
    if (!empty($errors)) : ?>
        <div class="alert alert-danger">
            <?php echo implode('<br>', $errors) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="form-group">
        <div class="form-row">
            <div class="form-group col">
                <label for="nom">Nom</label>
                <input id="nom" name="nom" type="text" class="form-control" value="<?php echo $_POST['email'] ?? '' ?>">
            </div>
            <div class="form-group col">
                <label for="prenom">Prénom</label>
                <input id="prenom" name="prenom" type="text" class="form-control" value="<?php echo $_POST['prenom'] ?? '' ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input id="email" name="email" type="text" class="form-control 
                <?php echo (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) ? 'is-invalid' : '' ?>" value="<?php echo $_POST['email'] ?? '' ?>">
            <div class="invalid-feedback">
                Merci de saisir une adresse mail valide
            </div>

        </div>
        <label for="message">Votre message</label>
        <textarea name="message" id="message" class="form-control mb-2"></textarea>
        <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
</section>

<?php
require_once('inc/footer.php');
