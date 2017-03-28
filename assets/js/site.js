$(document).ready(function () {
    function luoOpetusAikaKentta() {
        var element = $("#opAika").clone();
        element.removeAttr("id");
        element.fadeIn(500);
        element.appendTo("#opetusajat");
    }
    function luoHarjoitusRyhmaKentta() {
        var element = $("#harjRyhma").clone();
        element.removeAttr("id");
        element.fadeIn(500);
        element.appendTo("#harjoitusryhmat");
    }
});
