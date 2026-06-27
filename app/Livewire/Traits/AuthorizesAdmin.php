<?php

namespace App\Livewire\Traits;

trait AuthorizesAdmin
{
    /**
     * Livewire boot hook — runs on EVERY request for the component, both the
     * initial render and every subsequent action/method call (unlike mount(),
     * which only runs once). This guarantees privileged component methods
     * cannot be invoked by non-admins via direct Livewire update requests,
     * independent of the route-level role middleware.
     */
    public function bootAuthorizesAdmin(): void
    {
        if (! auth()->user()?->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }
    }
}
