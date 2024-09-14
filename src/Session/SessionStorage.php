<?php

namespace Sophy\Session;

interface SessionStorage
{
    public function start();

    public function save();

    public function id();

    public function get($key, $default = null);

    public function set($key, $value);

    public function has($key);

    public function remove($key);

    public function destroy();
}
