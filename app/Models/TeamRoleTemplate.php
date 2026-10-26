<?php

namespace App\Models;

use App\Support\TemplateCustomizationStore;
use Illuminate\Database\Eloquent\Model;

class TeamRoleTemplate extends Model
{
    protected $primaryKey = 'team_role_template_id';

    protected $fillable = [
        'group_name',
        'faculty_id',
        'group_id',
        'role',
        'selected_template',
        'is_published',
        'version',
        'updated_by',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'revision' => 'integer',
    ];

    /**
     * Whether the revision column exists yet.
     *
     * Deploys run migrations but carry on when they fail, so the code can be
     * live on a database that has not been migrated. Without the column a save
     * still works; it just cannot detect a conflict.
     */
    public static function hasRevisionColumn(): bool
    {
        static $has = null;

        return $has ??= \Illuminate\Support\Facades\Schema::hasColumn('team_role_templates', 'revision');
    }

    /** @var array|null In-memory customization payload before persist */
    protected ?array $pendingCustomizations = null;

    /** @var array|null In-memory layout payload before persist */
    protected ?array $pendingLayout = null;

    public function versions()
    {
        return $this->hasMany(TeamRoleTemplateVersion::class, 'team_role_template_id', 'team_role_template_id')
            ->orderByDesc('version');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    public function layouts()
    {
        return $this->hasMany(TemplateLayout::class, 'team_role_template_id', 'team_role_template_id')
            ->where('version_id', TemplateCustomizationStore::LIVE_VERSION_ID)
            ->orderBy('sort_order');
    }

    public function elements()
    {
        return $this->hasMany(TemplateElement::class, 'team_role_template_id', 'team_role_template_id')
            ->where('version_id', TemplateCustomizationStore::LIVE_VERSION_ID);
    }

    public function images()
    {
        return $this->hasMany(TemplateImage::class, 'team_role_template_id', 'team_role_template_id')
            ->where('version_id', TemplateCustomizationStore::LIVE_VERSION_ID);
    }

    public function contentItems()
    {
        return $this->hasMany(TemplateContentItem::class, 'team_role_template_id', 'team_role_template_id')
            ->where('version_id', TemplateCustomizationStore::LIVE_VERSION_ID);
    }

    public function getCustomizationsAttribute(): array
    {
        if ($this->pendingCustomizations !== null) {
            return $this->pendingCustomizations;
        }
        if (!$this->team_role_template_id) {
            return [];
        }

        return TemplateCustomizationStore::readCustomizations((int) $this->team_role_template_id, null);
    }

    public function setCustomizationsAttribute($value): void
    {
        $this->pendingCustomizations = is_array($value) ? $value : [];
    }

    public function getLayoutAttribute(): array
    {
        if ($this->pendingLayout !== null) {
            return $this->pendingLayout;
        }
        if (!$this->team_role_template_id) {
            return \App\Support\HotelTemplateBuilder::defaultLayout();
        }

        return TemplateCustomizationStore::readLayout((int) $this->team_role_template_id, null);
    }

    public function setLayoutAttribute($value): void
    {
        $this->pendingLayout = is_array($value) ? $value : [];
    }

    protected static function booted(): void
    {
        // Every write of the content moves the revision on, whichever code path
        // made it, so a browser holding the old number cannot write over it.
        static::saving(function (TeamRoleTemplate $template) {
            if ($template->pendingCustomizations === null && $template->pendingLayout === null) {
                return;
            }
            if (self::hasRevisionColumn()) {
                $template->revision = (int) $template->revision + 1;
            }
        });

        static::saved(function (TeamRoleTemplate $template) {
            if ($template->pendingCustomizations === null && $template->pendingLayout === null) {
                return;
            }

            $customizations = $template->pendingCustomizations ?? TemplateCustomizationStore::readCustomizations((int) $template->team_role_template_id, null);
            $layout = $template->pendingLayout ?? TemplateCustomizationStore::readLayout((int) $template->team_role_template_id, null);

            TemplateCustomizationStore::write($template, $customizations, $layout, null);

            $template->pendingCustomizations = null;
            $template->pendingLayout = null;
        });
    }
}
