<?php

namespace Database\Seeders;

use App\Models\MembershipCategory;
use App\Models\MembershipLevel;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Student', 'description' => 'Currently enrolled students.'],
            ['name' => 'Regular', 'description' => 'Standard individual membership.'],
            ['name' => 'Senior Citizen', 'description' => 'Membership for senior citizens.'],
            ['name' => 'Corporate', 'description' => 'Organization-sponsored membership.'],
        ] as $category) {
            MembershipCategory::updateOrCreate(
                ['name' => $category['name']],
                [...$category, 'slug' => str($category['name'])->slug(), 'is_active' => true],
            );
        }

        foreach ([
            ['name' => 'Basic', 'fee' => 500, 'validity_months' => 12, 'benefits' => "Member directory access\nStandard support"],
            ['name' => 'Silver', 'fee' => 1000, 'validity_months' => 12, 'benefits' => "Basic benefits\nEvent discounts"],
            ['name' => 'Gold', 'fee' => 2000, 'validity_months' => 12, 'benefits' => "Silver benefits\nPriority event access"],
            ['name' => 'Platinum', 'fee' => 3500, 'validity_months' => 24, 'benefits' => "Gold benefits\nVIP support\nExclusive programs"],
        ] as $level) {
            MembershipLevel::updateOrCreate(
                ['name' => $level['name']],
                [...$level, 'slug' => str($level['name'])->slug(), 'is_active' => true],
            );
        }
    }
}
