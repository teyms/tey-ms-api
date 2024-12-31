<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
// use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Log;


class GoogleAuthController extends Controller
{
    public function handleGoogleCallback(Request $request)
    {
        // Validate the token received from the frontend
        $request->validate(['token' => 'required|string']);

        // Use Socialite to retrieve user details with the token
        /** @var \Laravel\Socialite\Two\GoogleProvider  */
        $driver = Socialite::driver('google');
        $googleUser = $driver->stateless()->userFromToken($request->input('token'));

        Log::info('$googleUser ' . json_encode($googleUser));

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
            ]
        );

        // Generate a token for API authentication
        $token = $user->createToken('Personal Access Token')->accessToken;
        $expiration = Carbon::now()->addDay();  // Expires in 1days
        $user->tokens->last()->update(['expires_at' => $expiration]);

        $result = [
            'success'   => 1,
            'msg'       => 'Successfully Authenticated with Google Auth',
            'data'      => [
                'googleToken' => $token,
                'token' => $user->tokens->last()->token,
                'user'  => $user->makeHidden(['tokens'])
            ]
        ];
        $result_code = 200;
        
        return response()->json($result, $result_code);
    }

    public function logout(Request $request){
        try{
            $params = [];
            if($request->has('email') && isset($request->email)) $params['email'] = $request->email;
            if($request->has('token') && isset($request->token)) $params['token'] = $request->token;

            $latest_token = DB::table('users')
                        ->leftJoin('personal_access_tokens', 'users.id', '=', 'personal_access_tokens.tokenable_id')
                        ->where('personal_access_tokens.created_at', '=', function ($query) {
                            $query->selectRaw('MAX(created_at)')
                                ->from('personal_access_tokens')
                                ->whereColumn('tokenable_id', 'users.id');
                        })
                        // ->select('users.*', 'personal_access_tokens.*')  // Selecting user data and the token data
                        ->select('personal_access_tokens.*')  // Selecting user data and the token data
                        ->first();

            if(!$latest_token){
                $result = [
                    'success'   => 0,
                    'msg'       => 'Logout failed',
                    'data'      => null
                ];
                $result_code = 422;
                return response()->json($result, $result_code);
                
            }

            if($latest_token->expires_at && Carbon::parse($latest_token->expires_at)->isPast()){
                $result = [
                    'success'   => 1,
                    'msg'       => 'Logged out successfully, due to token expired',
                    'data'      => null
                ];
                $result_code = 200;
                return response()->json($result, $result_code);
            }

            // Get the token ID
            $tokenId = $latest_token->id; // This is the ID of the latest token

            // Update the expiration time to now
            DB::table('personal_access_tokens')
                ->where('id', $tokenId)
                ->update(['expires_at' => Carbon::now()]);

            $result = [
                'success'   => 1,
                'msg'       => 'Logged out successfully',
                'data'      => null
            ];
            $result_code = 200;
            return response()->json($result, $result_code);


        }catch(Exception $error){
            $result = [
                'success'   => 0,
                'msg'       => 'Logout failed with Error',
                'data'      => null
            ];
            $result_code = 422;
            return response()->json($result, $result_code);
        }

    }

    /*
    public function refreshGoogleAccessToken($email, $refreshToken)
    {
        // Create a Guzzle client instance
        $client = new Client();

        try {
            // Send the request to Google's OAuth token endpoint
            $response = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'client_id' => env('GCP_CLIENT_ID'),
                    'client_secret' => env('GCP_CLIENT_SECRET'),
                    'refresh_token' => $refreshToken,
                    'grant_type' => 'refresh_token',
                ],
            ]);

            // Decode the JSON response
            $data = json_decode($response->getBody()->getContents(), true);

            $user = User::Where('email', $email)->firstOrDefault();
            if($user){
                // update new token??
            }

            // Check if the response contains an access_token
            if (isset($data['access_token'])) {
                return $data; // Contains new access_token
            }

            return null; // No access_token in response
        } catch (RequestException $e) {
            // Handle exceptions, such as network issues, 4xx/5xx responses
            if ($e->hasResponse()) {
                $errorResponse = $e->getResponse();
                $errorBody = $errorResponse->getBody()->getContents();
                // Log or handle the error response here
                Log::error('Error refreshing Google access token: ' . $errorBody);
            }

            return null;
        }
    }
    */

    // // public function getGoogleUserFromToken(Request $request, $email)
    // // {
    // //     // Assume we have the refresh token stored or received from the request
    // //     $refreshToken = $request->input('refresh_token'); 

    // //     // Refresh the access token
    // //     $newTokens = $this->refreshGoogleAccessToken($email, $refreshToken);

    // //     if ($newTokens) {
    // //         // Use the new access token with Socialite
    // //         /** @var \Laravel\Socialite\Two\GoogleProvider  */
    // //         $driver = Socialite::driver('google');
    // //         $googleUser = $driver->stateless()->userFromToken($newTokens['access_token']);
            
    // //         return response()->json($googleUser);
    // //     }

    // //     return response()->json(['error' => 'Unable to refresh access token'], 400);
    // // }

    /*
    public function revokeGoogleTokens(User $user)
    {
        // Create a new Guzzle client instance
        $client = new Client();
    
        // Revoke the access token
        $response = $client->post('https://oauth2.googleapis.com/revoke', [
            'form_params' => [
                'token' => $user->google_access_token,
            ]
        ]);
    
        if ($response->getStatusCode() === 200) {
            // Optionally revoke the refresh token as well, if needed
            $client->post('https://oauth2.googleapis.com/revoke', [
                'form_params' => [
                    'token' => $user->google_refresh_token,
                ]
            ]);
    
            // Optionally, you could log or handle the response further
        } else {
            // Handle errors, if any (Google could return an error if the token is invalid)
            // You can log the error or notify the user
            Log::error('Failed to revoke Google token');
        }
    }
    */

    /*
    private function verifyGoogleToken($token){
        Log::info('$googleUser->token  $token ' . $token);
        Log::info('$googleUser->token  !empty($token) ' . !empty($token));

        if($token && !empty($token)){
            // Verify the token with Google's API
            $http = new Client();
            try {
                $response = $http->get('https://oauth2.googleapis.com/tokeninfo', [
                    'query' => ['access_token' => $token],
                ]);

                $googleUser = json_decode($response->getBody(), true);

                Log::info('$googleUser_verifyGoogleToken' . json_encode($googleUser));

                // Check for required fields
                if (!isset($googleUser['email'])) {
                    $result = [
                        'success'   => 0,
                        'msg'       => 'Unauthorized',
                        'data'      => false
                    ];
                    // return response()->json(['error' => 'Unauthorized'], 401);            
                    return json_encode($result);   
                }

                // Optionally, match the email with your database
                $user = User::where('email', $googleUser['email'])->first();

                if (!$user) {
                    $result = [
                        'success'   => 0,
                        'msg'       => 'Unauthorized',
                        'data'      => false
                    ];
                return json_encode($result);               
                    // return response()->json(['error' => 'Unauthorized'], 401);
                }

                // Add the authenticated user to the request
                // $request->merge(['user' => $user]);
                // return $next($request);     

                $result = [
                    'success'   => 1,
                    'msg'       => 'Successfully verified Google token',
                    'data'      => true
                ];
                return json_encode($result);                   

            } catch (\Exception $e) {
                Log::info('$Exception e' . $e);

                $result = [
                    'success'   => 0,
                    'msg'       => 'Invalid Google token ' . $e,
                    'data'      => false
                ];
                return json_encode($result);   
                // return response()->json(['error' => 'Invalid Google token'], 401);
            }  
        }
        $result = [
            'success'   => 0,
            'msg'       => 'Invalid Input Token or empty',
            'data'      => false
        ];
        return json_encode($result); 
    }
    */
}
