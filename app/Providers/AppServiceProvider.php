<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\SubAdminPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Doesn't follow the {Model}Policy auto-discovery convention (the
        // permission names don't match), so it's registered explicitly.
        Gate::policy(User::class, SubAdminPolicy::class);

        // GST Report isn't backed by an Eloquent model — it's a single
        // legacy permission name, not the usual four-ability CRUD set —
        // so a closure-based Gate stands in for a policy class here.
        Gate::define('view-gst-report', fn (User $user) => $user->hasLegacyPermission('GST Report'));

        // Same pattern as above — "Send Email" (estimate_mail.php) is a
        // single legacy permission, not tied to any one model.
        Gate::define('send-email', fn (User $user) => $user->hasLegacyPermission('Send Email'));

        // Same pattern again — "Salary Sheet" (salary_bill.php).
        Gate::define('view-salary-sheet', fn (User $user) => $user->hasLegacyPermission('Salary Sheet'));

        // "Edit Measurement" is shared by both the Bill and Estimate
        // measurement-sheet editors in legacy (add_edit_bill_measurement.php
        // / add_edit_estimate_measurement.php both check this exact
        // permission name, not two separate per-module ones).
        Gate::define('edit-measurement', fn (User $user) => $user->hasLegacyPermission('Edit Measurement'));
    }
}
