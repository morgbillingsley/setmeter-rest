<?php

class Home extends Controller
{
    public function index(string $path)
    {
        return $this->send("Hello from " . SITE_NAME);
    }
}

?>