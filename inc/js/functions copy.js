

// quand on update la photo d'un produit, on change la photo sur le preview en même temps
document.addEventListener('DOMContentLoaded', function () {

    //controle d'existence
    if (document.getElementById('photo')) {

        document.getElementById('photo').addEventListener('change', function (e) {

            // en Jquery ça donne : 
            // $('#photo').on('change', function (e) {

            let fichier = e.target.files;

            // file reader est un objet qui permet 
            let reader = new FileReader();

            // lit les fichier comme un URL
            reader.readAsDataURL(fichier[0]);




            reader.onload = function (event) {
                //verif
                // console.log(event.target.result);
                // document.getElementById('preview').innerHTML = '<img src="' + event.target.result + '" alt="' + fichier[0].name + '" class="img-fluid vignette" id="placeholder">';

                // on redeclare cet evenement car au changement d'image, on a perdu l'evenement car on a écrasé l'image ayant placeholder comme id.
                // $('#placeholder').on('drop', updatePhoto)
                document.getElementById('placeholder').setAttribute('src', event.target.result);
                document.getElementById('placeholder').setAttribute('alt', fichier[0].name);
            }
        })
    }

    let confirmations = document.querySelectorAll('.confirm');

    for (let i = 0; i < confirmations.length; i++) {
        confirmations[i].onclick = function () {
            //confimr() est une fonction JS prédéfinie qui ouvre une boite de dialogue qui retourne true/false selon l'interaction
            return (confirm('Êtes-vous sûr(e) de vouloir supprimer ce produit ?'));
            // si false, le comportement naturel du lien est bloqué 
        }
    }

    if (document.getElementById('modaleConfirm')) {
        $('#modaleConfirm').modal('show');
        // document.getElementById('modaleConfirm').style.display = 'block';

    }

    let lignes = document.querySelectorAll('#tabcommandes tr[data-idcmd]')

    const URL = 'http://localhost/ifocop/boutique/';

    for (let i = 0; i < lignes.length; i++) {
        console.log(lignes[i].dataset);
        lignes[i].style.cursor = 'pointer';
        lignes[i].addEventListener('click', function () {
            // redirection JS
            window.location.href = URL + 'admin/gestion_commandes.php?action=details&id_commande=' + this.dataset.idcmd;
        })
    }

    let selectetats = document.querySelectorAll('#tabcommandes tr[data-idcmd] td select');
    for (let i = 0; i < selectetats.length; i++) {
        selectetats[i].addEventListener('click', function (e) {
            // e représente l'evenement click 
            // Je ne propage pas l'événement click sur le parent tr de cette cellule afin de ne pas changer la commande affichée alors que je change le statut d'une commande
            e.stopPropagation();
        })
    }

    if ($('#bandeaucookies').length > 0) {

        $('#bandeaucookies').animate({
            bottom: 0
        }, 1000)
    }
    if ($('#confirmcookies').length > 0) {
        $('#confirmcookies').on('click', function (e) {
            e.preventDefault; // neutralise le comportement naturel du clic sur un lien

            let maintenant = new Date();//temps actuel en GMT 
            let expiration = new Date(maintenant.getTime() + 30 * 24 * 3600 * 1000); // getTime() traduit le GMT en millisecondes et 30jours(1000 pour les millisecondes)

            // il faut retraduire les millisecondes en GMT pour le paramètre d'expiration du cookie
            document.cookie = "acceptcookies= true; expires=" + expiration.toGMTString() + "; path=/"

            $('#bandeaucookies').animate({
                bottom: '-70px'
            }, 1000);
        });
    }

    if ($('#placeholder').length > 0) {

        $('html')
            // on empeche le comportement par defaut du navigateur qui consisterait à ouvrir l'image dans le navigateur
            .on('dragover', function (e) {
                e.stopPropagation();// empeche que le parent de l'élément n'ai le comportement du dragover
                e.preventDefault();
                $('#placeholder').css('border', '5px dashed orange');
            })
            .on('drop', function (e) {
                e.stopPropagation();
                e.preventDefault();
                $('#placeholder').css('border', '');

            })
            .on('dragleave', function (e) {
                //lorsqu'on quitte notre page on s'assure qu'il n'y ait pas de coordonnées du coté horizontal ou vertical de l'événement dragleave (en gros si on ne détecte pas le curseur faisant un dragover dans le cadre de la page on réinitialise la bordure)
                if (e.originalEvent.pageX == 0 || e.originalEvent.pageY == 0) {
                    $('#placeholder').css('border', '');
                }

            });

        $('#placeholder').on('drop', updatePhoto);


    }

    function updatePhoto(e) {

        $('#placeholder').css('border', '');

        //file est un objet, une liste de photos
        let fichier = e.originalEvent.dataTransfer.files;

        //on alimente la propriété files de la photo
        $('#photo')[0].files = fichier;

        let evenement = new Event('change');
        document.getElementById('photo').dispatchEvent(evenement); // déclencher l'addeventlistener(change) déclaré plus


    }
})