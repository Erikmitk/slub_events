jQuery( document ).ready(function($) {
    $(function () {
        $('#datetimepicker1').datetimepicker({
            locale: 'de',
            calendarWeeks: true,
            format: 'DD-MM-YYYY'
        });
    });

    var validator = $( "#newEventForm" ).validate({
        ignore: 'input[type="button"],input[type="submit"]',
        errorClass: "has-error",
        validClass: "has-success",
        highlight: function(element, errorClass, validClass) {
            $(element).parent("div").addClass(errorClass).removeClass(validClass);
        },
        unhighlight: function(element, errorClass, validClass) {
            $(element).parent("div").removeClass(errorClass).addClass(validClass);
        },
        rules: {
            genius_bar: {
                required: true
            },
            recurring: {
                required: true
            },
            requiregroup: {
                required: true
            },
        }
    });

      tinymce.init({
        selector: 'textarea',
        language: 'de',
        plugins: "code, link, image",
        min_height: 300,
        menubar: false,
        toolbar: 'undo redo | styleselect | bold italic strikethrough | bullist numlist | link image | code'
      });

    $('ul.category_tree').bonsai({
        expandAll: false,
        checkboxes: true, // depends on jquery.qubit plugin
        handleDuplicateCheckboxes: false // optional
    });

    $("#tab-eventtype .radiogroup label").click(function () {

        var labelSelected = $(this).text().trim();

        $('.nav-tabs > .active').next('li').find('a').trigger('click');

        $('button.option-genius_bar').text(labelSelected);

    });

    $("#tab-recurring .radiogroup label").click(function () {

        var labelSelected = $(this).text().trim();

        $('.nav-tabs > .active').next('li').find('a').trigger('click');

        $('button.option-recurring').text(labelSelected);

    });

});

function checkBoxes(objThis) {
    // Checkbox selected? (true/false)
    var blnChecked = objThis.checked;
    // parent node
    var objHelp = objThis.parentNode;

    while (objHelp.nodeName.toUpperCase() != "LI") {
        // next parent node
        objHelp = objHelp.parentNode;
    }

    var arrInput = objHelp.getElementsByTagName("input");
    var intLen = arrInput.length;

    for (var i = 0; i < intLen; i++) {
        // select/unselect Checkbox
        if (arrInput[i].type == "checkbox") {
            arrInput[i].checked = blnChecked;
        }
    }
}

function checkBoxContacts(objThis) {

    // Checkbox selected? (true/false)
    var blnChecked = objThis.checked;
    var selectlist = document.getElementById('field-contact-search');

    // un-/select all according to the checkbox state
    for (var i = 0; i < selectlist.length; i++) {
        selectlist.options[i].selected = blnChecked;
    }
    if (blnChecked)
        selectlist.disabled = true;
    else
        selectlist.disabled = false;

}

// add new event initCheckBoxContacts
addEvent(window, "load", initCheckBoxContacts);
function initCheckBoxContacts() {
    CheckBoxContacts.init();
}
var CheckBoxContacts = new function () {
};

// init
CheckBoxContacts.init = function () {

    // Find td with classname 'foldtree' which contains the foldable tree
    if (!document.getElementById) return;
    var chkbox = document.getElementById("checkbox-all-contacts");
    var selectlist = document.getElementById('field-contact-search');
    if (!selectlist) return;
    var selected = 0;
    for (var i = 0; i < selectlist.length; i++) {
        if (selectlist.options[i].selected == true)
            selected++;
    }
    if (selected == selectlist.length) {
        chkbox.checked = true;
        selectlist.disabled = "disabled";
    }
    else {
        chkbox.checked = false;
        selectlist.disabled = false;
    }
};
