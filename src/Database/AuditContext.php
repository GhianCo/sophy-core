<?php

namespace Sophy\Database;

/**
 * Holds the callable that resolves the current authenticated user's ID.
 *
 * Register once at boot time (e.g. in a ServiceProvider):
 *
 *   AuditContext::setUserResolver(function () {
 *       return session()->get('user_id');
 *   });
 *
 * All models using HasAuditFields will call this resolver automatically.
 */
final class AuditContext
{
    /** @var callable|null */
    private static $userResolver = null;

    /**
     * Register the callable that returns the current user's ID.
     *
     * @param callable $resolver Must return int|string|null
     */
    public static function setUserResolver(callable $resolver): void
    {
        self::$userResolver = $resolver;
    }

    /**
     * Returns the current user ID, or null if no resolver is set or the resolver returns falsy.
     *
     * @return int|string|null
     */
    public static function getCurrentUserId()
    {
        if (self::$userResolver !== null) {
            $id = (self::$userResolver)();
            return $id !== null && $id !== '' && $id !== false ? $id : null;
        }

        return null;
    }

    /**
     * Remove the registered resolver (useful in tests).
     */
    public static function forgetUserResolver(): void
    {
        self::$userResolver = null;
    }
}
