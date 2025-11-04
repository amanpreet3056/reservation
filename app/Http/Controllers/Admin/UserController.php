<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('role')->orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = true;

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', __('User created successfully.'));
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', __('User updated successfully.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors(['user' => __('You cannot delete your own account.')]);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', __('User removed successfully.'));
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct', 'exists:users,id'],
        ]);

        $currentUserId = $request->user()->id;

        $ids = collect($data['ids'])
            ->unique()
            ->reject(fn (int $id) => $id === $currentUserId)
            ->all();

        if (empty($ids)) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors(['users' => __('No valid users selected for deletion. Your own account cannot be removed.')]);
        }

        $deleted = User::query()->whereIn('id', $ids)->delete();

        $message = trans_choice('Deleted :count user.|Deleted :count users.', $deleted, ['count' => $deleted]);

        if (count($data['ids']) !== count($ids)) {
            $message .= ' ' . __('Your own account was not removed.');
        }

        return redirect()->route('admin.users.index')->with('success', $message);
    }
}
