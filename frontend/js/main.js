var inSession = false;

function WSCall(method) {
    this.method = method;
}

$('document').ready(function(){
    var conn = new WebSocket('ws://localhost:8080');
    conn.onopen = function(e) {
        console.log("Connection established!");
    };

    conn.onmessage = function(e) {
        console.log(e.data);
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

