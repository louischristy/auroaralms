<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Database\Seeder;

class CourseSeederBatch3 extends Seeder
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
                            'answer_text' => $aData['answer'],
                            'is_correct' => $aData['is_correct'],
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
            // ── Module 15: Spear Phishing & Whaling ──
            [
                'title' => 'Spear Phishing & Whaling',
                'slug' => 'spear-phishing-whaling',
                'description' => 'Understand targeted phishing attacks aimed at specific individuals and executives, and learn how to defend against them.',
                'objectives' => [
                    'Distinguish spear phishing from generic phishing campaigns',
                    'Identify whaling attacks targeting executives and senior leadership',
                    'Recognize reconnaissance techniques attackers use to craft targeted emails',
                    'Apply verification procedures to prevent successful targeted attacks',
                ],
                'category' => 'Phishing & Email Security',
                'difficulty' => 'advanced',
                'duration_minutes' => 30,
                'passing_score' => 75,
                'sort_order' => 15,
                'lessons' => [
                    [
                        'title' => 'Understanding Spear Phishing',
                        'slug' => 'understanding-spear-phishing',
                        'duration_minutes' => 10,
                        'content' => '<h3>Understanding Spear Phishing</h3>
<p>Spear phishing is a highly targeted form of phishing where attackers research their victims and craft personalized messages designed to be convincing to a specific individual. Unlike mass phishing campaigns that cast a wide net, spear phishing emails reference real projects, colleagues, or events that the victim would recognize.</p>

<h3>How Spear Phishing Differs from Regular Phishing</h3>
<ul>
<li><strong>Personalization:</strong> Messages address you by name and reference your actual role, department, or recent activities</li>
<li><strong>Research-based:</strong> Attackers spend time gathering information from LinkedIn, company websites, social media, and public records before crafting the email</li>
<li><strong>Low volume, high impact:</strong> Instead of sending millions of generic emails, attackers send a handful of carefully crafted messages to specific targets</li>
<li><strong>Contextual timing:</strong> Attacks often coincide with real events — quarterly reports, company announcements, or industry conferences</li>
</ul>

<h3>Reconnaissance Techniques Attackers Use</h3>
<p>Before sending a spear phishing email, attackers build a profile of their target using publicly available information. They scour LinkedIn for your job title, reporting structure, and recent career changes. They review company press releases and SEC filings for project names and financial details. Social media posts revealing travel plans, personal interests, or professional frustrations all become ammunition for a convincing pretext.</p>

<p>Attackers also monitor data breaches for leaked credentials and use them to access internal systems, gaining even deeper knowledge of organizational structure, email formats, and ongoing projects. This level of preparation is what makes spear phishing so dangerous — the resulting email looks entirely legitimate because it is built from real information about you and your organization.</p>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Warning:</strong> An email that references your exact project name, your manager\'s name, and a real deadline is far more dangerous than one that says "Dear Customer." The more specific an unexpected request is, the more carefully you should verify it.
</div>',
                    ],
                    [
                        'title' => 'Whaling: Targeting the C-Suite',
                        'slug' => 'whaling-targeting-c-suite',
                        'duration_minutes' => 10,
                        'content' => '<h3>Whaling: Targeting the C-Suite</h3>
<p>Whaling is a specialized form of spear phishing that targets senior executives — CEOs, CFOs, CTOs, and board members. These attacks are called "whaling" because attackers go after the biggest targets in an organization, individuals who have the authority to approve large wire transfers, access the most sensitive data, or override security procedures.</p>

<h3>Why Executives Are Prime Targets</h3>
<ul>
<li><strong>Authority:</strong> Executives can authorize financial transactions, approve vendor changes, and access virtually any system or dataset in the organization</li>
<li><strong>Busy schedules:</strong> Senior leaders often review emails quickly between meetings, making them more likely to act on urgent requests without thorough verification</li>
<li><strong>Public profiles:</strong> Executive biographies, speaking engagements, board memberships, and social media presence provide rich material for crafting convincing pretexts</li>
<li><strong>Less security training:</strong> Ironically, some organizations exempt executives from mandatory security awareness training, creating a gap that attackers exploit</li>
</ul>

<h3>Common Whaling Scenarios</h3>
<p>A CFO receives what appears to be an email from the CEO requesting an urgent wire transfer for a confidential acquisition. The email references a real company the organization has been evaluating and insists on discretion, discouraging the CFO from verifying through normal channels. Another common scenario involves a fake legal subpoena or regulatory notice sent to a CEO, urging them to click a link to review documents related to a supposed investigation. These emails exploit authority, urgency, and confidentiality to bypass normal verification steps.</p>

<h3>Real-World Impact</h3>
<p>Whaling attacks have resulted in some of the largest financial losses from social engineering. In 2016, an Austrian aerospace company lost 42 million euros when attackers impersonated the CEO and instructed the finance department to transfer funds for a fake acquisition. Similar attacks have cost organizations tens of millions of dollars globally, with the FBI estimating that business email compromise — of which whaling is a major component — has caused over 50 billion dollars in losses since 2013.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Key principle:</strong> The higher someone\'s authority, the more rigorously their requests should be verified — not less. Organizations should establish out-of-band verification procedures specifically for high-value requests from senior leadership.
</div>',
                    ],
                    [
                        'title' => 'Defending Against Targeted Attacks',
                        'slug' => 'defending-targeted-attacks',
                        'duration_minutes' => 10,
                        'content' => '<h3>Defending Against Targeted Attacks</h3>
<p>Because spear phishing and whaling bypass many technical controls by using legitimate-looking, well-researched pretexts, defense requires a combination of technical measures, organizational procedures, and individual vigilance.</p>

<h3>Verification Procedures</h3>
<ul>
<li><strong>Out-of-band confirmation:</strong> For any financial request, credential change, or sensitive data transfer, verify through a different communication channel — call the requester on a known phone number, walk to their office, or use an internal messaging platform</li>
<li><strong>Dual authorization:</strong> Require two people to approve wire transfers, vendor payment changes, or access to sensitive systems</li>
<li><strong>Callback verification:</strong> Establish a policy that all requests above a financial threshold must be confirmed with a phone call to a pre-registered number, not a number provided in the email itself</li>
<li><strong>Slow down urgency:</strong> Treat any request that demands immediate action and discourages verification as a red flag, regardless of who it appears to come from</li>
</ul>

<h3>Technical Controls</h3>
<p>Organizations should implement DMARC, DKIM, and SPF to prevent direct domain spoofing. Advanced email security gateways can detect anomalies in email headers that indicate impersonation. Display name spoofing detection alerts users when an email\'s display name matches an internal executive but the address is external. Banner warnings on emails originating outside the organization remind recipients to verify before acting.</p>

<h3>Reducing Your Attack Surface</h3>
<ul>
<li><strong>Limit public exposure:</strong> Be cautious about how much organizational detail appears on LinkedIn, company websites, and social media</li>
<li><strong>Review social media privacy settings:</strong> Information about travel plans, personal interests, and professional relationships can all be weaponized</li>
<li><strong>Secure executive accounts:</strong> Enforce hardware security keys for MFA on all executive accounts, and monitor them for unusual login patterns</li>
</ul>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Best practice:</strong> Create a culture where verifying a request from a senior leader is seen as responsible — not disrespectful. Executives should actively encourage their teams to confirm unusual requests.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Spear Phishing & Whaling Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the primary difference between spear phishing and regular phishing?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Spear phishing targets specific individuals with personalized, researched messages, unlike generic phishing campaigns sent to many people.',
                            'answers' => [
                                ['answer' => 'Spear phishing uses more advanced malware', 'is_correct' => false],
                                ['answer' => 'Spear phishing targets specific individuals with personalized messages based on research', 'is_correct' => true],
                                ['answer' => 'Spear phishing only targets financial institutions', 'is_correct' => false],
                                ['answer' => 'Spear phishing is always sent via text message instead of email', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why are executives particularly vulnerable to whaling attacks?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Executives have authority to approve large transactions, often review emails quickly, have public profiles, and may receive less security training.',
                            'answers' => [
                                ['answer' => 'They use older computers that are easier to hack', 'is_correct' => false],
                                ['answer' => 'They never use email encryption', 'is_correct' => false],
                                ['answer' => 'They have authority over finances and sensitive data, and their public profiles provide rich material for pretexts', 'is_correct' => true],
                                ['answer' => 'They are required to click on all links in their email', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is "out-of-band verification"?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Out-of-band verification means confirming a request through a different communication channel than the one the request arrived on.',
                            'answers' => [
                                ['answer' => 'Verifying an email request by replying to the same email', 'is_correct' => false],
                                ['answer' => 'Confirming a request through a different communication channel, such as a phone call', 'is_correct' => true],
                                ['answer' => 'Checking if the sender is in your contacts list', 'is_correct' => false],
                                ['answer' => 'Forwarding the email to your IT department for scanning', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which reconnaissance technique do attackers commonly use before a spear phishing attack?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Attackers research targets using LinkedIn, social media, company websites, press releases, and leaked data from breaches.',
                            'answers' => [
                                ['answer' => 'Randomly guessing the victim\'s interests and job role', 'is_correct' => false],
                                ['answer' => 'Researching the target on LinkedIn, social media, and company websites to gather personal and professional details', 'is_correct' => true],
                                ['answer' => 'Calling the victim directly and asking for their information', 'is_correct' => false],
                                ['answer' => 'Sending a survey to the entire company', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A request arrives from your CEO demanding an urgent wire transfer and asking you to keep it confidential. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Urgency combined with requests for secrecy are classic whaling red flags. Always verify through a known phone number or in person.',
                            'answers' => [
                                ['answer' => 'Process the transfer immediately since it came from the CEO', 'is_correct' => false],
                                ['answer' => 'Reply to the email asking for more details', 'is_correct' => false],
                                ['answer' => 'Verify the request by calling the CEO on a known phone number or confirming in person', 'is_correct' => true],
                                ['answer' => 'Forward the email to a colleague for a second opinion', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 16: Email Link & Attachment Analysis ──
            [
                'title' => 'Email Link & Attachment Analysis',
                'slug' => 'email-link-attachment-analysis',
                'description' => 'Learn practical techniques to safely analyze suspicious links and attachments before opening them.',
                'objectives' => [
                    'Inspect URLs and hyperlinks for signs of malicious intent',
                    'Identify dangerous attachment types and obfuscation techniques',
                    'Use sandboxing and scanning tools to analyze suspicious files safely',
                    'Apply a decision framework for handling unknown links and attachments',
                ],
                'category' => 'Phishing & Email Security',
                'difficulty' => 'intermediate',
                'duration_minutes' => 25,
                'passing_score' => 75,
                'sort_order' => 16,
                'lessons' => [
                    [
                        'title' => 'Analyzing Suspicious Links',
                        'slug' => 'analyzing-suspicious-links',
                        'duration_minutes' => 8,
                        'content' => '<h3>Analyzing Suspicious Links</h3>
<p>Malicious links are the most common payload in phishing emails. Learning to inspect a URL before clicking it is one of the most valuable security skills you can develop. Attackers use a variety of techniques to disguise malicious URLs as legitimate ones, and understanding these techniques helps you spot them reliably.</p>

<h3>Hover Before You Click</h3>
<p>The most fundamental technique is hovering your mouse over a link without clicking to reveal the actual destination URL. The displayed text of a link can say anything — "Click here to view your invoice" — but the underlying URL tells the truth. In most email clients and browsers, hovering shows the real URL in a tooltip or the status bar at the bottom of the window.</p>

<h3>URL Anatomy and Red Flags</h3>
<ul>
<li><strong>Domain mismatch:</strong> The link text says "microsoft.com" but the actual URL points to "microsoft-login.malicious-site.com" — the real domain is always the last part before the first single slash</li>
<li><strong>Lookalike domains:</strong> Characters that look similar are swapped — "rnicrosoft.com" (rn instead of m), "paypa1.com" (number 1 instead of letter l), or internationalized domain names using Cyrillic characters</li>
<li><strong>URL shorteners:</strong> Services like bit.ly or tinyurl.com hide the real destination. Use URL expander tools to reveal where shortened links actually lead before clicking</li>
<li><strong>Excessive subdomains:</strong> "login.microsoft.com.verify-account.evil.com" — the real domain here is "evil.com," and everything before it is a subdomain designed to look legitimate</li>
<li><strong>IP addresses instead of domains:</strong> Legitimate companies virtually never send links with raw IP addresses like "http://192.168.1.100/login"</li>
</ul>

<h3>Safe Link Checking Tools</h3>
<p>When you are unsure about a link, do not click it to find out. Instead, copy the URL (right-click, copy link address) and paste it into a URL scanning service. VirusTotal, URLScan.io, and Google Safe Browsing all allow you to check a URL without visiting it. These services will tell you if the destination is known to be malicious, recently registered, or associated with phishing campaigns.</p>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Never</strong> paste a suspicious URL directly into your browser\'s address bar — even loading the page can trigger drive-by downloads or exploit browser vulnerabilities. Always use a scanning tool or sandbox.
</div>',
                    ],
                    [
                        'title' => 'Identifying Dangerous Attachments',
                        'slug' => 'identifying-dangerous-attachments',
                        'duration_minutes' => 9,
                        'content' => '<h3>Identifying Dangerous Attachments</h3>
<p>Email attachments remain one of the primary delivery mechanisms for malware. Attackers disguise malicious files using a variety of techniques that exploit both technical features and human psychology. Understanding which file types are dangerous and how attackers hide them is essential for protecting yourself and your organization.</p>

<h3>High-Risk File Types</h3>
<ul>
<li><strong>Executable files:</strong> .exe, .scr, .bat, .cmd, .ps1, .vbs, .js — these can run code directly on your system and should almost never arrive via email legitimately</li>
<li><strong>Office documents with macros:</strong> .docm, .xlsm, .pptm — the "m" suffix indicates macro-enabled files. Macros can execute arbitrary code when enabled</li>
<li><strong>Archive files:</strong> .zip, .rar, .7z — attackers use these to bypass email scanners, especially password-protected archives where the password is provided in the email body</li>
<li><strong>Disk image files:</strong> .iso, .img — these mount as virtual drives and can contain executable files that bypass some security scanning</li>
<li><strong>Shortcut files:</strong> .lnk — Windows shortcut files can execute commands and are increasingly used in phishing campaigns</li>
</ul>

<h3>Obfuscation Techniques</h3>
<p>Attackers use several tricks to make dangerous files appear safe. Double extensions like "invoice.pdf.exe" exploit Windows\' default behavior of hiding known file extensions, so the file appears as "invoice.pdf" to the user. Right-to-left override characters can make "report_exe.pdf" display as "report_fdp.exe" on screen, reversing the extension visually. Attackers also embed malicious macros in seemingly innocent Office documents, with the document itself displaying a blurry image and instructions to "Enable Content" to view it clearly.</p>

<h3>What to Do with Suspicious Attachments</h3>
<ul>
<li><strong>Were you expecting it?</strong> If someone sends an unexpected attachment, verify with the sender through a separate channel before opening</li>
<li><strong>Check the file type:</strong> Enable file extension display in your operating system so you can see the true extension</li>
<li><strong>Scan before opening:</strong> Right-click and scan with your antivirus, or upload to VirusTotal for multi-engine scanning</li>
<li><strong>Never enable macros:</strong> If a document asks you to enable macros or "Enable Content" to view it, treat it as suspicious</li>
</ul>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Tip:</strong> Configure your operating system to always show file extensions. On Windows, open File Explorer, go to View, and check "File name extensions." This simple change defeats many obfuscation tricks.
</div>',
                    ],
                    [
                        'title' => 'Sandboxing and Safe Analysis',
                        'slug' => 'sandboxing-safe-analysis',
                        'duration_minutes' => 8,
                        'content' => '<h3>Sandboxing and Safe Analysis</h3>
<p>When you need to examine a suspicious file or link more closely, sandboxing provides a safe, isolated environment where malicious content cannot harm your actual system. Sandboxing is a technique used by both security professionals and everyday users to analyze unknown content without risk.</p>

<h3>What Is a Sandbox?</h3>
<p>A sandbox is an isolated computing environment where programs can run without affecting the host system. Think of it as a disposable virtual computer — anything that happens inside the sandbox stays inside the sandbox. If a file turns out to be malware, it can only infect the sandbox, which is then simply deleted. Sandboxes can be full virtual machines, lightweight containers, or cloud-based analysis services.</p>

<h3>Cloud-Based Sandbox Services</h3>
<ul>
<li><strong>VirusTotal:</strong> Upload files or URLs for scanning by over 70 antivirus engines simultaneously. Results include detection rates, behavioral analysis, and community reputation scores</li>
<li><strong>Any.Run:</strong> An interactive sandbox that lets you watch malware execute in real time in a virtual Windows environment, showing exactly what the file does — which files it creates, which network connections it makes, and which registry keys it modifies</li>
<li><strong>Hybrid Analysis:</strong> Provides automated malware analysis with detailed reports including network traffic, dropped files, and behavioral indicators</li>
<li><strong>URLScan.io:</strong> Takes a screenshot of a URL and analyzes its behavior without you ever visiting the page, showing redirects, loaded resources, and any detected threats</li>
</ul>

<h3>Your Decision Framework</h3>
<p>When you receive a suspicious link or attachment, follow this process: First, assess the context — were you expecting this file from this sender? Second, inspect without interacting — hover over links, check file extensions, look for red flags. Third, scan externally — use VirusTotal or your organization\'s security tools. Fourth, if still uncertain, do not open it — report it to your security team and let them analyze it in a controlled environment. The cost of delaying to verify is always lower than the cost of opening something malicious.</p>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Remember:</strong> You are not expected to be a malware analyst. Your job is to recognize something suspicious and route it to the right people. When in doubt, report it — your security team would rather investigate ten false alarms than miss one real attack.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Email Link & Attachment Analysis Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'How can you identify the real domain in a URL like "login.microsoft.com.evil-site.com/verify"?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The real domain is the last part before the first single slash. In this case, "evil-site.com" is the actual domain.',
                            'answers' => [
                                ['answer' => 'The real domain is "login.microsoft.com"', 'is_correct' => false],
                                ['answer' => 'The real domain is "evil-site.com" — it is the last domain segment before the first slash', 'is_correct' => true],
                                ['answer' => 'The real domain is "verify"', 'is_correct' => false],
                                ['answer' => 'You cannot determine the real domain without clicking the link', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why are password-protected ZIP archives a concern in email?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Password-protected archives cannot be scanned by email security gateways, allowing malicious files to bypass automated detection.',
                            'answers' => [
                                ['answer' => 'ZIP files always contain viruses', 'is_correct' => false],
                                ['answer' => 'Password protection prevents email security scanners from inspecting the contents', 'is_correct' => true],
                                ['answer' => 'Passwords on ZIP files are always weak and easily cracked', 'is_correct' => false],
                                ['answer' => 'ZIP files cannot be opened on modern operating systems', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the purpose of a malware sandbox?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A sandbox provides an isolated environment where suspicious files can be executed and analyzed without risking damage to the actual system.',
                            'answers' => [
                                ['answer' => 'To permanently store malware samples for research', 'is_correct' => false],
                                ['answer' => 'To provide an isolated environment where suspicious files can be safely executed and analyzed', 'is_correct' => true],
                                ['answer' => 'To block all email attachments from being delivered', 'is_correct' => false],
                                ['answer' => 'To encrypt files so they cannot be opened by anyone', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A document asks you to "Enable Content" or "Enable Macros" to view it. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Legitimate documents do not require macros to be viewed. This is a common technique used to trick users into enabling malicious code execution.',
                            'answers' => [
                                ['answer' => 'Enable macros since the document needs them to display properly', 'is_correct' => false],
                                ['answer' => 'Enable macros but only temporarily while you view the document', 'is_correct' => false],
                                ['answer' => 'Treat it as suspicious — legitimate documents do not require macros to be viewed, and report it to security', 'is_correct' => true],
                                ['answer' => 'Forward it to a colleague to open on their computer instead', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does a double file extension like "invoice.pdf.exe" indicate?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Double extensions are used to disguise executable files as safe file types. The real extension is the last one (.exe), but Windows may hide it by default.',
                            'answers' => [
                                ['answer' => 'The file is a PDF that has been compressed into an executable format', 'is_correct' => false],
                                ['answer' => 'The file is corrupted and cannot be opened', 'is_correct' => false],
                                ['answer' => 'It is likely a malicious executable disguised as a PDF — the true file type is determined by the last extension', 'is_correct' => true],
                                ['answer' => 'The file is a special format that requires both PDF and EXE readers', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 17: Pretexting & Impersonation ──
            [
                'title' => 'Pretexting & Impersonation',
                'slug' => 'pretexting-impersonation',
                'description' => 'Learn how attackers build fake scenarios and impersonate trusted figures to manipulate people into divulging information or granting access.',
                'objectives' => [
                    'Define pretexting and explain how it differs from other social engineering techniques',
                    'Identify common impersonation personas used in attacks',
                    'Recognize the psychological principles attackers exploit during pretexting',
                    'Apply verification strategies to counter pretexting attempts',
                ],
                'category' => 'Social Engineering',
                'difficulty' => 'intermediate',
                'duration_minutes' => 25,
                'passing_score' => 75,
                'sort_order' => 17,
                'lessons' => [
                    [
                        'title' => 'The Art of Pretexting',
                        'slug' => 'art-of-pretexting',
                        'duration_minutes' => 8,
                        'content' => '<h3>The Art of Pretexting</h3>
<p>Pretexting is a social engineering technique where an attacker creates a fabricated scenario — a pretext — to engage a victim and manipulate them into providing information, granting access, or performing an action they otherwise would not. Unlike phishing, which relies primarily on email and deceptive links, pretexting often involves direct human interaction through phone calls, in-person visits, or extended conversations over messaging platforms.</p>

<h3>How Pretexting Works</h3>
<p>A successful pretext has three components: a believable identity, a plausible story, and a reasonable request. The attacker first establishes who they claim to be — an IT support technician, a vendor, a new employee, or an auditor. They then present a scenario that explains why they need something from the target — a system upgrade requires temporary credentials, a compliance audit needs employee records, or a delivery requires after-hours access. Finally, the request itself is calibrated to seem proportionate to the scenario, so it does not raise immediate suspicion.</p>

<h3>Why Pretexting Is Effective</h3>
<ul>
<li><strong>Authority:</strong> People tend to comply with requests from perceived authority figures such as IT staff, executives, or auditors</li>
<li><strong>Helpfulness:</strong> Most employees want to be cooperative and helpful, especially when someone appears to be in a difficult situation</li>
<li><strong>Social proof:</strong> Attackers drop names of real employees or reference real projects to establish credibility and belonging</li>
<li><strong>Reciprocity:</strong> An attacker might do a small favor first — fixing a printer issue or holding a door — before making their real request</li>
</ul>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Key insight:</strong> Pretexting exploits trust and social norms, not technical vulnerabilities. The best firewall in the world cannot stop an employee from voluntarily sharing a password with someone they believe is from IT support.
</div>',
                    ],
                    [
                        'title' => 'Common Impersonation Personas',
                        'slug' => 'common-impersonation-personas',
                        'duration_minutes' => 9,
                        'content' => '<h3>Common Impersonation Personas</h3>
<p>Attackers choose their impersonation persona based on the type of access or information they need. Each persona comes with built-in authority and a natural reason to make requests that would otherwise seem unusual. Understanding these common personas helps you recognize when someone might not be who they claim to be.</p>

<h3>IT Support Technician</h3>
<p>This is one of the most frequently impersonated roles. The attacker calls claiming to be from the help desk, referencing a "ticket" or "system alert" about the victim\'s account. They may ask for credentials to "verify your account," request remote access to "fix an issue," or instruct the victim to visit a malicious website to "install an update." The urgency of a supposed technical problem makes people comply quickly.</p>

<h3>Vendor or Contractor</h3>
<p>Posing as a vendor representative, the attacker might claim they need access to a server room for maintenance, request updated payment information for an existing contract, or ask for credentials to a shared system. They reference real vendor names and contract details gathered during reconnaissance to sound credible. This persona is especially effective because organizations routinely interact with many vendors, making it difficult to verify every individual.</p>

<h3>New Employee or Intern</h3>
<p>An attacker posing as a new hire can ask seemingly innocent questions to gather sensitive information — "I\'m new and can\'t figure out how to access the shared drive, can you show me?" or "What\'s the Wi-Fi password for the office?" This persona exploits people\'s natural desire to help newcomers and makes information requests seem reasonable.</p>

<h3>Executive or Authority Figure</h3>
<p>Impersonating a senior leader creates pressure to comply without questioning. The attacker might call the front desk claiming to be the VP of Operations, locked out of their account while traveling, and urgently needing a password reset. Fear of displeasing a superior overrides security training, especially when the request comes with time pressure.</p>

<h3>External Auditor or Regulator</h3>
<p>Claiming to be from a regulatory body or audit firm, attackers request access to sensitive systems, financial records, or employee data. The implied threat of non-compliance penalties makes people anxious to cooperate quickly, often bypassing normal verification procedures in their haste to appear compliant.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Notice the pattern:</strong> Every persona carries built-in authority and a plausible reason for unusual requests. The defense is always the same — verify identity through official channels before complying.
</div>',
                    ],
                    [
                        'title' => 'Countering Pretexting Attacks',
                        'slug' => 'countering-pretexting-attacks',
                        'duration_minutes' => 8,
                        'content' => '<h3>Countering Pretexting Attacks</h3>
<p>Defending against pretexting requires a combination of healthy skepticism, established verification procedures, and an organizational culture that supports questioning unusual requests. No one should feel embarrassed about verifying someone\'s identity — this is a sign of good security awareness, not paranoia.</p>

<h3>Verification Strategies</h3>
<ul>
<li><strong>Call back on an official number:</strong> If someone claims to be from IT, a vendor, or another department, look up their number in the company directory or official records and call them back directly — do not use a number they provide</li>
<li><strong>Ask for credentials:</strong> Legitimate visitors should have an appointment, a point of contact, or an employee badge. Ask them to verify through their official contact within your organization</li>
<li><strong>Check with your manager:</strong> When in doubt, escalate. If someone is making an unusual request, tell them you need to confirm with your supervisor before proceeding</li>
<li><strong>Use challenge questions:</strong> Establish internal verification codes or questions that only legitimate staff would know, especially for phone-based requests involving account changes or data access</li>
</ul>

<h3>Red Flags to Watch For</h3>
<ul>
<li>Urgency or pressure to act immediately without following normal procedures</li>
<li>Requests to bypass security controls or "make an exception just this once"</li>
<li>Emotional manipulation — flattery, intimidation, or sympathy</li>
<li>Name-dropping executives or projects to establish legitimacy without offering verifiable credentials</li>
<li>Resistance to being verified — "Don\'t you know who I am?" or "There\'s no time for that"</li>
</ul>

<h3>Building Organizational Resilience</h3>
<p>Organizations should establish clear procedures for identity verification, especially for requests involving credentials, physical access, financial transactions, or sensitive data. Regular social engineering simulations — including phone-based pretexting tests — help employees practice recognizing and responding to these attacks in a safe environment. Post-simulation debriefs reinforce lessons learned without shaming individuals who were deceived.</p>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Golden rule:</strong> Legitimate requesters will understand and appreciate verification. Anyone who pressures you to skip verification is either an attacker or someone who needs a reminder about security policy.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Pretexting & Impersonation Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What are the three components of a successful pretext?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A successful pretext requires a believable identity, a plausible story, and a reasonable request that fits the scenario.',
                            'answers' => [
                                ['answer' => 'A fake website, a phishing email, and malware', 'is_correct' => false],
                                ['answer' => 'A believable identity, a plausible story, and a reasonable request', 'is_correct' => true],
                                ['answer' => 'A uniform, a fake badge, and a clipboard', 'is_correct' => false],
                                ['answer' => 'Technical knowledge, hacking tools, and a VPN', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Someone calls claiming to be from IT support and asks for your password to fix a system issue. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Legitimate IT support will never ask for your password. Hang up and call IT on the official number listed in your company directory.',
                            'answers' => [
                                ['answer' => 'Give them the password since IT needs it to help you', 'is_correct' => false],
                                ['answer' => 'Refuse and hang up, then call IT on the official company directory number to verify', 'is_correct' => true],
                                ['answer' => 'Give them a fake password to test if they are legitimate', 'is_correct' => false],
                                ['answer' => 'Ask them to email you instead so you have a written record', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is the "new employee" persona effective for social engineering?',
                            'type' => 'multiple_choice',
                            'explanation' => 'People naturally want to help newcomers, and questions from new employees about systems, access, and procedures seem innocent and reasonable.',
                            'answers' => [
                                ['answer' => 'New employees have special access privileges that attackers want to exploit', 'is_correct' => false],
                                ['answer' => 'People naturally want to help newcomers, making information requests seem innocent', 'is_correct' => true],
                                ['answer' => 'New employees are not tracked by security cameras', 'is_correct' => false],
                                ['answer' => 'Companies never verify the identity of new employees', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of the following is a red flag that someone may be using pretexting?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Resistance to identity verification is a strong indicator of pretexting. Legitimate people will cooperate with security procedures.',
                            'answers' => [
                                ['answer' => 'They arrive at the scheduled time for their appointment', 'is_correct' => false],
                                ['answer' => 'They provide their employee badge when asked', 'is_correct' => false],
                                ['answer' => 'They resist verification and pressure you to act immediately', 'is_correct' => true],
                                ['answer' => 'They follow the visitor sign-in process at reception', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What psychological principle does an attacker exploit when they say "Your VP asked me to handle this directly"?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Name-dropping an authority figure exploits the principle of authority — people tend to comply with requests associated with senior leaders.',
                            'answers' => [
                                ['answer' => 'Scarcity — implying limited time to act', 'is_correct' => false],
                                ['answer' => 'Authority — associating the request with a senior leader to pressure compliance', 'is_correct' => true],
                                ['answer' => 'Reciprocity — doing a favor to expect one in return', 'is_correct' => false],
                                ['answer' => 'Consistency — referencing a previous agreement', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 18: Tailgating & Physical Social Engineering ──
            [
                'title' => 'Tailgating & Physical Social Engineering',
                'slug' => 'tailgating-physical-social-engineering',
                'description' => 'Recognize and prevent physical social engineering attacks including tailgating, piggybacking, and unauthorized facility access.',
                'objectives' => [
                    'Define tailgating and piggybacking and explain the difference between them',
                    'Identify common physical social engineering tactics used to gain building access',
                    'Apply proper access control procedures to prevent unauthorized entry',
                    'Respond appropriately when encountering a potential tailgating situation',
                ],
                'category' => 'Social Engineering',
                'difficulty' => 'beginner',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'sort_order' => 18,
                'lessons' => [
                    [
                        'title' => 'Understanding Physical Social Engineering',
                        'slug' => 'understanding-physical-social-engineering',
                        'duration_minutes' => 7,
                        'content' => '<h3>Understanding Physical Social Engineering</h3>
<p>Physical social engineering involves manipulating people to gain unauthorized physical access to buildings, offices, server rooms, or other restricted areas. While cybersecurity often focuses on digital threats, many of the most damaging breaches begin with an attacker physically entering a facility. Once inside, an attacker can install hardware keyloggers, access unlocked computers, steal documents, plant listening devices, or connect rogue devices to the internal network.</p>

<h3>Tailgating vs. Piggybacking</h3>
<p>These two terms describe slightly different techniques for bypassing physical access controls:</p>
<ul>
<li><strong>Tailgating:</strong> Following an authorized person through a secure door without their knowledge. The attacker walks closely behind as the door closes, slipping through before it latches. The authorized person may not even realize someone followed them in.</li>
<li><strong>Piggybacking:</strong> Following an authorized person through a secure door with their knowledge and consent. The attacker might say "I forgot my badge" or have their hands full with boxes, prompting the authorized person to hold the door open out of courtesy.</li>
</ul>

<h3>Why Physical Access Matters</h3>
<p>Physical access to a facility can bypass nearly all digital security controls. An attacker inside your building can plug a small rogue device into a network port that provides persistent remote access. They can install a hardware keylogger between a keyboard and computer that records every keystroke, including passwords. They can photograph sensitive documents, whiteboards with strategic plans, or screens displaying confidential information. A few minutes of physical access can compromise months of careful cybersecurity work.</p>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Reality check:</strong> Penetration testers report that tailgating is one of the easiest ways to gain unauthorized access to a building. Most people instinctively hold doors open for others, and few are comfortable challenging someone who appears to belong.
</div>',
                    ],
                    [
                        'title' => 'Common Physical Access Tactics',
                        'slug' => 'common-physical-access-tactics',
                        'duration_minutes' => 7,
                        'content' => '<h3>Common Physical Access Tactics</h3>
<p>Beyond tailgating and piggybacking, attackers use a variety of creative tactics to gain unauthorized physical access. Recognizing these techniques helps you stay alert to attempts that might otherwise seem innocent or unremarkable.</p>

<h3>The Delivery Person</h3>
<p>An attacker arrives carrying packages, wearing a uniform from a delivery company, and asks to be let in to deliver items to a specific department. The uniform and packages create an immediate assumption of legitimacy. In some cases, attackers purchase authentic-looking uniforms online or simply wear a high-visibility vest and carry a clipboard, which is often enough to avoid questioning.</p>

<h3>The Hands-Full Trick</h3>
<p>An attacker approaches a secure entrance carrying a stack of boxes, a tray of coffee cups, or other items that make it difficult to badge in. Most people instinctively hold the door open to help someone who appears to be struggling. This exploits natural politeness and is remarkably effective in office environments where people regularly carry supplies.</p>

<h3>The Smoker\'s Entrance</h3>
<p>Many buildings have designated smoking areas near side entrances that are frequently propped open or where access controls are less strict. Attackers join the group of smokers, engage in casual conversation, and then walk back inside with the group. Building rapport with regular smokers over several visits can establish the attacker as a familiar face who is never questioned.</p>

<h3>The Emergency or Urgency Play</h3>
<p>An attacker claims to be late for a critical meeting, locked out because their badge was stolen, or responding to an urgent maintenance request. The manufactured urgency pressures employees to help immediately rather than following verification procedures. This works because people do not want to be responsible for causing someone to miss an important meeting or delaying a critical repair.</p>

<h3>Dumpster Diving</h3>
<p>While not strictly a building access technique, going through an organization\'s trash can provide badge templates, internal phone directories, organizational charts, and discarded access credentials that support more sophisticated physical intrusion attempts later. Many organizations do not adequately secure their waste disposal areas.</p>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Think about it:</strong> How many of these scenarios would succeed at your workplace? If you can picture any of them working, that represents a gap worth addressing with your security team.
</div>',
                    ],
                    [
                        'title' => 'Prevention and Response',
                        'slug' => 'tailgating-prevention-response',
                        'duration_minutes' => 6,
                        'content' => '<h3>Prevention and Response</h3>
<p>Preventing physical social engineering requires a combination of technical controls, clear procedures, and a culture where challenging unverified individuals is encouraged and supported by management.</p>

<h3>Individual Best Practices</h3>
<ul>
<li><strong>One person, one badge:</strong> Never hold doors for people you do not recognize. Politely direct them to reception or ask them to badge in themselves</li>
<li><strong>Challenge unknown individuals:</strong> If you see someone in a restricted area without a visible badge, ask if you can help them or direct them to where they should check in</li>
<li><strong>Report propped-open doors:</strong> If you find a secure door propped open, close it and report it to facilities or security</li>
<li><strong>Escort visitors:</strong> Visitors should always be accompanied by an employee, especially in areas with sensitive equipment or information</li>
<li><strong>Lock your workstation:</strong> Press Windows+L or Ctrl+Command+Q every time you leave your desk, even briefly</li>
</ul>

<h3>Organizational Controls</h3>
<ul>
<li><strong>Mantraps and turnstiles:</strong> Physical barriers that allow only one person through at a time, preventing tailgating entirely</li>
<li><strong>Visitor management systems:</strong> Require all visitors to sign in, receive a temporary badge, and be assigned an escort</li>
<li><strong>Security cameras:</strong> Visible cameras at access points deter casual attempts and provide evidence for investigation</li>
<li><strong>Badge-visible policy:</strong> Require all employees to wear visible identification at all times while on premises</li>
<li><strong>Regular penetration testing:</strong> Include physical social engineering in security assessments to identify weaknesses</li>
</ul>

<h3>How to Challenge Someone Politely</h3>
<p>Many people feel uncomfortable questioning someone in their workplace. Use these approaches that are firm but respectful: "Hi, I don\'t think we\'ve met — are you visiting someone today?" or "Can I help you find where you\'re going? I\'d be happy to walk you to reception." If someone becomes hostile when asked to verify their identity, that itself is a strong red flag — report it to security immediately.</p>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Remember:</strong> Being politely cautious is not rude — it is responsible. You are protecting your colleagues, your organization, and yourself.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Tailgating & Physical Social Engineering Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the difference between tailgating and piggybacking?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Tailgating is following through without the authorized person\'s knowledge, while piggybacking involves their knowing consent (e.g., holding the door).',
                            'answers' => [
                                ['answer' => 'Tailgating is done without the authorized person\'s knowledge; piggybacking is done with their consent', 'is_correct' => true],
                                ['answer' => 'Tailgating is done in vehicles; piggybacking is done on foot', 'is_correct' => false],
                                ['answer' => 'Tailgating involves fake badges; piggybacking involves no badges', 'is_correct' => false],
                                ['answer' => 'There is no difference — they are the same thing', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is physical access to a facility a serious security concern?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Physical access allows attackers to install rogue devices, access unlocked computers, steal documents, and bypass digital security controls entirely.',
                            'answers' => [
                                ['answer' => 'It is not a serious concern because digital security controls protect everything', 'is_correct' => false],
                                ['answer' => 'Physical access allows attackers to bypass digital controls by installing hardware, accessing systems, and stealing information directly', 'is_correct' => true],
                                ['answer' => 'Physical access only matters for stealing office supplies', 'is_correct' => false],
                                ['answer' => 'Physical access is only a concern in government buildings', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Someone approaches a secure door behind you carrying boxes and says they forgot their badge. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Politely direct them to reception or ask them to contact their host. Holding the door defeats the purpose of access controls.',
                            'answers' => [
                                ['answer' => 'Hold the door open for them since they clearly work here', 'is_correct' => false],
                                ['answer' => 'Ignore them and walk through quickly', 'is_correct' => false],
                                ['answer' => 'Politely direct them to reception or ask them to contact their host for assistance', 'is_correct' => true],
                                ['answer' => 'Ask them for their employee ID number verbally', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which physical control most effectively prevents tailgating?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Mantraps and turnstiles are designed to allow only one person through at a time, making tailgating physically impossible.',
                            'answers' => [
                                ['answer' => 'Security cameras that record everyone entering', 'is_correct' => false],
                                ['answer' => 'A sign reminding people not to hold the door', 'is_correct' => false],
                                ['answer' => 'Mantraps or turnstiles that allow only one person through at a time', 'is_correct' => true],
                                ['answer' => 'Requiring all visitors to wear red badges', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do if you find a secure door propped open?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Propped-open secure doors defeat access controls entirely. Close the door and report it to facilities or security.',
                            'answers' => [
                                ['answer' => 'Leave it open since someone probably propped it for a reason', 'is_correct' => false],
                                ['answer' => 'Close it and report it to facilities or security', 'is_correct' => true],
                                ['answer' => 'Stand guard by the door until security arrives', 'is_correct' => false],
                                ['answer' => 'Send an email to the entire office about the open door', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 19: Credential Management & SSO ──
            [
                'title' => 'Credential Management & SSO',
                'slug' => 'credential-management-sso',
                'description' => 'Learn how enterprise credential management and single sign-on systems work, and how to use them securely.',
                'objectives' => [
                    'Explain how single sign-on (SSO) and federated identity systems work',
                    'Use enterprise password managers effectively and securely',
                    'Understand the security benefits and risks of centralized credential management',
                    'Follow best practices for managing service accounts and shared credentials',
                ],
                'category' => 'Password & Authentication',
                'difficulty' => 'intermediate',
                'duration_minutes' => 30,
                'passing_score' => 75,
                'sort_order' => 19,
                'lessons' => [
                    [
                        'title' => 'Single Sign-On Explained',
                        'slug' => 'single-sign-on-explained',
                        'duration_minutes' => 10,
                        'content' => '<h3>Single Sign-On Explained</h3>
<p>Single Sign-On (SSO) is an authentication mechanism that allows users to log in once and gain access to multiple applications and services without re-entering credentials for each one. When you log into your company\'s identity provider in the morning and then seamlessly access your email, project management tool, HR portal, and cloud storage without additional login prompts, that is SSO at work.</p>

<h3>How SSO Works</h3>
<p>SSO relies on a trusted identity provider (IdP) — such as Okta, Azure Active Directory, or Google Workspace — that authenticates you and then issues secure tokens to other applications (called service providers) confirming your identity. When you click on an application, it redirects you to the IdP, which recognizes your existing session and sends back a token that grants access. The application never sees your password; it only receives a cryptographic assertion that the IdP has verified your identity.</p>

<h3>Common SSO Protocols</h3>
<ul>
<li><strong>SAML (Security Assertion Markup Language):</strong> The most widely used protocol for enterprise SSO. It exchanges XML-based authentication data between the IdP and service providers. Most enterprise SaaS applications support SAML</li>
<li><strong>OAuth 2.0 / OpenID Connect:</strong> OAuth handles authorization (what you can access), while OpenID Connect adds an authentication layer (who you are). These are commonly used for web and mobile applications, and are what power "Sign in with Google" or "Sign in with Microsoft" buttons</li>
<li><strong>Kerberos:</strong> Used primarily in on-premises Windows environments, Kerberos provides SSO within an Active Directory domain, allowing seamless access to file shares, printers, and internal applications</li>
</ul>

<h3>Security Benefits of SSO</h3>
<ul>
<li><strong>Fewer passwords:</strong> Users manage fewer credentials, reducing password fatigue and the temptation to reuse passwords across services</li>
<li><strong>Centralized access control:</strong> IT can enable or disable access to all applications from a single dashboard, making onboarding and offboarding faster and more reliable</li>
<li><strong>Stronger authentication:</strong> MFA can be enforced at the IdP level, automatically protecting every connected application</li>
<li><strong>Better visibility:</strong> Centralized logging shows who accessed what and when, improving audit capabilities</li>
</ul>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Important trade-off:</strong> SSO creates a single point of compromise — if an attacker gains access to your SSO account, they potentially gain access to everything. This is why MFA on your SSO identity provider is absolutely critical.
</div>',
                    ],
                    [
                        'title' => 'Enterprise Password Managers',
                        'slug' => 'enterprise-password-managers',
                        'duration_minutes' => 10,
                        'content' => '<h3>Enterprise Password Managers</h3>
<p>Even with SSO, most organizations have applications and systems that do not support it — legacy systems, vendor portals, social media accounts, and infrastructure credentials all require separate passwords. Enterprise password managers provide a secure, centralized way to store, share, and manage these credentials across teams without resorting to sticky notes, spreadsheets, or shared documents.</p>

<h3>How Enterprise Password Managers Work</h3>
<p>An enterprise password manager encrypts all stored credentials using strong encryption (typically AES-256) and stores them in a secure vault. Each user has their own vault protected by a master password and MFA. Administrators can create shared vaults for teams, controlling who has access to which credentials. When someone needs a password, they retrieve it from the vault rather than remembering it or storing it insecurely. Most password managers integrate with browsers and operating systems, automatically filling credentials when you visit a login page.</p>

<h3>Key Features for Organizations</h3>
<ul>
<li><strong>Secure sharing:</strong> Share credentials with team members without revealing the actual password — the password manager fills it in automatically, and access can be revoked instantly</li>
<li><strong>Password generation:</strong> Built-in generators create strong, unique passwords for every account, eliminating the risk of weak or reused passwords</li>
<li><strong>Audit trails:</strong> Administrators can see who accessed which credentials and when, supporting compliance and incident investigation</li>
<li><strong>Emergency access:</strong> Designated individuals can be granted emergency access to critical credentials if the primary owner is unavailable</li>
<li><strong>Breach monitoring:</strong> Many enterprise password managers monitor whether stored credentials appear in known data breaches and alert users to change compromised passwords</li>
</ul>

<h3>Best Practices</h3>
<ul>
<li>Use a strong, unique master password that you do not use anywhere else — this is the one password you must memorize</li>
<li>Enable MFA on your password manager account — it protects the keys to everything else</li>
<li>Never export your vault to a plain text file or share vault credentials via email or chat</li>
<li>Use the password generator for every new account — let the tool create randomized, strong passwords</li>
<li>Regularly review shared vault access and remove people who no longer need it</li>
</ul>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>The goal:</strong> You should only need to remember two things — your SSO password and your password manager master password. Everything else should be stored securely in the password manager and autofilled when needed.
</div>',
                    ],
                    [
                        'title' => 'Service Accounts and Shared Credentials',
                        'slug' => 'service-accounts-shared-credentials',
                        'duration_minutes' => 10,
                        'content' => '<h3>Service Accounts and Shared Credentials</h3>
<p>Service accounts are special accounts used by applications, automated processes, and systems rather than individual people. They power the integrations between your tools — the connection between your CRM and email system, the automated backup process that runs nightly, or the deployment pipeline that pushes code to production. Managing these accounts securely is critical because they often have elevated privileges and their compromise can affect entire systems.</p>

<h3>Risks of Service Accounts</h3>
<ul>
<li><strong>Static credentials:</strong> Service account passwords are often set once and never rotated, remaining valid for years even after the people who created them have left the organization</li>
<li><strong>Excessive privileges:</strong> Service accounts are frequently granted more access than they actually need, creating a larger blast radius if compromised</li>
<li><strong>Poor visibility:</strong> Without proper tracking, organizations lose track of which service accounts exist, what they access, and who is responsible for them</li>
<li><strong>Shared passwords:</strong> Teams sometimes share a single set of credentials among multiple people for convenience, making it impossible to trace actions to individuals</li>
</ul>

<h3>Best Practices for Service Account Security</h3>
<ul>
<li><strong>Inventory all service accounts:</strong> Maintain a register of every service account, its purpose, its privileges, and its owner. Review this register quarterly</li>
<li><strong>Apply least privilege:</strong> Grant each service account only the minimum permissions needed for its specific function</li>
<li><strong>Rotate credentials regularly:</strong> Automate credential rotation where possible. Use secrets management tools like HashiCorp Vault, AWS Secrets Manager, or Azure Key Vault</li>
<li><strong>Monitor for anomalies:</strong> Set up alerts for unusual service account behavior — logins from unexpected locations, access at unusual times, or privilege escalation attempts</li>
<li><strong>Assign ownership:</strong> Every service account must have a named human owner who is responsible for its security and lifecycle</li>
</ul>

<h3>Handling Shared Credentials</h3>
<p>When credentials truly must be shared — a social media account, a shared vendor portal, or a team email alias — store them in a team vault within your enterprise password manager. Never share credentials through email, chat messages, or documents. When a team member leaves or changes roles, rotate the shared credentials immediately. Wherever possible, replace shared credentials with individual accounts that have role-based access, providing accountability and easier revocation.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Audit question:</strong> If a service account were compromised today, would you know which systems it could access, when it was last used, and who is responsible for it? If the answer to any of these is "no," that account needs attention.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Credential Management & SSO Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the primary security risk of single sign-on (SSO)?',
                            'type' => 'multiple_choice',
                            'explanation' => 'SSO creates a single point of compromise — if the SSO account is breached, the attacker gains access to all connected applications.',
                            'answers' => [
                                ['answer' => 'SSO makes passwords weaker by using shorter credentials', 'is_correct' => false],
                                ['answer' => 'SSO creates a single point of compromise — one breached account gives access to everything', 'is_correct' => true],
                                ['answer' => 'SSO only works with outdated encryption protocols', 'is_correct' => false],
                                ['answer' => 'SSO requires users to remember more passwords than before', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the role of an Identity Provider (IdP) in SSO?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The IdP authenticates the user and issues cryptographic tokens to service providers confirming the user\'s identity, without sharing the actual password.',
                            'answers' => [
                                ['answer' => 'It stores all application data in a central database', 'is_correct' => false],
                                ['answer' => 'It authenticates the user and issues tokens to service providers confirming their identity', 'is_correct' => true],
                                ['answer' => 'It automatically generates new passwords for every application', 'is_correct' => false],
                                ['answer' => 'It replaces the need for any form of authentication', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should credentials never be shared via email or chat messages?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Email and chat messages can be intercepted, stored indefinitely in logs, and are difficult to revoke. Password managers provide secure sharing with access control.',
                            'answers' => [
                                ['answer' => 'Because email and chat services charge extra for sending passwords', 'is_correct' => false],
                                ['answer' => 'Because messages can be intercepted, persist in logs indefinitely, and cannot be revoked', 'is_correct' => true],
                                ['answer' => 'Because passwords are automatically deleted by email spam filters', 'is_correct' => false],
                                ['answer' => 'Because email services block messages containing passwords', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the most important action when a team member with access to shared credentials leaves the organization?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Shared credentials should be rotated immediately when someone with access leaves, since they could still use the old credentials.',
                            'answers' => [
                                ['answer' => 'Send them a reminder not to use the credentials after leaving', 'is_correct' => false],
                                ['answer' => 'Wait for the next scheduled rotation cycle to change the passwords', 'is_correct' => false],
                                ['answer' => 'Rotate all shared credentials the person had access to immediately', 'is_correct' => true],
                                ['answer' => 'Delete the shared vault and create a new one', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of the following is a best practice for service account management?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Every service account should have a named human owner responsible for its security, privileges, and lifecycle management.',
                            'answers' => [
                                ['answer' => 'Give service accounts full administrator privileges for flexibility', 'is_correct' => false],
                                ['answer' => 'Use the same service account for multiple applications to reduce complexity', 'is_correct' => false],
                                ['answer' => 'Assign a named human owner to every service account and review privileges quarterly', 'is_correct' => true],
                                ['answer' => 'Set strong passwords for service accounts once and never change them', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 20: Data Loss Prevention ──
            [
                'title' => 'Data Loss Prevention',
                'slug' => 'data-loss-prevention',
                'description' => 'Understand DLP tools, policies, and practices that prevent sensitive data from leaving your organization through unauthorized channels.',
                'objectives' => [
                    'Explain what data loss prevention (DLP) is and why it matters',
                    'Identify common channels through which data leaks occur',
                    'Understand how DLP policies and tools detect and block unauthorized data transfers',
                    'Follow organizational DLP policies in daily work activities',
                ],
                'category' => 'Data Protection & Privacy',
                'difficulty' => 'intermediate',
                'duration_minutes' => 25,
                'passing_score' => 75,
                'sort_order' => 20,
                'lessons' => [
                    [
                        'title' => 'What Is Data Loss Prevention?',
                        'slug' => 'what-is-dlp',
                        'duration_minutes' => 8,
                        'content' => '<h3>What Is Data Loss Prevention?</h3>
<p>Data Loss Prevention (DLP) is a set of strategies, policies, and tools designed to prevent sensitive information from being transferred outside an organization through unauthorized channels. DLP systems monitor data in three states: data at rest (stored on servers, databases, and endpoints), data in motion (being transmitted over networks, email, or messaging), and data in use (being accessed or modified by users and applications).</p>

<h3>Why DLP Matters</h3>
<p>Organizations handle vast quantities of sensitive data — customer personal information, financial records, intellectual property, trade secrets, health records, and employee data. A single data leak can result in regulatory fines running into millions of dollars, loss of customer trust, competitive disadvantage, and legal liability. DLP provides a safety net that catches both accidental disclosures (an employee emailing a spreadsheet to the wrong person) and malicious exfiltration (an insider deliberately stealing data before leaving the company).</p>

<h3>Types of Sensitive Data DLP Protects</h3>
<ul>
<li><strong>Personally Identifiable Information (PII):</strong> Names, Social Security numbers, addresses, dates of birth, and other data that can identify individuals</li>
<li><strong>Financial data:</strong> Credit card numbers, bank account details, financial statements, and transaction records</li>
<li><strong>Protected Health Information (PHI):</strong> Medical records, diagnoses, treatment plans, and insurance information</li>
<li><strong>Intellectual property:</strong> Source code, product designs, research data, proprietary algorithms, and trade secrets</li>
<li><strong>Regulated data:</strong> Any data subject to compliance frameworks such as GDPR, HIPAA, PCI-DSS, or SOX</li>
</ul>

<h3>How DLP Detection Works</h3>
<p>DLP tools use several techniques to identify sensitive data. Content inspection scans files and messages for patterns matching known sensitive data formats — credit card numbers, Social Security numbers, or specific keywords. Context analysis examines who is sending data, where it is going, and whether that transfer aligns with normal business patterns. Machine learning models can identify sensitive documents based on their structure and content, even when specific patterns are not present. Together, these techniques create a comprehensive detection capability that catches both obvious and subtle data exposure.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Key concept:</strong> DLP is not just a technology — it is a combination of policies defining what is sensitive, tools that enforce those policies, and people who handle data responsibly. Technology alone cannot prevent data loss without clear policies and user awareness.
</div>',
                    ],
                    [
                        'title' => 'Common Data Leak Channels',
                        'slug' => 'common-data-leak-channels',
                        'duration_minutes' => 8,
                        'content' => '<h3>Common Data Leak Channels</h3>
<p>Data can leave an organization through many channels, some obvious and some surprisingly subtle. Understanding these channels helps you recognize risky behaviors and understand why certain organizational policies exist.</p>

<h3>Email</h3>
<p>Email remains the most common channel for data leaks, both accidental and intentional. Autocomplete errors send sensitive spreadsheets to the wrong recipients. Reply-all on large distribution lists exposes confidential discussions. Forwarding email chains includes earlier messages the recipient should not see. Employees email files to personal accounts to "work from home," creating copies outside organizational control.</p>

<h3>Cloud Storage and File Sharing</h3>
<p>Uploading files to personal cloud storage accounts (Google Drive, Dropbox, OneDrive personal) moves data outside the organization\'s security perimeter. Sharing links with overly broad permissions — "anyone with the link can view" — can expose sensitive documents to the entire internet. Shadow IT services that employees adopt without IT approval may lack adequate security controls and data handling agreements.</p>

<h3>Removable Media</h3>
<p>USB drives, external hard drives, and SD cards can quickly copy large volumes of data. A departing employee can walk out with gigabytes of proprietary information on a pocket-sized device. Lost or stolen USB drives containing unencrypted data have been the source of numerous major data breaches, particularly in healthcare and government.</p>

<h3>Messaging and Collaboration Tools</h3>
<p>Employees may share sensitive information through personal messaging apps, social media direct messages, or unauthorized collaboration platforms. Screenshots of confidential screens shared via messaging bypass most DLP controls because the data is captured as an image rather than text.</p>

<h3>Printing and Physical Documents</h3>
<p>Printed documents containing sensitive data can be left on printers, desks, or in trash bins. Visitors or cleaning staff can photograph exposed documents. Print jobs sent to shared printers may be collected by the wrong person.</p>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>The accidental leak is far more common than the malicious one.</strong> Most data loss incidents result from carelessness, convenience shortcuts, or lack of awareness — not deliberate theft. DLP tools exist to catch these honest mistakes before they become breaches.
</div>',
                    ],
                    [
                        'title' => 'Working Within DLP Policies',
                        'slug' => 'working-within-dlp-policies',
                        'duration_minutes' => 9,
                        'content' => '<h3>Working Within DLP Policies</h3>
<p>DLP policies exist to protect both the organization and you as an individual. When a DLP tool blocks an action — preventing you from sending an email, uploading a file, or copying data to a USB drive — it is not a malfunction or an obstacle. It is detecting that something you are about to do involves sensitive data and a potentially risky channel.</p>

<h3>What Happens When DLP Triggers</h3>
<ul>
<li><strong>Block:</strong> The action is prevented entirely. You may see a notification explaining that the transfer violates policy. This happens for the highest-risk scenarios</li>
<li><strong>Warn:</strong> You receive an alert that the action involves sensitive data, but you can choose to proceed with a justification. The action and your justification are logged</li>
<li><strong>Encrypt:</strong> The data is automatically encrypted before transmission, ensuring it remains protected even if intercepted</li>
<li><strong>Quarantine:</strong> The file or message is held for review by the security team before delivery</li>
<li><strong>Log:</strong> The action is recorded for auditing purposes but not blocked. This is common for lower-risk scenarios where monitoring is sufficient</li>
</ul>

<h3>Best Practices for Handling Sensitive Data</h3>
<ul>
<li><strong>Know your data classification:</strong> Understand your organization\'s data classification scheme — public, internal, confidential, restricted — and apply the appropriate handling procedures for each level</li>
<li><strong>Use approved channels:</strong> Transfer sensitive data only through approved and encrypted channels. If you need to share data externally, use the organization\'s secure file transfer solution, not personal email or cloud storage</li>
<li><strong>Minimize data exposure:</strong> Share only the specific data needed, not entire databases or files. Redact or mask sensitive fields when full data is not required</li>
<li><strong>Clean up after yourself:</strong> Delete local copies of sensitive data when you no longer need them. Clear your downloads folder regularly. Shred printed documents containing sensitive information</li>
</ul>

<h3>When DLP Gets in the Way</h3>
<p>If a DLP policy blocks a legitimate business action, do not try to work around it by renaming files, splitting data across multiple emails, or using personal accounts. Instead, contact your IT or security team to request an exception or find an approved alternative. Circumventing DLP controls is typically a policy violation that can result in disciplinary action, and it undermines the protections that safeguard everyone\'s data.</p>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Think of DLP as a seatbelt:</strong> It might occasionally be inconvenient, but it exists to protect you and others from serious harm. Work with it, not around it.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Data Loss Prevention Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What are the three states of data that DLP systems monitor?',
                            'type' => 'multiple_choice',
                            'explanation' => 'DLP monitors data at rest (stored), data in motion (being transmitted), and data in use (being accessed or modified).',
                            'answers' => [
                                ['answer' => 'Data at rest, data in motion, and data in use', 'is_correct' => true],
                                ['answer' => 'Data in databases, data in spreadsheets, and data in emails', 'is_correct' => false],
                                ['answer' => 'Encrypted data, unencrypted data, and archived data', 'is_correct' => false],
                                ['answer' => 'Public data, private data, and classified data', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the most common channel through which data leaks occur?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Email remains the most common data leak channel due to autocomplete errors, reply-all mistakes, and employees sending files to personal accounts.',
                            'answers' => [
                                ['answer' => 'USB drives and removable media', 'is_correct' => false],
                                ['answer' => 'Email — through autocomplete errors, reply-all mistakes, and forwarding sensitive information', 'is_correct' => true],
                                ['answer' => 'Cloud storage services', 'is_correct' => false],
                                ['answer' => 'Printed documents left on desks', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A DLP tool blocks you from sending a file that a client urgently needs. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Never circumvent DLP controls. Contact IT or security to request an exception or find an approved secure method for the transfer.',
                            'answers' => [
                                ['answer' => 'Rename the file extension and try again', 'is_correct' => false],
                                ['answer' => 'Send it from your personal email account instead', 'is_correct' => false],
                                ['answer' => 'Contact IT or security to request an exception or find an approved alternative', 'is_correct' => true],
                                ['answer' => 'Split the file into smaller pieces to avoid detection', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which DLP response involves allowing the action but requiring the user to provide a business justification?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A "warn" response alerts the user that sensitive data is involved, allows them to proceed with a justification, and logs both the action and the reason.',
                            'answers' => [
                                ['answer' => 'Block', 'is_correct' => false],
                                ['answer' => 'Quarantine', 'is_correct' => false],
                                ['answer' => 'Warn — the user can proceed after providing a justification, which is logged', 'is_correct' => true],
                                ['answer' => 'Encrypt', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is emailing files to your personal account to work from home a data security risk?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Personal accounts move data outside the organization\'s security controls, DLP monitoring, and access management, creating uncontrolled copies of sensitive data.',
                            'answers' => [
                                ['answer' => 'It uses too much bandwidth on the company network', 'is_correct' => false],
                                ['answer' => 'It creates copies of data outside the organization\'s security controls and monitoring', 'is_correct' => true],
                                ['answer' => 'Personal email services automatically share files publicly', 'is_correct' => false],
                                ['answer' => 'It is only a risk if the files are larger than 25 MB', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 21: Cloud Data Security ──
            [
                'title' => 'Cloud Data Security',
                'slug' => 'cloud-data-security',
                'description' => 'Learn how to secure data stored in cloud services, understand the shared responsibility model, and prevent cloud-specific data exposures.',
                'objectives' => [
                    'Understand the shared responsibility model for cloud security',
                    'Identify common cloud data security risks and misconfigurations',
                    'Apply access controls and encryption to protect cloud-stored data',
                    'Follow best practices for using cloud storage and collaboration tools securely',
                ],
                'category' => 'Data Protection & Privacy',
                'difficulty' => 'intermediate',
                'duration_minutes' => 30,
                'passing_score' => 75,
                'sort_order' => 21,
                'lessons' => [
                    [
                        'title' => 'The Shared Responsibility Model',
                        'slug' => 'shared-responsibility-model',
                        'duration_minutes' => 10,
                        'content' => '<h3>The Shared Responsibility Model</h3>
<p>When your organization stores data in the cloud, security is not entirely the cloud provider\'s problem or entirely yours — it is shared between both parties. This concept, called the shared responsibility model, is fundamental to understanding cloud security. Misunderstanding it is one of the most common reasons organizations experience cloud data breaches.</p>

<h3>What the Cloud Provider Secures</h3>
<p>Cloud providers like AWS, Microsoft Azure, and Google Cloud Platform are responsible for securing the underlying infrastructure — the physical data centers, servers, networking equipment, and the hypervisor layer that hosts virtual machines. They handle physical security, power, cooling, and the base-level software that their services run on. Major cloud providers invest billions in security and maintain certifications like SOC 2, ISO 27001, and FedRAMP. Their infrastructure is generally far more secure than what most organizations could build on their own.</p>

<h3>What Your Organization Secures</h3>
<p>Your organization is responsible for everything you put into the cloud and how you configure it. This includes:</p>
<ul>
<li><strong>Data:</strong> Classifying, encrypting, and managing the data you store in cloud services</li>
<li><strong>Access management:</strong> Controlling who can access your cloud resources and with what permissions</li>
<li><strong>Configuration:</strong> Setting up security groups, network rules, encryption settings, and logging correctly</li>
<li><strong>Application security:</strong> Securing the applications you build and deploy on cloud infrastructure</li>
<li><strong>Operating systems:</strong> Patching and maintaining any virtual machines you manage</li>
</ul>

<h3>The Configuration Gap</h3>
<p>The majority of cloud data breaches are caused not by flaws in the cloud platform itself but by customer misconfigurations. A storage bucket left publicly accessible, an overly permissive IAM policy, or an unencrypted database endpoint — these are all customer-side configuration errors that expose data despite the provider\'s robust infrastructure security. Understanding that the security of your data in the cloud depends primarily on how you configure it is the first step toward protecting it.</p>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Critical point:</strong> "We use AWS/Azure/GCP" is not a security strategy. The cloud provider secures the building — you secure everything you put inside it and how you lock the doors.
</div>',
                    ],
                    [
                        'title' => 'Common Cloud Security Risks',
                        'slug' => 'common-cloud-security-risks',
                        'duration_minutes' => 10,
                        'content' => '<h3>Common Cloud Security Risks</h3>
<p>Cloud environments introduce specific security risks that differ from traditional on-premises infrastructure. Understanding these risks helps you avoid the mistakes that lead to the most common and damaging cloud data breaches.</p>

<h3>Misconfigured Storage</h3>
<p>Cloud storage services — S3 buckets on AWS, Blob Storage on Azure, Cloud Storage on GCP — are a frequent source of data exposure. When these services are configured with public access enabled, anyone on the internet can read or download the contents. Researchers routinely discover publicly exposed storage containing customer databases, backup files, credentials, and intellectual property. Many of the largest data breaches in recent years have traced back to a single misconfigured storage bucket that was never intended to be publicly accessible.</p>

<h3>Excessive Permissions</h3>
<p>Cloud identity and access management (IAM) systems are powerful but complex. Granting users or service accounts broader permissions than they need — the opposite of least privilege — creates risk. A developer account with administrator access to all production databases, or a service account with full read/write access when it only needs read, expands the potential damage from any single compromised credential. Permission creep happens gradually as people request access for projects and never have it revoked.</p>

<h3>Shadow IT and Unsanctioned Cloud Services</h3>
<p>Employees frequently adopt cloud services without IT approval — a marketing team using an unapproved survey tool, a developer spinning up a personal AWS account for testing, or a team sharing files through a consumer-grade cloud storage service. These unsanctioned services operate outside the organization\'s security policies, DLP controls, and compliance monitoring, creating blind spots where sensitive data can be stored or transmitted without oversight.</p>

<h3>Inadequate Encryption</h3>
<p>Data should be encrypted both at rest (while stored) and in transit (while being transmitted). Many cloud services offer encryption options but do not enable them by default, or offer basic encryption that may not meet regulatory requirements. Customer-managed encryption keys provide more control but add operational complexity. The gap between available encryption features and actually configured encryption is a common vulnerability.</p>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Most cloud breaches are not sophisticated attacks.</strong> They are the result of simple misconfigurations — a public storage bucket, an overly permissive policy, or encryption that was available but never turned on.
</div>',
                    ],
                    [
                        'title' => 'Securing Your Cloud Data',
                        'slug' => 'securing-your-cloud-data',
                        'duration_minutes' => 10,
                        'content' => '<h3>Securing Your Cloud Data</h3>
<p>Protecting data in the cloud requires deliberate action at every level — from how you configure cloud services to how you handle data in your daily work. These best practices apply whether you are a cloud administrator or an everyday user of cloud-based applications.</p>

<h3>For Everyone</h3>
<ul>
<li><strong>Use only approved cloud services:</strong> Store organizational data only in services that IT has vetted and approved. If you need a tool that is not approved, request it through the proper channels rather than signing up independently</li>
<li><strong>Review sharing settings:</strong> Before sharing a file or folder in cloud storage, check the sharing permissions. Use specific-person sharing rather than "anyone with the link" whenever possible. Periodically review who has access to your shared files and remove access that is no longer needed</li>
<li><strong>Enable MFA:</strong> Protect your cloud accounts with multi-factor authentication. A compromised cloud account password without MFA gives an attacker access to everything in that account</li>
<li><strong>Be careful with sync:</strong> Cloud sync tools that automatically mirror files between your computer and the cloud can inadvertently upload sensitive data. Be mindful of what folders are synced and what you save in them</li>
</ul>

<h3>For Cloud Administrators</h3>
<ul>
<li><strong>Enable encryption everywhere:</strong> Turn on server-side encryption for all storage services. Use TLS for all data in transit. Consider customer-managed keys for the most sensitive data</li>
<li><strong>Enforce least privilege:</strong> Regularly audit IAM policies and remove unnecessary permissions. Use groups and roles rather than individual permissions for easier management</li>
<li><strong>Enable logging and monitoring:</strong> Turn on cloud audit logging (CloudTrail, Activity Log, Cloud Audit Logs) for all services and send logs to a centralized monitoring platform</li>
<li><strong>Use cloud security posture management (CSPM):</strong> Deploy tools that continuously scan your cloud configuration for misconfigurations, compliance violations, and security risks</li>
<li><strong>Block public access by default:</strong> Configure organization-level policies that prevent storage services from being made publicly accessible unless explicitly exempted</li>
</ul>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Simple rule:</strong> Treat cloud storage like a filing cabinet — lock it, control who has a key, and periodically check who still has access. The cloud makes sharing easy, but that ease cuts both ways.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Cloud Data Security Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'In the shared responsibility model, who is responsible for configuring access controls on cloud resources?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The customer is responsible for configuring access controls, encryption, and security settings on their cloud resources — the provider secures only the underlying infrastructure.',
                            'answers' => [
                                ['answer' => 'The cloud provider handles all access control configuration', 'is_correct' => false],
                                ['answer' => 'The customer is responsible for configuring access controls on their cloud resources', 'is_correct' => true],
                                ['answer' => 'A third-party auditor manages all access controls', 'is_correct' => false],
                                ['answer' => 'Access controls are automatically configured by AI', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the most common cause of cloud data breaches?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Customer misconfigurations — such as publicly accessible storage buckets and overly permissive permissions — are the leading cause of cloud data breaches.',
                            'answers' => [
                                ['answer' => 'Sophisticated zero-day attacks against cloud infrastructure', 'is_correct' => false],
                                ['answer' => 'Physical break-ins at cloud data centers', 'is_correct' => false],
                                ['answer' => 'Customer misconfigurations like publicly accessible storage and excessive permissions', 'is_correct' => true],
                                ['answer' => 'Cloud providers selling customer data to third parties', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is "shadow IT" in the context of cloud security?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Shadow IT refers to cloud services and tools adopted by employees without IT department approval, operating outside the organization\'s security controls.',
                            'answers' => [
                                ['answer' => 'A type of malware that hides in cloud services', 'is_correct' => false],
                                ['answer' => 'Cloud services and tools used by employees without IT approval, outside security controls', 'is_correct' => true],
                                ['answer' => 'The dark web version of cloud computing', 'is_correct' => false],
                                ['answer' => 'Backup copies of data stored in secondary data centers', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which sharing setting should you prefer when sharing files in cloud storage?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Sharing with specific named people provides the most control and prevents unintended access from anyone who happens to obtain the link.',
                            'answers' => [
                                ['answer' => 'Anyone with the link can edit — it is the most convenient', 'is_correct' => false],
                                ['answer' => 'Anyone in the organization can view', 'is_correct' => false],
                                ['answer' => 'Share with specific named people whenever possible', 'is_correct' => true],
                                ['answer' => 'Public — so everyone can find it easily', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should organizations block public access to cloud storage by default?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Blocking public access by default prevents accidental exposure of sensitive data from misconfigured storage, which is the most common cause of cloud data breaches.',
                            'answers' => [
                                ['answer' => 'To reduce cloud storage costs by limiting access', 'is_correct' => false],
                                ['answer' => 'To improve cloud performance by reducing traffic', 'is_correct' => false],
                                ['answer' => 'To prevent accidental data exposure from misconfigured storage — the most common cause of cloud breaches', 'is_correct' => true],
                                ['answer' => 'Because cloud providers require it for compliance certification', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 22: Fileless Malware & Advanced Threats ──
            [
                'title' => 'Fileless Malware & Advanced Threats',
                'slug' => 'fileless-malware-advanced-threats',
                'description' => 'Learn about advanced persistent threats and fileless malware that evade traditional detection by operating entirely in memory.',
                'objectives' => [
                    'Explain how fileless malware operates differently from traditional malware',
                    'Identify common fileless attack techniques including living-off-the-land',
                    'Understand how advanced persistent threats (APTs) maintain long-term access',
                    'Recognize behavioral indicators of advanced threats on endpoints and networks',
                ],
                'category' => 'Malware & Ransomware',
                'difficulty' => 'advanced',
                'duration_minutes' => 35,
                'passing_score' => 75,
                'sort_order' => 22,
                'lessons' => [
                    [
                        'title' => 'Understanding Fileless Malware',
                        'slug' => 'understanding-fileless-malware',
                        'duration_minutes' => 12,
                        'content' => '<h3>Understanding Fileless Malware</h3>
<p>Traditional malware operates by writing malicious files to disk — executables, scripts, or libraries that antivirus software can scan and detect. Fileless malware takes a fundamentally different approach: it operates entirely in memory, hijacking legitimate system tools and processes to carry out malicious actions without ever writing a detectable file to the hard drive. This makes it significantly harder for traditional antivirus solutions to detect.</p>

<h3>How Fileless Malware Works</h3>
<p>Instead of dropping a malicious executable onto a system, fileless malware typically begins with a delivery mechanism — a phishing email, a malicious website, or an exploit targeting a software vulnerability. Once the initial foothold is established, the attack leverages tools already present on the operating system to carry out its objectives. On Windows systems, this often means using PowerShell, Windows Management Instrumentation (WMI), the .NET framework, or Windows Script Host to execute malicious commands directly in memory.</p>

<h3>Living Off the Land (LOtL)</h3>
<p>"Living off the land" describes the technique of using legitimate, pre-installed system tools for malicious purposes. Because these tools are trusted by the operating system and by security software, their execution does not typically raise alarms. Common LOtL binaries (sometimes called LOLBins) include:</p>
<ul>
<li><strong>PowerShell:</strong> Can download and execute code from remote servers, manipulate system settings, and access virtually any Windows component — all without creating a file on disk</li>
<li><strong>WMI (Windows Management Instrumentation):</strong> Used for system management, but can also execute code, create persistent scheduled tasks, and move laterally across networks</li>
<li><strong>Certutil:</strong> A legitimate certificate management tool that can also download files from the internet, encode/decode data, and bypass application controls</li>
<li><strong>Mshta:</strong> Runs Microsoft HTML Applications, which can execute JavaScript or VBScript code</li>
</ul>

<h3>Registry and Memory Persistence</h3>
<p>Although fileless malware avoids writing traditional executable files, it still needs to survive system reboots. Attackers achieve persistence by storing encoded malicious scripts in Windows Registry keys, WMI event subscriptions, or scheduled tasks. On each boot, the operating system reads these entries and executes the embedded code through legitimate tools, re-establishing the malware\'s presence in memory without ever creating a scannable file.</p>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Why this matters:</strong> If your organization relies solely on traditional antivirus that scans files on disk, fileless malware can operate undetected. Detection requires behavioral analysis and monitoring of system tool usage patterns, not just file scanning.
</div>',
                    ],
                    [
                        'title' => 'Advanced Persistent Threats',
                        'slug' => 'advanced-persistent-threats',
                        'duration_minutes' => 12,
                        'content' => '<h3>Advanced Persistent Threats (APTs)</h3>
<p>An Advanced Persistent Threat is not a single piece of malware but a sustained, coordinated cyber operation conducted by well-resourced threat actors — typically nation-state groups, organized crime syndicates, or sophisticated criminal enterprises. APTs differ from opportunistic attacks in their patience, sophistication, and objectives. Where a typical cybercriminal wants quick financial gain, an APT operator may spend months or years inside a network, silently gathering intelligence, stealing intellectual property, or positioning for future disruption.</p>

<h3>The APT Lifecycle</h3>
<ul>
<li><strong>Reconnaissance:</strong> Extensive research on the target organization — employees, systems, technologies, business relationships, and vulnerabilities — often lasting weeks or months before any technical attack begins</li>
<li><strong>Initial compromise:</strong> Gaining entry through spear phishing, zero-day exploits, supply chain compromise, or exploiting internet-facing systems. The initial foothold is typically minimal — just enough to establish a communication channel</li>
<li><strong>Establish persistence:</strong> Installing backdoors, creating additional access paths, and ensuring the compromise survives reboots, password changes, and system updates. Fileless techniques are commonly used here</li>
<li><strong>Lateral movement:</strong> Moving from the initial compromised system to other systems across the network, escalating privileges, and accessing increasingly sensitive data. Attackers use legitimate credentials and tools to blend in with normal network activity</li>
<li><strong>Data collection and exfiltration:</strong> Identifying and extracting the target data — intellectual property, strategic plans, communications, credentials — often slowly and in small increments to avoid triggering volume-based alerts</li>
<li><strong>Maintain access:</strong> Even after achieving their objective, APT actors often maintain dormant access points for future operations, sometimes remaining undetected for years</li>
</ul>

<h3>Notable APT Characteristics</h3>
<p>APT groups are known for custom tooling built specifically for their target, zero-day exploits that are unknown to vendors and have no patches, and operational security that makes attribution difficult. They operate on timescales of months to years, not hours to days. Their targets are selected strategically — defense contractors, energy infrastructure, pharmaceutical companies, government agencies, and technology firms with valuable intellectual property.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Perspective:</strong> Most organizations will not face a dedicated APT. However, the techniques APTs pioneered — fileless malware, living off the land, credential theft — have trickled down to common cybercriminals. Understanding APT methods helps you defend against the broader threat landscape.
</div>',
                    ],
                    [
                        'title' => 'Detection and Defense Strategies',
                        'slug' => 'advanced-threat-detection-defense',
                        'duration_minutes' => 11,
                        'content' => '<h3>Detection and Defense Strategies</h3>
<p>Defending against fileless malware and advanced threats requires moving beyond traditional signature-based detection toward behavioral analysis, continuous monitoring, and defense in depth. No single tool or technique is sufficient — effective defense combines multiple layers that together make attacks harder to execute and easier to detect.</p>

<h3>Behavioral Detection</h3>
<p>Since fileless malware uses legitimate tools, detection must focus on how those tools are used rather than what files exist on disk. Endpoint Detection and Response (EDR) solutions monitor process behavior in real time, looking for anomalous patterns such as:</p>
<ul>
<li>PowerShell executing encoded or obfuscated commands</li>
<li>Unusual parent-child process relationships (e.g., Microsoft Word spawning PowerShell)</li>
<li>System tools making network connections to external addresses</li>
<li>Credential access tools being invoked on workstations where they normally are not used</li>
<li>WMI or scheduled tasks being created by processes that do not normally create them</li>
</ul>

<h3>Network Monitoring</h3>
<p>Even fileless malware must communicate with its command-and-control (C2) infrastructure. Network monitoring can detect unusual DNS queries, connections to known malicious IP addresses, encrypted traffic to unexpected destinations, and data exfiltration patterns. Network Detection and Response (NDR) tools analyze traffic metadata and behavior to identify threats that endpoint tools might miss.</p>

<h3>Hardening and Reduction</h3>
<ul>
<li><strong>Restrict scripting tools:</strong> Use application control policies (AppLocker, WDAC) to limit who can run PowerShell, WMI, and other commonly abused tools. Constrained Language Mode for PowerShell limits its capabilities on workstations where full scripting is not needed</li>
<li><strong>Enable script logging:</strong> Turn on PowerShell script block logging, module logging, and transcription. These create a record of every PowerShell command executed, making detection and investigation far easier</li>
<li><strong>Patch promptly:</strong> Many initial compromises exploit known vulnerabilities. Rapid patching closes the windows attackers rely on</li>
<li><strong>Segment your network:</strong> Limit lateral movement by segmenting your network so that compromise of one system does not automatically provide access to everything</li>
</ul>

<h3>What You Can Do as an Individual</h3>
<p>While much of advanced threat defense is handled by security teams and automated tools, individuals play a critical role. Report any unusual system behavior — unexpected slowdowns, strange pop-ups, programs launching on their own, or unfamiliar processes in Task Manager. Keep your systems updated, do not disable security tools, and be especially cautious with documents and links from unfamiliar sources. Your vigilance is the human detection layer that complements technical controls.</p>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Defense in depth:</strong> No single security control stops every attack. Layering prevention (patching, hardening), detection (EDR, NDR, logging), and response (incident plans, trained teams) creates a resilient defense that forces attackers to overcome multiple barriers.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Fileless Malware & Advanced Threats Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What makes fileless malware difficult for traditional antivirus to detect?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Fileless malware operates entirely in memory using legitimate system tools, so there are no malicious files on disk for traditional antivirus to scan.',
                            'answers' => [
                                ['answer' => 'It encrypts itself with unbreakable encryption', 'is_correct' => false],
                                ['answer' => 'It operates in memory using legitimate system tools, leaving no malicious files on disk to scan', 'is_correct' => true],
                                ['answer' => 'It deletes itself after each execution', 'is_correct' => false],
                                ['answer' => 'It disguises itself as an antivirus update', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does "living off the land" mean in cybersecurity?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Living off the land means using legitimate, pre-installed system tools like PowerShell and WMI for malicious purposes, avoiding the need to introduce detectable malware.',
                            'answers' => [
                                ['answer' => 'Attackers use only physical access to compromise systems', 'is_correct' => false],
                                ['answer' => 'Attackers use legitimate, pre-installed system tools for malicious purposes', 'is_correct' => true],
                                ['answer' => 'Attackers operate from rural locations to avoid detection', 'is_correct' => false],
                                ['answer' => 'Attackers only steal data that is publicly available', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What distinguishes an Advanced Persistent Threat (APT) from a typical cyberattack?',
                            'type' => 'multiple_choice',
                            'explanation' => 'APTs are sustained, coordinated operations by well-resourced actors (often nation-states) that operate over months or years with strategic objectives.',
                            'answers' => [
                                ['answer' => 'APTs always use zero-day exploits exclusively', 'is_correct' => false],
                                ['answer' => 'APTs are sustained operations by well-resourced actors operating over months or years with strategic objectives', 'is_correct' => true],
                                ['answer' => 'APTs only target government organizations', 'is_correct' => false],
                                ['answer' => 'APTs use more malware files than typical attacks', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which detection approach is most effective against fileless malware?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Behavioral analysis monitors how system tools are used — detecting anomalous patterns like unusual PowerShell commands or unexpected process relationships.',
                            'answers' => [
                                ['answer' => 'Scanning all files on disk with updated antivirus signatures', 'is_correct' => false],
                                ['answer' => 'Blocking all email attachments', 'is_correct' => false],
                                ['answer' => 'Behavioral analysis that monitors how system tools are used and detects anomalous patterns', 'is_correct' => true],
                                ['answer' => 'Requiring all users to change passwords weekly', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why do APT operators often maintain access to a network even after achieving their primary objective?',
                            'type' => 'multiple_choice',
                            'explanation' => 'APT actors maintain dormant access points for potential future operations — collecting additional intelligence, responding to new objectives, or positioning for disruption.',
                            'answers' => [
                                ['answer' => 'They forget to clean up after themselves', 'is_correct' => false],
                                ['answer' => 'They need to continuously download malware updates', 'is_correct' => false],
                                ['answer' => 'They maintain dormant access for future operations, additional intelligence gathering, or potential disruption', 'is_correct' => true],
                                ['answer' => 'They are legally required to maintain access for investigation purposes', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 23: IoT & Smart Device Security ──
            [
                'title' => 'IoT & Smart Device Security',
                'slug' => 'iot-smart-device-security',
                'description' => 'Understand the security risks of Internet of Things devices and learn how to secure smart devices in both workplace and home environments.',
                'objectives' => [
                    'Identify common IoT security vulnerabilities and attack vectors',
                    'Assess the risks that IoT devices introduce to organizational networks',
                    'Apply security best practices for deploying and managing IoT devices',
                    'Secure personal smart devices that connect to work networks or handle work data',
                ],
                'category' => 'Mobile & Remote Work Security',
                'difficulty' => 'intermediate',
                'duration_minutes' => 25,
                'passing_score' => 70,
                'sort_order' => 23,
                'lessons' => [
                    [
                        'title' => 'The IoT Security Landscape',
                        'slug' => 'iot-security-landscape',
                        'duration_minutes' => 8,
                        'content' => '<h3>The IoT Security Landscape</h3>
<p>The Internet of Things (IoT) encompasses any device that connects to a network and communicates data — far beyond traditional computers and phones. Smart thermostats, security cameras, printers, conference room displays, building access systems, industrial sensors, medical devices, and even smart light bulbs all fall under the IoT umbrella. These devices are increasingly present in workplaces, creating new entry points that attackers can exploit to access corporate networks.</p>

<h3>Why IoT Devices Are Uniquely Vulnerable</h3>
<ul>
<li><strong>Minimal security design:</strong> Many IoT devices are built for functionality and cost efficiency, with security as an afterthought. They may lack the processing power for strong encryption or the memory for robust security features</li>
<li><strong>Default credentials:</strong> IoT devices frequently ship with default usernames and passwords (like "admin/admin" or "admin/password") that are publicly documented and rarely changed by users</li>
<li><strong>Infrequent updates:</strong> Unlike computers and phones that receive regular security patches, many IoT devices receive updates rarely or not at all. Manufacturers may stop supporting devices within a few years, leaving known vulnerabilities permanently unpatched</li>
<li><strong>Long lifespans:</strong> IoT devices like security cameras, HVAC controllers, and industrial sensors may operate for 10-15 years, far beyond the period during which the manufacturer provides security updates</li>
<li><strong>Network visibility:</strong> Organizations often lack complete inventories of IoT devices on their networks. A device that nobody knows about cannot be monitored or secured</li>
</ul>

<h3>IoT in the Workplace</h3>
<p>Modern offices contain dozens of IoT devices that many people do not think of as security risks. Smart TVs in conference rooms run full operating systems that can be compromised. Network printers store copies of printed documents and have network interfaces that can be exploited. Badge readers and security cameras, if compromised, can reveal physical security information. Even smart coffee machines and refrigerators connected to the corporate network provide potential entry points for attackers.</p>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Startling fact:</strong> The 2017 breach of a North American casino\'s high-roller database was achieved through a compromised smart thermometer in a lobby fish tank connected to the casino\'s network. Any connected device is a potential entry point.
</div>',
                    ],
                    [
                        'title' => 'Common IoT Attack Vectors',
                        'slug' => 'common-iot-attack-vectors',
                        'duration_minutes' => 9,
                        'content' => '<h3>Common IoT Attack Vectors</h3>
<p>Attackers exploit IoT devices through several well-established techniques. Understanding these attack vectors helps you evaluate the risk that any connected device introduces to your environment.</p>

<h3>Default and Weak Credentials</h3>
<p>The most common IoT attack vector is simply logging into devices using their default credentials. The Mirai botnet, one of the most damaging IoT attacks in history, scanned the internet for devices using default factory credentials and recruited them into a massive botnet. It ultimately launched distributed denial-of-service attacks that took down major websites including Twitter, Netflix, and Reddit. The entire attack relied on the simple fact that millions of IoT device owners never changed their default passwords.</p>

<h3>Unencrypted Communications</h3>
<p>Many IoT devices transmit data without encryption, meaning anyone on the same network can intercept their communications. A smart security camera sending unencrypted video, a badge reader transmitting access codes in plain text, or a medical device reporting patient vitals without encryption all create opportunities for eavesdropping and data theft. Attackers can also inject false data into unencrypted communication channels, potentially causing physical consequences in industrial or medical settings.</p>

<h3>Firmware Vulnerabilities</h3>
<p>IoT firmware often contains known vulnerabilities in its underlying components — outdated operating system kernels, insecure web servers for the management interface, or hardcoded cryptographic keys. Since firmware updates are rare, these vulnerabilities persist long after they are publicly known. Attackers use tools that scan for specific firmware versions and automatically apply known exploits, making the attack scalable across millions of identical devices worldwide.</p>

<h3>Network Pivoting</h3>
<p>Compromised IoT devices are often used not as the final target but as a stepping stone to reach more valuable systems on the same network. An attacker who compromises a smart printer on the corporate network can use it to scan internal systems, intercept network traffic, and move laterally toward servers, databases, and user workstations that are protected from direct external access but trust internal network traffic.</p>

<h3>Supply Chain Attacks</h3>
<p>Some IoT devices have been found to ship with pre-installed malware or backdoors inserted during manufacturing. Counterfeit devices sold through unauthorized channels may look identical to legitimate products but contain compromised firmware. Even legitimate devices may include third-party software components with unknown vulnerabilities.</p>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>The scale of the problem:</strong> There are an estimated 15 billion IoT devices in operation today, projected to exceed 30 billion by 2030. Each one is a potential entry point, and the majority have minimal security built in.
</div>',
                    ],
                    [
                        'title' => 'Securing IoT Devices',
                        'slug' => 'securing-iot-devices',
                        'duration_minutes' => 8,
                        'content' => '<h3>Securing IoT Devices</h3>
<p>While IoT devices present significant security challenges, practical steps can dramatically reduce the risk they introduce. These practices apply to both organizational deployments and personal smart devices, especially those that connect to networks where work data is present.</p>

<h3>Essential Security Steps</h3>
<ul>
<li><strong>Change default credentials immediately:</strong> The single most impactful action is changing default usernames and passwords on every IoT device before connecting it to any network. Use strong, unique passwords for each device</li>
<li><strong>Update firmware regularly:</strong> Check for and apply firmware updates on a regular schedule. Subscribe to the manufacturer\'s security advisories. Replace devices that no longer receive updates from the manufacturer</li>
<li><strong>Network segmentation:</strong> Place IoT devices on a separate network segment (VLAN) that is isolated from your primary corporate or home network. This prevents a compromised IoT device from directly accessing computers, servers, and sensitive data</li>
<li><strong>Disable unnecessary features:</strong> Turn off services you do not use — remote management, UPnP, telnet, and unused ports. Every enabled service is a potential attack surface</li>
<li><strong>Inventory and monitor:</strong> Maintain a list of every IoT device on your network, including its manufacturer, firmware version, and purpose. Monitor network traffic from IoT devices for unusual patterns</li>
</ul>

<h3>For Home and Remote Workers</h3>
<ul>
<li><strong>Separate your networks:</strong> Use your router\'s guest network or VLAN feature to keep smart home devices on a different network segment from your work computer</li>
<li><strong>Secure your router:</strong> Your home router is the gateway to every connected device. Change its default password, update its firmware, disable remote management, and use WPA3 encryption if available</li>
<li><strong>Be selective:</strong> Not everything needs to be "smart." Evaluate whether the convenience of a connected device is worth the security risk it introduces to your home network</li>
<li><strong>Review privacy settings:</strong> Many IoT devices collect and transmit data to the manufacturer by default. Review privacy settings and disable unnecessary data collection</li>
</ul>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Practical takeaway:</strong> You do not need to avoid IoT devices entirely. You need to treat them as what they are — network-connected computers with minimal built-in security — and apply proportionate controls: change defaults, update regularly, isolate from sensitive systems, and monitor for anomalies.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'IoT & Smart Device Security Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the most common attack vector used against IoT devices?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Default and weak credentials are the most exploited IoT vulnerability. The Mirai botnet compromised millions of devices simply by trying factory-default passwords.',
                            'answers' => [
                                ['answer' => 'Advanced zero-day exploits targeting IoT firmware', 'is_correct' => false],
                                ['answer' => 'Default credentials that were never changed from factory settings', 'is_correct' => true],
                                ['answer' => 'Physical tampering with the device hardware', 'is_correct' => false],
                                ['answer' => 'Social engineering attacks targeting the device manufacturer', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is network segmentation important for IoT devices?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Segmentation isolates IoT devices from critical systems, so a compromised smart device cannot directly access servers, workstations, or sensitive data.',
                            'answers' => [
                                ['answer' => 'It makes IoT devices run faster by reducing network congestion', 'is_correct' => false],
                                ['answer' => 'It isolates IoT devices so a compromise cannot spread to critical systems and sensitive data', 'is_correct' => true],
                                ['answer' => 'It is required by all IoT device manufacturers as part of the warranty', 'is_correct' => false],
                                ['answer' => 'It automatically updates the firmware on all connected devices', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What makes IoT devices particularly difficult to keep secure over time?',
                            'type' => 'multiple_choice',
                            'explanation' => 'IoT devices often have long operational lifespans (10-15 years) but receive security updates for only a fraction of that time, leaving known vulnerabilities permanently unpatched.',
                            'answers' => [
                                ['answer' => 'IoT devices are too expensive to replace when vulnerabilities are found', 'is_correct' => false],
                                ['answer' => 'Their long lifespans exceed the period during which manufacturers provide security updates', 'is_correct' => true],
                                ['answer' => 'IoT devices cannot connect to the internet to download updates', 'is_correct' => false],
                                ['answer' => 'Security patches for IoT devices require government approval', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'How should remote workers protect their work computer from IoT risks at home?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Placing smart home devices on a separate network segment (e.g., guest network) isolates them from the work computer and prevents cross-contamination.',
                            'answers' => [
                                ['answer' => 'Disconnect all smart devices whenever using the work computer', 'is_correct' => false],
                                ['answer' => 'Place smart home devices on a separate network segment from the work computer', 'is_correct' => true],
                                ['answer' => 'Only use IoT devices that were purchased through the employer', 'is_correct' => false],
                                ['answer' => 'Connect all devices to the same network for easier management', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What was notable about the Mirai botnet attack?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Mirai compromised millions of IoT devices by scanning for those still using factory-default credentials, then used them for massive DDoS attacks.',
                            'answers' => [
                                ['answer' => 'It was the first malware to use artificial intelligence', 'is_correct' => false],
                                ['answer' => 'It compromised millions of IoT devices using factory-default credentials to launch massive DDoS attacks', 'is_correct' => true],
                                ['answer' => 'It only affected enterprise-grade networking equipment', 'is_correct' => false],
                                ['answer' => 'It was created by a government agency for surveillance purposes', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 24: Workplace Safety & Secure Disposal ──
            [
                'title' => 'Workplace Safety & Secure Disposal',
                'slug' => 'workplace-safety-secure-disposal',
                'description' => 'Learn proper procedures for securely disposing of documents, storage media, and electronic equipment to prevent data recovery by unauthorized parties.',
                'objectives' => [
                    'Understand why secure disposal of documents and devices is critical for data protection',
                    'Identify the correct disposal method for different types of media and equipment',
                    'Follow organizational clean desk and secure workspace policies',
                    'Recognize the risks of improper disposal and real-world consequences',
                ],
                'category' => 'Physical Security & Workplace Safety',
                'difficulty' => 'beginner',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'sort_order' => 24,
                'lessons' => [
                    [
                        'title' => 'Why Secure Disposal Matters',
                        'slug' => 'why-secure-disposal-matters',
                        'duration_minutes' => 7,
                        'content' => '<h3>Why Secure Disposal Matters</h3>
<p>Every organization generates a continuous stream of documents, storage media, and electronic equipment that eventually reaches the end of its useful life. How these items are disposed of is a critical security concern. Improper disposal has been the source of numerous data breaches, regulatory fines, and reputational damage. Simply throwing a document in the trash or donating an old laptop without wiping it can expose sensitive information to anyone who finds it.</p>

<h3>The Data Afterlife</h3>
<p>Data does not disappear when you delete a file or throw away a printout. Deleted files remain on hard drives until the disk space is overwritten by new data, which may take months or never happen. Formatted drives retain recoverable data unless specifically overwritten with secure erase tools. Printed documents in regular trash are accessible to anyone who looks. Old phones, tablets, and USB drives sold, donated, or discarded often contain recoverable personal and corporate data.</p>

<h3>Real-World Consequences</h3>
<ul>
<li><strong>Healthcare:</strong> A major hospital faced a multi-million dollar HIPAA fine after patient records were found in a public dumpster near a closed facility. The records had been placed in regular trash rather than being shredded</li>
<li><strong>Financial:</strong> A bank received regulatory sanctions after old hard drives containing customer financial data were sold at an electronics surplus auction without being wiped</li>
<li><strong>Corporate:</strong> A technology company\'s unreleased product designs were discovered by a competitor after prototype circuit boards were disposed of through regular e-waste channels without destruction</li>
<li><strong>Government:</strong> Classified documents have been recovered from recycling bins, leading to investigations and security clearance revocations</li>
</ul>

<h3>Regulatory Requirements</h3>
<p>Many regulations explicitly require secure disposal of data and media. GDPR requires organizations to ensure personal data is rendered unrecoverable when no longer needed. HIPAA mandates specific disposal procedures for protected health information. PCI-DSS requires destruction of cardholder data when it is no longer needed for business or legal reasons. Failure to comply with these disposal requirements carries the same penalties as any other violation of the regulation.</p>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>The last mile of data protection:</strong> Organizations invest heavily in securing data while it is in use, but often neglect the disposal phase. A data breach from improper disposal is just as damaging and costly as one from a hack.
</div>',
                    ],
                    [
                        'title' => 'Disposal Methods by Media Type',
                        'slug' => 'disposal-methods-by-media-type',
                        'duration_minutes' => 7,
                        'content' => '<h3>Disposal Methods by Media Type</h3>
<p>Different types of media require different disposal methods to ensure data cannot be recovered. Using the wrong method can leave data fully recoverable despite your best intentions.</p>

<h3>Paper Documents</h3>
<ul>
<li><strong>Cross-cut shredding:</strong> The standard for most confidential documents. Cross-cut shredders create small particles that are extremely difficult to reassemble, unlike strip-cut shredders whose long strips can potentially be reconstructed</li>
<li><strong>Secure shredding services:</strong> For large volumes, certified destruction services collect documents in locked bins and shred them at an industrial facility, providing certificates of destruction for compliance records</li>
<li><strong>Pulping:</strong> For the highest security requirements, documents are dissolved in water and chemicals, rendering them completely unrecoverable. This is used for classified government materials</li>
</ul>

<h3>Hard Drives and SSDs</h3>
<ul>
<li><strong>Secure erase (HDDs):</strong> Software tools overwrite every sector of a traditional hard drive with random data, multiple times. NIST SP 800-88 guidelines recommend at least one full overwrite pass for most purposes</li>
<li><strong>Cryptographic erase (SSDs):</strong> Due to how SSDs manage data internally, traditional overwriting is not reliable. Cryptographic erase destroys the encryption key that protects the drive\'s contents, rendering all data unreadable</li>
<li><strong>Degaussing:</strong> Exposing magnetic media to a strong magnetic field that scrambles the stored data. Effective for traditional hard drives and magnetic tapes but does not work on SSDs</li>
<li><strong>Physical destruction:</strong> Shredding, crushing, or drilling drives provides the highest assurance. This is required for the most sensitive data classifications</li>
</ul>

<h3>Mobile Devices and Tablets</h3>
<ul>
<li><strong>Factory reset with encryption:</strong> Enable full-device encryption before performing a factory reset. The reset destroys the encryption keys, making the data unrecoverable even if storage chips are read directly</li>
<li><strong>Remove SIM and SD cards:</strong> Always remove SIM cards and external storage cards before disposing of or recycling mobile devices. These often contain contacts, messages, and files independent of the device\'s internal storage</li>
</ul>

<h3>Removable Media</h3>
<p>USB drives, CD/DVDs, and SD cards should be physically destroyed when they have held sensitive data. USB drives can be shredded or crushed. Optical media should be shredded — simply breaking a disc may leave recoverable fragments. Secure erase tools can wipe USB drives and SD cards, but physical destruction is preferred for sensitive data since flash memory wear-leveling can leave data remnants.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>When in doubt, physically destroy it.</strong> Software-based erasure is sufficient for most situations, but physical destruction provides certainty. Your IT department or a certified destruction vendor can handle this for you.
</div>',
                    ],
                    [
                        'title' => 'Clean Desk and Secure Workspace',
                        'slug' => 'clean-desk-secure-workspace',
                        'duration_minutes' => 6,
                        'content' => '<h3>Clean Desk and Secure Workspace</h3>
<p>A clean desk policy is a workplace rule requiring employees to clear their desks of all documents, notes, and removable media at the end of each workday. While it may seem like a simple tidiness preference, clean desk policies are a recognized security control recommended by frameworks including ISO 27001 and required by many compliance standards. The goal is to prevent sensitive information from being exposed to unauthorized individuals outside of working hours.</p>

<h3>What a Clean Desk Policy Covers</h3>
<ul>
<li><strong>Documents and printouts:</strong> File them in a locked cabinet or drawer, or shred them if no longer needed. Never leave sensitive documents on your desk overnight or when you leave for meetings</li>
<li><strong>Sticky notes and whiteboards:</strong> Passwords, access codes, project details, and other information on sticky notes or whiteboards are visible to anyone walking by — visitors, cleaning staff, or unauthorized individuals</li>
<li><strong>Removable media:</strong> USB drives, external hard drives, and backup media should be stored in a locked location when not in active use</li>
<li><strong>Computer screens:</strong> Lock your workstation when stepping away (Windows+L or Ctrl+Command+Q). Set an automatic screen lock timeout of 5 minutes or less</li>
<li><strong>Mobile devices:</strong> Do not leave phones, tablets, or laptops unattended in visible locations</li>
</ul>

<h3>Printer and Shared Equipment Security</h3>
<p>Print jobs containing sensitive information should be collected immediately. Many organizations use secure print features that hold jobs on the printer until the user authenticates at the device with a badge or PIN. Fax machines and multifunction devices often store copies of transmitted documents in internal memory — be aware of this when sending sensitive materials and ensure devices are properly decommissioned.</p>

<h3>Visitor and Shared Space Awareness</h3>
<p>Be mindful of information exposure in shared spaces — conference rooms, break rooms, and open-plan offices. Erase whiteboards after meetings that discussed sensitive topics. Collect all printed materials and notes at the end of a meeting. Be aware of shoulder surfing — people intentionally or unintentionally viewing your screen in open environments. Use privacy screens on laptops when working in public or shared areas.</p>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Make it a habit:</strong> Before leaving your desk for the day, do a quick scan: documents filed or shredded, drawers locked, screen locked, removable media secured. Ten seconds of habit prevents hours of incident response.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Workplace Safety & Secure Disposal Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'Why can\'t you simply delete files to securely dispose of data on a hard drive?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Deleting a file only removes the reference to it — the actual data remains on disk until overwritten, and can be recovered with readily available tools.',
                            'answers' => [
                                ['answer' => 'Deleting only removes the reference to the file — the data remains on disk and can be recovered with available tools', 'is_correct' => true],
                                ['answer' => 'Deleting files is always sufficient for secure disposal', 'is_correct' => false],
                                ['answer' => 'Deleted files are automatically encrypted by the operating system', 'is_correct' => false],
                                ['answer' => 'Hard drives physically destroy data sectors when files are deleted', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the recommended disposal method for SSDs containing sensitive data?',
                            'type' => 'multiple_choice',
                            'explanation' => 'SSDs use wear-leveling that makes traditional overwriting unreliable. Cryptographic erase destroys the encryption key, or physical destruction provides certainty.',
                            'answers' => [
                                ['answer' => 'Standard format using the operating system\'s built-in tools', 'is_correct' => false],
                                ['answer' => 'Degaussing with a strong magnetic field', 'is_correct' => false],
                                ['answer' => 'Cryptographic erase or physical destruction, since traditional overwriting is unreliable on SSDs', 'is_correct' => true],
                                ['answer' => 'Overwriting with zeros three times using standard erase software', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do before performing a factory reset on a mobile device you are disposing of?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Enabling encryption before the reset ensures the encryption keys are destroyed, making data unrecoverable even from the storage chips.',
                            'answers' => [
                                ['answer' => 'Remove the screen protector and case', 'is_correct' => false],
                                ['answer' => 'Enable full-device encryption, then perform the factory reset', 'is_correct' => true],
                                ['answer' => 'Delete all apps one by one before resetting', 'is_correct' => false],
                                ['answer' => 'Nothing — a factory reset completely erases all data', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should whiteboards be erased after meetings discussing sensitive topics?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Information left on whiteboards is visible to anyone who enters the room afterward — cleaning staff, visitors, or employees without need-to-know.',
                            'answers' => [
                                ['answer' => 'Whiteboard markers are expensive and should not be wasted', 'is_correct' => false],
                                ['answer' => 'Old markings interfere with the next meeting\'s presentation', 'is_correct' => false],
                                ['answer' => 'Information on whiteboards is visible to anyone entering the room, including unauthorized people', 'is_correct' => true],
                                ['answer' => 'Whiteboard cleaner contains chemicals that degrade over time', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which type of paper shredder provides adequate security for confidential documents?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Cross-cut shredders create small particles that are extremely difficult to reassemble, unlike strip-cut shredders whose strips can potentially be pieced back together.',
                            'answers' => [
                                ['answer' => 'Strip-cut shredders that create long, thin strips', 'is_correct' => false],
                                ['answer' => 'Cross-cut shredders that create small particles', 'is_correct' => true],
                                ['answer' => 'Any shredder is equally effective for confidential documents', 'is_correct' => false],
                                ['answer' => 'Manual tearing is sufficient for most confidential documents', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 25: Incident Response Planning ──
            [
                'title' => 'Incident Response Planning',
                'slug' => 'incident-response-planning',
                'description' => 'Learn how to build, maintain, and execute an effective incident response plan that minimizes damage and accelerates recovery from security incidents.',
                'objectives' => [
                    'Describe the phases of an incident response lifecycle',
                    'Identify the key roles and responsibilities in an incident response team',
                    'Understand the importance of preparation, documentation, and regular testing',
                    'Apply lessons-learned practices to continuously improve incident response capabilities',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'intermediate',
                'duration_minutes' => 30,
                'passing_score' => 75,
                'sort_order' => 25,
                'lessons' => [
                    [
                        'title' => 'The Incident Response Lifecycle',
                        'slug' => 'incident-response-lifecycle',
                        'duration_minutes' => 10,
                        'content' => '<h3>The Incident Response Lifecycle</h3>
<p>Incident response is the organized approach to addressing and managing the aftermath of a security breach or cyberattack. The goal is to handle the situation in a way that limits damage, reduces recovery time and costs, and prevents recurrence. The NIST (National Institute of Standards and Technology) framework defines four phases that form a continuous cycle of improvement.</p>

<h3>Phase 1: Preparation</h3>
<p>Preparation is the most important phase because it determines how effectively you can respond when an incident occurs. This includes establishing an incident response team with defined roles, creating and maintaining an incident response plan, deploying monitoring and detection tools, conducting regular training and tabletop exercises, and maintaining an inventory of critical assets and their locations. Preparation also means having pre-established relationships with external resources — legal counsel, forensics firms, law enforcement contacts, and insurance providers — so you are not scrambling to find them during a crisis.</p>

<h3>Phase 2: Detection and Analysis</h3>
<p>This phase involves identifying that an incident has occurred, determining its scope and severity, and understanding what systems and data are affected. Detection comes from multiple sources — automated alerts from security tools, reports from employees, notifications from external parties, or anomalies noticed during routine monitoring. Analysis requires investigating the alert to confirm whether it is a true incident, assessing its potential impact, and classifying its severity to determine the appropriate response level.</p>

<h3>Phase 3: Containment, Eradication, and Recovery</h3>
<p>Once an incident is confirmed, the priority shifts to limiting the damage. Short-term containment prevents further spread — isolating affected systems, blocking malicious IP addresses, or disabling compromised accounts. Eradication removes the threat — cleaning malware, closing exploited vulnerabilities, and resetting compromised credentials. Recovery restores systems to normal operation — restoring from clean backups, rebuilding compromised systems, and monitoring for signs that the threat has returned. Each step must be carefully documented for both the investigation and any regulatory reporting requirements.</p>

<h3>Phase 4: Post-Incident Activity</h3>
<p>After an incident is resolved, a structured review examines what happened, how it was detected, how effectively the response worked, and what can be improved. This "lessons learned" process produces concrete actions — updated procedures, additional training, new detection rules, or infrastructure changes — that strengthen the organization\'s defenses against future incidents. Without this phase, the same types of incidents tend to recur.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Key insight:</strong> The lifecycle is a cycle, not a line. Lessons from post-incident reviews feed back into preparation, making each subsequent response more effective.
</div>',
                    ],
                    [
                        'title' => 'Building an Incident Response Team',
                        'slug' => 'building-incident-response-team',
                        'duration_minutes' => 10,
                        'content' => '<h3>Building an Incident Response Team</h3>
<p>An incident response team (IRT) is a cross-functional group responsible for managing security incidents from detection through resolution. The team must include both technical and non-technical roles because incidents have technical, legal, communication, and business dimensions that must be managed simultaneously.</p>

<h3>Core Team Roles</h3>
<ul>
<li><strong>Incident Commander:</strong> Leads the response effort, makes critical decisions, coordinates between workstreams, and serves as the single point of authority during an active incident. This person manages the process, not necessarily the technical investigation</li>
<li><strong>Technical Lead:</strong> Directs the technical investigation — analyzing systems, identifying the attack vector, assessing the blast radius, and guiding containment and eradication efforts</li>
<li><strong>Communications Lead:</strong> Manages all internal and external communications — employee notifications, customer disclosures, media statements, and regulatory notifications. Clear, timely communication prevents misinformation and maintains trust</li>
<li><strong>Legal Counsel:</strong> Advises on regulatory notification requirements (many jurisdictions have mandatory breach notification timelines), evidence preservation obligations, liability considerations, and law enforcement engagement</li>
<li><strong>Business Liaison:</strong> Represents affected business units, communicates operational impact, and helps prioritize recovery of business-critical systems</li>
</ul>

<h3>Extended Team Members</h3>
<p>Depending on the incident, additional members may include human resources (if an insider is involved), public relations (if media attention is expected), executive sponsors (for major incidents requiring C-level decisions), third-party forensics experts (for complex investigations), and IT operations (for system recovery and rebuilding). These roles should be identified in advance so they can be activated quickly when needed.</p>

<h3>Practical Considerations</h3>
<ul>
<li><strong>Availability:</strong> Incidents do not respect business hours. Ensure team members have alternates and that contact information is current and accessible even if primary communication systems are compromised</li>
<li><strong>Communication channels:</strong> Establish out-of-band communication methods (phone bridges, separate messaging platforms) in case the primary corporate systems are affected by the incident</li>
<li><strong>Authority:</strong> The incident response plan must clearly define what actions the team can take without additional approval — isolating systems, blocking accounts, engaging external resources — to avoid delays during critical moments</li>
<li><strong>Documentation:</strong> Designate someone to maintain a timeline and log of all actions, decisions, and findings during the incident. This documentation is essential for post-incident review, legal proceedings, and regulatory reporting</li>
</ul>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Test it before you need it:</strong> A team that has never practiced together will struggle during a real incident. Regular tabletop exercises where the team walks through simulated scenarios build familiarity, expose gaps, and develop the muscle memory needed for effective real-world response.
</div>',
                    ],
                    [
                        'title' => 'Testing and Improving Your Plan',
                        'slug' => 'testing-improving-ir-plan',
                        'duration_minutes' => 10,
                        'content' => '<h3>Testing and Improving Your Plan</h3>
<p>An incident response plan that sits on a shelf untested is little better than having no plan at all. Regular testing validates that the plan works as intended, that team members know their roles, that communication channels function, and that the plan reflects the current environment. Testing also builds the confidence and familiarity that enables calm, effective action during a real crisis.</p>

<h3>Types of Testing</h3>
<ul>
<li><strong>Tabletop exercises:</strong> The most common and accessible testing method. Team members gather (in person or virtually) and walk through a simulated scenario step by step, discussing how they would respond at each stage. A facilitator introduces new developments — "The attacker has now moved to the finance server" — and the team discusses their actions. No actual systems are affected. These exercises reveal gaps in procedures, communication, and decision-making</li>
<li><strong>Functional exercises:</strong> Team members perform their actual response tasks — running forensic tools, executing containment procedures, drafting communications — in a controlled environment. This tests both the plan and the technical capabilities of the team</li>
<li><strong>Full-scale simulations:</strong> The closest to a real incident. An attack is simulated against production or production-like systems, and the team responds as they would to a real event. These are resource-intensive but provide the most realistic assessment of readiness</li>
<li><strong>Red team exercises:</strong> An internal or external red team attempts to breach the organization using real attack techniques. The incident response team is not told in advance, providing a genuine test of detection and response capabilities</li>
</ul>

<h3>The Lessons-Learned Process</h3>
<p>After every incident — and every exercise — conduct a structured review within one to two weeks while details are fresh. The review should answer: What happened and when? How was it detected? Was the response effective? What worked well? What could be improved? The output is a concrete action plan with assigned owners and deadlines, not a vague commitment to "do better." Track these actions to completion and verify they are effective.</p>

<h3>Keeping the Plan Current</h3>
<ul>
<li>Review and update the plan at least annually, or whenever there are significant changes to infrastructure, personnel, or business operations</li>
<li>Update contact lists quarterly — people change roles, phone numbers, and email addresses more often than you think</li>
<li>Incorporate new threat intelligence — if your industry is seeing a new type of attack, add a scenario for it to your next exercise</li>
<li>Align with regulatory changes — new breach notification requirements, updated compliance standards, or changes in law enforcement cooperation procedures</li>
</ul>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>The real metric:</strong> The quality of an incident response program is not measured by the thickness of the plan document. It is measured by how quickly and effectively the team can detect, contain, and recover from an actual incident — and that comes from regular practice and continuous improvement.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Incident Response Planning Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'According to the NIST framework, what are the four phases of incident response?',
                            'type' => 'multiple_choice',
                            'explanation' => 'NIST defines four phases: Preparation, Detection and Analysis, Containment/Eradication/Recovery, and Post-Incident Activity (Lessons Learned).',
                            'answers' => [
                                ['answer' => 'Prevention, Detection, Response, and Punishment', 'is_correct' => false],
                                ['answer' => 'Preparation, Detection and Analysis, Containment/Eradication/Recovery, and Post-Incident Activity', 'is_correct' => true],
                                ['answer' => 'Identification, Negotiation, Payment, and Restoration', 'is_correct' => false],
                                ['answer' => 'Monitoring, Alerting, Blocking, and Reporting', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the primary role of the Incident Commander during a security incident?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The Incident Commander leads the overall response, makes critical decisions, and coordinates between different workstreams — managing the process rather than doing the technical investigation.',
                            'answers' => [
                                ['answer' => 'Performing the technical forensic analysis personally', 'is_correct' => false],
                                ['answer' => 'Leading the response effort, making critical decisions, and coordinating between workstreams', 'is_correct' => true],
                                ['answer' => 'Communicating with the media and public', 'is_correct' => false],
                                ['answer' => 'Restoring systems from backups', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What type of testing involves walking through a simulated scenario as a discussion exercise?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Tabletop exercises are discussion-based walkthroughs of simulated scenarios where no actual systems are affected, making them the most accessible form of IR testing.',
                            'answers' => [
                                ['answer' => 'Red team exercise', 'is_correct' => false],
                                ['answer' => 'Full-scale simulation', 'is_correct' => false],
                                ['answer' => 'Tabletop exercise', 'is_correct' => true],
                                ['answer' => 'Penetration test', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is the post-incident lessons-learned review important?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Lessons learned identify what worked and what did not, producing concrete improvements that prevent recurrence and strengthen future responses.',
                            'answers' => [
                                ['answer' => 'It is only needed for regulatory compliance documentation', 'is_correct' => false],
                                ['answer' => 'It determines which employee is at fault for the incident', 'is_correct' => false],
                                ['answer' => 'It identifies improvements that prevent recurrence and strengthen future responses', 'is_correct' => true],
                                ['answer' => 'It is optional and only recommended for large organizations', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should incident response teams establish out-of-band communication channels?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Primary corporate communication systems (email, chat) may be compromised or unavailable during an incident, so alternative channels ensure the team can still coordinate.',
                            'answers' => [
                                ['answer' => 'To avoid using company email during work hours', 'is_correct' => false],
                                ['answer' => 'Because primary systems may be compromised or unavailable during the incident', 'is_correct' => true],
                                ['answer' => 'To prevent employees from overhearing incident discussions', 'is_correct' => false],
                                ['answer' => 'Because regulatory frameworks prohibit using corporate systems during incidents', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 26: Compliance Frameworks & Audits ──
            [
                'title' => 'Compliance Frameworks & Audits',
                'slug' => 'compliance-frameworks-audits',
                'description' => 'Understand major compliance frameworks including SOC 2, ISO 27001, and GDPR, and learn how to prepare for and participate in security audits.',
                'objectives' => [
                    'Describe the purpose and scope of major compliance frameworks (SOC 2, ISO 27001, GDPR)',
                    'Understand the audit process and what auditors evaluate',
                    'Identify your role in maintaining compliance in daily work',
                    'Prepare effectively for compliance audits through documentation and evidence collection',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'intermediate',
                'duration_minutes' => 35,
                'passing_score' => 75,
                'sort_order' => 26,
                'lessons' => [
                    [
                        'title' => 'Understanding Compliance Frameworks',
                        'slug' => 'understanding-compliance-frameworks',
                        'duration_minutes' => 12,
                        'content' => '<h3>Understanding Compliance Frameworks</h3>
<p>Compliance frameworks are structured sets of guidelines, controls, and best practices that organizations follow to meet regulatory requirements, industry standards, or customer expectations for security and data protection. They provide a systematic approach to managing information security rather than leaving it to ad hoc decisions. While compliance does not guarantee security, it establishes a baseline of controls that significantly reduce risk.</p>

<h3>SOC 2 (Service Organization Control 2)</h3>
<p>SOC 2 is an auditing standard developed by the American Institute of CPAs (AICPA) that evaluates how well a service organization manages data based on five Trust Services Criteria: Security (the foundation — are systems protected against unauthorized access?), Availability (are systems available for operation as committed?), Processing Integrity (is data processing complete, valid, and accurate?), Confidentiality (is information designated as confidential properly protected?), and Privacy (is personal information collected, used, retained, and disclosed in accordance with the organization\'s privacy notice?). SOC 2 reports are commonly requested by enterprise customers before they will trust a SaaS vendor with their data.</p>

<h3>ISO 27001</h3>
<p>ISO 27001 is an international standard for information security management systems (ISMS). It requires organizations to systematically identify information security risks, select appropriate controls to address those risks, and implement a management system to ensure controls remain effective over time. Certification requires an audit by an accredited certification body and ongoing surveillance audits to maintain the certification. ISO 27001 is recognized globally and is often required for doing business with European and international organizations.</p>

<h3>GDPR (General Data Protection Regulation)</h3>
<p>The GDPR is a European Union regulation that governs the processing of personal data of EU residents, regardless of where the processing organization is located. It establishes rights for individuals including the right to access their data, the right to have it deleted, the right to data portability, and the right to be informed about how their data is used. Organizations must demonstrate lawful bases for processing personal data, implement appropriate security measures, report breaches within 72 hours, and appoint Data Protection Officers in certain circumstances. Non-compliance can result in fines of up to 4% of global annual revenue.</p>

<h3>Other Important Frameworks</h3>
<ul>
<li><strong>HIPAA:</strong> United States regulation governing the protection of patient health information, applicable to healthcare providers, insurers, and their business associates</li>
<li><strong>PCI-DSS:</strong> Payment Card Industry standard required for any organization that stores, processes, or transmits credit card data</li>
<li><strong>NIST CSF:</strong> The National Institute of Standards and Technology Cybersecurity Framework, widely used as a voluntary guideline for improving cybersecurity posture</li>
</ul>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Why this matters to you:</strong> Compliance is not just an IT concern. Every employee who handles data, follows security procedures, or uses organizational systems plays a role in maintaining compliance. Auditors often interview regular employees, not just security staff.
</div>',
                    ],
                    [
                        'title' => 'The Audit Process',
                        'slug' => 'the-audit-process',
                        'duration_minutes' => 12,
                        'content' => '<h3>The Audit Process</h3>
<p>A compliance audit is a systematic evaluation of an organization\'s adherence to a specific framework\'s requirements. Understanding the audit process helps you prepare effectively and reduces the anxiety that audits often create. Audits are not adversarial — auditors are there to verify that controls work as intended, not to find fault with individuals.</p>

<h3>Types of Audits</h3>
<ul>
<li><strong>Internal audits:</strong> Conducted by your own organization\'s audit team or an internal compliance function. These identify gaps and prepare you for external audits. Internal audits are an opportunity to find and fix issues before an external auditor does</li>
<li><strong>External audits:</strong> Conducted by independent third-party firms. For certifications like SOC 2 and ISO 27001, external audits are required. The auditor has no relationship to the organization and provides an objective assessment</li>
<li><strong>Regulatory audits:</strong> Conducted by government regulators or industry bodies to verify compliance with mandatory regulations like GDPR, HIPAA, or PCI-DSS. These may be scheduled or triggered by complaints or incidents</li>
</ul>

<h3>What Auditors Evaluate</h3>
<p>Auditors examine three things: policies (do you have documented rules and procedures?), implementation (are those policies actually implemented in practice?), and evidence (can you prove it?). For example, if your policy says access reviews happen quarterly, the auditor will ask to see the logs of the last four access reviews, including who conducted them, what was reviewed, and what actions were taken. The gap between policy and practice is where most audit findings occur.</p>

<h3>Common Audit Evidence</h3>
<ul>
<li><strong>Policies and procedures:</strong> Written documents defining security rules, data handling procedures, incident response plans, and access control policies</li>
<li><strong>System configurations:</strong> Screenshots or exports showing that security settings are configured as the policy requires — encryption enabled, logging active, access controls in place</li>
<li><strong>Access logs:</strong> Records showing who accessed what systems, when, and whether access reviews were conducted</li>
<li><strong>Training records:</strong> Evidence that employees completed security awareness training, including completion dates and scores</li>
<li><strong>Change records:</strong> Documentation of system changes including approvals, testing, and rollback plans</li>
<li><strong>Incident records:</strong> Logs of security incidents, how they were handled, and what improvements resulted from the review</li>
</ul>

<h3>Interview Expectations</h3>
<p>Auditors frequently interview employees at all levels to verify that policies are understood and followed in practice. You may be asked questions like: "How do you handle sensitive data in your role?" or "What would you do if you suspected a security incident?" or "How do you manage access to the systems you use?" Answer honestly and specifically — describe what you actually do, not what you think the auditor wants to hear. If you do not know the answer, say so rather than guessing.</p>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Audit tip:</strong> The best audit preparation is doing things right consistently, not cramming before the auditor arrives. If you follow security procedures every day, the audit is simply a confirmation of what you already do.
</div>',
                    ],
                    [
                        'title' => 'Your Role in Compliance',
                        'slug' => 'your-role-in-compliance',
                        'duration_minutes' => 11,
                        'content' => '<h3>Your Role in Compliance</h3>
<p>Compliance is often perceived as the responsibility of the security team, the compliance department, or senior management. In reality, every employee contributes to compliance through their daily actions. The security controls that auditors evaluate only work when individual people follow the procedures those controls depend on. A perfectly written access control policy means nothing if employees share passwords or leave systems unlocked.</p>

<h3>Daily Compliance Practices</h3>
<ul>
<li><strong>Follow access control procedures:</strong> Use only the access you have been authorized for. Do not share credentials, use shared accounts when individual accounts are available, or ask colleagues to look something up using their higher-level access</li>
<li><strong>Complete required training:</strong> Security awareness training is not just a checkbox — it is an auditable control. Complete it on time, and actually engage with the material. Auditors check training completion rates and may ask about specific topics covered</li>
<li><strong>Handle data according to classification:</strong> Know your organization\'s data classification levels and handle data according to its classification. Treat restricted data with the controls required for that level, even when it seems inconvenient</li>
<li><strong>Report incidents promptly:</strong> Incident reporting is a compliance requirement in virtually every framework. If you see something suspicious, report it through the proper channels immediately — delayed reporting can itself be a compliance violation</li>
<li><strong>Document your work:</strong> When compliance-relevant processes are part of your role — access reviews, change approvals, data handling procedures — document that you followed the required steps. "If it isn\'t documented, it didn\'t happen" is a core audit principle</li>
</ul>

<h3>Preparing for Audit Interactions</h3>
<ul>
<li><strong>Know your policies:</strong> Be familiar with the security policies relevant to your role. You do not need to memorize them word for word, but you should know where to find them and understand their key requirements</li>
<li><strong>Be honest:</strong> If an auditor asks about a process and you know it is not always followed perfectly, be truthful. Auditors are experienced at detecting rehearsed answers, and honesty about challenges is more valuable than a polished but inaccurate response</li>
<li><strong>Provide evidence proactively:</strong> If asked about a process, offer to show the evidence — pull up the logs, show the documentation, demonstrate the procedure. This builds auditor confidence more than verbal descriptions alone</li>
<li><strong>Stay calm:</strong> Audits are routine business processes. A finding is not a personal failure — it is an opportunity for organizational improvement</li>
</ul>

<h3>Continuous Compliance</h3>
<p>The most effective compliance programs operate continuously rather than in annual cycles. Continuous compliance means monitoring controls in real time, addressing deviations promptly, and maintaining audit-ready documentation at all times. This approach reduces the stress and effort of audit season because you are always prepared. It also provides better actual security because controls are consistently enforced rather than hastily assembled before an audit.</p>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>The bottom line:</strong> Compliance is not a separate activity from your normal work — it is how you do your normal work. When you follow security procedures, handle data carefully, complete your training, and report issues promptly, you are maintaining compliance without even thinking about it.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Compliance Frameworks & Audits Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the maximum fine under GDPR for serious violations?',
                            'type' => 'multiple_choice',
                            'explanation' => 'GDPR allows fines of up to 4% of global annual revenue or 20 million euros, whichever is greater, for the most serious violations.',
                            'answers' => [
                                ['answer' => '1 million euros per violation', 'is_correct' => false],
                                ['answer' => 'Up to 4% of global annual revenue or 20 million euros, whichever is greater', 'is_correct' => true],
                                ['answer' => '10% of national revenue', 'is_correct' => false],
                                ['answer' => 'There are no financial penalties under GDPR', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What three things do auditors primarily evaluate?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Auditors evaluate policies (documented rules), implementation (whether policies are practiced), and evidence (proof that procedures are followed).',
                            'answers' => [
                                ['answer' => 'Revenue, headcount, and market share', 'is_correct' => false],
                                ['answer' => 'Policies, implementation, and evidence', 'is_correct' => true],
                                ['answer' => 'Hardware, software, and networking', 'is_correct' => false],
                                ['answer' => 'Hiring practices, employee satisfaction, and retention rates', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What are the five Trust Services Criteria evaluated in a SOC 2 audit?',
                            'type' => 'multiple_choice',
                            'explanation' => 'SOC 2 evaluates Security, Availability, Processing Integrity, Confidentiality, and Privacy.',
                            'answers' => [
                                ['answer' => 'Security, Availability, Processing Integrity, Confidentiality, and Privacy', 'is_correct' => true],
                                ['answer' => 'Authentication, Authorization, Accounting, Auditing, and Archiving', 'is_correct' => false],
                                ['answer' => 'People, Process, Technology, Governance, and Risk', 'is_correct' => false],
                                ['answer' => 'Identify, Protect, Detect, Respond, and Recover', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'During an audit interview, what should you do if you do not know the answer to a question?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Honesty is essential in audit interviews. Say you do not know rather than guessing, and offer to find the answer or connect the auditor with someone who knows.',
                            'answers' => [
                                ['answer' => 'Give the best answer you can think of to avoid looking unprepared', 'is_correct' => false],
                                ['answer' => 'Redirect the auditor to a different topic you know better', 'is_correct' => false],
                                ['answer' => 'Say you do not know and offer to find the answer or connect them with someone who does', 'is_correct' => true],
                                ['answer' => 'Refer them to the written policy without further explanation', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is continuous compliance more effective than annual compliance efforts?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Continuous compliance ensures controls are consistently enforced and documentation is always current, providing both better security and reduced audit preparation stress.',
                            'answers' => [
                                ['answer' => 'Because regulatory audits only happen continuously, never annually', 'is_correct' => false],
                                ['answer' => 'Because it eliminates the need for any formal audit process', 'is_correct' => false],
                                ['answer' => 'Because controls are consistently enforced and documentation stays current, providing better actual security', 'is_correct' => true],
                                ['answer' => 'Because continuous compliance is cheaper than hiring an annual auditor', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
