<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Role::class => RolePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (! $this->app->routesAreCached()) {
            Passport::tokensExpireIn(now()->addDays(15));
            Passport::refreshTokensExpireIn(now()->addDays(30));
            Passport::personalAccessTokensExpireIn(now()->addMonths(6));
        }

        if ($this->app->request->header('Password-reset')) {
            ResetPassword::createUrlUsing(function ($user, string $token) {
                return  config('mail.reset') . "{$token}/{$user->email}";
            });
        }

        VerifyEmail::createUrlUsing(function ($notifiable) {
//            $frontendUrl = config('main.front_url');
            $backendUrl = config('main.back_url');

            $id = $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());

            $verifyUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $id,
                    'hash' => $hash,
                ]
            );

            $query = parse_url($verifyUrl, PHP_URL_QUERY);

            return $backendUrl . "/email/verify/{$id}/{$hash}?{$query}";
//            return $frontendUrl . "/register/verification/{$id}/{$hash}?{$query}";
        });

        VerifyEmail::toMailUsing(function (User $user, string $verificationUrl) {
            return (new MailMessage)
                ->subject("ელფოსტის დადასტურება.")->view('email.verify', ['verifyUrl' => $verificationUrl]);
        });

        // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->email == 'super.admin@wl.com' ? true : null;
        });
    }
}
