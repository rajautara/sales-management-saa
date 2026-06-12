<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Users extends Component
{
    use WithPagination;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'staff';

    public ?int $editingUserId = null;

    public function mount(): void
    {
        if (! auth()->user()?->hasAnyRole(['admin', 'super-admin'])) {
            abort(403);
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                $this->editingUserId ? 'unique:users,email,' . $this->editingUserId : 'unique:users,email'
            ],
            'password' => [$this->editingUserId ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,staff'],
        ];
    }

    public function edit(int $userId): void
    {
        $user = User::findOrFail($userId);
        
        // Ensure user belongs to same company
        if ($user->company_id !== auth()->user()?->company_id) {
            abort(403);
        }

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->roles->first()?->name ?? 'staff';
        $this->password = '';
    }

    public function cancelEdit(): void
    {
        $this->reset(['editingUserId', 'name', 'email', 'password', 'role']);
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            
            // Ensure user belongs to same company
            if ($user->company_id !== auth()->user()?->company_id) {
                abort(403);
            }

            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (! empty($validated['password'])) {
                $user->update([
                    'password' => $validated['password'],
                ]);
            }

            $user->syncRoles([$validated['role']]);

            session()->flash('success', 'User updated successfully.');
        } else {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
                'company_id' => auth()->user()?->company_id,
            ]);

            $user->assignRole($validated['role']);

            session()->flash('success', 'User created successfully.');
        }

        $this->reset(['editingUserId', 'name', 'email', 'password', 'role']);
    }

    public function delete(int $userId): void
    {
        if ($userId === auth()->id()) {
            session()->flash('error', 'You cannot delete yourself.');
            return;
        }

        $user = User::findOrFail($userId);

        // Ensure user belongs to same company
        if ($user->company_id !== auth()->user()?->company_id) {
            abort(403);
        }

        $user->delete();
        session()->flash('success', 'User deleted successfully.');
    }

    public function render()
    {
        $company = auth()->user()?->company;
        $users = ($company ? $company->users() : User::query())
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'super-admin'))
            ->paginate(10);

        return view('livewire.settings.users', compact('users'));
    }
}
