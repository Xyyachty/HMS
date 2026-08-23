<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Support\HotelImageStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * The student's own account details.
 *
 * Mirrors FacultyController::updateProfile — same fields, same avatar handling —
 * because it is the same form from the student's side. What a student may change
 * is only what describes them: their name, how to reach them, and their photo.
 * Their student number, block, adviser, class, team and roles are records the
 * school and their faculty keep, so they are shown but never posted here.
 */
class StudentController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $student = $user?->student;

        if (!$student) {
            abort(403, 'Student account not found.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            // Fourth argument names the column to ignore by. Without it the rule
            // looks for a column called "id", which users no longer has.
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->user_id . ',user_id'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
            'remove_avatar' => ['nullable', 'boolean'],
        ]);

        $fullName = trim(implode(' ', array_filter([
            $validated['first_name'],
            $validated['middle_name'] ?? null,
            $validated['last_name'],
        ])));

        $userData = [
            'name' => $fullName,
            'first_name' => $validated['first_name'],
            'middle_name' => $validated['middle_name'] ?? null,
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
        ];

        if ($request->boolean('remove_avatar') && $user->avatar) {
            Storage::disk(HotelImageStore::disk())->delete($user->avatar);
            $userData['avatar'] = null;
        }

        if ($request->hasFile('avatar')) {
            try {
                $storedPath = $request->file('avatar')->store('avatars/students', HotelImageStore::disk());
            } catch (\Throwable $e) {
                Log::error('Student avatar upload failed', [
                    'user_id' => $user->user_id,
                    'disk' => HotelImageStore::disk(),
                    'error' => $e->getMessage(),
                ]);

                return back()->withErrors(['avatar' => 'Could not upload the photo to storage. Please try again.']);
            }

            if ($user->avatar) {
                Storage::disk(HotelImageStore::disk())->delete($user->avatar);
            }

            $userData['avatar'] = $storedPath;
        }

        $user->update($userData);

        $student->update([
            'phone_number' => $validated['phone_number'] ?? null,
        ]);

        ActivityLog::recordFor(ActivityLog::ACCOUNT_UPDATED, 'Updated their own student profile.');

        // The dashboard is one page of sections, so say which one to reopen —
        // otherwise the round trip lands the student back on the default tab.
        return redirect()->route('students.dashboard', ['section' => 'profile'])
            ->with('success', 'Profile information updated successfully.');
    }
}
