<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use Illuminate\Database\Seeder;

class CertificateTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Classic Professional',
                'description' => 'Traditional double-border certificate with serif fonts and corner flourishes.',
                'is_default' => true,
                'config' => [
                    'border_style' => 'classic',
                    'border_color' => '#2B4C7E',
                    'accent_color' => '#3A7BD5',
                    'background_color' => '#ffffff',
                    'font_style' => 'classic',
                    'custom_title' => 'Certificate of Completion',
                    'custom_subtitle' => 'Cybersecurity Awareness Training',
                    'show_score' => true,
                    'show_logo' => true,
                    'show_certificate_number' => true,
                    'show_expiry' => true,
                    'show_date_issued' => true,
                    'show_course_duration' => false,
                    'show_signatures' => false,
                    'signatures' => [],
                    'issuing_authority' => '',
                    'footer_text' => '',
                    'decorative_elements' => 'corners',
                ],
            ],
            [
                'name' => 'Modern Clean',
                'description' => 'Minimalist design with sans-serif fonts and clean lines.',
                'is_default' => false,
                'config' => [
                    'border_style' => 'modern',
                    'border_color' => '#1a1a2e',
                    'accent_color' => '#16213e',
                    'background_color' => '#ffffff',
                    'font_style' => 'modern',
                    'custom_title' => 'Certificate of Achievement',
                    'custom_subtitle' => 'Security Training Program',
                    'show_score' => true,
                    'show_logo' => true,
                    'show_certificate_number' => true,
                    'show_expiry' => true,
                    'show_date_issued' => true,
                    'show_course_duration' => true,
                    'show_signatures' => false,
                    'signatures' => [],
                    'issuing_authority' => '',
                    'footer_text' => '',
                    'decorative_elements' => 'none',
                ],
            ],
            [
                'name' => 'Executive with Signatures',
                'description' => 'Formal certificate with ornate borders, seal, and signature lines.',
                'is_default' => false,
                'config' => [
                    'border_style' => 'ornate',
                    'border_color' => '#8B4513',
                    'accent_color' => '#B8860B',
                    'background_color' => '#fffef5',
                    'font_style' => 'elegant',
                    'custom_title' => 'Certificate of Completion',
                    'custom_subtitle' => 'Professional Cybersecurity Training',
                    'show_score' => false,
                    'show_logo' => true,
                    'show_certificate_number' => true,
                    'show_expiry' => true,
                    'show_date_issued' => true,
                    'show_course_duration' => false,
                    'show_signatures' => true,
                    'signatures' => [
                        ['name' => 'Training Director', 'title' => 'Head of Security Training'],
                        ['name' => 'CISO', 'title' => 'Chief Information Security Officer'],
                    ],
                    'issuing_authority' => 'Auroara Security Training Institute',
                    'issuing_authority_title' => 'Accredited Cybersecurity Training Provider',
                    'footer_text' => 'This certificate is valid for 12 months from the date of issue.',
                    'decorative_elements' => 'seal',
                ],
            ],
        ];

        foreach ($templates as $data) {
            CertificateTemplate::withoutGlobalScopes()->updateOrCreate(
                ['name' => $data['name'], 'tenant_id' => null],
                $data
            );
        }
    }
}
