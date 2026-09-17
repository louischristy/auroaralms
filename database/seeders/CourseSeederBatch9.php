<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Database\Seeder;

class CourseSeederBatch9 extends Seeder
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
                            'answer' => $aData['answer'],
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
            // ── Course 42: Cyber Hygiene Daily Practices ──
            [
                'title' => 'Cyber Hygiene Daily Practices',
                'slug' => 'cyber-hygiene-daily-practices',
                'description' => 'Build essential daily habits that protect your devices, accounts, and data from common cyber threats through consistent software updates, backups, and account management.',
                'objectives' => [
                    'Understand why timely software updates and patches are critical to security',
                    'Implement a reliable backup strategy following the 3-2-1 rule',
                    'Use password managers effectively to maintain strong, unique credentials',
                    'Develop a daily routine of security-conscious behaviors that reduce risk',
                ],
                'category' => 'Password & Authentication',
                'difficulty' => 'beginner',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'is_mandatory' => true,
                'sort_order' => 42,
                'lessons' => [
                    [
                        'title' => 'Software Updates & Patch Management',
                        'slug' => 'software-updates-patch-management',
                        'duration_minutes' => 7,
                        'content' => '<h3>Software Updates & Patch Management</h3>
<p>Software updates are one of the most effective and straightforward defenses against cyberattacks. When vendors discover vulnerabilities in their products, they release patches that fix those security holes. Every day you delay installing an update is another day attackers can exploit the known weakness. The vast majority of successful cyberattacks exploit vulnerabilities for which patches were already available but had not been applied.</p>

<h3>Why Patches Matter</h3>
<p>When a vulnerability is discovered and publicly disclosed, attackers immediately begin scanning the internet for systems that have not yet been patched. This race between patching and exploitation is often measured in hours, not days. The WannaCry ransomware attack of 2017 exploited a Windows vulnerability for which Microsoft had released a patch two months earlier. Organizations that had applied the patch were protected; those that had not suffered devastating consequences, including the shutdown of hospitals, factories, and government agencies worldwide.</p>

<h3>What to Keep Updated</h3>
<ul>
<li><strong>Operating system:</strong> Windows, macOS, Linux, iOS, and Android all receive regular security updates. Enable automatic updates wherever possible</li>
<li><strong>Web browsers:</strong> Chrome, Firefox, Safari, and Edge are frequent targets. Modern browsers update automatically, but verify this is enabled in your settings</li>
<li><strong>Applications:</strong> Office suites, PDF readers, media players, and communication tools all need regular updates. Pay special attention to apps that handle files from external sources</li>
<li><strong>Firmware:</strong> Routers, printers, and IoT devices have firmware that also needs updating. These are often overlooked but can serve as entry points for attackers</li>
<li><strong>Plugins and extensions:</strong> Browser extensions, Java, and other plugins should be updated or removed if no longer needed</li>
</ul>

<h3>Building an Update Routine</h3>
<p>Set your devices to install updates automatically whenever the option exists. For systems where automatic updates are not practical, designate a specific time each week to check for and install pending updates. Do not dismiss or postpone update notifications repeatedly. If an update requires a restart, schedule it for the end of your workday rather than ignoring it indefinitely. Treat update notifications with the same urgency as you would a fire alarm test — it may seem routine, but it exists to protect you.</p>',
                    ],
                    [
                        'title' => 'Backup Best Practices',
                        'slug' => 'backup-best-practices',
                        'duration_minutes' => 7,
                        'content' => '<h3>Backup Best Practices</h3>
<p>Backups are your last line of defense against data loss from ransomware, hardware failure, accidental deletion, or natural disasters. A reliable backup strategy means that even in the worst-case scenario, you can recover your critical files and resume work. Without backups, a single ransomware attack or hard drive failure can permanently destroy years of work, irreplaceable photos, or critical business records.</p>

<h3>The 3-2-1 Backup Rule</h3>
<p>The gold standard for backup strategy is the 3-2-1 rule: keep at least three copies of your data, stored on two different types of media, with one copy stored offsite. For example, you might have your working files on your laptop, a backup on an external hard drive at home, and another backup in a cloud storage service. This approach ensures that no single event — theft, fire, hardware failure, or ransomware — can destroy all copies of your data simultaneously.</p>

<h3>Backup Methods</h3>
<ul>
<li><strong>Cloud backup services:</strong> Services like OneDrive, Google Drive, iCloud, or dedicated backup solutions automatically sync your files to secure remote servers. They protect against local disasters and theft, and most offer version history so you can recover older versions of files</li>
<li><strong>External drives:</strong> USB hard drives or SSDs provide fast local backups. Keep at least one external backup disconnected from your computer when not actively backing up, so ransomware cannot encrypt it along with your main files</li>
<li><strong>Network-attached storage (NAS):</strong> For homes and small offices, a NAS device provides centralized backup for multiple computers on your network with redundant drives that protect against single-drive failure</li>
<li><strong>System images:</strong> Full system images capture your entire operating system, applications, and settings, allowing you to restore a complete working environment rather than just individual files</li>
</ul>

<h3>Testing Your Backups</h3>
<p>A backup you have never tested is a backup you cannot trust. Schedule regular restore tests — pick a random file from your backup and verify you can successfully recover it. Check that your backup software is actually running and completing without errors. Many people discover their backups have been failing silently for months only when they desperately need to recover data. Make testing part of your routine, not something you do only after a disaster.</p>',
                    ],
                    [
                        'title' => 'Account Hygiene & Password Managers',
                        'slug' => 'account-hygiene-password-managers',
                        'duration_minutes' => 6,
                        'content' => '<h3>Account Hygiene & Password Managers</h3>
<p>Account hygiene refers to the ongoing maintenance of your online accounts to minimize security risks. Over time, most people accumulate dozens or even hundreds of accounts across various services. Many of these accounts use weak passwords, reused credentials, or remain active long after you have stopped using the service. Each neglected account is a potential entry point for attackers, especially when data breaches expose old credentials that you have reused elsewhere.</p>

<h3>Why Password Managers Are Essential</h3>
<p>The human brain cannot reliably remember unique, strong passwords for every account. This is not a personal failing — it is a fundamental limitation that password managers solve. A password manager generates, stores, and automatically fills strong, unique passwords for every account you use. You only need to remember one master password to unlock your vault. This single change eliminates password reuse, which is the root cause of the majority of account compromises following data breaches.</p>

<h3>Choosing and Using a Password Manager</h3>
<ul>
<li><strong>Select a reputable tool:</strong> Options like Bitwarden, 1Password, Dashlane, or your organization\'s approved manager all provide strong encryption and cross-device syncing</li>
<li><strong>Create a strong master password:</strong> Use a passphrase of four or more random words that is easy to remember but hard to guess, such as "correct horse battery staple" — length matters more than complexity</li>
<li><strong>Enable MFA on the password manager itself:</strong> Your password vault is the most critical account to protect with multi-factor authentication</li>
<li><strong>Let it generate passwords:</strong> Use the built-in generator for every new account. Aim for at least 16 characters with a mix of types</li>
<li><strong>Install browser extensions and mobile apps:</strong> Autofill integration makes using unique passwords as convenient as reusing one password everywhere</li>
</ul>

<h3>Regular Account Maintenance</h3>
<p>Periodically review your accounts and close those you no longer use. Check whether your email addresses appear in known data breaches using services like Have I Been Pwned. When a breach notification arrives, change the affected password immediately — and if you reused that password elsewhere, change it on those accounts too. Remove unnecessary permissions granted to third-party apps connected to your Google, Microsoft, or social media accounts. Think of account hygiene like dental hygiene: small, consistent efforts prevent painful problems later.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Cyber Hygiene Daily Practices Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the 3-2-1 backup rule?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The 3-2-1 rule means keeping at least 3 copies of data, on 2 different media types, with 1 copy stored offsite to protect against localized disasters.',
                            'answers' => [
                                ['answer' => 'Back up 3 times a day, to 2 drives, keeping 1 copy encrypted', 'is_correct' => false],
                                ['answer' => 'Keep 3 copies of data on 2 different media types with 1 copy offsite', 'is_correct' => true],
                                ['answer' => 'Use 3 different backup tools, 2 cloud services, and 1 USB drive', 'is_correct' => false],
                                ['answer' => 'Back up every 3 months to 2 locations and test 1 time per year', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is delaying software updates a security risk?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Once a vulnerability is publicly disclosed, attackers actively scan for unpatched systems. Delaying updates leaves known vulnerabilities open for exploitation.',
                            'answers' => [
                                ['answer' => 'Updates slow down your computer permanently', 'is_correct' => false],
                                ['answer' => 'Attackers exploit known vulnerabilities in unpatched software, often within hours of disclosure', 'is_correct' => true],
                                ['answer' => 'Vendors stop supporting software that is not updated within 24 hours', 'is_correct' => false],
                                ['answer' => 'Delayed updates cause data corruption in existing files', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the primary benefit of using a password manager?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Password managers generate and store unique, strong passwords for every account, eliminating the dangerous practice of password reuse.',
                            'answers' => [
                                ['answer' => 'It makes logging in slower, which deters attackers', 'is_correct' => false],
                                ['answer' => 'It eliminates the need for any passwords at all', 'is_correct' => false],
                                ['answer' => 'It generates and stores unique, strong passwords for every account, preventing password reuse', 'is_correct' => true],
                                ['answer' => 'It automatically changes all your passwords every day', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you keep at least one backup disconnected from your computer?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Ransomware can encrypt all connected drives and network shares. An offline backup cannot be reached by malware and remains safe for recovery.',
                            'answers' => [
                                ['answer' => 'Connected drives wear out faster from constant use', 'is_correct' => false],
                                ['answer' => 'Ransomware can encrypt all connected drives, so an offline backup stays safe for recovery', 'is_correct' => true],
                                ['answer' => 'Your computer runs faster with fewer drives connected', 'is_correct' => false],
                                ['answer' => 'It is required by law in most countries', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do when you receive a data breach notification for a service you use?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Change the affected password immediately, and change it on any other accounts where you reused the same password, since attackers try breached credentials across multiple services.',
                            'answers' => [
                                ['answer' => 'Ignore it since breaches happen to everyone', 'is_correct' => false],
                                ['answer' => 'Delete your email account to stop future notifications', 'is_correct' => false],
                                ['answer' => 'Change the affected password immediately and change it on any other accounts where you reused it', 'is_correct' => true],
                                ['answer' => 'Wait for the company to reset your password automatically', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Course 43: Security Culture & Reporting ──
            [
                'title' => 'Security Culture & Reporting',
                'slug' => 'security-culture-reporting',
                'description' => 'Learn how to contribute to a strong security culture in your organization by developing a security-aware mindset and knowing how to properly report and learn from security incidents.',
                'objectives' => [
                    'Understand what a security-aware culture looks like and why it matters',
                    'Know how and when to report security incidents through proper channels',
                    'Recognize that near-misses and minor events are valuable learning opportunities',
                    'Contribute to a blame-free reporting environment that strengthens organizational resilience',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'beginner',
                'duration_minutes' => 15,
                'passing_score' => 70,
                'sort_order' => 43,
                'lessons' => [
                    [
                        'title' => 'Building a Security-Aware Mindset',
                        'slug' => 'building-security-aware-mindset',
                        'duration_minutes' => 5,
                        'content' => '<h3>Building a Security-Aware Mindset</h3>
<p>A security-aware mindset means making security considerations a natural part of how you think and work every day, rather than something you only think about during annual training. It is the difference between locking your front door out of habit and having to remind yourself to do it each time. Organizations with strong security cultures experience significantly fewer breaches because their employees instinctively pause before clicking a suspicious link, question unusual requests, and speak up when something does not seem right.</p>

<h3>Security Is Everyone\'s Responsibility</h3>
<p>Many employees assume that cybersecurity is solely the IT department\'s job. In reality, every person in an organization is a potential target and a potential defender. The most sophisticated firewall cannot prevent an employee from willingly entering credentials on a phishing page. The best intrusion detection system cannot stop someone from holding a door open for a tailgater. Security technology provides essential layers of defense, but human judgment remains the critical factor in preventing most incidents.</p>

<h3>Daily Habits That Build Security Awareness</h3>
<ul>
<li><strong>Pause before clicking:</strong> Take a moment to evaluate every link and attachment before interacting with it, especially in unexpected messages</li>
<li><strong>Verify unusual requests:</strong> If a colleague, vendor, or manager makes an out-of-the-ordinary request involving credentials, payments, or data, confirm it through a separate communication channel</li>
<li><strong>Lock your screen:</strong> Every time you step away from your workstation, lock it. Make it automatic and habitual</li>
<li><strong>Speak up about concerns:</strong> If something feels wrong — a suspicious email, a stranger in the office, a process that seems insecure — say something. It is far better to raise a false alarm than to stay silent about a real threat</li>
<li><strong>Stay current:</strong> Pay attention to security newsletters, alerts, and training updates from your organization. Threats evolve constantly, and awareness must keep pace</li>
</ul>

<h3>The Bystander Effect in Security</h3>
<p>In security, just as in emergency situations, people often assume someone else will handle it. If everyone in the office sees a suspicious email and assumes a colleague reported it, it may go unreported entirely. Adopt the mindset that if you notice something, you are the one responsible for reporting it. Do not assume someone else already has. This personal ownership of security is the foundation of a truly security-aware culture.</p>',
                    ],
                    [
                        'title' => 'How to Report Security Incidents',
                        'slug' => 'how-to-report-security-incidents',
                        'duration_minutes' => 5,
                        'content' => '<h3>How to Report Security Incidents</h3>
<p>Knowing how to report a security incident quickly and effectively is just as important as recognizing one. The speed and quality of your initial report can mean the difference between containing a minor issue and suffering a major breach. Every minute of delay gives attackers more time to move laterally through systems, exfiltrate data, or establish persistent access.</p>

<h3>What Counts as a Security Incident</h3>
<p>A security incident is any event that threatens the confidentiality, integrity, or availability of your organization\'s information or systems. This includes obvious events like ransomware infections and data breaches, but also less dramatic situations that many people overlook:</p>
<ul>
<li><strong>Phishing emails:</strong> Even if you did not click the link, report it so the security team can warn others and block the sender</li>
<li><strong>Lost or stolen devices:</strong> A missing laptop, phone, or USB drive that contained work data, even if encrypted</li>
<li><strong>Unauthorized access:</strong> Noticing that someone accessed files or systems they should not have, or finding you have access you should not</li>
<li><strong>Suspicious behavior:</strong> A colleague downloading large amounts of data before their resignation, or a stranger in a restricted area</li>
<li><strong>Accidental data exposure:</strong> Sending an email with sensitive data to the wrong recipient, or discovering a misconfigured file share</li>
<li><strong>Unusual system behavior:</strong> Unexpected pop-ups, programs running that you did not start, or dramatically slower performance</li>
</ul>

<h3>How to File an Effective Report</h3>
<p>When reporting a security incident, include as much of the following information as you can, but do not delay reporting to gather every detail:</p>
<ul>
<li><strong>What happened:</strong> Describe the event clearly and factually</li>
<li><strong>When it happened:</strong> Date and time, as precisely as you can recall</li>
<li><strong>How you noticed:</strong> What alerted you to the issue</li>
<li><strong>What actions you took:</strong> Did you click a link, open an attachment, provide information, or disconnect a device?</li>
<li><strong>Evidence:</strong> Preserve emails, screenshots, error messages, or URLs — do not delete them</li>
</ul>

<h3>Reporting Channels</h3>
<p>Know your organization\'s reporting channels before an incident occurs. Most organizations provide a dedicated email address (such as security@company.com), a phone hotline, a ticketing system, or a button in the email client to report phishing. Use whichever channel is fastest. If you are unsure whether something qualifies as an incident, report it anyway — the security team would rather triage a false positive than miss a genuine threat.</p>',
                    ],
                    [
                        'title' => 'Learning from Security Events',
                        'slug' => 'learning-from-security-events',
                        'duration_minutes' => 5,
                        'content' => '<h3>Learning from Security Events</h3>
<p>Every security event — whether a full-blown breach, a near-miss, or even a phishing simulation that several employees fell for — represents a learning opportunity. Organizations with mature security cultures treat incidents not as failures to punish but as data points that reveal weaknesses in processes, technology, or training. This blame-free approach to security events is essential because it encourages reporting and honest discussion about what went wrong.</p>

<h3>The Blame-Free Reporting Culture</h3>
<p>If employees fear punishment for reporting a security incident they may have caused, they will hide it. An employee who clicked a phishing link and is afraid of being fired may say nothing, giving attackers hours or days of undetected access. In contrast, organizations that respond to honest mistakes with support and education create an environment where incidents are reported immediately. The goal is not to find someone to blame but to find the gap that allowed the incident to happen and close it.</p>

<h3>Post-Incident Reviews</h3>
<ul>
<li><strong>What happened:</strong> Establish a clear timeline of events from initial compromise to detection and containment</li>
<li><strong>How it was detected:</strong> Was it caught by technology, by an alert employee, or by an external party? Detection speed is a key metric to track</li>
<li><strong>What worked:</strong> Identify the controls, processes, and decisions that limited the impact of the incident</li>
<li><strong>What failed:</strong> Determine which defenses did not work as expected and why, without assigning personal blame</li>
<li><strong>What we will change:</strong> Define specific, actionable improvements with owners and deadlines</li>
</ul>

<h3>Sharing Lessons Learned</h3>
<p>Security lessons are only valuable if they reach the people who need them. After a post-incident review, share relevant findings with the broader organization in a way that educates without exposing sensitive details. Anonymize the specifics but preserve the lesson. For example, instead of naming the employee who fell for a phishing email, describe the technique used and the red flags that could have been spotted. Regular security bulletins, team meetings, and updated training materials all serve as channels for disseminating these lessons.</p>

<h3>Continuous Improvement</h3>
<p>Security is not a destination but an ongoing process. Each incident review should feed back into your organization\'s security program, updating risk assessments, refining detection capabilities, improving training content, and strengthening response procedures. Track metrics over time — such as mean time to detect, mean time to report, and phishing simulation click rates — to measure whether your security culture is genuinely improving or merely going through the motions.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Security Culture & Reporting Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'Why is a blame-free reporting culture important for security?',
                            'type' => 'multiple_choice',
                            'explanation' => 'When employees fear punishment, they hide incidents. A blame-free culture encourages immediate reporting, which is critical for fast containment.',
                            'answers' => [
                                ['answer' => 'It means nobody is responsible for security', 'is_correct' => false],
                                ['answer' => 'It ensures employees are never disciplined for any reason', 'is_correct' => false],
                                ['answer' => 'It encourages immediate incident reporting because employees do not fear punishment for honest mistakes', 'is_correct' => true],
                                ['answer' => 'It eliminates the need for a dedicated security team', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You receive a phishing email but did not click the link. Should you report it?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Reporting phishing emails even if you did not click allows the security team to warn others, block the sender, and track attack campaigns targeting the organization.',
                            'answers' => [
                                ['answer' => 'No, since you did not click the link there is no incident', 'is_correct' => false],
                                ['answer' => 'Yes, so the security team can warn others and block the sender', 'is_correct' => true],
                                ['answer' => 'Only if it looks particularly convincing', 'is_correct' => false],
                                ['answer' => 'No, just delete it and move on', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the "bystander effect" in the context of security?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The bystander effect in security means everyone assumes someone else will report the issue, so nobody does. Each person should take personal responsibility for reporting.',
                            'answers' => [
                                ['answer' => 'Attackers only target people who are standing nearby', 'is_correct' => false],
                                ['answer' => 'Everyone assumes someone else has already reported the incident, so nobody reports it', 'is_correct' => true],
                                ['answer' => 'Bystanders are more likely to be targeted by phishing', 'is_correct' => false],
                                ['answer' => 'People who witness a cyberattack become immune to future attacks', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should a post-incident review focus on?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Post-incident reviews should identify what happened, what worked, what failed, and what specific improvements to make — without assigning personal blame.',
                            'answers' => [
                                ['answer' => 'Finding and disciplining the person who caused the incident', 'is_correct' => false],
                                ['answer' => 'Identifying what happened, what worked, what failed, and what specific changes to make', 'is_correct' => true],
                                ['answer' => 'Writing a press release about the incident', 'is_correct' => false],
                                ['answer' => 'Purchasing new security software to replace all existing tools', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of the following is an example of a security incident that should be reported?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Sending sensitive data to the wrong recipient is an accidental data exposure and constitutes a security incident that should be reported immediately.',
                            'answers' => [
                                ['answer' => 'Receiving a marketing email from a company you subscribe to', 'is_correct' => false],
                                ['answer' => 'Your computer restarting after a scheduled update', 'is_correct' => false],
                                ['answer' => 'Accidentally sending a spreadsheet with customer data to the wrong email recipient', 'is_correct' => true],
                                ['answer' => 'Forgetting your lunch in the office refrigerator', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Course 44: Supply Chain & Third-Party Risk ──
            [
                'title' => 'Supply Chain & Third-Party Risk',
                'slug' => 'supply-chain-third-party-risk',
                'description' => 'Understand how attackers exploit trusted vendor relationships and software supply chains to compromise organizations, and learn strategies for assessing and mitigating third-party risk.',
                'objectives' => [
                    'Explain how supply chain attacks work and why they are increasingly common',
                    'Assess third-party vendors for security risks using structured evaluation methods',
                    'Identify warning signs of supply chain compromise in software and services',
                    'Apply practical measures to reduce exposure to third-party security risks',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'advanced',
                'duration_minutes' => 25,
                'passing_score' => 75,
                'sort_order' => 44,
                'lessons' => [
                    [
                        'title' => 'Understanding Supply Chain Attacks',
                        'slug' => 'understanding-supply-chain-attacks',
                        'duration_minutes' => 8,
                        'content' => '<h3>Understanding Supply Chain Attacks</h3>
<p>A supply chain attack occurs when an attacker compromises a trusted third party — a software vendor, service provider, or hardware manufacturer — to gain access to that third party\'s customers. Instead of attacking your organization directly, the attacker infiltrates a product or service you already trust and use, turning it into a delivery mechanism for malicious code or unauthorized access. This approach is devastatingly effective because it exploits the implicit trust organizations place in their suppliers.</p>

<h3>Why Supply Chain Attacks Are Growing</h3>
<p>Modern organizations depend on hundreds of third-party vendors, open-source libraries, cloud services, and managed service providers. Each of these dependencies creates a potential entry point. Attackers have recognized that compromising a single widely-used vendor can give them simultaneous access to thousands of organizations — a far more efficient approach than attacking each target individually. The increasing complexity of software supply chains, with layers of dependencies built on other dependencies, creates a vast and difficult-to-monitor attack surface.</p>

<h3>Notable Supply Chain Attacks</h3>
<ul>
<li><strong>SolarWinds (2020):</strong> Attackers inserted malicious code into the SolarWinds Orion software update process, distributing a backdoor to approximately 18,000 organizations including major government agencies and Fortune 500 companies. The compromise went undetected for months because the malicious code was delivered through a legitimate, signed software update</li>
<li><strong>Kaseya VSA (2021):</strong> The REvil ransomware group exploited vulnerabilities in Kaseya\'s remote management software, which is used by managed service providers. The attack cascaded from Kaseya to its MSP customers to those MSPs\' clients, ultimately affecting over 1,500 businesses worldwide</li>
<li><strong>Log4Shell (2021):</strong> A critical vulnerability in the Log4j logging library, used in millions of Java applications worldwide, demonstrated how a flaw in a single open-source component could expose an enormous number of systems. Many organizations did not even know they were using Log4j because it was embedded deep within other software dependencies</li>
<li><strong>3CX (2023):</strong> Attackers compromised the build environment of 3CX, a popular business phone system, inserting malware into the official desktop application that was then distributed to customers through the normal update mechanism</li>
</ul>

<h3>The Trust Problem</h3>
<p>Supply chain attacks are particularly dangerous because they weaponize trust. Your security team has approved the vendor. Your firewall allows the connection. Your endpoint protection trusts the signed software. When the attack comes through a legitimate channel, traditional security controls often fail to detect it. This is why supply chain security requires a fundamentally different approach than perimeter defense — you must consider the security posture of every entity your organization depends on.</p>',
                    ],
                    [
                        'title' => 'Vendor Risk Assessment',
                        'slug' => 'vendor-risk-assessment',
                        'duration_minutes' => 9,
                        'content' => '<h3>Vendor Risk Assessment</h3>
<p>Vendor risk assessment is the systematic process of evaluating the security posture of third-party organizations before and during your business relationship with them. A thorough assessment helps you understand what risks a vendor introduces to your environment and whether those risks are acceptable given the value the vendor provides. This is not a one-time activity — vendor risk must be monitored continuously because a vendor\'s security posture can change over time.</p>

<h3>Key Assessment Areas</h3>
<ul>
<li><strong>Data handling:</strong> What data will the vendor have access to? How will they store, process, transmit, and ultimately dispose of it? Do they encrypt data at rest and in transit?</li>
<li><strong>Security certifications:</strong> Does the vendor hold relevant certifications such as SOC 2 Type II, ISO 27001, or industry-specific certifications like HITRUST for healthcare? These certify that an independent auditor has verified their security controls</li>
<li><strong>Incident response:</strong> Does the vendor have a documented incident response plan? How quickly will they notify you of a breach affecting your data? Contractual notification timelines are critical</li>
<li><strong>Access controls:</strong> How does the vendor control access to your data internally? Do they enforce least privilege, MFA, and role-based access for their employees?</li>
<li><strong>Subcontractors:</strong> Does the vendor use subcontractors or sub-processors who will also have access to your data? Each additional party in the chain adds risk</li>
</ul>

<h3>Tiering Your Vendors</h3>
<p>Not all vendors carry the same level of risk. A practical approach is to tier vendors based on the sensitivity of data they access and the criticality of the services they provide. Critical vendors — those with access to sensitive data or whose failure would disrupt your operations — require the most rigorous assessment, including on-site audits, detailed questionnaire responses, and regular reassessment. Lower-risk vendors, such as those providing commodity services with no data access, can be assessed with lighter-weight methods.</p>

<h3>Contractual Protections</h3>
<ul>
<li><strong>Security requirements:</strong> Specify minimum security controls the vendor must maintain, including encryption standards, access controls, and patch management timelines</li>
<li><strong>Breach notification:</strong> Require notification within a specific timeframe (typically 24 to 72 hours) of any security incident affecting your data</li>
<li><strong>Right to audit:</strong> Reserve the right to audit the vendor\'s security controls or request evidence of compliance at reasonable intervals</li>
<li><strong>Data return and destruction:</strong> Define how your data will be returned or securely destroyed at the end of the relationship</li>
<li><strong>Liability and indemnification:</strong> Establish clear liability for security breaches caused by the vendor\'s negligence</li>
</ul>

<p>Remember that a vendor assessment questionnaire is only as good as the verification behind it. Vendors will naturally present their security in the best light. Where possible, validate their responses with independent evidence such as audit reports, penetration test summaries, and certification documentation.</p>',
                    ],
                    [
                        'title' => 'Protecting Against Third-Party Compromise',
                        'slug' => 'protecting-against-third-party-compromise',
                        'duration_minutes' => 8,
                        'content' => '<h3>Protecting Against Third-Party Compromise</h3>
<p>Even with thorough vendor assessments, you cannot eliminate supply chain risk entirely. A vendor you assessed as secure today could be compromised tomorrow. Therefore, your defense strategy must include measures that limit the blast radius of a third-party compromise and enable rapid detection and response when one occurs. The goal is to ensure that a compromised vendor cannot easily become a compromised organization.</p>

<h3>Principle of Least Privilege for Vendors</h3>
<p>Grant third-party vendors and their software only the minimum access necessary to perform their function. If a vendor needs access to your network for maintenance, restrict that access to the specific systems they service, during defined time windows, with full logging enabled. Avoid giving vendors persistent, broad access that remains active around the clock. Review vendor access permissions quarterly and revoke access that is no longer needed. Many organizations discover during audits that former vendors still have active credentials months after contracts ended.</p>

<h3>Network Segmentation</h3>
<ul>
<li><strong>Isolate vendor access:</strong> Place vendor-accessible systems in separate network segments so that a compromised vendor connection cannot reach your most sensitive systems</li>
<li><strong>Limit lateral movement:</strong> Even if an attacker enters through a vendor\'s compromised software, segmentation prevents them from easily moving to other parts of your network</li>
<li><strong>Monitor segment boundaries:</strong> Implement detection at the boundaries between segments to alert on unusual traffic patterns that might indicate compromise</li>
</ul>

<h3>Software Supply Chain Controls</h3>
<ul>
<li><strong>Software Bill of Materials (SBOM):</strong> Maintain an inventory of all software components and dependencies in your environment. When a vulnerability like Log4Shell is announced, an SBOM lets you quickly determine if you are affected</li>
<li><strong>Verify software integrity:</strong> Check digital signatures and hashes of software updates before deploying them. If a vendor\'s signing key is compromised, as in the SolarWinds attack, this is difficult to detect, but unsigned or incorrectly signed updates should never be installed</li>
<li><strong>Staged rollouts:</strong> Deploy vendor updates to a small test group before pushing them organization-wide. This limits exposure if an update is compromised and gives time to detect anomalous behavior</li>
<li><strong>Monitor vendor behavior:</strong> Track the network connections, file system activity, and resource consumption of vendor software. Establish baselines of normal behavior so deviations are detectable</li>
</ul>

<h3>Incident Preparedness</h3>
<p>Include third-party compromise scenarios in your incident response planning. Know in advance which vendors have access to which systems, so that when a vendor announces a breach, you can immediately assess your exposure and take containment actions. Maintain offline backups that cannot be affected by compromised vendor software. Practice response scenarios that involve isolating vendor connections and switching to manual processes while the situation is assessed. The organizations that responded best to the SolarWinds compromise were those that had already considered supply chain attacks in their incident response plans.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Supply Chain & Third-Party Risk Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What makes supply chain attacks particularly dangerous compared to direct attacks?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Supply chain attacks exploit the trust organizations place in their vendors. Malicious code delivered through legitimate, trusted channels bypasses many traditional security controls.',
                            'answers' => [
                                ['answer' => 'They use more advanced encryption than direct attacks', 'is_correct' => false],
                                ['answer' => 'They exploit trusted vendor relationships, delivering malicious code through legitimate channels that bypass traditional security controls', 'is_correct' => true],
                                ['answer' => 'They only target small businesses that cannot afford security tools', 'is_correct' => false],
                                ['answer' => 'They are easier to detect because they come from known sources', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a Software Bill of Materials (SBOM) and why is it important?',
                            'type' => 'multiple_choice',
                            'explanation' => 'An SBOM is an inventory of all software components and dependencies. It allows you to quickly determine if you are affected when a vulnerability in a component is disclosed.',
                            'answers' => [
                                ['answer' => 'A purchase order for new software licenses', 'is_correct' => false],
                                ['answer' => 'An inventory of all software components and dependencies, enabling rapid identification of affected systems when vulnerabilities are disclosed', 'is_correct' => true],
                                ['answer' => 'A list of all employees who have access to software tools', 'is_correct' => false],
                                ['answer' => 'A backup copy of all software installed in the organization', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should vendor software updates be deployed in staged rollouts rather than all at once?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Staged rollouts limit exposure if an update is compromised, giving time to detect anomalous behavior in the test group before it affects the entire organization.',
                            'answers' => [
                                ['answer' => 'To reduce network bandwidth usage during peak hours', 'is_correct' => false],
                                ['answer' => 'Because vendors require staged deployments in their license agreements', 'is_correct' => false],
                                ['answer' => 'To limit exposure if the update is compromised, allowing detection of issues before organization-wide deployment', 'is_correct' => true],
                                ['answer' => 'To give the vendor time to fix bugs discovered after release', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'In the SolarWinds attack, how was the malicious code distributed to victims?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The attackers compromised SolarWinds\' build process and inserted malicious code into a legitimate, digitally signed software update that was distributed through normal channels.',
                            'answers' => [
                                ['answer' => 'Through phishing emails sent to SolarWinds customers', 'is_correct' => false],
                                ['answer' => 'By exploiting known vulnerabilities in customer firewalls', 'is_correct' => false],
                                ['answer' => 'Through a legitimate, signed software update that had been tampered with during the build process', 'is_correct' => true],
                                ['answer' => 'By physically accessing data centers and installing hardware implants', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the most important contractual protection when engaging a vendor who will handle sensitive data?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A breach notification clause with a defined timeline ensures you learn about compromises quickly enough to take containment actions and meet your own regulatory obligations.',
                            'answers' => [
                                ['answer' => 'A clause guaranteeing the lowest possible price for services', 'is_correct' => false],
                                ['answer' => 'A breach notification requirement with a defined timeline so you can respond quickly to incidents affecting your data', 'is_correct' => true],
                                ['answer' => 'A requirement that the vendor use the same operating system as your organization', 'is_correct' => false],
                                ['answer' => 'A clause allowing unlimited data storage at no extra cost', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
