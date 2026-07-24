<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username'  => 'required|string|min:5|max:20|unique:users,username',
            'password'  => 'required|string|min:6|max:20',
            'phone'     => 'required|string|unique:users,phone',
            'real_name' => 'nullable|string|max:50',
            'gender'    => 'nullable|integer|in:0,1,2',
            'age'       => 'nullable|integer|min:1|max:200',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'msg'  => $validator->errors()->first(),
                'data' => null,
            ]);
        }

        $user = User::create([
            'username'  => $request->username,
            'password'  => Hash::make($request->password),
            'phone'     => $request->phone,
            'real_name' => $request->real_name,
            'gender'    => $request->gender ?? 0,
            'age'       => $request->age,
            'status'    => 1,
        ]);

        $token = auth('api')->login($user);

        return response()->json([
            'code' => 200,
            'msg'  => '注册成功',
            'data' => [
                'user_id'  => $user->id,
                'username' => $user->username,
                'token'    => $token,
            ],
        ]);
    }

    /**
     * Get a JWT via given credentials.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'msg'  => $validator->errors()->first(),
                'data' => null,
            ]);
        }

        // 先尝试用户名登录
        $token = auth('api')->attempt([
            'username' => $request->username,
            'password' => $request->password,
        ]);

        // 再尝试手机号登录
        if (! $token) {
            $token = auth('api')->attempt([
                'phone'    => $request->username,
                'password' => $request->password,
            ]);
        }

        if (! $token) {
            return response()->json([
                'code' => 400,
                'msg'  => '用户名或密码错误',
                'data' => null,
            ]);
        }

        /** @var \App\Models\User $user */
        $user = auth('api')->user();

        if ($user->status !== 1) {
            auth('api')->logout();

            return response()->json([
                'code' => 400,
                'msg'  => '账号已被禁用',
                'data' => null,
            ]);
        }

        $user->update(['last_login_time' => now()]);

        return response()->json([
            'code' => 200,
            'msg'  => '登录成功',
            'data' => [
                'user_id'   => $user->id,
                'username'  => $user->username,
                'token'     => $token,
                'expire_at' => now()->addMinutes(config('jwt.ttl'))->format('Y-m-d\TH:i:s'),
            ],
        ]);
    }

    /**
     * Get the authenticated User.
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'code' => 200,
            'msg'  => '成功',
            'data' => [
                'user_id'   => $user->id,
                'username'  => $user->username,
                'real_name' => $user->real_name,
                'phone'     => $user->phone,
                'gender'    => $user->gender,
                'age'       => $user->age,
                'avatar'    => $user->avatar,
                'status'    => $user->status,
            ],
        ]);
    }

    /**
     * Log the user out (invalidate the token).
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'code' => 200,
            'msg'  => '退出成功',
        ]);
    }

    /**
     * Refresh a token.
     */
    public function refresh(): JsonResponse
    {
        /** @var \Tymon\JWTAuth\JWTGuard $guard */
        $guard = auth('api');
        $token = $guard->refresh();

        return response()->json([
            'code' => 200,
            'msg'  => '刷新成功',
            'data' => [
                'token'     => $token,
                'expire_at' => now()->addMinutes(config('jwt.ttl'))->format('Y-m-d\TH:i:s'),
            ],
        ]);
    }
}
