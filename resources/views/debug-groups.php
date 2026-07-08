<!DOCTYPE html>
<html>
<head>
    <title>Debug Faculty Groups</title>
</head>
<body>
    <h1>Faculty Groups Debug</h1>
    <?php
    $facultyId = auth()->user()?->faculty?->id ?? null;
    
    if (!$facultyId) {
        echo "<p>No faculty ID found for current user</p>";
    } else {
        echo "<p>Faculty ID: {$facultyId}</p>";
        
        $groups = \App\Models\StudentGroup::with('student.user', 'roles')
            ->where('faculty_id', $facultyId)
            ->latest()
            ->get()
            ->groupBy('group_name');
        
        echo "<p>Total Groups: " . count($groups) . "</p>";
        
        foreach ($groups as $groupName => $groupMembers) {
            echo "<h2>Group: {$groupName}</h2>";
            echo "<ul>";
            foreach ($groupMembers as $member) {
                $user = $member->student?->user;
                $name = $user?->name ?? 'Unknown';
                $roles = $member->roles->pluck('role')->implode(', ');
                echo "<li>{$name} - {$roles}</li>";
            }
            echo "</ul>";
        }
    }
    ?>
</body>
</html>
