<?php

namespace Sophy\Exceptions;

class NotFoundException extends SophyException {
    public static function showMessage($message) {
        return new static($message, 404);
    }
}
