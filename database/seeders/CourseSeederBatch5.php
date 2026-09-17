<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Database\Seeder;

class CourseSeederBatch5 extends Seeder
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
            // ── Module 23: Clean Desk & Secure Workspace ──
            [
                'title' => 'Clean Desk & Secure Workspace',
                'slug' => 'clean-desk-secure-workspace',
                'description' => 'Learn how to maintain a clean desk and secure workspace to protect sensitive information from physical exposure and unauthorized access.',
                'objectives' => [
                    'Understand the purpose and importance of a clean desk policy',
                    'Secure screens, devices, and workstations when stepping away',
                    'Properly dispose of sensitive documents using approved methods',
                    'Reduce the risk of information exposure in shared office environments',
                ],
                'category' => 'Physical Security & Workplace Safety',
                'difficulty' => 'beginner',
                'duration_minutes' => 15,
                'passing_score' => 70,
                'sort_order' => 23,
                'lessons' => [
                    [
                        'title' => 'The Clean Desk Policy',
                        'slug' => 'the-clean-desk-policy',
                        'duration_minutes' => 5,
                        'content' => '<h3>The Clean Desk Policy</h3>
<p>A clean desk policy requires employees to clear their workspaces of sensitive documents, removable media, and confidential materials whenever they leave their desk unattended. This straightforward practice is one of the most effective physical security controls an organization can implement. It reduces the risk that visitors, cleaning staff, or unauthorized individuals can see, photograph, or take sensitive information left in plain sight.</p>

<h3>Why Clean Desks Matter</h3>
<p>Consider what sits on the average office desk during a busy workday: printed reports with financial data, sticky notes with passwords or account numbers, client contracts, employee records, USB drives, and notebooks with meeting notes that may contain strategic plans. Each of these items is a potential source of data exposure. A visitor walking through the office, a contractor performing maintenance, or even a colleague without the appropriate clearance could glance at or photograph any of these items. In open-plan offices, the risk is even greater because there are fewer physical barriers between workstations.</p>

<h3>What a Clean Desk Policy Covers</h3>
<ul>
<li><strong>Paper documents:</strong> All printed materials containing sensitive information must be filed in locked drawers or cabinets when you leave your desk, even for a short break</li>
<li><strong>Removable media:</strong> USB drives, external hard drives, CDs, and SD cards must be stored securely and never left plugged in to unattended machines</li>
<li><strong>Notebooks and sticky notes:</strong> Handwritten notes often contain passwords, phone numbers, account details, or meeting summaries that should not be visible</li>
<li><strong>Whiteboards:</strong> Erase whiteboards after meetings, especially those containing project details, architecture diagrams, or strategic plans</li>
<li><strong>Personal devices:</strong> Phones, tablets, and laptops should be locked or secured when you step away</li>
</ul>

<h3>Making It a Habit</h3>
<p>The most effective approach is to build clean desk practices into your daily routine. Before leaving for lunch, take thirty seconds to file documents and lock your drawers. At the end of each day, do a quick sweep of your desk surface. Many organizations conduct periodic clean desk audits where security teams walk through the office after hours and flag desks with exposed sensitive materials. Treating a clean desk as a professional habit rather than a burden makes compliance natural and consistent.</p>',
                    ],
                    [
                        'title' => 'Securing Screens & Devices',
                        'slug' => 'securing-screens-and-devices',
                        'duration_minutes' => 5,
                        'content' => '<h3>Securing Screens & Devices</h3>
<p>An unlocked computer screen is an open door to your organization\'s data. When you walk away from your workstation without locking the screen, anyone who passes by can read your emails, access your files, browse your applications, and even send messages as you. Screen security is a critical complement to a clean desk policy because digital information is just as vulnerable to shoulder surfing and opportunistic access as paper documents.</p>

<h3>Lock Your Screen Every Time</h3>
<p>Locking your screen should become as automatic as locking your car when you park it. On Windows, press Windows+L. On macOS, press Control+Command+Q. These keyboard shortcuts take less than a second and immediately protect everything on your screen. Many organizations configure automatic screen locks that activate after a period of inactivity, typically five to fifteen minutes, but you should never rely solely on the timeout. Lock your screen manually every time you stand up, even if you are just walking to the printer or getting coffee. An attacker or opportunistic insider needs only seconds to access an unlocked machine.</p>

<h3>Privacy Screens and Monitor Positioning</h3>
<p>Privacy screen filters are thin overlays that attach to your monitor and narrow the viewing angle so that only someone sitting directly in front of the screen can read it. Anyone viewing from the side sees only a darkened screen. These are essential for employees who handle sensitive data in open-plan offices, co-working spaces, or while traveling. Position your monitor so that it does not face windows, hallways, or high-traffic areas where passersby could read the screen content.</p>

<h3>Mobile Device Security</h3>
<ul>
<li><strong>Enable biometric or PIN lock:</strong> Every phone and tablet should require authentication to unlock, with a timeout of no more than two minutes</li>
<li><strong>Enable remote wipe:</strong> If your device is lost or stolen, you or your IT department should be able to erase its contents remotely</li>
<li><strong>Disable lock screen notifications:</strong> Email previews and message notifications displayed on a locked screen can expose sensitive information to anyone who picks up the device</li>
<li><strong>Never leave devices unattended:</strong> In public places like coffee shops, airports, or conferences, take your devices with you even for short breaks</li>
</ul>

<p>Remember that screen security protects not just your own work but also the data of colleagues, clients, and partners whose information may be visible in your applications. Treat every screen as a window that needs closing when you step away.</p>',
                    ],
                    [
                        'title' => 'Proper Document Disposal',
                        'slug' => 'proper-document-disposal',
                        'duration_minutes' => 5,
                        'content' => '<h3>Proper Document Disposal</h3>
<p>Improper disposal of documents is one of the most overlooked security vulnerabilities in any organization. Throwing sensitive papers into a regular trash bin or recycling container makes them accessible to anyone who handles the waste, from cleaning crews to dumpster divers specifically targeting corporate discards. Proper document disposal ensures that confidential information is destroyed beyond recovery before it leaves your control.</p>

<h3>Cross-Cut Shredding</h3>
<p>The standard for secure document destruction is cross-cut shredding, which cuts paper both horizontally and vertically into small particles. Strip-cut shredders, which produce long ribbons, are not considered secure because determined attackers can reconstruct documents from strips. Most offices provide cross-cut shredders or secure shredding bins that are collected and destroyed by certified document destruction services. Always use the designated shredding method rather than tearing documents by hand, which leaves pieces large enough to be reassembled.</p>

<h3>What Must Be Shredded</h3>
<ul>
<li><strong>Documents with personal data:</strong> Anything containing names, addresses, Social Security numbers, account numbers, or employee information</li>
<li><strong>Financial records:</strong> Invoices, bank statements, expense reports, and budget documents</li>
<li><strong>Contracts and legal documents:</strong> Drafts, signed agreements, and correspondence related to legal matters</li>
<li><strong>Internal communications:</strong> Printed emails, memos, and meeting minutes that contain strategic, personnel, or financial information</li>
<li><strong>Drafts and working copies:</strong> Early versions of documents often contain information that was deliberately removed from the final version</li>
</ul>

<h3>Digital Media Disposal</h3>
<p>Paper is not the only medium requiring secure disposal. USB drives, hard drives, CDs, DVDs, and backup tapes all contain data that persists after simple deletion. Simply deleting files or formatting a drive does not destroy the data; it merely marks the space as available for reuse, and the original data can be recovered with freely available tools. Secure disposal of digital media requires physical destruction (shredding or degaussing for magnetic media) or certified data wiping that overwrites the entire storage medium multiple times.</p>

<p>When in doubt about whether a document or device needs secure disposal, treat it as sensitive. The cost of shredding one extra piece of paper is negligible compared to the cost of a data breach caused by improperly discarded information.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Clean Desk & Secure Workspace Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the primary purpose of a clean desk policy?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A clean desk policy prevents sensitive information from being exposed to unauthorized individuals such as visitors, cleaning staff, or passersby.',
                            'answers' => [
                                ['answer' => 'To keep the office looking tidy for visitors', 'is_correct' => false],
                                ['answer' => 'To prevent sensitive information from being exposed to unauthorized individuals', 'is_correct' => true],
                                ['answer' => 'To reduce clutter and improve employee productivity', 'is_correct' => false],
                                ['answer' => 'To comply with fire safety regulations about clear desk surfaces', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the fastest way to lock your screen on a Windows computer?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Windows+L is the keyboard shortcut that instantly locks your Windows workstation.',
                            'answers' => [
                                ['answer' => 'Ctrl+Alt+Delete and then click Lock', 'is_correct' => false],
                                ['answer' => 'Press the power button briefly', 'is_correct' => false],
                                ['answer' => 'Press Windows+L', 'is_correct' => true],
                                ['answer' => 'Close the laptop lid and wait for it to sleep', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is strip-cut shredding considered insufficient for sensitive documents?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Strip-cut shredders produce long ribbons that a determined attacker can piece back together to reconstruct the original document.',
                            'answers' => [
                                ['answer' => 'Strip-cut shredders are too slow for office use', 'is_correct' => false],
                                ['answer' => 'Strip-cut shredders produce long ribbons that can be reassembled to reconstruct documents', 'is_correct' => true],
                                ['answer' => 'Strip-cut shredders damage the shredding mechanism over time', 'is_correct' => false],
                                ['answer' => 'Strip-cut shredders cannot handle stapled documents', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a privacy screen filter used for?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Privacy screen filters narrow the viewing angle so only someone sitting directly in front of the monitor can read it, preventing shoulder surfing.',
                            'answers' => [
                                ['answer' => 'To reduce eye strain from blue light emitted by monitors', 'is_correct' => false],
                                ['answer' => 'To prevent others from reading your screen by narrowing the viewing angle', 'is_correct' => true],
                                ['answer' => 'To block malware from displaying on your screen', 'is_correct' => false],
                                ['answer' => 'To improve the resolution and color accuracy of your display', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is simply deleting files from a USB drive not considered secure disposal?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Deleting files only marks the space as available for reuse. The original data remains on the drive and can be recovered with freely available tools.',
                            'answers' => [
                                ['answer' => 'Because the files move to a recycle bin on the USB drive', 'is_correct' => false],
                                ['answer' => 'Because deleting files only marks the space as reusable and the data can be recovered with recovery tools', 'is_correct' => true],
                                ['answer' => 'Because USB drives do not support file deletion', 'is_correct' => false],
                                ['answer' => 'Because the files are automatically backed up to the cloud before deletion', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 24: Visitor Management & Access Control ──
            [
                'title' => 'Visitor Management & Access Control',
                'slug' => 'visitor-management-access-control',
                'description' => 'Learn proper procedures for managing visitors, controlling physical access to your facility, and reporting unauthorized access attempts.',
                'objectives' => [
                    'Follow proper visitor escort and sign-in procedures',
                    'Understand how access control systems protect your facility',
                    'Identify and report unauthorized access attempts',
                    'Apply badge and identification verification practices',
                ],
                'category' => 'Physical Security & Workplace Safety',
                'difficulty' => 'beginner',
                'duration_minutes' => 15,
                'passing_score' => 70,
                'sort_order' => 24,
                'lessons' => [
                    [
                        'title' => 'Visitor Escort Procedures',
                        'slug' => 'visitor-escort-procedures',
                        'duration_minutes' => 5,
                        'content' => '<h3>Visitor Escort Procedures</h3>
<p>Every person who enters your workplace but is not an employee represents a potential security risk if not properly managed. Visitors include clients, vendors, contractors, delivery personnel, job candidates, and guests. A robust visitor management process ensures that every non-employee is identified, authorized, tracked, and supervised throughout their visit. This protects sensitive areas, confidential information, and the physical safety of everyone on the premises.</p>

<h3>The Visitor Sign-In Process</h3>
<p>All visitors should sign in at a designated reception area before entering the workplace. The sign-in process should capture the visitor\'s full name, the company or organization they represent, the employee they are visiting, the purpose of their visit, and the date and time of arrival. Many organizations use electronic visitor management systems that capture a photo, print a temporary badge, and automatically notify the host employee that their guest has arrived. The visitor log creates an audit trail that can be reviewed during security investigations.</p>

<h3>Escorting Visitors</h3>
<ul>
<li><strong>Meet at reception:</strong> Always collect your visitor from the reception area personally rather than giving them directions to find your desk or meeting room</li>
<li><strong>Stay with your visitor:</strong> Visitors should be accompanied by their host at all times while in non-public areas. Never leave a visitor unattended in a workspace, server room, or restricted area</li>
<li><strong>Limit access to necessary areas:</strong> Take visitors only to the areas relevant to their visit. There is no reason for a delivery driver to walk through the engineering floor</li>
<li><strong>Escort to exit:</strong> When the visit is complete, walk your visitor back to reception and ensure they sign out and return their temporary badge</li>
</ul>

<h3>Temporary Badges</h3>
<p>Visitor badges serve two critical functions: they identify the wearer as a non-employee so staff can recognize them, and they often encode access limitations that restrict which doors the badge can open. Temporary badges should be visually distinct from employee badges, typically using a different color or a large "VISITOR" label. They should expire at the end of the day and be collected at sign-out. Unreturned badges should be deactivated immediately and the incident documented.</p>

<p>If you encounter someone in your workplace without a visible badge, do not assume they belong there. Politely ask if you can help them or direct them to reception. This small action is one of the most effective security measures any employee can take.</p>',
                    ],
                    [
                        'title' => 'Access Control Systems',
                        'slug' => 'access-control-systems',
                        'duration_minutes' => 5,
                        'content' => '<h3>Access Control Systems</h3>
<p>Access control systems are the technology and procedures that determine who can enter specific areas of your facility and when. These systems replace simple lock-and-key arrangements with electronic controls that can be managed centrally, audited thoroughly, and adjusted instantly. Understanding how these systems work helps you appreciate why certain procedures exist and how to use them correctly.</p>

<h3>Types of Access Control</h3>
<ul>
<li><strong>Card-based systems:</strong> Proximity cards or smart cards are the most common form of electronic access control. You tap or swipe your card at a reader to unlock a door. Each card has a unique identifier linked to your access permissions in a central database</li>
<li><strong>Biometric systems:</strong> Fingerprint scanners, facial recognition, and iris scanners verify your physical identity rather than something you carry. These are used in high-security areas because biometrics cannot be shared, lost, or stolen like a card</li>
<li><strong>PIN and keypad systems:</strong> A numeric code is entered to gain access. PINs are often used as a second factor alongside a card for sensitive areas, requiring both something you have and something you know</li>
<li><strong>Mobile credentials:</strong> Smartphone-based access uses Bluetooth or NFC to communicate with door readers, eliminating the need for a physical card. The credential is stored securely on the phone and protected by the device\'s own lock screen</li>
</ul>

<h3>Zone-Based Access</h3>
<p>Most organizations divide their facility into security zones with different access levels. Public areas like the lobby and visitor meeting rooms require no badge. General office space requires a valid employee badge. Sensitive areas like finance, HR, and executive offices may require additional authorization. High-security zones like server rooms, data centers, and research labs typically require multi-factor authentication and are restricted to specific personnel. This layered approach ensures that even if someone gains access to the building, they cannot reach the most sensitive areas without additional credentials.</p>

<h3>Your Responsibilities</h3>
<p>Never share your access card or PIN with anyone, including colleagues. If a coworker has forgotten their badge, direct them to security or reception to obtain a temporary credential rather than lending yours. Report a lost or stolen badge to security immediately so it can be deactivated. Never prop open doors that are controlled by access systems, even if it seems more convenient for foot traffic. Each of these practices ensures that the access control system can do its job of tracking who is where and when.</p>',
                    ],
                    [
                        'title' => 'Reporting Unauthorized Access',
                        'slug' => 'reporting-unauthorized-access',
                        'duration_minutes' => 5,
                        'content' => '<h3>Reporting Unauthorized Access</h3>
<p>Detecting and reporting unauthorized access is every employee\'s responsibility. Access control systems and security cameras provide technical monitoring, but they cannot replace the observation skills of people who work in the facility every day. You know who belongs in your area, what normal activity looks like, and when something seems out of place. That awareness makes you one of the most valuable components of your organization\'s physical security.</p>

<h3>Signs of Unauthorized Access</h3>
<ul>
<li><strong>Unfamiliar individuals without badges:</strong> Someone walking through your workspace without a visible employee or visitor badge is the most obvious sign of potential unauthorized access</li>
<li><strong>Tailgating attempts:</strong> Someone trying to follow an employee through a badge-controlled door without using their own credential</li>
<li><strong>Doors found propped open:</strong> Secure doors that are wedged or propped open defeat access controls and may indicate someone is maintaining unauthorized entry</li>
<li><strong>Unusual after-hours activity:</strong> People in the office at unusual times, particularly in areas they do not normally work in</li>
<li><strong>Tampered locks or readers:</strong> Damage to door locks, card readers, or security cameras may indicate an attempted or successful breach</li>
<li><strong>Unfamiliar vehicles in restricted parking areas:</strong> Vehicles without proper permits in areas designated for employees or specific departments</li>
</ul>

<h3>How to Report</h3>
<p>When you observe something suspicious, report it promptly through your organization\'s established channels. Most companies have a security hotline, a dedicated email address, or an incident reporting system. If the situation appears to involve an immediate threat, call building security or emergency services directly. When reporting, provide as much detail as possible: the time, location, a description of the individual or activity, the direction they were heading, and any identifying features such as clothing or equipment they were carrying.</p>

<h3>Overcoming the Bystander Effect</h3>
<p>Many people hesitate to report suspicious activity because they worry about being wrong or causing inconvenience. This is known as the bystander effect, and it is one of the biggest obstacles to effective physical security. Remember that security teams would rather investigate ten false alarms than miss one genuine intrusion. You are not accusing anyone of a crime by reporting something unusual. You are simply ensuring that trained professionals can assess the situation. Organizations that foster a culture of reporting without blame consistently have stronger security postures than those where employees stay silent.</p>

<p>Make sure you know your organization\'s reporting procedures before an incident occurs. Knowing the security desk phone number, the incident reporting portal, and the emergency procedures means you can act quickly and confidently when something does not look right.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Visitor Management & Access Control Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What should you do when your visitor arrives at the building?',
                            'type' => 'multiple_choice',
                            'explanation' => 'You should meet your visitor at reception personally rather than giving them directions to navigate through the office alone.',
                            'answers' => [
                                ['answer' => 'Send them directions to your desk via text message', 'is_correct' => false],
                                ['answer' => 'Ask a colleague near reception to point them in the right direction', 'is_correct' => false],
                                ['answer' => 'Meet them at reception personally and escort them to the meeting area', 'is_correct' => true],
                                ['answer' => 'Have them wait outside until you finish your current task', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A colleague asks to borrow your access badge because they forgot theirs at home. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Sharing access badges undermines access control systems. Direct your colleague to security or reception for a temporary credential.',
                            'answers' => [
                                ['answer' => 'Lend them your badge for the day since you trust them', 'is_correct' => false],
                                ['answer' => 'Direct them to security or reception to obtain a temporary badge', 'is_correct' => true],
                                ['answer' => 'Let them tailgate through doors behind you throughout the day', 'is_correct' => false],
                                ['answer' => 'Give them your PIN code so they can enter without a badge', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should visitor badges be visually distinct from employee badges?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Visually distinct visitor badges allow any employee to quickly identify non-employees and ensure they are being properly escorted.',
                            'answers' => [
                                ['answer' => 'To make visitors feel welcome with a specially designed badge', 'is_correct' => false],
                                ['answer' => 'So that any employee can quickly identify non-employees and verify they are being escorted', 'is_correct' => true],
                                ['answer' => 'Because visitor badges use different technology than employee badges', 'is_correct' => false],
                                ['answer' => 'To limit the number of badges the company needs to produce', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the primary advantage of zone-based access control?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Zone-based access creates layers of security so that gaining access to one area does not automatically grant access to more sensitive areas.',
                            'answers' => [
                                ['answer' => 'It reduces the number of doors that need electronic locks', 'is_correct' => false],
                                ['answer' => 'It allows visitors to navigate the building without an escort', 'is_correct' => false],
                                ['answer' => 'It creates layers of security so access to one zone does not grant access to more sensitive zones', 'is_correct' => true],
                                ['answer' => 'It eliminates the need for security cameras in the building', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why do many people hesitate to report suspicious activity in the workplace?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The bystander effect causes people to hesitate because they worry about being wrong or causing inconvenience, but security teams prefer investigating false alarms over missing real threats.',
                            'answers' => [
                                ['answer' => 'Because reporting systems are too complicated to use', 'is_correct' => false],
                                ['answer' => 'Because security teams discourage unnecessary reports', 'is_correct' => false],
                                ['answer' => 'Because of the bystander effect — they worry about being wrong or causing inconvenience', 'is_correct' => true],
                                ['answer' => 'Because most suspicious activity turns out to be a security drill', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 25: Incident Response Basics ──
            [
                'title' => 'Incident Response Basics',
                'slug' => 'incident-response-basics',
                'description' => 'Understand what constitutes a security incident, learn the incident response lifecycle, and know your role when an incident occurs.',
                'objectives' => [
                    'Define what constitutes a security incident versus a normal event',
                    'Describe the phases of the incident response lifecycle',
                    'Know your specific responsibilities when a security incident is detected',
                    'Follow proper escalation and communication procedures during incidents',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'intermediate',
                'duration_minutes' => 25,
                'passing_score' => 75,
                'is_mandatory' => true,
                'sort_order' => 25,
                'lessons' => [
                    [
                        'title' => 'What Is a Security Incident',
                        'slug' => 'what-is-a-security-incident',
                        'duration_minutes' => 8,
                        'content' => '<h3>What Is a Security Incident</h3>
<p>A security incident is any event that threatens the confidentiality, integrity, or availability of an organization\'s information systems and data. Not every unusual event qualifies as an incident. A failed login attempt might be someone mistyping their password, but hundreds of failed login attempts against multiple accounts in rapid succession is an incident. Understanding the distinction between routine events and genuine incidents helps you respond appropriately and avoid both complacency and unnecessary alarm.</p>

<h3>Common Types of Security Incidents</h3>
<ul>
<li><strong>Malware infections:</strong> A computer becomes infected with ransomware, spyware, a trojan, or a virus that compromises its normal operation or the data it stores</li>
<li><strong>Phishing compromises:</strong> An employee clicks a malicious link or provides credentials to a fraudulent site, potentially giving attackers access to internal systems</li>
<li><strong>Unauthorized access:</strong> Someone gains access to systems, data, or physical spaces without proper authorization, whether through stolen credentials, exploitation of vulnerabilities, or social engineering</li>
<li><strong>Data breaches:</strong> Sensitive data is exposed, stolen, or transmitted to unauthorized parties, whether through a deliberate attack or an accidental disclosure</li>
<li><strong>Denial of service:</strong> Systems or networks are overwhelmed with traffic or requests, making them unavailable to legitimate users</li>
<li><strong>Insider threats:</strong> A current or former employee, contractor, or partner misuses their authorized access to harm the organization</li>
</ul>

<h3>Events vs. Incidents</h3>
<p>Security teams distinguish between events and incidents. An event is any observable occurrence in a system or network, such as a user logging in, a firewall blocking a connection, or an antivirus scan completing. Most events are routine and benign. An incident is an event or series of events that actually threatens or harms the organization\'s security posture. The transition from event to incident often depends on context, frequency, and correlation. A single blocked connection is an event. Thousands of blocked connections from the same source targeting different ports is an incident.</p>

<p>Your ability to recognize when something crosses the line from ordinary to suspicious is a vital skill. If your computer suddenly starts running slowly, programs crash unexpectedly, unfamiliar software appears, or you receive security alerts you did not trigger, these could be signs of a security incident that warrant immediate reporting to your IT or security team.</p>',
                    ],
                    [
                        'title' => 'The Incident Response Process',
                        'slug' => 'the-incident-response-process',
                        'duration_minutes' => 9,
                        'content' => '<h3>The Incident Response Process</h3>
<p>Incident response follows a structured lifecycle that helps organizations handle security incidents systematically rather than reacting in an ad hoc manner. The widely adopted NIST framework defines four phases: Preparation, Detection and Analysis, Containment Eradication and Recovery, and Post-Incident Activity. Each phase has specific objectives and activities that ensure incidents are managed effectively from start to finish.</p>

<h3>Phase 1: Preparation</h3>
<p>Preparation happens before any incident occurs. It involves building the team, tools, and procedures needed to respond effectively. This includes creating an incident response plan that defines roles and responsibilities, establishing communication channels, deploying monitoring and detection tools, and conducting regular training exercises and simulations. Organizations that invest in preparation respond faster and more effectively when real incidents occur. A key part of preparation is ensuring every employee knows how to recognize and report potential incidents.</p>

<h3>Phase 2: Detection and Analysis</h3>
<p>Detection involves identifying that a security incident has occurred or is in progress. This can happen through automated alerts from security tools, reports from employees who notice something unusual, notifications from external parties such as law enforcement or security researchers, or discovery during routine audits. Once detected, the incident response team analyzes the incident to determine its scope, severity, and potential impact. This analysis guides the response strategy and determines what level of resources to mobilize.</p>

<h3>Phase 3: Containment, Eradication, and Recovery</h3>
<ul>
<li><strong>Containment:</strong> Stop the incident from spreading or causing further damage. This might mean isolating affected systems from the network, disabling compromised accounts, or blocking malicious IP addresses</li>
<li><strong>Eradication:</strong> Remove the root cause of the incident. This could involve removing malware, patching vulnerabilities, closing unauthorized access points, or resetting compromised credentials</li>
<li><strong>Recovery:</strong> Restore affected systems and services to normal operation. This includes restoring data from backups, rebuilding compromised systems, and monitoring closely for any signs that the attacker has maintained persistence</li>
</ul>

<h3>Phase 4: Post-Incident Activity</h3>
<p>After the incident is resolved, the team conducts a post-incident review, sometimes called a lessons-learned meeting or retrospective. The goal is to understand what happened, what worked well in the response, what could be improved, and what changes should be made to prevent similar incidents. This phase produces updated procedures, improved detection rules, additional training needs, and a documented incident report. Organizations that skip this phase are doomed to repeat the same mistakes.</p>',
                    ],
                    [
                        'title' => 'Your Role in Incident Response',
                        'slug' => 'your-role-in-incident-response',
                        'duration_minutes' => 8,
                        'content' => '<h3>Your Role in Incident Response</h3>
<p>You do not need to be a cybersecurity expert to play a critical role in incident response. As an employee, you are often the first person to notice that something is wrong. Your prompt action in recognizing, reporting, and supporting the response to a security incident can mean the difference between a minor disruption and a major breach. Every minute of delay in reporting gives an attacker more time to expand their access and cause greater damage.</p>

<h3>Step 1: Recognize and Report Immediately</h3>
<p>When you suspect a security incident, report it to your IT or security team right away using your organization\'s established reporting channels. This might be a dedicated security hotline, a ticketing system, an email address, or a chat channel. Do not try to investigate or fix the problem yourself, as well-intentioned actions can destroy evidence or worsen the situation. Common triggers for reporting include receiving a suspicious email you may have clicked on, noticing unfamiliar programs or processes on your computer, seeing unexpected account activity or password change notifications, discovering that files are missing or modified, or observing someone accessing areas or systems they should not be in.</p>

<h3>Step 2: Preserve Evidence</h3>
<ul>
<li><strong>Do not turn off your computer:</strong> If your machine may be compromised, leave it powered on but disconnect it from the network by unplugging the Ethernet cable or disabling Wi-Fi. Shutting down can destroy volatile evidence in memory that investigators need</li>
<li><strong>Do not delete anything:</strong> Even if you see suspicious files or emails, do not delete them. They are evidence that the incident response team needs to analyze</li>
<li><strong>Document what you saw:</strong> Write down what happened, when it happened, and what actions you took. Note any error messages, unusual behavior, or suspicious communications. Screenshots are especially valuable</li>
<li><strong>Preserve the suspicious email:</strong> If the incident involves a phishing email, do not forward it. Use your email client\'s "Report Phishing" button or forward it as an attachment to your security team</li>
</ul>

<h3>Step 3: Follow Instructions</h3>
<p>Once you have reported the incident, follow the instructions provided by your security team. They may ask you to change your passwords, avoid using certain systems, provide additional information, or continue working normally while they investigate. Cooperate fully and honestly. The security team is not looking to assign blame; they are trying to understand and contain the incident. If you made a mistake, such as clicking a phishing link, admitting it immediately helps the team respond faster and more effectively.</p>

<p>Remember that your organization\'s incident response plan exists to handle these situations. Trust the process, report promptly, and support the team. That is the most valuable contribution any employee can make during a security incident.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Incident Response Basics Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the key difference between a security event and a security incident?',
                            'type' => 'multiple_choice',
                            'explanation' => 'An event is any observable occurrence, while an incident is an event or series of events that actually threatens the organization\'s security.',
                            'answers' => [
                                ['answer' => 'Events are digital while incidents are physical', 'is_correct' => false],
                                ['answer' => 'An event is any observable occurrence; an incident actually threatens or harms the organization\'s security', 'is_correct' => true],
                                ['answer' => 'Events happen during business hours while incidents happen after hours', 'is_correct' => false],
                                ['answer' => 'Events are caused by outsiders while incidents are caused by insiders', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'If you suspect your computer has been compromised by malware, what should you do with the machine?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Leave the computer powered on to preserve volatile evidence in memory, but disconnect it from the network to prevent the malware from spreading.',
                            'answers' => [
                                ['answer' => 'Immediately shut it down and restart to clear the malware', 'is_correct' => false],
                                ['answer' => 'Keep it powered on but disconnect it from the network', 'is_correct' => true],
                                ['answer' => 'Continue working normally and monitor for more symptoms', 'is_correct' => false],
                                ['answer' => 'Run your own antivirus scan before contacting anyone', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which phase of incident response involves conducting a lessons-learned review?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Post-Incident Activity is the final phase where the team reviews what happened and identifies improvements to prevent similar incidents.',
                            'answers' => [
                                ['answer' => 'Preparation', 'is_correct' => false],
                                ['answer' => 'Detection and Analysis', 'is_correct' => false],
                                ['answer' => 'Containment, Eradication, and Recovery', 'is_correct' => false],
                                ['answer' => 'Post-Incident Activity', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question' => 'You accidentally clicked a link in a phishing email. What is the most important thing to do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Report the incident immediately and honestly to your security team. Admitting the mistake quickly allows the team to contain the damage before it spreads.',
                            'answers' => [
                                ['answer' => 'Delete the email and hope nothing happened', 'is_correct' => false],
                                ['answer' => 'Try to investigate the link yourself to assess the damage', 'is_correct' => false],
                                ['answer' => 'Report it immediately and honestly to your security team', 'is_correct' => true],
                                ['answer' => 'Wait to see if anything unusual happens before reporting', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is the Preparation phase important even though no incident is happening?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Preparation builds the team, tools, plans, and skills needed to respond effectively. Organizations that prepare respond faster and suffer less damage during real incidents.',
                            'answers' => [
                                ['answer' => 'It is not important — preparation only wastes time and resources', 'is_correct' => false],
                                ['answer' => 'It ensures the organization has the team, tools, and procedures ready to respond quickly and effectively when incidents occur', 'is_correct' => true],
                                ['answer' => 'It is only needed to satisfy audit requirements', 'is_correct' => false],
                                ['answer' => 'It focuses on punishing employees who caused previous incidents', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 26: Data Breach Notification ──
            [
                'title' => 'Data Breach Notification',
                'slug' => 'data-breach-notification',
                'description' => 'Understand what constitutes a data breach, learn about legal notification requirements, and know the steps for post-breach recovery.',
                'objectives' => [
                    'Define what constitutes a data breach under major regulations',
                    'Understand notification requirements and timelines under GDPR, PDPA, and other laws',
                    'Describe the steps involved in post-breach investigation and recovery',
                    'Know your role in supporting the breach notification process',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'intermediate',
                'duration_minutes' => 20,
                'passing_score' => 75,
                'sort_order' => 26,
                'lessons' => [
                    [
                        'title' => 'Understanding Data Breaches',
                        'slug' => 'understanding-data-breaches',
                        'duration_minutes' => 7,
                        'content' => '<h3>Understanding Data Breaches</h3>
<p>A data breach is a security incident in which sensitive, protected, or confidential data is accessed, disclosed, or stolen by an unauthorized party. Data breaches can result from external attacks such as hacking and malware, internal threats such as employees misusing access, or accidental exposures such as misconfigured databases or emails sent to the wrong recipient. Regardless of the cause, a data breach can have severe consequences including regulatory fines, legal liability, reputational damage, and loss of customer trust.</p>

<h3>What Counts as a Data Breach</h3>
<p>Under most data protection regulations, a data breach occurs whenever personal data is subject to unauthorized access, disclosure, alteration, or destruction. This includes obvious scenarios like a hacker stealing a customer database, but it also includes less dramatic situations that many employees might not immediately recognize as breaches:</p>
<ul>
<li><strong>Misdirected emails:</strong> Sending a spreadsheet of employee salary data to the wrong person is a data breach, even if the recipient is a trusted colleague</li>
<li><strong>Lost or stolen devices:</strong> A laptop containing unencrypted customer records that is lost at an airport or stolen from a car is a data breach</li>
<li><strong>Improper disposal:</strong> Throwing printed documents with personal data into regular trash rather than shredding them can constitute a breach</li>
<li><strong>Unauthorized viewing:</strong> An employee accessing patient medical records out of curiosity rather than for a legitimate business purpose is a breach of confidentiality</li>
<li><strong>Cloud misconfigurations:</strong> A storage bucket or database left open to the internet without authentication exposes all the data it contains</li>
</ul>

<h3>The Scale of the Problem</h3>
<p>Data breaches affect organizations of every size and industry. The average cost of a data breach in 2024 reached 4.88 million US dollars globally according to IBM\'s annual report, with healthcare and financial services sectors consistently facing the highest costs. Beyond direct financial impact, breached organizations experience an average customer churn increase of three to five percent, and it takes an average of 292 days to identify and contain a breach. These numbers underscore why prevention, rapid detection, and effective response are all critical components of data protection.</p>

<p>Understanding that a data breach encompasses far more than a dramatic hacking event helps you stay alert to everyday situations that could trigger breach notification obligations for your organization.</p>',
                    ],
                    [
                        'title' => 'Notification Requirements & Timelines',
                        'slug' => 'notification-requirements-timelines',
                        'duration_minutes' => 7,
                        'content' => '<h3>Notification Requirements & Timelines</h3>
<p>When a data breach occurs, organizations are typically required by law to notify both the relevant regulatory authorities and the affected individuals. The specific requirements vary by jurisdiction, but the trend worldwide is toward stricter notification obligations with shorter timelines. Failing to notify within the required period can result in additional penalties on top of those imposed for the breach itself.</p>

<h3>GDPR (European Union)</h3>
<p>The General Data Protection Regulation requires organizations to notify the relevant supervisory authority within 72 hours of becoming aware of a breach involving personal data, unless the breach is unlikely to result in a risk to individuals\' rights and freedoms. If the breach is likely to result in a high risk to affected individuals, those individuals must also be notified without undue delay. The notification must describe the nature of the breach, the categories and approximate number of individuals affected, the likely consequences, and the measures taken to address it.</p>

<h3>PDPA (Singapore and Thailand)</h3>
<p>Singapore\'s Personal Data Protection Act requires notification to the Personal Data Protection Commission and affected individuals as soon as practicable if the breach is of a significant scale or involves sensitive data. Thailand\'s PDPA similarly requires notification to the relevant authority within 72 hours. Both regulations emphasize that organizations must assess the severity of the breach and notify promptly when there is a risk of harm to individuals.</p>

<h3>Other Key Regulations</h3>
<ul>
<li><strong>CCPA/CPRA (California):</strong> Requires notification to affected California residents whose unencrypted personal information was accessed. The state Attorney General must also be notified if more than 500 residents are affected</li>
<li><strong>HIPAA (US Healthcare):</strong> Requires notification to affected individuals within 60 days. Breaches affecting 500 or more individuals must also be reported to the HHS Secretary and media outlets</li>
<li><strong>NDB (Australia):</strong> The Notifiable Data Breaches scheme requires notification to the Office of the Australian Information Commissioner and affected individuals when a breach is likely to cause serious harm</li>
</ul>

<h3>What Notification Must Include</h3>
<p>Regardless of jurisdiction, breach notifications generally must include: what happened and when, what types of data were involved, what the organization is doing to address the breach, what steps affected individuals can take to protect themselves, and how individuals can contact the organization for more information. Clear, honest, and timely communication during a breach is not just a legal requirement but a critical factor in maintaining trust with customers and partners.</p>',
                    ],
                    [
                        'title' => 'Post-Breach Recovery',
                        'slug' => 'post-breach-recovery',
                        'duration_minutes' => 6,
                        'content' => '<h3>Post-Breach Recovery</h3>
<p>Recovering from a data breach extends far beyond restoring systems and patching vulnerabilities. It involves a coordinated effort across technical, legal, communications, and business teams to understand what happened, meet regulatory obligations, support affected individuals, and strengthen defenses against future incidents. The quality of an organization\'s post-breach response often determines whether it retains the trust of its customers, partners, and regulators.</p>

<h3>Immediate Recovery Steps</h3>
<ul>
<li><strong>Forensic investigation:</strong> A thorough investigation determines exactly what happened, what data was affected, how the breach occurred, and whether the attacker still has access. This investigation must be conducted carefully to preserve evidence for potential legal proceedings</li>
<li><strong>System restoration:</strong> Compromised systems are rebuilt or restored from clean backups. All compromised credentials are reset. Vulnerabilities that enabled the breach are patched. Additional monitoring is deployed to detect any signs of continued attacker presence</li>
<li><strong>Legal and regulatory response:</strong> The legal team coordinates notification to regulatory authorities within required timelines, prepares notification letters for affected individuals, and manages any resulting regulatory inquiries or investigations</li>
<li><strong>Stakeholder communication:</strong> Beyond regulatory notification, the organization communicates with employees, business partners, investors, and the public as appropriate. Transparent communication helps maintain trust even in difficult circumstances</li>
</ul>

<h3>Supporting Affected Individuals</h3>
<p>Organizations typically offer affected individuals resources to help them protect themselves. This may include free credit monitoring services, identity theft protection, a dedicated hotline for questions and concerns, and clear guidance on steps individuals can take such as changing passwords, monitoring account statements, and placing fraud alerts. Providing meaningful support demonstrates that the organization takes its responsibility to protect personal data seriously.</p>

<h3>Long-Term Improvements</h3>
<p>Every breach should drive lasting improvements to the organization\'s security posture. The post-incident review identifies root causes and systemic weaknesses that allowed the breach to occur. This leads to updated security policies, enhanced technical controls, improved training programs, and revised incident response procedures. Organizations that treat breaches as learning opportunities emerge with stronger defenses than they had before. Those that merely patch the immediate vulnerability without addressing underlying issues are likely to experience repeat incidents.</p>

<p>As an employee, your role in post-breach recovery may include cooperating with investigators, following updated security procedures, completing additional training, and supporting colleagues who may be affected. Your patience and cooperation during this challenging period are essential to the organization\'s recovery.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Data Breach Notification Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'Which of the following scenarios constitutes a data breach?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Sending personal data to the wrong recipient is a data breach because it involves unauthorized disclosure of personal information.',
                            'answers' => [
                                ['answer' => 'A firewall blocking an attempted intrusion from the internet', 'is_correct' => false],
                                ['answer' => 'An employee accidentally emailing a spreadsheet of customer data to the wrong recipient', 'is_correct' => true],
                                ['answer' => 'An employee failing to log in after three incorrect password attempts', 'is_correct' => false],
                                ['answer' => 'A scheduled system maintenance window that takes the website offline', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Under GDPR, how quickly must an organization notify the supervisory authority after becoming aware of a data breach?',
                            'type' => 'multiple_choice',
                            'explanation' => 'GDPR requires notification to the supervisory authority within 72 hours of becoming aware of a breach involving personal data.',
                            'answers' => [
                                ['answer' => '24 hours', 'is_correct' => false],
                                ['answer' => '72 hours', 'is_correct' => true],
                                ['answer' => '7 days', 'is_correct' => false],
                                ['answer' => '30 days', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is a forensic investigation important after a data breach?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Forensic investigation determines exactly what happened, what data was affected, how the breach occurred, and whether the attacker still has access.',
                            'answers' => [
                                ['answer' => 'To assign blame to the employee who caused the breach', 'is_correct' => false],
                                ['answer' => 'To determine what happened, what data was affected, and whether the attacker still has access', 'is_correct' => true],
                                ['answer' => 'To calculate the exact financial losses from the breach', 'is_correct' => false],
                                ['answer' => 'To satisfy insurance companies before they process a claim', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should an organization typically offer individuals affected by a data breach?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Organizations typically offer credit monitoring, identity theft protection, a dedicated hotline, and guidance on self-protective steps.',
                            'answers' => [
                                ['answer' => 'A formal apology letter and nothing else', 'is_correct' => false],
                                ['answer' => 'Automatic compensation of one thousand dollars per person', 'is_correct' => false],
                                ['answer' => 'Credit monitoring, identity theft protection, and guidance on protective steps they can take', 'is_correct' => true],
                                ['answer' => 'Free access to the organization\'s products for one year', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A laptop containing unencrypted customer records is stolen from an employee\'s car. Is this a data breach?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Yes. A stolen device containing unencrypted personal data is a data breach because the data is now accessible to an unauthorized party.',
                            'answers' => [
                                ['answer' => 'No, because no hacking was involved', 'is_correct' => false],
                                ['answer' => 'No, because the thief probably wanted the laptop, not the data', 'is_correct' => false],
                                ['answer' => 'Yes, because unencrypted personal data is now accessible to an unauthorized party', 'is_correct' => true],
                                ['answer' => 'Only if the customer data includes financial information', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 27: Regulatory Compliance Overview ──
            [
                'title' => 'Regulatory Compliance Overview',
                'slug' => 'regulatory-compliance-overview',
                'description' => 'Gain a broad understanding of key data protection regulations including GDPR, PDPA, and CCPA, and learn how to build a culture of compliance in your organization.',
                'objectives' => [
                    'Describe the core principles of GDPR, PDPA, and CCPA',
                    'Identify industry-specific compliance requirements relevant to your role',
                    'Understand how compliance affects daily work activities and data handling',
                    'Contribute to building and maintaining a culture of compliance',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'intermediate',
                'duration_minutes' => 25,
                'passing_score' => 75,
                'sort_order' => 27,
                'lessons' => [
                    [
                        'title' => 'Key Regulations (GDPR, PDPA, CCPA)',
                        'slug' => 'key-regulations-gdpr-pdpa-ccpa',
                        'duration_minutes' => 9,
                        'content' => '<h3>Key Regulations: GDPR, PDPA, CCPA</h3>
<p>Data protection regulations establish the legal framework for how organizations collect, process, store, and share personal information. These laws exist to protect individuals\' privacy rights and hold organizations accountable for the data they handle. Even if your role does not involve direct data processing, understanding these regulations helps you make better decisions about how you handle information in your daily work.</p>

<h3>GDPR (General Data Protection Regulation)</h3>
<p>The GDPR, effective since May 2018, is the European Union\'s comprehensive data protection law that has become the global benchmark for privacy regulation. It applies to any organization that processes the personal data of EU residents, regardless of where the organization is located. Key principles include lawfulness and transparency in data processing, purpose limitation (data collected for one purpose cannot be used for another without consent), data minimization (collect only what is necessary), accuracy, storage limitation, and security. Violations can result in fines of up to 20 million euros or four percent of global annual turnover, whichever is higher. The GDPR also gives individuals strong rights including the right to access their data, correct inaccuracies, request deletion, and object to processing.</p>

<h3>PDPA (Personal Data Protection Act)</h3>
<p>Several countries in the Asia-Pacific region have enacted Personal Data Protection Acts. Singapore\'s PDPA, one of the most established, governs the collection, use, and disclosure of personal data by organizations. It requires organizations to obtain consent before collecting personal data, allow individuals to access and correct their data, protect data with reasonable security measures, and limit retention to the period necessary for the purpose. Thailand\'s PDPA, which became fully effective in 2022, follows a similar model with strong individual rights and mandatory breach notification. Both laws reflect the global trend toward stronger privacy protections and carry significant penalties for non-compliance.</p>

<h3>CCPA/CPRA (California Consumer Privacy Act / California Privacy Rights Act)</h3>
<p>California\'s privacy laws give residents significant control over their personal information. The CCPA, enhanced by the CPRA in 2023, gives consumers the right to know what personal data is collected about them, request deletion of their data, opt out of the sale or sharing of their data, and not be discriminated against for exercising these rights. The law applies to businesses meeting certain thresholds related to revenue, data volume, or the proportion of revenue derived from selling personal data. It has influenced privacy legislation across other US states and at the federal level.</p>

<p>While these regulations differ in their specifics, they share common themes: transparency, consent, data minimization, security, and individual rights. Treating these principles as universal best practices ensures compliance across multiple jurisdictions.</p>',
                    ],
                    [
                        'title' => 'Industry-Specific Requirements',
                        'slug' => 'industry-specific-requirements',
                        'duration_minutes' => 8,
                        'content' => '<h3>Industry-Specific Requirements</h3>
<p>Beyond general data protection laws, many industries have their own regulatory frameworks that impose additional requirements on how data is handled, stored, and protected. These regulations reflect the unique sensitivity of data in each sector and the potential consequences of its misuse. If your organization operates in a regulated industry, you must comply with both the general data protection laws and the industry-specific requirements that apply to your sector.</p>

<h3>Healthcare: HIPAA</h3>
<p>The Health Insurance Portability and Accountability Act applies to healthcare providers, health plans, healthcare clearinghouses, and their business associates in the United States. HIPAA establishes strict rules for protecting Protected Health Information (PHI), including medical records, treatment histories, and insurance information. The Privacy Rule governs who can access PHI and under what circumstances. The Security Rule requires administrative, physical, and technical safeguards for electronic PHI. Violations can result in fines ranging from one hundred dollars to fifty thousand dollars per violation, with annual maximums reaching 1.5 million dollars per violation category.</p>

<h3>Finance: PCI-DSS and SOX</h3>
<p>The Payment Card Industry Data Security Standard applies to any organization that stores, processes, or transmits credit card data. It requires measures including encryption of cardholder data, access controls, regular vulnerability testing, and network segmentation. Non-compliance can result in fines from card brands and loss of the ability to process card payments. The Sarbanes-Oxley Act requires publicly traded companies to maintain internal controls over financial reporting, with criminal penalties for executives who certify fraudulent financial statements.</p>

<h3>Government and Defense</h3>
<p>Organizations handling government data or working on defense contracts face stringent requirements. In the United States, frameworks like FedRAMP for cloud services and CMMC (Cybersecurity Maturity Model Certification) for defense contractors mandate specific security controls, assessments, and certifications. Handling classified information requires additional personnel clearances, physical security measures, and information handling procedures defined by national security regulations.</p>

<h3>Cross-Industry Standards</h3>
<ul>
<li><strong>ISO 27001:</strong> An international standard for information security management systems that provides a framework for managing security risks. Certification demonstrates to customers and partners that an organization follows recognized security practices</li>
<li><strong>SOC 2:</strong> A reporting framework for service organizations that demonstrates controls over security, availability, processing integrity, confidentiality, and privacy. SOC 2 reports are frequently requested by customers evaluating the security of cloud services and SaaS providers</li>
</ul>

<p>Understanding which regulations apply to your organization and your role helps you make informed decisions about how to handle data every day. When in doubt about whether a particular action complies with your industry\'s requirements, consult your compliance or legal team before proceeding.</p>',
                    ],
                    [
                        'title' => 'Building a Compliance Culture',
                        'slug' => 'building-a-compliance-culture',
                        'duration_minutes' => 8,
                        'content' => '<h3>Building a Compliance Culture</h3>
<p>Compliance is not a checkbox exercise or a responsibility that belongs solely to the legal and security teams. True compliance is a culture, a shared set of values and behaviors where every employee understands why data protection matters and integrates compliance practices into their daily work. Organizations with strong compliance cultures experience fewer incidents, respond more effectively when incidents occur, and earn greater trust from customers, partners, and regulators.</p>

<h3>From Obligation to Ownership</h3>
<p>The shift from viewing compliance as a burden to treating it as a shared responsibility begins with understanding the "why" behind the rules. Regulations exist because real people suffer real harm when their data is mishandled. A leaked medical record can lead to discrimination. A stolen financial identity can take years to recover from. A disclosed home address can endanger someone\'s physical safety. When employees understand that compliance protects real people, they are more likely to follow procedures willingly rather than looking for shortcuts.</p>

<h3>Practical Steps for Every Employee</h3>
<ul>
<li><strong>Know your data:</strong> Understand what types of personal and sensitive data you handle in your role. If you are unsure whether something is sensitive, treat it as if it is and ask your compliance team</li>
<li><strong>Follow data handling procedures:</strong> Use approved systems for storing and sharing data. Do not create shadow copies in personal cloud storage, local spreadsheets, or messaging apps</li>
<li><strong>Practice data minimization:</strong> Collect and retain only the data you actually need for your specific purpose. Delete or archive data when it is no longer required</li>
<li><strong>Report concerns promptly:</strong> If you notice a potential compliance issue, whether it is a misconfigured sharing setting, an improper data request, or a process that does not follow policy, report it through the appropriate channels</li>
<li><strong>Complete training:</strong> Compliance training is not optional. Stay current with your organization\'s training requirements and apply what you learn in your daily work</li>
</ul>

<h3>Leadership\'s Role</h3>
<p>A compliance culture starts at the top. When leaders prioritize data protection in their decisions, allocate resources for security and compliance initiatives, and hold themselves to the same standards they expect from their teams, compliance becomes part of the organizational identity rather than an afterthought. Leaders who cut corners on compliance send a message that it is not truly important, regardless of what the policies say.</p>

<h3>Continuous Improvement</h3>
<p>The regulatory landscape evolves constantly as new laws are enacted and existing ones are updated. A compliance culture embraces continuous learning and adaptation rather than treating compliance as a one-time project. Regular policy reviews, updated training, tabletop exercises, and open communication about new requirements keep the organization ahead of regulatory changes rather than scrambling to catch up after the fact.</p>

<p>Remember that compliance is ultimately about trust. Customers trust your organization with their personal information. Employees trust that their employer handles their data responsibly. Partners trust that shared data will be protected. Every time you follow a compliance procedure, you are upholding that trust.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Regulatory Compliance Overview Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the maximum fine for GDPR violations?',
                            'type' => 'multiple_choice',
                            'explanation' => 'GDPR violations can result in fines of up to 20 million euros or 4% of global annual turnover, whichever is higher.',
                            'answers' => [
                                ['answer' => '1 million euros or 1% of annual revenue', 'is_correct' => false],
                                ['answer' => '10 million euros or 2% of annual revenue', 'is_correct' => false],
                                ['answer' => '20 million euros or 4% of global annual turnover, whichever is higher', 'is_correct' => true],
                                ['answer' => '50 million euros regardless of company size', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What right does the CCPA give California consumers regarding the sale of their personal data?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The CCPA gives California consumers the right to opt out of the sale or sharing of their personal data.',
                            'answers' => [
                                ['answer' => 'The right to receive payment for their personal data', 'is_correct' => false],
                                ['answer' => 'The right to opt out of the sale or sharing of their personal data', 'is_correct' => true],
                                ['answer' => 'The right to sell their own data to competing companies', 'is_correct' => false],
                                ['answer' => 'The right to prevent companies from collecting any data about them', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does PCI-DSS apply to?',
                            'type' => 'multiple_choice',
                            'explanation' => 'PCI-DSS applies to any organization that stores, processes, or transmits credit card data.',
                            'answers' => [
                                ['answer' => 'Only banks and financial institutions', 'is_correct' => false],
                                ['answer' => 'Any organization that stores, processes, or transmits credit card data', 'is_correct' => true],
                                ['answer' => 'Only e-commerce websites that accept online payments', 'is_correct' => false],
                                ['answer' => 'Only organizations with more than 500 employees', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does "data minimization" mean in practice?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Data minimization means collecting and retaining only the personal data that is actually necessary for your specific purpose.',
                            'answers' => [
                                ['answer' => 'Using the smallest possible database to store data', 'is_correct' => false],
                                ['answer' => 'Collecting and retaining only the data actually necessary for your specific purpose', 'is_correct' => true],
                                ['answer' => 'Compressing all data files to reduce storage costs', 'is_correct' => false],
                                ['answer' => 'Deleting all data at the end of each business day', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is a compliance culture more effective than treating compliance as a checkbox exercise?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A compliance culture integrates data protection into daily decisions, resulting in fewer incidents and stronger trust, rather than just meeting minimum requirements.',
                            'answers' => [
                                ['answer' => 'Because regulators check whether organizations have a compliance culture during audits', 'is_correct' => false],
                                ['answer' => 'Because a culture integrates data protection into daily decisions, leading to fewer incidents and stronger trust', 'is_correct' => true],
                                ['answer' => 'Because it eliminates the need for formal compliance policies and procedures', 'is_correct' => false],
                                ['answer' => 'Because it reduces the cost of compliance software licenses', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
