<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectUser;

class ProjectUserSeeder extends Seeder
{
    public function run()
    {
        ProjectUser::factory()->count(15)->create();
    }
}
