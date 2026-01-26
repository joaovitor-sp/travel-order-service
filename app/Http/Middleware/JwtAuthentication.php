<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Log;
use App\Domain\Models\User;
use Illuminate\Support\Facades\Auth;

class JwtAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $rawToken = $request->bearerToken() ?: $request->query('token');

            if (!$rawToken) {
                Log::warning('JwtAuth: token not provided (no Authorization header)');
                return response()->json(['message' => 'Token not provided'], Response::HTTP_UNAUTHORIZED);
            }

            $payload = JWTAuth::setToken($rawToken)->getPayload();

            $id = $payload->get('sub');
            $name = $payload->get('name');
            $isAdmin = (bool) ($payload->get('is_admin') ?? false);

            if (empty($id)) {
                Log::warning('JwtAuth: payload missing sub');
                return response()->json(['message' => 'Invalid token: sub missing'], Response::HTTP_UNAUTHORIZED);
            }

            $user = User::updateOrCreate(
                ['id' => $id],
                ['name' => $name, 'is_admin' => $isAdmin]
            );

            Auth::setUser($user);
            Log::info('JwtAuth: authenticated', ['sub' => $id, 'name' => $name, 'is_admin' => $isAdmin]);

        } catch (TokenExpiredException $e) {
            Log::warning('JwtAuth: expired');
            return response()->json(['message' => 'Token expired'], Response::HTTP_UNAUTHORIZED);

        } catch (TokenInvalidException $e) {
            Log::warning('JwtAuth: invalid', ['err' => $e->getMessage()]);
            return response()->json(['message' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);

        } catch (JWTException $e) {
            Log::warning('JwtAuth: parse error', ['err' => $e->getMessage()]);
            return response()->json(['message' => 'Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}