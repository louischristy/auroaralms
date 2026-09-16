<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Database\Seeder;

class CourseSeederBatch2 extends Seeder
{
    public function run(): void
    {
        $courses = $this->getCourses();

        foreach ($courses as $courseData) {
            $course = Course::updateOrCreate(
                ['slug' => $courseData['slug']],
                [
                    'title' => $courseData['title'],
                    'description' => $courseData['description'],
                    'objectives' => $courseData['objectives'],
                    'category' => $courseData['category'],
                    'difficulty' => $courseData['difficulty'],
                    'duration_minutes' => $courseData['duration_minutes'],
                    'passing_score' => $courseData['passing_score'],
                    'is_active' => true,
                    'is_mandatory' => $courseData['is_mandatory'] ?? false,
                    'sort_order' => $courseData['sort_order'],
                ]
            );

            // Lessons
            foreach ($courseData['lessons'] as $i => $lessonData) {
                Lesson::updateOrCreate(
                    ['course_id' => $course->id, 'slug' => $lessonData['slug']],
                    [
                        'title' => $lessonData['title'],
                        'content' => $lessonData['content'],
                        'content_type' => $lessonData['content_type'] ?? 'text',
                        'video_url' => $lessonData['video_url'] ?? null,
                        'duration_minutes' => $lessonData['duration_minutes'],
                        'sort_order' => $i,
                        'is_active' => true,
                    ]
                );
            }

            // Quiz
            if (!empty($courseData['quiz'])) {
                $quiz = Quiz::updateOrCreate(
                    ['course_id' => $course->id],
                    [
                        'title' => $courseData['quiz']['title'],
                        'instructions' => $courseData['quiz']['instructions'] ?? null,
                        'max_attempts' => 3,
                        'shuffle_questions' => true,
                        'show_correct_answers' => true,
                        'is_active' => true,
                    ]
                );

                foreach ($courseData['quiz']['questions'] as $qi => $qData) {
                    $question = QuizQuestion::updateOrCreate(
                        ['quiz_id' => $quiz->id, 'sort_order' => $qi],
                        [
                            'question' => $qData['question'],
                            'question_type' => $qData['type'] ?? 'multiple_choice',
                            'explanation' => $qData['explanation'] ?? null,
                            'points' => 1,
                        ]
                    );

                    // Delete old answers and recreate
                    $question->answers()->delete();
                    foreach ($qData['answers'] as $ai => $aData) {
                        QuizAnswer::create([
                            'question_id' => $question->id,
                            'answer_text' => $aData['text'],
                            'is_correct' => $aData['correct'] ?? false,
                            'sort_order' => $ai,
                        ]);
                    }
                }
            }
        }
    }

    private function getCourses(): array
    {
        return [
            // ── Module 6: Secure Email Practices ──
            [
                'title' => 'Secure Email Practices',
                'slug' => 'secure-email-practices',
                'description' => 'Build good daily habits for sending, receiving, and managing email securely at work.',
                'objectives' => [
                    'Configure and use secure email settings',
                    'Recognize spoofing, encryption, and authentication protections',
                    'Apply best practices for sending sensitive information by email',
                    'Avoid common email mistakes that lead to data leaks',
                ],
                'category' => 'Phishing & Email Security',
                'difficulty' => 'intermediate',
                'duration_minutes' => 22,
                'passing_score' => 70,
                'sort_order' => 5,
                'lessons' => [
                    [
                        'title' => 'How Email Authentication Protects You',
                        'slug' => 'email-authentication-protocols',
                        'duration_minutes' => 7,
                        'content' => '<h2>How Email Authentication Protects You</h2>
<p>Email was never designed with security in mind, which is why anyone can technically "spoof" a sender address. Modern authentication protocols exist to make spoofing harder to pull off — and easier to catch.</p>

<h3>SPF (Sender Policy Framework)</h3>
<p>Lets a domain publish a list of mail servers authorized to send email on its behalf. If an email claims to be from <code>@yourcompany.com</code> but comes from a server not on that list, SPF flags it.</p>

<h3>DKIM (DomainKeys Identified Mail)</h3>
<p>Adds a digital signature to outgoing email, verified using a public key published in DNS. This proves the message was not altered in transit and really was sent by the claimed domain.</p>

<h3>DMARC (Domain-based Message Authentication)</h3>
<p>Tells receiving mail servers what to do when SPF or DKIM checks fail — quarantine, reject, or allow — and sends reports back to the domain owner about spoofing attempts.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>What this means for you:</strong> These protocols run automatically in the background. They reduce spoofed mail reaching your inbox, but they are not perfect — look-alike domains (like <code>yourcompany-support.com</code>) still pass all three checks because they are technically legitimate domains.
</div>

<h3>Reading Email Headers (Advanced)</h3>
<p>When something feels off, you can view the full email headers ("Show Original" in Gmail, "View Source" in Outlook) to see the <code>Return-Path</code> and <code>Received</code> fields, which reveal the true sending server — even when the display name looks legitimate.</p>',
                    ],
                    [
                        'title' => 'Sending Sensitive Information Safely',
                        'slug' => 'sending-sensitive-information',
                        'duration_minutes' => 8,
                        'content' => '<h2>Sending Sensitive Information Safely</h2>
<p>Email is convenient but inherently insecure in transit unless extra steps are taken. Follow these rules before you hit send.</p>

<h3>Before You Send</h3>
<ul>
<li><strong>Double-check every recipient</strong> — autocomplete is the #1 cause of accidental data leaks</li>
<li><strong>Remove unnecessary CC/BCC</strong> recipients, especially on reply-all threads</li>
<li><strong>Ask: does this need to be email at all?</strong> Some information belongs in a secure portal, not an inbox</li>
</ul>

<h3>Protecting the Content</h3>
<ul>
<li><strong>Password-protect attachments</strong> containing confidential data, and send the password via a different channel (phone, SMS, chat)</li>
<li><strong>Use encrypted email</strong> tools (S/MIME, PGP, or your organization\'s secure send feature) for regulated data</li>
<li><strong>Avoid pasting sensitive data directly</strong> into the email body — attachments are easier to control and revoke</li>
<li><strong>Use expiring links</strong> from approved file-sharing tools instead of attaching large sensitive files directly</li>
</ul>

<h3>Common Mistakes That Cause Breaches</h3>
<table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
<tr style="background: #FEF2F2;"><th style="padding: 8px; text-align: left; border: 1px solid #E5E7EB;">Mistake</th><th style="padding: 8px; text-align: left; border: 1px solid #E5E7EB;">Consequence</th></tr>
<tr><td style="padding: 8px; border: 1px solid #E5E7EB;">Autocomplete sends to wrong "John"</td><td style="padding: 8px; border: 1px solid #E5E7EB;">Data sent to unrelated external contact</td></tr>
<tr><td style="padding: 8px; border: 1px solid #E5E7EB;">Reply-all on a large distribution list</td><td style="padding: 8px; border: 1px solid #E5E7EB;">Confidential info exposed to unintended staff</td></tr>
<tr><td style="padding: 8px; border: 1px solid #E5E7EB;">Forwarding a whole thread</td><td style="padding: 8px; border: 1px solid #E5E7EB;">Earlier confidential messages exposed further down the chain</td></tr>
</table>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Tip:</strong> Many mail clients support a short "undo send" delay (5–30 seconds). Enable it — it has saved countless accidental sends.
</div>',
                    ],
                    [
                        'title' => 'Email Hygiene & Inbox Habits',
                        'slug' => 'email-hygiene-habits',
                        'duration_minutes' => 7,
                        'content' => '<h2>Email Hygiene & Inbox Habits</h2>

<h3>Managing Your Inbox Securely</h3>
<ul>
<li><strong>Set up MFA</strong> on your email account — it is often the master key to every other account via password resets</li>
<li><strong>Review connected apps</strong> periodically and revoke access for anything you no longer use</li>
<li><strong>Watch your "Sent" and "Rules" folders</strong> — attackers who compromise an account often create hidden forwarding rules to silently exfiltrate mail</li>
<li><strong>Log out of shared or public computers</strong> after checking email</li>
</ul>

<h3>Recognizing a Compromised Account</h3>
<ul>
<li>Contacts report receiving strange emails "from you" that you didn\'t send</li>
<li>Sent items contain messages you don\'t recognize</li>
<li>Unexpected password reset or login alert emails</li>
<li>New, unfamiliar forwarding rules or filters appear</li>
</ul>

<h3>If Your Account Is Compromised</h3>
<ol>
<li>Change your password immediately from a clean, trusted device</li>
<li>Enable or re-verify MFA</li>
<li>Check and remove any unfamiliar forwarding rules or delegate access</li>
<li>Report the incident to IT security right away</li>
<li>Warn contacts who may have received malicious mail from your account</li>
</ol>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Good habit:</strong> Periodically check your account\'s "recent activity" or "sign-in history" page — most providers show you every device and location that has accessed your mailbox.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Secure Email Practices Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What does SPF (Sender Policy Framework) do?',
                            'explanation' => 'SPF lets a domain publish which mail servers are authorized to send email on its behalf, helping receiving servers detect spoofed senders.',
                            'answers' => [
                                ['text' => 'Encrypts the body of an email', 'correct' => false],
                                ['text' => 'Lists which mail servers are authorized to send for a domain', 'correct' => true],
                                ['text' => 'Blocks all attachments automatically', 'correct' => false],
                                ['text' => 'Scans for grammar errors', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the most common cause of accidentally emailing sensitive data to the wrong person?',
                            'explanation' => 'Autocomplete suggesting a similarly-named contact is the leading cause of accidental data leaks via email.',
                            'answers' => [
                                ['text' => 'Malware on the sender\'s computer', 'correct' => false],
                                ['text' => 'Autocomplete selecting the wrong recipient', 'correct' => true],
                                ['text' => 'A weak email password', 'correct' => false],
                                ['text' => 'Server misconfiguration', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Best practice when sending a password-protected sensitive attachment?',
                            'explanation' => 'Sending the password through a separate channel (SMS, phone call, chat) prevents an intercepted email from exposing both the file and its password together.',
                            'answers' => [
                                ['text' => 'Include the password in the same email', 'correct' => false],
                                ['text' => 'Send the password via a different communication channel', 'correct' => true],
                                ['text' => 'Use the recipient\'s name as the password', 'correct' => false],
                                ['text' => 'Skip the password to save time', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You notice a forwarding rule in your mailbox that you never created. What does this suggest?',
                            'explanation' => 'Attackers who compromise an account often set up hidden forwarding rules to quietly exfiltrate future emails.',
                            'answers' => [
                                ['text' => 'A normal IT maintenance task', 'correct' => false],
                                ['text' => 'Your account may be compromised', 'correct' => true],
                                ['text' => 'A spam filter update', 'correct' => false],
                                ['text' => 'Nothing to worry about', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you avoid reply-all on large distribution lists when sharing sensitive updates?',
                            'explanation' => 'Reply-all on large lists can expose confidential information to far more people than intended.',
                            'answers' => [
                                ['text' => 'It uses too much server storage', 'correct' => false],
                                ['text' => 'It may expose confidential information to unintended recipients', 'correct' => true],
                                ['text' => 'It triggers spam filters', 'correct' => false],
                                ['text' => 'It is against email etiquette only, not a security issue', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 7: Business Email Compromise Defense ──
            [
                'title' => 'Business Email Compromise Defense',
                'slug' => 'business-email-compromise-defense',
                'description' => 'Learn how BEC scams target finance and executive workflows, and how to stop fraudulent wire transfers before they happen.',
                'objectives' => [
                    'Understand how BEC attacks are planned and executed',
                    'Recognize the warning signs of a fraudulent payment request',
                    'Apply verification controls for financial transactions',
                    'Know how to respond if a fraudulent transfer occurs',
                ],
                'category' => 'Phishing & Email Security',
                'difficulty' => 'intermediate',
                'duration_minutes' => 24,
                'passing_score' => 70,
                'sort_order' => 6,
                'lessons' => [
                    [
                        'title' => 'Anatomy of a BEC Attack',
                        'slug' => 'anatomy-of-bec-attack',
                        'duration_minutes' => 8,
                        'content' => '<h2>Anatomy of a Business Email Compromise (BEC) Attack</h2>
<p>BEC is one of the costliest forms of cybercrime — the FBI\'s Internet Crime Complaint Center has attributed tens of billions of dollars in losses to BEC over the past decade. Unlike mass phishing, BEC is highly targeted and often involves no malware at all — just careful social engineering.</p>

<h3>Stage 1: Reconnaissance</h3>
<p>Attackers research a company through LinkedIn, press releases, and public filings to learn the names of executives, finance staff, vendors, and typical payment processes.</p>

<h3>Stage 2: Access or Spoofing</h3>
<p>The attacker either:</p>
<ul>
<li>Compromises a real executive or vendor mailbox via a prior phishing attack, or</li>
<li>Registers a look-alike domain (e.g., <code>company-lnc.com</code> instead of <code>company-inc.com</code>) to spoof a trusted sender</li>
</ul>

<h3>Stage 3: The Request</h3>
<p>A carefully timed email arrives — often when the real executive is known to be traveling — requesting an urgent wire transfer, a change to vendor banking details, or a batch of gift cards.</p>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Classic BEC scenarios:</strong> CEO fraud ("I need this wire sent before my flight"), vendor invoice fraud ("our bank account has changed, please update"), and payroll diversion ("please update my direct deposit details").
</div>

<h3>Stage 4: The Payout</h3>
<p>Funds are sent to an account controlled by the attacker, often routed through several accounts or converted to cryptocurrency within hours to frustrate recovery efforts.</p>',
                    ],
                    [
                        'title' => 'Red Flags in Financial Requests',
                        'slug' => 'red-flags-financial-requests',
                        'duration_minutes' => 8,
                        'content' => '<h2>Red Flags in Financial Requests</h2>

<h3>Behavioral Red Flags</h3>
<ul>
<li><strong>Unusual urgency:</strong> "This must be done in the next hour" or "before I board my flight"</li>
<li><strong>Secrecy:</strong> "Don\'t discuss this with anyone else on the team"</li>
<li><strong>Deviation from process:</strong> Bypassing the normal approval chain or purchase order system</li>
<li><strong>Communication only by email:</strong> The requester avoids phone calls or video</li>
</ul>

<h3>Technical Red Flags</h3>
<ul>
<li>Sender domain is subtly different from the real one (extra letter, hyphen, different TLD)</li>
<li>Reply-to address differs from the sender address</li>
<li>Message sent outside normal business hours for that person</li>
<li>Sudden change to previously established banking details</li>
</ul>

<h3>Vendor Invoice Fraud Specifics</h3>
<p>A very common BEC variant targets accounts payable teams:</p>
<ol>
<li>Attacker monitors or spoofs a real vendor\'s email thread</li>
<li>Sends an "updated invoice" or "new banking details" message that looks routine</li>
<li>Payment is redirected to the attacker\'s account instead of the real vendor</li>
</ol>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Golden rule:</strong> Any change to banking details or payment instructions — no matter who appears to request it — must be verified by phone using a number you already have on file, never a number provided in the email itself.
</div>',
                    ],
                    [
                        'title' => 'Verification Controls & Incident Response',
                        'slug' => 'bec-verification-controls',
                        'duration_minutes' => 8,
                        'content' => '<h2>Verification Controls & Incident Response</h2>

<h3>Building Strong Controls</h3>
<ul>
<li><strong>Dual approval:</strong> Require two authorized people to approve any wire transfer above a set threshold</li>
<li><strong>Callback verification:</strong> Always confirm payment changes by phone, using a previously known number — not one supplied in the request</li>
<li><strong>Out-of-band confirmation:</strong> Verify unusual executive requests via a separate channel (a call, an in-person check, or a messaging app the executive is known to use)</li>
<li><strong>Vendor onboarding checks:</strong> Verify new vendor bank details independently before the first payment</li>
<li><strong>Payment cooling-off period:</strong> Add a short delay for first-time or changed payment destinations</li>
</ul>

<h3>Training Finance Teams</h3>
<p>Finance and accounts payable staff should be specifically trained to slow down and verify — not just general staff phishing awareness. They are the primary target of BEC.</p>

<h3>If a Fraudulent Transfer Happens</h3>
<ol>
<li><strong>Act within minutes, not hours</strong> — contact your bank immediately to attempt a recall or hold</li>
<li><strong>Report to law enforcement</strong> (e.g., via the FBI\'s IC3 or your local cybercrime unit) as soon as possible — faster reporting significantly improves recovery odds</li>
<li><strong>Notify your security team</strong> to check for a compromised mailbox or the source of the spoofed domain</li>
<li><strong>Preserve all evidence</strong> — do not delete the fraudulent emails</li>
<li><strong>Review and tighten controls</strong> to prevent repeat incidents</li>
</ol>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Remember:</strong> The single best defense against BEC costs nothing — pick up the phone and call a known, trusted number before moving money or changing payment details.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Business Email Compromise Defense Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What makes BEC different from typical mass phishing?',
                            'explanation' => 'BEC is highly targeted, researched, and often relies purely on social engineering rather than malware.',
                            'answers' => [
                                ['text' => 'It always involves ransomware', 'correct' => false],
                                ['text' => 'It is highly targeted and relies on social engineering rather than malware', 'correct' => true],
                                ['text' => 'It only targets individuals, never companies', 'correct' => false],
                                ['text' => 'It is sent from public Gmail accounts only', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You receive an email claiming to be from a vendor asking you to update their bank account details for future payments. What should you do?',
                            'explanation' => 'Banking detail changes must always be verified through a known, independent channel — never trust the contact info given in the request itself.',
                            'answers' => [
                                ['text' => 'Update the details immediately since the request looks official', 'correct' => false],
                                ['text' => 'Reply to the email to confirm', 'correct' => false],
                                ['text' => 'Call the vendor using a previously known phone number to verify', 'correct' => true],
                                ['text' => 'Ignore the email entirely', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of these is a classic behavioral red flag of a BEC attempt?',
                            'explanation' => 'Extreme urgency combined with a request for secrecy is a hallmark of BEC social engineering.',
                            'answers' => [
                                ['text' => 'A normal invoice sent during business hours', 'correct' => false],
                                ['text' => 'Extreme urgency and a request to keep it confidential', 'correct' => true],
                                ['text' => 'An email from a verified internal address about routine matters', 'correct' => false],
                                ['text' => 'A scheduled monthly report', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the recommended control for wire transfers above a certain amount?',
                            'explanation' => 'Dual approval requiring two authorized individuals significantly reduces the risk of a single compromised employee approving a fraudulent transfer.',
                            'answers' => [
                                ['text' => 'Single approval by the fastest available staff member', 'correct' => false],
                                ['text' => 'Dual approval by two authorized people', 'correct' => true],
                                ['text' => 'No approval needed if the requester is a manager', 'correct' => false],
                                ['text' => 'Approval only required for transfers under $1,000', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'If your organization discovers it just sent a fraudulent wire transfer, what should be done FIRST?',
                            'explanation' => 'Speed matters enormously in fraud recovery — contacting the bank immediately gives the best chance of recalling or freezing the funds.',
                            'answers' => [
                                ['text' => 'Wait until the next business day to report it', 'correct' => false],
                                ['text' => 'Contact the bank immediately to attempt a recall', 'correct' => true],
                                ['text' => 'Delete the fraudulent emails to avoid embarrassment', 'correct' => false],
                                ['text' => 'Confront the suspected attacker directly', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 8: Multi-Factor Authentication Deep Dive ──
            [
                'title' => 'Multi-Factor Authentication Deep Dive',
                'slug' => 'mfa-deep-dive',
                'description' => 'Go beyond the basics of MFA — understand attack techniques against it and how to choose and use the strongest methods available.',
                'objectives' => [
                    'Explain why MFA matters and how each factor type works',
                    'Recognize MFA bypass techniques like SIM swapping and MFA fatigue',
                    'Select the strongest MFA method for a given situation',
                    'Set up MFA correctly and manage recovery options safely',
                ],
                'category' => 'Password & Authentication',
                'difficulty' => 'intermediate',
                'duration_minutes' => 22,
                'passing_score' => 70,
                'sort_order' => 7,
                'lessons' => [
                    [
                        'title' => 'MFA Methods Compared',
                        'slug' => 'mfa-methods-compared',
                        'duration_minutes' => 7,
                        'content' => '<h2>MFA Methods Compared</h2>
<p>Not all MFA is created equal. Understanding the strengths and weaknesses of each method helps you choose wisely and recognize when a method isn\'t enough.</p>

<h3>SMS / Voice Codes</h3>
<p>A one-time code sent by text or phone call. Convenient, but vulnerable to <strong>SIM swapping</strong> (an attacker convinces your carrier to transfer your number to their device) and to interception on insecure networks.</p>

<h3>Authenticator Apps (TOTP)</h3>
<p>Apps like Google Authenticator or Microsoft Authenticator generate a time-based one-time code (TOTP) directly on your device, with no network transmission required. Far more resistant to interception than SMS.</p>

<h3>Push Notifications</h3>
<p>The app sends an "Approve/Deny" prompt to your phone. Convenient, but vulnerable to <strong>MFA fatigue attacks</strong> (see next lesson) if you approve prompts without thinking.</p>

<h3>Hardware Security Keys (FIDO2/WebAuthn)</h3>
<p>Physical devices (e.g., YubiKey) that use public-key cryptography bound to the specific website you\'re logging into. This makes them <strong>phishing-resistant</strong> — even a perfect fake login page cannot use your key to authenticate elsewhere.</p>

<h3>Biometrics</h3>
<p>Fingerprint or facial recognition, usually used to unlock a device or app locally rather than transmitted anywhere. Strong when paired with hardware-backed storage (e.g., a phone\'s secure enclave).</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Security ranking (weakest to strongest):</strong> SMS codes &lt; Push notifications &lt; Authenticator app codes &lt; Hardware security keys.
</div>',
                    ],
                    [
                        'title' => 'How Attackers Bypass MFA',
                        'slug' => 'how-attackers-bypass-mfa',
                        'duration_minutes' => 8,
                        'content' => '<h2>How Attackers Bypass MFA</h2>
<p>MFA blocks the vast majority of automated attacks, but determined attackers have developed specific techniques to get around it. Knowing them helps you avoid falling for them.</p>

<h3>MFA Fatigue (Prompt Bombing)</h3>
<p>An attacker who already has your password triggers repeated push notification requests, hoping you\'ll get annoyed or confused and tap "Approve" just to make them stop.</p>
<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 12px 0;">
<strong>Defense:</strong> Never approve a push notification you didn\'t initiate. If you receive unexpected prompts, deny them all and change your password immediately — it means someone has it.
</div>

<h3>SIM Swapping</h3>
<p>The attacker impersonates you to your mobile carrier (using stolen personal details) and convinces them to port your phone number to a SIM card they control, allowing them to receive your SMS codes.</p>

<h3>Real-Time Phishing (Adversary-in-the-Middle)</h3>
<p>A fake login page silently relays your credentials and MFA code to the real site in real time, capturing the session token before it expires. This defeats SMS and authenticator app codes but <strong>not</strong> hardware security keys, because the key cryptographically verifies the actual website domain.</p>

<h3>SS7 Network Exploits</h3>
<p>Sophisticated attackers can exploit weaknesses in telecom signaling protocols to intercept SMS messages, without needing physical access to your phone at all.</p>

<h3>Session Hijacking</h3>
<p>After a successful login, attackers steal the session cookie/token (via malware or a compromised browser extension) — bypassing the need to defeat MFA at all, since the session is already authenticated.</p>',
                    ],
                    [
                        'title' => 'Setting Up MFA the Right Way',
                        'slug' => 'setting-up-mfa-correctly',
                        'duration_minutes' => 7,
                        'content' => '<h2>Setting Up MFA the Right Way</h2>

<h3>Choosing Your Method</h3>
<ul>
<li>Use an <strong>authenticator app or hardware key</strong> wherever offered, instead of SMS</li>
<li>For your most critical accounts (email, banking, password manager), use a <strong>hardware security key</strong> if the service supports it</li>
<li>Enable MFA on <strong>every</strong> account that offers it, not just work accounts — personal email is often the recovery path into everything else</li>
</ul>

<h3>Managing Recovery Codes</h3>
<ul>
<li>When you enable MFA, you\'ll typically be given one-time <strong>backup/recovery codes</strong> — store them somewhere secure, such as a password manager, not a sticky note or plain text file</li>
<li>Never share recovery codes with anyone, including someone claiming to be "IT support" — legitimate IT will never ask for them</li>
<li>Regenerate recovery codes if you suspect they may have been exposed</li>
</ul>

<h3>Registering Multiple Methods</h3>
<p>Register at least two MFA methods (e.g., an authenticator app plus a backup hardware key) so you\'re not locked out if you lose your primary device.</p>

<h3>Responding to Suspicious MFA Activity</h3>
<ul>
<li>Unexpected push notifications → deny and change your password</li>
<li>A text saying your carrier plan/SIM changed and you didn\'t request it → contact your carrier immediately (possible SIM swap)</li>
<li>Login alerts from unfamiliar locations or devices → investigate and revoke that session</li>
</ul>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Bottom line:</strong> MFA dramatically reduces your risk, but it is not a silver bullet. Combine it with strong, unique passwords and healthy skepticism toward unexpected prompts.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'MFA Deep Dive Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'Why are hardware security keys considered "phishing-resistant"?',
                            'explanation' => 'Hardware keys use cryptography bound to the actual website domain, so even a perfect fake login page cannot successfully use the key to authenticate.',
                            'answers' => [
                                ['text' => 'They cannot be lost or stolen', 'correct' => false],
                                ['text' => 'They cryptographically verify the actual website domain being logged into', 'correct' => true],
                                ['text' => 'They don\'t require a password at all', 'correct' => false],
                                ['text' => 'They automatically block phishing emails', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You suddenly receive 10 MFA push notifications in a row that you did not request. What is happening and what should you do?',
                            'explanation' => 'This is an MFA fatigue (prompt bombing) attack. You should deny all prompts and change your password immediately, since the attacker already has it.',
                            'answers' => [
                                ['text' => 'Approve one to make them stop', 'correct' => false],
                                ['text' => 'This is an MFA fatigue attack — deny all prompts and change your password', 'correct' => true],
                                ['text' => 'Ignore it, it\'s probably a glitch', 'correct' => false],
                                ['text' => 'Turn off MFA to stop the notifications', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is "SIM swapping"?',
                            'explanation' => 'SIM swapping is when an attacker convinces your mobile carrier to transfer your phone number to a SIM card they control, letting them intercept SMS-based MFA codes.',
                            'answers' => [
                                ['text' => 'Switching phone carriers for better rates', 'correct' => false],
                                ['text' => 'An attacker convincing your carrier to port your number to their SIM card', 'correct' => true],
                                ['text' => 'Physically stealing someone\'s phone', 'correct' => false],
                                ['text' => 'A type of malware that infects SIM cards', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which MFA method is generally weakest against modern attacks?',
                            'explanation' => 'SMS codes are the weakest common MFA method, vulnerable to SIM swapping and SS7 interception.',
                            'answers' => [
                                ['text' => 'Hardware security keys', 'correct' => false],
                                ['text' => 'Authenticator app codes', 'correct' => false],
                                ['text' => 'SMS text message codes', 'correct' => true],
                                ['text' => 'Biometric unlock with hardware-backed storage', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Where should you store MFA backup/recovery codes?',
                            'explanation' => 'Recovery codes should be stored securely, such as in a password manager, never in plain text or shared with anyone.',
                            'answers' => [
                                ['text' => 'In a sticky note on your monitor', 'correct' => false],
                                ['text' => 'In a securely encrypted password manager', 'correct' => true],
                                ['text' => 'Shared with a coworker as backup', 'correct' => false],
                                ['text' => 'In a plain text file on your desktop', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 9: Insider Threat Awareness ──
            [
                'title' => 'Insider Threat Awareness',
                'slug' => 'insider-threat-awareness',
                'description' => 'Understand the risks posed by insiders — malicious, negligent, or compromised — and how to help prevent and report insider threats.',
                'objectives' => [
                    'Differentiate between malicious, negligent, and compromised insiders',
                    'Recognize behavioral and technical warning signs',
                    'Understand organizational controls that mitigate insider risk',
                    'Know how and when to report a concern',
                ],
                'category' => 'Social Engineering',
                'difficulty' => 'intermediate',
                'duration_minutes' => 22,
                'passing_score' => 70,
                'sort_order' => 8,
                'lessons' => [
                    [
                        'title' => 'What Is an Insider Threat?',
                        'slug' => 'what-is-insider-threat',
                        'duration_minutes' => 7,
                        'content' => '<h2>What Is an Insider Threat?</h2>
<p>An insider threat comes from someone who already has legitimate access to your organization\'s systems and data — an employee, contractor, or business partner — rather than an outside attacker breaking in.</p>

<h3>Three Categories of Insider Threats</h3>

<h4>1. Malicious Insiders</h4>
<p>Individuals who intentionally misuse their access, often driven by financial gain, revenge (e.g., after a termination or grievance), or ideology. Examples include stealing customer data to sell, sabotaging systems before leaving, or leaking trade secrets to a competitor.</p>

<h4>2. Negligent Insiders</h4>
<p>Well-meaning employees who cause harm through carelessness — falling for phishing, misconfiguring cloud storage permissions, using weak passwords, or losing an unencrypted laptop. This is by far the <strong>most common</strong> category.</p>

<h4>3. Compromised Insiders</h4>
<p>Legitimate accounts that have been taken over by an external attacker (via phishing, malware, or credential theft), who then operates using that trusted access — making it far harder to detect than an outside intrusion.</p>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Why it matters:</strong> Insider incidents often go undetected far longer than external breaches, precisely because the activity comes from a trusted, authorized account.
</div>',
                    ],
                    [
                        'title' => 'Warning Signs to Watch For',
                        'slug' => 'insider-threat-warning-signs',
                        'duration_minutes' => 7,
                        'content' => '<h2>Warning Signs to Watch For</h2>
<p>Insider threats are rarely sudden — there are usually early indicators, both behavioral and technical.</p>

<h3>Behavioral Indicators</h3>
<ul>
<li>Expressing strong disgruntlement about the organization, a manager, or an upcoming termination</li>
<li>Working unusual hours without a clear business reason, especially accessing sensitive systems late at night</li>
<li>Requesting access to data or systems well beyond their role\'s needs</li>
<li>Sudden, unexplained changes in financial circumstances or lifestyle</li>
<li>Attempting to bypass established security or approval processes</li>
</ul>

<h3>Technical Indicators</h3>
<ul>
<li>Large or unusual data downloads, especially to external drives or personal cloud storage</li>
<li>Accessing systems or files unrelated to their current job duties</li>
<li>Repeated failed login attempts on accounts or systems they shouldn\'t need</li>
<li>Use of unauthorized USB devices or personal email to move company data</li>
<li>Disabling or attempting to disable logging and monitoring tools</li>
</ul>

<h3>High-Risk Moments</h3>
<p>Insider risk rises notably around:</p>
<ul>
<li>Resignation or termination announcements (before the employee\'s last day)</li>
<li>Performance reviews or disciplinary action</li>
<li>Mergers, layoffs, or major reorganizations</li>
</ul>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Important:</strong> A single warning sign rarely means someone is a threat — most disgruntled employees never act maliciously. The goal is awareness and appropriate reporting, not suspicion of colleagues.
</div>',
                    ],
                    [
                        'title' => 'Prevention, Controls & Reporting',
                        'slug' => 'insider-threat-prevention-reporting',
                        'duration_minutes' => 8,
                        'content' => '<h2>Prevention, Controls & Reporting</h2>

<h3>Organizational Controls</h3>
<ul>
<li><strong>Least privilege:</strong> Employees should only have access to the systems and data required for their current role</li>
<li><strong>Access reviews:</strong> Regularly audit who has access to what, and remove access promptly when roles change or employment ends</li>
<li><strong>Offboarding procedures:</strong> Immediately revoke all system access, badges, and credentials the moment an employee departs</li>
<li><strong>Data loss prevention (DLP) tools:</strong> Automatically flag or block unusual data transfers</li>
<li><strong>Segregation of duties:</strong> No single person should control an entire sensitive process end-to-end (e.g., approving and processing the same payment)</li>
</ul>

<h3>Your Role as an Employee</h3>
<ul>
<li>Follow the principle of least privilege — don\'t request access you don\'t need "just in case"</li>
<li>Report access you still have from a previous role that you no longer need</li>
<li>Never share your credentials or badge with a colleague, even to be helpful</li>
<li>Speak up if you notice a colleague accessing data unrelated to their job</li>
</ul>

<h3>How and When to Report a Concern</h3>
<p>Report to your manager, HR, or security team if you observe:</p>
<ul>
<li>A colleague downloading unusually large amounts of data before their departure date</li>
<li>Someone attempting to access systems clearly outside their role</li>
<li>Explicit statements of intent to harm the organization or steal data</li>
</ul>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Confidential reporting:</strong> Most organizations provide a confidential or anonymous reporting channel for insider threat concerns. Reporting in good faith protects both the organization and, often, the colleague in question — early intervention can prevent a situation from escalating.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Insider Threat Awareness Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'Which category of insider threat is the MOST common?',
                            'explanation' => 'Negligent insiders — well-meaning employees who make mistakes like falling for phishing or misconfiguring settings — represent the most common category of insider risk.',
                            'answers' => [
                                ['text' => 'Malicious insiders', 'correct' => false],
                                ['text' => 'Negligent insiders', 'correct' => true],
                                ['text' => 'Compromised insiders', 'correct' => false],
                                ['text' => 'External hackers posing as insiders', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a "compromised insider"?',
                            'explanation' => 'A compromised insider is a legitimate account taken over by an external attacker, who then operates using that trusted access.',
                            'answers' => [
                                ['text' => 'An employee who intentionally steals data', 'correct' => false],
                                ['text' => 'A legitimate account taken over and used by an external attacker', 'correct' => true],
                                ['text' => 'A new employee still in training', 'correct' => false],
                                ['text' => 'A contractor with limited access', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'When does insider risk tend to increase notably?',
                            'explanation' => 'Insider risk rises around resignation, termination, disciplinary action, and major organizational changes.',
                            'answers' => [
                                ['text' => 'During routine, uneventful weeks', 'correct' => false],
                                ['text' => 'Around resignations, terminations, and major reorganizations', 'correct' => true],
                                ['text' => 'Only during the employee\'s first week', 'correct' => false],
                                ['text' => 'Insider risk is constant and never changes', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does the "principle of least privilege" mean?',
                            'explanation' => 'Least privilege means giving employees only the access needed for their current role, reducing the potential damage from any single compromised or malicious account.',
                            'answers' => [
                                ['text' => 'Giving all employees full administrator access for convenience', 'correct' => false],
                                ['text' => 'Giving employees only the access required for their current role', 'correct' => true],
                                ['text' => 'Reducing the number of employees with any access at all', 'correct' => false],
                                ['text' => 'Requiring a manager\'s password for every login', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You notice a coworker downloading unusually large amounts of customer data shortly before their announced last day. What should you do?',
                            'explanation' => 'Unusual, large data transfers around a departure date are a recognized warning sign and should be reported through proper channels.',
                            'answers' => [
                                ['text' => 'Say nothing — it\'s not your business', 'correct' => false],
                                ['text' => 'Confront the coworker directly and demand an explanation', 'correct' => false],
                                ['text' => 'Report the observation to your manager, HR, or security team', 'correct' => true],
                                ['text' => 'Post about it on the company chat channel', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 10: Ransomware Prevention & Response ──
            [
                'title' => 'Ransomware Prevention & Response',
                'slug' => 'ransomware-prevention-response',
                'description' => 'A focused deep dive into how modern ransomware operates and the specific steps organizations and individuals take to prevent and respond to it.',
                'objectives' => [
                    'Understand how modern "double extortion" ransomware operates',
                    'Identify the initial access methods ransomware gangs rely on',
                    'Apply prevention practices that reduce ransomware risk',
                    'Follow the correct incident response sequence during an attack',
                ],
                'category' => 'Malware & Ransomware',
                'difficulty' => 'intermediate',
                'duration_minutes' => 24,
                'passing_score' => 70,
                'sort_order' => 9,
                'lessons' => [
                    [
                        'title' => 'How Modern Ransomware Operates',
                        'slug' => 'how-modern-ransomware-operates',
                        'duration_minutes' => 8,
                        'content' => '<h2>How Modern Ransomware Operates</h2>
<p>Ransomware has evolved far beyond simply encrypting files. Understanding the modern attack lifecycle helps explain why prevention matters so much more than recovery alone.</p>

<h3>The "Double Extortion" Model</h3>
<p>Most ransomware groups today don\'t just encrypt data — they <strong>steal it first</strong>. Even if you restore from backups without paying, attackers threaten to publish or sell the stolen data unless a ransom is paid. Some groups add a third layer ("triple extortion"): also threatening the victim\'s customers or launching denial-of-service attacks.</p>

<h3>Typical Attack Lifecycle</h3>
<ol>
<li><strong>Initial access:</strong> Phishing email, exposed remote desktop (RDP), or an unpatched vulnerability</li>
<li><strong>Foothold & reconnaissance:</strong> Attacker quietly explores the network, often for days or weeks, identifying valuable data and backup systems</li>
<li><strong>Privilege escalation:</strong> Attacker obtains administrator-level credentials</li>
<li><strong>Data exfiltration:</strong> Sensitive data is copied out before encryption begins</li>
<li><strong>Backup sabotage:</strong> Attacker deletes or disables backup systems to prevent easy recovery</li>
<li><strong>Encryption & ransom note:</strong> Files across the network are encrypted simultaneously and a ransom demand is left</li>
</ol>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Ransomware-as-a-Service (RaaS):</strong> Many attacks today are carried out by affiliates who "rent" ransomware tools from a criminal organization in exchange for a cut of the profits — lowering the technical bar for launching an attack.
</div>',
                    ],
                    [
                        'title' => 'Reducing Your Ransomware Risk',
                        'slug' => 'reducing-ransomware-risk',
                        'duration_minutes' => 8,
                        'content' => '<h2>Reducing Your Ransomware Risk</h2>

<h3>The 3-2-1 Backup Rule</h3>
<p>Keep <strong>3</strong> copies of important data, on <strong>2</strong> different types of media, with <strong>1</strong> copy stored offline or immutable (cannot be modified or deleted, even by an attacker with admin access). Offline/immutable backups are the single most effective ransomware defense — attackers cannot encrypt or delete what they cannot reach.</p>

<h3>Reduce Initial Access Points</h3>
<ul>
<li>Never expose Remote Desktop Protocol (RDP) directly to the internet — require a VPN with MFA first</li>
<li>Patch operating systems and software promptly — many ransomware campaigns exploit known, patchable vulnerabilities</li>
<li>Disable macros in Office documents from the internet by default</li>
<li>Use email filtering to block common ransomware delivery attachments</li>
</ul>

<h3>Limit Lateral Movement</h3>
<ul>
<li>Segment your network so a compromise in one area doesn\'t easily spread to everything</li>
<li>Enforce least-privilege access so a single compromised account can\'t reach the entire environment</li>
<li>Require MFA for all administrative and remote access accounts</li>
</ul>

<h3>Your Role as an Employee</h3>
<ul>
<li>Never disable antivirus/endpoint protection, even temporarily</li>
<li>Report suspicious emails and unusual system slowdowns immediately — early detection during the "foothold" stage can stop an attack before encryption occurs</li>
<li>Don\'t plug in unknown USB devices</li>
<li>Follow patch and update prompts on your work devices promptly</li>
</ul>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Key insight:</strong> Ransomware attackers typically spend days or weeks inside a network before triggering encryption. Reporting suspicious activity early can stop an attack before it ever reaches that stage.
</div>',
                    ],
                    [
                        'title' => 'Responding to a Ransomware Incident',
                        'slug' => 'responding-to-ransomware-incident',
                        'duration_minutes' => 8,
                        'content' => '<h2>Responding to a Ransomware Incident</h2>

<h3>Immediate Actions (First 15 Minutes)</h3>
<ol>
<li><strong>Isolate affected systems:</strong> Disconnect infected devices from the network immediately (unplug Ethernet, disable WiFi) to stop encryption from spreading</li>
<li><strong>Do not power off</strong> infected machines — this can destroy forensic evidence needed for investigation and potential decryption research</li>
<li><strong>Alert your security/incident response team immediately</strong> by phone, not email (email systems may be compromised)</li>
<li><strong>Do not attempt to negotiate</strong> or communicate with attackers yourself — this is handled by trained incident responders and, often, law enforcement</li>
</ol>

<h3>Investigation Phase</h3>
<ul>
<li>Determine the scope: which systems, accounts, and data were affected</li>
<li>Identify the initial access point to prevent immediate re-infection</li>
<li>Check backup integrity — confirm offline/immutable backups were not compromised</li>
<li>Preserve logs and evidence for forensic analysis and potential law enforcement involvement</li>
</ul>

<h3>The Question of Paying the Ransom</h3>
<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 12px 0;">
<strong>Paying is strongly discouraged:</strong> It funds further criminal activity, offers no guarantee of receiving a working decryption key or that stolen data won\'t still be leaked, and may violate sanctions law depending on the group involved. This decision should only ever be made by senior leadership and legal counsel, never by an individual employee.
</div>

<h3>Recovery & Post-Incident</h3>
<ol>
<li>Rebuild affected systems from clean images rather than trusting a "cleaned" infected system</li>
<li>Restore data from verified clean backups</li>
<li>Reset all credentials that may have been exposed</li>
<li>Conduct a post-incident review and strengthen controls that failed</li>
<li>Notify affected customers/regulators if personal data was exfiltrated, per applicable law</li>
</ol>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Ransomware Prevention & Response Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is "double extortion" ransomware?',
                            'explanation' => 'Double extortion means attackers steal data before encrypting it, threatening to leak it even if the victim restores from backup without paying.',
                            'answers' => [
                                ['text' => 'Demanding payment twice from the same victim', 'correct' => false],
                                ['text' => 'Stealing data before encryption and threatening to leak it separately from the ransom demand', 'correct' => true],
                                ['text' => 'Encrypting files twice for extra security', 'correct' => false],
                                ['text' => 'Targeting two companies at once', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does the 3-2-1 backup rule recommend?',
                            'explanation' => 'The 3-2-1 rule: 3 copies of data, on 2 different media types, with 1 copy stored offline or immutable.',
                            'answers' => [
                                ['text' => '3 copies on 2 media types, with 1 offline or immutable', 'correct' => true],
                                ['text' => '3 different antivirus programs installed', 'correct' => false],
                                ['text' => '2 backups taken 1 time per year', 'correct' => false],
                                ['text' => '3 people must approve any backup', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You discover a ransomware infection spreading on your network. What is the FIRST thing you should do?',
                            'explanation' => 'Isolating affected systems from the network immediately is the top priority to stop the spread before further action.',
                            'answers' => [
                                ['text' => 'Power off all computers immediately', 'correct' => false],
                                ['text' => 'Isolate/disconnect affected systems from the network', 'correct' => true],
                                ['text' => 'Try to negotiate with the attackers yourself', 'correct' => false],
                                ['text' => 'Pay the ransom right away to save time', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should Remote Desktop Protocol (RDP) never be exposed directly to the internet?',
                            'explanation' => 'Exposed RDP is one of the most common initial access points ransomware attackers exploit; it should always require a VPN with MFA.',
                            'answers' => [
                                ['text' => 'It slows down network performance', 'correct' => false],
                                ['text' => 'It is a common initial access point exploited by ransomware attackers', 'correct' => true],
                                ['text' => 'It is expensive to maintain', 'correct' => false],
                                ['text' => 'It is not compatible with most operating systems', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Who should decide whether to pay a ransomware ransom?',
                            'explanation' => 'The decision to pay a ransom carries legal and financial implications and should be made by senior leadership and legal counsel, never by an individual employee.',
                            'answers' => [
                                ['text' => 'Whichever employee first discovers the infection', 'correct' => false],
                                ['text' => 'The IT helpdesk', 'correct' => false],
                                ['text' => 'Senior leadership and legal counsel', 'correct' => true],
                                ['text' => 'The attacker should decide the amount and the company must comply', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 11: Mobile Device Security ──
            [
                'title' => 'Mobile Device Security',
                'slug' => 'mobile-device-security',
                'description' => 'Learn to secure smartphones and tablets used for work, including app permissions, public WiFi risks, and lost-device procedures.',
                'objectives' => [
                    'Configure mobile devices with basic security settings',
                    'Recognize risky app permissions and public network dangers',
                    'Apply safe practices for BYOD (bring your own device) use',
                    'Know what to do if a device is lost or stolen',
                ],
                'category' => 'Mobile & Remote Work Security',
                'difficulty' => 'beginner',
                'duration_minutes' => 18,
                'passing_score' => 70,
                'sort_order' => 10,
                'lessons' => [
                    [
                        'title' => 'Securing Your Mobile Device',
                        'slug' => 'securing-your-mobile-device',
                        'duration_minutes' => 6,
                        'content' => '<h2>Securing Your Mobile Device</h2>
<p>Smartphones often hold as much sensitive information as a laptop — email, documents, authentication apps, and contacts — but are far more likely to be lost, stolen, or left unattended.</p>

<h3>Baseline Settings Everyone Should Enable</h3>
<ul>
<li><strong>Screen lock:</strong> Use a PIN, password, or biometric (fingerprint/face) lock — never leave a device with no lock at all</li>
<li><strong>Auto-lock timeout:</strong> Set the shortest timeout you can tolerate (30 seconds to 1 minute)</li>
<li><strong>Automatic OS and app updates:</strong> Keep security patches current</li>
<li><strong>Find My Device / Find My iPhone:</strong> Enable device tracking and remote wipe capability</li>
<li><strong>Full-device encryption:</strong> Enabled by default on most modern phones, but verify it in settings</li>
</ul>

<h3>App Store Hygiene</h3>
<ul>
<li>Only install apps from official app stores (Apple App Store, Google Play)</li>
<li>Avoid "sideloading" apps from unofficial websites</li>
<li>Check app reviews and download counts before installing unfamiliar apps</li>
<li>Uninstall apps you no longer use</li>
</ul>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Tip:</strong> Treat your phone the way you\'d treat your laptop — it deserves the same level of caution, because it usually has access to the same accounts.
</div>',
                    ],
                    [
                        'title' => 'App Permissions & Public WiFi',
                        'slug' => 'app-permissions-public-wifi',
                        'duration_minutes' => 6,
                        'content' => '<h2>App Permissions & Public WiFi</h2>

<h3>Reviewing App Permissions</h3>
<p>Before granting a permission, ask: does this app genuinely need it for its core function?</p>
<ul>
<li>A flashlight app asking for your contacts list — red flag</li>
<li>A calculator app requesting microphone access — red flag</li>
<li>Review permissions periodically in your phone\'s privacy settings and revoke anything unnecessary</li>
</ul>

<h3>Common Risky Permissions</h3>
<table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
<tr style="background: #FEF2F2;"><th style="padding: 8px; text-align: left; border: 1px solid #E5E7EB;">Permission</th><th style="padding: 8px; text-align: left; border: 1px solid #E5E7EB;">Risk if misused</th></tr>
<tr><td style="padding: 8px; border: 1px solid #E5E7EB;">Location (always)</td><td style="padding: 8px; border: 1px solid #E5E7EB;">Continuous tracking of your movements</td></tr>
<tr><td style="padding: 8px; border: 1px solid #E5E7EB;">Microphone/Camera</td><td style="padding: 8px; border: 1px solid #E5E7EB;">Covert recording or surveillance</td></tr>
<tr><td style="padding: 8px; border: 1px solid #E5E7EB;">Contacts</td><td style="padding: 8px; border: 1px solid #E5E7EB;">Harvesting personal/professional networks</td></tr>
<tr><td style="padding: 8px; border: 1px solid #E5E7EB;">SMS</td><td style="padding: 8px; border: 1px solid #E5E7EB;">Intercepting one-time passcodes</td></tr>
</table>

<h3>Public WiFi Dangers</h3>
<p>Public WiFi at cafes, airports, and hotels is often unencrypted or poorly secured. Risks include:</p>
<ul>
<li><strong>Man-in-the-middle attacks:</strong> An attacker on the same network intercepts your traffic</li>
<li><strong>Evil twin networks:</strong> A fake WiFi hotspot named to look like the real one (e.g., "Airport_WiFi_Free")</li>
<li><strong>Packet sniffing:</strong> Capturing unencrypted data sent over the network</li>
</ul>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Best practice:</strong> Use your organization\'s VPN whenever connecting to public WiFi for work purposes, or use your phone\'s cellular hotspot instead when handling sensitive information.
</div>',
                    ],
                    [
                        'title' => 'BYOD Practices & Lost Device Response',
                        'slug' => 'byod-lost-device-response',
                        'duration_minutes' => 6,
                        'content' => '<h2>BYOD Practices & Lost Device Response</h2>

<h3>Bring Your Own Device (BYOD) Best Practices</h3>
<ul>
<li>Enroll your personal device in your organization\'s Mobile Device Management (MDM) system if required — this allows separation of work and personal data</li>
<li>Keep work apps and data in a separate, managed "container" or profile where supported</li>
<li>Never store company data in personal cloud storage or personal email</li>
<li>Understand what your organization can and cannot see or remote-wipe on a BYOD device — usually just the managed work container, not your personal data</li>
</ul>

<h3>Traveling with Devices</h3>
<ul>
<li>Never leave devices unattended in public places, even briefly</li>
<li>Use a privacy screen filter in crowded places to prevent shoulder surfing</li>
<li>Disable Bluetooth and WiFi auto-connect when not needed</li>
<li>Be aware some countries may inspect or copy device data at border crossings — follow your organization\'s travel security policy</li>
</ul>

<h2>If Your Device Is Lost or Stolen</h2>
<ol>
<li><strong>Report immediately</strong> to your IT/security team — every minute matters</li>
<li><strong>Use Find My Device</strong> to attempt to locate, lock, or remotely wipe it</li>
<li><strong>Change passwords</strong> for accounts accessible from that device, starting with email</li>
<li><strong>Revoke active sessions</strong> for key accounts from another trusted device</li>
<li><strong>File a police report</strong> if required by your organization\'s policy (often needed for insurance or compliance)</li>
</ol>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Remember:</strong> A device with a strong lock screen, encryption, and remote wipe enabled turns a lost phone from a data breach into a minor inconvenience.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Mobile Device Security Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'A flashlight app requests access to your contacts and microphone. What should you do?',
                            'explanation' => 'Permissions that have no clear relationship to an app\'s core function are a red flag and should be denied.',
                            'answers' => [
                                ['text' => 'Grant the permissions since it\'s a trusted app store app', 'correct' => false],
                                ['text' => 'Deny the unnecessary permissions — they have no reason to be needed', 'correct' => true],
                                ['text' => 'Grant them only once', 'correct' => false],
                                ['text' => 'Ignore the prompt', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is an "evil twin" network?',
                            'explanation' => 'An evil twin is a fake WiFi hotspot designed to look like a legitimate one, used to intercept victims\' traffic.',
                            'answers' => [
                                ['text' => 'A backup WiFi router', 'correct' => false],
                                ['text' => 'A fake WiFi hotspot designed to look like a legitimate network', 'correct' => true],
                                ['text' => 'Two routers on the same network for redundancy', 'correct' => false],
                                ['text' => 'A virus that duplicates itself on WiFi', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you use when connecting to public WiFi for work purposes?',
                            'explanation' => 'A VPN encrypts your traffic, protecting it from interception on untrusted public networks.',
                            'answers' => [
                                ['text' => 'Your organization\'s VPN', 'correct' => true],
                                ['text' => 'Any open network available', 'correct' => false],
                                ['text' => 'Incognito/private browsing mode only', 'correct' => false],
                                ['text' => 'No special precautions are needed', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You lose your work phone while traveling. What is the FIRST thing you should do?',
                            'explanation' => 'Reporting immediately to IT/security allows the fastest response, including remote lock or wipe.',
                            'answers' => [
                                ['text' => 'Wait until you get home to deal with it', 'correct' => false],
                                ['text' => 'Report it immediately to your IT/security team', 'correct' => true],
                                ['text' => 'Buy a replacement phone first', 'correct' => false],
                                ['text' => 'Post about it on social media to see if someone finds it', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a key benefit of Mobile Device Management (MDM) enrollment for BYOD devices?',
                            'explanation' => 'MDM allows work data to be kept in a separate, managed container, letting the organization control work data without accessing personal data.',
                            'answers' => [
                                ['text' => 'It gives the organization full access to all personal data on the device', 'correct' => false],
                                ['text' => 'It separates and manages work data in a container, independent of personal data', 'correct' => true],
                                ['text' => 'It removes the need for a screen lock', 'correct' => false],
                                ['text' => 'It automatically backs up personal photos', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 12: Remote Work Security ──
            [
                'title' => 'Remote Work Security',
                'slug' => 'remote-work-security',
                'description' => 'Practical guidance for staying secure while working from home, coworking spaces, or while traveling.',
                'objectives' => [
                    'Secure a home network and workspace for remote work',
                    'Use VPNs and secure connections correctly',
                    'Apply safe practices in shared and public workspaces',
                    'Follow proper procedures for remote incident reporting',
                ],
                'category' => 'Mobile & Remote Work Security',
                'difficulty' => 'beginner',
                'duration_minutes' => 18,
                'passing_score' => 70,
                'sort_order' => 11,
                'lessons' => [
                    [
                        'title' => 'Securing Your Home Network',
                        'slug' => 'securing-home-network',
                        'duration_minutes' => 6,
                        'content' => '<h2>Securing Your Home Network</h2>
<p>When you work remotely, your home network effectively becomes an extension of your company\'s perimeter. Securing your router is not optional.</p>

<h3>Router Security Checklist</h3>
<ul>
<li><strong>Change the default admin password</strong> — default credentials for router brands are publicly documented and widely exploited</li>
<li><strong>Use WPA3 (or WPA2 at minimum)</strong> encryption for your WiFi, never WEP or an open network</li>
<li><strong>Set a strong WiFi password</strong>, distinct from the router admin password</li>
<li><strong>Keep router firmware updated</strong> — check for updates periodically or enable auto-update if available</li>
<li><strong>Disable remote management</strong> of the router unless you specifically need it</li>
<li><strong>Rename the default network name (SSID)</strong> to something that doesn\'t reveal the router brand/model</li>
</ul>

<h3>Guest Network Separation</h3>
<p>Set up a separate guest WiFi network for visitors, smart home devices (IoT), and personal devices. This limits the blast radius if any single device is compromised, keeping your work devices isolated.</p>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Did you know?</strong> Many smart home devices (cameras, thermostats, speakers) have weak security and are common entry points for attackers into a home network. Keep them on a separate network from work devices.
</div>',
                    ],
                    [
                        'title' => 'Using VPNs and Secure Connections',
                        'slug' => 'using-vpns-secure-connections',
                        'duration_minutes' => 6,
                        'content' => '<h2>Using VPNs and Secure Connections</h2>

<h3>What a VPN Does</h3>
<p>A Virtual Private Network (VPN) creates an encrypted tunnel between your device and your organization\'s network, protecting your traffic from interception and often granting access to internal resources not exposed to the public internet.</p>

<h3>VPN Best Practices</h3>
<ul>
<li><strong>Always connect to your company VPN</strong> before accessing work systems or sensitive data, especially outside the office</li>
<li>Only use your organization\'s <strong>approved</strong> VPN client — never a free, unvetted third-party VPN for work purposes</li>
<li>Keep VPN client software updated</li>
<li>Log out or disconnect the VPN when not actively working, per your organization\'s policy</li>
</ul>

<h3>Recognizing VPN-Related Red Flags</h3>
<ul>
<li>Being prompted to disable your VPN "for troubleshooting" by an unverified support contact — verify through official channels first</li>
<li>Certificate warnings when connecting — never bypass these; report them to IT</li>
<li>Unusually slow or frequently disconnecting VPN — could indicate a network issue, but also worth reporting</li>
</ul>

<h3>Secure Video Conferencing</h3>
<ul>
<li>Use waiting rooms or meeting passwords for sensitive meetings</li>
<li>Don\'t reuse a single generic meeting link publicly or on social media</li>
<li>Be mindful of what\'s visible in your background during video calls (documents, whiteboards, screens)</li>
<li>Mute and disable video by default when joining unfamiliar or large meetings</li>
</ul>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Rule of thumb:</strong> If you wouldn\'t do something in the office without asking IT first, don\'t do it remotely without asking either.
</div>',
                    ],
                    [
                        'title' => 'Working Safely in Shared Spaces',
                        'slug' => 'working-safely-shared-spaces',
                        'duration_minutes' => 6,
                        'content' => '<h2>Working Safely in Shared Spaces</h2>
<p>Coffee shops, coworking spaces, and shared living situations introduce risks that a private home office doesn\'t have.</p>

<h3>Physical Security in Public</h3>
<ul>
<li>Position your screen away from windows and other people\'s line of sight</li>
<li>Use a privacy screen filter for sensitive work in public</li>
<li>Never leave your laptop unattended — even for "just a minute" to grab a coffee refill</li>
<li>Lock your screen every time you step away, no matter how briefly (Win+L / Cmd+Ctrl+Q)</li>
</ul>

<h3>Overheard Conversations</h3>
<p>Be mindful of confidential calls in public or shared spaces — use a headset, lower your voice, and consider whether a call should wait until you\'re in a private location.</p>

<h3>Household Considerations</h3>
<ul>
<li>Don\'t let family members or roommates use your work device</li>
<li>Keep printed sensitive documents secured, not left on shared tables</li>
<li>Position your workspace so screens aren\'t visible to household visitors</li>
<li>Use headphones for confidential calls when others are nearby</li>
</ul>

<h3>Reporting Incidents While Remote</h3>
<p>Remote incident reporting works the same as in-office reporting — don\'t let distance become an excuse to delay:</p>
<ol>
<li>Contact your IT/security team through the usual channel (phone is often faster than email/chat)</li>
<li>Disconnect from the network if malware or compromise is suspected</li>
<li>Follow the same escalation steps you would use in the office</li>
</ol>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Remember:</strong> Working remotely doesn\'t reduce your security responsibilities — if anything, it means more of the perimeter depends on your own good habits.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Remote Work Security Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'Why should you change your home router\'s default admin password?',
                            'explanation' => 'Default router credentials are publicly documented by brand and model, making them easy for attackers to exploit if unchanged.',
                            'answers' => [
                                ['text' => 'It makes the WiFi signal stronger', 'correct' => false],
                                ['text' => 'Default credentials are publicly known and widely exploited', 'correct' => true],
                                ['text' => 'It is legally required in most countries', 'correct' => false],
                                ['text' => 'It speeds up your internet connection', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should smart home devices (cameras, speakers, thermostats) be kept on a separate network from work devices?',
                            'explanation' => 'IoT devices often have weaker security and are common entry points for attackers; separating them limits the blast radius of a compromise.',
                            'answers' => [
                                ['text' => 'To save WiFi bandwidth', 'correct' => false],
                                ['text' => 'Because they often have weak security and could be an entry point for attackers', 'correct' => true],
                                ['text' => 'It\'s required by the device manufacturer', 'correct' => false],
                                ['text' => 'It has no security benefit, only convenience', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Someone contacts you claiming to be IT support and asks you to disable your VPN "for troubleshooting." What should you do?',
                            'explanation' => 'Unverified requests to disable security controls should always be verified through official channels before acting.',
                            'answers' => [
                                ['text' => 'Disable it immediately since they said they were IT', 'correct' => false],
                                ['text' => 'Verify the request through an official, known channel before doing anything', 'correct' => true],
                                ['text' => 'Ignore the request and do nothing', 'correct' => false],
                                ['text' => 'Ask a coworker instead of verifying with IT', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You need to step away from your laptop at a coffee shop for a moment. What should you do?',
                            'explanation' => 'Locking your screen and never leaving a device unattended in public are essential habits, even for brief moments.',
                            'answers' => [
                                ['text' => 'Leave it as is since you\'ll be quick', 'correct' => false],
                                ['text' => 'Lock the screen and take the laptop with you, or have someone you trust watch it', 'correct' => true],
                                ['text' => 'Turn the screen off but leave it unlocked', 'correct' => false],
                                ['text' => 'Ask a stranger nearby to watch it', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the main purpose of a company VPN for remote workers?',
                            'explanation' => 'A VPN encrypts traffic between your device and the company network, protecting data from interception and enabling secure access to internal resources.',
                            'answers' => [
                                ['text' => 'To make your internet connection faster', 'correct' => false],
                                ['text' => 'To create an encrypted tunnel and enable secure access to company resources', 'correct' => true],
                                ['text' => 'To block all websites except work-related ones', 'correct' => false],
                                ['text' => 'To automatically back up your files', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 13: Data Privacy Regulations (GDPR & PDPA) ──
            [
                'title' => 'Data Privacy Regulations (GDPR & PDPA)',
                'slug' => 'data-privacy-regulations-gdpr-pdpa',
                'description' => 'A deeper look at how GDPR and PDPA affect day-to-day work, individual rights, and your obligations when handling personal data.',
                'objectives' => [
                    'Explain the core principles behind GDPR and PDPA',
                    'Identify individual data subject rights and how to respond to requests',
                    'Recognize what qualifies as a reportable data breach',
                    'Apply privacy-by-design thinking in daily work',
                ],
                'category' => 'Data Protection & Privacy',
                'difficulty' => 'intermediate',
                'duration_minutes' => 24,
                'passing_score' => 70,
                'sort_order' => 12,
                'lessons' => [
                    [
                        'title' => 'Core Principles of GDPR and PDPA',
                        'slug' => 'core-principles-gdpr-pdpa',
                        'duration_minutes' => 8,
                        'content' => '<h2>Core Principles of GDPR and PDPA</h2>
<p>While GDPR (EU) and PDPA (Malaysia) are separate laws, they share a common foundation of principles that guide how personal data must be handled.</p>

<h3>Lawfulness, Fairness & Transparency</h3>
<p>Personal data can only be collected and processed when there is a valid legal basis (such as consent, a contract, or a legitimate business interest), and individuals must be told clearly how their data will be used.</p>

<h3>Purpose Limitation</h3>
<p>Data collected for one purpose (e.g., processing an order) should not be repurposed for something unrelated (e.g., unsolicited marketing) without a new lawful basis or fresh consent.</p>

<h3>Data Minimization</h3>
<p>Collect only what is genuinely necessary. If a form field isn\'t needed to deliver the service, don\'t collect it "just in case."</p>

<h3>Accuracy</h3>
<p>Personal data should be kept accurate and up to date, with reasonable steps taken to correct or erase inaccurate data.</p>

<h3>Storage Limitation</h3>
<p>Data should not be kept longer than necessary for its original purpose — follow your organization\'s retention schedule and securely dispose of data once it expires.</p>

<h3>Integrity & Confidentiality (Security)</h3>
<p>Appropriate technical and organizational measures (encryption, access controls, staff training) must protect personal data from unauthorized access, loss, or damage.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Key difference:</strong> GDPR applies broadly to any organization processing EU residents\' data, regardless of where the organization is based. PDPA governs commercial data processing activities specifically within Malaysia.
</div>',
                    ],
                    [
                        'title' => 'Individual Rights & Handling Requests',
                        'slug' => 'individual-rights-handling-requests',
                        'duration_minutes' => 8,
                        'content' => '<h2>Individual Rights & Handling Requests</h2>
<p>Both GDPR and PDPA give individuals ("data subjects") rights over their own personal data. As an employee, you may be the first point of contact for such a request.</p>

<h3>Common Data Subject Rights (GDPR)</h3>
<ul>
<li><strong>Right to access:</strong> Request a copy of the personal data held about them</li>
<li><strong>Right to rectification:</strong> Request correction of inaccurate data</li>
<li><strong>Right to erasure ("right to be forgotten"):</strong> Request deletion of their data, subject to certain exceptions</li>
<li><strong>Right to data portability:</strong> Receive their data in a structured, machine-readable format</li>
<li><strong>Right to object:</strong> Object to certain types of processing, such as direct marketing</li>
</ul>

<h3>PDPA Equivalent Rights</h3>
<p>PDPA grants Malaysian data subjects similar rights to access and correct their personal data, and to withdraw consent for processing.</p>

<h3>What To Do If You Receive a Request</h3>
<ol>
<li><strong>Don\'t handle it yourself</strong> — forward it immediately to your Data Protection Officer (DPO) or privacy team</li>
<li><strong>Note the date received</strong> — statutory response timeframes (e.g., GDPR\'s one month) start from that date</li>
<li><strong>Don\'t delete or alter any data</strong> related to the request before the privacy team reviews it</li>
<li><strong>Don\'t promise a specific outcome</strong> to the requester — let the designated team assess and respond</li>
</ol>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Remember:</strong> Even an informal request — a customer emailing "please delete my account and data" — counts as a formal rights request and must be routed to the right team promptly.
</div>',
                    ],
                    [
                        'title' => 'Recognizing and Reporting Data Breaches',
                        'slug' => 'recognizing-reporting-data-breaches',
                        'duration_minutes' => 8,
                        'content' => '<h2>Recognizing and Reporting Data Breaches</h2>

<h3>What Counts as a Data Breach?</h3>
<p>A personal data breach is any incident leading to accidental or unlawful destruction, loss, alteration, unauthorized disclosure of, or access to personal data. This is broader than most people assume:</p>
<ul>
<li>Emailing a document with personal data to the wrong recipient</li>
<li>A lost or stolen laptop, phone, or USB drive containing personal data</li>
<li>A misconfigured cloud storage bucket left publicly accessible</li>
<li>An employee accessing customer records without a legitimate business reason</li>
<li>A successful phishing attack that exposes customer or employee data</li>
<li>Physical documents with personal data left in an unsecured location</li>
</ul>

<h3>Why Speed Matters</h3>
<p>GDPR requires organizations to notify the relevant supervisory authority within <strong>72 hours</strong> of becoming aware of a breach that risks individuals\' rights and freedoms. That clock starts the moment <em>anyone</em> in the organization becomes aware — including you.</p>

<h3>What To Do If You Discover a Potential Breach</h3>
<ol>
<li><strong>Report it immediately</strong> to your Data Protection Officer or security team — do not wait to assess severity yourself</li>
<li><strong>Do not try to "quietly fix it"</strong> by deleting evidence or reaching out to the recipient yourself first</li>
<li><strong>Provide as much detail as possible:</strong> what data was involved, how it happened, when you noticed it, who else may know</li>
<li><strong>Cooperate fully</strong> with the ensuing investigation</li>
</ol>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>There is no "too small to report":</strong> A single misdirected email counts as a reportable breach. Reporting quickly — even for something that feels minor — is always the right call, and you will not be penalized for reporting in good faith.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Data Privacy Regulations Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What does "data minimization" require?',
                            'explanation' => 'Data minimization means only collecting personal data that is genuinely necessary for the stated purpose.',
                            'answers' => [
                                ['text' => 'Collecting as much data as possible for future use', 'correct' => false],
                                ['text' => 'Collecting only the data genuinely necessary for the purpose', 'correct' => true],
                                ['text' => 'Storing data in the smallest file size possible', 'correct' => false],
                                ['text' => 'Deleting all data within 24 hours', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A customer emails asking you to delete all their personal data. What should you do?',
                            'explanation' => 'Requests should be forwarded immediately to the Data Protection Officer or privacy team, noting the date received, rather than being handled informally.',
                            'answers' => [
                                ['text' => 'Delete their data yourself right away', 'correct' => false],
                                ['text' => 'Ignore it since it wasn\'t submitted on an official form', 'correct' => false],
                                ['text' => 'Forward it immediately to your Data Protection Officer or privacy team', 'correct' => true],
                                ['text' => 'Tell them you cannot delete any data ever', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Within how many hours must GDPR-covered organizations typically notify authorities of a qualifying data breach?',
                            'explanation' => 'GDPR requires notification to the relevant supervisory authority within 72 hours of becoming aware of a qualifying breach.',
                            'answers' => [
                                ['text' => '24 hours', 'correct' => false],
                                ['text' => '72 hours', 'correct' => true],
                                ['text' => '7 days', 'correct' => false],
                                ['text' => '30 days', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of the following counts as a reportable data breach?',
                            'explanation' => 'Even a single misdirected email containing personal data qualifies as a reportable data breach under GDPR and PDPA.',
                            'answers' => [
                                ['text' => 'Sending a customer\'s personal data to the wrong email address', 'correct' => true],
                                ['text' => 'Updating a customer\'s address in the CRM', 'correct' => false],
                                ['text' => 'A scheduled data backup completing successfully', 'correct' => false],
                                ['text' => 'An employee viewing their own HR record', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do first if you discover a potential data breach?',
                            'explanation' => 'Reporting immediately to the Data Protection Officer or security team is essential — do not try to quietly fix it yourself first.',
                            'answers' => [
                                ['text' => 'Try to fix it quietly on your own first', 'correct' => false],
                                ['text' => 'Report it immediately to your Data Protection Officer or security team', 'correct' => true],
                                ['text' => 'Wait to see if anyone complains', 'correct' => false],
                                ['text' => 'Contact the affected individuals directly yourself before telling anyone internally', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 14: Physical Security Awareness ──
            [
                'title' => 'Physical Security Awareness',
                'slug' => 'physical-security-awareness',
                'description' => 'Understand the physical-world side of information security — badge access, visitor management, clean desks, and workplace safety.',
                'objectives' => [
                    'Recognize the role physical security plays in overall information security',
                    'Follow correct badge, access, and visitor procedures',
                    'Apply clean desk and secure disposal practices',
                    'Respond appropriately to physical security incidents',
                ],
                'category' => 'Physical Security & Workplace Safety',
                'difficulty' => 'beginner',
                'duration_minutes' => 16,
                'passing_score' => 70,
                'sort_order' => 13,
                'lessons' => [
                    [
                        'title' => 'Why Physical Security Matters',
                        'slug' => 'why-physical-security-matters',
                        'duration_minutes' => 5,
                        'content' => '<h2>Why Physical Security Matters</h2>
<p>No amount of firewalls or encryption helps if someone can simply walk into your office and access an unlocked computer, steal a laptop, or take a photo of a whiteboard covered in sensitive plans.</p>

<h3>Physical Security Is Part of Information Security</h3>
<p>Many serious breaches begin with a physical intrusion rather than a digital one:</p>
<ul>
<li>An unauthorized visitor plugging a device into an open network port</li>
<li>A stolen unencrypted laptop containing customer records</li>
<li>Someone reading sensitive documents left on a printer tray</li>
<li>A stranger tailgating into a restricted server room</li>
</ul>

<h3>Your Building\'s Layers of Defense</h3>
<p>Physical security typically works in layers, similar to network security:</p>
<ol>
<li><strong>Perimeter:</strong> Fencing, gates, exterior doors</li>
<li><strong>Building access:</strong> Badge readers, reception, security guards</li>
<li><strong>Zone access:</strong> Restricted areas like server rooms, executive floors, or finance departments</li>
<li><strong>Asset-level:</strong> Locked cabinets, cable locks, screen locks</li>
</ol>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Key idea:</strong> Every layer only works if people follow the procedures. A badge reader means nothing if employees routinely hold the door for people without badges.
</div>',
                    ],
                    [
                        'title' => 'Access Control & Visitor Management',
                        'slug' => 'access-control-visitor-management',
                        'duration_minutes' => 5,
                        'content' => '<h2>Access Control & Visitor Management</h2>

<h3>Badge & Access Card Rules</h3>
<ul>
<li>Never lend your badge to anyone, including coworkers who forgot theirs</li>
<li>Report a lost or stolen badge immediately so it can be deactivated</li>
<li>Wear your badge visibly at all times on company premises where required</li>
<li>Challenge (politely) anyone in a restricted area without a visible badge</li>
</ul>

<h3>Tailgating Prevention</h3>
<p>Tailgating — following an authorized person through a secure door without badging in — is one of the most common physical security failures.</p>
<ul>
<li>Do not hold secure doors open for people you don\'t recognize, even if they claim to have forgotten their badge</li>
<li>Politely direct them to reception or security instead</li>
<li>Report repeated tailgating attempts to security, even if they seem innocent</li>
</ul>

<h3>Managing Visitors</h3>
<ul>
<li>All visitors should sign in, receive a visible visitor badge, and be escorted in non-public areas</li>
<li>Never leave a visitor unattended near sensitive equipment or documents</li>
<li>Verify the identity and purpose of unscheduled visitors before granting any access</li>
<li>Be cautious of delivery personnel or contractors requesting access beyond their stated purpose</li>
</ul>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Social engineering overlap:</strong> Attackers posing as delivery drivers, maintenance workers, or new employees rely on people\'s natural politeness to bypass physical security. It is never rude to verify — it\'s expected.
</div>',
                    ],
                    [
                        'title' => 'Clean Desk Practices & Incident Response',
                        'slug' => 'clean-desk-physical-incident-response',
                        'duration_minutes' => 6,
                        'content' => '<h2>Clean Desk Practices & Incident Response</h2>

<h3>Clean Desk Policy</h3>
<ul>
<li>Lock away sensitive documents, storage media, and devices when not in use</li>
<li>Clear your desk of confidential papers at the end of each day</li>
<li>Never leave printouts unattended at shared printers — use "follow-me" printing where available</li>
<li>Lock your computer screen every time you step away, even briefly</li>
<li>Store portable devices (laptops, external drives) securely, not left visible in an unlocked car or bag</li>
</ul>

<h3>Secure Document Disposal</h3>
<ul>
<li>Use designated shredding bins for confidential paper waste — never the regular trash or recycling</li>
<li>Ensure old hard drives, USB drives, and storage media are securely wiped or physically destroyed before disposal</li>
<li>Follow your organization\'s asset disposal procedure for retired equipment</li>
</ul>

<h3>Workplace Safety Awareness</h3>
<ul>
<li>Know the location of emergency exits, fire extinguishers, and assembly points</li>
<li>Report broken locks, propped-open secure doors, or malfunctioning badge readers promptly</li>
<li>Report suspicious individuals or unattended packages to security rather than investigating yourself</li>
</ul>

<h3>Responding to a Physical Security Incident</h3>
<ol>
<li>If you see someone attempting unauthorized entry, do not confront them directly — alert security or reception</li>
<li>If a device or asset is stolen, report it to security and IT immediately so accounts can be secured</li>
<li>If you find sensitive documents left unsecured, collect them safely and report the exposure so it can be tracked and addressed</li>
</ol>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Remember:</strong> Physical security is everyone\'s responsibility. Reporting a propped door or an unfamiliar face in a restricted area takes seconds and can prevent a serious incident.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Physical Security Awareness Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is "tailgating" in a physical security context?',
                            'explanation' => 'Tailgating is following an authorized person through a secure access point without using your own credentials.',
                            'answers' => [
                                ['text' => 'Driving too closely behind another car', 'correct' => false],
                                ['text' => 'Following an authorized person through a secure door without badging in', 'correct' => true],
                                ['text' => 'Waiting in line at reception', 'correct' => false],
                                ['text' => 'Sharing a badge with a coworker', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A stranger without a visible badge asks you to hold a secure door open because they forgot theirs. What should you do?',
                            'explanation' => 'You should not hold secure doors for unrecognized individuals; direct them to reception or security to be properly verified.',
                            'answers' => [
                                ['text' => 'Hold the door open to be polite', 'correct' => false],
                                ['text' => 'Politely direct them to reception or security instead', 'correct' => true],
                                ['text' => 'Ask them to show ID and let them in yourself', 'correct' => false],
                                ['text' => 'Ignore them completely', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Where should confidential paper documents be disposed of?',
                            'explanation' => 'Confidential documents should go in designated shredding bins, never regular trash or recycling, to prevent dumpster diving.',
                            'answers' => [
                                ['text' => 'The regular office trash bin', 'correct' => false],
                                ['text' => 'A designated shredding bin', 'correct' => true],
                                ['text' => 'The recycling bin, folded in half', 'correct' => false],
                                ['text' => 'Left on your desk until end of month', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do if you notice someone attempting unauthorized entry into a restricted area?',
                            'explanation' => 'You should alert security or reception rather than confronting the individual directly, for your own safety and proper handling.',
                            'answers' => [
                                ['text' => 'Confront them directly and demand identification', 'correct' => false],
                                ['text' => 'Alert security or reception immediately', 'correct' => true],
                                ['text' => 'Follow them to see where they go', 'correct' => false],
                                ['text' => 'Do nothing since it\'s not your responsibility', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a core principle of the "clean desk" policy?',
                            'explanation' => 'The clean desk policy requires locking away sensitive documents and locking your screen whenever you step away, even briefly.',
                            'answers' => [
                                ['text' => 'Keeping your desk visually tidy for appearance only', 'correct' => false],
                                ['text' => 'Securing sensitive documents and locking your screen whenever you step away', 'correct' => true],
                                ['text' => 'Removing all personal items from your desk', 'correct' => false],
                                ['text' => 'Only applying to shared desks, not assigned ones', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 15: Incident Reporting Procedures ──
            [
                'title' => 'Incident Reporting Procedures',
                'slug' => 'incident-reporting-procedures',
                'description' => 'Learn what qualifies as a security incident, how to report one correctly, and why timely reporting matters for everyone.',
                'objectives' => [
                    'Identify what counts as a reportable security incident',
                    'Understand your organization\'s incident reporting process',
                    'Know what information to include when reporting',
                    'Overcome common hesitations about reporting incidents',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'beginner',
                'duration_minutes' => 16,
                'passing_score' => 70,
                'is_mandatory' => true,
                'sort_order' => 14,
                'lessons' => [
                    [
                        'title' => 'What Counts as a Security Incident?',
                        'slug' => 'what-counts-as-security-incident',
                        'duration_minutes' => 5,
                        'content' => '<h2>What Counts as a Security Incident?</h2>
<p>A security incident is any event that could compromise the confidentiality, integrity, or availability of information or systems. Incidents are more common — and more varied — than most people expect.</p>

<h3>Examples of Reportable Incidents</h3>
<ul>
<li>Clicking a suspicious link or opening an unexpected attachment</li>
<li>Receiving a phishing or suspicious email, even if you didn\'t click anything</li>
<li>A lost or stolen laptop, phone, badge, or USB drive</li>
<li>Noticing unusual computer behavior (slow performance, unexpected pop-ups, unfamiliar programs)</li>
<li>Sending sensitive information to the wrong recipient</li>
<li>Discovering an unlocked door, propped-open secure area, or unattended visitor</li>
<li>A colleague sharing their password or account access with someone else</li>
<li>Receiving a suspicious phone call requesting sensitive information</li>
<li>Finding sensitive documents left unsecured</li>
</ul>

<h3>The "When in Doubt, Report" Principle</h3>
<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>You don\'t need to be certain something is a security incident to report it.</strong> Security teams would rather investigate ten false alarms than miss one real incident. Reporting something that turns out to be nothing is never a mistake.
</div>',
                    ],
                    [
                        'title' => 'How to Report an Incident',
                        'slug' => 'how-to-report-incident',
                        'duration_minutes' => 5,
                        'content' => '<h2>How to Report an Incident</h2>

<h3>Know Your Reporting Channels</h3>
<p>Every organization should have clearly communicated ways to report incidents. Common channels include:</p>
<ul>
<li>A dedicated security email address (e.g., security@yourcompany.com)</li>
<li>A "Report Phishing" button built into your email client</li>
<li>A security hotline or helpdesk phone number for urgent issues</li>
<li>An internal incident reporting portal or ticketing system</li>
</ul>
<p>Know these channels <strong>before</strong> you need them — during an actual incident is the wrong time to be searching for who to call.</p>

<h3>What Information to Include</h3>
<p>A useful incident report typically includes:</p>
<ul>
<li><strong>What happened:</strong> A clear, factual description of the event</li>
<li><strong>When:</strong> Date and time you noticed or when it occurred</li>
<li><strong>What systems or data were involved:</strong> Be as specific as you can</li>
<li><strong>What actions you\'ve already taken:</strong> e.g., "I disconnected from WiFi" or "I have not clicked the link"</li>
<li><strong>Any evidence:</strong> Screenshots, the suspicious email itself (forwarded, not just described), error messages</li>
</ul>

<h3>Urgency Matters</h3>
<p>Use the most urgent channel available (phone/hotline) for active incidents like ongoing ransomware encryption or suspected account compromise. Use email or a ticketing system for lower-urgency items like a suspicious email you already deleted safely.</p>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Tip:</strong> Save your organization\'s security contact number in your phone right now, before you ever need it in a hurry.
</div>',
                    ],
                    [
                        'title' => 'Overcoming Hesitation to Report',
                        'slug' => 'overcoming-hesitation-to-report',
                        'duration_minutes' => 6,
                        'content' => '<h2>Overcoming Hesitation to Report</h2>
<p>Studies consistently show that employees delay or avoid reporting incidents due to fear or embarrassment — and that delay is often what turns a minor incident into a major breach.</p>

<h3>Common Reasons People Don\'t Report</h3>
<ul>
<li><strong>Fear of blame or punishment:</strong> "I\'ll get in trouble for clicking that link"</li>
<li><strong>Embarrassment:</strong> "I should have known better, I don\'t want to look foolish"</li>
<li><strong>Uncertainty:</strong> "I\'m not sure if this is actually a big deal"</li>
<li><strong>Assuming someone else will report it:</strong> "Surely IT already knows about this"</li>
<li><strong>Time pressure:</strong> "I don\'t have time to fill out a report right now"</li>
</ul>

<h3>Why a "Blameless" Reporting Culture Matters</h3>
<p>Most mature security organizations adopt a <strong>blameless reporting culture</strong>: the goal is to fix the problem and learn from it, not to punish the person who reported it. Punishing reporters teaches everyone else to hide incidents — which is far more dangerous than the original mistake.</p>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>The math of reporting speed:</strong> An incident reported within minutes can often be fully contained. The same incident reported days later, after data has already been exfiltrated or malware has spread, can cost an organization millions and take months to recover from.
</div>

<h3>What Happens After You Report</h3>
<ol>
<li>The security team acknowledges your report (often automatically)</li>
<li>They investigate and assess the scope and severity</li>
<li>They take containment action if needed (blocking a sender, isolating a device, resetting credentials)</li>
<li>They may follow up with you for more details</li>
<li>They typically communicate the outcome or any required follow-up actions</li>
</ol>

<p>Reporting is not an admission of failure — it\'s an active contribution to your organization\'s security. The employees who report suspicious activity are the ones who stop incidents before they become disasters.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Incident Reporting Procedures Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'You receive a suspicious email but did not click anything in it. Should you report it?',
                            'explanation' => 'Even suspicious emails that were not acted upon should be reported — they may be part of a broader campaign targeting your organization.',
                            'answers' => [
                                ['text' => 'No, only report if you actually clicked something', 'correct' => false],
                                ['text' => 'Yes, report it even though you didn\'t click anything', 'correct' => true],
                                ['text' => 'Only report it if it happens more than once', 'correct' => false],
                                ['text' => 'No, just delete it silently', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the "when in doubt, report" principle?',
                            'explanation' => 'You don\'t need certainty that something is a real incident before reporting — security teams prefer investigating false alarms over missing real ones.',
                            'answers' => [
                                ['text' => 'Only report incidents you are 100% certain about', 'correct' => false],
                                ['text' => 'You should report anything that might be a security concern, even if unsure', 'correct' => true],
                                ['text' => 'Wait 24 hours before reporting to confirm it\'s real', 'correct' => false],
                                ['text' => 'Ask a coworker\'s opinion before ever reporting', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which channel should you use for an active, ongoing incident like suspected ransomware encrypting files right now?',
                            'explanation' => 'Active, urgent incidents should be reported via the most immediate channel available, such as a security hotline or phone call, not email.',
                            'answers' => [
                                ['text' => 'A low-priority email ticket', 'correct' => false],
                                ['text' => 'The most urgent channel available, such as a phone call or hotline', 'correct' => true],
                                ['text' => 'Wait until the next team meeting to mention it', 'correct' => false],
                                ['text' => 'Post about it on the company chat for general visibility', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a "blameless reporting culture"?',
                            'explanation' => 'A blameless culture focuses on fixing and learning from incidents rather than punishing the person who reported them, which encourages faster, more honest reporting.',
                            'answers' => [
                                ['text' => 'A culture where no one is ever held accountable for anything', 'correct' => false],
                                ['text' => 'A culture that focuses on fixing problems and learning, rather than punishing reporters', 'correct' => true],
                                ['text' => 'A policy where incidents are never investigated', 'correct' => false],
                                ['text' => 'A rule that only managers can report incidents', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why does reporting speed matter so much for incident response?',
                            'explanation' => 'An incident reported quickly can often be fully contained, while the same incident reported days later can result in much greater damage and cost.',
                            'answers' => [
                                ['text' => 'It doesn\'t matter — timing has no effect on outcomes', 'correct' => false],
                                ['text' => 'Faster reporting allows quicker containment, limiting damage and cost', 'correct' => true],
                                ['text' => 'It only matters for legal recordkeeping purposes', 'correct' => false],
                                ['text' => 'Slower reporting is actually safer since it avoids false alarms', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
