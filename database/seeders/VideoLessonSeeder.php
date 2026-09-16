<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VideoLessonSeeder extends Seeder
{
    public function run(): void
    {
        $videoLessons = $this->getVideoLessons();

        foreach ($videoLessons as $courseSlug => $lessonData) {
            $course = Course::where('slug', $courseSlug)
                ->whereNull('tenant_id')
                ->first();

            if (!$course) {
                $this->command->warn("Course not found: {$courseSlug}");
                continue;
            }

            $slug = Str::slug($lessonData['title']);

            Lesson::updateOrCreate(
                [
                    'course_id' => $course->id,
                    'slug' => $slug,
                ],
                [
                    'title' => $lessonData['title'],
                    'content_type' => 'video',
                    'video_url' => $lessonData['video_url'],
                    'content' => $lessonData['content'],
                    'duration_minutes' => $lessonData['duration_minutes'],
                    'sort_order' => 0,
                    'is_active' => true,
                ]
            );
        }
    }

    private function getVideoLessons(): array
    {
        return [
            // Course 1: Phishing Fundamentals
            'phishing-fundamentals' => [
                'title' => 'Video: Phishing Awareness Training',
                'video_url' => 'https://www.youtube.com/embed/1jfm2E_wvBo',
                'duration_minutes' => 8,
                'content' => $this->buildContent([
                    'Phishing attacks use deceptive emails and messages to trick you into revealing sensitive information',
                    'Always verify the sender\'s identity and hover over links before clicking',
                    'Look for urgency tactics, misspellings, and mismatched URLs as red flags',
                    'Report suspicious emails immediately rather than ignoring or deleting them',
                ]),
            ],

            // Course 2: Password Security & MFA (DB slug: password-security)
            'password-security-best-practices' => [
                'title' => 'Video: Password Security Essentials',
                'video_url' => 'https://www.youtube.com/embed/0Wd3JoUHXno',
                'duration_minutes' => 5,
                'content' => $this->buildContent([
                    'Strong passwords use a mix of length, complexity, and uniqueness across accounts',
                    'Password managers eliminate the need to memorize dozens of credentials',
                    'Multi-factor authentication adds a critical second layer of defense',
                    'Never reuse passwords — a breach on one site can compromise all your accounts',
                ]),
            ],

            // Course 3: Social Engineering Tactics
            'social-engineering-tactics' => [
                'title' => 'Video: What Is Social Engineering?',
                'video_url' => 'https://www.youtube.com/embed/Na40ZrNq0ww',
                'duration_minutes' => 6,
                'content' => $this->buildContent([
                    'Social engineering exploits human psychology rather than technical vulnerabilities',
                    'Common tactics include pretexting, baiting, quid pro quo, and tailgating',
                    'Attackers create a sense of urgency, authority, or trust to manipulate victims',
                    'Always verify requests through a separate communication channel before acting',
                ]),
            ],

            // Course 4: Data Protection Essentials
            'data-protection-essentials' => [
                'title' => 'Video: Data Protection and GDPR Awareness',
                'video_url' => 'https://www.youtube.com/embed/O5ol97elmBg',
                'duration_minutes' => 7,
                'content' => $this->buildContent([
                    'Classify data by sensitivity level — public, internal, confidential, and restricted',
                    'Handle personal data according to applicable regulations like GDPR and PDPA',
                    'Encrypt sensitive data both in transit and at rest',
                    'Report data breaches immediately — delays can result in regulatory penalties',
                ]),
            ],

            // Course 5: Malware & Ransomware Defense (DB slug: malware-awareness)
            'malware-awareness-prevention' => [
                'title' => 'Video: Phishing and Ransomware Defense',
                'video_url' => 'https://www.youtube.com/embed/D_yAYhjNE-0',
                'duration_minutes' => 5,
                'content' => $this->buildContent([
                    'Malware includes viruses, trojans, worms, spyware, and ransomware',
                    'Ransomware encrypts your files and demands payment — never pay the ransom',
                    'Keep software updated and avoid downloading files from untrusted sources',
                    'Maintain regular backups so you can recover without paying attackers',
                ]),
            ],

            // Course 6: Mobile & Remote Work Security (DB slug: mobile-device-security)
            'mobile-device-security' => [
                'title' => 'Video: Mobile Security Awareness',
                'video_url' => 'https://www.youtube.com/embed/ahNb6kA0Lms',
                'duration_minutes' => 5,
                'content' => $this->buildContent([
                    'Enable screen locks, biometrics, and remote wipe on all mobile devices',
                    'Avoid connecting to public Wi-Fi without a VPN for work-related tasks',
                    'Review app permissions regularly and only install apps from official stores',
                    'Report lost or stolen devices to IT immediately for remote lockdown',
                ]),
            ],

            // Course 7: Safe Internet & Browsing (DB slug: secure-email-practices — closest match for browsing/email safety)
            'secure-email-practices' => [
                'title' => 'Video: Cybersecurity Awareness for Safe Browsing',
                'video_url' => 'https://www.youtube.com/embed/ofRl5VSSgNk',
                'duration_minutes' => 10,
                'content' => $this->buildContent([
                    'Verify website URLs and look for HTTPS before entering any credentials',
                    'Avoid clicking on pop-ups, ads, or links from unknown sources',
                    'Use email authentication tools to verify sender legitimacy',
                    'Keep your browser and extensions updated to patch known vulnerabilities',
                ]),
            ],

            // Course 8: Physical Security Basics (DB slug: physical-security-awareness)
            'physical-security-awareness' => [
                'title' => 'Video: Physical Security and Tailgating Prevention',
                'video_url' => 'https://www.youtube.com/embed/Vh9g4BvzGU0',
                'duration_minutes' => 4,
                'content' => $this->buildContent([
                    'Never allow tailgating — each person must badge in individually',
                    'Lock your workstation every time you step away, even briefly',
                    'Secure sensitive documents and never leave them unattended',
                    'Challenge or report unfamiliar individuals in restricted areas',
                ]),
            ],

            // Course 9: Incident Reporting Procedures
            'incident-reporting-procedures' => [
                'title' => 'Video: Incident Response Fundamentals',
                'video_url' => 'https://www.youtube.com/embed/nNrkE6e3Vus',
                'duration_minutes' => 12,
                'content' => $this->buildContent([
                    'Report security incidents immediately — early detection limits damage',
                    'Know your organization\'s incident reporting channels and escalation paths',
                    'Document what happened, when, and what systems or data may be affected',
                    'Preserve evidence by not modifying or deleting potentially compromised files',
                ]),
            ],

            // Course 10: Spear Phishing & Whaling
            'spear-phishing-whaling' => [
                'title' => 'Video: Phishing Deception Tactics',
                'video_url' => 'https://www.youtube.com/embed/WNVTGTrWcvw',
                'duration_minutes' => 4,
                'content' => $this->buildContent([
                    'Spear phishing targets specific individuals using personal information gathered from research',
                    'Whaling attacks focus on executives and high-value targets with convincing pretexts',
                    'Verify unexpected requests from leadership through a separate communication channel',
                    'Be cautious of emails referencing personal details — attackers use social media for reconnaissance',
                ]),
            ],

            // Course 11: Email Link & Attachment Analysis
            'email-link-attachment-analysis' => [
                'title' => 'Video: Analyzing Phishing Emails',
                'video_url' => 'https://www.youtube.com/embed/6EmD3k3Pb8Y',
                'duration_minutes' => 6,
                'content' => $this->buildContent([
                    'Hover over links to inspect the actual URL before clicking',
                    'Be suspicious of unexpected attachments, especially .exe, .zip, and macro-enabled files',
                    'Use sandbox environments to safely analyze suspicious files',
                    'Check email headers and sender domains for signs of spoofing',
                ]),
            ],

            // Course 12: Pretexting & Impersonation
            'pretexting-impersonation' => [
                'title' => 'Video: Social Engineering Through Impersonation',
                'video_url' => 'https://www.youtube.com/embed/WufW-JF6Ub8',
                'duration_minutes' => 8,
                'content' => $this->buildContent([
                    'Pretexting involves creating a fabricated scenario to gain a victim\'s trust',
                    'Attackers commonly impersonate IT support, vendors, or senior management',
                    'Always verify identity through official channels before sharing sensitive information',
                    'Be wary of unsolicited calls or messages requesting urgent action or credentials',
                ]),
            ],

            // Course 13: Tailgating & Physical Social Engineering
            'tailgating-physical-social-engineering' => [
                'title' => 'Video: Tailgating Security Awareness',
                'video_url' => 'https://www.youtube.com/embed/Q-Id0YfCXyA',
                'duration_minutes' => 3,
                'content' => $this->buildContent([
                    'Tailgating occurs when an unauthorized person follows an employee into a secure area',
                    'Politely challenge anyone attempting to follow you through access-controlled doors',
                    'Report propped-open doors and malfunctioning access controls immediately',
                    'Social engineers exploit politeness — security protocols override social norms',
                ]),
            ],

            // Course 14: Credential Management & SSO
            'credential-management-sso' => [
                'title' => 'Video: Strong Password and Credential Management',
                'video_url' => 'https://www.youtube.com/embed/pHNVpjC5ZWE',
                'duration_minutes' => 4,
                'content' => $this->buildContent([
                    'Single Sign-On (SSO) reduces password fatigue while maintaining security',
                    'Use enterprise password managers for credentials that fall outside SSO',
                    'Service accounts and shared credentials require strict access controls and rotation',
                    'Never store passwords in plaintext, spreadsheets, or sticky notes',
                ]),
            ],

            // Course 15: Business Email Compromise Defense (reuse phishing video)
            'business-email-compromise-defense' => [
                'title' => 'Video: Recognizing Email-Based Attacks',
                'video_url' => 'https://www.youtube.com/embed/1jfm2E_wvBo',
                'duration_minutes' => 8,
                'content' => $this->buildContent([
                    'BEC attacks impersonate executives or vendors to authorize fraudulent transactions',
                    'Always verify financial requests through a secondary channel like a phone call',
                    'Watch for subtle email address changes such as domain misspellings',
                    'Implement dual-approval processes for wire transfers and payment changes',
                ]),
            ],

            // Course 16: MFA Deep Dive (reuse password security video)
            'multi-factor-authentication-deep-dive' => [
                'title' => 'Video: Understanding Multi-Factor Authentication',
                'video_url' => 'https://www.youtube.com/embed/0Wd3JoUHXno',
                'duration_minutes' => 5,
                'content' => $this->buildContent([
                    'MFA combines something you know, something you have, and something you are',
                    'Hardware security keys and authenticator apps are more secure than SMS codes',
                    'Attackers can bypass MFA through SIM swapping and real-time phishing proxies',
                    'Enable MFA on every account that supports it, starting with email and financial services',
                ]),
            ],

            // Course 17: Insider Threat Awareness (reuse social engineering video)
            'insider-threat-awareness' => [
                'title' => 'Video: Understanding Insider Threats',
                'video_url' => 'https://www.youtube.com/embed/Na40ZrNq0ww',
                'duration_minutes' => 6,
                'content' => $this->buildContent([
                    'Insider threats come from employees, contractors, or partners with authorized access',
                    'Warning signs include unusual data access patterns and after-hours activity',
                    'Both malicious intent and negligent behavior can lead to insider incidents',
                    'Report concerning behavior through your organization\'s designated channels',
                ]),
            ],

            // Course 18: Ransomware Prevention & Response (reuse malware/ransomware video)
            'ransomware-prevention-response' => [
                'title' => 'Video: Ransomware Awareness and Prevention',
                'video_url' => 'https://www.youtube.com/embed/D_yAYhjNE-0',
                'duration_minutes' => 5,
                'content' => $this->buildContent([
                    'Modern ransomware often exfiltrates data before encrypting it for double extortion',
                    'Regular offline backups are your best defense against ransomware demands',
                    'Phishing emails remain the most common ransomware delivery method',
                    'If infected, disconnect from the network immediately and contact IT security',
                ]),
            ],

            // Course 19: Remote Work Security (reuse mobile security video)
            'remote-work-security' => [
                'title' => 'Video: Securing Your Remote Workspace',
                'video_url' => 'https://www.youtube.com/embed/ahNb6kA0Lms',
                'duration_minutes' => 5,
                'content' => $this->buildContent([
                    'Use a VPN for all work-related internet activity when working remotely',
                    'Secure your home Wi-Fi with a strong password and WPA3 encryption',
                    'Keep work devices separate from personal devices when possible',
                    'Lock your screen and secure your workspace even at home',
                ]),
            ],

            // Course 20: Data Privacy Regulations (reuse GDPR video)
            'data-privacy-regulations-gdpr-pdpa' => [
                'title' => 'Video: Data Privacy and Regulatory Compliance',
                'video_url' => 'https://www.youtube.com/embed/O5ol97elmBg',
                'duration_minutes' => 7,
                'content' => $this->buildContent([
                    'GDPR, PDPA, and similar regulations govern how personal data must be collected and processed',
                    'Data subjects have rights including access, correction, deletion, and portability',
                    'Organizations must have a lawful basis for processing personal data',
                    'Non-compliance can result in significant fines and reputational damage',
                ]),
            ],

            // Course 21: Data Loss Prevention (reuse data protection video)
            'data-loss-prevention' => [
                'title' => 'Video: Preventing Data Loss',
                'video_url' => 'https://www.youtube.com/embed/O5ol97elmBg',
                'duration_minutes' => 7,
                'content' => $this->buildContent([
                    'DLP tools monitor and control data transfers to prevent unauthorized sharing',
                    'Common data leak channels include email, cloud storage, USB drives, and printing',
                    'Classify data properly so DLP policies can enforce appropriate protections',
                    'Work within DLP policies rather than trying to circumvent them',
                ]),
            ],

            // Course 22: Cloud Data Security (reuse cybersecurity awareness video)
            'cloud-data-security' => [
                'title' => 'Video: Cloud Security Fundamentals',
                'video_url' => 'https://www.youtube.com/embed/ofRl5VSSgNk',
                'duration_minutes' => 10,
                'content' => $this->buildContent([
                    'Understand the shared responsibility model — your provider secures infrastructure, you secure your data',
                    'Configure cloud storage permissions carefully to avoid accidental public exposure',
                    'Enable logging and monitoring for all cloud services',
                    'Use strong authentication and access controls for cloud platforms',
                ]),
            ],

            // Course 23: Fileless Malware & Advanced Threats (reuse ransomware video)
            'fileless-malware-advanced-threats' => [
                'title' => 'Video: Advanced Malware Threats',
                'video_url' => 'https://www.youtube.com/embed/D_yAYhjNE-0',
                'duration_minutes' => 5,
                'content' => $this->buildContent([
                    'Fileless malware operates in memory and leaves minimal traces on disk',
                    'These attacks often exploit legitimate system tools like PowerShell and WMI',
                    'Keep endpoint detection and response (EDR) tools updated and active',
                    'Disable unnecessary scripting engines and enforce application whitelisting',
                ]),
            ],

            // Course 24: IoT & Smart Device Security (reuse mobile security video)
            'iot-smart-device-security' => [
                'title' => 'Video: IoT and Device Security',
                'video_url' => 'https://www.youtube.com/embed/ahNb6kA0Lms',
                'duration_minutes' => 5,
                'content' => $this->buildContent([
                    'IoT devices often ship with default credentials that must be changed immediately',
                    'Segment IoT devices on a separate network from critical business systems',
                    'Keep firmware updated — many IoT vulnerabilities are patched through updates',
                    'Disable unnecessary features and services on connected devices',
                ]),
            ],

            // Course 25: Workplace Safety & Secure Disposal (reuse physical security video)
            'workplace-safety-secure-disposal' => [
                'title' => 'Video: Physical Security in the Workplace',
                'video_url' => 'https://www.youtube.com/embed/Vh9g4BvzGU0',
                'duration_minutes' => 4,
                'content' => $this->buildContent([
                    'Shred or securely destroy documents containing sensitive information',
                    'Wipe hard drives and storage media before disposal or recycling',
                    'Maintain a clean desk policy to prevent unauthorized viewing of information',
                    'Follow your organization\'s asset disposal procedures for all equipment',
                ]),
            ],

            // Course 26: Incident Response Planning (reuse incident response video)
            'incident-response-planning' => [
                'title' => 'Video: Building an Incident Response Plan',
                'video_url' => 'https://www.youtube.com/embed/nNrkE6e3Vus',
                'duration_minutes' => 12,
                'content' => $this->buildContent([
                    'An effective incident response plan covers preparation, detection, containment, eradication, and recovery',
                    'Assign clear roles and responsibilities before an incident occurs',
                    'Conduct regular tabletop exercises to test and improve your response plan',
                    'Document lessons learned after every incident to strengthen future defenses',
                ]),
            ],

            // Course 27: Compliance Frameworks & Audits (reuse GDPR video)
            'compliance-frameworks-audits' => [
                'title' => 'Video: Compliance and Audit Readiness',
                'video_url' => 'https://www.youtube.com/embed/O5ol97elmBg',
                'duration_minutes' => 7,
                'content' => $this->buildContent([
                    'Common frameworks include ISO 27001, SOC 2, NIST, and PCI DSS',
                    'Audits verify that security controls are implemented and operating effectively',
                    'Maintain documentation and evidence of compliance activities year-round',
                    'Every employee plays a role in maintaining the organization\'s compliance posture',
                ]),
            ],
        ];
    }

    private function buildContent(array $takeaways): string
    {
        $items = implode("\n", array_map(fn ($t) => "    <li>{$t}</li>", $takeaways));

        return <<<HTML
<div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
    <h3 class="text-blue-800 font-semibold mb-2">📹 Video Lesson</h3>
    <p class="text-blue-700 text-sm">Watch the video above, then review the key takeaways below before proceeding to the text lessons.</p>
</div>
<h3>Key Takeaways</h3>
<ul>
{$items}
</ul>
HTML;
    }
}
