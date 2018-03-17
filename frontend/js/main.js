var inSession = false;

function WSCall(method) {
    this.method = method;
}

function showView(view) {
    view.addClass('d-flex');
}

function hideView(view) {
    view.removeClass('d-flex');
}

function addUserToContainer(client) {
    var userElement = $("<div data-toggle=\"tooltip\" data-placement=\"bottom\">")
        .addClass('user-circle rounded-circle shrink')
        .css('background-color', '#' + client.color)
        .prop('title', client.name).attr('id', 'user-'+client.id);
    $('#user-container').append(userElement);

    // Wait a little before revealing to enable transition
    window.setTimeout(function() {
        userElement.removeClass('shrink')
    }, 100);

    // Init tooltips
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
}

function removeUserFromContainer(client) {
    var userElement = $("#user-"+client.id);
    userElement.addClass('shrink disappear');
    // Wait until the transition is finished
    window.setTimeout(function() {
        userElement.remove();
    }, 500);
}

function joinSession(pin, clients) {
    hideView($('#main'));
    showView($('#in-session'));

    $('#nav-game-pin').text(pin);
    for(var i = 0; i < clients.length; i++) {
        addUserToContainer(clients[i]);
    }
}

function youAre(name) {
    $('#name-input').val(name);
}

function clientAdded(client) {
    addUserToContainer(client);
}

function clientLeft(client) {
    removeUserFromContainer(client);
}

$('document').ready(function(){
    showView($('#main'));

    var hostname = window.location.hostname;
    var port = '5000';

    var conn = new WebSocket('ws://'+hostname+':'+port);
    conn.onopen = function(e) {
        console.log("Connection established!");
    };

    conn.onmessage = function(e) {
        var data = JSON.parse(e.data);
        switch(data.method) {
            case 'join_session':
                joinSession(data.pin, data.clients);
                console.log(data);
                break;
            case 'client_added':
                clientAdded(data.client);
                console.log(data);
                break;
            case 'client_left':
                clientLeft(data.client);
                console.log(data);
                break;
            case 'you_are':
                console.log(data);
                youAre(data.client.name);
                break;
            default:
                console.log('Unknown method: '+e.data);
        }
    };

    var sessionCodeInput = $('#session-code-input');
    var sessionCodeInputButton = $('#session-code-input-button');
    var sessionCreateButton = $('#session-create-button');

    sessionCodeInputButton.click(function() {
        if(inSession) return;

        var joinSessionCall = new WSCall('join_session');
        joinSessionCall.pin = sessionCodeInput.val();

        conn.send(JSON.stringify(joinSessionCall));
    });

    sessionCreateButton.click(function() {
       if(inSession) return;

       var createSessionCall = new WSCall('create_session');
       conn.send(JSON.stringify((createSessionCall)));
    });

    sessionCodeInput.on('input', function() {
        var inputString = sessionCodeInput.val();
        sessionCodeInput.val(inputString.replace(/\D/g,''));
    });
});
