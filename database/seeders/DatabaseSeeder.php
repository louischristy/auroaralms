<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            PlatformSettingsSeeder::class,
            DemoTenantSeeder::class,
            CourseCategorySeeder::class,
            CourseSeeder::class,
            CourseSeederBatch2::class,
            CourseSeederBatch3::class,
            PhishingTemplateSeeder::class,
            BadgeSeeder::class,
            VideoLessonSeeder::class,
        ]);
    }
}
