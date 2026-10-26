<?php

namespace App\Exceptions;

use App\Models\TeamRoleTemplate;
use RuntimeException;

/**
 * A save was based on an older copy of the template than the one now stored.
 *
 * Thrown inside the save transaction, before anything is written, so the row
 * is left exactly as the teammate who saved first left it. Carries that row so
 * the controller can hand the current version back to the browser to rebase on.
 */
class TemplateConflictException extends RuntimeException
{
    public function __construct(public readonly TeamRoleTemplate $current)
    {
        parent::__construct('This template was changed by someone else since you loaded it.');
    }
}
