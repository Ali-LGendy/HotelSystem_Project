
use Cog\Laravel\Ban\Http\Middleware\ForbidBannedUser::class;

protected $routeMiddleware = [
    // ... other middlewares
    'role' => \App\Http\Middleware\ReceptionistMiddleware::class,
    'check.ban' => ForbidBannedUser::class,
    'ban' => \Cog\Laravel\Ban\Http\Middleware\CheckUserBanned::class,
];