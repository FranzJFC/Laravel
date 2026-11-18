<?php

namespace Database\Seeders;

use App\Models\MembershipCategory;
use Illuminate\Database\Seeder;

class MembershipCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['Regular', 'Standard', 500], ['Student', 'Associate', 250]] as [$name, $level, $fee]) {
            MembershipCategory::updateOrCreate(['name' => $name], ['slug' => strtolower($name), 'level' => $level, 'fee' => $fee, 'is_active' => true]);
        }
    }
}
