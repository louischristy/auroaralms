<?php

namespace Database\Seeders;

use App\Models\PhishingTemplate;
use Illuminate\Database\Seeder;

class PhishingTemplateSeederBatch3 extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Microsoft Teams Meeting Invitation',
                'scenario_type' => 'credential_harvest',
                'subject' => 'You have been invited to a Teams meeting: Q3 Strategy Review',
                'sender_name' => 'Microsoft Teams',
                'sender_email' => 'noreply@teams-microsoft.net',
                'difficulty' => 'medium',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f5f5f5;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f5f5;padding:20px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:4px;overflow:hidden;">
<tr><td style="background-color:#464EB8;padding:16px 24px;">
<table width="100%" cellpadding="0" cellspacing="0"><tr>
<td><span style="color:#ffffff;font-size:18px;font-weight:600;">Microsoft Teams</span></td>
</tr></table>
</td></tr>
<tr><td style="padding:32px 24px;">
<p style="margin:0 0 8px;font-size:14px;color:#242424;">Hello,</p>
<p style="margin:0 0 24px;font-size:14px;color:#242424;">You have been invited to a meeting.</p>
<table width="100%" cellpadding="0" cellspacing="0" style="border-left:4px solid #464EB8;padding-left:16px;margin-bottom:24px;">
<tr><td>
<p style="margin:0 0 4px;font-size:18px;font-weight:600;color:#242424;">Q3 Strategy Review</p>
<p style="margin:0 0 4px;font-size:14px;color:#616161;">Wednesday, September 24, 2026 2:00 PM - 3:00 PM (UTC)</p>
<p style="margin:0;font-size:14px;color:#616161;">Organizer: David Chen, VP of Operations</p>
</td></tr>
</table>
<table cellpadding="0" cellspacing="0" style="margin-bottom:24px;"><tr>
<td style="background-color:#464EB8;border-radius:4px;padding:10px 24px;">
<a href="#" style="color:#ffffff;text-decoration:none;font-size:14px;font-weight:600;">Join the meeting</a>
</td>
</tr></table>
<p style="margin:0 0 16px;font-size:12px;color:#616161;">Or join by entering the meeting ID and passcode:</p>
<p style="margin:0 0 4px;font-size:12px;color:#616161;">Meeting ID: 284 193 776 42</p>
<p style="margin:0 0 24px;font-size:12px;color:#616161;">Passcode: qR7kPw</p>
<hr style="border:none;border-top:1px solid #edebe9;margin:0 0 16px;">
<p style="margin:0;font-size:11px;color:#616161;">Microsoft respects your privacy. Review our <a href="#" style="color:#464EB8;">privacy statement</a>.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Phishing Simulation</title></head>
<body style="margin:0;padding:40px 20px;background-color:#f5f5f5;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
<div style="max-width:600px;margin:0 auto;background-color:#d4edda;border:2px solid #28a745;border-radius:8px;padding:32px;">
<h1 style="margin:0 0 16px;color:#155724;font-size:24px;">&#9989; This Was a Phishing Simulation</h1>
<p style="margin:0 0 16px;color:#155724;font-size:16px;">This was a security awareness exercise. No real meeting exists. Here are the red flags you should have noticed:</p>
<ul style="margin:0 0 16px;padding-left:20px;color:#155724;font-size:14px;line-height:1.8;">
<li><strong>Sender domain:</strong> The email came from <em>teams-microsoft.net</em> instead of the legitimate <em>microsoft.com</em> domain.</li>
<li><strong>Unknown organizer:</strong> The meeting was organized by someone you may not recognize or who doesn't match your company directory.</li>
<li><strong>Urgency and vagueness:</strong> A broad "Q3 Strategy Review" meeting with no agenda or attendee list is designed to provoke curiosity.</li>
<li><strong>Hover over links:</strong> The "Join the meeting" button does not point to a legitimate teams.microsoft.com URL.</li>
</ul>
<p style="margin:0;color:#155724;font-size:14px;">Always verify meeting invitations through your Teams app or calendar before clicking links in emails.</p>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'LinkedIn Connection Request',
                'scenario_type' => 'credential_harvest',
                'subject' => 'You have a new connection request from Sarah Mitchell',
                'sender_name' => 'LinkedIn',
                'sender_email' => 'notifications@linkedln-mail.com',
                'difficulty' => 'easy',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f3f2ef;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f2ef;padding:20px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
<tr><td style="padding:16px 24px;border-bottom:1px solid #e0e0e0;">
<span style="font-size:20px;font-weight:700;color:#0a66c2;">Linked</span><span style="font-size:20px;font-weight:700;color:#0a66c2;background-color:#0a66c2;color:#fff;padding:1px 5px;border-radius:3px;margin-left:1px;">in</span>
</td></tr>
<tr><td style="padding:24px;">
<table width="100%" cellpadding="0" cellspacing="0"><tr>
<td width="72" valign="top">
<div style="width:64px;height:64px;border-radius:50%;background-color:#ddd;text-align:center;line-height:64px;font-size:28px;color:#666;">SM</div>
</td>
<td valign="top" style="padding-left:12px;">
<p style="margin:0 0 4px;font-size:16px;font-weight:600;color:#191919;">Sarah Mitchell</p>
<p style="margin:0 0 4px;font-size:14px;color:#666666;">Senior Recruiter at Fortune Global 500 Company</p>
<p style="margin:0;font-size:13px;color:#999999;">San Francisco Bay Area &bull; 500+ connections</p>
</td>
</tr></table>
<p style="margin:20px 0;font-size:14px;color:#191919;line-height:1.5;">Hi, I came across your profile and would love to connect. I have an exciting opportunity that might interest you.</p>
<table cellpadding="0" cellspacing="0" style="margin:0 auto 20px;"><tr>
<td style="background-color:#0a66c2;border-radius:24px;padding:10px 32px;">
<a href="#" style="color:#ffffff;text-decoration:none;font-size:16px;font-weight:600;">Accept Invitation</a>
</td>
</tr></table>
<p style="margin:0;text-align:center;"><a href="#" style="color:#0a66c2;font-size:14px;text-decoration:none;">View Profile</a></p>
</td></tr>
<tr><td style="padding:16px 24px;background-color:#f9f9f9;border-top:1px solid #e0e0e0;">
<p style="margin:0;font-size:11px;color:#999999;text-align:center;">You are receiving LinkedIn notification emails. <a href="#" style="color:#0a66c2;">Unsubscribe</a> | <a href="#" style="color:#0a66c2;">Help</a></p>
<p style="margin:8px 0 0;font-size:11px;color:#999999;text-align:center;">&copy; 2026 LinkedIn Corporation, 1000 W Maude Ave, Sunnyvale, CA 94085</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Phishing Simulation</title></head>
<body style="margin:0;padding:40px 20px;background-color:#f3f2ef;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<div style="max-width:600px;margin:0 auto;background-color:#d4edda;border:2px solid #28a745;border-radius:8px;padding:32px;">
<h1 style="margin:0 0 16px;color:#155724;font-size:24px;">&#9989; This Was a Phishing Simulation</h1>
<p style="margin:0 0 16px;color:#155724;font-size:16px;">This was a security awareness exercise. Here are the red flags you should have noticed:</p>
<ul style="margin:0 0 16px;padding-left:20px;color:#155724;font-size:14px;line-height:1.8;">
<li><strong>Misspelled domain:</strong> The sender email uses <em>linkedln-mail.com</em> (with an "l" instead of "I") — not a legitimate LinkedIn domain.</li>
<li><strong>Vague job offer:</strong> "An exciting opportunity" with no specifics is a common social engineering tactic.</li>
<li><strong>Generic profile:</strong> The sender has no specific company name, just "Fortune Global 500 Company."</li>
<li><strong>Direct action link:</strong> Legitimate LinkedIn emails ask you to view requests in the app, not click "Accept" directly in the email.</li>
<li><strong>Urgency through flattery:</strong> Unsolicited recruiter messages that flatter you are designed to bypass critical thinking.</li>
</ul>
<p style="margin:0;color:#155724;font-size:14px;">Always log in to LinkedIn directly to manage connection requests rather than clicking email links.</p>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'Dropbox Storage Full Alert',
                'scenario_type' => 'credential_harvest',
                'subject' => 'Your Dropbox is 98% full — files will stop syncing',
                'sender_name' => 'Dropbox',
                'sender_email' => 'no-reply@dropbox-cloud.com',
                'difficulty' => 'medium',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f7f5f2;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f7f5f2;padding:20px 0;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;">
<tr><td style="padding:32px 40px 24px;text-align:center;">
<div style="font-size:28px;font-weight:700;color:#1e1919;margin-bottom:8px;">Dropbox</div>
</td></tr>
<tr><td style="padding:0 40px 32px;">
<div style="background-color:#fff4e5;border-radius:8px;padding:16px;margin-bottom:24px;text-align:center;">
<p style="margin:0 0 4px;font-size:14px;font-weight:600;color:#b7791f;">&#9888; Storage Almost Full</p>
<p style="margin:0;font-size:13px;color:#975a16;">You've used 1.96 GB of your 2 GB storage</p>
</div>
<div style="background-color:#f0f0f0;border-radius:8px;height:12px;margin-bottom:8px;overflow:hidden;">
<div style="background:linear-gradient(90deg,#0061ff 0%,#e53e3e 90%);height:100%;width:98%;border-radius:8px;"></div>
</div>
<p style="margin:0 0 24px;font-size:12px;color:#637282;text-align:right;">1.96 GB / 2.00 GB</p>
<p style="margin:0 0 16px;font-size:14px;color:#1e1919;line-height:1.6;">Your Dropbox is almost full. When you reach your storage limit, files will stop syncing across your devices and shared folders may become read-only.</p>
<p style="margin:0 0 24px;font-size:14px;color:#1e1919;line-height:1.6;">Sign in to manage your files or upgrade your plan to get more space.</p>
<table width="100%" cellpadding="0" cellspacing="0"><tr>
<td align="center">
<a href="#" style="display:inline-block;background-color:#0061ff;color:#ffffff;text-decoration:none;padding:12px 32px;border-radius:8px;font-size:14px;font-weight:600;">Manage your storage</a>
</td>
</tr></table>
</td></tr>
<tr><td style="padding:20px 40px;border-top:1px solid #e5e5e5;">
<p style="margin:0;font-size:11px;color:#b0b0b0;text-align:center;">Dropbox, Inc. 1800 Owens St, San Francisco, CA 94158</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Phishing Simulation</title></head>
<body style="margin:0;padding:40px 20px;background-color:#f7f5f2;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<div style="max-width:600px;margin:0 auto;background-color:#d4edda;border:2px solid #28a745;border-radius:8px;padding:32px;">
<h1 style="margin:0 0 16px;color:#155724;font-size:24px;">&#9989; This Was a Phishing Simulation</h1>
<p style="margin:0 0 16px;color:#155724;font-size:16px;">This was a security awareness exercise. Here are the red flags you should have noticed:</p>
<ul style="margin:0 0 16px;padding-left:20px;color:#155724;font-size:14px;line-height:1.8;">
<li><strong>Fake domain:</strong> The email came from <em>dropbox-cloud.com</em>, not the legitimate <em>dropbox.com</em> or <em>dropboxmail.com</em>.</li>
<li><strong>Scare tactic:</strong> Warning that "files will stop syncing" creates urgency to act without thinking.</li>
<li><strong>Generic greeting:</strong> No personalized username or account email — legitimate Dropbox emails address you by name.</li>
<li><strong>Login redirect:</strong> The "Manage your storage" button leads to a credential harvesting page, not dropbox.com.</li>
</ul>
<p style="margin:0;color:#155724;font-size:14px;">Always navigate to dropbox.com directly to check your storage usage rather than clicking email links.</p>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'Company Survey - Employee Satisfaction',
                'scenario_type' => 'credential_harvest',
                'subject' => 'Action Required: Complete Your Annual Employee Satisfaction Survey',
                'sender_name' => 'HR Department',
                'sender_email' => 'hr-surveys@company-feedback.org',
                'difficulty' => 'easy',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:20px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:4px;overflow:hidden;">
<tr><td style="background-color:#2c3e50;padding:24px;text-align:center;">
<h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:600;">Annual Employee Satisfaction Survey 2026</h1>
</td></tr>
<tr><td style="padding:32px 24px;">
<p style="margin:0 0 16px;font-size:15px;color:#333333;line-height:1.6;">Dear Valued Employee,</p>
<p style="margin:0 0 16px;font-size:15px;color:#333333;line-height:1.6;">As part of our commitment to continuous improvement, we are conducting our annual employee satisfaction survey. Your feedback is essential in shaping company policies and workplace culture for the coming year.</p>
<div style="background-color:#fef9e7;border-left:4px solid #f1c40f;padding:12px 16px;margin-bottom:20px;">
<p style="margin:0;font-size:14px;color:#7d6608;"><strong>Deadline:</strong> This survey must be completed by end of day Friday, September 25, 2026. Participation is mandatory for all employees.</p>
</div>
<p style="margin:0 0 16px;font-size:15px;color:#333333;line-height:1.6;">The survey takes approximately 5 minutes and covers topics including job satisfaction, management effectiveness, and workplace environment. To verify your identity, you will be asked to confirm your employee ID and department.</p>
<table cellpadding="0" cellspacing="0" style="margin:0 auto 24px;"><tr>
<td style="background-color:#27ae60;border-radius:4px;padding:14px 40px;">
<a href="#" style="color:#ffffff;text-decoration:none;font-size:16px;font-weight:600;">Start Survey Now</a>
</td>
</tr></table>
<p style="margin:0 0 8px;font-size:13px;color:#999999;">This survey is administered by an independent third-party partner to ensure anonymity.</p>
<p style="margin:0;font-size:13px;color:#999999;">If you have questions, contact HR at ext. 4200.</p>
</td></tr>
<tr><td style="padding:16px 24px;background-color:#f9f9f9;border-top:1px solid #eeeeee;">
<p style="margin:0;font-size:11px;color:#aaaaaa;text-align:center;">Human Resources Department &bull; Internal Communications</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Phishing Simulation</title></head>
<body style="margin:0;padding:40px 20px;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:0 auto;background-color:#d4edda;border:2px solid #28a745;border-radius:8px;padding:32px;">
<h1 style="margin:0 0 16px;color:#155724;font-size:24px;">&#9989; This Was a Phishing Simulation</h1>
<p style="margin:0 0 16px;color:#155724;font-size:16px;">This was a security awareness exercise. Here are the red flags you should have noticed:</p>
<ul style="margin:0 0 16px;padding-left:20px;color:#155724;font-size:14px;line-height:1.8;">
<li><strong>External domain:</strong> The email came from <em>company-feedback.org</em>, not your organization's actual email domain.</li>
<li><strong>Requests personal information:</strong> Asking for your employee ID and department in a "survey" is a data harvesting technique.</li>
<li><strong>Mandatory with a tight deadline:</strong> Pressuring you to complete it by Friday creates urgency that bypasses caution.</li>
<li><strong>Generic greeting:</strong> "Dear Valued Employee" instead of your actual name suggests a mass phishing campaign.</li>
<li><strong>Third-party claim:</strong> Mentioning an unnamed "independent third-party partner" adds false legitimacy.</li>
</ul>
<p style="margin:0;color:#155724;font-size:14px;">Always confirm internal surveys with your HR department through official channels before providing any personal information.</p>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'Zoom Account Deactivation Notice',
                'scenario_type' => 'credential_harvest',
                'subject' => 'Action Required: Your Zoom account will be deactivated in 24 hours',
                'sender_name' => 'Zoom Support',
                'sender_email' => 'support@zoom-accounts.com',
                'difficulty' => 'hard',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f6f6f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f6f6;padding:20px 0;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;overflow:hidden;">
<tr><td style="padding:28px 40px 20px;">
<div style="font-size:24px;font-weight:700;color:#2d8cff;margin-bottom:4px;">zoom</div>
</td></tr>
<tr><td style="padding:0 40px 32px;">
<h2 style="margin:0 0 16px;font-size:20px;color:#232333;font-weight:600;">Account Deactivation Notice</h2>
<p style="margin:0 0 16px;font-size:14px;color:#232333;line-height:1.6;">We've detected that your Zoom account has not met our updated Terms of Service requirements. As a result, your account is scheduled for deactivation.</p>
<div style="background-color:#f8f8f8;border-radius:8px;padding:16px;margin-bottom:20px;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:4px 0;font-size:13px;color:#747487;">Account:</td><td style="padding:4px 0;font-size:13px;color:#232333;text-align:right;">Your registered email</td></tr>
<tr><td style="padding:4px 0;font-size:13px;color:#747487;">Status:</td><td style="padding:4px 0;font-size:13px;color:#e02828;text-align:right;font-weight:600;">Pending Deactivation</td></tr>
<tr><td style="padding:4px 0;font-size:13px;color:#747487;">Deadline:</td><td style="padding:4px 0;font-size:13px;color:#232333;text-align:right;">24 hours from this notice</td></tr>
<tr><td style="padding:4px 0;font-size:13px;color:#747487;">Impact:</td><td style="padding:4px 0;font-size:13px;color:#232333;text-align:right;">All recordings and contacts lost</td></tr>
</table>
</div>
<p style="margin:0 0 20px;font-size:14px;color:#232333;line-height:1.6;">To prevent deactivation and retain all your data, please verify your account by confirming your credentials below:</p>
<table width="100%" cellpadding="0" cellspacing="0"><tr>
<td align="center">
<a href="#" style="display:inline-block;background-color:#2d8cff;color:#ffffff;text-decoration:none;padding:12px 36px;border-radius:8px;font-size:15px;font-weight:600;">Verify My Account</a>
</td>
</tr></table>
<p style="margin:20px 0 0;font-size:12px;color:#747487;line-height:1.5;">If you did not request this action or believe this is an error, please verify your account immediately to prevent any disruption. Failure to act within 24 hours will result in permanent data loss.</p>
</td></tr>
<tr><td style="padding:20px 40px;border-top:1px solid #eeeeee;">
<p style="margin:0;font-size:11px;color:#aaaaaa;text-align:center;">Zoom Video Communications, Inc. 55 Almaden Blvd, Suite 600, San Jose, CA 95113</p>
<p style="margin:6px 0 0;font-size:11px;color:#aaaaaa;text-align:center;"><a href="#" style="color:#2d8cff;">Privacy Policy</a> &bull; <a href="#" style="color:#2d8cff;">Terms of Service</a> &bull; <a href="#" style="color:#2d8cff;">Unsubscribe</a></p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Phishing Simulation</title></head>
<body style="margin:0;padding:40px 20px;background-color:#f6f6f6;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
<div style="max-width:600px;margin:0 auto;background-color:#d4edda;border:2px solid #28a745;border-radius:8px;padding:32px;">
<h1 style="margin:0 0 16px;color:#155724;font-size:24px;">&#9989; This Was a Phishing Simulation</h1>
<p style="margin:0 0 16px;color:#155724;font-size:16px;">This was a security awareness exercise. Here are the red flags you should have noticed:</p>
<ul style="margin:0 0 16px;padding-left:20px;color:#155724;font-size:14px;line-height:1.8;">
<li><strong>Fake domain:</strong> The email came from <em>zoom-accounts.com</em>, not the legitimate <em>zoom.us</em> domain.</li>
<li><strong>Extreme urgency:</strong> A 24-hour deadline with threats of "permanent data loss" is designed to make you panic and act without thinking.</li>
<li><strong>Vague policy violation:</strong> "Updated Terms of Service requirements" is deliberately non-specific — a real notice would explain exactly what is wrong.</li>
<li><strong>Credential request:</strong> Zoom would never ask you to "confirm your credentials" via an email link to prevent deactivation.</li>
<li><strong>Contradictory instructions:</strong> The fine print says "if you did not request this action" but then tells you to verify anyway, which makes no logical sense.</li>
</ul>
<p style="margin:0;color:#155724;font-size:14px;">Always log in to zoom.us directly and check your account settings rather than following links in threatening emails.</p>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'PayPal Suspicious Activity',
                'scenario_type' => 'credential_harvest',
                'subject' => 'We noticed unusual activity on your PayPal account',
                'sender_name' => 'PayPal Security',
                'sender_email' => 'security@paypal-alerts.com',
                'difficulty' => 'medium',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f5f7fa;font-family:Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f5f7fa;padding:20px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;">
<tr><td style="padding:24px 32px;border-bottom:1px solid #eeeeee;">
<span style="font-size:22px;font-weight:700;color:#003087;">Pay</span><span style="font-size:22px;font-weight:700;color:#009cde;">Pal</span>
</td></tr>
<tr><td style="padding:32px;">
<div style="text-align:center;margin-bottom:24px;">
<div style="display:inline-block;width:48px;height:48px;border-radius:50%;background-color:#fef3cd;text-align:center;line-height:48px;font-size:24px;">&#9888;</div>
</div>
<h2 style="margin:0 0 16px;font-size:18px;color:#2c2e2f;text-align:center;">Unusual login activity detected</h2>
<p style="margin:0 0 20px;font-size:14px;color:#2c2e2f;line-height:1.6;">We noticed a login to your PayPal account from an unrecognized device or location. For your protection, we've temporarily limited some account features.</p>
<div style="background-color:#f5f7fa;border-radius:6px;padding:16px;margin-bottom:24px;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:6px 0;font-size:13px;color:#687173;">Date:</td><td style="padding:6px 0;font-size:13px;color:#2c2e2f;">September 23, 2026 at 3:42 AM UTC</td></tr>
<tr><td style="padding:6px 0;font-size:13px;color:#687173;">Device:</td><td style="padding:6px 0;font-size:13px;color:#2c2e2f;">Chrome on Windows 11</td></tr>
<tr><td style="padding:6px 0;font-size:13px;color:#687173;">Location:</td><td style="padding:6px 0;font-size:13px;color:#2c2e2f;">Lagos, Nigeria</td></tr>
<tr><td style="padding:6px 0;font-size:13px;color:#687173;">IP Address:</td><td style="padding:6px 0;font-size:13px;color:#2c2e2f;">105.112.47.203</td></tr>
</table>
</div>
<p style="margin:0 0 24px;font-size:14px;color:#2c2e2f;line-height:1.6;">If this wasn't you, please secure your account immediately by verifying your identity:</p>
<table width="100%" cellpadding="0" cellspacing="0"><tr>
<td align="center">
<a href="#" style="display:inline-block;background-color:#0070ba;color:#ffffff;text-decoration:none;padding:14px 40px;border-radius:24px;font-size:15px;font-weight:600;">Secure My Account</a>
</td>
</tr></table>
<p style="margin:24px 0 0;font-size:12px;color:#687173;line-height:1.5;">If you did make this login, you can disregard this email. Your account security is our top priority.</p>
</td></tr>
<tr><td style="padding:20px 32px;background-color:#f5f7fa;border-top:1px solid #eeeeee;">
<p style="margin:0;font-size:11px;color:#aaaaaa;text-align:center;">PayPal, Inc. 2211 North First Street, San Jose, CA 95131</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Phishing Simulation</title></head>
<body style="margin:0;padding:40px 20px;background-color:#f5f7fa;font-family:Helvetica,Arial,sans-serif;">
<div style="max-width:600px;margin:0 auto;background-color:#d4edda;border:2px solid #28a745;border-radius:8px;padding:32px;">
<h1 style="margin:0 0 16px;color:#155724;font-size:24px;">&#9989; This Was a Phishing Simulation</h1>
<p style="margin:0 0 16px;color:#155724;font-size:16px;">This was a security awareness exercise. Here are the red flags you should have noticed:</p>
<ul style="margin:0 0 16px;padding-left:20px;color:#155724;font-size:14px;line-height:1.8;">
<li><strong>Fake domain:</strong> The email came from <em>paypal-alerts.com</em>, not the legitimate <em>paypal.com</em>.</li>
<li><strong>Fear-based urgency:</strong> Reporting a login from a suspicious location (Lagos, Nigeria) is designed to trigger a panic response.</li>
<li><strong>Account limitation threat:</strong> Claiming features are "temporarily limited" pressures you to act immediately.</li>
<li><strong>Generic greeting:</strong> PayPal emails always address you by your full name, not generically.</li>
</ul>
<p style="margin:0;color:#155724;font-size:14px;">Always log in to paypal.com directly to review account activity and security alerts.</p>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'DocuSign Document Ready for Signature',
                'scenario_type' => 'malware_link',
                'subject' => 'Complete with DocuSign: Annual NDA Renewal - Signature Required',
                'sender_name' => 'DocuSign via Legal Department',
                'sender_email' => 'dse@docusign-notifications.net',
                'difficulty' => 'hard',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:20px 0;">
<tr><td align="center">
<table width="580" cellpadding="0" cellspacing="0" style="background-color:#ffffff;">
<tr><td style="background-color:#FBE64A;padding:12px 24px;">
<span style="font-size:18px;font-weight:700;color:#1A1A2E;">DocuSign</span>
</td></tr>
<tr><td style="padding:32px 24px;">
<p style="margin:0 0 20px;font-size:14px;color:#333333;line-height:1.6;">Legal Department sent you a document to review and sign.</p>
<div style="border:1px solid #e0e0e0;border-radius:4px;padding:20px;margin-bottom:24px;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:0 0 12px;">
<p style="margin:0;font-size:12px;color:#999999;text-transform:uppercase;letter-spacing:1px;">Document</p>
<p style="margin:4px 0 0;font-size:15px;color:#333333;font-weight:600;">Annual NDA Renewal Agreement 2026-2027</p>
</td></tr>
<tr><td style="padding:0 0 12px;border-top:1px solid #f0f0f0;padding-top:12px;">
<p style="margin:0;font-size:12px;color:#999999;text-transform:uppercase;letter-spacing:1px;">Sent By</p>
<p style="margin:4px 0 0;font-size:14px;color:#333333;">Rebecca Torres, General Counsel</p>
</td></tr>
<tr><td style="border-top:1px solid #f0f0f0;padding-top:12px;">
<p style="margin:0;font-size:12px;color:#999999;text-transform:uppercase;letter-spacing:1px;">Note from sender</p>
<p style="margin:4px 0 0;font-size:14px;color:#333333;font-style:italic;">"Please review and sign the attached NDA renewal at your earliest convenience. This is required for continued access to confidential project materials."</p>
</td></tr>
</table>
</div>
<table width="100%" cellpadding="0" cellspacing="0"><tr>
<td align="center">
<a href="#" style="display:inline-block;background-color:#FBE64A;color:#1A1A2E;text-decoration:none;padding:14px 48px;font-size:16px;font-weight:700;border-radius:4px;">REVIEW DOCUMENT</a>
</td>
</tr></table>
<p style="margin:24px 0 0;font-size:11px;color:#999999;line-height:1.5;">This message was sent to you by Rebecca Torres via DocuSign Electronic Signature Service. If you would rather not receive email from this sender you may <a href="#" style="color:#0072C6;">opt out</a> of future notifications.</p>
</td></tr>
<tr><td style="padding:16px 24px;background-color:#f9f9f9;border-top:1px solid #e0e0e0;">
<p style="margin:0;font-size:11px;color:#999999;text-align:center;">This message was sent by DocuSign. &copy; 2026 DocuSign, Inc.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Phishing Simulation</title></head>
<body style="margin:0;padding:40px 20px;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:0 auto;background-color:#d4edda;border:2px solid #28a745;border-radius:8px;padding:32px;">
<h1 style="margin:0 0 16px;color:#155724;font-size:24px;">&#9989; This Was a Phishing Simulation</h1>
<p style="margin:0 0 16px;color:#155724;font-size:16px;">This was a security awareness exercise. Here are the red flags you should have noticed:</p>
<ul style="margin:0 0 16px;padding-left:20px;color:#155724;font-size:14px;line-height:1.8;">
<li><strong>Fake domain:</strong> The email came from <em>docusign-notifications.net</em>, not the legitimate <em>docusign.com</em> or <em>docusign.net</em>.</li>
<li><strong>Unknown sender:</strong> "Rebecca Torres, General Counsel" may not match anyone in your actual legal department — always verify with your company directory.</li>
<li><strong>Implied consequences:</strong> Threatening loss of "access to confidential project materials" pressures you to sign quickly without scrutiny.</li>
<li><strong>Malware risk:</strong> The "Review Document" button could download a malicious file disguised as a document rather than opening a legitimate DocuSign page.</li>
<li><strong>No security code:</strong> Legitimate DocuSign emails include a unique security code at the bottom. This email has none.</li>
</ul>
<p style="margin:0;color:#155724;font-size:14px;">Always verify DocuSign requests by logging into docusign.com directly or contacting the purported sender through a known channel.</p>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'Slack Workspace Invitation',
                'scenario_type' => 'credential_harvest',
                'subject' => 'You\'ve been invited to join a Slack workspace',
                'sender_name' => 'Slack',
                'sender_email' => 'notifications@slack-invites.com',
                'difficulty' => 'medium',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f8f8f8;font-family:'Lato',Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f8f8;padding:20px 0;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e8e8e8;">
<tr><td style="padding:32px 40px 24px;text-align:center;">
<div style="display:inline-block;width:80px;height:80px;border-radius:16px;background-color:#4A154B;text-align:center;line-height:80px;margin-bottom:16px;">
<span style="color:#ffffff;font-size:36px;font-weight:700;">#</span>
</div>
<h2 style="margin:0 0 8px;font-size:20px;color:#1d1c1d;font-weight:700;">Join the Project Phoenix workspace</h2>
<p style="margin:0;font-size:14px;color:#616061;">Michael Roberts has invited you to collaborate</p>
</td></tr>
<tr><td style="padding:0 40px 32px;">
<div style="background-color:#f8f8f8;border-radius:8px;padding:16px;margin-bottom:24px;">
<p style="margin:0 0 8px;font-size:13px;color:#616061;">Workspace: <strong style="color:#1d1c1d;">Project Phoenix</strong></p>
<p style="margin:0 0 8px;font-size:13px;color:#616061;">Members: <strong style="color:#1d1c1d;">47 people</strong></p>
<p style="margin:0;font-size:13px;color:#616061;">Channels: <strong style="color:#1d1c1d;">#general, #engineering, #design, #launches</strong></p>
</div>
<table width="100%" cellpadding="0" cellspacing="0"><tr>
<td align="center">
<a href="#" style="display:inline-block;background-color:#4A154B;color:#ffffff;text-decoration:none;padding:12px 40px;border-radius:4px;font-size:15px;font-weight:700;">Join Now</a>
</td>
</tr></table>
<p style="margin:20px 0 0;font-size:12px;color:#616061;text-align:center;">This invitation will expire in 48 hours.</p>
</td></tr>
<tr><td style="padding:16px 40px;background-color:#f8f8f8;border-top:1px solid #e8e8e8;">
<p style="margin:0;font-size:11px;color:#999999;text-align:center;">Sent by Slack Technologies, LLC &bull; 500 Howard St, San Francisco, CA 94105</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Phishing Simulation</title></head>
<body style="margin:0;padding:40px 20px;background-color:#f8f8f8;font-family:'Lato',Helvetica,Arial,sans-serif;">
<div style="max-width:600px;margin:0 auto;background-color:#d4edda;border:2px solid #28a745;border-radius:8px;padding:32px;">
<h1 style="margin:0 0 16px;color:#155724;font-size:24px;">&#9989; This Was a Phishing Simulation</h1>
<p style="margin:0 0 16px;color:#155724;font-size:16px;">This was a security awareness exercise. Here are the red flags you should have noticed:</p>
<ul style="margin:0 0 16px;padding-left:20px;color:#155724;font-size:14px;line-height:1.8;">
<li><strong>Fake domain:</strong> The email came from <em>slack-invites.com</em>, not the legitimate <em>slack.com</em>.</li>
<li><strong>Unknown workspace:</strong> "Project Phoenix" is not a recognized workspace in your organization — verify with your IT team before joining.</li>
<li><strong>Unknown inviter:</strong> "Michael Roberts" may not be someone you know — always confirm workspace invitations through known channels.</li>
<li><strong>Expiration pressure:</strong> The 48-hour expiration creates artificial urgency to click before verifying.</li>
</ul>
<p style="margin:0;color:#155724;font-size:14px;">Always verify Slack workspace invitations by contacting the sender directly or checking with your IT department.</p>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'Amazon Order Confirmation',
                'scenario_type' => 'credential_harvest',
                'subject' => 'Your Amazon order #112-9374856-2947163 has been placed',
                'sender_name' => 'Amazon.com',
                'sender_email' => 'ship-confirm@amazon-orders.com',
                'difficulty' => 'easy',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#eaeded;font-family:Arial,Helvetica,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#eaeded;padding:20px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff;">
<tr><td style="background-color:#232f3e;padding:14px 20px;">
<span style="font-size:22px;font-weight:700;color:#ff9900;">amazon</span>
</td></tr>
<tr><td style="padding:24px 20px;">
<h2 style="margin:0 0 4px;font-size:18px;color:#111111;">Order Confirmation</h2>
<p style="margin:0 0 20px;font-size:14px;color:#565959;">Thank you for your order. We'll send a confirmation when your items ship.</p>
<div style="border:1px solid #d5d9d9;border-radius:8px;padding:16px;margin-bottom:20px;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:4px 0;font-size:13px;color:#565959;">Order #:</td><td style="padding:4px 0;font-size:13px;color:#111111;text-align:right;font-weight:600;">112-9374856-2947163</td></tr>
<tr><td style="padding:4px 0;font-size:13px;color:#565959;">Order Date:</td><td style="padding:4px 0;font-size:13px;color:#111111;text-align:right;">September 23, 2026</td></tr>
<tr><td style="padding:4px 0;font-size:13px;color:#565959;">Order Total:</td><td style="padding:4px 0;font-size:13px;color:#111111;text-align:right;font-weight:600;">$849.99</td></tr>
</table>
</div>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
<tr>
<td width="80" valign="top"><div style="width:72px;height:72px;background-color:#f0f0f0;border-radius:4px;border:1px solid #ddd;text-align:center;line-height:72px;font-size:11px;color:#999;">Image</div></td>
<td valign="top" style="padding-left:12px;">
<p style="margin:0 0 4px;font-size:14px;color:#0066c0;font-weight:600;">Apple AirPods Pro (2nd Generation)</p>
<p style="margin:0 0 4px;font-size:13px;color:#565959;">Qty: 1</p>
<p style="margin:0;font-size:14px;color:#111111;font-weight:600;">$249.99</p>
</td>
</tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
<tr>
<td width="80" valign="top"><div style="width:72px;height:72px;background-color:#f0f0f0;border-radius:4px;border:1px solid #ddd;text-align:center;line-height:72px;font-size:11px;color:#999;">Image</div></td>
<td valign="top" style="padding-left:12px;">
<p style="margin:0 0 4px;font-size:14px;color:#0066c0;font-weight:600;">Samsung 27" 4K UHD Monitor</p>
<p style="margin:0 0 4px;font-size:13px;color:#565959;">Qty: 1</p>
<p style="margin:0;font-size:14px;color:#111111;font-weight:600;">$599.99</p>
</td>
</tr>
</table>
<p style="margin:0 0 16px;font-size:14px;color:#111111;">Didn't place this order? <a href="#" style="color:#0066c0;text-decoration:none;font-weight:600;">Cancel this order</a> or <a href="#" style="color:#0066c0;text-decoration:none;font-weight:600;">report unauthorized activity</a>.</p>
<table width="100%" cellpadding="0" cellspacing="0"><tr>
<td align="center">
<a href="#" style="display:inline-block;background-color:#ffd814;color:#0f1111;text-decoration:none;padding:10px 32px;border-radius:20px;font-size:13px;font-weight:600;border:1px solid #fcd200;">View or Manage Order</a>
</td>
</tr></table>
</td></tr>
<tr><td style="padding:16px 20px;background-color:#f0f2f2;border-top:1px solid #e7e7e7;">
<p style="margin:0;font-size:11px;color:#999999;text-align:center;">&copy; 2026 Amazon.com, Inc. or its affiliates. All rights reserved.</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Phishing Simulation</title></head>
<body style="margin:0;padding:40px 20px;background-color:#eaeded;font-family:Arial,Helvetica,sans-serif;">
<div style="max-width:600px;margin:0 auto;background-color:#d4edda;border:2px solid #28a745;border-radius:8px;padding:32px;">
<h1 style="margin:0 0 16px;color:#155724;font-size:24px;">&#9989; This Was a Phishing Simulation</h1>
<p style="margin:0 0 16px;color:#155724;font-size:16px;">This was a security awareness exercise. Here are the red flags you should have noticed:</p>
<ul style="margin:0 0 16px;padding-left:20px;color:#155724;font-size:14px;line-height:1.8;">
<li><strong>Fake domain:</strong> The email came from <em>amazon-orders.com</em>, not the legitimate <em>amazon.com</em>.</li>
<li><strong>Order you didn't place:</strong> The email reports an $849.99 purchase you never made — this is designed to trigger a panic response so you click "Cancel."</li>
<li><strong>High-value items:</strong> Expensive items (AirPods, monitor) increase the urgency to "cancel" what you think is a fraudulent charge.</li>
<li><strong>Trap links:</strong> Both "Cancel this order" and "report unauthorized activity" lead to credential harvesting pages, not Amazon.</li>
<li><strong>No shipping address:</strong> A real Amazon order confirmation always includes the delivery address.</li>
</ul>
<p style="margin:0;color:#155724;font-size:14px;">Always go directly to amazon.com and check "Your Orders" to verify any purchase notifications.</p>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'GitHub Repository Access Granted',
                'scenario_type' => 'malware_link',
                'subject' => '[GitHub] You\'ve been added to the infrastructure-configs repository',
                'sender_name' => 'GitHub',
                'sender_email' => 'noreply@github-notifications.io',
                'difficulty' => 'hard',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f6f8fa;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f8fa;padding:20px 0;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border:1px solid #d0d7de;border-radius:6px;overflow:hidden;">
<tr><td style="padding:24px 32px;border-bottom:1px solid #d0d7de;">
<table width="100%" cellpadding="0" cellspacing="0"><tr>
<td><span style="font-size:20px;color:#24292f;">
<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 16 16" style="vertical-align:middle;margin-right:8px;"><path fill="#24292f" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path></svg>
GitHub</span></td>
</tr></table>
</td></tr>
<tr><td style="padding:32px;">
<h2 style="margin:0 0 16px;font-size:20px;color:#24292f;font-weight:600;">Repository access granted</h2>
<p style="margin:0 0 20px;font-size:14px;color:#57606a;line-height:1.6;"><strong style="color:#24292f;">@devops-admin</strong> has invited you to collaborate on the <strong style="color:#24292f;">acme-corp/infrastructure-configs</strong> repository.</p>
<div style="background-color:#f6f8fa;border:1px solid #d0d7de;border-radius:6px;padding:16px;margin-bottom:24px;">
<table width="100%" cellpadding="0" cellspacing="0">
<tr><td style="padding:4px 0;font-size:13px;color:#57606a;">Repository:</td><td style="padding:4px 0;font-size:13px;color:#24292f;font-weight:600;">acme-corp/infrastructure-configs</td></tr>
<tr><td style="padding:4px 0;font-size:13px;color:#57606a;">Permission:</td><td style="padding:4px 0;font-size:13px;color:#24292f;">Write access</td></tr>
<tr><td style="padding:4px 0;font-size:13px;color:#57606a;">Description:</td><td style="padding:4px 0;font-size:13px;color:#24292f;">Production infrastructure and deployment configurations</td></tr>
<tr><td style="padding:4px 0;font-size:13px;color:#57606a;">Latest commit:</td><td style="padding:4px 0;font-size:13px;color:#0969da;"><a href="#" style="color:#0969da;text-decoration:none;">fix: update SSL cert rotation script</a></td></tr>
</table>
</div>
<table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:16px;"><tr>
<td align="center">
<a href="#" style="display:inline-block;background-color:#2da44e;color:#ffffff;text-decoration:none;padding:10px 24px;border-radius:6px;font-size:14px;font-weight:600;border:1px solid rgba(27,31,36,.15);">View invitation</a>
</td>
<td width="12"></td>
<td align="center">
<a href="#" style="display:inline-block;background-color:#f6f8fa;color:#24292f;text-decoration:none;padding:10px 24px;border-radius:6px;font-size:14px;font-weight:600;border:1px solid #d0d7de;">Clone repository</a>
</td>
</tr></table>
<p style="margin:0;font-size:12px;color:#57606a;">You can also accept this invitation by visiting the <a href="#" style="color:#0969da;text-decoration:none;">repository page</a> directly.</p>
</td></tr>
<tr><td style="padding:16px 32px;background-color:#f6f8fa;border-top:1px solid #d0d7de;">
<p style="margin:0;font-size:11px;color:#57606a;text-align:center;">You're receiving this because you were granted access. <a href="#" style="color:#0969da;text-decoration:none;">Manage notification settings</a>.</p>
<p style="margin:6px 0 0;font-size:11px;color:#8b949e;text-align:center;">GitHub, Inc. &bull; 88 Colin P Kelly Jr St &bull; San Francisco, CA 94107</p>
</td></tr>
</table>
</td></tr>
</table>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Phishing Simulation</title></head>
<body style="margin:0;padding:40px 20px;background-color:#f6f8fa;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Helvetica,Arial,sans-serif;">
<div style="max-width:600px;margin:0 auto;background-color:#d4edda;border:2px solid #28a745;border-radius:8px;padding:32px;">
<h1 style="margin:0 0 16px;color:#155724;font-size:24px;">&#9989; This Was a Phishing Simulation</h1>
<p style="margin:0 0 16px;color:#155724;font-size:16px;">This was a security awareness exercise. Here are the red flags you should have noticed:</p>
<ul style="margin:0 0 16px;padding-left:20px;color:#155724;font-size:14px;line-height:1.8;">
<li><strong>Fake domain:</strong> The email came from <em>github-notifications.io</em>, not the legitimate <em>github.com</em>.</li>
<li><strong>Sensitive repository name:</strong> "infrastructure-configs" with "Production infrastructure and deployment configurations" is designed to pique curiosity and make clicking seem job-relevant.</li>
<li><strong>Clone repository button:</strong> This could trigger a malicious download. Legitimate GitHub invitations do not offer a direct "Clone" action in the email.</li>
<li><strong>Generic inviter:</strong> "@devops-admin" is a generic username — verify who actually sent the invitation through your internal team channels.</li>
<li><strong>Write access to production:</strong> Being granted write access to production configs unsolicited should be a major red flag warranting immediate verification.</li>
</ul>
<p style="margin:0;color:#155724;font-size:14px;">Always navigate to github.com directly to view and accept repository invitations. Never clone repositories from email links.</p>
</div>
</body>
</html>
HTML,
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
