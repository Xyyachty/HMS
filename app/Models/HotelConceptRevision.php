<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One entry in a hotel concept's edit history: who saved it, what moved, when.
 *
 * Rows are written once and never touched again, so only created_at is tracked.
 */
class HotelConceptRevision extends Model
{
    protected $primaryKey = 'hotel_concept_revision_id';

    /** A revision is never updated. */
    public const UPDATED_AT = null;

    /**
     * The diff column is field_changes, not changes: Eloquent\Model already owns a
     * $changes property, which shadows an attribute of that name on read.
     */
    protected $fillable = [
        'hotel_concept_id',
        'user_id',
        'editor_name',
        'action',
        'field_changes',
        'title',
        'description',
        'hotel_type',
    ];

    protected $casts = [
        'field_changes' => 'array',
        'created_at' => 'datetime',
    ];

    public const CREATED = 'created';
    public const UPDATED = 'updated';

    public const ACTIONS = [
        self::CREATED => 'Created the concept',
        self::UPDATED => 'Updated the concept',
    ];

    public function concept()
    {
        return $this->belongsTo(HotelConcept::class, 'hotel_concept_id', 'hotel_concept_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action] ?? ucfirst((string) $this->action);
    }

    /**
     * Shape read by the student card and the faculty Team Details modal.
     *
     * "What was changed" is spelled out per field rather than handed over as raw
     * columns, so both portals print the same sentence without repeating the
     * label lookup.
     */
    public function toPortalArray(): array
    {
        $changes = [];
        foreach ((array) ($this->field_changes ?? []) as $field => $change) {
            $from = $change['from'] ?? null;
            $to = $change['to'] ?? null;

            if ($field === 'hotel_type') {
                $from = HotelConcept::typeLabel($from);
                $to = HotelConcept::typeLabel($to);
            }

            $changes[] = [
                'field' => $field,
                'label' => HotelConcept::TRACKED_FIELDS[$field] ?? ucfirst(str_replace('_', ' ', $field)),
                'from' => (string) $from,
                'to' => (string) $to,
            ];
        }

        return [
            'id' => $this->hotel_concept_revision_id,
            'action' => $this->action,
            'action_label' => $this->action_label,
            'editor' => $this->editor_name ?: ($this->user?->name ?? 'Unknown member'),
            'changes' => $changes,
            'title' => $this->title,
            'description' => $this->description,
            'hotel_type' => $this->hotel_type,
            'hotel_type_label' => HotelConcept::typeLabel($this->hotel_type),
            'created_at' => optional($this->created_at)->format('M d, Y g:i A'),
            'created_at_human' => optional($this->created_at)->diffForHumans(),
        ];
    }
}
