<?php
namespace Draw;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Draw\DrawSession;
use Draw\WSCall;
use Draw\DrawClient;

class DrawSocket implements MessageComponentInterface {
    protected $clients;
    protected $sessions;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->sessions = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $client = new DrawClient($conn);
        // Store the new connection to send messages to later
        $this->clients->attach($client);

        echo "New connection! ({$client->getName()})\n";
        $returnCall = new WSCall('you_are', array(
            'id' => $client->getName(),
        ));
        $client->send(json_encode($returnCall));
    }

    public function onMessage(ConnectionInterface $conn, $msg) {
        $client = new DrawClient($conn);
        echo sprintf("Connection %d sent %s.\n", $client->getName(), $msg);

        $call = json_decode($msg);
        switch($call->method) {
            case 'join_session':
                if($call->pin !== null) {
                    /** @var DrawSession $session */
                    foreach ($this->sessions as $session) {
                        if ($call->pin === $session->getPin()) {
                            $session->addClient($client); // Add client to session
                            $session->notifyClients('client_added', array('client_name' => $client->getName()), $client);

                            $returnCall = new WSCall('join_session', array(
                                'pin' => $call->pin,
                                'clients' => $session->getClientNames(),
                            ));
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

                $returnCall = new WSCall('join_session', array(
                    'pin' => $session->getPin(),
                    'clients' => $session->getClientNames(),
                ));
                $client->send(json_encode($returnCall));
                break;

        }
    }

    public function onClose(ConnectionInterface $conn) {
        $client = new DrawClient($conn);
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($client);


        echo "Client {$client->getName()} has disconnected\n";

    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $client = new DrawClient($conn);
        echo "An error has occurred: {$e->getMessage()}\n";

        $client->close();
    }
}