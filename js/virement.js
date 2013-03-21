$('#userName').typeahead({
    source: function(input, process){
        $('#userId').val("");
        $.get('ajax', 'search='+input, function(data) {
            map = {};
            usernames = [];
        
            $.each(JSON.parse(data), function (i, user) {
                map[user.name] = user;
                usernames.push(user.name);
            });
        
            process(usernames);
        });
    },
    matcher: function(item){
        return true;
    },
    updater: function(item){
        $('#userId').val(map[item].id);
        $('#userName').blur();
        return item;
    }
});

$('#userName').click(function(){
    $('#userName').val("");
    $('#userId').val("");
})