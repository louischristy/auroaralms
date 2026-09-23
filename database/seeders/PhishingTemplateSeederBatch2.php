<?php

namespace Database\Seeders;

use App\Models\PhishingTemplate;
use Illuminate\Database\Seeder;

class PhishingTemplateSeederBatch2 extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Delivery Notification - Package Pending',
                'scenario_type' => 'malware_link',
                'subject' => 'Your package could not be delivered - Action required',
                'sender_name' => 'Delivery Notifications',
                'sender_email' => 'notifications@delivery-tracking-center.com',
                'difficulty' => 'easy',
                'body_html' => '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
<div style="background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0;">
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="font-size: 40px;">📦</div>
        <h2 style="color: #1a1a1a; margin: 10px 0 5px;">Delivery Attempt Failed</h2>
        <p style="color: #888; font-size: 13px; margin: 0;">Tracking Number: PKG-2024-991847</p>
    </div>
    <p style="color: #555; line-height: 1.6;">Dear Customer,</p>
    <p style="color: #555; line-height: 1.6;">We attempted to deliver your package today, but <strong>no one was available to receive it</strong>. The package is currently being held at our sorting facility.</p>
    <div style="background: #fef3c7; border-radius: 6px; padding: 12px 16px; margin: 16px 0;">
        <p style="color: #92400e; font-size: 13px; margin: 0;"><strong>Important:</strong> If delivery is not rescheduled within 48 hours, the package will be returned to sender.</p>
    </div>
    <p style="color: #555; line-height: 1.6;">Please click below to confirm your delivery address and choose a new delivery time:</p>
    <div style="text-align: center; margin: 25px 0;">
        <a href="#" style="background: #f59e0b; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">Reschedule Delivery</a>
    </div>
    <p style="color: #888; font-size: 12px;">This is an automated notification. Please do not reply directly to this email.</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="color: #aaa; font-size: 11px; text-align: center;">Delivery Tracking Center | Customer Support</p>
</div>
</body>
</html>',
                'landing_page_html' => '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Reschedule Delivery</title></head>
<body style="font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
<div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 450px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="font-size: 40px; margin-bottom: 8px;">📦</div>
        <h2 style="color: #1a1a1a; margin: 0 0 4px;">Reschedule Your Delivery</h2>
        <p style="color: #666; font-size: 14px; margin: 0;">Confirm your address to proceed</p>
    </div>
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; text-align: center;">
        <div style="font-size: 32px; margin-bottom: 8px;">🎓</div>
        <h3 style="color: #166534; margin: 0 0 8px;">This Was a Phishing Simulation</h3>
        <p style="color: #15803d; font-size: 14px; line-height: 1.6; margin: 0;">You clicked on a simulated phishing link. In a real attack, this page could have stolen your personal information or installed malware on your device.</p>
    </div>
    <div style="margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 6px;">
        <h4 style="color: #1a1a1a; margin: 0 0 8px; font-size: 14px;">🔍 Red Flags You Missed:</h4>
        <ul style="color: #555; font-size: 13px; line-height: 1.8; margin: 0; padding-left: 20px;">
            <li>The sender email domain (delivery-tracking-center.com) is not a real shipping company</li>
            <li>The email creates urgency with a 48-hour deadline</li>
            <li>Legitimate delivery services use your name, not "Dear Customer"</li>
            <li>Real tracking numbers can be verified on the carrier\'s official website</li>
        </ul>
    </div>
</div>
</body>
</html>',
            ],
            [
                'name' => 'HR Benefits Enrollment',
                'scenario_type' => 'credential_harvest',
                'subject' => 'Open Enrollment Period Ending - Update Your Benefits',
                'sender_name' => 'Human Resources',
                'sender_email' => 'benefits@hr-portal-secure.com',
                'difficulty' => 'medium',
                'body_html' => '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
<div style="background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0;">
    <div style="display: flex; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #6366f1; padding-bottom: 15px;">
        <div>
            <h2 style="color: #1a1a1a; margin: 0;">Benefits Open Enrollment</h2>
            <p style="color: #6366f1; font-size: 13px; margin: 4px 0 0;">Human Resources Department</p>
        </div>
    </div>
    <p style="color: #555; line-height: 1.6;">Dear Team Member,</p>
    <p style="color: #555; line-height: 1.6;">This is a reminder that the <strong>annual benefits open enrollment period closes this Friday</strong>. If you have not yet reviewed and updated your elections, please do so as soon as possible.</p>
    <p style="color: #555; line-height: 1.6;">Changes available during this period include:</p>
    <ul style="color: #555; line-height: 1.8;">
        <li>Health, dental, and vision plan selection</li>
        <li>Life insurance and disability coverage updates</li>
        <li>Flexible Spending Account (FSA) contributions</li>
        <li>Dependent additions or removals</li>
        <li>401(k) contribution adjustments</li>
    </ul>
    <p style="color: #555; line-height: 1.6;"><strong>Important:</strong> If no changes are submitted by the deadline, your current elections will roll over. However, if you need to add dependents or change plan tiers, you must act now.</p>
    <div style="text-align: center; margin: 25px 0;">
        <a href="#" style="background: #6366f1; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">Access Benefits Portal</a>
    </div>
    <p style="color: #888; font-size: 12px;">Questions? Contact HR at ext. 2100 or visit the HR office on the 3rd floor.</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="color: #aaa; font-size: 11px; text-align: center;">Human Resources | Benefits Administration</p>
</div>
</body>
</html>',
                'landing_page_html' => '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Benefits Portal</title></head>
<body style="font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
<div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 450px;">
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; text-align: center;">
        <div style="font-size: 32px; margin-bottom: 8px;">🎓</div>
        <h3 style="color: #166534; margin: 0 0 8px;">This Was a Phishing Simulation</h3>
        <p style="color: #15803d; font-size: 14px; line-height: 1.6; margin: 0;">You clicked on a simulated phishing link that impersonated your HR department. A real attacker could have captured your login credentials.</p>
    </div>
    <div style="margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 6px;">
        <h4 style="color: #1a1a1a; margin: 0 0 8px; font-size: 14px;">🔍 Red Flags You Missed:</h4>
        <ul style="color: #555; font-size: 13px; line-height: 1.8; margin: 0; padding-left: 20px;">
            <li>The sender domain (hr-portal-secure.com) is not your company\'s actual domain</li>
            <li>HR benefits portals should be accessed directly, not through email links</li>
            <li>The email creates urgency with a Friday deadline</li>
            <li>Your real HR team would use your actual company email domain</li>
        </ul>
    </div>
    <div style="margin-top: 16px; padding: 12px 16px; background: #eff6ff; border-radius: 6px;">
        <p style="color: #1e40af; font-size: 13px; line-height: 1.6; margin: 0;"><strong>Tip:</strong> Always navigate to HR portals by typing the URL directly in your browser or using your company\'s intranet bookmarks.</p>
    </div>
</div>
</body>
</html>',
            ],
            [
                'name' => 'Shared Document Notification',
                'scenario_type' => 'credential_harvest',
                'subject' => 'John shared "Q4 Budget Review.xlsx" with you',
                'sender_name' => 'John Mitchell via CloudDocs',
                'sender_email' => 'sharing@cloud-docs-notify.com',
                'difficulty' => 'hard',
                'body_html' => '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
<div style="background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
        <div style="width: 40px; height: 40px; background: #4285f4; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: bold; font-size: 18px;">JM</div>
        <div>
            <p style="color: #1a1a1a; font-weight: bold; margin: 0; font-size: 15px;">John Mitchell</p>
            <p style="color: #888; font-size: 12px; margin: 2px 0 0;">john.mitchell@company.com</p>
        </div>
    </div>
    <p style="color: #555; line-height: 1.6;">John Mitchell has shared a document with you:</p>
    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 16px 0; display: flex; align-items: center; gap: 12px;">
        <div style="width: 40px; height: 40px; background: #22863a; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
            <span style="color: #fff; font-weight: bold; font-size: 11px;">XLSX</span>
        </div>
        <div>
            <p style="color: #1a1a1a; font-weight: 600; margin: 0; font-size: 14px;">Q4 Budget Review.xlsx</p>
            <p style="color: #888; font-size: 12px; margin: 2px 0 0;">Updated 2 hours ago &middot; 847 KB</p>
        </div>
    </div>
    <p style="color: #555; line-height: 1.6; font-size: 14px; font-style: italic; background: #f9fafb; padding: 12px; border-left: 3px solid #e5e7eb; border-radius: 4px;">&ldquo;Hey, please review the updated budget figures before our meeting tomorrow. Let me know if the projections look right.&rdquo;</p>
    <div style="text-align: center; margin: 25px 0;">
        <a href="#" style="background: #4285f4; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">Open Document</a>
    </div>
    <p style="color: #aaa; font-size: 11px; text-align: center;">You received this email because john.mitchell@company.com shared a file with you.<br>CloudDocs &middot; Secure Document Sharing</p>
</div>
</body>
</html>',
                'landing_page_html' => '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Sign in to view document</title></head>
<body style="font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
<div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 450px;">
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; text-align: center;">
        <div style="font-size: 32px; margin-bottom: 8px;">🎓</div>
        <h3 style="color: #166534; margin: 0 0 8px;">This Was a Phishing Simulation</h3>
        <p style="color: #15803d; font-size: 14px; line-height: 1.6; margin: 0;">You clicked on a simulated phishing link disguised as a document sharing notification. This is one of the hardest phishing types to detect.</p>
    </div>
    <div style="margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 6px;">
        <h4 style="color: #1a1a1a; margin: 0 0 8px; font-size: 14px;">🔍 Red Flags You Missed:</h4>
        <ul style="color: #555; font-size: 13px; line-height: 1.8; margin: 0; padding-left: 20px;">
            <li>The sender domain (cloud-docs-notify.com) is not Google, Microsoft, or your company</li>
            <li>"CloudDocs" is not a real product — real services use their actual brand name</li>
            <li>Legitimate sharing notifications link directly to the document, not a login page</li>
            <li>Check with the sender through a separate channel before clicking</li>
        </ul>
    </div>
    <div style="margin-top: 16px; padding: 12px 16px; background: #eff6ff; border-radius: 6px;">
        <p style="color: #1e40af; font-size: 13px; line-height: 1.6; margin: 0;"><strong>Tip:</strong> When you receive a document sharing email, navigate to the service directly (drive.google.com, sharepoint.com) and check your shared files there instead of clicking the email link.</p>
    </div>
</div>
</body>
</html>',
            ],
            [
                'name' => 'MFA Reset Notification',
                'scenario_type' => 'credential_harvest',
                'subject' => 'Action Required: Your MFA device has been removed',
                'sender_name' => 'Identity & Access Management',
                'sender_email' => 'iam-security@company-identity.com',
                'difficulty' => 'hard',
                'body_html' => '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
<div style="background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0;">
    <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 12px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 20px;">⚠️</span>
        <span style="color: #991b1b; font-weight: bold; font-size: 14px;">Security Alert — Immediate Action Required</span>
    </div>
    <p style="color: #555; line-height: 1.6;">Hello,</p>
    <p style="color: #555; line-height: 1.6;">We detected that your multi-factor authentication (MFA) device was <strong>removed from your account</strong> at <strong>3:47 AM today</strong>. This change was not initiated through normal channels.</p>
    <div style="background: #f9fafb; border-radius: 6px; padding: 16px; margin: 16px 0;">
        <table style="width: 100%; font-size: 13px; color: #555;">
            <tr><td style="padding: 4px 0;"><strong>Event:</strong></td><td>MFA Device Removed</td></tr>
            <tr><td style="padding: 4px 0;"><strong>Time:</strong></td><td>3:47 AM (UTC)</td></tr>
            <tr><td style="padding: 4px 0;"><strong>IP Address:</strong></td><td>185.220.101.42 (Unknown)</td></tr>
            <tr><td style="padding: 4px 0;"><strong>Location:</strong></td><td>Eastern Europe</td></tr>
        </table>
    </div>
    <p style="color: #555; line-height: 1.6;">If you did not make this change, your account may be compromised. Please <strong>immediately re-enroll your MFA device</strong> to secure your account.</p>
    <div style="text-align: center; margin: 25px 0;">
        <a href="#" style="background: #dc2626; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">Re-enroll MFA Now</a>
    </div>
    <p style="color: #888; font-size: 12px;">If you did remove your MFA device intentionally, you can safely ignore this email.</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="color: #aaa; font-size: 11px; text-align: center;">Identity & Access Management | Security Operations Center</p>
</div>
</body>
</html>',
                'landing_page_html' => '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>MFA Enrollment</title></head>
<body style="font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
<div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 450px;">
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; text-align: center;">
        <div style="font-size: 32px; margin-bottom: 8px;">🎓</div>
        <h3 style="color: #166534; margin: 0 0 8px;">This Was a Phishing Simulation</h3>
        <p style="color: #15803d; font-size: 14px; line-height: 1.6; margin: 0;">You clicked on a sophisticated phishing link exploiting MFA security concerns. Ironically, attackers use fake MFA alerts to steal the very credentials MFA is meant to protect.</p>
    </div>
    <div style="margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 6px;">
        <h4 style="color: #1a1a1a; margin: 0 0 8px; font-size: 14px;">🔍 Red Flags You Missed:</h4>
        <ul style="color: #555; font-size: 13px; line-height: 1.8; margin: 0; padding-left: 20px;">
            <li>The sender domain (company-identity.com) is not your actual company domain</li>
            <li>The event occurred at 3:47 AM from "Eastern Europe" — designed to create panic</li>
            <li>Real MFA alerts would come from your actual identity provider</li>
            <li>Your IT team would never ask you to click an email link to re-enroll MFA</li>
        </ul>
    </div>
    <div style="margin-top: 16px; padding: 12px 16px; background: #eff6ff; border-radius: 6px;">
        <p style="color: #1e40af; font-size: 13px; line-height: 1.6; margin: 0;"><strong>Tip:</strong> If you receive a real security alert about your account, contact your IT security team directly — never click links in the alert email itself.</p>
    </div>
</div>
</body>
</html>',
            ],
            [
                'name' => 'Voicemail Transcription',
                'scenario_type' => 'malware_link',
                'subject' => 'New Voicemail from +1 (555) 847-2291 - 2 min 14 sec',
                'sender_name' => 'Voicemail Service',
                'sender_email' => 'voicemail@unified-comms-portal.com',
                'difficulty' => 'easy',
                'body_html' => '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
<div style="background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0;">
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 50px; height: 50px; background: #10b981; border-radius: 50%; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center;">
            <span style="color: #fff; font-size: 24px;">📞</span>
        </div>
        <h2 style="color: #1a1a1a; margin: 0 0 4px;">New Voicemail</h2>
        <p style="color: #888; font-size: 13px; margin: 0;">Received today at 10:23 AM</p>
    </div>
    <div style="background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; margin: 16px 0;">
        <table style="width: 100%; font-size: 13px; color: #555;">
            <tr><td style="padding: 4px 0; width: 100px;"><strong>From:</strong></td><td>+1 (555) 847-2291</td></tr>
            <tr><td style="padding: 4px 0;"><strong>Duration:</strong></td><td>2 minutes, 14 seconds</td></tr>
            <tr><td style="padding: 4px 0;"><strong>Priority:</strong></td><td><span style="color: #dc2626; font-weight: bold;">High</span></td></tr>
        </table>
    </div>
    <p style="color: #555; line-height: 1.6; font-size: 14px;"><strong>Transcription preview:</strong> <em>&quot;Hi, this is regarding your account. We need to speak with you urgently about a matter that requires your immediate attention. Please call us back or listen to the full message...&quot;</em></p>
    <div style="text-align: center; margin: 25px 0;">
        <a href="#" style="background: #10b981; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">▶ Play Voicemail</a>
    </div>
    <p style="color: #aaa; font-size: 11px; text-align: center;">Unified Communications | Voicemail-to-Email Service</p>
</div>
</body>
</html>',
                'landing_page_html' => '<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Voicemail Player</title></head>
<body style="font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
<div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 450px;">
    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; text-align: center;">
        <div style="font-size: 32px; margin-bottom: 8px;">🎓</div>
        <h3 style="color: #166534; margin: 0 0 8px;">This Was a Phishing Simulation</h3>
        <p style="color: #15803d; font-size: 14px; line-height: 1.6; margin: 0;">You clicked on a simulated phishing link disguised as a voicemail notification. These emails often deliver malware disguised as audio files.</p>
    </div>
    <div style="margin-top: 20px; padding: 16px; background: #f9fafb; border-radius: 6px;">
        <h4 style="color: #1a1a1a; margin: 0 0 8px; font-size: 14px;">🔍 Red Flags You Missed:</h4>
        <ul style="color: #555; font-size: 13px; line-height: 1.8; margin: 0; padding-left: 20px;">
            <li>The sender domain (unified-comms-portal.com) is not your company\'s phone system</li>
            <li>Real voicemail systems use your company\'s actual phone service domain</li>
            <li>The vague "urgency" in the transcription is a social engineering tactic</li>
            <li>"Play Voicemail" buttons in emails often download malware, not audio</li>
        </ul>
    </div>
    <div style="margin-top: 16px; padding: 12px 16px; background: #eff6ff; border-radius: 6px;">
        <p style="color: #1e40af; font-size: 13px; line-height: 1.6; margin: 0;"><strong>Tip:</strong> Access voicemails directly through your phone system or its official app — never through email links.</p>
    </div>
</div>
</body>
</html>',
            ],
        ];

        foreach ($templates as $template) {
            PhishingTemplate::updateOrCreate(
                ['name' => $template['name']],
                array_merge($template, ['is_system' => true])
            );
        }
    }
}
