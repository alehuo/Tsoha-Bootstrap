
function luoOpetusAikaKentta() {
    var element = $("#opAika").clone();
    element.removeAttr("id");
    element.show();
    element.appendTo("#opetusajat");
}
function luoHarjoitusRyhmaKentta() {
    var element = $("#harjRyhma").clone();
    element.removeAttr("id");
    element.show();
    element.appendTo("#harjoitusryhmat");
}

