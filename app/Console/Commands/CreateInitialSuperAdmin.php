<?php

namespace App\Console\Commands;

use App\Models\SuperAdmin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class CreateInitialSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:super-admin:create
                            {--name= : Super admin name}
                            {--email= : Super admin email}
                            {--password= : Super admin password}
                            {--update : Update an existing super admin with the same email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an initial super admin account for central app access';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Super admin name');
        $email = $this->option('email') ?: $this->ask('Super admin email');
        $password = $this->option('password') ?: $this->secret('Super admin password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $existing = SuperAdmin::query()->where('email', $email)->first();

        if ($existing !== null && ! $this->option('update')) {
            $this->error('A super admin with this email already exists. Use --update to modify it.');

            return self::FAILURE;
        }

        if ($existing !== null) {
            $existing->update([
                'name' => $name,
                'password' => $password,
            ]);

            $this->info('Super admin updated successfully.');

            return self::SUCCESS;
        }

        SuperAdmin::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $this->info('Super admin created successfully.');

        return self::SUCCESS;
    }
}
