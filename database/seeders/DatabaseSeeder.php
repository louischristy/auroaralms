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
            CourseSeederBatch4::class,
            CourseSeederBatch5::class,
            CourseSeederBatch6::class,
            CourseSeederBatch7::class,
            CourseSeederBatch8::class,
            CourseSeederBatch9::class,
            CourseSeederBatch10::class,
            CourseSeederBatch11::class,
            PhishingTemplateSeeder::class,
            PhishingTemplateSeederBatch2::class,
            PhishingTemplateSeederBatch3::class,
            BadgeSeeder::class,
            VideoLessonSeeder::class,
        ]);
    }
}
