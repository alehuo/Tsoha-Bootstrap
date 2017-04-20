
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
var delay = (function () {
    var timer = 0;
    return function (callback, ms) {
        clearTimeout(timer);
        timer = setTimeout(callback, ms);
    };
})();
$("#searchInput").keyup(function () {
    delay(function () {
        //Tyhjennä hakusivu
        document.getElementById("searchRes").innerHTML = "";
        var input = $("#searchInput").val();
        if (input) {
            $.post("/oodi/searchres", {searchTerm: input}, function (data) {
                $.each(jQuery.parseJSON(data), function (i, obj) {
                    var tableRow = document.createElement("tr");
                    var linkki = document.createElement("a");
                    linkki.href = "/oodi/course/" + obj.id;
                    linkki.innerHTML = obj.nimi;
                    var nimi = document.createElement("td");
                    var vastuuYksikko = document.createElement("td");
                    vastuuYksikko.innerHTML = obj.vastuuyksikko;
                    var aloitusJaLopetus = document.createElement("td");
                    aloitusJaLopetus.innerHTML = moment(obj.aloituspvm*1000).format("DD.MM.YYYY") + " - " + moment(obj.lopetuspvm*1000).format("DD.MM.YYYY");
                    var nopat = document.createElement("td");
                    nopat.innerHTML = obj.nopat;

                    nimi.appendChild(linkki);
                    tableRow.appendChild(nimi);
                    tableRow.appendChild(vastuuYksikko);
                    tableRow.appendChild(aloitusJaLopetus);
                    tableRow.appendChild(nopat);

                    document.getElementById("searchRes").appendChild(tableRow);
                });

            });

        }
    }, 300);
});

$(document).ready(function () {
    $('form.destroy-form').on('submit', function (submit) {
        var confirm_message = $(this).attr('data-confirm');
        if (!confirm(confirm_message)) {
            submit.preventDefault();
        }
    });
});