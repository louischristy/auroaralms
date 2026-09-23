<?php

namespace Database\Seeders;

use App\Models\PhishingTemplate;
use Illuminate\Database\Seeder;

class PhishingTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Urgent Password Reset',
                'scenario_type' => 'credential_harvest',
                'subject' => 'ACTION REQUIRED: Your password expires in 24 hours',
                'sender_name' => 'IT Security Team',
                'sender_email' => 'security@company-helpdesk.com',
                'difficulty' => 'easy',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
<div style="background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0;">
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="background: #dc2626; color: #fff; padding: 8px 16px; border-radius: 4px; display: inline-block; font-weight: bold;">URGENT</div>
    </div>
    <h2 style="color: #1a1a1a; margin-bottom: 10px;">Your Password Will Expire Soon</h2>
    <p style="color: #555; line-height: 1.6;">Dear Employee,</p>
    <p style="color: #555; line-height: 1.6;">Our security system has detected that your password will expire within the next <strong>24 hours</strong>. To avoid losing access to your account and company resources, please update your password immediately.</p>
    <p style="color: #555; line-height: 1.6;">If you do not reset your password before it expires, your account will be temporarily locked and you will need to contact IT support for assistance.</p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="#" style="background: #2563eb; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">Reset Password Now</a>
    </div>
    <p style="color: #888; font-size: 12px;">This is an automated message from your IT Security Team. Please do not reply to this email.</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="color: #aaa; font-size: 11px; text-align: center;">IT Security Department | Company Helpdesk</p>
</div>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Password Reset</title></head>
<body style="font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
<div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
    <h2 style="text-align: center; color: #1a1a1a; margin-bottom: 8px;">Reset Your Password</h2>
    <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 24px;">Enter your credentials to continue</p>
    <form>
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Email</label>
        <input type="email" placeholder="you@company.com" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 16px; box-sizing: border-box;">
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Current Password</label>
        <input type="password" placeholder="Enter current password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 16px; box-sizing: border-box;">
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">New Password</label>
        <input type="password" placeholder="Enter new password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 24px; box-sizing: border-box;">
        <button type="submit" style="width: 100%; padding: 12px; background: #2563eb; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;">Update Password</button>
    </form>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'IT System Upgrade Required',
                'scenario_type' => 'credential_harvest',
                'subject' => 'Mandatory: System Upgrade - Action Required by Friday',
                'sender_name' => 'IT Department',
                'sender_email' => 'it-updates@company-systems.net',
                'difficulty' => 'medium',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
<div style="background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0;">
    <div style="border-left: 4px solid #f59e0b; padding-left: 12px; margin-bottom: 20px;">
        <h2 style="color: #1a1a1a; margin: 0;">System Upgrade Notice</h2>
        <p style="color: #f59e0b; font-size: 13px; margin: 4px 0 0;">Mandatory - All Employees</p>
    </div>
    <p style="color: #555; line-height: 1.6;">Hello,</p>
    <p style="color: #555; line-height: 1.6;">As part of our ongoing infrastructure improvements, we are rolling out a mandatory system upgrade for all employees. This upgrade includes critical security patches and performance enhancements.</p>
    <p style="color: #555; line-height: 1.6;"><strong>What you need to do:</strong></p>
    <ol style="color: #555; line-height: 1.8;">
        <li>Click the link below to access the upgrade portal</li>
        <li>Log in with your company credentials</li>
        <li>Follow the on-screen instructions to complete the upgrade</li>
    </ol>
    <p style="color: #555; line-height: 1.6;"><strong>Deadline:</strong> All employees must complete this upgrade by end of day Friday.</p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="#" style="background: #059669; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">Start Upgrade</a>
    </div>
    <p style="color: #888; font-size: 12px;">Questions? Contact the IT Help Desk at ext. 4357.</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="color: #aaa; font-size: 11px; text-align: center;">IT Department | Internal Communications</p>
</div>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>System Upgrade Portal</title></head>
<body style="font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
<div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 60px; height: 60px; background: #059669; border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;">
            <span style="color: #fff; font-size: 28px;">&#9881;</span>
        </div>
        <h2 style="color: #1a1a1a; margin-bottom: 4px;">Upgrade Portal</h2>
        <p style="color: #666; font-size: 14px;">Sign in to begin the upgrade</p>
    </div>
    <form>
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Username</label>
        <input type="text" placeholder="Enter your username" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 16px; box-sizing: border-box;">
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Password</label>
        <input type="password" placeholder="Enter your password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 24px; box-sizing: border-box;">
        <button type="submit" style="width: 100%; padding: 12px; background: #059669; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;">Sign In & Upgrade</button>
    </form>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'Invoice Payment Overdue',
                'scenario_type' => 'malware_link',
                'subject' => 'OVERDUE: Invoice #INV-2024-8847 - Payment Required',
                'sender_name' => 'Accounts Receivable',
                'sender_email' => 'billing@quickbooks-invoice.com',
                'difficulty' => 'medium',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
<div style="background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: #1a1a1a; margin: 0;">Invoice Overdue</h2>
        <span style="background: #fef2f2; color: #dc2626; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">OVERDUE</span>
    </div>
    <p style="color: #555; line-height: 1.6;">Dear Accounts Payable,</p>
    <p style="color: #555; line-height: 1.6;">This is a reminder that Invoice <strong>#INV-2024-8847</strong> is now <strong>15 days overdue</strong>. Please review the invoice details below and process payment at your earliest convenience to avoid late fees.</p>
    <div style="background: #f9fafb; border-radius: 6px; padding: 16px; margin: 20px 0;">
        <table style="width: 100%; font-size: 14px; color: #555;">
            <tr><td style="padding: 4px 0;"><strong>Invoice Number:</strong></td><td style="text-align: right;">#INV-2024-8847</td></tr>
            <tr><td style="padding: 4px 0;"><strong>Amount Due:</strong></td><td style="text-align: right; color: #dc2626; font-weight: bold;">$4,287.50</td></tr>
            <tr><td style="padding: 4px 0;"><strong>Due Date:</strong></td><td style="text-align: right;">January 2, 2024</td></tr>
            <tr><td style="padding: 4px 0;"><strong>Late Fee:</strong></td><td style="text-align: right;">$128.63</td></tr>
        </table>
    </div>
    <div style="text-align: center; margin: 30px 0;">
        <a href="#" style="background: #2563eb; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">View Invoice & Pay Now</a>
    </div>
    <p style="color: #888; font-size: 12px;">If you have already sent payment, please disregard this notice. For questions, contact billing@vendor-services.com.</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="color: #aaa; font-size: 11px; text-align: center;">Accounts Receivable | Vendor Services LLC</p>
</div>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Invoice Portal</title></head>
<body style="font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
<div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
    <h2 style="text-align: center; color: #1a1a1a; margin-bottom: 8px;">Invoice Payment Portal</h2>
    <p style="text-align: center; color: #666; font-size: 14px; margin-bottom: 24px;">Sign in to view and pay your invoice</p>
    <form>
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Email Address</label>
        <input type="email" placeholder="you@company.com" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 16px; box-sizing: border-box;">
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Password</label>
        <input type="password" placeholder="Enter password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 24px; box-sizing: border-box;">
        <button type="submit" style="width: 100%; padding: 12px; background: #2563eb; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;">Sign In</button>
    </form>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'CEO Wire Transfer Request',
                'scenario_type' => 'urgent_action',
                'subject' => 'Confidential - Urgent Wire Transfer Needed',
                'sender_name' => 'Michael Thompson, CEO',
                'sender_email' => 'michael.thompson@company-executive.com',
                'difficulty' => 'hard',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
<div style="background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0;">
    <p style="color: #555; line-height: 1.6;">Hi,</p>
    <p style="color: #555; line-height: 1.6;">I need you to handle something urgently and confidentially. I'm currently in back-to-back meetings with our board of directors and cannot make calls right now.</p>
    <p style="color: #555; line-height: 1.6;">We need to process a wire transfer of <strong>$47,500</strong> to a new vendor for a time-sensitive acquisition deal. The board has approved this, and we need it completed before 3:00 PM today.</p>
    <p style="color: #555; line-height: 1.6;">Please click the link below to access the secure transfer portal and complete the transaction. I'll provide the full details once you're logged in.</p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="#" style="background: #7c3aed; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">Access Transfer Portal</a>
    </div>
    <p style="color: #555; line-height: 1.6;"><strong>IMPORTANT:</strong> Please keep this confidential until the deal is finalized. Do not discuss with other team members.</p>
    <p style="color: #555; line-height: 1.6;">Thanks for handling this quickly.</p>
    <p style="color: #555; line-height: 1.6;">Best regards,<br><strong>Michael Thompson</strong><br>Chief Executive Officer</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="color: #aaa; font-size: 11px;">Sent from my iPhone</p>
</div>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Secure Transfer Portal</title></head>
<body style="font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
<div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 60px; height: 60px; background: #7c3aed; border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center;">
            <span style="color: #fff; font-size: 28px;">&#128274;</span>
        </div>
        <h2 style="color: #1a1a1a; margin-bottom: 4px;">Secure Transfer Portal</h2>
        <p style="color: #666; font-size: 14px;">Authenticate to proceed</p>
    </div>
    <form>
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Corporate Email</label>
        <input type="email" placeholder="you@company.com" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 16px; box-sizing: border-box;">
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Password</label>
        <input type="password" placeholder="Enter password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 24px; box-sizing: border-box;">
        <button type="submit" style="width: 100%; padding: 12px; background: #7c3aed; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;">Authenticate & Continue</button>
    </form>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'Shared Document Notification',
                'scenario_type' => 'credential_harvest',
                'subject' => 'Document shared with you: "Q4 Budget Review.xlsx"',
                'sender_name' => 'Google Drive',
                'sender_email' => 'drive-noreply@google-docs-share.com',
                'difficulty' => 'easy',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
<div style="background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0;">
    <div style="text-align: center; margin-bottom: 20px;">
        <div style="width: 48px; height: 48px; margin: 0 auto;">
            <svg viewBox="0 0 48 48" style="width: 48px; height: 48px;"><polygon points="24,4 44,14 44,34 24,44 4,34 4,14" fill="#4285f4"/><text x="24" y="28" text-anchor="middle" fill="white" font-size="16" font-family="Arial">D</text></svg>
        </div>
    </div>
    <p style="color: #555; line-height: 1.6; text-align: center;"><strong>Sarah Johnson</strong> has shared a document with you:</p>
    <div style="background: #f0f7ff; border: 1px solid #d0e3ff; border-radius: 8px; padding: 16px; margin: 20px 0; text-align: center;">
        <p style="font-size: 18px; color: #1a73e8; margin: 0; font-weight: bold;">Q4 Budget Review.xlsx</p>
        <p style="color: #666; font-size: 13px; margin-top: 4px;">Spreadsheet - 2.4 MB</p>
    </div>
    <p style="color: #555; line-height: 1.6; text-align: center; font-size: 14px;">Sarah added the note: "Please review the numbers in Tab 3 before our meeting tomorrow."</p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="#" style="background: #1a73e8; color: #ffffff; padding: 12px 30px; border-radius: 6px; text-decoration: none; font-weight: bold; display: inline-block;">Open in Google Drive</a>
    </div>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="color: #aaa; font-size: 11px; text-align: center;">Google LLC, 1600 Amphitheatre Parkway, Mountain View, CA 94043</p>
</div>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Sign in - Google Accounts</title></head>
<body style="font-family: Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
<div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #1a1a1a; margin-bottom: 4px; font-size: 24px;">Sign in</h2>
        <p style="color: #666; font-size: 14px;">to continue to Google Drive</p>
    </div>
    <form>
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Email or phone</label>
        <input type="email" placeholder="Enter your email" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 16px; box-sizing: border-box;">
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Password</label>
        <input type="password" placeholder="Enter your password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 24px; box-sizing: border-box;">
        <button type="submit" style="width: 100%; padding: 12px; background: #1a73e8; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;">Next</button>
    </form>
</div>
</body>
</html>
HTML,
            ],
            [
                'name' => 'Security Alert - Unusual Login',
                'scenario_type' => 'credential_harvest',
                'subject' => 'Security Alert: Unusual sign-in activity on your account',
                'sender_name' => 'Microsoft Account Security',
                'sender_email' => 'account-security@microsoft-alerts.com',
                'difficulty' => 'medium',
                'body_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f5f5f5;">
<div style="background: #ffffff; border-radius: 8px; padding: 30px; border: 1px solid #e0e0e0;">
    <div style="margin-bottom: 20px;">
        <span style="font-size: 22px; font-weight: bold; color: #0078d4;">Microsoft</span>
    </div>
    <h2 style="color: #1a1a1a; margin-bottom: 10px; font-size: 20px;">Unusual sign-in activity</h2>
    <p style="color: #555; line-height: 1.6;">We detected something unusual about a recent sign-in to your Microsoft account.</p>
    <div style="background: #fff4e5; border-left: 4px solid #ff8c00; padding: 12px 16px; margin: 20px 0; border-radius: 0 6px 6px 0;">
        <p style="margin: 0; color: #333; font-size: 14px;"><strong>Sign-in details:</strong></p>
    </div>
    <table style="width: 100%; font-size: 14px; color: #555; margin: 16px 0;">
        <tr><td style="padding: 6px 0;"><strong>Country/region:</strong></td><td>Russia</td></tr>
        <tr><td style="padding: 6px 0;"><strong>IP address:</strong></td><td>185.220.101.47</td></tr>
        <tr><td style="padding: 6px 0;"><strong>Date:</strong></td><td>January 17, 2024 at 3:42 AM</td></tr>
        <tr><td style="padding: 6px 0;"><strong>Platform:</strong></td><td>Windows 10</td></tr>
        <tr><td style="padding: 6px 0;"><strong>Browser:</strong></td><td>Chrome 120.0</td></tr>
    </table>
    <p style="color: #555; line-height: 1.6;">If this was you, you can safely ignore this message. If not, please secure your account immediately.</p>
    <div style="text-align: center; margin: 30px 0;">
        <a href="#" style="background: #0078d4; color: #ffffff; padding: 12px 30px; border-radius: 4px; text-decoration: none; font-weight: bold; display: inline-block;">Review Recent Activity</a>
    </div>
    <p style="color: #888; font-size: 12px;">You can also verify your identity and change your password to help secure your account.</p>
    <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;">
    <p style="color: #aaa; font-size: 11px; text-align: center;">Microsoft Corporation, One Microsoft Way, Redmond, WA 98052</p>
</div>
</body>
</html>
HTML,
                'landing_page_html' => <<<'HTML'
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Sign in to your account</title></head>
<body style="font-family: 'Segoe UI', Arial, sans-serif; background: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0;">
<div style="background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
    <div style="margin-bottom: 24px;">
        <span style="font-size: 22px; font-weight: bold; color: #0078d4;">Microsoft</span>
    </div>
    <h2 style="color: #1a1a1a; margin-bottom: 4px; font-size: 20px;">Sign in</h2>
    <p style="color: #666; font-size: 14px; margin-bottom: 24px;">Verify your identity to review account activity</p>
    <form>
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Email, phone, or Skype</label>
        <input type="email" placeholder="Enter your email" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 16px; box-sizing: border-box;">
        <label style="display: block; font-size: 14px; color: #333; margin-bottom: 4px;">Password</label>
        <input type="password" placeholder="Enter password" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 24px; box-sizing: border-box;">
        <button type="submit" style="width: 100%; padding: 12px; background: #0078d4; color: #fff; border: none; border-radius: 4px; font-size: 16px; cursor: pointer;">Sign in</button>
    </form>
</div>
</body>
</html>
HTML,
            ],
        ];

        foreach ($templates as $template) {
            PhishingTemplate::updateOrCreate(
                ['name' => $template['name']],
                array_merge($template, ["is_system" => true])
            );
        }
    }
}
