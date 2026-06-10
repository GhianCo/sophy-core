<?php

namespace Sophy\Database\Concerns;

use Sophy\Database\AuditContext;

/**
 * Automatically manages audit columns on INSERT, UPDATE and soft-delete.
 *
 * HOW IT WORKS
 * ─────────────
 * The trait overrides save() from SophyDB\Model and adds softDelete() / restore().
 * Before any persist operation it inspects $fillable: if an audit column is declared
 * there, it is filled automatically. If the column is absent from $fillable the
 * trait silently skips it — so existing models are never broken.
 *
 * COLUMNS MANAGED
 * ─────────────────
 * Timestamps  → created_at (INSERT only)  |  updated_at (INSERT + UPDATE)
 * Blameable   → created_by (INSERT only)  |  updated_by (INSERT + UPDATE)
 * Soft-delete → deleted_at + deleted_by + updated_at  (via softDelete())
 *
 * USAGE IN AN ENTITY
 * ───────────────────
 * use Sophy\Database\Concerns\HasAuditFields;
 *
 * abstract class PostEntity extends Model
 * {
 *     use HasAuditFields;
 *
 *     protected $fillable = [
 *         'title', 'body',
 *         'created_at', 'updated_at',          // enable timestamps
 *         'created_by', 'updated_by',          // enable blameable
 *         // 'deleted_at', 'deleted_by',        // add if soft-delete needed
 *     ];
 * }
 *
 * REGISTER THE USER RESOLVER (once, at boot time)
 * ──────────────────────────────────────────────────
 * AuditContext::setUserResolver(function () {
 *     return session()->get('user_id');
 * });
 *
 * CUSTOMISE COLUMN NAMES (optional, override in the Entity)
 * ───────────────────────────────────────────────────────────
 * protected $createdAtColumn = 'fecha_creacion';
 *
 * DISABLE PER MODEL (optional)
 * ──────────────────────────────
 * protected $auditTimestamps = false;  // skip created_at / updated_at
 * protected $auditBlameable  = false;  // skip created_by / updated_by
 */
trait HasAuditFields
{
    /**
     * Whether to manage timestamp columns (created_at / updated_at / deleted_at).
     *
     * @var bool
     */
    protected $auditTimestamps = true;

    /**
     * Whether to manage blameable columns (created_by / updated_by / deleted_by).
     *
     * @var bool
     */
    protected $auditBlameable = true;

    // ── Column name defaults (override in the Entity if your schema differs) ──

    /** @var string */
    protected $createdAtColumn = 'created_at';

    /** @var string */
    protected $updatedAtColumn = 'updated_at';

    /** @var string */
    protected $deletedAtColumn = 'deleted_at';

    /** @var string */
    protected $createdByColumn = 'created_by';

    /** @var string */
    protected $updatedByColumn = 'updated_by';

    /** @var string */
    protected $deletedByColumn = 'deleted_by';

    // ──────────────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Persist the model, injecting audit fields before the actual SQL.
     *
     * Behaviour mirrors SophyDB\Model::save():
     *   - No primary key in attributes → INSERT  (sets created_at, created_by)
     *   - Primary key present          → UPDATE  (sets updated_at, updated_by)
     *
     * @return array The persisted attributes.
     */
    public function save(): array
    {
        $isInsert = !isset($this->attributes[$this->primaryKey]);
        $now      = $this->freshTimestamp();
        $userId   = AuditContext::getCurrentUserId();

        if ($this->auditTimestamps) {
            // created_at: INSERT only, do not overwrite if already provided
            if ($isInsert && $this->auditColumnExists($this->createdAtColumn)
                && !isset($this->attributes[$this->createdAtColumn])
            ) {
                $this->attributes[$this->createdAtColumn] = $now;
            }

            // updated_at: always (INSERT and UPDATE)
            if ($this->auditColumnExists($this->updatedAtColumn)) {
                $this->attributes[$this->updatedAtColumn] = $now;
            }
        }

        if ($this->auditBlameable && $userId !== null) {
            // created_by: INSERT only, do not overwrite if already provided
            if ($isInsert && $this->auditColumnExists($this->createdByColumn)
                && !isset($this->attributes[$this->createdByColumn])
            ) {
                $this->attributes[$this->createdByColumn] = $userId;
            }

            // updated_by: always (INSERT and UPDATE)
            if ($this->auditColumnExists($this->updatedByColumn)) {
                $this->attributes[$this->updatedByColumn] = $userId;
            }
        }

        return parent::save();
    }

    /**
     * Mark the record as deleted without removing it from the database.
     *
     * Sets deleted_at (and optionally deleted_by / updated_at / updated_by).
     * Requires the model to already have a primary key (i.e. be persisted).
     *
     * @return array The updated attributes.
     * @throws \RuntimeException If the model has no primary key.
     */
    public function softDelete(): array
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            throw new \RuntimeException(
                'Cannot soft-delete a model without a primary key.'
            );
        }

        $now      = $this->freshTimestamp();
        $userId   = AuditContext::getCurrentUserId();
        $changes  = [];

        if ($this->auditTimestamps) {
            if ($this->auditColumnExists($this->deletedAtColumn)) {
                $changes[$this->deletedAtColumn] = $now;
            }
            if ($this->auditColumnExists($this->updatedAtColumn)) {
                $changes[$this->updatedAtColumn] = $now;
            }
        }

        if ($this->auditBlameable && $userId !== null) {
            if ($this->auditColumnExists($this->deletedByColumn)) {
                $changes[$this->deletedByColumn] = $userId;
            }
            if ($this->auditColumnExists($this->updatedByColumn)) {
                $changes[$this->updatedByColumn] = $userId;
            }
        }

        if (!empty($changes)) {
            $id = $this->attributes[$this->primaryKey];
            $this->where($this->primaryKey, $id)->update($changes);

            foreach ($changes as $column => $value) {
                $this->attributes[$column] = $value;
            }
        }

        return $this->attributes;
    }

    /**
     * Undo a soft-delete: clears deleted_at and deleted_by, refreshes updated_at.
     *
     * @return array The updated attributes.
     * @throws \RuntimeException If the model has no primary key.
     */
    public function restore(): array
    {
        if (!isset($this->attributes[$this->primaryKey])) {
            throw new \RuntimeException(
                'Cannot restore a model without a primary key.'
            );
        }

        $now     = $this->freshTimestamp();
        $changes = [];

        if ($this->auditTimestamps) {
            if ($this->auditColumnExists($this->deletedAtColumn)) {
                $changes[$this->deletedAtColumn] = null;
            }
            if ($this->auditColumnExists($this->updatedAtColumn)) {
                $changes[$this->updatedAtColumn] = $now;
            }
        }

        if ($this->auditBlameable) {
            if ($this->auditColumnExists($this->deletedByColumn)) {
                $changes[$this->deletedByColumn] = null;
            }
        }

        if (!empty($changes)) {
            $id = $this->attributes[$this->primaryKey];
            $this->where($this->primaryKey, $id)->update($changes);

            foreach ($changes as $column => $value) {
                $this->attributes[$column] = $value;
            }
        }

        return $this->attributes;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Returns the current timestamp string used for all audit columns.
     * Override in the Entity to change the format.
     *
     * @return string  e.g. "2025-06-10 14:30:00"
     */
    protected function freshTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Returns true when the given column is declared in $fillable.
     * This is the opt-in contract: add the column to $fillable to activate it.
     *
     * @param  string $column
     * @return bool
     */
    protected function auditColumnExists(string $column): bool
    {
        return in_array($column, $this->fillable, true);
    }
}
