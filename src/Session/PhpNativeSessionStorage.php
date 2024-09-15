<?php

namespace Sophy\Session;

class PhpNativeSessionStorage implements SessionStorage
{
    public function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function save()
    {
        session_write_close();
    }

    public function id()
    {
        return session_id();
    }

    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public function remove($key)
    {
        unset($_SESSION[$key]);
    }

    public function destroy()
    {
        session_destroy();
    }
}
