<?php

namespace Sophy\Exceptions;

class ConexionDBException extends SophyException {
    public static function showMessage($message) {
        return new static($message, 500);
    }
}
