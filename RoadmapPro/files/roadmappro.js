var newProfileCounter = 0;
var newGroupCounter = 0;
var newThresholdCounter = 0;

function addProfileRow(statusValues, statusStrings) {
    var tableId = "profiles";
    var table = document.getElementById(tableId);
    var rows = table.getElementsByTagName("tr").length;
    var tr = table.insertRow(rows);
    var td1 = document.createElement("td");
    var td2 = document.createElement("td");
    var td3 = document.createElement("td");
    var td4 = document.createElement("td");
    var td5 = document.createElement("td");
    var td6 = document.createElement("td");

    var optionstring = '';
    for (var i = 0; i < statusValues.length; i++) {
        var value = statusValues[i];
        var string = statusStrings[i];

        optionstring += '<option value="' + value + '">' + string + '</option>'
    }

    /** name */
    td1.innerHTML = '<input type="text" name="profile-name[]" size="15" maxlength="128" value="">';
    /** status */
    td2.innerHTML = '<select name="new-status-' + newProfileCounter + '[]" multiple="multiple">' + optionstring + '</select>';
    newProfileCounter++;
    /** color */
    td3.innerHTML = '<label><input class="color {pickerFace:4,pickerClosable:true}" type="text" name="profile-color[]" value=""/></label>';
    /** priority */
    td4.innerHTML = '<input type="text" name="profile-prio[]" size="15" maxlength="3" value="">';
    /** effort */
    td5.innerHTML = '<input type="text" name="profile-effort[]" size="15" maxlength="3" value="">';
    /** action */
    td6.innerHTML = '';

    tr.appendChild(td1);
    tr.appendChild(td2);
    tr.appendChild(td3);
    tr.appendChild(td4);
    tr.appendChild(td5);
    tr.appendChild(td6);

    var evt = document.createEvent('Event');
    evt.initEvent('load', false, false);
    window.dispatchEvent(evt);
}

function addGroupRow(profileIds, profileNames) {
    var tableId = "profilegroups";
    var table = document.getElementById(tableId);
    var rows = table.getElementsByTagName("tr").length;
    var tr = table.insertRow(rows);
    var td1 = document.createElement("td");
    var td2 = document.createElement("td");
    var td3 = document.createElement("td");

    var optionstring = '';
    for (var i = 0; i < profileIds.length; i++) {
        var value = profileIds[i];
        var string = profileNames[i];

        optionstring += '<option value="' + value + '">' + string + '</option>'
    }

    /** name */
    td1.innerHTML = '<input type="text" name="group-name[]" size="15" maxlength="128" value="">';
    /** profile */
    td2.innerHTML = '<select name="new-group-profile-' + newGroupCounter + '[]" multiple="multiple">' + optionstring + '</select>';
    newGroupCounter++;
    /** action */
    td3.innerHTML = '';

    tr.appendChild(td1);
    tr.appendChild(td2);
    tr.appendChild(td3);
}

function addThresholdRow() {
    var tableId = "thresholds";
    var table = document.getElementById(tableId);
    var rows = table.getElementsByTagName("tr").length;
    var tr = table.insertRow(rows);
    var td1 = document.createElement("td");
    var td2 = document.createElement("td");
    var td3 = document.createElement("td");
    var td4 = document.createElement("td");
    var td5 = document.createElement("td");

    /** from */
    td1.innerHTML = '<input type="text" name="threshold-from[]" size="15" maxlength="128" value="">';
    /** to */
    td2.innerHTML = '<input type="text" name="threshold-to[]" size="15" maxlength="128" value="">';
    /** unit */
    td3.innerHTML = '<input type="text" name="new-threshold-unit-' + newThresholdCounter + '" size="15" maxlength="128" value="">';
    newThresholdCounter++;
    /** factor */
    td4.innerHTML = '<input type="text" name="threshold-factor[]" size="15" maxlength="128" value="">';

    tr.appendChild(td1);
    tr.appendChild(td2);
    tr.appendChild(td3);
    tr.appendChild(td4);
    tr.appendChild(td5);
}

function delRow(initialRowCount, tableId) {
    var table = document.getElementById(tableId);
    var rows = table.getElementsByTagName("tr").length;

    if (rows > ( initialRowCount + 2 )) {
        document.getElementById(tableId).deleteRow(--rows);
    }
}

function addProgressBarToDirectory(versionId, progressHtmlString) {
    var trdiv = document.getElementById('d' + versionId);

    var trbardiv = document.createElement("div");
    trbardiv.className = 'tr';
    trdiv.parentNode.appendChild(trbardiv);

    var tddiv = document.createElement("div");
    tddiv.className = 'td';
    trbardiv.appendChild(tddiv);

    var p9001div = document.createElement("div");
    p9001div.className = 'progress9002';
    p9001div.innerHTML = progressHtmlString;

    tddiv.appendChild(p9001div);
}

function addVersionEntryToDirectory(projectName, versionId, versionName) {
    var table = document.getElementById(projectName);

    var trdiv = document.createElement("div");
    trdiv.className = 'tr';
    trdiv.id = 'd' + versionId;
    table.appendChild(trdiv);

    var tddiv = document.createElement("div");
    tddiv.className = 'td';
    tddiv.innerHTML = '<a class="directory version" href="#v' + versionId + '">' + versionName + '</a>';

    trdiv.appendChild(tddiv);
}

function addProjectEntryToDirectory(tableId, projectId, projectName) {
    var table = document.getElementById(tableId);

    var trdiv = document.createElement("div");
    trdiv.className = 'tr';
    table.appendChild(trdiv);

    var tddiv = document.createElement("div");
    tddiv.className = 'td';
    tddiv.id = projectName;
    tddiv.innerHTML = '<div class="tr"><div class="td"><a class="directory project" href="#p' + projectId + '">' + projectName + '</a></div></div>';

    trdiv.appendChild(tddiv);
}

function backToTop() {
    $(document).ready(function () {
        // Der Button wird mit JavaScript erzeugt und vor dem Ende des body eingebunden.
        var back_to_top_button = ['<a href="#top" class="back-to-top"></a>'].join("");
        $("body").append(back_to_top_button)

        // Der Button wird ausgeblendet
        $(".back-to-top").hide();

        // Funktion für das Scroll-Verhalten
        $(function () {
            $(window).scroll(function () {
                if ($(this).scrollTop() > 100) { // Wenn 100 Pixel gescrolled wurde
                    $('.back-to-top').fadeIn();
                } else {
                    $('.back-to-top').fadeOut();
                }
            });

            $('.back-to-top').click(function () { // Klick auf den Button
                $('body,html').animate({
                    scrollTop: 0
                }, 800);
                return false;
            });
        });
    });
}