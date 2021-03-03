
    let url = new URL('http://localhost/ifocop/projet_php_pierre_duda/');

    import Datepicker from '../../node_modules/vanillajs-datepicker/js/Datepicker.js';
    import DateRangePicker from '../../node_modules/vanillajs-datepicker/js/DateRangePicker.js';
    import fr from '../../node_modules/vanillajs-datepicker/js/i18n/locales/fr.js';
    Object.assign(Datepicker.locales, fr);
    
if (document.getElementById('sandbox')) {

    const elem = document.getElementById('sandbox');
    const rangepicker = new DateRangePicker(elem, {
        format: 'dd/mm/yyyy',
        language: 'fr',
        buttonClass: 'btn',
        minDate: Date.now(),
    });
}
