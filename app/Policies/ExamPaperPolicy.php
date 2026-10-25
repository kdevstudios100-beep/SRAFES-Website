<?php

namespace App\Policies;

use App\Enum\Permission;
use App\Models\User;

class ExamPaperPolicy extends ResourcePolicy
{
    protected function viewPermission(): Permission
    {
        return Permission::ViewExaminationResults;
    }

    protected function managePermission(): Permission
    {
        return Permission::ManageExams;
    }

    // Papers of a locked exam are frozen with its marks
    public function update(User $user, mixed $record): bool
    {
        return $this->canManage($user) && ! $record->exam->isLocked();
    }

    // Papers with marks are kept so no marks are lost
    public function delete(User $user, mixed $record): bool
    {
        return $this->canManage($user) && ! $record->exam->isLocked() && ! $record->marks()->exists();
    }
}
