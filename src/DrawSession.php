<?php

namespace Draw;

use Ratchet\ConnectionInterface;

/**
 * Class DrawSession
 *
 * @package Draw
 */
class DrawSession {

    /**
     * @var string $pin
     */
    protected $pin;

    /**
     * @var \SplObjectStorage $clients
     */
    protected $clients;

    /**
     * Session constructor.
     */
    public function __construct() {
        $this->clients = new \SplObjectStorage();
    }

    /**
     * @return string
     */
    public function getPin() {
        return $this->pin;
    }

    /**
     * Generates a pin that does not belong to any other sessions
     * @param \SplObjectStorage $sessions
     */
    public function generatePin(\SplObjectStorage $sessions) {
        $pin = (string) rand(100000, 999999);
        $unique = false; // Assume not unique
        while(!$unique) {
            $unique = true;
            /** @var DrawSession $session */
            foreach ($sessions as $session) {
                if($session->getPin() === $pin) $unique = false;
            }
            $pin = (string) rand(100000, 999999);
        }
        $this->pin = $pin;
    }

    /**
     * @return \SplObjectStorage
     */
    public function getClients() {
        return $this->clients;
    }

    /**
     * @return array of client ids
     */
    public function getClientNames() {
        $idArray = array();
        /** @var DrawClient $client */
        foreach ($this->clients as $client) {
            array_push($idArray, $client->getName());
        }
        return $idArray;
    }

    /**
     * @param DrawClient $client
     */
    public function addClient(DrawClient $client) {
        $this->clients->attach($client);
    }

    /**
     * Notify all the session's clients of an event.
     *
     * @param string $event
     * @param array $data is an relational array containing extra fields for the WSCall object
     * @param DrawClient $excludedClient
     */
    public function notifyClients($event, $data = null, $excludedClient = null){
        // Create the return call object
        $returnCall = new WSCall($event);
        if($data !== null) {
            foreach ($data as $property => $value) {
                $returnCall->addProperty($property, $value);
            }
        }

        // Send it to all clients in the session (except maybe one excluded client)
        foreach ($this->clients as $client) {
            if($excludedClient !== null && $client !== $excludedClient)
                $client->send(json_encode($returnCall));
        }
    }
}