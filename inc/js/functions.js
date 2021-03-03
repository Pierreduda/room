


document.addEventListener('DOMContentLoaded', function () {

    let confirmations = document.querySelectorAll('.confirm');

    for (let i = 0; i < confirmations.length; i++) {
        confirmations[i].onclick = function () {
            if (window.location.href.indexOf("gestion_produits") > -1) {
                return (confirm('Êtes-vous sûr(e) de vouloir supprimer ce produit ?'));
            }
            if (window.location.href.indexOf("gestion_membres") > -1)
                return (confirm('Êtes-vous sûr(e) de vouloir supprimer ce membre ?'));
        }
    }

    if (document.getElementById('modaleConfirm')) {
        $('#modaleConfirm').modal('show');
    }

    if (document.getElementById('photo')) {

        document.getElementById('photo').addEventListener('change', function (e) {

            let fichier = e.target.files;
            let reader = new FileReader();
            reader.readAsDataURL(fichier[0]);

            reader.onload = function (event) {
                document.getElementById('placeholder').setAttribute('src', event.target.result);
                document.getElementById('placeholder').setAttribute('alt', fichier[0].name);
            }
        })
    }

    if($(".datepickerjquery")){
        $(function () {
            $(".datepickerjquery").datepicker();
        });
    }
    



})