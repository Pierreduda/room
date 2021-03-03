let url = new URL('http://localhost/ifocop/projet_php_pierre_duda/');


document.addEventListener("DOMContentLoaded", function (e) {

    if (document.getElementById('produits')) {

        let filtres = document.getElementsByClassName("filtre");
        let filtre_obj = {};
        for (let i = 0; i < filtres.length; i++) {
            // 1- pour chaque bouton on créer l'evenement
            filtres[i].addEventListener('click', function (e) {

                // 5- Si le bouton contient la class selected, au clique, on l'enlève de l'objet et on  lui retire la class pour qu'il puisse être selectionné à nouveau
                if (filtres[i].classList.contains('selected')) {
                    let obj_name = filtre_obj[filtres[i].name];

                    obj_name.splice(obj_name.indexOf(filtres[i].value), 1);
                    filtres[i].classList.remove('selected');
                } else {
                    // 2- Si l'objet n'existe pas, on l'initialise
                    if (!filtre_obj.hasOwnProperty(filtres[i].name)) {
                        filtre_obj[filtres[i].name] = [];
                    }
                    // 3- l'objet contient le nom d'un filtre dans lequel on insère la valeur
                    filtre_obj[filtres[i].name].push(filtres[i].value)
                    // 4- on ajoute une class qui permettra de déselectionner le filtre
                    filtres[i].classList.add('selected');
                }

                ajax_filtre(filtre_obj);
            });


        }

        if (document.getElementById('capacite')) {
            let filtre_capacite = document.getElementById('capacite');
            filtre_capacite.addEventListener('change', function () {
                if (!filtre_obj.hasOwnProperty(filtre_capacite.name)) {
                    filtre_obj[filtre_capacite.name] = [];
                }
                filtre_obj[filtre_capacite.name].push(filtre_capacite.value);
                ajax_filtre(filtre_obj);
            })
        }

        if (document.getElementById('prix')) {
            let filtre_prix = document.getElementById('prix');
            filtre_prix.addEventListener('mouseup', function () {
                document.getElementById('prix-select').innerText = filtre_prix.value + '€'
                if (!filtre_obj.hasOwnProperty(filtre_prix.name)) {
                    filtre_obj[filtre_prix.name] = [];
                }
                filtre_obj[filtre_prix.name].push(filtre_prix.value);
                ajax_filtre(filtre_obj);

            })
        }

        if (document.getElementsByClassName("datepicker-cell")) {
            let date_cell = document.getElementsByClassName("datepicker-cell");
            let date_arrivee_input = document.getElementsByName("date_arrivee")[0];
            let date_depart_input = document.getElementsByName("date_depart")[0];
            for (let j = 0; j < date_cell.length; j++) {
                date_cell[j].addEventListener('click', function () {
                    function waitforvalue() {
                        let date_arrivee = dayjs(date_arrivee_input.value, 'DD/MM/YYYY').format('YYYY-MM-DD HH:mm:ss');
                        let date_depart = dayjs(date_depart_input.value, 'DD/MM/YYYY').format('YYYY-MM-DD HH:mm:ss');
                        console.log(date_arrivee);
                        console.log(date_depart);

                        if (!filtre_obj.hasOwnProperty(date_arrivee_input.name)) {
                            filtre_obj[date_arrivee_input.name] = [];
                        }
                        if (!filtre_obj.hasOwnProperty(date_depart_input.name)) {
                            filtre_obj[date_depart_input.name] = [];
                        }
                        filtre_obj[date_arrivee_input.name].push(date_arrivee);
                        filtre_obj[date_depart_input.name].push(date_depart);

                        ajax_filtre(filtre_obj);

                    }
                    setTimeout(waitforvalue, 100);

                })

            }

        }



        function ajax_filtre(filtre_obj) {
            $.ajax({
                type: "POST",
                data: filtre_obj,
                url: url + 'inc/ajax.php',
                success: function (msg) {
                    $('#produits').html(msg);
                }
            });
        }


    }
})