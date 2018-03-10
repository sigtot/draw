<?php

namespace Draw;

use Ratchet\ConnectionInterface;
use Draw\DrawClient;

class DrawClientStorage extends \SplObjectStorage {

    /**
     * @param ConnectionInterface $conn
     *
     * @return DrawClient|object
     */
    public function findByConnection(ConnectionInterface $conn) {
        $this->rewind();
        while($this->valid()) {
            if ($this->current()->getConnection() === $conn) {
                return $this->current();
            }
            $this->next();
        }
        return null;
    }
}
