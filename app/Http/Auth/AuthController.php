<?php

namespace App\Http\Auth;

use App\Helper\Auth\RegisterHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\EmailVerifyRequest;
use App\Http\Requests\PhoneVerificationRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->client = DB::table('oauth_clients')
            ->where('id', 2)->first();
    }

    /**
     * Registration for customers & sellers.
     *
     * @param RegisterRequest $request
     * @return array
     */
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);

        $user = User::create($data);

        $user->email
            ? $user->sendEmailVerificationNotification()
            : $user->generatePhoneCode();

        return [
            'user' => $user
        ];
    }

    /**
     * Login method for customers & sellers.
     *
     * @param Request $request
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = RegisterHelper::requestKey($request)->except('login', 'remember');

        if (!Auth::attempt(
            $credentials,
            $request->has('remember')
        )) {
            return response()->json(['message' => 'Incorrect Credentials'], 422);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Incorrect Credentials'], 422);
        }

//        if (!$user->verified()) {
//            if ($request->user()->hasPhone()) {
//                return response()->json([
//                    'identifier_type' => 'Phone',
//                    'identifier' => $request->user()->phone,
//                    'id' => $request->user()->id
//                ], 403);
//            } else {
//                $request->user()->sendEmailVerificationNotification();
//                return response()->json([
//                    'identifier_type' => 'Email',
//                    'identifier' => $request->user()->email
//                ],403);
//            }
//        }

        $login = RegisterHelper::getKey($request->login);

        $request->request->add(
            [
                'grant_type' => 'password',
                'client_id' => $this->client->id,
                'client_secret' => $this->client->secret,
                'username' => $credentials[$login],
                'password' => $credentials['password'],
                'scope' => '*',
            ]
        );

        $tokenRequest = $request->create(
            config('app.url') . '/oauth/token',
            'post'
        );

        $instance = json_decode(Route::dispatch($tokenRequest)->getContent());

        return response()->json(
            [
                'user' => $user,
                'access_token' => $instance->access_token,
                'refresh_token' => $instance->refresh_token,
            ]
        );
    }

    /**
     * here comes email click route and after verification link with user, email_verification_at fills.
     * @param EmailVerifyRequest $request
     */
    public function verifyEmail(EmailVerifyRequest $request, $id)
    {
        $request->fulfillEmail();

        return view('email.successfully');

//        return \response()->json([
//            'status' => 'success',
//            'access_token' => $this->userToken(User::find($id))
//        ]);
    }

    /**
     * Verify user phone number.
     * @param PhoneVerificationRequest $request
     * @return JsonResponse
     */
    public function verifyPhone(PhoneVerificationRequest $request, User $user): JsonResponse
    {
        $request->fulfillPhone();

        return response()->json([
            'status' => 'success',
            'access_token' => $this->userToken($user),
        ]);
    }

    private function userToken($user)
    {
        Auth::login($user, true);

        return $user->createToken('Api auth')->accessToken;
    }

    /**
     * Send Password reset link to user.
     * @param Request $request
     * @return Application|ResponseFactory|JsonResponse|Response
     */
    public function passwordReset(Request $request): Response|JsonResponse|Application|ResponseFactory
    {
        $requestKey = RegisterHelper::requestKey($request);

        return $requestKey->exists('email') ?
            $this->sendEmailResetLink($requestKey) : $this->sendPhoneResetCode($requestKey);
    }

    /**
     * Send Password reset link to user.
     * @param Request $request
     * @return JsonResponse
     */
    private function sendEmailResetLink(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['email' => __($status)]);
        }

        return response()->json(['status' => __($status)], 404);
    }

    /**
     * Generate phone code and send to user to reset password.
     * @param Request $request
     * @return JsonResponse
     */
    public function sendPhoneResetCode(Request $request): JsonResponse
    {
        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['phone' => $request->phone, 'message' => 'Incorrect credentials'], 404);
        }

        $user->generatePhoneCode();

        return response()->json(['phone' => $request->phone, 'message' => 'Code Sent to user']);
    }

    /**
     * Update password from mail link.
     */
    public function updatePasswordFromLink(ResetPasswordRequest $request): Response|Application|ResponseFactory
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill(
                    [
                        'password' => Hash::make($password)
                    ]
                )->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return response(['status' => 'Problem occurred during operation'], 422);
        }

        return response(['status' => 'success']);
    }

    /**
     * Update password with phone number.
     * @param UpdatePasswordRequest $request
     * @return JsonResponse
     */
    public function updatePasswordFromPhone(UpdatePasswordRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->phone)->first();

        if ($user->otp && ($user->otp->code == $request->code)) {
            $user->update(
                [
                    'password' => bcrypt($request->password),
                ]
            );

            $user->otp->delete();

            return \response()->json(['status' => true, 'message' => 'success']);
        }

        return response()->json(['status' => false, 'message' => 'Failed, please try again']);
    }

    /**
     * Check if otp is correct
     */
    public function checkOtp(Request $request): JsonResponse
    {
        $user = User::where('phone', $request->phone)->first();

        if ($user->otp && ($user->otp->code == $request->code)) {
            return response()->json(
                [
                    'status' => true,
                    'message' => 'Otp code is correct',
                ]
            );
        }

        return response()->json(
            [
                'status' => false,
                'message' => 'Otp code is not correct',
            ],
            401
        );
    }

    /**
     * Revoke current user token that causes log out
     *
     * @return array
     */
    public function logout(): array
    {
        return [
            'status' => Auth::user()
                ->token()
                ->revoke()
        ];
    }

    /**
     * Callback from social network.
     * @param Request $request
     * @return JsonResponse
     */
    public function socialCallback(Request $request): JsonResponse
    {
        try {
            $user = Socialite::driver($request->social_type)->userFromToken($request->access_token);

            if (!$user->getEmail()) {
                return \response()->json(
                    ['message' => 'you have to specify email address in your facebook account to sign in Black Mount!'],
                    401
                );
            }

            $user = User::firstOrCreate(
                [
                    'email' => $user->getEmail()
                ],
                [
                    'email' => $user->getEmail(),
                    'password' => bcrypt(Str::random(24)),
                    'first_name' => $user?->user['given_name'] ?? '',
                    'last_name' => $user?->user['family_name'] ?? '',
                ]
            );

            $user->markEmailAsVerified();

            Auth::login($user, true);

            return \response()->json(
                [
                    'status' => $user,
                    'token' => $user->createToken('Api auth')->accessToken
                ]
            );
        } catch (\Exception $exception) {
            return \response()->json(['message' => $exception->getMessage()], 501);
        }
    }

}
