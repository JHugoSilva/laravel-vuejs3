<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{
    public function index() {
        $users = User::query()
        ->when(request('query'), function($query, $searchQuery){
            $query->where('name', 'like', "%{$searchQuery}%");
        })
        ->latest()
        ->paginate(2);
        return $users;
    }

    public function store() {
        request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:8'
        ]);

        $users = User::create([
            'name' => request('name'),
            'email' => request('email'),
            'password' => bcrypt(request('password')),
            'role' => 2
        ]);

        return $users;
    }

    public function update(User $user) {
        request()->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$user->id,
            'password' => 'sometimes|min:8'
        ]);
        $user->update([
            'name' => request('name'),
            'email' => request('email'),
            'password' => request('password') ? bcrypt(request('password')) : $user->password
        ]);

        return response()->json([
            'users' => $user,
            'status' => 200
        ]);
    }

    public function destroy(User $user) {
        $user->delete();
        return response()->noContent();
    }

    public function changeRole(User $user) {
        $user->update([
            'role' => request('role')
        ]);

        return response()->json(['success' => true]);
    }

    public function bulkDelete() {
        User::whereIn('id', request('ids'))->delete();
        return response()->json(['message' => 'Users deleted successfully!']);
    }
}
