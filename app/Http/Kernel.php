protected $routeMiddleware = [
    // ... other middlewares
    'role' => \App\Http\Middleware\ReceptionistMiddleware::class,
];