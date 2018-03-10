<?php

namespace Draw;

use Ratchet\ConnectionInterface;
use Draw\DrawClientStorage;
use Draw\DrawSessionStorage;

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
        $this->clients = new DrawClientStorage();
    }

    /**
     * @return string
     */
    public function getPin() {
        return $this->pin;
    }

    /**
     * Generates a pin that does not belong to any other sessions
     * @param DrawSessionStorage $sessions
     */
    public function generatePin(DrawSessionStorage $sessions) {
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
     * @return DrawClientStorage
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
     * Adds client to $clients and set the client's session to this session
     *
     * @param DrawClient $client
     */
    public function addClient(DrawClient $client) {
        $this->clients->attach($client);
        $client->setSession($this);

        $returnCall = new WSCall('join_session', array(
            'pin' => $this->getPin(),
            'clients' => $this->getClientNames(),
        ));
        $client->send(json_encode($returnCall));

        $this->notifyClients('client_added', array('client_name' => $client->getName()), $client);
    }

    /**
     * Remove client from $clients and null the client's session
     *
     * @param DrawClient $client
     */
    public function removeClient(DrawClient $client) {
        $this->clients->detach($client);
        $client->setSession(null);

        $this->notifyClients('client_left', array(
            'client_name' => $client->getName(),
        ));
    }

    /**
     * Notify all the session's clients of an event.
     *
     * @param string $event
     * @param array $data is a relational array containing extra fields for the WSCall object
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
            if($excludedClient == null || $client !== $excludedClient)
                $client->send(json_encode($returnCall));
        }
    }
}