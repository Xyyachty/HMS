<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    /**
     * The names of the attributes that should not be trimmed.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Template text is saved exactly as the student typed it. A two-line hero
     * title whose second line was cleared is saved as "Where Elegance\n";
     * trimming that newline made it read back as a one-line edit, and the
     * template's own second line ("Meets Comfort") came back on every reload.
     */
    protected function transform($key, $value)
    {
        if (str_starts_with($key, 'customizations.') && str_ends_with($key, '.text')) {
            return $value;
        }

        return parent::transform($key, $value);
    }
}
