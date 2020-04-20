<?php

namespace App\Console\Commands;

use App\Objects\User;
use Illuminate\Console\Command;

class AddUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $name = $this->ask('What is the new username?');
        $email = $this->ask('What is the new user email?');
        $password = $this->secret('What is the new user password?');
        $admin = $this->choice('Is this user an admin?', ['Yes', 'No'], 1);

        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = $password;
        $user->admin = $admin;

    }
}
