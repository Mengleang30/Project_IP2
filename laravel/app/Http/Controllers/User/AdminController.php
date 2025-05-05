<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController
{
    public function listAllCustomers()
    {
        $customers = User::where('role', 'customer')->get();
        if ($customers->isEmpty()) {
            return response()->json(['message' => 'No customers found'], 404);
        }
        return response()->json($customers);
    }

    public function findCustomerById($id)
    {
        $customer = User::find($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }
        return response()->json($customer);
    }


    public function deleteCustomer($id)
    {
        $customer = User::find($id);
        if (!$customer) {
            return response()->json(['message' => 'Customer not found'], 404);
        }
        $customer->delete();
        return response()->json(['message' => 'Customer deleted successfully']);
    }

    public function updatePassword(Request $request){
        $validated = $request->validate([
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8',
        ]);
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        if (!password_verify($validated['current_password'], $user->password)) {
            return response()->json(['message' => 'Your current password is incorrect'], 403);
        }
        $user->update([
            'password' => bcrypt($validated['new_password']),
        ]);
        return response()->json([
            'message' => 'Password updated successfully',
            'user' => $user,
        ], 200);
    }

    public function updateUser(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'password' => 'required|string|min:8',
        ]);
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->update([
            'name' => $validated['name'],
            // 'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => bcrypt($validated['password']),
        ]);
        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ], 200);
    }

}
