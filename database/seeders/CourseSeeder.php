<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
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
            // ── Module 1: Phishing Fundamentals ──
            [
                'title' => 'Phishing Fundamentals',
                'slug' => 'phishing-fundamentals',
                'description' => 'Learn to identify and defend against phishing attacks — the #1 cyber threat facing organizations today.',
                'objectives' => [
                    'Define phishing and explain why it is effective',
                    'Identify common red flags in phishing emails',
                    'Distinguish between phishing, spear phishing, and whaling',
                    'Follow the correct procedure when you suspect a phishing attempt',
                ],
                'category' => 'Phishing & Email Security',
                'difficulty' => 'beginner',
                'duration_minutes' => 25,
                'passing_score' => 70,
                'is_mandatory' => true,
                'sort_order' => 0,
                'lessons' => [
                    [
                        'title' => 'What Is Phishing?',
                        'slug' => 'what-is-phishing',
                        'duration_minutes' => 5,
                        'content' => '<h2>What Is Phishing?</h2>
<p>Phishing is a type of <strong>social engineering attack</strong> where an attacker pretends to be a trusted entity — a bank, a colleague, an IT department — to trick you into revealing sensitive information or taking a harmful action.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Key Fact:</strong> Over 90% of successful cyber attacks start with a phishing email. In 2023, phishing caused an estimated $4.9 billion in losses worldwide.
</div>

<h3>How Phishing Works</h3>
<ol>
<li><strong>Bait:</strong> The attacker crafts a convincing message that creates urgency or curiosity.</li>
<li><strong>Hook:</strong> The message contains a malicious link, attachment, or request for information.</li>
<li><strong>Catch:</strong> The victim clicks the link, opens the file, or provides credentials.</li>
<li><strong>Exploit:</strong> The attacker uses the stolen data to access systems, steal money, or launch further attacks.</li>
</ol>

<h3>Why Phishing Works</h3>
<ul>
<li><strong>Trust:</strong> We naturally trust familiar brands and authority figures.</li>
<li><strong>Urgency:</strong> Messages that demand immediate action bypass careful thinking.</li>
<li><strong>Volume:</strong> Attackers send millions of emails — even a tiny success rate is profitable.</li>
<li><strong>Sophistication:</strong> Modern phishing emails can be nearly indistinguishable from legitimate ones.</li>
</ul>',
                    ],
                    [
                        'title' => 'Types of Phishing Attacks',
                        'slug' => 'types-of-phishing',
                        'duration_minutes' => 7,
                        'content' => '<h2>Types of Phishing Attacks</h2>

<h3>1. Email Phishing (Mass Phishing)</h3>
<p>The most common form. Attackers send identical emails to thousands of recipients, impersonating well-known brands like Microsoft, Amazon, or your bank.</p>
<p><em>Example:</em> "Your account has been suspended. Click here to verify your identity."</p>

<h3>2. Spear Phishing</h3>
<p>Targeted attacks aimed at specific individuals. The attacker researches you — your name, job title, colleagues — to craft a personalized, convincing message.</p>
<p><em>Example:</em> "Hi Sarah, here\'s the budget spreadsheet you asked about in yesterday\'s meeting." (from someone impersonating your manager)</p>

<h3>3. Whaling</h3>
<p>Spear phishing that targets <strong>senior executives</strong> (the "big fish"). These attacks often involve fake legal subpoenas, tax documents, or board meeting agendas.</p>

<h3>4. Smishing (SMS Phishing)</h3>
<p>Phishing via text message. Often includes shortened URLs and urgent requests like "Your package delivery failed — click to reschedule."</p>

<h3>5. Vishing (Voice Phishing)</h3>
<p>Phone calls from attackers pretending to be IT support, the IRS, or your bank. They pressure you to provide passwords, install software, or transfer funds.</p>

<h3>6. Business Email Compromise (BEC)</h3>
<p>The attacker either hacks or impersonates an executive\'s email account and requests wire transfers, gift card purchases, or sensitive data from employees.</p>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Remember:</strong> BEC attacks cost organizations an average of $125,000 per incident. Always verify unusual financial requests through a separate communication channel.
</div>',
                    ],
                    [
                        'title' => 'Spotting Phishing Red Flags',
                        'slug' => 'spotting-red-flags',
                        'duration_minutes' => 8,
                        'content' => '<h2>How to Spot a Phishing Email</h2>
<p>Train yourself to check for these red flags <strong>every time</strong> you receive an unexpected email:</p>

<h3>1. Check the Sender Address</h3>
<p>Look at the <strong>actual email address</strong>, not just the display name. Attackers use addresses like:</p>
<ul>
<li><code>support@micros0ft.com</code> (zero instead of "o")</li>
<li><code>hr@company-secure-login.com</code> (not your company\'s domain)</li>
<li><code>ceo@gmail.com</code> (your CEO wouldn\'t use Gmail for business)</li>
</ul>

<h3>2. Look for Urgency & Threats</h3>
<p>Phrases designed to make you act without thinking:</p>
<ul>
<li>"Your account will be locked in 24 hours"</li>
<li>"Immediate action required"</li>
<li>"You will be fined if you don\'t respond"</li>
</ul>

<h3>3. Hover Over Links (Don\'t Click!)</h3>
<p>Hover your mouse over any link to see the actual URL. Watch for:</p>
<ul>
<li>Misspelled domains: <code>paypa1.com</code>, <code>arnazon.com</code></li>
<li>Extra words: <code>login-microsoft-security.com</code></li>
<li>HTTP (not HTTPS) for sensitive pages</li>
</ul>

<h3>4. Check for Poor Grammar & Formatting</h3>
<p>While modern phishing is more polished, watch for odd phrasing, strange fonts, blurry logos, or inconsistent formatting.</p>

<h3>5. Unexpected Attachments</h3>
<p>Be wary of unexpected file attachments, especially:</p>
<ul>
<li><code>.exe</code>, <code>.scr</code>, <code>.zip</code> files</li>
<li>Office documents asking you to "Enable Macros"</li>
<li>PDFs from unknown senders</li>
</ul>

<h3>6. Too Good to Be True</h3>
<p>"You\'ve won a $500 gift card!" or "You\'re getting a surprise bonus!" — if you didn\'t enter a contest, you didn\'t win one.</p>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>The SLAM Method:</strong><br>
<strong>S</strong>ender — Do you know them?<br>
<strong>L</strong>inks — Where do they really go?<br>
<strong>A</strong>ttachments — Were you expecting one?<br>
<strong>M</strong>essage — Does it create urgency or fear?
</div>',
                    ],
                    [
                        'title' => 'What to Do When You Suspect Phishing',
                        'slug' => 'what-to-do',
                        'duration_minutes' => 5,
                        'content' => '<h2>What to Do When You Suspect a Phishing Attempt</h2>

<h3>Step 1: STOP — Don\'t Click Anything</h3>
<p>If something feels off, trust your instincts. Do not click links, download attachments, or reply to the email.</p>

<h3>Step 2: REPORT — Notify Your IT/Security Team</h3>
<p>Forward the suspicious email to your organization\'s security team or use the "Report Phishing" button in your email client. Most organizations have a dedicated reporting address.</p>

<h3>Step 3: VERIFY — Use a Separate Channel</h3>
<p>If the email appears to come from a colleague or vendor, contact them through a <strong>different channel</strong> (phone call, Teams/Slack message, in-person) to confirm they sent it.</p>

<h3>Step 4: DELETE — Remove the Email</h3>
<p>Once reported, delete the email from your inbox and trash folder.</p>

<h3>If You\'ve Already Clicked</h3>
<ol>
<li><strong>Disconnect</strong> from the network (unplug Ethernet or disable WiFi)</li>
<li><strong>Report</strong> the incident to IT security immediately</li>
<li><strong>Change</strong> your passwords from a different, clean device</li>
<li><strong>Monitor</strong> your accounts for unusual activity</li>
<li><strong>Don\'t</strong> feel embarrassed — reporting quickly limits the damage</li>
</ol>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Important:</strong> You will never be punished for reporting a suspicious email, even if it turns out to be legitimate. It\'s always better to report and be wrong than to ignore and be right.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Phishing Fundamentals Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass. You have 3 attempts.',
                    'questions' => [
                        [
                            'question' => 'What percentage of successful cyber attacks begin with a phishing email?',
                            'explanation' => 'Over 90% of successful cyber attacks start with a phishing email, making it the most common attack vector.',
                            'answers' => [
                                ['text' => 'About 30%', 'correct' => false],
                                ['text' => 'About 50%', 'correct' => false],
                                ['text' => 'Over 90%', 'correct' => true],
                                ['text' => 'About 10%', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is "spear phishing"?',
                            'explanation' => 'Spear phishing is a targeted attack aimed at specific individuals, using personal details to make the message more convincing.',
                            'answers' => [
                                ['text' => 'Phishing that uses fishing-themed imagery', 'correct' => false],
                                ['text' => 'A targeted phishing attack aimed at a specific individual', 'correct' => true],
                                ['text' => 'Phishing via text messages', 'correct' => false],
                                ['text' => 'Any phishing email with an attachment', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You receive an email from "IT Support" asking you to click a link and update your password immediately. The email address is support@company-security-update.com. What should you do?',
                            'explanation' => 'The domain doesn\'t match your company\'s actual domain. This is a red flag. Always report suspicious emails and verify through official channels.',
                            'answers' => [
                                ['text' => 'Click the link and update your password since IT is asking', 'correct' => false],
                                ['text' => 'Reply to ask if the email is legitimate', 'correct' => false],
                                ['text' => 'Report it to your security team and verify through a separate channel', 'correct' => true],
                                ['text' => 'Forward it to your colleagues to warn them', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does the "S" in the SLAM method stand for?',
                            'explanation' => 'SLAM stands for Sender, Links, Attachments, Message — a quick checklist for evaluating suspicious emails.',
                            'answers' => [
                                ['text' => 'Security', 'correct' => false],
                                ['text' => 'Sender', 'correct' => true],
                                ['text' => 'Spam', 'correct' => false],
                                ['text' => 'Subject', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You accidentally clicked a link in a phishing email. What is the FIRST thing you should do?',
                            'explanation' => 'Disconnecting from the network limits the attacker\'s ability to access your system or spread to other devices.',
                            'answers' => [
                                ['text' => 'Change your password on the same device', 'correct' => false],
                                ['text' => 'Delete the email and pretend it didn\'t happen', 'correct' => false],
                                ['text' => 'Disconnect from the network and report to IT', 'correct' => true],
                                ['text' => 'Run a virus scan', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which type of phishing attack targets senior executives?',
                            'explanation' => 'Whaling targets high-level executives — the "big fish." These attacks often involve fake legal or financial documents.',
                            'answers' => [
                                ['text' => 'Smishing', 'correct' => false],
                                ['text' => 'Vishing', 'correct' => false],
                                ['text' => 'Spear phishing', 'correct' => false],
                                ['text' => 'Whaling', 'correct' => true],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 2: Social Engineering Tactics ──
            [
                'title' => 'Social Engineering Tactics',
                'slug' => 'social-engineering-tactics',
                'description' => 'Understand how attackers manipulate human psychology to bypass security measures.',
                'objectives' => [
                    'Define social engineering and its key principles',
                    'Recognize common social engineering techniques',
                    'Understand the psychology behind social engineering',
                    'Apply defensive strategies against social engineering attacks',
                ],
                'category' => 'Social Engineering',
                'difficulty' => 'beginner',
                'duration_minutes' => 30,
                'passing_score' => 70,
                'is_mandatory' => true,
                'sort_order' => 1,
                'lessons' => [
                    [
                        'title' => 'The Psychology of Social Engineering',
                        'slug' => 'psychology-of-social-engineering',
                        'duration_minutes' => 8,
                        'content' => '<h2>The Psychology of Social Engineering</h2>
<p>Social engineering exploits fundamental human traits — trust, helpfulness, fear, and curiosity — rather than technical vulnerabilities. Understanding these psychological triggers is your best defense.</p>

<h3>Cialdini\'s 6 Principles of Influence</h3>
<p>Psychologist Robert Cialdini identified six principles that social engineers exploit:</p>

<h4>1. Authority</h4>
<p>We tend to comply with requests from people in positions of power. An attacker might impersonate your CEO, a police officer, or an IT administrator.</p>
<p><em>"This is the IT Director. I need your login credentials immediately for an emergency security update."</em></p>

<h4>2. Urgency / Scarcity</h4>
<p>When we believe time is limited, we make hasty decisions. Attackers create artificial deadlines to bypass rational thinking.</p>
<p><em>"Your account will be permanently deleted in 2 hours unless you verify now."</em></p>

<h4>3. Social Proof</h4>
<p>We look to others\' behavior to guide our own. Attackers claim that others have already complied.</p>
<p><em>"Everyone in your department has already completed this form."</em></p>

<h4>4. Liking</h4>
<p>We\'re more likely to comply with people we like or who are similar to us. Attackers build rapport before making their request.</p>

<h4>5. Reciprocity</h4>
<p>When someone does something for us, we feel obligated to return the favor. Attackers may offer "help" first before asking for access.</p>

<h4>6. Commitment / Consistency</h4>
<p>Once we\'ve said yes to something small, we\'re more likely to say yes to bigger requests. Attackers start small and escalate.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Defense:</strong> Whenever you feel pressured, rushed, or flattered into giving up information or access, that emotional trigger IS the attack. Pause, verify, and report.
</div>',
                    ],
                    [
                        'title' => 'Common Social Engineering Techniques',
                        'slug' => 'common-techniques',
                        'duration_minutes' => 8,
                        'content' => '<h2>Common Social Engineering Techniques</h2>

<h3>Pretexting</h3>
<p>The attacker creates a fabricated scenario (a "pretext") to engage the victim. They might pose as an IT technician, a delivery person, or a new employee who needs help.</p>
<p><em>Example: A caller claims to be from your bank\'s fraud department and needs to "verify your account" by having you read your account number and security questions.</em></p>

<h3>Baiting</h3>
<p>Leaving malware-infected physical devices (USB drives, CDs) in public areas hoping someone will pick them up and plug them into a computer.</p>
<p><em>Example: A USB drive labeled "Employee Salary Data Q4" left in the parking lot.</em></p>

<h3>Tailgating / Piggybacking</h3>
<p>Following an authorized person through a secure door or gate without using their own credentials.</p>
<p><em>Example: "Could you hold the door? My badge isn\'t working and I\'m running late for a meeting."</em></p>

<h3>Quid Pro Quo</h3>
<p>Offering something in exchange for information or access. Often involves fake IT support calls.</p>
<p><em>Example: "Hi, I\'m calling from IT support. We detected a virus on your computer. If you give me remote access, I can fix it for free."</em></p>

<h3>Watering Hole Attacks</h3>
<p>Compromising websites that a target group frequently visits, rather than attacking them directly.</p>
<p><em>Example: Infecting a popular industry forum that employees of a target company regularly browse.</em></p>

<h3>Dumpster Diving</h3>
<p>Searching through trash for sensitive information — printed documents, sticky notes with passwords, discarded hard drives.</p>

<div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Real-World Stat:</strong> According to the Verizon Data Breach Investigations Report, 74% of breaches involve the human element — social engineering, errors, or misuse.
</div>',
                    ],
                    [
                        'title' => 'Defending Against Social Engineering',
                        'slug' => 'defending-against-social-engineering',
                        'duration_minutes' => 7,
                        'content' => '<h2>Defending Against Social Engineering</h2>

<h3>The Verification Principle</h3>
<p>The single most effective defense: <strong>always verify identity through a separate, trusted channel</strong> before sharing any sensitive information or granting access.</p>

<h3>Practical Defense Strategies</h3>

<h4>1. Question Unexpected Requests</h4>
<ul>
<li>Does this request make sense in context?</li>
<li>Would this person normally ask for this?</li>
<li>Why is this urgent?</li>
</ul>

<h4>2. Verify Identity Independently</h4>
<ul>
<li>Call back on a known, official number (not the one they gave you)</li>
<li>Contact the person through a different channel (email → phone, or vice versa)</li>
<li>Check with your manager or security team</li>
</ul>

<h4>3. Follow Established Procedures</h4>
<ul>
<li>Never bypass security procedures, even under pressure from "authority"</li>
<li>Wire transfers and data requests should follow formal approval workflows</li>
<li>Physical access requests should go through proper channels</li>
</ul>

<h4>4. Protect Physical Security</h4>
<ul>
<li>Never hold doors for people without badges</li>
<li>Shred sensitive documents</li>
<li>Lock your screen when you step away (Win+L or Cmd+Ctrl+Q)</li>
<li>Never plug in unknown USB devices</li>
</ul>

<h4>5. Limit Information Sharing</h4>
<ul>
<li>Be cautious about what you share on social media</li>
<li>Don\'t discuss sensitive work details in public</li>
<li>Be wary of "casual" questions from strangers about your work</li>
</ul>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Remember:</strong> A legitimate person or organization will never pressure you to bypass security. If they do, that\'s the clearest sign of an attack.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Social Engineering Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'Which psychological principle does an attacker exploit when they impersonate your CEO and demand immediate action?',
                            'explanation' => 'Impersonating authority figures exploits the "Authority" principle — we tend to comply with requests from people in power positions.',
                            'answers' => [
                                ['text' => 'Social Proof', 'correct' => false],
                                ['text' => 'Authority and Urgency', 'correct' => true],
                                ['text' => 'Reciprocity', 'correct' => false],
                                ['text' => 'Liking', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You find a USB drive labeled "Confidential — Salary Data" in the parking lot. What should you do?',
                            'explanation' => 'This is a classic "baiting" attack. Unknown USB drives may contain malware that executes when plugged in.',
                            'answers' => [
                                ['text' => 'Plug it into your computer to see whose it is', 'correct' => false],
                                ['text' => 'Give it to a colleague to check', 'correct' => false],
                                ['text' => 'Turn it in to your IT/security team without plugging it in', 'correct' => true],
                                ['text' => 'Throw it away', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is "pretexting"?',
                            'explanation' => 'Pretexting involves creating a fabricated scenario to gain a victim\'s trust and extract information.',
                            'answers' => [
                                ['text' => 'Sending phishing emails with fake attachments', 'correct' => false],
                                ['text' => 'Creating a fabricated scenario to trick someone into sharing information', 'correct' => true],
                                ['text' => 'Following someone through a secure door', 'correct' => false],
                                ['text' => 'Leaving infected USB drives in public areas', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the BEST defense against social engineering?',
                            'explanation' => 'Verifying the identity of the requester through a separate, trusted channel is the most effective way to defeat social engineering.',
                            'answers' => [
                                ['text' => 'Using strong passwords', 'correct' => false],
                                ['text' => 'Installing antivirus software', 'correct' => false],
                                ['text' => 'Verifying identity through a separate, trusted channel', 'correct' => true],
                                ['text' => 'Ignoring all phone calls from unknown numbers', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Someone follows you through a secure door saying their badge isn\'t working. This is an example of:',
                            'explanation' => 'Tailgating (or piggybacking) is following an authorized person through a secure access point without using your own credentials.',
                            'answers' => [
                                ['text' => 'Pretexting', 'correct' => false],
                                ['text' => 'Tailgating', 'correct' => true],
                                ['text' => 'Baiting', 'correct' => false],
                                ['text' => 'Quid Pro Quo', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 3: Password Security Best Practices ──
            [
                'title' => 'Password Security Best Practices',
                'slug' => 'password-security',
                'description' => 'Master the art of creating and managing strong passwords to protect your accounts and organization.',
                'objectives' => [
                    'Understand why passwords are a critical security control',
                    'Create strong, unique passwords',
                    'Use password managers effectively',
                    'Enable and use multi-factor authentication (MFA)',
                ],
                'category' => 'Password & Authentication',
                'difficulty' => 'beginner',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'is_mandatory' => true,
                'sort_order' => 2,
                'lessons' => [
                    [
                        'title' => 'Why Passwords Matter',
                        'slug' => 'why-passwords-matter',
                        'duration_minutes' => 5,
                        'content' => '<h2>Why Passwords Matter</h2>
<p>Passwords are the first line of defense for your digital identity. A single compromised password can give attackers access to email, financial systems, customer data, and your entire organization\'s network.</p>

<h3>How Attackers Crack Passwords</h3>

<h4>Brute Force</h4>
<p>Trying every possible combination. A 6-character lowercase password can be cracked in <strong>under 1 second</strong>. A 12-character mixed password takes <strong>centuries</strong>.</p>

<h4>Dictionary Attacks</h4>
<p>Trying common words, names, and known passwords. "password123", "qwerty", and "letmein" are all in attacker dictionaries.</p>

<h4>Credential Stuffing</h4>
<p>Using username/password pairs from previous data breaches on other sites. If you reuse passwords, one breach compromises all your accounts.</p>

<h4>Shoulder Surfing</h4>
<p>Watching you type your password. This can happen in offices, cafés, or on public transport.</p>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Alarming Stat:</strong> 81% of data breaches involve weak or stolen passwords. The most common password in the world is still "123456".
</div>',
                    ],
                    [
                        'title' => 'Creating Strong Passwords',
                        'slug' => 'creating-strong-passwords',
                        'duration_minutes' => 5,
                        'content' => '<h2>Creating Strong Passwords</h2>

<h3>The Passphrase Method (Recommended)</h3>
<p>Instead of a complex but forgettable password, use a <strong>passphrase</strong> — a string of random, unrelated words:</p>
<p style="font-family: monospace; font-size: 18px; background: #F3F4F6; padding: 12px; border-radius: 8px; text-align: center;">correct-horse-battery-staple</p>
<p>This is 28 characters long, easy to remember, and extremely difficult to crack.</p>

<h3>Password Strength Rules</h3>
<ul>
<li><strong>Length > Complexity:</strong> A 16-character passphrase beats an 8-character "P@$$w0rd!" every time</li>
<li><strong>Minimum 12 characters</strong> for any password</li>
<li><strong>Mix character types</strong> when required: uppercase, lowercase, numbers, symbols</li>
<li><strong>Never use personal info:</strong> birthdays, pet names, addresses, children\'s names</li>
<li><strong>Never reuse passwords</strong> across different accounts</li>
</ul>

<h3>What NOT to Do</h3>
<table style="width: 100%; border-collapse: collapse; margin: 16px 0;">
<tr style="background: #FEF2F2;"><th style="padding: 8px; text-align: left; border: 1px solid #E5E7EB;">Bad Password</th><th style="padding: 8px; text-align: left; border: 1px solid #E5E7EB;">Why</th></tr>
<tr><td style="padding: 8px; border: 1px solid #E5E7EB;">Password123!</td><td style="padding: 8px; border: 1px solid #E5E7EB;">In every dictionary attack list</td></tr>
<tr><td style="padding: 8px; border: 1px solid #E5E7EB;">John2024</td><td style="padding: 8px; border: 1px solid #E5E7EB;">Personal info + predictable year</td></tr>
<tr><td style="padding: 8px; border: 1px solid #E5E7EB;">Company@123</td><td style="padding: 8px; border: 1px solid #E5E7EB;">Predictable company + pattern</td></tr>
<tr><td style="padding: 8px; border: 1px solid #E5E7EB;">qwerty2024</td><td style="padding: 8px; border: 1px solid #E5E7EB;">Keyboard pattern</td></tr>
</table>',
                    ],
                    [
                        'title' => 'Password Managers & Multi-Factor Authentication',
                        'slug' => 'password-managers-mfa',
                        'duration_minutes' => 7,
                        'content' => '<h2>Password Managers</h2>
<p>A password manager stores all your passwords in an encrypted vault, protected by one strong master password. It generates unique, complex passwords for every account.</p>

<h3>Benefits</h3>
<ul>
<li><strong>One password to remember</strong> — the master password</li>
<li><strong>Unique password for every account</strong> — no reuse risk</li>
<li><strong>Auto-fill</strong> — prevents typos and shoulder surfing</li>
<li><strong>Breach alerts</strong> — notifies you if a saved password appears in a data breach</li>
</ul>

<h3>Trusted Password Managers</h3>
<p>Your organization may provide one. Common options include 1Password, Bitwarden, and LastPass. Check with your IT team for the approved tool.</p>

<h2>Multi-Factor Authentication (MFA)</h2>
<p>MFA requires <strong>two or more verification methods</strong> to prove your identity:</p>

<h3>The Three Factors</h3>
<ol>
<li><strong>Something you know</strong> — password, PIN</li>
<li><strong>Something you have</strong> — phone, security key, smart card</li>
<li><strong>Something you are</strong> — fingerprint, face scan</li>
</ol>

<h3>MFA Methods (Ranked by Security)</h3>
<ol>
<li>🥇 <strong>Hardware security keys</strong> (YubiKey) — phishing resistant</li>
<li>🥈 <strong>Authenticator apps</strong> (Google Authenticator, Microsoft Authenticator) — time-based codes</li>
<li>🥉 <strong>SMS codes</strong> — better than nothing, but vulnerable to SIM swapping</li>
</ol>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>Enable MFA everywhere:</strong> Even if an attacker steals your password, MFA blocks them at the door. It stops 99.9% of automated attacks.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Password Security Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the most important factor in password strength?',
                            'explanation' => 'Length is more important than complexity. A 16-character passphrase is far stronger than an 8-character complex password.',
                            'answers' => [
                                ['text' => 'Using special characters', 'correct' => false],
                                ['text' => 'Length (number of characters)', 'correct' => true],
                                ['text' => 'Using numbers', 'correct' => false],
                                ['text' => 'Changing it frequently', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is "credential stuffing"?',
                            'explanation' => 'Credential stuffing uses stolen username/password pairs from one breach to try logging into other services.',
                            'answers' => [
                                ['text' => 'Guessing passwords based on personal information', 'correct' => false],
                                ['text' => 'Using breached credentials from one site to attack other sites', 'correct' => true],
                                ['text' => 'Brute-forcing every possible password combination', 'correct' => false],
                                ['text' => 'Watching someone type their password', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which MFA method is the most secure?',
                            'explanation' => 'Hardware security keys (like YubiKey) are phishing-resistant and provide the strongest MFA protection.',
                            'answers' => [
                                ['text' => 'SMS text messages', 'correct' => false],
                                ['text' => 'Email codes', 'correct' => false],
                                ['text' => 'Hardware security keys (e.g., YubiKey)', 'correct' => true],
                                ['text' => 'Security questions', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What percentage of data breaches involve weak or stolen passwords?',
                            'explanation' => '81% of data breaches are linked to weak or stolen passwords, making password security a critical priority.',
                            'answers' => [
                                ['text' => '25%', 'correct' => false],
                                ['text' => '50%', 'correct' => false],
                                ['text' => '81%', 'correct' => true],
                                ['text' => '95%', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of these is the strongest password?',
                            'explanation' => 'A long passphrase of random words is far stronger than short complex passwords or patterns.',
                            'answers' => [
                                ['text' => 'P@$$w0rd!', 'correct' => false],
                                ['text' => 'Company2024!', 'correct' => false],
                                ['text' => 'purple-monkey-dishwasher-telescope', 'correct' => true],
                                ['text' => 'Abcd1234', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 4: Data Protection Essentials ──
            [
                'title' => 'Data Protection Essentials',
                'slug' => 'data-protection-essentials',
                'description' => 'Learn how to handle, store, and share sensitive data securely to protect your organization and comply with regulations.',
                'objectives' => [
                    'Classify data by sensitivity level',
                    'Apply appropriate handling procedures for each data class',
                    'Understand key data protection regulations (GDPR, PDPA)',
                    'Prevent data leakage through secure practices',
                ],
                'category' => 'Data Protection & Privacy',
                'difficulty' => 'intermediate',
                'duration_minutes' => 25,
                'passing_score' => 70,
                'sort_order' => 3,
                'lessons' => [
                    [
                        'title' => 'Data Classification',
                        'slug' => 'data-classification',
                        'duration_minutes' => 7,
                        'content' => '<h2>Data Classification</h2>
<p>Not all data is equal. Classifying data by sensitivity ensures the right level of protection is applied.</p>

<h3>Classification Levels</h3>

<div style="background: #FEF2F2; padding: 16px; border-radius: 8px; margin: 12px 0; border-left: 4px solid #EF4444;">
<h4 style="margin-top: 0;">🔴 Confidential / Restricted</h4>
<p>The most sensitive data. Unauthorized disclosure causes severe harm.</p>
<p><strong>Examples:</strong> Customer PII (names, ICs, financial data), trade secrets, encryption keys, passwords, medical records, legal documents.</p>
<p><strong>Handling:</strong> Encrypted at rest and in transit. Strict access controls. No sharing via email without encryption.</p>
</div>

<div style="background: #FEF3C7; padding: 16px; border-radius: 8px; margin: 12px 0; border-left: 4px solid #F59E0B;">
<h4 style="margin-top: 0;">🟡 Internal Use Only</h4>
<p>For internal use within the organization. Not meant for external parties.</p>
<p><strong>Examples:</strong> Internal policies, org charts, project plans, meeting notes, internal email discussions.</p>
<p><strong>Handling:</strong> Access limited to employees. Don\'t share externally without approval. Use company systems for storage.</p>
</div>

<div style="background: #ECFDF5; padding: 16px; border-radius: 8px; margin: 12px 0; border-left: 4px solid #10B981;">
<h4 style="margin-top: 0;">🟢 Public</h4>
<p>Information intended for public consumption.</p>
<p><strong>Examples:</strong> Marketing materials, press releases, published reports, public website content.</p>
<p><strong>Handling:</strong> No restrictions on sharing. Still maintain accuracy and branding standards.</p>
</div>

<h3>When In Doubt</h3>
<p>If you\'re unsure how to classify data, treat it as <strong>Internal Use Only</strong> and ask your manager or data protection officer.</p>',
                    ],
                    [
                        'title' => 'Secure Data Handling Practices',
                        'slug' => 'secure-data-handling',
                        'duration_minutes' => 8,
                        'content' => '<h2>Secure Data Handling Practices</h2>

<h3>Storing Data Securely</h3>
<ul>
<li><strong>Use approved systems:</strong> Store data only in company-approved storage (SharePoint, approved cloud, company servers) — never on personal devices or unauthorized cloud services</li>
<li><strong>Encrypt sensitive files:</strong> Use encryption for confidential data at rest</li>
<li><strong>Access controls:</strong> Only share access with people who need it ("need-to-know" principle)</li>
<li><strong>Regular backups:</strong> Ensure critical data is backed up following your company\'s backup policy</li>
</ul>

<h3>Sharing Data Safely</h3>
<ul>
<li><strong>Internal sharing:</strong> Use company platforms (Teams, SharePoint, approved file share)</li>
<li><strong>External sharing:</strong> Use encrypted channels. Password-protect files. Get approval for sharing confidential data</li>
<li><strong>Double-check recipients:</strong> Verify email addresses before sending sensitive information</li>
<li><strong>Avoid CC/BCC mistakes:</strong> Check you\'re not accidentally including unintended recipients</li>
</ul>

<h3>Data Disposal</h3>
<ul>
<li><strong>Shred physical documents</strong> containing sensitive information</li>
<li><strong>Secure-delete digital files</strong> — simply deleting or emptying the recycle bin is not enough</li>
<li><strong>Wipe devices</strong> before returning, selling, or disposing of them</li>
<li><strong>Return company data</strong> when leaving the organization</li>
</ul>

<h3>Clean Desk Policy</h3>
<p>At the end of each day:</p>
<ul>
<li>Lock away sensitive documents</li>
<li>Clear your desk of confidential papers</li>
<li>Lock your computer screen</li>
<li>Remove sticky notes with passwords (better yet, never write passwords on sticky notes!)</li>
</ul>',
                    ],
                    [
                        'title' => 'Data Protection Regulations',
                        'slug' => 'data-protection-regulations',
                        'duration_minutes' => 7,
                        'content' => '<h2>Data Protection Regulations</h2>
<p>Organizations must comply with data protection laws. Non-compliance can result in massive fines and reputational damage.</p>

<h3>Key Regulations</h3>

<h4>GDPR (General Data Protection Regulation — EU)</h4>
<ul>
<li>Applies to any organization processing EU residents\' personal data</li>
<li>Requires lawful basis for processing (consent, contract, legitimate interest)</li>
<li>Individuals have rights: access, deletion, portability, objection</li>
<li>72-hour breach notification requirement</li>
<li><strong>Fines: Up to €20M or 4% of global annual revenue</strong></li>
</ul>

<h4>PDPA (Personal Data Protection Act — Malaysia)</h4>
<ul>
<li>Governs commercial processing of personal data in Malaysia</li>
<li>7 data protection principles: General, Notice & Choice, Disclosure, Security, Retention, Data Integrity, Access</li>
<li>Requires consent for data collection and processing</li>
<li><strong>Fines: Up to RM500,000 and/or 3 years imprisonment</strong></li>
</ul>

<h3>Your Responsibilities</h3>
<ol>
<li><strong>Only collect data you need</strong> — data minimization</li>
<li><strong>Keep data accurate</strong> and up to date</li>
<li><strong>Don\'t keep data longer than necessary</strong> — follow retention schedules</li>
<li><strong>Report breaches immediately</strong> to your Data Protection Officer</li>
<li><strong>Respond to data subject requests</strong> within required timeframes</li>
</ol>

<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>A data breach is not just a technical event</strong> — sending an email to the wrong person, losing a laptop, or leaving documents on a train are all reportable data breaches.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Data Protection Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'A spreadsheet containing customer names, email addresses, and phone numbers should be classified as:',
                            'explanation' => 'Customer PII (Personally Identifiable Information) is classified as Confidential/Restricted and requires encryption and strict access controls.',
                            'answers' => [
                                ['text' => 'Public', 'correct' => false],
                                ['text' => 'Internal Use Only', 'correct' => false],
                                ['text' => 'Confidential / Restricted', 'correct' => true],
                                ['text' => 'It depends on the number of customers', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the GDPR\'s maximum fine for serious violations?',
                            'explanation' => 'GDPR fines can reach up to €20 million or 4% of global annual revenue, whichever is higher.',
                            'answers' => [
                                ['text' => '€1 million', 'correct' => false],
                                ['text' => '€10 million or 2% of revenue', 'correct' => false],
                                ['text' => '€20 million or 4% of global annual revenue', 'correct' => true],
                                ['text' => '€100,000', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You accidentally send a confidential file to the wrong email address. What should you do?',
                            'explanation' => 'Sending confidential data to the wrong recipient is a data breach that must be reported immediately, regardless of intent.',
                            'answers' => [
                                ['text' => 'Send a follow-up email asking them to delete it', 'correct' => false],
                                ['text' => 'Report it to your Data Protection Officer / IT Security immediately', 'correct' => true],
                                ['text' => 'Ignore it if it was an internal email', 'correct' => false],
                                ['text' => 'Wait to see if anyone notices', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of these is NOT a valid method for disposing of sensitive documents?',
                            'explanation' => 'Simply putting documents in the regular recycling bin exposes them to dumpster diving attacks.',
                            'answers' => [
                                ['text' => 'Cross-cut shredding', 'correct' => false],
                                ['text' => 'Putting them in the regular recycling bin', 'correct' => true],
                                ['text' => 'Professional destruction services', 'correct' => false],
                                ['text' => 'Secure shredding bins', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does "data minimization" mean?',
                            'explanation' => 'Data minimization means collecting only the personal data that is necessary for the specific purpose — a core principle of GDPR and PDPA.',
                            'answers' => [
                                ['text' => 'Using the smallest file format possible', 'correct' => false],
                                ['text' => 'Collecting only the data you actually need for the stated purpose', 'correct' => true],
                                ['text' => 'Compressing data to save storage space', 'correct' => false],
                                ['text' => 'Deleting all data after one year', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 5: Malware Awareness ──
            [
                'title' => 'Malware Awareness & Prevention',
                'slug' => 'malware-awareness',
                'description' => 'Understand the different types of malware, how they spread, and how to protect your devices and organization.',
                'objectives' => [
                    'Identify common types of malware and their effects',
                    'Recognize how malware is delivered and installed',
                    'Apply best practices to prevent malware infections',
                    'Know what to do if you suspect a malware infection',
                ],
                'category' => 'Malware & Ransomware',
                'difficulty' => 'beginner',
                'duration_minutes' => 25,
                'passing_score' => 70,
                'is_mandatory' => true,
                'sort_order' => 4,
                'lessons' => [
                    [
                        'title' => 'Types of Malware',
                        'slug' => 'types-of-malware',
                        'duration_minutes' => 8,
                        'content' => '<h2>Types of Malware</h2>
<p>Malware ("malicious software") is any program designed to damage, disrupt, or gain unauthorized access to computer systems.</p>

<h3>1. Viruses</h3>
<p>Attach to legitimate programs or files and spread when the infected file is shared or executed. They can corrupt data, slow down systems, and spread across networks.</p>

<h3>2. Ransomware</h3>
<p>Encrypts your files and demands payment (ransom) for the decryption key. Even if you pay, there\'s no guarantee your files will be restored.</p>
<div style="background: #FEF2F2; border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin: 12px 0;">
<strong>Ransomware Impact:</strong> The average ransomware payment in 2023 exceeded $1.5 million. Recovery costs (downtime, lost data, reputation) average $4.5 million.
</div>

<h3>3. Trojans</h3>
<p>Disguised as legitimate software. Once installed, they create backdoors allowing attackers remote access to your system. Named after the Trojan Horse of Greek mythology.</p>

<h3>4. Spyware</h3>
<p>Secretly monitors your activity — keystrokes, browsing history, credentials — and sends the data to the attacker. Often bundled with free software downloads.</p>

<h3>5. Worms</h3>
<p>Self-replicating malware that spreads across networks without user interaction. Can consume bandwidth, overload servers, and spread to all connected devices.</p>

<h3>6. Adware</h3>
<p>Displays unwanted advertisements. While often more annoying than dangerous, it can track browsing habits and slow down your system.</p>

<h3>7. Rootkits</h3>
<p>Designed to hide deep in your operating system, making them extremely difficult to detect and remove. They give attackers persistent, hidden access.</p>',
                    ],
                    [
                        'title' => 'How Malware Spreads',
                        'slug' => 'how-malware-spreads',
                        'duration_minutes' => 7,
                        'content' => '<h2>How Malware Spreads</h2>

<h3>Email Attachments & Links</h3>
<p>The most common delivery method. Malware is embedded in attachments (Word docs with macros, PDFs, ZIP files) or linked to from phishing emails.</p>

<h3>Malicious Websites</h3>
<p><strong>Drive-by downloads:</strong> Simply visiting a compromised website can trigger a malware download. Attackers exploit vulnerabilities in your browser or plugins.</p>
<p><strong>Fake downloads:</strong> "Your Flash Player is out of date! Click here to update." — these are almost always malware.</p>

<h3>Removable Media</h3>
<p>USB drives, external hard drives, and memory cards can carry malware that auto-executes when plugged in.</p>

<h3>Software Downloads</h3>
<p>Downloading software from unofficial sources, pirated software, or fake "free" versions of paid tools often includes bundled malware.</p>

<h3>Network Propagation</h3>
<p>Once inside a network, worms and other malware can spread to other connected devices automatically, exploiting unpatched vulnerabilities.</p>

<h3>Social Engineering</h3>
<p>Tricking users into installing malware themselves — fake IT support calls asking you to install "remote access tools," fake software updates, or apps that request excessive permissions.</p>

<div style="background: #EFF6FF; border-left: 4px solid #3B82F6; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>The Common Thread:</strong> Most malware requires some form of human action to install — clicking a link, opening an attachment, or running a program. Your awareness is the strongest defense.
</div>',
                    ],
                    [
                        'title' => 'Preventing & Responding to Malware',
                        'slug' => 'preventing-malware',
                        'duration_minutes' => 7,
                        'content' => '<h2>Preventing Malware Infections</h2>

<h3>Keep Everything Updated</h3>
<ul>
<li><strong>Operating system:</strong> Install security updates promptly — they patch known vulnerabilities</li>
<li><strong>Applications:</strong> Update browsers, Office, and all software regularly</li>
<li><strong>Antivirus:</strong> Keep definitions current and run regular scans</li>
</ul>

<h3>Practice Safe Browsing</h3>
<ul>
<li>Only download software from official sources and company-approved app stores</li>
<li>Look for HTTPS and valid certificates on websites</li>
<li>Don\'t click pop-up ads or "you\'ve won" banners</li>
<li>Be cautious with browser extensions — only install what you need from trusted sources</li>
</ul>

<h3>Email Safety</h3>
<ul>
<li>Don\'t open attachments from unknown senders</li>
<li>Never enable macros in documents from untrusted sources</li>
<li>Verify unexpected attachments with the sender through a separate channel</li>
</ul>

<h3>Physical Device Safety</h3>
<ul>
<li>Never plug in unknown USB devices</li>
<li>Lock your screen when away from your desk</li>
<li>Use full-disk encryption on laptops</li>
</ul>

<h2>If You Suspect a Malware Infection</h2>
<p>Signs include: unusually slow performance, unexpected pop-ups, programs opening or closing on their own, files disappearing or being encrypted, unusual network activity.</p>

<h3>Immediate Steps</h3>
<ol>
<li><strong>Disconnect</strong> from the network immediately (unplug Ethernet, disable WiFi)</li>
<li><strong>Don\'t turn off</strong> the computer (forensic evidence may be needed)</li>
<li><strong>Report</strong> to IT security immediately — call, don\'t email (email may be compromised)</li>
<li><strong>Don\'t try to fix it yourself</strong> — let security professionals handle it</li>
<li><strong>Note</strong> what you were doing when you noticed the issue</li>
</ol>

<div style="background: #ECFDF5; border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin: 16px 0;">
<strong>For Ransomware:</strong> Never pay the ransom. It funds criminal operations and doesn\'t guarantee data recovery. Report immediately and rely on backups.
</div>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Malware Awareness Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What type of malware encrypts your files and demands payment?',
                            'explanation' => 'Ransomware encrypts victims\' files and demands a ransom payment for the decryption key.',
                            'answers' => [
                                ['text' => 'Spyware', 'correct' => false],
                                ['text' => 'Trojan', 'correct' => false],
                                ['text' => 'Ransomware', 'correct' => true],
                                ['text' => 'Adware', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the most common method for delivering malware?',
                            'explanation' => 'Email attachments and links in phishing emails remain the most common malware delivery method.',
                            'answers' => [
                                ['text' => 'USB drives', 'correct' => false],
                                ['text' => 'Email attachments and links', 'correct' => true],
                                ['text' => 'Physical break-ins', 'correct' => false],
                                ['text' => 'Bluetooth connections', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You receive a pop-up saying "Your computer is infected! Download this antivirus now!" What should you do?',
                            'explanation' => 'Fake virus warnings are a common malware delivery tactic. Never download software from pop-ups.',
                            'answers' => [
                                ['text' => 'Click to download the suggested antivirus', 'correct' => false],
                                ['text' => 'Close the pop-up, do not click anything in it, and report it to IT', 'correct' => true],
                                ['text' => 'Run your existing antivirus scan first, then download', 'correct' => false],
                                ['text' => 'Ignore it — pop-ups are harmless', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'If you suspect your computer has malware, what is the FIRST thing you should do?',
                            'explanation' => 'Disconnecting from the network prevents malware from spreading to other devices or sending data to attackers.',
                            'answers' => [
                                ['text' => 'Turn off the computer immediately', 'correct' => false],
                                ['text' => 'Disconnect from the network', 'correct' => true],
                                ['text' => 'Try to remove the malware yourself', 'correct' => false],
                                ['text' => 'Email IT support for help', 'correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you NOT pay a ransomware ransom?',
                            'explanation' => 'Paying ransoms funds criminal operations and provides no guarantee of data recovery. Organizations should rely on backups instead.',
                            'answers' => [
                                ['text' => 'It\'s illegal in all countries', 'correct' => false],
                                ['text' => 'It funds criminals and doesn\'t guarantee data recovery', 'correct' => true],
                                ['text' => 'The ransom amount is always too high', 'correct' => false],
                                ['text' => 'Your insurance will cover the losses anyway', 'correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
