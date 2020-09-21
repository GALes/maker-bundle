<?php

namespace GALes\MakerBundle;

class GALesMaker
{
    /**
     * @var string
     */
    private $nombre;

    public function __construct($nombre)
    {
        $this->nombre = $nombre;
    }

    public function HolaMundo()
    {
        $nombre = $this->nombre;
        return "Hola " . $this->nombre . " desde el Bundle Separado!!!";
    }
}