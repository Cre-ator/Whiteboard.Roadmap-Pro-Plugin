var newProfileCounter = 0;

function addProfileRow ( statusValues, statusStrings )
{
    var tableId = "profiles";
    var table = document.getElementById ( tableId );
    var rows = table.getElementsByTagName ( "tr" ).length;
    var tr = table.insertRow ( rows );
    var td1 = document.createElement ( "td" );
    var td2 = document.createElement ( "td" );
    var td3 = document.createElement ( "td" );
    var td4 = document.createElement ( "td" );
    var td5 = document.createElement ( "td" );
    var td6 = document.createElement ( "td" );

    var optionstring = '';
    for ( var i = 0; i < statusValues.length; i++ )
    {
        var value = statusValues[ i ];
        var string = statusStrings[ i ];

        optionstring += '<option value="' + value + '">' + string + '</option>'
    }

    /** name */
    td1.innerHTML = '<input type="text" name="profile-name[]" size="15" maxlength="128" value="">';
    /** released */
    td2.innerHTML = '<select name="new-status-' + newProfileCounter + '[]" multiple="multiple">' + optionstring + '</select>';
    newProfileCounter++;
    /** obsolete */
    td3.innerHTML = '<label><input class="color {pickerFace:4,pickerClosable:true}" type="text" name="profile-color[]" value=""/></label>';
    /** date */
    td4.innerHTML = '<input type="text" name="profile-prio[]" size="15" maxlength="3" value="">';
    /** description */
    td5.innerHTML = '<input type="text" name="profile-effort[]" size="15" maxlength="3" value="">';
    /** document type */
    td6.innerHTML = '';

    tr.appendChild ( td1 );
    tr.appendChild ( td2 );
    tr.appendChild ( td3 );
    tr.appendChild ( td4 );
    tr.appendChild ( td5 );
    tr.appendChild ( td6 );

    var evt = document.createEvent ( 'Event' );
    evt.initEvent ( 'load', false, false );
    window.dispatchEvent ( evt );
}

function delProfileRow ( initialRowCount )
{
    var tableId = "profiles";
    var table = document.getElementById ( tableId );
    var rows = table.getElementsByTagName ( "tr" ).length;

    if ( rows > ( initialRowCount + 2 ) )
    {
        document.getElementById ( tableId ).deleteRow ( --rows );
    }
}