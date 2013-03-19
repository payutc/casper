function lookup(inputString) {
    if(inputString.length == 0) {
        // Hide the suggestion box.
        $('#suggestions').hide();
    } else {
        $.get('ajax', 'search='+inputString, function(data) {
            if(data.length >0) {
                if ( $("#userName").is(":focus") ) 
                $('#suggestions').show();
                $('#autoSuggestionsList').html(data);
            }
        });
    }
} // lookup

function fill(thisValue) {
    var elem = thisValue.split('!!!');
    id = elem[0];
    firstname = elem[1];
    lastname = elem[2];


              
    $('#userName').val(firstname + ' ' + lastname);
    $('#userId').val(id);
    $('#suggestions').hide();
}

