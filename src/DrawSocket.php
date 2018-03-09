<?php
namespace Draw;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Draw\DrawSession;
use Draw\WSCall;

class DrawSocket implements MessageComponentInterface {
    protected $clients;
    protected $sessions;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->sessions = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $client) {
        // Store the new connection to send messages to later
        $this->clients->attach($client);

        echo "New connection! ({$client->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $client, $msg) {
        echo sprintf("Connection %d sent %s.\n", $client->resourceId, $msg);

        $call = json_decode($msg);
        switch($call->method) {
            case 'join_session':
                if($call->pin !== null) {
                    /** @var DrawSession $session */
                    foreach ($this->sessions as $session) {
                        if ($call->pin === $session->getPin()) {
                            $session->addClient($client); // Add client to session
                            //$session->notifyClients('client_added')

                            $returnCall = new WSCall('join_session');
                            $returnCall->addProperty('pin', $call->pin);
                            $client->send(json_encode($returnCall));
                            break;
                        }
                    }
                }
                break;
            case 'create_session':
                $session = new DrawSession();
                $session->generatePin($this->sessions);
                $session->addClient($client);
                $this->sessions->attach($session);

                $returnCall = new WSCall('join_session');
                $returnCall->addProperty('pin', $session->getPin());
                $client->send(json_encode($returnCall));
                break;

        }
    }

    public function onClose(ConnectionInterface $client) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($client);

        echo "Connection {$client->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $client, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $client->close();
    }
}