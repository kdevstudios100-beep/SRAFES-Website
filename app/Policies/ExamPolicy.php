<?php

namespace App\Policies;

use App\Enum\Permission;
use App\Models\User;

class ExamPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ViewExaminationResults;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageExams;
    }

    // Exams holding marks or published results are kept so no marks are lost
    public function delete(User $user, mixed $record): bool
    {
        return $this->canManage($user) && ! $record->hasMarks() && $record->published_at === null;
    }
}
