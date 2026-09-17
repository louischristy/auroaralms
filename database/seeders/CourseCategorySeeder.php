<?php

namespace Database\Seeders;

use App\Models\CourseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Phishing & Email Security',
            'Social Engineering',
            'Password & Authentication',
            'Data Protection & Privacy',
            'Malware & Ransomware',
            'Mobile & Remote Work Security',
            'Physical Security & Workplace Safety',
            'Incident Response & Compliance',
            'Organization Specific',
        ];

        foreach ($categories as $index => $name) {
            CourseCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'sort_order' => $index,
                    'is_active' => true,
                ]
            );
        }
    }
}
