<?php

namespace App\Console\Commands;

use Firebase\JWT\JWT;
use Illuminate\Console\Command;

class GenerateApiToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:generate-token 
                            {url? : The URL this token is valid for}
                            {--expires=3600 : Token expiration time in seconds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a JWT API token for testing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $url = $this->argument('url') ?? 'http://localhost:8000/api/*';
        $expires = (int) $this->option('expires');
        
        $payload = [
            'url' => $url,
            'iat' => time(),
            'exp' => time() + $expires,
            'iss' => config('app.name'),
        ];

        $secretKey = config('sanctum.api_auth_secret_key');
        $token = JWT::encode($payload, $secretKey, 'HS256');

        $this->info('API Token generated successfully!');
        $this->line('');
        $this->line('Token: ' . $token);
        $this->line('');
        $this->line('Usage:');
        $this->line('  Header: X-BugTrackApi: ' . $token);
        $this->line('');
        $this->line('Example curl:');
        $this->line('  curl -H "X-BugTrackApi: ' . $token . '" http://localhost:8000/api/user');
        $this->line('');
        $this->line('Token expires: ' . date('Y-m-d H:i:s', time() + $expires));

        return self::SUCCESS;
    }
} 