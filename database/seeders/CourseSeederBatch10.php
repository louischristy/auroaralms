<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Database\Seeder;

class CourseSeederBatch10 extends Seeder
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
            [
                'slug' => 'ransomware-defense-recovery',
                'title' => 'Ransomware Defense & Recovery',
                'description' => 'Learn how ransomware attacks work, how to prevent them, and what to do if your organization is hit. Covers modern ransomware tactics including double extortion and ransomware-as-a-service.',
                'objectives' => [
                    'Understand how ransomware infiltrates organizations',
                    'Recognize the warning signs of a ransomware attack',
                    'Follow proper incident response procedures during an attack',
                    'Implement backup strategies that protect against ransomware',
                ],
                'category' => 'Malware & Ransomware',
                'difficulty' => 'intermediate',
                'duration_minutes' => 35,
                'passing_score' => 70,
                'is_mandatory' => true,
                'sort_order' => 28,
                'lessons' => [
                    [
                        'slug' => 'how-ransomware-works',
                        'title' => 'How Ransomware Works',
                        'duration_minutes' => 8,
                        'content' => '<h2>Understanding Ransomware</h2>
<p>Ransomware is malicious software that encrypts your files and demands payment for the decryption key. Modern ransomware has evolved far beyond simple file encryption.</p>

<h3>The Ransomware Kill Chain</h3>
<ol>
<li><strong>Initial Access</strong> — Attackers gain entry through phishing emails, compromised credentials, or vulnerable applications.</li>
<li><strong>Lateral Movement</strong> — Once inside, they move across the network to identify critical systems.</li>
<li><strong>Data Exfiltration</strong> — Modern attackers steal data before encrypting, enabling "double extortion."</li>
<li><strong>Encryption</strong> — Files are encrypted simultaneously, often triggered outside business hours.</li>
<li><strong>Ransom Demand</strong> — A ransom note demands payment in cryptocurrency.</li>
</ol>

<h3>Ransomware-as-a-Service (RaaS)</h3>
<p>Criminal organizations now sell ransomware toolkits to affiliates who carry out attacks, splitting the ransom payments. This has dramatically lowered the barrier to entry for attackers.</p>

<h3>Double and Triple Extortion</h3>
<ul>
<li><strong>Single extortion</strong>: Pay to decrypt your files</li>
<li><strong>Double extortion</strong>: Pay to decrypt AND prevent public release of stolen data</li>
<li><strong>Triple extortion</strong>: All of the above PLUS threatening your customers or partners directly</li>
</ul>',
                    ],
                    [
                        'slug' => 'preventing-ransomware-attacks',
                        'title' => 'Preventing Ransomware Attacks',
                        'duration_minutes' => 10,
                        'content' => '<h2>Prevention Strategies</h2>
<p>Most ransomware attacks are preventable. The majority exploit human error or known vulnerabilities.</p>

<h3>Email Security</h3>
<ul>
<li>Never open unexpected attachments, especially .exe, .zip, .js, or Office files with macros</li>
<li>Hover over links before clicking — verify the actual URL</li>
<li>Be wary of urgency tactics: "Your account will be locked in 2 hours"</li>
<li>Report suspicious emails immediately</li>
</ul>

<h3>System Hygiene</h3>
<ul>
<li><strong>Keep software updated</strong> — Many strains exploit known, patched vulnerabilities</li>
<li><strong>Use strong, unique passwords</strong> with MFA on all accounts</li>
<li><strong>Limit admin privileges</strong> — Use standard user accounts for daily work</li>
<li><strong>Disable macros</strong> in Office documents from external sources</li>
</ul>

<h3>The 3-2-1 Backup Rule</h3>
<p>The most effective defense against ransomware:</p>
<ul>
<li><strong>3</strong> copies of your data</li>
<li><strong>2</strong> different types of storage media</li>
<li><strong>1</strong> copy stored offsite or offline (air-gapped)</li>
</ul>
<p>Regularly test your backups by performing restoration exercises. An untested backup is not a backup.</p>',
                    ],
                    [
                        'slug' => 'responding-to-ransomware',
                        'title' => 'Responding to a Ransomware Attack',
                        'duration_minutes' => 10,
                        'content' => '<h2>When Ransomware Strikes</h2>
<p>If you suspect a ransomware attack, every minute counts.</p>

<h3>Immediate Actions (First 5 Minutes)</h3>
<ol>
<li><strong>Disconnect from the network</strong> — Unplug Ethernet, disable Wi-Fi. Prevents spreading.</li>
<li><strong>Do NOT turn off your computer</strong> — Encryption keys may be in memory.</li>
<li><strong>Call IT security</strong> — Use your phone, not email or chat (may be compromised).</li>
<li><strong>Document what you see</strong> — Take photos of the ransom note with your phone.</li>
</ol>

<h3>What NOT to Do</h3>
<ul>
<li><strong>Do not pay the ransom</strong> — Only 65% of payers get all data back. It funds crime.</li>
<li><strong>Do not try to decrypt files yourself</strong> — You may cause further damage</li>
<li><strong>Do not delete the ransomware</strong> — Forensic teams need it</li>
<li><strong>Do not use compromised systems to communicate</strong></li>
</ul>

<h3>Recovery Process</h3>
<ol>
<li>Containing the infection to prevent further spread</li>
<li>Identifying the ransomware variant and scope of impact</li>
<li>Checking for available decryption tools</li>
<li>Restoring systems from clean backups</li>
<li>Post-incident review to prevent recurrence</li>
</ol>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Ransomware Defense & Recovery Quiz',
                    'questions' => [
                        [
                            'question' => 'What is "double extortion" in the context of ransomware?',
                            'explanation' => 'Double extortion involves both encrypting files and threatening to publicly release stolen data.',
                            'answers' => [
                                ['answer' => 'Demanding payment twice — once to decrypt and once to delete malware', 'is_correct' => false],
                                ['answer' => 'Encrypting files AND threatening to release stolen data publicly', 'is_correct' => true],
                                ['answer' => 'Attacking two different organizations simultaneously', 'is_correct' => false],
                                ['answer' => 'Requiring payment in two different cryptocurrencies', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do FIRST if you notice signs of ransomware on your computer?',
                            'explanation' => 'Disconnecting from the network prevents ransomware from spreading to other systems.',
                            'answers' => [
                                ['answer' => 'Turn off your computer immediately', 'is_correct' => false],
                                ['answer' => 'Try to delete the suspicious files', 'is_correct' => false],
                                ['answer' => 'Disconnect from the network (unplug Ethernet, disable Wi-Fi)', 'is_correct' => true],
                                ['answer' => 'Email your IT department about the problem', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does the "1" in the 3-2-1 backup rule mean?',
                            'explanation' => 'The 3-2-1 rule: 3 copies, 2 different media types, 1 copy offsite or air-gapped.',
                            'answers' => [
                                ['answer' => '1 backup performed per day', 'is_correct' => false],
                                ['answer' => '1 copy stored offsite or offline (air-gapped)', 'is_correct' => true],
                                ['answer' => '1 person responsible for backup management', 'is_correct' => false],
                                ['answer' => '1 hour maximum for backup completion', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you NOT pay a ransomware demand?',
                            'explanation' => 'Paying ransoms funds criminal enterprises, encourages future attacks, and does not guarantee data recovery.',
                            'answers' => [
                                ['answer' => 'Because it is illegal in all countries', 'is_correct' => false],
                                ['answer' => 'Because attackers always delete your data regardless', 'is_correct' => false],
                                ['answer' => 'Because it funds criminal activity and does not guarantee recovery', 'is_correct' => true],
                                ['answer' => 'Because the ransom amount always increases after payment', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is Ransomware-as-a-Service (RaaS)?',
                            'explanation' => 'RaaS is a criminal business model where ransomware developers sell or lease their tools to affiliates.',
                            'answers' => [
                                ['answer' => 'A cloud-based security tool for detecting ransomware', 'is_correct' => false],
                                ['answer' => 'A government program to help organizations recover', 'is_correct' => false],
                                ['answer' => 'Criminal organizations selling ransomware toolkits to affiliates', 'is_correct' => true],
                                ['answer' => 'An insurance product covering ransomware costs', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'insider-threat-awareness',
                'title' => 'Insider Threat Awareness',
                'description' => 'Understand the risks posed by insider threats — both malicious and accidental — and learn how to recognize warning signs and respond appropriately.',
                'objectives' => [
                    'Define the different types of insider threats',
                    'Recognize behavioral indicators of potential insider threats',
                    'Know the proper channels for reporting suspicious behavior',
                    'Understand your role in protecting organizational assets',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'intermediate',
                'duration_minutes' => 30,
                'passing_score' => 70,
                'is_mandatory' => true,
                'sort_order' => 29,
                'lessons' => [
                    [
                        'slug' => 'types-of-insider-threats',
                        'title' => 'Types of Insider Threats',
                        'duration_minutes' => 8,
                        'content' => '<h2>Understanding Insider Threats</h2>
<p>An insider threat comes from anyone with authorized access to an organization\'s systems, data, or facilities — current and former employees, contractors, business partners, and vendors.</p>

<h3>Categories of Insider Threats</h3>

<h4>1. Malicious Insiders</h4>
<p>Individuals who intentionally misuse their access for personal gain, revenge, or espionage:</p>
<ul>
<li>Stealing trade secrets before joining a competitor</li>
<li>Sabotaging systems after being terminated</li>
<li>Selling confidential data to external parties</li>
</ul>

<h4>2. Negligent Insiders</h4>
<p>Well-meaning employees who accidentally cause harm — the most common type:</p>
<ul>
<li>Falling for phishing emails and giving away credentials</li>
<li>Leaving laptops or documents in unsecured locations</li>
<li>Sharing passwords with colleagues for convenience</li>
<li>Sending sensitive data to the wrong recipient</li>
</ul>

<h4>3. Compromised Insiders</h4>
<p>Employees whose credentials have been stolen by external attackers. The employee may be completely unaware their account is being misused.</p>

<h3>Why Insider Threats Are Dangerous</h3>
<p>Insiders already have trusted access, know where valuable data resides, and understand internal processes. They can bypass many security controls designed to keep outsiders out.</p>',
                    ],
                    [
                        'slug' => 'recognizing-warning-signs',
                        'title' => 'Recognizing Warning Signs',
                        'duration_minutes' => 8,
                        'content' => '<h2>Behavioral Indicators</h2>
<p>While no single indicator is proof of malicious intent, patterns of concerning behavior warrant attention.</p>

<h3>Digital Warning Signs</h3>
<ul>
<li>Accessing files or systems outside their job responsibilities</li>
<li>Downloading or copying large volumes of data, especially before resignation</li>
<li>Logging in at unusual hours without a clear business reason</li>
<li>Attempting to bypass security controls or access restrictions</li>
<li>Using unauthorized storage devices (USB drives, personal cloud accounts)</li>
<li>Sending sensitive files to personal email addresses</li>
</ul>

<h3>Behavioral Warning Signs</h3>
<ul>
<li>Expressing significant dissatisfaction about the organization</li>
<li>Sudden interest in projects outside their role</li>
<li>Reluctance to take vacation or let others access their work</li>
<li>Violations of security policies that seem intentional</li>
</ul>

<h3>Important Caveats</h3>
<p>These indicators require context. Someone working unusual hours may be in a different time zone. The goal is to notice patterns and report concerns — not to accuse colleagues.</p>
<p><strong>Trust your instincts.</strong> If something feels wrong, it is better to report it and be wrong than to stay silent.</p>',
                    ],
                    [
                        'slug' => 'reporting-and-prevention',
                        'title' => 'Reporting and Prevention',
                        'duration_minutes' => 8,
                        'content' => '<h2>How to Report Concerns</h2>
<p>Every employee has a role in preventing insider threats. Reporting is about protecting everyone in the organization.</p>

<h3>Reporting Channels</h3>
<ul>
<li><strong>Direct manager</strong> — For general concerns about team member behavior</li>
<li><strong>IT Security team</strong> — For technical concerns (suspicious system activity)</li>
<li><strong>HR department</strong> — For behavioral concerns or workplace issues</li>
<li><strong>Anonymous tip line</strong> — If your organization provides one</li>
</ul>

<h3>What to Include in a Report</h3>
<ul>
<li>What you observed (specific actions, not interpretations)</li>
<li>When and where it happened</li>
<li>Who was involved</li>
<li>Any evidence you have — but do not investigate on your own</li>
</ul>

<h3>Prevention Best Practices</h3>
<ul>
<li><strong>Least privilege</strong> — Only request access you genuinely need</li>
<li><strong>Clean desk policy</strong> — Lock your screen and secure documents when away</li>
<li><strong>Proper offboarding</strong> — Return all company assets when leaving</li>
<li><strong>Secure data handling</strong> — Use only approved tools for sensitive data</li>
<li><strong>Speak up</strong> — Report concerns early, even if uncertain</li>
</ul>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Insider Threat Awareness Quiz',
                    'questions' => [
                        [
                            'question' => 'Which type of insider threat is the most common?',
                            'explanation' => 'Negligent insiders — well-meaning employees who accidentally cause harm — account for the majority of incidents.',
                            'answers' => [
                                ['answer' => 'Malicious insiders seeking financial gain', 'is_correct' => false],
                                ['answer' => 'Negligent insiders who accidentally cause harm', 'is_correct' => true],
                                ['answer' => 'Corporate spies planted by competitors', 'is_correct' => false],
                                ['answer' => 'Disgruntled former employees', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A colleague starts downloading large amounts of data right after announcing their resignation. What should you do?',
                            'explanation' => 'This is a significant warning sign. Report it through proper channels.',
                            'answers' => [
                                ['answer' => 'Confront the colleague and ask what they are doing', 'is_correct' => false],
                                ['answer' => 'Ignore it — they probably need the files for reference', 'is_correct' => false],
                                ['answer' => 'Report it to your IT security team or manager', 'is_correct' => true],
                                ['answer' => 'Try to access their computer to see what they downloaded', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a "compromised insider"?',
                            'explanation' => 'A compromised insider is an employee whose credentials have been stolen by external attackers.',
                            'answers' => [
                                ['answer' => 'An employee fired for security violations', 'is_correct' => false],
                                ['answer' => 'An employee whose credentials were stolen and are being used by external attackers', 'is_correct' => true],
                                ['answer' => 'A contractor who works for multiple competing organizations', 'is_correct' => false],
                                ['answer' => 'An employee who deliberately shares data with competitors', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which is the best example of the "least privilege" principle?',
                            'explanation' => 'Least privilege means having access only to what you need for your specific role.',
                            'answers' => [
                                ['answer' => 'Giving all employees admin access for efficiency', 'is_correct' => false],
                                ['answer' => 'Only requesting access to systems you need for your specific role', 'is_correct' => true],
                                ['answer' => 'Sharing your login with a trusted colleague on vacation', 'is_correct' => false],
                                ['answer' => 'Using the same password for all systems', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why are insider threats particularly dangerous compared to external attacks?',
                            'explanation' => 'Insiders already have trusted access and knowledge of internal systems.',
                            'answers' => [
                                ['answer' => 'Insiders always have more technical skills than hackers', 'is_correct' => false],
                                ['answer' => 'Insiders already have trusted access and knowledge of internal systems', 'is_correct' => true],
                                ['answer' => 'Insider attacks are never detected by security tools', 'is_correct' => false],
                                ['answer' => 'Organizations are not allowed to monitor employee activity', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'cloud-security-fundamentals',
                'title' => 'Cloud Security Fundamentals',
                'description' => 'Learn the security risks and best practices for using cloud services like Microsoft 365, Google Workspace, and cloud storage.',
                'objectives' => [
                    'Understand the shared responsibility model for cloud security',
                    'Securely configure cloud storage and sharing settings',
                    'Recognize common cloud-based attack vectors',
                    'Apply best practices for cloud account security',
                ],
                'category' => 'Data Protection & Privacy',
                'difficulty' => 'intermediate',
                'duration_minutes' => 30,
                'passing_score' => 70,
                'sort_order' => 30,
                'lessons' => [
                    [
                        'slug' => 'shared-responsibility-model',
                        'title' => 'The Shared Responsibility Model',
                        'duration_minutes' => 8,
                        'content' => '<h2>Who Is Responsible for Cloud Security?</h2>
<p>Security is a shared responsibility between the cloud provider and your organization. The provider secures the infrastructure; you secure your data and access.</p>

<h3>What the Cloud Provider Handles</h3>
<ul>
<li>Physical security of data centers</li>
<li>Network infrastructure and hardware</li>
<li>Platform-level patches and updates</li>
<li>Availability and disaster recovery of the service</li>
</ul>

<h3>What Your Organization Handles</h3>
<ul>
<li>Who has access to your data and at what level</li>
<li>How data is classified and what is stored in the cloud</li>
<li>Configuration of security settings (sharing, permissions, encryption)</li>
<li>User account security (passwords, MFA, session management)</li>
</ul>

<h3>What YOU Handle</h3>
<ul>
<li>Using strong, unique passwords with MFA</li>
<li>Checking sharing settings before sharing files</li>
<li>Not storing sensitive data in unauthorized services</li>
<li>Reporting suspicious activity immediately</li>
</ul>',
                    ],
                    [
                        'slug' => 'secure-file-sharing',
                        'title' => 'Secure File Sharing & Storage',
                        'duration_minutes' => 8,
                        'content' => '<h2>Cloud Sharing Best Practices</h2>
<p>Cloud storage makes sharing easy — sometimes too easy. A single misconfigured link can expose sensitive data to the entire internet.</p>

<h3>Common Sharing Mistakes</h3>
<ul>
<li><strong>"Anyone with the link"</strong> — Makes files accessible to anyone, including via search engines</li>
<li><strong>Overly broad folder permissions</strong> — Sharing entire folders when only one file is needed</li>
<li><strong>Forgotten shared files</strong> — Old shared links left active indefinitely</li>
<li><strong>Personal cloud accounts</strong> — Using personal Dropbox or Google Drive for company data</li>
</ul>

<h3>Safe Sharing Rules</h3>
<ol>
<li><strong>Share with specific people</strong> — Use individual email addresses, not "anyone with the link"</li>
<li><strong>Minimum permission level</strong> — "Viewer" unless the person needs to edit</li>
<li><strong>Set expiration dates</strong> — Use auto-expiring links for temporary sharing</li>
<li><strong>Audit regularly</strong> — Review and revoke access you no longer need</li>
<li><strong>Use approved services only</strong> — Avoid "Shadow IT"</li>
</ol>

<h3>Shadow IT</h3>
<p>Cloud services employees use without IT approval create blind spots for your security team and may violate compliance requirements. If you need a tool, request it through proper channels.</p>',
                    ],
                    [
                        'slug' => 'cloud-account-security',
                        'title' => 'Protecting Your Cloud Accounts',
                        'duration_minutes' => 8,
                        'content' => '<h2>Account Security Essentials</h2>
<p>Your cloud accounts are the keys to your organization\'s data. A compromised account can give attackers access to everything.</p>

<h3>Multi-Factor Authentication (MFA)</h3>
<p>MFA is the single most effective defense for cloud accounts:</p>
<ul>
<li><strong>Best</strong>: Hardware security keys (YubiKey, Titan)</li>
<li><strong>Good</strong>: Authenticator apps (Google Authenticator, Microsoft Authenticator)</li>
<li><strong>Acceptable</strong>: SMS codes (vulnerable to SIM swapping but better than nothing)</li>
</ul>

<h3>OAuth and Third-Party App Access</h3>
<p>Be cautious when apps request access to your cloud accounts:</p>
<ul>
<li>Only authorize apps approved by your IT team</li>
<li>Review what permissions the app requests before clicking "Allow"</li>
<li>Regularly review and revoke access for unused apps</li>
</ul>

<h3>Session Management</h3>
<ul>
<li>Log out of cloud services on shared or public computers</li>
<li>Review active sessions and sign out of unfamiliar devices</li>
<li>Use private/incognito mode on shared machines</li>
</ul>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Cloud Security Fundamentals Quiz',
                    'questions' => [
                        [
                            'question' => 'In the shared responsibility model, who manages user access to cloud data?',
                            'explanation' => 'The customer organization is responsible for managing who has access to their data.',
                            'answers' => [
                                ['answer' => 'The cloud provider handles all security automatically', 'is_correct' => false],
                                ['answer' => 'Your organization is responsible for managing user access', 'is_correct' => true],
                                ['answer' => 'The government regulates cloud data access', 'is_correct' => false],
                                ['answer' => 'Individual users have no responsibility', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the biggest risk of sharing a file with "Anyone with the link"?',
                            'explanation' => 'Links can be forwarded, leaked, or indexed by search engines.',
                            'answers' => [
                                ['answer' => 'The file will be automatically deleted after 30 days', 'is_correct' => false],
                                ['answer' => 'Anyone who obtains the URL can access it, including via forwarding or search engines', 'is_correct' => true],
                                ['answer' => 'The file owner loses edit access', 'is_correct' => false],
                                ['answer' => 'Extra bandwidth charges', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is "Shadow IT"?',
                            'explanation' => 'Shadow IT refers to cloud services used without IT knowledge or approval.',
                            'answers' => [
                                ['answer' => 'A type of malware hiding in cloud services', 'is_correct' => false],
                                ['answer' => 'Cloud services used by employees without IT approval', 'is_correct' => true],
                                ['answer' => 'A secret IT security team', 'is_correct' => false],
                                ['answer' => 'An automatic background backup system', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which form of multi-factor authentication is the most secure?',
                            'explanation' => 'Hardware security keys resist phishing, SIM swapping, and man-in-the-middle attacks.',
                            'answers' => [
                                ['answer' => 'SMS text message codes', 'is_correct' => false],
                                ['answer' => 'Email-based verification codes', 'is_correct' => false],
                                ['answer' => 'Hardware security keys (e.g., YubiKey)', 'is_correct' => true],
                                ['answer' => 'Security questions', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Before authorizing a third-party app to access your cloud account, what should you do?',
                            'explanation' => 'Always review permissions and only authorize IT-approved apps.',
                            'answers' => [
                                ['answer' => 'Click Allow immediately', 'is_correct' => false],
                                ['answer' => 'Check if the app is IT-approved and review its permission requests', 'is_correct' => true],
                                ['answer' => 'Use your personal account instead', 'is_correct' => false],
                                ['answer' => 'Disable MFA to simplify the process', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'ai-security-awareness',
                'title' => 'AI Security & Safe Usage',
                'description' => 'Learn the security risks of AI tools like ChatGPT, Copilot, and other generative AI services, and how to use them safely in the workplace.',
                'objectives' => [
                    'Understand the data privacy risks of AI tools',
                    'Recognize AI-powered social engineering attacks',
                    'Follow organizational policies for AI tool usage',
                    'Identify deepfakes and AI-generated misinformation',
                ],
                'category' => 'Data Protection & Privacy',
                'difficulty' => 'beginner',
                'duration_minutes' => 25,
                'passing_score' => 70,
                'is_mandatory' => true,
                'sort_order' => 31,
                'lessons' => [
                    [
                        'slug' => 'ai-data-privacy-risks',
                        'title' => 'AI Tools and Data Privacy',
                        'duration_minutes' => 8,
                        'content' => '<h2>The Hidden Risks of AI Tools</h2>
<p>AI tools like ChatGPT, Google Gemini, and Microsoft Copilot are powerful productivity boosters — but they come with significant data privacy risks that every employee must understand.</p>

<h3>What Happens to Your Data?</h3>
<p>When you type something into a public AI tool, your input may be:</p>
<ul>
<li><strong>Stored on external servers</strong> — Often in a different country with different privacy laws</li>
<li><strong>Used to train future models</strong> — Your confidential data could influence responses to other users</li>
<li><strong>Retained indefinitely</strong> — Even after you delete your conversation</li>
<li><strong>Accessed by the AI provider\'s employees</strong> — For quality review and model improvement</li>
</ul>

<h3>What You Should NEVER Put Into Public AI Tools</h3>
<ul>
<li>Source code, API keys, or system architecture details</li>
<li>Customer data, PII (personally identifiable information)</li>
<li>Financial data, business strategies, or M&A plans</li>
<li>Internal communications, meeting notes with sensitive content</li>
<li>Legal documents, contracts, or compliance-related information</li>
</ul>

<h3>Safe Alternatives</h3>
<p>If your organization provides enterprise AI tools (e.g., Azure OpenAI, enterprise ChatGPT), use those — they typically have data protection agreements that prevent your data from being used for training. Always check with IT before using any AI tool for work.</p>',
                    ],
                    [
                        'slug' => 'ai-powered-attacks',
                        'title' => 'AI-Powered Social Engineering',
                        'duration_minutes' => 8,
                        'content' => '<h2>How Attackers Use AI</h2>
<p>Criminals are using AI to make their attacks more sophisticated and harder to detect.</p>

<h3>AI-Enhanced Phishing</h3>
<ul>
<li><strong>Perfect grammar and tone</strong> — AI generates flawless phishing emails in any language, eliminating the spelling errors that used to be red flags</li>
<li><strong>Personalized at scale</strong> — AI scrapes social media to craft targeted messages for thousands of victims simultaneously</li>
<li><strong>Conversation bots</strong> — AI chatbots can engage in realistic back-and-forth conversations to build trust before requesting credentials</li>
</ul>

<h3>Deepfakes</h3>
<p>AI can now create convincing fake audio and video:</p>
<ul>
<li><strong>Voice cloning</strong> — A few seconds of audio is enough to clone someone\'s voice. Attackers have used this to impersonate CEOs on phone calls requesting wire transfers.</li>
<li><strong>Video deepfakes</strong> — Realistic fake video calls where the attacker appears to be a known colleague or executive.</li>
</ul>

<h3>How to Protect Yourself</h3>
<ul>
<li>Verify unusual requests through a separate channel (call them back on a known number)</li>
<li>Establish code words for high-value transactions</li>
<li>Be skeptical of urgent audio/video requests, especially involving money</li>
<li>Look for subtle artifacts: unnatural blinking, lip sync issues, robotic speech patterns</li>
</ul>',
                    ],
                    [
                        'slug' => 'responsible-ai-usage',
                        'title' => 'Responsible AI Usage at Work',
                        'duration_minutes' => 7,
                        'content' => '<h2>Using AI Tools Responsibly</h2>
<p>AI can be a tremendous asset when used correctly. Follow these guidelines to get the benefits while managing the risks.</p>

<h3>Before Using Any AI Tool</h3>
<ol>
<li><strong>Check your organization\'s AI policy</strong> — Know which tools are approved and what restrictions apply</li>
<li><strong>Classify your data</strong> — Is the information you want to input public, internal, confidential, or restricted?</li>
<li><strong>Use enterprise versions</strong> — When available, these have stronger data protections</li>
</ol>

<h3>While Using AI Tools</h3>
<ul>
<li><strong>Sanitize your prompts</strong> — Remove names, account numbers, and specific details before asking AI for help</li>
<li><strong>Verify AI outputs</strong> — AI can "hallucinate" — generate plausible-sounding but incorrect information</li>
<li><strong>Do not blindly trust AI-generated code</strong> — It may contain security vulnerabilities</li>
<li><strong>Keep a human in the loop</strong> — AI should assist decisions, not make them</li>
</ul>

<h3>AI and Intellectual Property</h3>
<p>Be aware that AI-generated content may raise IP questions:</p>
<ul>
<li>AI outputs may inadvertently reproduce copyrighted material</li>
<li>Content generated by AI may not be eligible for copyright protection</li>
<li>Your organization may have specific rules about AI-generated work product</li>
</ul>',
                    ],
                ],
                'quiz' => [
                    'title' => 'AI Security & Safe Usage Quiz',
                    'questions' => [
                        [
                            'question' => 'What is the main data privacy risk of using public AI tools for work?',
                            'explanation' => 'Data entered into public AI tools may be stored, used for training, and accessed by the provider\'s staff.',
                            'answers' => [
                                ['answer' => 'AI tools are too slow for business use', 'is_correct' => false],
                                ['answer' => 'Your inputs may be stored, used for training, and accessed by provider staff', 'is_correct' => true],
                                ['answer' => 'AI tools always give incorrect answers', 'is_correct' => false],
                                ['answer' => 'AI tools cost too much per query', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'How are attackers using AI voice cloning?',
                            'explanation' => 'Attackers clone the voices of executives to make fraudulent phone calls requesting wire transfers or sensitive information.',
                            'answers' => [
                                ['answer' => 'To improve their customer service hold music', 'is_correct' => false],
                                ['answer' => 'To impersonate executives on phone calls requesting urgent wire transfers', 'is_correct' => true],
                                ['answer' => 'To create better-quality podcast recordings', 'is_correct' => false],
                                ['answer' => 'To translate phone calls into other languages', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do before entering any data into an AI tool?',
                            'explanation' => 'Always check your organization\'s AI policy and classify the data first to avoid inputting confidential information.',
                            'answers' => [
                                ['answer' => 'Nothing — AI tools are always safe to use', 'is_correct' => false],
                                ['answer' => 'Check your organization\'s AI policy and classify the data sensitivity', 'is_correct' => true],
                                ['answer' => 'Ask the AI tool if it is secure', 'is_correct' => false],
                                ['answer' => 'Use a VPN to hide your identity', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you not blindly trust AI-generated code?',
                            'explanation' => 'AI-generated code may contain security vulnerabilities, bugs, or use outdated practices.',
                            'answers' => [
                                ['answer' => 'AI-generated code is always written in the wrong language', 'is_correct' => false],
                                ['answer' => 'AI code runs slower than human-written code', 'is_correct' => false],
                                ['answer' => 'It may contain security vulnerabilities or incorrect logic', 'is_correct' => true],
                                ['answer' => 'Companies cannot legally use AI-generated code', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'How can you verify an urgent video call is not a deepfake?',
                            'explanation' => 'Contact the person through a separate, known channel to verify the request is genuine.',
                            'answers' => [
                                ['answer' => 'Ask the person on the call to confirm their identity', 'is_correct' => false],
                                ['answer' => 'Check if the video quality is HD', 'is_correct' => false],
                                ['answer' => 'Verify through a separate channel — call them back on a known number', 'is_correct' => true],
                                ['answer' => 'Deepfakes are impossible to detect, so you cannot verify', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'zero-trust-security-principles',
                'title' => 'Zero Trust Security Principles',
                'description' => 'Understand the Zero Trust security model and how it changes the way we think about network access, identity verification, and data protection.',
                'objectives' => [
                    'Explain the core principle of "never trust, always verify"',
                    'Understand why traditional perimeter security is insufficient',
                    'Apply Zero Trust principles in daily work activities',
                    'Support your organization\'s Zero Trust initiatives',
                ],
                'category' => 'Password & Authentication',
                'difficulty' => 'intermediate',
                'duration_minutes' => 25,
                'passing_score' => 70,
                'sort_order' => 32,
                'lessons' => [
                    [
                        'slug' => 'why-zero-trust',
                        'title' => 'Why Zero Trust?',
                        'duration_minutes' => 7,
                        'content' => '<h2>The Problem with Traditional Security</h2>
<p>Traditional security followed a "castle and moat" approach: build a strong perimeter (firewall), and once you are inside, you are trusted. This model has fundamental flaws in the modern workplace.</p>

<h3>Why the Perimeter Model Fails</h3>
<ul>
<li><strong>Remote work</strong> — Employees work from home, coffee shops, and airports — outside the perimeter</li>
<li><strong>Cloud services</strong> — Data lives in the cloud, not behind a corporate firewall</li>
<li><strong>BYOD</strong> — Personal devices access corporate resources</li>
<li><strong>Lateral movement</strong> — Once an attacker gets inside, they move freely across the network</li>
</ul>

<h3>The Zero Trust Principle</h3>
<p><strong>"Never trust, always verify."</strong></p>
<p>Zero Trust assumes that no user, device, or network connection should be automatically trusted — even if they are inside the corporate network. Every access request must be verified based on:</p>
<ul>
<li><strong>Identity</strong> — Who is requesting access?</li>
<li><strong>Device</strong> — Is the device secure and compliant?</li>
<li><strong>Context</strong> — Is this request normal for this user at this time?</li>
<li><strong>Least privilege</strong> — Grant only the minimum access needed</li>
</ul>',
                    ],
                    [
                        'slug' => 'zero-trust-in-practice',
                        'title' => 'Zero Trust in Your Daily Work',
                        'duration_minutes' => 8,
                        'content' => '<h2>How Zero Trust Affects You</h2>
<p>Zero Trust may mean more verification steps in your day, but each one protects you and the organization.</p>

<h3>What You May Experience</h3>
<ul>
<li><strong>More frequent authentication</strong> — Logging in more often, especially when switching contexts</li>
<li><strong>MFA everywhere</strong> — Multi-factor authentication on more applications and services</li>
<li><strong>Conditional access</strong> — Being asked for additional verification when accessing from new locations or devices</li>
<li><strong>Granular permissions</strong> — Access to specific files or folders rather than entire drives</li>
<li><strong>Device compliance checks</strong> — Your device may need to pass security checks before connecting</li>
</ul>

<h3>Why These Steps Matter</h3>
<p>Each verification step is a checkpoint that can stop an attacker. If your credentials are stolen:</p>
<ul>
<li>MFA blocks the attacker at login</li>
<li>Conditional access flags the unusual location</li>
<li>Least privilege limits what they can reach even if they get in</li>
<li>Continuous monitoring detects abnormal behavior patterns</li>
</ul>

<h3>Supporting Zero Trust</h3>
<ul>
<li>Embrace MFA and additional verification — they protect you</li>
<li>Keep your devices updated and compliant with security policies</li>
<li>Report any unusual access prompts or authentication requests</li>
<li>Do not try to work around access controls, even for convenience</li>
</ul>',
                    ],
                    [
                        'slug' => 'micro-segmentation-least-privilege',
                        'title' => 'Micro-Segmentation & Least Privilege',
                        'duration_minutes' => 7,
                        'content' => '<h2>Limiting the Blast Radius</h2>
<p>Two key Zero Trust strategies ensure that even if a breach occurs, the damage is contained.</p>

<h3>Micro-Segmentation</h3>
<p>Instead of one big open network, the network is divided into small, isolated segments. Think of it as a submarine: if one compartment floods, watertight doors prevent the whole ship from sinking.</p>
<ul>
<li>HR systems are separated from engineering systems</li>
<li>Production servers are isolated from development servers</li>
<li>Guest Wi-Fi is completely separated from corporate networks</li>
<li>Each segment has its own access controls</li>
</ul>

<h3>Least Privilege Access</h3>
<p>You should have access only to what you need for your specific job, and only for as long as you need it.</p>
<ul>
<li><strong>Just-in-time access</strong> — Request elevated access when needed, with automatic expiration</li>
<li><strong>Role-based access</strong> — Permissions tied to your job role, not given individually</li>
<li><strong>Regular access reviews</strong> — Periodic audits to remove access that is no longer needed</li>
</ul>

<h3>Your Role</h3>
<ul>
<li>Request only the access you need — resist the temptation to ask for "just in case" access</li>
<li>Return or release access when a project ends</li>
<li>Participate in access reviews when asked by IT</li>
<li>Report if you discover you have access to systems you should not</li>
</ul>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Zero Trust Security Principles Quiz',
                    'questions' => [
                        [
                            'question' => 'What is the core principle of Zero Trust security?',
                            'explanation' => 'Zero Trust assumes no user, device, or connection should be automatically trusted.',
                            'answers' => [
                                ['answer' => 'Trust employees inside the office but not remote workers', 'is_correct' => false],
                                ['answer' => 'Never trust, always verify — every access request must be authenticated', 'is_correct' => true],
                                ['answer' => 'Trust is established once and never needs to be reverified', 'is_correct' => false],
                                ['answer' => 'Only trust devices purchased by the company', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is the traditional "castle and moat" security model no longer sufficient?',
                            'explanation' => 'Remote work, cloud services, and BYOD mean employees and data exist outside the traditional perimeter.',
                            'answers' => [
                                ['answer' => 'Because firewalls have become too expensive', 'is_correct' => false],
                                ['answer' => 'Because employees, data, and devices now exist outside the corporate perimeter', 'is_correct' => true],
                                ['answer' => 'Because attackers have stopped targeting corporate networks', 'is_correct' => false],
                                ['answer' => 'Because governments have banned perimeter security', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is micro-segmentation?',
                            'explanation' => 'Micro-segmentation divides the network into small, isolated segments so a breach in one area cannot spread.',
                            'answers' => [
                                ['answer' => 'Breaking large files into smaller pieces for faster transfer', 'is_correct' => false],
                                ['answer' => 'Dividing the network into isolated segments so breaches cannot spread', 'is_correct' => true],
                                ['answer' => 'Installing multiple antivirus programs on one computer', 'is_correct' => false],
                                ['answer' => 'Creating separate email accounts for each project', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is "just-in-time access"?',
                            'explanation' => 'Just-in-time access grants elevated permissions only when needed and automatically revokes them afterward.',
                            'answers' => [
                                ['answer' => 'Granting permanent admin access to save time', 'is_correct' => false],
                                ['answer' => 'Requesting elevated access only when needed, with automatic expiration', 'is_correct' => true],
                                ['answer' => 'Accessing systems only during business hours', 'is_correct' => false],
                                ['answer' => 'Getting access exactly when a new employee starts', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why might you experience more frequent login prompts in a Zero Trust environment?',
                            'explanation' => 'Zero Trust requires continuous verification, so you are re-authenticated more often, especially when changing context.',
                            'answers' => [
                                ['answer' => 'Because the IT system is broken and keeps logging you out', 'is_correct' => false],
                                ['answer' => 'Because Zero Trust continuously verifies identity, especially when context changes', 'is_correct' => true],
                                ['answer' => 'Because your password is too short', 'is_correct' => false],
                                ['answer' => 'Because the company wants to reduce productivity', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'secure-communication-practices',
                'title' => 'Secure Communication Practices',
                'description' => 'Learn to communicate securely using email, messaging apps, video calls, and file sharing. Protect sensitive conversations from eavesdropping and interception.',
                'objectives' => [
                    'Choose the right communication channel for sensitive information',
                    'Verify recipient identity before sharing confidential data',
                    'Recognize when a communication channel may be compromised',
                    'Apply encryption and security features in common tools',
                ],
                'category' => 'Phishing & Email Security',
                'difficulty' => 'beginner',
                'duration_minutes' => 25,
                'passing_score' => 70,
                'sort_order' => 33,
                'lessons' => [
                    [
                        'slug' => 'choosing-secure-channels',
                        'title' => 'Choosing the Right Communication Channel',
                        'duration_minutes' => 8,
                        'content' => '<h2>Not All Channels Are Equal</h2>
<p>Different types of information require different levels of security. Choosing the wrong channel can expose sensitive data.</p>

<h3>Channel Security Levels</h3>
<table>
<tr><th>Channel</th><th>Security Level</th><th>Good For</th></tr>
<tr><td>End-to-end encrypted messaging (Signal)</td><td>High</td><td>Sensitive internal discussions</td></tr>
<tr><td>Corporate email with TLS</td><td>Medium-High</td><td>Business communications</td></tr>
<tr><td>Corporate chat (Teams, Slack)</td><td>Medium</td><td>Internal collaboration</td></tr>
<tr><td>Personal email (Gmail, Yahoo)</td><td>Low</td><td>Never use for work</td></tr>
<tr><td>SMS text messages</td><td>Low</td><td>MFA codes only, not sensitive data</td></tr>
<tr><td>Social media DMs</td><td>Very Low</td><td>Never use for work</td></tr>
</table>

<h3>Rules of Thumb</h3>
<ul>
<li>Use corporate-approved channels for all work communication</li>
<li>Never discuss confidential information over personal messaging apps</li>
<li>For highly sensitive data, confirm the channel is encrypted</li>
<li>When in doubt, ask your security team which channel to use</li>
</ul>',
                    ],
                    [
                        'slug' => 'verifying-identities',
                        'title' => 'Verifying Who You Are Talking To',
                        'duration_minutes' => 8,
                        'content' => '<h2>Trust but Verify</h2>
<p>Attackers regularly impersonate colleagues, executives, and vendors. Before sharing sensitive information, verify you are communicating with the real person.</p>

<h3>Verification Techniques</h3>
<ul>
<li><strong>Call back on a known number</strong> — If you receive an unusual email request, call the sender on their known office or mobile number (not the number in the email)</li>
<li><strong>Check email headers</strong> — Look at the actual sender address, not just the display name</li>
<li><strong>Use a different channel</strong> — Verify an email request via chat, or a chat request via phone</li>
<li><strong>Pre-arranged code words</strong> — For high-value transactions, establish verification phrases</li>
</ul>

<h3>Red Flags in Communication</h3>
<ul>
<li>Urgency: "This must be done NOW, no time to verify"</li>
<li>Secrecy: "Don\'t tell anyone about this request"</li>
<li>Authority: "The CEO personally asked for this"</li>
<li>Unusual requests: Wire transfers, credential sharing, disabling security features</li>
<li>Pressure: "You\'ll be responsible if this doesn\'t happen"</li>
</ul>

<h3>The Two-Channel Rule</h3>
<p>For any request involving money, credentials, or sensitive data: <strong>always verify through a second, independent channel</strong>. If someone emails asking for a wire transfer, call them. If they call asking for credentials, email them to confirm. Attackers rarely control multiple channels simultaneously.</p>',
                    ],
                    [
                        'slug' => 'secure-file-transfer',
                        'title' => 'Secure File Sharing & Transfer',
                        'duration_minutes' => 7,
                        'content' => '<h2>Sharing Files Safely</h2>
<p>Files often contain the most sensitive organizational data. How you share them matters as much as who you share them with.</p>

<h3>Approved vs. Unapproved Methods</h3>
<ul>
<li><strong>DO</strong>: Use your organization\'s approved file sharing platform (SharePoint, Google Workspace, approved SFTP)</li>
<li><strong>DO</strong>: Set specific permissions — share with named individuals, not "anyone with a link"</li>
<li><strong>DO</strong>: Use password-protected archives for sensitive attachments, sending the password via a different channel</li>
<li><strong>DON\'T</strong>: Email sensitive files as plain attachments to external recipients</li>
<li><strong>DON\'T</strong>: Use personal file sharing services (personal Dropbox, WeTransfer)</li>
<li><strong>DON\'T</strong>: Share files via social media or messaging apps</li>
</ul>

<h3>Before You Hit Send</h3>
<ol>
<li><strong>Double-check the recipient</strong> — Autocomplete can select the wrong person</li>
<li><strong>Review the file contents</strong> — Make sure it does not contain hidden data (metadata, tracked changes, hidden sheets)</li>
<li><strong>Classify the data</strong> — Is this file appropriate for the chosen sharing method?</li>
<li><strong>Set an expiration</strong> — Remove access after it is no longer needed</li>
</ol>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Secure Communication Practices Quiz',
                    'questions' => [
                        [
                            'question' => 'You receive an email from your CEO urgently requesting a wire transfer. What should you do first?',
                            'explanation' => 'Always verify unusual financial requests through a separate, known channel.',
                            'answers' => [
                                ['answer' => 'Process the transfer immediately — the CEO is waiting', 'is_correct' => false],
                                ['answer' => 'Reply to the email asking for confirmation', 'is_correct' => false],
                                ['answer' => 'Call the CEO on their known phone number to verify the request', 'is_correct' => true],
                                ['answer' => 'Forward the email to your team for a group decision', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the "two-channel rule" for verifying sensitive requests?',
                            'explanation' => 'Verify any request involving money, credentials, or sensitive data through a second, independent communication channel.',
                            'answers' => [
                                ['answer' => 'Send the same message twice for confirmation', 'is_correct' => false],
                                ['answer' => 'Verify through a second, independent communication channel', 'is_correct' => true],
                                ['answer' => 'Have two people approve every request', 'is_correct' => false],
                                ['answer' => 'Use two passwords for every account', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of these is the LEAST secure way to share sensitive work files?',
                            'explanation' => 'Personal email accounts lack corporate security controls and may violate compliance requirements.',
                            'answers' => [
                                ['answer' => 'Corporate SharePoint with named sharing', 'is_correct' => false],
                                ['answer' => 'Approved SFTP server', 'is_correct' => false],
                                ['answer' => 'Password-protected archive via corporate email', 'is_correct' => false],
                                ['answer' => 'Personal Gmail account', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question' => 'What should you check before sending a file to an external recipient?',
                            'explanation' => 'Files may contain hidden metadata, tracked changes, or data in hidden sheets that you did not intend to share.',
                            'answers' => [
                                ['answer' => 'Only the file name', 'is_correct' => false],
                                ['answer' => 'The recipient, file contents, hidden data (metadata/tracked changes), and data classification', 'is_correct' => true],
                                ['answer' => 'Just the file size to make sure it will send', 'is_correct' => false],
                                ['answer' => 'Whether the file is in PDF format', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which communication channel is generally the most secure for sensitive business discussions?',
                            'explanation' => 'End-to-end encrypted messaging ensures that only the sender and recipient can read the messages.',
                            'answers' => [
                                ['answer' => 'SMS text messages', 'is_correct' => false],
                                ['answer' => 'Social media direct messages', 'is_correct' => false],
                                ['answer' => 'End-to-end encrypted messaging (e.g., Signal)', 'is_correct' => true],
                                ['answer' => 'Personal email', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
