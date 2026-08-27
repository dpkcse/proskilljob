<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Candidate\CandidateResource;
use App\Http\Resources\Company\CompanyResource;
use App\Models\User;
use Firebase\Auth\Token\Exception\InvalidToken;
use Illuminate\Http\Request;
use Kreait\Firebase\Contract\Auth;
use Kreait\Firebase\Factory;
use Throwable;

class SocialAuthController extends Controller
{
    public function socialAuthentication(Request $request)
    {

        $request->validate([
            'firebaseToken' => 'required',
            'provider' => 'required|in:google,facebook',
            'actionKey' => 'required|in:login,registration',
        ]);

        $providerArray = [
            'google' => 'google.com',
            'facebook' => 'facebook.com',
        ];

        $accessToken = $request->firebaseToken;
        $provider = $providerArray[$request->provider];

        try {
            $auth = $this->firebaseAuth();
            if (! $auth) {
                return response()->json([
                    'data' => [
                        'message' => 'Social authentication is temporarily unavailable.',
                    ],
                ], 503);
            }

            $signInResult = $auth->signInWithIdpAccessToken($provider, $accessToken, $redirectUrl = null, $oauthTokenSecret = null, $linkingIdToken = null, $rawNonce = null);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'data' => [
                    'message' => 'Social authentication is temporarily unavailable.',
                ],
            ], 503);
        }

        $idTokenString = $signInResult->idToken();

        try {
            $verifiedIdToken = $auth->verifyIdToken($idTokenString);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'data' => [
                    'message' => 'Unauthorized - Can\'t parse the token: '.$e->getMessage(),
                ],
            ], 401);
        } catch (InvalidToken $e) {
            return response()->json([
                'data' => [
                    'message' => 'Unauthorized - Token is invalide: '.$e->getMessage(),
                ],
            ], 401);
        } catch (Throwable $e) {
            return response()->json([
                'data' => [
                    'message' => $e->getMessage(),
                ],
            ], 401);
        }

        // Retrieve the UID (User ID) from the verified Firebase credential's token
        $uid = $verifiedIdToken->claims()->get('sub');

        // Retrieve the user model linked with the Firebase UID
        $user = User::where('firebase_uid', $uid)->first();

        try {

            if (! $user) {
                if ($request->actionKey == 'login') {
                    return response()->json([
                        'data' => [
                            'message' => 'User does not exist, please register an account first',
                        ],
                    ]);
                } elseif ($request->actionKey == 'registration') {

                    if ($request->has('role')) {
                        $user = User::create([
                            'firebase_uid' => $uid,
                            'role' => $request->input('role'),
                            'name' => $verifiedIdToken->claims()->get('name'),
                            'email' => $verifiedIdToken->claims()->get('email'),
                        ]);
                    } else {
                        return response()->json([
                            'data' => [
                                'message' => 'Role is missing',
                            ],
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'data' => [
                    'message' => 'Error creating user: '.$e->getMessage(),
                ],
            ], 500);
        }

        $token = $user->createToken('naxas')->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'message' => 'User data retrieved successfully',
                'user' => $user->role == 'candidate' ? new CandidateResource($user->candidate) : new CompanyResource($user->company),
            ],
        ], 200);

    }

    private function firebaseAuth(): ?Auth
    {
        $credentials = config('firebase.projects.'.config('firebase.default', 'app').'.credentials');
        $legacyCredentials = storage_path('firebase_credentials.json');

        if (! $credentials && is_file($legacyCredentials)) {
            $credentials = $legacyCredentials;
        }

        if (! $credentials) {
            return null;
        }

        return (new Factory)->withServiceAccount($credentials)->createAuth();
    }
}
