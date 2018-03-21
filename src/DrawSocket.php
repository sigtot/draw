<?php
namespace Draw;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Draw\DrawSession;
use Draw\WSCall;
use Draw\DrawClient;
use Draw\DrawClientStorage;
use Draw\DrawSessionStorage;

class DrawSocket implements MessageComponentInterface {
    protected $clients;
    protected $sessions;

    public function __construct() {
        $this->clients = new DrawClientStorage;
        $this->sessions = new DrawSessionStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $client = new DrawClient($conn);
        // Store the new connection to send messages to later
        $this->clients->attach($client);

        echo "New connection! ({$client->getName()})\n";
        $returnCall = new WSCall('you_are', array( 'client' => array(
            'name' => $client->getName(),
            'id' => $client->getId(),
        )));
        $client->send(json_encode($returnCall));
    }

    public function onMessage(ConnectionInterface $conn, $msg) {
        $client = $this->clients->findByConnection($conn);
        echo sprintf("Connection %d sent %s.\n", $client->getName(), $msg);

        $call = json_decode($msg);
        switch($call->method) {
            case 'join_session':
                if($call->pin !== null) {
                    /** @var DrawSession $session */
                    foreach ($this->sessions as $session) {
                        if ($call->pin === $session->getPin()) {
                            $session->addClient($client); // Add client to session
                            return;
                        }
                    }
                }
                break;

            case 'create_session':
                $session = new DrawSession();
                $session->generatePin($this->sessions);
                $session->addClient($client);
                $this->sessions->attach($session);
                break;

            case 'change_name':
                $client->setName(htmlspecialchars($call->name, ENT_QUOTES));
                $client->getSession()->notifyClients('client_changed_name', array(
                    'id' => $client->getId(),
                    'newName' => $client->getName(),
                ));
                break;
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $client = $this->clients->findByConnection($conn);
        // The connection is closed, remove it, as we can no longer send it messages
        $session = $client->getSession();
        if($session !== null){
            if(sizeof($session->getClients()) === 1) $this->sessions->detach($session);
            $session->removeClient($client);
        }
        $this->clients->detach($client);
        echo "Client {$client->getName()} has disconnected\n";

    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $client = $this->clients->findByConnection($conn);

        $session = $client->getSession();
        $session->removeClient($client);
        if(sizeof($session->getClients()) === 0) $this->sessions->detach($session);

        $this->clients->detach($client);

        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}
