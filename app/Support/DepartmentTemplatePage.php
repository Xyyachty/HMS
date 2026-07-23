<?php

namespace App\Support;

use App\Models\StudentGroup;
use App\Models\Task;
use App\Models\User;

/**
 * Shared bootstrapping for department hotel-template pages.
 */
class DepartmentTemplatePage
{
    public static function boot(User $authUser, string $role): array
    {
        $student = $authUser->student;
        $groupMembership = $student
            ? StudentGroup::with(['student.user', 'roles'])->where('student_id', $student->id)->first()
            : null;

        $facultyId = $groupMembership?->faculty_id;
        $groupName = $groupMembership?->group_name;
        $studentRoles = $groupMembership ? $groupMembership->roles->pluck('role')->toArray() : [];

        $groupMembers = collect();
        $group = null;

        if ($groupMembership) {
            $groupMembers = StudentGroup::with('student.user', 'roles')
                ->where('group_name', $groupName)
                ->when($facultyId, fn ($q) => $q->where('faculty_id', $facultyId))
                ->get()
                ->map(function ($member) {
                    $user = $member->student?->user;
                    $displayName = trim(implode(' ', array_filter([
                        $user?->last_name,
                        $user?->first_name,
                        $user?->middle_name,
                    ])));
                    $displayName = $displayName !== '' ? $displayName : ($user?->name ?? 'Student');

                    return (object) [
                        'id' => $user?->id,
                        'name' => $displayName,
                        'email' => $user?->email,
                        'roles' => $member->roles->pluck('role')->toArray(),
                    ];
                });

            $group = (object) [
                'name' => $groupName,
                'members' => $groupMembers,
            ];
        }

        $tasks = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('role', $role)->where('status', 'active')->get()
            : collect();

        $tasksByRole = $facultyId
            ? Task::where('faculty_id', $facultyId)->where('status', 'active')->get()->groupBy('role')
            : collect();

        $selectedTemplate = null;
        $templateCustomizations = [];
        $templateVersion = null;
        $templateLayout = HotelTemplateBuilder::defaultLayout();
        $templatePayload = null;
        $canEditTemplate = false;

        $editablePages = HotelTemplateBuilder::editablePagesForRole($role);
        $preferredPage = HotelTemplateBuilder::preferredPageForRole($role);

        if ($groupMembership) {
            $roleTemplate = HotelTemplateBuilder::ensureTemplate($groupMembership, $role);
            $canEditTemplate = HotelTemplateBuilder::canEdit($authUser, $groupMembership, $role);
            $templatePayload = HotelTemplateBuilder::payload($roleTemplate, $canEditTemplate);
            // Always hydrate the iframe with the merged team site so everyone sees synced work
            $templateCustomizations = $templatePayload['customizations'] ?? [];
            $templateLayout = $roleTemplate->layout ?? $templateLayout;
            $templateVersion = $templatePayload['sync_version'] ?? optional($roleTemplate->updated_at)->timestamp;
            $editablePages = $templatePayload['editable_pages'] ?? $editablePages;
            $preferredPage = $templatePayload['preferred_page'] ?? $preferredPage;

            // Group template is locked by Front Desk only — teammates see nothing until chosen
            $frontDeskTemplate = $role === 'front_desk'
                ? $roleTemplate
                : HotelTemplateBuilder::ensureTemplate($groupMembership, 'front_desk');

            $selectedTemplate = $frontDeskTemplate->selected_template ?: null;

            // Keep non–front-desk rows in sync once Front Desk has chosen
            if ($role !== 'front_desk' && !empty($selectedTemplate) && $roleTemplate->selected_template !== $selectedTemplate) {
                $roleTemplate->selected_template = $selectedTemplate;
                $roleTemplate->save();
            }
        }

        return compact(
            'tasks',
            'groupMembers',
            'group',
            'studentRoles',
            'tasksByRole',
            'groupName',
            'facultyId',
            'selectedTemplate',
            'templateCustomizations',
            'templateVersion',
            'templateLayout',
            'templatePayload',
            'canEditTemplate',
            'editablePages',
            'preferredPage'
        ) + ['builderRole' => $role];
    }
}
