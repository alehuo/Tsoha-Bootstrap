
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

function luoEditSivulleOpetusAikaKentta() {
    var element = $("#opAika").clone();
    element.removeAttr("id");
    element.find('[name*="opetusaikaHuone[]"]').first().prop('disabled', false);
    element.find('[name*="opetusaikaAloitusaika[]"]').first().prop('disabled', false);
    element.find('[name*="opetusaikaKesto[]"]').first().prop('disabled', false);
    element.find('[name*="opetusaikaViikonpaiva[]"]').first().prop('disabled', false);
    element.show();
    element.appendTo("#opetusajat");
}

function luoEditSivulleHarjoitusRyhmaKentta() {
    var element = $("#hryhma").clone();
    element.removeAttr("id");
    element.find('[name*="harjoitusryhmaHuone[]"]').first().prop('disabled', false);
    element.find('[name*="harjoitusryhmaAloitusaika[]"]').first().prop('disabled', false);
    element.find('[name*="harjoitusryhmaKesto[]"]').first().prop('disabled', false);
    element.find('[name*="harjoitusryhmaViikonpaiva[]"]').first().prop('disabled', false);
    element.show();
    element.appendTo("#harjoitusryhmat");
}