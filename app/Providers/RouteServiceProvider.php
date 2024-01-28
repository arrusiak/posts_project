<?php

namespace App\Providers;

use App\Exceptions\Http\InvalidFreezeCommentException;
use App\Exceptions\Http\InvalidFreezeException;
use App\Exceptions\Http\InvalidUserException;
use App\Models\Freeze;
use App\Models\FreezeComment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });

        $this->configureBindings();
    }

    protected function configureBindings(): void
    {
        Route::bind('post_id', function ($value) {
            $post = Post::find($value);

            if (!$post) {
                Log::error("Failed to get post", ['post_id' => $value]);
                throw new NotFoundHttpException();
            }

            return $post;
        });
    }
}
