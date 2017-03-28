$(document).ready(function () {
    //alert('Hello World!');
});

function luoInput(parentId, inputType, inputName, placeholder) {
    var input = document.createElement('input');
    input.type = inputType;
    input.name = inputName;
    input.placeholder = placeholder;
    document.getElementById(parentId).appendChild(input);
}
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