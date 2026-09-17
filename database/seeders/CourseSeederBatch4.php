<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Database\Seeder;

class CourseSeederBatch4 extends Seeder
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
            // ── Module 18: Remote Work Security Essentials ──
            [
                'title' => 'Remote Work Security Essentials',
                'slug' => 'remote-work-security-essentials',
                'description' => 'Learn how to maintain strong cybersecurity practices while working remotely, including securing your home office, using public networks safely, and protecting collaboration tools.',
                'objectives' => [
                    'Set up a secure home office environment with proper network and device configurations',
                    'Identify and mitigate risks when using public Wi-Fi and shared networks',
                    'Apply security best practices to collaboration and communication tools',
                    'Understand your responsibilities for protecting company data outside the office',
                ],
                'category' => 'Mobile & Remote Work Security',
                'difficulty' => 'beginner',
                'duration_minutes' => 25,
                'passing_score' => 70,
                'sort_order' => 18,
                'is_mandatory' => true,
                'lessons' => [
                    [
                        'title' => 'Securing Your Home Office',
                        'slug' => 'securing-your-home-office',
                        'duration_minutes' => 8,
                        'content' => '<h3>Securing Your Home Office</h3>
<p>Working from home introduces security challenges that do not exist in a corporate office. Your employer\'s IT team has spent years hardening the office network with firewalls, intrusion detection systems, network segmentation, and physical access controls. Your home network, by default, has none of these protections. Taking a few deliberate steps to secure your home office can dramatically reduce your risk.</p>

<h3>Secure Your Home Router</h3>
<p>Your home router is the gateway between your devices and the internet, and it is your first line of defense. Start by changing the default administrator password — attackers know the factory defaults for every major router brand. Use WPA3 encryption if your router supports it, or WPA2 at minimum. Disable WPS (Wi-Fi Protected Setup), which has known vulnerabilities that allow attackers to brute-force your network password. Keep your router firmware updated, as manufacturers regularly patch security vulnerabilities. Finally, consider creating a separate Wi-Fi network for your work devices, isolating them from smart home gadgets, gaming consoles, and family devices that may have weaker security.</p>

<h3>Physical Security at Home</h3>
<ul>
<li><strong>Lock your screen:</strong> Always lock your computer when stepping away, even briefly. Family members, visitors, or anyone with physical access could inadvertently or intentionally access sensitive work data</li>
<li><strong>Secure your workspace:</strong> If you handle sensitive documents, keep them in a locked drawer or cabinet. Shred printed documents when they are no longer needed rather than placing them in household recycling</li>
<li><strong>Position your screen:</strong> Be mindful of windows and shared spaces where others might see your screen. Video calls can inadvertently reveal whiteboards, documents, or screens in the background</li>
<li><strong>Protect your devices:</strong> Do not leave laptops unattended in cars, hotel rooms, or common areas. Use a cable lock if working in a shared space</li>
</ul>

<h3>Separating Work and Personal Use</h3>
<p>Use your company-issued devices exclusively for work whenever possible. Personal devices may lack endpoint protection, disk encryption, or security configurations that your IT team has deployed on managed devices. Avoid letting family members use your work laptop for browsing, gaming, or schoolwork — a single malicious download could compromise your entire corporate account. If you must use a personal device, ensure it has up-to-date antivirus software, full-disk encryption enabled, and a strong login password.</p>

<p>Keep work data on approved company platforms and storage — do not save company files to personal cloud storage accounts or local personal drives where they fall outside organizational security controls and backup policies.</p>',
                    ],
                    [
                        'title' => 'Safe Use of Public Networks',
                        'slug' => 'safe-use-of-public-networks',
                        'duration_minutes' => 8,
                        'content' => '<h3>Safe Use of Public Networks</h3>
<p>Working from coffee shops, airports, hotels, and co-working spaces is a reality for many remote employees. However, public Wi-Fi networks are among the most dangerous environments for handling sensitive work data. These networks are typically unencrypted, meaning anyone within range can potentially intercept your traffic. Understanding the risks and knowing how to mitigate them is essential for safe remote work.</p>

<h3>Why Public Wi-Fi Is Dangerous</h3>
<p>Public Wi-Fi networks are inherently insecure for several reasons. They are often unencrypted, meaning data travels in plain text that can be captured by anyone with basic tools. Attackers can set up rogue access points — fake networks with names like "Airport_Free_WiFi" — that look legitimate but route all your traffic through their device. Even on legitimate networks, man-in-the-middle attacks allow attackers to position themselves between you and the network, intercepting and potentially modifying your communications. Session hijacking can steal your authentication cookies, giving attackers access to your logged-in accounts.</p>

<h3>Protecting Yourself on Public Networks</h3>
<ul>
<li><strong>Always use a VPN:</strong> A Virtual Private Network encrypts all traffic between your device and your company\'s network, making it unreadable to anyone intercepting it on the local network. Connect your VPN before doing any work-related activity on a public network</li>
<li><strong>Verify the network name:</strong> Ask staff for the exact network name before connecting. Attackers create convincing duplicates — "Starbucks_WiFi_Free" next to the real "Starbucks_WiFi"</li>
<li><strong>Disable auto-connect:</strong> Turn off the setting that automatically connects your device to known networks. This prevents your device from connecting to a rogue network that shares a name with one you have used before</li>
<li><strong>Use HTTPS everywhere:</strong> Ensure that every website you visit uses HTTPS (look for the padlock icon). Modern browsers can be configured to warn you or block connections to non-HTTPS sites</li>
<li><strong>Avoid sensitive transactions:</strong> Even with a VPN, avoid accessing highly sensitive systems like financial platforms or HR databases from public networks when possible</li>
</ul>

<h3>Mobile Hotspot as an Alternative</h3>
<p>When security is critical, consider using your smartphone as a personal mobile hotspot instead of public Wi-Fi. A cellular connection is significantly harder to intercept than public Wi-Fi because it uses the carrier\'s encrypted infrastructure. While not immune to all attacks, a personal hotspot eliminates the risks of shared networks, rogue access points, and local eavesdropping. Many employers will cover the additional data costs for employees who regularly work from public locations.</p>

<p>If you must use public Wi-Fi, treat every network as hostile. Assume someone is watching, and take every precaution to protect your data accordingly.</p>',
                    ],
                    [
                        'title' => 'Collaboration Tool Security',
                        'slug' => 'collaboration-tool-security',
                        'duration_minutes' => 8,
                        'content' => '<h3>Collaboration Tool Security</h3>
<p>Remote work depends heavily on collaboration platforms — video conferencing, instant messaging, file sharing, and project management tools. These tools are essential for productivity, but each one represents a potential attack surface if not used securely. Attackers increasingly target collaboration platforms because they contain a wealth of sensitive business communications, documents, and credentials.</p>

<h3>Video Conferencing Security</h3>
<p>Video conferencing became a prime target during the shift to remote work, with "Zoom-bombing" and meeting infiltration making headlines. To protect your meetings, always use meeting passwords or waiting rooms to control who can join. Never share meeting links on public channels or social media. Use unique meeting IDs for sensitive discussions rather than your personal meeting room link, which is static and can be shared. Be cautious about screen sharing — close unnecessary tabs and applications before sharing your screen, as a momentary flash of an email or document can expose sensitive information. Disable the option for participants to share their screens unless needed for the meeting\'s purpose.</p>

<h3>Messaging and Chat Security</h3>
<ul>
<li><strong>Think before you share:</strong> Messages in platforms like Slack, Teams, or Google Chat are searchable and often retained indefinitely. Do not share passwords, API keys, credit card numbers, or other sensitive credentials through chat — use a password manager or secure vault instead</li>
<li><strong>Watch for impersonation:</strong> Attackers can create accounts with display names matching your colleagues. Verify unusual requests through another channel, especially if they involve money, credentials, or data access</li>
<li><strong>Manage channel membership:</strong> Review who has access to private channels, especially those containing sensitive project or financial information. Remove former team members promptly</li>
<li><strong>Be cautious with integrations:</strong> Third-party apps and bots connected to your collaboration platform can access messages and files. Only approve integrations that are vetted by your IT team</li>
</ul>

<h3>File Sharing Best Practices</h3>
<p>When sharing files through collaboration tools, use your organization\'s approved platforms rather than personal file sharing services. Set appropriate permissions — share with specific individuals rather than "anyone with the link" whenever possible. Review shared document permissions periodically and revoke access for people who no longer need it. Be wary of files shared by external parties through collaboration tools, as these can contain malware just like email attachments.</p>

<p>Remember that everything shared through collaboration tools is typically logged and discoverable. Treat these platforms with the same level of professionalism and security awareness you would apply to email.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Remote Work Security Essentials Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the first step to securing your home router for remote work?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Changing the default administrator password is the most critical first step, as attackers know factory defaults for all major router brands.',
                            'answers' => [
                                ['answer' => 'Upgrading to the most expensive router model available', 'is_correct' => false],
                                ['answer' => 'Changing the default administrator password and enabling WPA3 or WPA2 encryption', 'is_correct' => true],
                                ['answer' => 'Hiding the router in a closet so no one can find it', 'is_correct' => false],
                                ['answer' => 'Connecting as many devices as possible to test its capacity', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you always use a VPN when connecting to public Wi-Fi?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A VPN encrypts all traffic between your device and your company\'s network, making intercepted data unreadable to attackers on the local network.',
                            'answers' => [
                                ['answer' => 'VPNs make your internet connection faster on public networks', 'is_correct' => false],
                                ['answer' => 'VPNs encrypt your traffic so it cannot be read by attackers on the same network', 'is_correct' => true],
                                ['answer' => 'VPNs automatically block all advertisements and pop-ups', 'is_correct' => false],
                                ['answer' => 'VPNs are only needed for accessing entertainment websites', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the risk of sharing meeting links on public channels?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Publicly shared meeting links allow unauthorized individuals to join, potentially eavesdropping on sensitive discussions or disrupting the meeting.',
                            'answers' => [
                                ['answer' => 'The meeting software will crash from too many participants', 'is_correct' => false],
                                ['answer' => 'There is no risk as long as the meeting has a host', 'is_correct' => false],
                                ['answer' => 'Unauthorized individuals can join and eavesdrop on sensitive discussions', 'is_correct' => true],
                                ['answer' => 'Public links expire faster than private ones', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you avoid using personal devices for work tasks?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Personal devices typically lack the endpoint protection, encryption, and security configurations that IT deploys on managed work devices.',
                            'answers' => [
                                ['answer' => 'Personal devices are always slower than work devices', 'is_correct' => false],
                                ['answer' => 'Personal devices may lack endpoint protection, disk encryption, and security configurations deployed on managed devices', 'is_correct' => true],
                                ['answer' => 'Using personal devices is illegal in most countries', 'is_correct' => false],
                                ['answer' => 'Personal devices cannot connect to the internet securely', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do if you need internet access at a coffee shop and security is critical?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A personal mobile hotspot uses encrypted cellular infrastructure, eliminating risks from shared networks and rogue access points.',
                            'answers' => [
                                ['answer' => 'Connect to the first available open Wi-Fi network', 'is_correct' => false],
                                ['answer' => 'Ask another customer to share their personal hotspot', 'is_correct' => false],
                                ['answer' => 'Use your smartphone as a personal mobile hotspot instead of public Wi-Fi', 'is_correct' => true],
                                ['answer' => 'Disable all security software to improve connection speed', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 19: Mobile Device Security ──
            [
                'title' => 'Mobile Device Security',
                'slug' => 'mobile-device-security',
                'description' => 'Understand the threats targeting mobile devices and learn how to secure your smartphone and tablet against common attacks.',
                'objectives' => [
                    'Identify the most common mobile security threats facing employees today',
                    'Configure smartphones and tablets with essential security settings',
                    'Evaluate mobile apps for safety before installing them',
                    'Respond appropriately to a lost or compromised mobile device',
                ],
                'category' => 'Mobile & Remote Work Security',
                'difficulty' => 'beginner',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'sort_order' => 19,
                'lessons' => [
                    [
                        'title' => 'Mobile Threat Landscape',
                        'slug' => 'mobile-threat-landscape',
                        'duration_minutes' => 7,
                        'content' => '<h3>Mobile Threat Landscape</h3>
<p>Mobile devices have become primary targets for cybercriminals because they contain an extraordinary concentration of sensitive data — email accounts, banking apps, authentication tokens, corporate documents, GPS location history, personal photos, and contact lists. The average smartphone holds more personal and professional information than most desktop computers, yet many users apply far less security rigor to their mobile devices.</p>

<h3>Common Mobile Threats</h3>
<ul>
<li><strong>Phishing via SMS (smishing):</strong> Attackers send text messages impersonating banks, delivery services, or employers with malicious links. These are harder to detect on mobile because small screens hide full URLs and people tend to trust text messages more than email</li>
<li><strong>Malicious apps:</strong> Trojanized apps disguised as legitimate utilities, games, or productivity tools can steal data, record keystrokes, access your camera and microphone, or encrypt your files for ransom</li>
<li><strong>Network-based attacks:</strong> Connecting to compromised or rogue Wi-Fi networks allows attackers to intercept communications, inject malware, or redirect you to fake websites</li>
<li><strong>OS vulnerabilities:</strong> Unpatched operating systems contain known vulnerabilities that attackers actively exploit. Delayed updates leave devices exposed for weeks or months after patches become available</li>
<li><strong>Physical theft:</strong> A stolen device without proper security controls gives the thief access to everything on it — email, messaging apps, saved passwords, and corporate resources</li>
</ul>

<h3>Mobile-Specific Attack Vectors</h3>
<p>Mobile devices face unique threats that desktops do not. QR codes, now ubiquitous in restaurants, parking meters, and advertisements, can redirect to phishing sites or trigger malware downloads — and users cannot inspect the destination URL before scanning. Bluetooth vulnerabilities like BlueBorne allow attackers to take control of devices without any user interaction if Bluetooth is left on and discoverable. Malicious charging stations, known as "juice jacking," can install malware or extract data when you plug your phone into a public USB charging port.</p>

<p>The mobile threat landscape continues to evolve rapidly. Mobile malware increased by over 50 percent in recent years, with banking trojans and spyware leading the way. Organizations and individuals must treat mobile security with the same seriousness they apply to traditional computing environments.</p>',
                    ],
                    [
                        'title' => 'Securing Your Smartphone & Tablet',
                        'slug' => 'securing-your-smartphone-tablet',
                        'duration_minutes' => 7,
                        'content' => '<h3>Securing Your Smartphone & Tablet</h3>
<p>Securing your mobile device is not a single action but a set of configurations and habits that together create a strong defense. Most of these steps take only a few minutes to implement but provide ongoing protection against the majority of mobile threats.</p>

<h3>Essential Security Settings</h3>
<ul>
<li><strong>Strong lock screen:</strong> Use a six-digit PIN at minimum, or preferably a passphrase. Enable biometric authentication (fingerprint or face recognition) for convenience, but always set a strong PIN or password as the backup. Avoid simple patterns — they are easily observed and reproduced</li>
<li><strong>Automatic updates:</strong> Enable automatic operating system and app updates. Security patches fix known vulnerabilities that attackers actively exploit, and delays in updating leave you exposed</li>
<li><strong>Full-disk encryption:</strong> Modern iOS and Android devices encrypt storage by default when a screen lock is set. Verify this is enabled in your device settings — encryption makes stolen data unreadable without your passcode</li>
<li><strong>Find My Device:</strong> Enable the built-in device tracking feature (Find My iPhone or Find My Device on Android). This allows you to locate, lock, or remotely wipe a lost or stolen device</li>
<li><strong>Auto-lock timer:</strong> Set your screen to lock automatically after 30 seconds to one minute of inactivity. A longer timeout increases the window for unauthorized access</li>
</ul>

<h3>Reducing Your Attack Surface</h3>
<p>Disable Bluetooth and Wi-Fi when not actively using them — both are potential entry points for attackers. Turn off location services for apps that do not genuinely need your location. Review app permissions regularly and revoke access to the camera, microphone, contacts, and files for any app that does not need them for its core function. A flashlight app does not need access to your contacts or microphone.</p>

<h3>Responding to a Lost or Stolen Device</h3>
<p>If your device is lost or stolen, act immediately. Use Find My Device to locate it and remotely lock it with a message and contact number. If recovery is unlikely, initiate a remote wipe to erase all data. Report the loss to your IT department immediately so they can revoke the device\'s access to corporate resources, disable email access, and rotate any credentials stored on the device. Change passwords for any accounts that were logged in on the device, starting with email and banking. Contact your mobile carrier to suspend the SIM card and prevent unauthorized calls or texts.</p>',
                    ],
                    [
                        'title' => 'Mobile App Safety',
                        'slug' => 'mobile-app-safety',
                        'duration_minutes' => 6,
                        'content' => '<h3>Mobile App Safety</h3>
<p>The apps you install are the single biggest factor in your mobile device\'s security posture. Every app you install is granted some level of access to your device, and malicious or poorly secured apps can leak your data, drain your battery with background activity, or serve as a backdoor for attackers. Developing good habits around app installation and management is essential.</p>

<h3>Safe App Installation Practices</h3>
<ul>
<li><strong>Use official app stores only:</strong> Download apps exclusively from the Apple App Store or Google Play Store. While not perfect, these stores have vetting processes that catch most malicious apps. Never install apps from links in text messages, emails, or unknown websites</li>
<li><strong>Check the developer:</strong> Before installing, verify the developer name matches the expected company. Fake apps often impersonate popular brands but are published by unknown developers with slight name variations</li>
<li><strong>Read reviews critically:</strong> Look beyond the star rating. Fake reviews are often generic and posted in clusters on the same date. Genuine negative reviews describing suspicious behavior (unexpected ads, battery drain, permission requests) are important warning signs</li>
<li><strong>Check download count and age:</strong> Established apps from reputable developers typically have millions of downloads and years of history. A brand-new app with few downloads claiming to be from a major company is suspicious</li>
</ul>

<h3>Managing App Permissions</h3>
<p>When installing or updating an app, carefully review the permissions it requests. A legitimate weather app needs your location but not your contacts or microphone. A photo editor needs camera and storage access but not your call history. Both Android and iOS now allow you to grant permissions on a per-use basis — choose "Only while using the app" for location-sensitive permissions rather than "Always allow." Periodically audit your installed apps: go to Settings, review permissions, and uninstall apps you no longer use. Every installed app is a potential vulnerability, so keep only what you actively need.</p>

<h3>Recognizing Compromised Apps</h3>
<p>Watch for signs that an app may be compromised or malicious: unexpected battery drain, increased data usage, pop-up advertisements outside the app, new apps appearing that you did not install, your device running unusually hot, or unexpected charges on your account. If you notice these symptoms, identify and uninstall the suspicious app, run a security scan, and change passwords for any accounts the app could have accessed. Report the app to the app store so others are protected.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Mobile Device Security Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is "smishing"?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Smishing is phishing conducted via SMS text messages, often impersonating trusted organizations with malicious links.',
                            'answers' => [
                                ['answer' => 'A technique for encrypting text messages', 'is_correct' => false],
                                ['answer' => 'Phishing attacks delivered through SMS text messages', 'is_correct' => true],
                                ['answer' => 'A method of compressing files on mobile devices', 'is_correct' => false],
                                ['answer' => 'A secure messaging protocol used by banks', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do immediately if your work phone is stolen?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Immediately use Find My Device to remotely lock or wipe the device, and report the loss to your IT department so they can revoke corporate access.',
                            'answers' => [
                                ['answer' => 'Wait a few days in case it turns up', 'is_correct' => false],
                                ['answer' => 'Post about the theft on social media to spread the word', 'is_correct' => false],
                                ['answer' => 'Remotely lock or wipe the device and report the loss to your IT department immediately', 'is_correct' => true],
                                ['answer' => 'Buy a new phone and set it up with the same passwords', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you avoid installing apps from links in text messages?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Links in text messages can lead to malicious apps that bypass app store security vetting, potentially installing malware on your device.',
                            'answers' => [
                                ['answer' => 'Apps from text message links are always outdated versions', 'is_correct' => false],
                                ['answer' => 'Text message links bypass app store security vetting and may install malicious software', 'is_correct' => true],
                                ['answer' => 'Apps installed from links use more battery than store downloads', 'is_correct' => false],
                                ['answer' => 'Text message links only work on certain phone brands', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is "juice jacking"?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Juice jacking involves malicious public USB charging stations that can install malware or extract data from connected devices.',
                            'answers' => [
                                ['answer' => 'Overcharging your phone battery until it overheats', 'is_correct' => false],
                                ['answer' => 'Using a public USB charging port that secretly installs malware or steals data from your device', 'is_correct' => true],
                                ['answer' => 'A method attackers use to drain your battery remotely', 'is_correct' => false],
                                ['answer' => 'Stealing electricity from a neighbour\'s power outlet', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A flashlight app requests access to your contacts, microphone, and location. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A flashlight app has no legitimate need for contacts, microphone, or location access. Excessive permission requests indicate a potentially malicious app.',
                            'answers' => [
                                ['answer' => 'Grant all permissions since the app needs them to function', 'is_correct' => false],
                                ['answer' => 'Grant only location access and deny the rest', 'is_correct' => false],
                                ['answer' => 'Deny the excessive permissions and consider using a different app, as these requests are suspicious', 'is_correct' => true],
                                ['answer' => 'Install the app and review permissions later', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 20: Secure Wi-Fi & VPN Usage ──
            [
                'title' => 'Secure Wi-Fi & VPN Usage',
                'slug' => 'secure-wifi-vpn-usage',
                'description' => 'Understand Wi-Fi security risks, learn how VPNs protect your data, and develop skills to detect rogue wireless networks.',
                'objectives' => [
                    'Identify common Wi-Fi security vulnerabilities and attack methods',
                    'Explain how VPN technology works and when to use it',
                    'Configure devices to connect securely to wireless networks',
                    'Detect and avoid rogue access points and evil twin attacks',
                ],
                'category' => 'Mobile & Remote Work Security',
                'difficulty' => 'intermediate',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'sort_order' => 20,
                'lessons' => [
                    [
                        'title' => 'Wi-Fi Security Risks',
                        'slug' => 'wifi-security-risks',
                        'duration_minutes' => 7,
                        'content' => '<h3>Wi-Fi Security Risks</h3>
<p>Wireless networks are convenient but fundamentally different from wired connections in one critical way: radio signals travel through walls and open air, meaning anyone within range can potentially intercept them. Understanding Wi-Fi security risks helps you make informed decisions about when and how to connect wirelessly, whether at home, in the office, or in public.</p>

<h3>Common Wi-Fi Attack Methods</h3>
<ul>
<li><strong>Eavesdropping:</strong> On unencrypted or weakly encrypted networks, attackers can capture all data transmitted between your device and the access point using freely available tools like Wireshark. This includes website visits, form submissions, and any data sent over unencrypted protocols</li>
<li><strong>Man-in-the-middle (MitM) attacks:</strong> An attacker positions themselves between you and the access point, intercepting and potentially modifying traffic in both directions. They can inject malicious content into legitimate web pages, redirect you to phishing sites, or steal session cookies</li>
<li><strong>Evil twin attacks:</strong> An attacker sets up a rogue access point with the same name as a legitimate network. Your device connects to the stronger signal, routing all your traffic through the attacker\'s equipment. This is especially effective in locations where people expect free Wi-Fi</li>
<li><strong>Deauthentication attacks:</strong> An attacker sends forged disconnect messages to your device, forcing it off the legitimate network. When your device automatically reconnects, it may connect to the attacker\'s rogue access point instead</li>
</ul>

<h3>Wi-Fi Encryption Standards</h3>
<p>The security of a Wi-Fi connection depends heavily on its encryption standard. WEP (Wired Equivalent Privacy) is completely broken and can be cracked in minutes — never use it. WPA (Wi-Fi Protected Access) improved on WEP but has known vulnerabilities. WPA2 has been the standard for years and is reasonably secure when configured with a strong password, though the KRACK vulnerability demonstrated weaknesses in the protocol. WPA3 is the latest standard, offering stronger encryption, protection against brute-force password attacks, and forward secrecy that protects past sessions even if the password is later compromised.</p>

<p>Open networks with no encryption — commonly found in cafes, airports, and hotels — provide zero protection. Every byte of data you send or receive on an open network is visible to anyone nearby with the right tools. Even networks that use a shared password displayed on a sign offer limited protection, since everyone on the network shares the same encryption key.</p>',
                    ],
                    [
                        'title' => 'Understanding & Using VPNs',
                        'slug' => 'understanding-using-vpns',
                        'duration_minutes' => 7,
                        'content' => '<h3>Understanding & Using VPNs</h3>
<p>A Virtual Private Network (VPN) creates an encrypted tunnel between your device and a VPN server, protecting your data from eavesdropping regardless of the security of the underlying network. Think of it as sending your data through an armored pipe — even if someone can see the pipe, they cannot see or tamper with what is inside it.</p>

<h3>How VPNs Work</h3>
<p>When you activate a VPN, your device establishes an encrypted connection to a VPN server operated by your organization or a VPN provider. All your internet traffic is routed through this encrypted tunnel before reaching its destination. To anyone monitoring the local network — including the Wi-Fi operator, other users, or attackers — your traffic appears as an indecipherable stream of encrypted data. They can see that you are connected to a VPN server, but they cannot see which websites you visit, what data you send, or what you download.</p>

<h3>When to Use Your VPN</h3>
<ul>
<li><strong>Always on public Wi-Fi:</strong> Connect your VPN before opening any work application, browser, or email client on a public network. Many corporate VPNs can be configured to activate automatically on untrusted networks</li>
<li><strong>When accessing company resources:</strong> Your organization may require VPN connections to reach internal systems, file servers, or intranet sites that are not exposed to the public internet</li>
<li><strong>On hotel and conference networks:</strong> Even password-protected guest networks in hotels and conference centers should be treated as untrusted, as many guests share the same credentials</li>
<li><strong>On home networks for sensitive work:</strong> If your home network security is uncertain or you share your network with many devices, a VPN adds an extra layer of protection</li>
</ul>

<h3>VPN Limitations</h3>
<p>A VPN is not a silver bullet. It protects data in transit between your device and the VPN server, but it does not protect you from malware already on your device, phishing emails, or malicious websites you visit after the traffic exits the VPN tunnel. A VPN also does not make you anonymous — your VPN provider (or your employer, for corporate VPNs) can see your traffic. Split tunneling, where some traffic goes through the VPN and some does not, can create gaps if configured improperly. Always use the full-tunnel mode recommended by your IT department to ensure all work-related traffic is protected.</p>

<p>If your VPN connection drops unexpectedly, your traffic may revert to the unprotected network without warning. Many VPN clients include a "kill switch" that blocks all internet traffic if the VPN disconnects — enable this feature to prevent accidental data exposure.</p>',
                    ],
                    [
                        'title' => 'Detecting Rogue Networks',
                        'slug' => 'detecting-rogue-networks',
                        'duration_minutes' => 6,
                        'content' => '<h3>Detecting Rogue Networks</h3>
<p>Rogue access points and evil twin networks are among the most insidious wireless threats because they exploit a fundamental trust that users place in familiar network names. Your device does not verify the identity of a Wi-Fi network beyond its name and, in some cases, its password. An attacker who knows the name and password of a legitimate network can create a perfect copy that your device will happily connect to.</p>

<h3>Signs of a Rogue Network</h3>
<ul>
<li><strong>Duplicate network names:</strong> If you see two networks with identical or very similar names (e.g., "CompanyGuest" and "Company_Guest"), one may be an attacker\'s rogue access point. When in doubt, verify the correct network name with IT or facility staff</li>
<li><strong>Unexpectedly strong signal:</strong> If a network you normally connect to suddenly has a much stronger signal than usual, it may be because an attacker\'s access point is physically closer to you than the legitimate one</li>
<li><strong>Certificate warnings:</strong> If your browser or VPN client shows a certificate warning when connecting to a site you normally access without issues, you may be on a rogue network performing a man-in-the-middle attack. Never click through certificate warnings</li>
<li><strong>Captive portal anomalies:</strong> If the login page for a network you use regularly looks different, asks for more information than usual, or requests your email password instead of a guest code, treat it as suspicious</li>
<li><strong>Unexpected disconnections:</strong> Repeated disconnections from a known network followed by automatic reconnection can indicate a deauthentication attack designed to force your device onto a rogue access point</li>
</ul>

<h3>Protective Measures</h3>
<p>Configure your device to "forget" networks after use rather than saving them for automatic reconnection. This prevents your device from connecting to a rogue network that reuses a saved network name. Disable the option for your device to automatically connect to open networks. When connecting to a corporate network, use 802.1X enterprise authentication, which verifies the network\'s identity using certificates — not just the network name. This makes evil twin attacks significantly harder to execute.</p>

<p>On corporate networks, your IT team may deploy Wireless Intrusion Prevention Systems (WIPS) that automatically detect rogue access points and alert administrators. If you notice a suspicious network near your office, report it to IT immediately. Even if it turns out to be a neighbour\'s harmless router, the report helps your security team maintain awareness of the wireless environment around your facilities.</p>

<p>The simplest and most effective defense against rogue networks is to always use a VPN. Even if you unknowingly connect to a rogue access point, the VPN\'s encryption prevents the attacker from reading or modifying your traffic.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Secure Wi-Fi & VPN Usage Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is an "evil twin" attack?',
                            'type' => 'multiple_choice',
                            'explanation' => 'An evil twin is a rogue access point that impersonates a legitimate network by using the same name, tricking devices into connecting to it.',
                            'answers' => [
                                ['answer' => 'Two employees using the same Wi-Fi password at the same time', 'is_correct' => false],
                                ['answer' => 'A rogue access point that uses the same name as a legitimate network to trick devices into connecting', 'is_correct' => true],
                                ['answer' => 'A backup Wi-Fi router that activates when the primary fails', 'is_correct' => false],
                                ['answer' => 'A network vulnerability that creates duplicate data packets', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does a VPN "kill switch" do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A kill switch blocks all internet traffic if the VPN connection drops, preventing accidental data exposure on unprotected networks.',
                            'answers' => [
                                ['answer' => 'It shuts down your computer if a virus is detected', 'is_correct' => false],
                                ['answer' => 'It blocks all internet traffic if the VPN connection drops unexpectedly', 'is_correct' => true],
                                ['answer' => 'It disconnects other users from the same VPN server', 'is_correct' => false],
                                ['answer' => 'It permanently disables your VPN when your subscription expires', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is WEP encryption considered insecure?',
                            'type' => 'multiple_choice',
                            'explanation' => 'WEP has fundamental cryptographic weaknesses that allow it to be cracked in minutes with freely available tools.',
                            'answers' => [
                                ['answer' => 'WEP only works with older devices that are no longer manufactured', 'is_correct' => false],
                                ['answer' => 'WEP uses passwords that are too short to remember', 'is_correct' => false],
                                ['answer' => 'WEP has fundamental cryptographic flaws that allow it to be cracked in minutes', 'is_correct' => true],
                                ['answer' => 'WEP is secure but slows down network speeds too much', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Your browser shows a certificate warning when accessing a work site you visit daily. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A certificate warning on a familiar site may indicate a man-in-the-middle attack. Stop, disconnect from the network, and report the issue to IT.',
                            'answers' => [
                                ['answer' => 'Click through the warning since you trust the website', 'is_correct' => false],
                                ['answer' => 'Stop, do not proceed, disconnect from the network, and report it to IT as a potential MitM attack', 'is_correct' => true],
                                ['answer' => 'Switch to a different browser and try again', 'is_correct' => false],
                                ['answer' => 'Clear your browser cookies and refresh the page', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What advantage does WPA3 offer over WPA2?',
                            'type' => 'multiple_choice',
                            'explanation' => 'WPA3 provides stronger encryption, brute-force protection, and forward secrecy that protects past sessions even if the password is later compromised.',
                            'answers' => [
                                ['answer' => 'WPA3 eliminates the need for passwords entirely', 'is_correct' => false],
                                ['answer' => 'WPA3 provides stronger encryption, brute-force protection, and forward secrecy', 'is_correct' => true],
                                ['answer' => 'WPA3 doubles the speed of all Wi-Fi connections', 'is_correct' => false],
                                ['answer' => 'WPA3 allows unlimited devices on a single network', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 21: BYOD Security Best Practices ──
            [
                'title' => 'BYOD Security Best Practices',
                'slug' => 'byod-security-best-practices',
                'description' => 'Learn the security risks and best practices for using personal devices for work under a Bring Your Own Device policy.',
                'objectives' => [
                    'Understand the security risks associated with BYOD in the workplace',
                    'Implement proper separation between work and personal data on a shared device',
                    'Comply with organizational mobile device management and BYOD policies',
                    'Maintain device security standards required for accessing corporate resources',
                ],
                'category' => 'Mobile & Remote Work Security',
                'difficulty' => 'intermediate',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'sort_order' => 21,
                'lessons' => [
                    [
                        'title' => 'BYOD Risks & Policies',
                        'slug' => 'byod-risks-and-policies',
                        'duration_minutes' => 7,
                        'content' => '<h3>BYOD Risks & Policies</h3>
<p>Bring Your Own Device (BYOD) programs allow employees to use their personal smartphones, tablets, and laptops for work purposes. While this offers convenience and cost savings, it introduces significant security challenges. Personal devices operate outside the direct control of the IT department, creating gaps in visibility, enforcement, and protection that do not exist with company-issued hardware.</p>

<h3>Key BYOD Security Risks</h3>
<ul>
<li><strong>Data leakage:</strong> Corporate data stored on personal devices can be inadvertently shared through personal cloud backups, messaging apps, or social media. When personal and work data coexist on the same device, a screenshot of a personal conversation could accidentally capture a work notification containing sensitive information</li>
<li><strong>Inconsistent security posture:</strong> Personal devices may run outdated operating systems, lack encryption, use weak passwords, or have no antivirus protection. An employee\'s device might be jailbroken or rooted, removing built-in security protections</li>
<li><strong>Lost or stolen devices:</strong> When a personal device containing work data is lost or stolen, the organization faces the difficult balance between protecting corporate data and respecting the employee\'s personal property and privacy</li>
<li><strong>Malware exposure:</strong> Personal browsing habits, unvetted app installations, and shared device use with family members increase the risk of malware infection that could spread to corporate systems when the device connects to work resources</li>
<li><strong>Offboarding challenges:</strong> When an employee leaves the organization, removing corporate data from a personal device is complicated. Complete device wipes destroy personal data along with corporate data, creating potential legal and ethical issues</li>
</ul>

<h3>Understanding Your BYOD Policy</h3>
<p>Every organization with a BYOD program should have a clear policy that you need to read and understand before enrolling your personal device. This policy typically covers which devices and operating system versions are permitted, which security configurations are required (encryption, screen lock, antivirus), what data the organization can and cannot access on your device, what happens to your device if it is lost or you leave the company, and your responsibilities for keeping the device secure and updated. If your organization does not have a written BYOD policy, ask your IT department before using personal devices for work — the absence of a policy does not mean permission.</p>',
                    ],
                    [
                        'title' => 'Separating Work and Personal Data',
                        'slug' => 'separating-work-personal-data',
                        'duration_minutes' => 7,
                        'content' => '<h3>Separating Work and Personal Data</h3>
<p>The most critical challenge in BYOD security is maintaining clear separation between corporate and personal data on the same device. Without proper separation, sensitive work documents can end up in personal cloud storage, corporate contacts can be shared with personal apps, and personal activities can compromise work accounts.</p>

<h3>Containerization and Work Profiles</h3>
<p>Modern mobile operating systems and enterprise mobility tools provide built-in mechanisms for separating work and personal data. Android\'s Work Profile creates a completely separate container on your device with its own set of apps, data storage, and accounts. Apps inside the work profile cannot access data outside it, and vice versa. iOS achieves similar separation through managed app configurations. Enterprise mobility management (EMM) tools like Microsoft Intune, VMware Workspace ONE, and MobileIron create secure containers that isolate corporate data and apps from the rest of the device.</p>

<h3>Practical Separation Tips</h3>
<ul>
<li><strong>Use separate browsers:</strong> Use one browser for work (managed by your organization) and a different one for personal browsing. This prevents personal browsing history, saved passwords, and cookies from mixing with work sessions</li>
<li><strong>Do not forward work email to personal accounts:</strong> Forwarding corporate email to Gmail, Yahoo, or other personal accounts moves data outside organizational control and violates most BYOD policies</li>
<li><strong>Keep work files on approved platforms:</strong> Store and share work documents through approved corporate tools like SharePoint, Google Workspace, or your organization\'s chosen platform — never in personal Dropbox, iCloud, or Google Drive accounts</li>
<li><strong>Disable work app notifications on lock screen:</strong> Prevent sensitive work notifications from displaying on your lock screen where others can see them. Configure notifications to show content only when the device is unlocked</li>
<li><strong>Use separate cloud backup configurations:</strong> Ensure personal backups do not include work container data. Work data should be backed up through corporate channels only</li>
</ul>

<p>Think of the work container on your device as a sealed room. Data goes in and out only through doors controlled by your IT department. Anything you do in your personal space stays personal, and anything in the work space stays under corporate management. This separation protects both you and your organization.</p>',
                    ],
                    [
                        'title' => 'Device Management & Compliance',
                        'slug' => 'device-management-compliance',
                        'duration_minutes' => 6,
                        'content' => '<h3>Device Management & Compliance</h3>
<p>When you enroll a personal device in your organization\'s BYOD program, it typically becomes subject to Mobile Device Management (MDM) or Enterprise Mobility Management (EMM) policies. Understanding what these systems do and do not have access to on your personal device is essential for making an informed decision about participating in the program.</p>

<h3>What MDM Can and Cannot See</h3>
<p>This is a common source of anxiety for employees. On a properly configured BYOD enrollment, your organization\'s MDM typically can see device type and model, operating system version, whether encryption is enabled, whether a screen lock is set, installed work apps, and compliance status. It typically cannot see your personal photos, text messages, personal browsing history, personal app data, phone call logs, or GPS location (unless specifically required and disclosed). Your organization\'s BYOD policy should clearly state what the MDM accesses, and you have the right to know before enrolling.</p>

<h3>Maintaining Compliance</h3>
<ul>
<li><strong>Keep your OS updated:</strong> MDM policies often require a minimum OS version. Delayed updates can result in your device being flagged as non-compliant and losing access to corporate resources until updated</li>
<li><strong>Maintain security settings:</strong> Do not disable encryption, remove the screen lock, or jailbreak or root your device. These actions violate virtually all BYOD policies and may trigger an automatic lockout from corporate systems</li>
<li><strong>Install required security apps:</strong> Your organization may require a specific antivirus app, a VPN client, or an authentication app. Keep these installed and running</li>
<li><strong>Report issues promptly:</strong> If your device is lost, stolen, compromised, or showing unusual behavior, report it to IT immediately. Prompt reporting allows the team to protect corporate data before an attacker can access it</li>
</ul>

<h3>When You Leave the Organization</h3>
<p>When you leave or decide to withdraw from the BYOD program, your IT department will need to remove corporate data and the work container from your device. With proper containerization, this is a clean process that removes only work data, apps, and configurations without touching your personal content. A selective wipe removes the corporate container while leaving your photos, personal apps, and data untouched. Cooperate with this process promptly — delaying it may result in your device being remotely wiped entirely as a security precaution, which could affect personal data. Before offboarding, ensure any personal data accidentally stored in the work container is moved to your personal space.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'BYOD Security Best Practices Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the primary purpose of containerization on a BYOD device?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Containerization creates a secure separation between corporate and personal data on the same device, preventing data leakage between the two.',
                            'answers' => [
                                ['answer' => 'To make the device run faster by isolating work apps', 'is_correct' => false],
                                ['answer' => 'To create a secure separation between corporate and personal data on the same device', 'is_correct' => true],
                                ['answer' => 'To allow IT to monitor all personal activity on the device', 'is_correct' => false],
                                ['answer' => 'To limit the number of apps that can be installed', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of the following can a properly configured MDM typically NOT see on your personal BYOD device?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Proper BYOD MDM configurations cannot see personal photos, text messages, browsing history, or personal app data.',
                            'answers' => [
                                ['answer' => 'Your device\'s operating system version', 'is_correct' => false],
                                ['answer' => 'Whether encryption is enabled on the device', 'is_correct' => false],
                                ['answer' => 'Your personal photos, text messages, and personal browsing history', 'is_correct' => true],
                                ['answer' => 'The list of work apps installed in the corporate container', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What happens if you jailbreak or root a device enrolled in a BYOD program?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Jailbreaking or rooting removes built-in security protections and violates virtually all BYOD policies, typically triggering an automatic lockout from corporate systems.',
                            'answers' => [
                                ['answer' => 'Nothing, as jailbreaking only affects personal apps', 'is_correct' => false],
                                ['answer' => 'The device gains stronger security protections', 'is_correct' => false],
                                ['answer' => 'It violates BYOD policy and may trigger automatic lockout from corporate resources', 'is_correct' => true],
                                ['answer' => 'IT will remotely upgrade the device to fix the issue', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you avoid forwarding work email to a personal email account?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Forwarding corporate email to personal accounts moves data outside organizational security controls, violating most BYOD policies and increasing data leakage risk.',
                            'answers' => [
                                ['answer' => 'Personal email services cannot receive corporate emails', 'is_correct' => false],
                                ['answer' => 'It moves corporate data outside organizational security controls and violates BYOD policies', 'is_correct' => true],
                                ['answer' => 'Personal email accounts have smaller inbox limits', 'is_correct' => false],
                                ['answer' => 'Corporate email formatting does not display correctly in personal clients', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a "selective wipe" in the context of BYOD?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A selective wipe removes only the corporate container and work data from a personal device, leaving personal data untouched.',
                            'answers' => [
                                ['answer' => 'Erasing the entire device including all personal and work data', 'is_correct' => false],
                                ['answer' => 'Removing only the corporate container and work data while leaving personal data intact', 'is_correct' => true],
                                ['answer' => 'Deleting only personal data and keeping the work container', 'is_correct' => false],
                                ['answer' => 'Resetting the device to factory settings', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 22: Physical Security Awareness ──
            [
                'title' => 'Physical Security Awareness',
                'slug' => 'physical-security-awareness',
                'description' => 'Understand the importance of physical security in protecting organizational assets, from building access to document handling and recognizing suspicious activity.',
                'objectives' => [
                    'Follow proper building access and badge security procedures',
                    'Handle and dispose of physical documents containing sensitive information securely',
                    'Recognize and report suspicious activity in and around the workplace',
                    'Understand how physical security supports overall cybersecurity',
                ],
                'category' => 'Physical Security & Workplace Safety',
                'difficulty' => 'beginner',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'sort_order' => 22,
                'is_mandatory' => true,
                'lessons' => [
                    [
                        'title' => 'Building Access & Badge Security',
                        'slug' => 'building-access-badge-security',
                        'duration_minutes' => 7,
                        'content' => '<h3>Building Access & Badge Security</h3>
<p>Your employee badge is more than an identification card — it is a security credential that controls which doors you can open, which floors you can access, and which areas you are authorized to enter. Treating your badge with the same care you give your house keys or bank card is fundamental to maintaining the physical security of your workplace and everyone in it.</p>

<h3>Badge Security Fundamentals</h3>
<ul>
<li><strong>Wear your badge visibly:</strong> Keep your badge displayed at all times while on company premises. A visible badge signals to others that you belong and makes it easier to spot individuals without one</li>
<li><strong>Never lend your badge:</strong> Your badge is tied to your identity and access permissions. If someone uses your badge, any action they take — including entering restricted areas — is attributed to you. If a colleague forgets their badge, direct them to security or reception for a temporary pass</li>
<li><strong>Report lost badges immediately:</strong> A lost or stolen badge is a security incident. Report it to security or IT immediately so the badge can be deactivated and a replacement issued. An active badge in the wrong hands provides unrestricted access to your workplace</li>
<li><strong>Do not modify or copy your badge:</strong> Employee badges contain encrypted credentials. Attempting to clone or modify a badge violates security policy and may constitute a criminal offense</li>
</ul>

<h3>Access Control Best Practices</h3>
<p>Always badge in yourself at every controlled entry point, even if someone else is holding the door. This creates an accurate access log that security teams rely on for investigations and emergency headcounts. If you notice a door that is not closing properly, a malfunctioning card reader, or a broken lock, report it immediately to facilities management — a single broken access point undermines the entire physical security perimeter.</p>

<p>Be particularly careful in sensitive areas like server rooms, data centers, executive floors, and research labs. These areas typically have additional access controls for a reason. Even if you have access, be alert to who else is present and whether they should be there. When leaving a restricted area, ensure the door closes and latches behind you — do not leave it ajar for convenience.</p>

<h3>Visitor Management</h3>
<p>Visitors should never be left unattended in your workplace. If you are hosting a visitor, meet them at reception, ensure they receive a visitor badge, and escort them throughout their visit. When the meeting ends, walk them back to reception and confirm their visitor badge is returned. Unescorted visitors, even well-intentioned ones, can inadvertently access areas containing sensitive information or equipment. If you encounter someone without a badge and you do not recognize them, politely ask if you can help them find who they are looking for and direct them to reception.</p>',
                    ],
                    [
                        'title' => 'Securing Physical Documents',
                        'slug' => 'securing-physical-documents',
                        'duration_minutes' => 7,
                        'content' => '<h3>Securing Physical Documents</h3>
<p>In an increasingly digital world, physical documents remain a significant security concern. Contracts, financial reports, employee records, client information, and strategic plans all exist in printed form in many organizations. A single document left on a printer, desk, or in a trash bin can cause a data breach just as damaging as a digital one.</p>

<h3>The Clean Desk Policy</h3>
<p>A clean desk policy requires employees to clear their workspace of all sensitive materials at the end of each workday and whenever they leave their desk for an extended period. This simple practice prevents opportunistic access to confidential information by cleaning staff, visitors, contractors, or unauthorized employees. Lock sensitive documents in a desk drawer or filing cabinet when not actively using them. Remove whiteboards and flip charts containing strategic or sensitive information after meetings, or cover them if they must remain. At the end of each day, ensure no documents containing sensitive data are visible on your desk, in open trays, or pinned to boards.</p>

<h3>Printing and Copying Safely</h3>
<ul>
<li><strong>Use secure print:</strong> Most modern printers support secure print or pull printing, where documents wait in a queue until you authenticate at the printer with your badge or PIN. This prevents documents from sitting uncollected on the output tray where anyone can read them</li>
<li><strong>Collect prints immediately:</strong> If secure print is not available, go to the printer immediately after sending a job. Sensitive documents left on shared printers are a common source of accidental data exposure</li>
<li><strong>Check the copier:</strong> After copying sensitive documents, verify you have not left originals on the scanner glass. Also be aware that many modern copiers store images of copied documents on internal hard drives</li>
<li><strong>Limit copies:</strong> Print or copy only the number of documents you need. Extra copies increase the risk of one going astray</li>
</ul>

<h3>Secure Document Disposal</h3>
<p>Never dispose of documents containing sensitive information in regular trash or recycling bins. Use cross-cut shredders for destroying sensitive documents — strip-cut shredders produce pieces that can be reassembled. If your office uses a secure document destruction service, use the designated locked bins and verify the service provider is reputable and certified. Documents that should always be shredded include anything containing names and personal information, financial data, account numbers, passwords or access codes, internal strategy or planning documents, and client or vendor contract details. When in doubt, shred it — the cost of shredding an unnecessary document is negligible compared to the cost of a data breach from improperly disposed records.</p>',
                    ],
                    [
                        'title' => 'Recognizing Suspicious Activity',
                        'slug' => 'recognizing-suspicious-activity',
                        'duration_minutes' => 6,
                        'content' => '<h3>Recognizing Suspicious Activity</h3>
<p>Every employee plays a role in physical security by staying aware of their surroundings and reporting activity that seems out of place. You do not need to be a security professional to notice when something does not look right. Your familiarity with your workplace, your colleagues, and normal routines gives you a unique vantage point that security cameras and access logs cannot replicate.</p>

<h3>What Counts as Suspicious Activity</h3>
<ul>
<li><strong>Unfamiliar individuals in restricted areas:</strong> Someone you do not recognize in a server room, executive floor, or other restricted area without a visible badge or escort</li>
<li><strong>Unusual behavior near access points:</strong> Someone loitering near secure doors, watching people badge in, testing handles, or photographing access control equipment</li>
<li><strong>Unauthorized photography or recording:</strong> Someone taking photos or video of workspaces, whiteboards, computer screens, or security infrastructure without an apparent legitimate reason</li>
<li><strong>Attempts to bypass security:</strong> Someone asking you to hold a door, lend your badge, share an access code, or let them into an area they cannot access themselves</li>
<li><strong>Unusual equipment:</strong> Unfamiliar devices plugged into network ports, USB devices attached to shared computers, or unknown hardware in server rooms or wiring closets</li>
<li><strong>After-hours anomalies:</strong> Lights on in areas that should be closed, vehicles in restricted parking areas outside business hours, or sounds from unoccupied spaces</li>
</ul>

<h3>How to Report</h3>
<p>If you observe suspicious activity, do not confront the individual directly unless you are comfortable doing so and the situation is clearly non-threatening. Instead, note the details — who (physical description, badge or no badge), what (what they were doing), when (date and time), and where (specific location) — and report to your security team, facilities management, or through your organization\'s incident reporting channel. Do not assume someone else has already reported it. Multiple reports about the same incident are far better than zero reports because everyone assumed someone else called it in.</p>

<h3>Physical Security Supports Cybersecurity</h3>
<p>Physical and digital security are deeply interconnected. An attacker who gains physical access to your building can plug a rogue device into your network, install a keylogger on a shared workstation, steal backup tapes from an unlocked server room, or simply photograph screens displaying sensitive data. Many of the most sophisticated cyberattacks in history have included a physical component — from USB drives dropped in parking lots to attackers posing as IT technicians to access server rooms. By staying alert and reporting suspicious activity, you strengthen not just physical security but the entire security posture of your organization.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Physical Security Awareness Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What should you do if you lose your employee badge?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A lost badge must be reported immediately so it can be deactivated, preventing unauthorized access to the facility.',
                            'answers' => [
                                ['answer' => 'Wait until you are sure it is lost, then request a replacement next week', 'is_correct' => false],
                                ['answer' => 'Borrow a colleague\'s badge until you find yours', 'is_correct' => false],
                                ['answer' => 'Report it to security or IT immediately so the badge can be deactivated', 'is_correct' => true],
                                ['answer' => 'Post about it on the company chat in case someone found it', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the purpose of a clean desk policy?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A clean desk policy prevents unauthorized access to sensitive information left visible on desks by cleaning staff, visitors, or other unauthorized individuals.',
                            'answers' => [
                                ['answer' => 'To keep the office looking tidy for visiting clients', 'is_correct' => false],
                                ['answer' => 'To prevent unauthorized access to sensitive documents left visible on desks and workspaces', 'is_correct' => true],
                                ['answer' => 'To reduce the number of items the cleaning crew needs to work around', 'is_correct' => false],
                                ['answer' => 'To make it easier to find your own documents in the morning', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you use a cross-cut shredder instead of a strip-cut shredder for sensitive documents?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Strip-cut shredders produce long strips that can be reassembled. Cross-cut shredders create small particles that are virtually impossible to reconstruct.',
                            'answers' => [
                                ['answer' => 'Cross-cut shredders are quieter and use less electricity', 'is_correct' => false],
                                ['answer' => 'Strip-cut shredders produce pieces that can be reassembled, while cross-cut shredders create particles that cannot', 'is_correct' => true],
                                ['answer' => 'Cross-cut shredders work faster than strip-cut models', 'is_correct' => false],
                                ['answer' => 'There is no difference in security between the two types', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You notice an unfamiliar person in the server room without a badge. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'An unidentified person in a restricted area without a badge should be politely challenged or reported to security immediately.',
                            'answers' => [
                                ['answer' => 'Ignore them since they probably have permission you do not know about', 'is_correct' => false],
                                ['answer' => 'Politely ask if you can help them and report the situation to security', 'is_correct' => true],
                                ['answer' => 'Physically block them from leaving until security arrives', 'is_correct' => false],
                                ['answer' => 'Wait to see if they leave on their own', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'How does physical security relate to cybersecurity?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Physical access enables many cyberattacks, such as installing rogue network devices, keyloggers, or stealing data directly from unlocked systems.',
                            'answers' => [
                                ['answer' => 'They are completely separate disciplines with no overlap', 'is_correct' => false],
                                ['answer' => 'Physical security only matters for buildings, not for data', 'is_correct' => false],
                                ['answer' => 'Physical access enables cyberattacks like installing rogue devices, keyloggers, and stealing data from systems directly', 'is_correct' => true],
                                ['answer' => 'Cybersecurity has replaced the need for physical security entirely', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
