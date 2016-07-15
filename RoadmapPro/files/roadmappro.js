function add_profile_row ( status_values )
{
    var container_id = "profile_container";
    var profile_container = document.getElementById ( container_id );
    var profile_row = document.createElement ( "div" );
    var profile_col1 = document.createElement ( "div" );
    var profile_col2 = document.createElement ( "div" );
    var profile_col3 = document.createElement ( "div" );
    var profile_col4 = document.createElement ( "div" );

    var optionstring = '';
    status_values.forEach ( function ( status_value )
    {
        optionstring += "<option>" + status_value + "</option>"
    } );

    /** name */
    profile_col1.innerHTML = '<input type="text" id="new_profile" name="profile_name[]" style="width:100%;" maxlength="64" value="" />';
    /** status */
    profile_col2.innerHTML = '<select name="new_status[]" multiple="multiple">' + optionstring + '</select>';
    /** color */
    profile_col3.innerHTML = '<label><input class="color {pickerFace:4,pickerClosable:true}" type="text" name="profile_color[]" value=""/></label>';
    /** action */
    profile_col4.innerHTML = '';

    profile_container.appendChild ( profile_row );

    profile_row.className += "row";
    profile_row.id += "new_profile_row";

    profile_row.appendChild ( profile_col1 );
    profile_col1.className += "gridcol-1";
    profile_col1.id += "profile_col";
    profile_row.appendChild ( profile_col2 );
    profile_col2.className += "gridcol-1";
    profile_col2.id += "profile_col";
    profile_row.appendChild ( profile_col3 );
    profile_col3.className += "gridcol-1";
    profile_col3.id += "profile_col";
    profile_row.appendChild ( profile_col4 );
    profile_col4.className += "gridcol-3";
    profile_col4.id += "profile_col";

    var evt = document.createEvent ( 'Event' );
    evt.initEvent ( 'load', false, false );
    window.dispatchEvent ( evt );
}

function del_profile_row ( initial_row_count )
{
    var container_id = "profile_container";
    var profile_container = document.getElementById ( container_id );
    var rows = profile_container.getElementsByClassName ( "row" ).length;

    if ( rows > ( initial_row_count + 1 ) )
    {
        $ ( '#profile_container #profile_row:last' ).fadeOut ();
    }
}

// function checkProfileChange ()
// {
//     // firstSelector = document.getElementsByName ( "top-id" );
//     var selectorName = "top-id";
//     var secondSelector = document.getElementsByName ( selectorName );
//
//     secondSelector.addEventListener ( "onchange", function ()
//         {
//             alert ( "changed" );
//         }
//     );
// }