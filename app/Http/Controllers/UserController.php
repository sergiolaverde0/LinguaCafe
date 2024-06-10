<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\GoalService;
use App\Services\UserService;

// request classes
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;

class UserController extends Controller {
    private $userService;

    public function __construct(UserService $userService) {
        $this->userService = $userService;

    }

    public function isUserPasswordChanged() {
        $passwordChanged = Auth::user()->password_changed;
        return response($passwordChanged, 200);
    }

    public function getUsers() {
        try {
            $users = $this->userService->getUsers();
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json($users, 200);
    }

    public function showLoginForm() {
        $userCount = User::count();
        $theme = $_COOKIE['theme'] ?? 'light';

        if (Auth::check()) {
            return redirect()->intended('/');
        }

        return view('auth.login', [
            'userCount' => $userCount,
            'theme' => $theme
        ]);
    }
    
    public function authenticateUser(Request $request) {
        $email = $request->post('email');
        $password = $request->post('password');

        if (Auth::attempt([
            'email' => $email,
            'password' => $password,
        ])) {
            $request->session()->regenerate();
            Auth::logoutOtherDevices($password);
 
            return response()->json('User has been logged in successfully.', 200);
        } else {
            return response()->json('Login error.', 500);
        }
    }

    public function updatePassword(UpdatePasswordRequest $request) {
        $user = Auth::user();
        $password = $request->post('password');
        
        try {
            $this->userService->updatePassword($user, $password);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }
                
        return response()->json('Password has been updated successfully.', 200);
    }

    public function createUser(CreateUserRequest $request) {
        $userCount = User::count();
        $name = $request->post('name');
        $email = $request->post('email');
        $password = $request->post('password');
        $isAdmin = $request->post('isAdmin');
        $passwordChanged = $userCount === 0;

        // If this is the first user, it can be created without any authorization.
        if (!Auth::check() && $userCount !== 0) {
            abort(401, 'Not authorized to create a user.');
        }

        try {
            $this->userService->createUser($name, $email, $password, $isAdmin, $passwordChanged);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('User has been created successfully.', 200);
    }

    public function updateUser(UpdateUserRequest $request) {
        $userId = $request->post('userId');
        $name = $request->post('name');
        $email = $request->post('email');
        $isAdmin = $request->post('isAdmin');

        try {
            $this->userService->updateUser($userId, $name, $email, $isAdmin);
        } catch(\Exception $e) {
            abort(500, $e->getMessage());
        }

        return response()->json('User has been created successfully.', 200);
    }
}
