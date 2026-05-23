<?php

namespace App\Policies;

use App\Models\ReportTemplate;
use App\Models\User;

class ReportTemplatePolicy
{
    public function view(User $user, ReportTemplate $template): bool
    {
        return $template->is_public || $user->id === $template->user_id || $user->hasRole('admin');
    }

    public function update(User $user, ReportTemplate $template): bool
    {
        return $user->id === $template->user_id || $user->hasRole('admin');
    }

    public function delete(User $user, ReportTemplate $template): bool
    {
        return $user->id === $template->user_id || $user->hasRole('admin');
    }
}
