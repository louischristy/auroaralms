<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Database\Seeder;

class CourseSeederBatch7 extends Seeder
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
            // ── Course 33: Safe Internet Browsing ──
            [
                'title' => 'Safe Internet Browsing',
                'slug' => 'safe-internet-browsing',
                'description' => 'Learn how to browse the internet safely by recognizing malicious websites, configuring browser security settings, and practicing safe downloading habits.',
                'objectives' => [
                    'Identify signs of malicious or compromised websites',
                    'Configure browser security and privacy settings for maximum protection',
                    'Practice safe downloading habits to avoid malware infections',
                    'Understand how drive-by downloads and malvertising work',
                ],
                'category' => 'Malware & Ransomware',
                'difficulty' => 'beginner',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'sort_order' => 33,
                'lessons' => [
                    [
                        'title' => 'Recognizing Malicious Websites',
                        'slug' => 'recognizing-malicious-websites',
                        'duration_minutes' => 7,
                        'content' => '<h3>Recognizing Malicious Websites</h3>
<p>Malicious websites are a primary tool attackers use to distribute malware, steal credentials, and compromise personal and corporate devices. These sites can appear in search results, be linked from phishing emails, or even show up as advertisements on legitimate platforms. Learning to spot them before you interact is one of the most important browsing skills you can develop.</p>

<h3>URL Red Flags</h3>
<p>The web address itself often reveals whether a site is legitimate. Before clicking any link, examine the URL carefully. Look for misspelled domain names such as "gooogle.com" or "amaz0n.com" where letters are replaced with similar-looking characters. Check that the domain matches the organization you expect -- "login-microsoft-support.com" is not a Microsoft site, because the actual domain is "login-microsoft-support.com," not "microsoft.com." Be wary of excessively long URLs filled with random characters, as these often indicate phishing pages or redirect chains designed to obscure the true destination.</p>

<h3>Visual and Behavioral Warning Signs</h3>
<ul>
<li><strong>Missing or invalid HTTPS:</strong> Legitimate sites handling any sensitive information use HTTPS. A missing padlock icon or a browser warning about an invalid certificate is a strong signal to leave immediately</li>
<li><strong>Excessive pop-ups:</strong> Sites that bombard you with pop-up windows, especially those claiming your computer is infected or that you have won a prize, are almost certainly malicious</li>
<li><strong>Fake urgency messages:</strong> Warnings like "Your computer has 47 viruses! Call this number immediately!" are social engineering tactics designed to panic you into calling a scam support line or downloading fake antivirus software</li>
<li><strong>Poor design and grammar:</strong> While not always conclusive, many phishing sites have noticeable spelling errors, broken layouts, or low-resolution logos copied from legitimate sites</li>
<li><strong>Unexpected redirects:</strong> If clicking a link takes you through a series of rapid redirects before landing on an unfamiliar page, close the tab immediately</li>
</ul>

<h3>Drive-By Downloads and Malvertising</h3>
<p>Some malicious websites do not require you to click anything at all. Drive-by downloads exploit vulnerabilities in your browser or its plugins to silently install malware just by visiting a page. Malvertising injects malicious code into legitimate advertising networks, meaning even trusted websites can unknowingly serve dangerous ads. This is why keeping your browser and operating system updated is critical -- patches close the vulnerabilities that drive-by downloads exploit.</p>

<p>To protect yourself, always verify URLs before clicking, keep your browser updated, and pay attention to browser warnings. If your browser tells you a site is dangerous, trust that warning and navigate away.</p>',
                    ],
                    [
                        'title' => 'Browser Security Settings',
                        'slug' => 'browser-security-settings',
                        'duration_minutes' => 7,
                        'content' => '<h3>Browser Security Settings</h3>
<p>Your web browser is the primary gateway between your device and the internet, making its security configuration one of the most impactful steps you can take to protect yourself online. Modern browsers include numerous built-in security features, but many are not enabled by default or require configuration to provide maximum protection.</p>

<h3>Essential Security Features to Enable</h3>
<ul>
<li><strong>Safe Browsing or SmartScreen:</strong> Chrome, Firefox, Edge, and Safari all include built-in safe browsing features that warn you before visiting known malicious sites or downloading dangerous files. Ensure this feature is turned on in your browser settings</li>
<li><strong>Automatic updates:</strong> Browsers release security patches frequently. Enable automatic updates so you always have the latest protections against newly discovered vulnerabilities</li>
<li><strong>Pop-up blocker:</strong> Enable the built-in pop-up blocker and only allow pop-ups for sites you explicitly trust. Malicious pop-ups are a common delivery mechanism for scams and malware</li>
<li><strong>Do Not Track and tracking prevention:</strong> While not a security feature per se, reducing tracking limits the data available to attackers who compromise advertising networks or data brokers</li>
</ul>

<h3>Managing Extensions and Plugins</h3>
<p>Browser extensions can be incredibly useful, but they also represent a significant security risk. Extensions have access to your browsing data, and malicious or compromised extensions can steal passwords, inject advertisements, redirect searches, or monitor everything you do online. Only install extensions from official browser stores, and review the permissions each extension requests. Remove extensions you no longer use, as abandoned extensions may be sold to malicious actors who push compromised updates to existing users.</p>

<h3>Cookie and Privacy Settings</h3>
<p>Configure your browser to block third-party cookies, which are primarily used for cross-site tracking and offer little benefit to you. Consider using your browser in "strict" tracking protection mode, which blocks known trackers and fingerprinting attempts. Clear your browsing data periodically, including cookies and cached files, to remove any tracking data that has accumulated. For sensitive tasks like online banking, consider using a private browsing window, which does not retain cookies or history after the window is closed.</p>

<h3>HTTPS-Only Mode</h3>
<p>Most modern browsers offer an HTTPS-only mode that automatically upgrades connections to use encryption and warns you before loading any page over unencrypted HTTP. Enable this feature to ensure your browsing traffic is encrypted whenever possible, preventing eavesdroppers on public networks from seeing what you are doing online.</p>',
                    ],
                    [
                        'title' => 'Safe Downloading Practices',
                        'slug' => 'safe-downloading-practices',
                        'duration_minutes' => 6,
                        'content' => '<h3>Safe Downloading Practices</h3>
<p>Downloading files from the internet is one of the most common ways malware reaches your device. Attackers disguise malicious software as legitimate programs, documents, or media files, and distribute them through fake websites, compromised download portals, and even search engine advertisements. Adopting safe downloading habits dramatically reduces your risk of infection.</p>

<h3>Download Only from Official Sources</h3>
<p>Always download software directly from the official vendor website or an authorized app store. Avoid third-party download sites that bundle additional software with the program you want -- these bundled programs often include adware, browser hijackers, or outright malware. When searching for software, be aware that attackers purchase search engine ads to place malicious download links above legitimate results. Type the vendor URL directly into your address bar rather than clicking search results or ads.</p>

<h3>Verify File Integrity</h3>
<ul>
<li><strong>Check file extensions:</strong> Before opening any downloaded file, verify its extension matches what you expected. A file named "report.pdf.exe" is an executable disguised as a PDF</li>
<li><strong>Verify checksums:</strong> Many software vendors publish SHA-256 checksums alongside their downloads. Comparing the checksum of your downloaded file against the published value confirms the file has not been tampered with during download</li>
<li><strong>Scan before opening:</strong> Right-click downloaded files and scan them with your antivirus software before opening, or upload them to VirusTotal for multi-engine analysis</li>
<li><strong>Be cautious with archives:</strong> ZIP and RAR files can contain malicious executables. Extract them to a folder and inspect the contents before running anything inside</li>
</ul>

<h3>Recognizing Fake Download Buttons</h3>
<p>Many file-sharing and free software sites display multiple "Download" buttons, most of which are advertisements that lead to unwanted or malicious software. The real download link is often smaller and less prominent than the decoy ads. Hover over download buttons to check where they actually link before clicking. If a page has multiple download buttons competing for your attention, that is a strong signal to find a more reputable source for the file.</p>

<h3>Software Update Scams</h3>
<p>Pop-ups claiming your Flash Player, Java, or media player is out of date and needs immediate updating are almost always scams. Legitimate software updates come through the application itself or your operating system update mechanism, never through browser pop-ups. If you see such a message, close the tab and check for updates through the actual application or your system settings.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Safe Internet Browsing Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is a "drive-by download"?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Drive-by downloads exploit browser or plugin vulnerabilities to install malware automatically when you visit a compromised page, without any user interaction.',
                            'answers' => [
                                ['answer' => 'A download that starts automatically when you visit a malicious page, exploiting browser vulnerabilities', 'is_correct' => true],
                                ['answer' => 'A file you download while driving and using your phone', 'is_correct' => false],
                                ['answer' => 'A download from a cloud storage service', 'is_correct' => false],
                                ['answer' => 'A software update that installs without asking permission', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you enable HTTPS-only mode in your browser?',
                            'type' => 'multiple_choice',
                            'explanation' => 'HTTPS-only mode ensures your browsing traffic is encrypted, preventing eavesdroppers on the network from seeing your activity.',
                            'answers' => [
                                ['answer' => 'It makes websites load faster', 'is_correct' => false],
                                ['answer' => 'It ensures your browsing traffic is encrypted, protecting it from eavesdropping', 'is_correct' => true],
                                ['answer' => 'It blocks all advertisements on websites', 'is_correct' => false],
                                ['answer' => 'It prevents websites from using cookies', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the safest way to download software?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Downloading directly from the official vendor website or authorized app store ensures you get the genuine, unmodified software.',
                            'answers' => [
                                ['answer' => 'Search for it on a search engine and click the first result', 'is_correct' => false],
                                ['answer' => 'Use a third-party download site that bundles many programs together', 'is_correct' => false],
                                ['answer' => 'Download directly from the official vendor website or an authorized app store', 'is_correct' => true],
                                ['answer' => 'Ask a colleague to email you the installer file', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A pop-up appears saying your Flash Player is out of date and needs to be updated immediately. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Browser pop-ups claiming software is outdated are almost always scams. Legitimate updates come through the application itself or your operating system.',
                            'answers' => [
                                ['answer' => 'Click the update button to stay protected', 'is_correct' => false],
                                ['answer' => 'Close the tab -- legitimate updates come through the application or OS, not browser pop-ups', 'is_correct' => true],
                                ['answer' => 'Search for the update on a third-party site', 'is_correct' => false],
                                ['answer' => 'Call the support number shown in the pop-up', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why are browser extensions a potential security risk?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Extensions have access to your browsing data and can be compromised or sold to malicious actors who push harmful updates to existing users.',
                            'answers' => [
                                ['answer' => 'They slow down your internet connection', 'is_correct' => false],
                                ['answer' => 'They use too much disk space', 'is_correct' => false],
                                ['answer' => 'They have access to your browsing data and can be compromised or turned malicious through updates', 'is_correct' => true],
                                ['answer' => 'They prevent your browser from receiving security updates', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Course 34: Social Media Security ──
            [
                'title' => 'Social Media Security',
                'slug' => 'social-media-security',
                'description' => 'Understand the security risks associated with social media use and learn how to protect yourself and your organization from social media-based attacks.',
                'objectives' => [
                    'Identify the key threats targeting social media users in a corporate context',
                    'Configure privacy settings to limit exposure of personal and professional information',
                    'Recognize social media phishing, impersonation, and scam techniques',
                    'Apply best practices for safe social media use as an employee',
                ],
                'category' => 'Social Engineering',
                'difficulty' => 'beginner',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'sort_order' => 34,
                'lessons' => [
                    [
                        'title' => 'Social Media Threat Landscape',
                        'slug' => 'social-media-threat-landscape',
                        'duration_minutes' => 7,
                        'content' => '<h3>Social Media Threat Landscape</h3>
<p>Social media platforms have become a major attack surface for cybercriminals. With billions of users sharing personal and professional information daily, platforms like LinkedIn, Facebook, X (formerly Twitter), and Instagram provide attackers with a rich source of reconnaissance data and a direct communication channel to potential victims. Understanding the threat landscape is the first step toward using social media safely.</p>

<h3>Why Attackers Target Social Media</h3>
<p>Social media provides attackers with three key advantages. First, it offers vast amounts of personal information that users voluntarily share -- job titles, employer names, project details, travel plans, personal interests, and relationship networks. Second, it provides a trusted communication channel where people are accustomed to receiving messages from strangers, such as connection requests on LinkedIn or direct messages on Instagram. Third, the informal and fast-paced nature of social media encourages quick reactions rather than careful verification, which plays directly into social engineering tactics.</p>

<h3>Common Social Media Threats</h3>
<ul>
<li><strong>Reconnaissance for targeted attacks:</strong> Attackers mine social media profiles to build detailed dossiers on targets for spear phishing, pretexting, or business email compromise. Your LinkedIn profile showing your job title, department, and recent project can fuel a convincing phishing email</li>
<li><strong>Impersonation and fake profiles:</strong> Attackers create fake profiles mimicking real employees, executives, or recruiters to establish trust before launching an attack. A fake LinkedIn profile of your CEO could be used to message employees with malicious requests</li>
<li><strong>Credential theft:</strong> Fake login pages distributed through social media messages or posts steal usernames and passwords when users attempt to sign in</li>
<li><strong>Malware distribution:</strong> Shortened links in posts and messages can lead to malicious downloads. Attackers also compromise legitimate accounts and use them to spread malware to the victim\'s contacts</li>
<li><strong>Corporate espionage:</strong> Competitors and nation-state actors use fake profiles to connect with employees and gradually extract proprietary information through casual conversation</li>
</ul>

<h3>The Corporate Risk</h3>
<p>When employees share too much about their work on social media, it creates risk for the entire organization. Posts about internal tools, project timelines, vendor relationships, and office layouts give attackers the context they need to craft believable pretexts. Even seemingly harmless photos of office spaces can reveal security badge designs, network equipment, or whiteboard contents with strategic plans.</p>',
                    ],
                    [
                        'title' => 'Privacy Settings & Oversharing Risks',
                        'slug' => 'privacy-settings-oversharing-risks',
                        'duration_minutes' => 7,
                        'content' => '<h3>Privacy Settings and Oversharing Risks</h3>
<p>The default privacy settings on most social media platforms prioritize visibility and engagement over security. This means that unless you actively configure your settings, much of what you post is visible to anyone on the internet -- including attackers researching you or your organization. Taking control of your privacy settings is one of the most effective steps you can take to reduce your attack surface.</p>

<h3>Key Privacy Settings to Configure</h3>
<ul>
<li><strong>Profile visibility:</strong> Limit who can see your full profile, friend list, and posts. On Facebook, set your default post audience to "Friends" rather than "Public." On LinkedIn, consider restricting who can see your connections list, as it reveals your professional network</li>
<li><strong>Contact information:</strong> Remove your phone number, personal email, and home location from public profiles. Attackers use these for SIM swapping, targeted phishing, and physical social engineering</li>
<li><strong>Location sharing:</strong> Disable automatic location tagging on posts and photos. Sharing your real-time location broadcasts when you are away from home or the office, enabling both physical and digital attacks</li>
<li><strong>Search visibility:</strong> Control whether your profile appears in search engine results and whether people can find you by your email address or phone number</li>
<li><strong>Third-party app access:</strong> Regularly review and revoke access for third-party apps connected to your social media accounts. Many of these apps have excessive permissions and poor security practices</li>
</ul>

<h3>The Danger of Oversharing</h3>
<p>Oversharing on social media creates compounding risks. Posting about a business trip provides an attacker with your travel dates, airline, and hotel -- enough to craft a convincing phishing email disguised as a hotel confirmation or flight change notification. Sharing a photo of your new employee badge gives attackers a template to create a fake one. Announcing a promotion or new job provides the perfect pretext for a "welcome aboard" phishing email from a fake HR department.</p>

<p>Even information that seems harmless in isolation becomes dangerous when combined. Your birthday, pet\'s name, high school, and hometown -- commonly shared on social media -- are frequently used as security questions for account recovery. An attacker who gathers these details can bypass account recovery protections and take over your accounts.</p>

<h3>Guidelines for Work-Related Posting</h3>
<p>Before posting anything work-related on social media, ask yourself: could this information help someone craft a convincing attack against me or my organization? Avoid sharing details about internal tools and systems, vendor names and contract details, office security procedures, upcoming projects or product launches, organizational charts or reporting structures, and photos that reveal office layouts or screen contents. When in doubt, leave it out.</p>',
                    ],
                    [
                        'title' => 'Social Media Phishing & Scams',
                        'slug' => 'social-media-phishing-scams',
                        'duration_minutes' => 6,
                        'content' => '<h3>Social Media Phishing and Scams</h3>
<p>Phishing on social media is growing rapidly because people tend to be less suspicious of messages received through social platforms than through email. The informal, conversational nature of social media lowers people\'s guard, and attackers exploit this by embedding malicious links in direct messages, comments, and even job offers.</p>

<h3>LinkedIn-Based Attacks</h3>
<p>LinkedIn is a primary hunting ground for attackers because it is a professional platform where connecting with strangers is expected and encouraged. Common LinkedIn attack patterns include fake recruiter profiles offering attractive job opportunities that require you to click a link or download a "job description" file, connection requests from fabricated profiles that gradually build rapport before making malicious requests, and messages from compromised legitimate accounts sharing links to "industry reports" or "conference materials" that are actually malware.</p>

<h3>Fake Account Indicators</h3>
<ul>
<li><strong>Recently created profile:</strong> A brand-new account with a complete profile but no post history or meaningful engagement</li>
<li><strong>Stock or AI-generated photo:</strong> Profile photos that look overly polished or generic. Use reverse image search to check if the photo appears elsewhere online</li>
<li><strong>Inconsistent details:</strong> A claimed senior executive at a major company whose profile shows grammatical errors, sparse endorsements, and few connections</li>
<li><strong>Aggressive outreach:</strong> Immediate messages after connecting, especially with links, file attachments, or requests for personal information</li>
<li><strong>Too-good-to-be-true offers:</strong> Unsolicited job offers with significantly above-market salaries, investment opportunities with guaranteed returns, or exclusive deals requiring immediate action</li>
</ul>

<h3>Protecting Yourself from Social Media Scams</h3>
<p>Verify connection requests before accepting them -- check if the person genuinely works where they claim by looking at the company website or contacting the company directly. Never click shortened links in social media messages without expanding them first using a URL preview tool. Be skeptical of any message that creates urgency or asks you to move the conversation to a different platform, which is a common tactic to evade platform security monitoring. Report fake profiles and suspicious messages to the platform, as this helps protect other users as well.</p>

<p>If you receive a suspicious message that appears to be from a colleague or business contact, verify through a separate channel before responding. Their account may have been compromised, and your reply could give the attacker information they need for further attacks.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Social Media Security Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'Why is LinkedIn a particularly attractive platform for attackers?',
                            'type' => 'multiple_choice',
                            'explanation' => 'LinkedIn is professional by nature, so connecting with strangers is expected. It also contains rich professional details useful for targeted attacks.',
                            'answers' => [
                                ['answer' => 'It has weaker security than other platforms', 'is_correct' => false],
                                ['answer' => 'Connecting with strangers is expected, and profiles contain rich professional details useful for targeted attacks', 'is_correct' => true],
                                ['answer' => 'It does not allow users to block suspicious accounts', 'is_correct' => false],
                                ['answer' => 'It does not have any privacy settings available', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a key risk of sharing your travel plans on social media?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Travel details enable attackers to craft convincing phishing emails disguised as airline or hotel communications, and reveal when you are away.',
                            'answers' => [
                                ['answer' => 'Your employer might think you are taking too much time off', 'is_correct' => false],
                                ['answer' => 'Attackers can craft phishing emails mimicking airlines or hotels and know when you are away from the office', 'is_correct' => true],
                                ['answer' => 'Airlines might increase your ticket price', 'is_correct' => false],
                                ['answer' => 'Travel posts slow down your social media feed', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of the following is a sign that a social media profile may be fake?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A recently created profile with a complete biography but no post history or genuine engagement is a strong indicator of a fabricated account.',
                            'answers' => [
                                ['answer' => 'The person has more than 500 connections', 'is_correct' => false],
                                ['answer' => 'The profile was recently created with a complete bio but no post history or real engagement', 'is_correct' => true],
                                ['answer' => 'The person works at a large well-known company', 'is_correct' => false],
                                ['answer' => 'The profile has a professional headshot as the photo', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you regularly review third-party app access on your social media accounts?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Third-party apps may have excessive permissions and poor security. Unused apps that retain access create unnecessary risk if they are compromised.',
                            'answers' => [
                                ['answer' => 'To free up storage space on your device', 'is_correct' => false],
                                ['answer' => 'To make your social media feed load faster', 'is_correct' => false],
                                ['answer' => 'Because unused apps with access to your account create risk if they are compromised or have poor security practices', 'is_correct' => true],
                                ['answer' => 'Because social media platforms charge for connected apps', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A LinkedIn connection you do not know well sends you a link to an "exclusive industry report." What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Unsolicited links from unfamiliar connections are a common attack vector. Verify the sender and the link before clicking.',
                            'answers' => [
                                ['answer' => 'Click the link since it came from a LinkedIn connection', 'is_correct' => false],
                                ['answer' => 'Forward it to your entire team so they can benefit too', 'is_correct' => false],
                                ['answer' => 'Do not click the link -- verify the sender is legitimate and check the URL before interacting', 'is_correct' => true],
                                ['answer' => 'Download the report to your work computer for safekeeping', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Course 35: USB & Removable Media Security ──
            [
                'title' => 'USB & Removable Media Security',
                'slug' => 'usb-removable-media-security',
                'description' => 'Understand the risks posed by USB drives and removable media, and learn organizational policies and secure alternatives for file transfers.',
                'objectives' => [
                    'Identify common attack vectors that use USB drives and removable media',
                    'Understand organizational policies governing removable media use',
                    'Recognize the dangers of unknown or found USB devices',
                    'Use secure alternatives for transferring files between systems',
                ],
                'category' => 'Malware & Ransomware',
                'difficulty' => 'beginner',
                'duration_minutes' => 15,
                'passing_score' => 70,
                'sort_order' => 35,
                'lessons' => [
                    [
                        'title' => 'USB-Based Attack Vectors',
                        'slug' => 'usb-based-attack-vectors',
                        'duration_minutes' => 5,
                        'content' => '<h3>USB-Based Attack Vectors</h3>
<p>USB devices remain one of the most effective physical attack vectors in cybersecurity. Despite advances in network security, a single malicious USB drive plugged into a corporate computer can bypass firewalls, intrusion detection systems, and network segmentation entirely. Attackers have used USB-based attacks to compromise air-gapped military networks, critical infrastructure, and major corporations.</p>

<h3>The "Lost USB" Attack</h3>
<p>One of the simplest and most effective USB attacks involves dropping infected drives in parking lots, lobbies, cafeterias, or conference rooms where employees will find them. Studies have shown that between 45 and 98 percent of people who find a USB drive will plug it into their computer, often out of curiosity about its contents or a genuine desire to return it to its owner. Once inserted, the drive can automatically execute malware, install a backdoor, or begin exfiltrating data.</p>

<h3>Types of Malicious USB Devices</h3>
<ul>
<li><strong>Infected storage drives:</strong> Ordinary-looking USB drives preloaded with malware that executes when the drive is connected or when the user opens a file. The malware may be disguised as documents, photos, or other common files</li>
<li><strong>USB Rubber Ducky:</strong> A device that looks like a standard USB drive but is actually a programmable keyboard. When plugged in, it types pre-programmed keystrokes at superhuman speed, executing commands that can download malware, create backdoor accounts, or exfiltrate data in seconds</li>
<li><strong>USB Killer:</strong> A device designed to destroy hardware by sending a high-voltage electrical surge through the USB port, damaging the motherboard and potentially destroying the entire computer</li>
<li><strong>Rogue USB cables:</strong> Charging cables with embedded microcontrollers that can inject keystrokes or establish wireless connections to an attacker. These are indistinguishable from normal cables by appearance</li>
</ul>

<h3>Real-World Impact</h3>
<p>The Stuxnet worm, one of the most sophisticated pieces of malware ever created, was delivered to its target -- Iranian nuclear centrifuges on an air-gapped network -- via infected USB drives. This attack demonstrated that even networks completely disconnected from the internet are vulnerable to USB-based threats. In the corporate world, USB attacks have been used to install ransomware, steal intellectual property, and establish persistent network access that survives even after the USB device is removed.</p>',
                    ],
                    [
                        'title' => 'Removable Media Policies',
                        'slug' => 'removable-media-policies',
                        'duration_minutes' => 5,
                        'content' => '<h3>Removable Media Policies</h3>
<p>Organizations implement removable media policies to manage the significant risks that USB drives, external hard drives, SD cards, and other portable storage devices pose to corporate security. These policies balance the legitimate business need to transfer files with the need to prevent malware infections and data theft. Understanding and following your organization\'s policy is an essential part of maintaining security.</p>

<h3>Common Policy Approaches</h3>
<ul>
<li><strong>Complete prohibition:</strong> Some organizations, particularly those in high-security industries like defense, finance, and healthcare, ban all removable media entirely. USB ports may be physically disabled or blocked through endpoint management software</li>
<li><strong>Approved devices only:</strong> Organizations may allow only company-issued, encrypted USB drives that are registered and managed by IT. Personal USB devices are prohibited</li>
<li><strong>Scan-before-use:</strong> Some policies require all removable media to be scanned by IT security before being connected to any corporate system, similar to how packages are screened before entering a secure facility</li>
<li><strong>Role-based access:</strong> Only employees whose job function requires removable media use are granted the ability to use USB devices, and their activity is monitored and logged</li>
</ul>

<h3>What You Should Never Do</h3>
<p>Never plug a USB drive you found into your computer. This applies whether you found it in the parking lot, at a conference, or on your desk. Never use personal USB drives on work computers or work USB drives on personal computers, as this creates a bridge that can carry malware in either direction. Never borrow a USB drive from someone you do not know well, even if the request seems reasonable. Never disable or circumvent USB port restrictions on your work computer, as these controls exist to protect the entire network.</p>

<h3>Handling Found USB Devices</h3>
<p>If you find a USB drive anywhere in or around your workplace, do not plug it in. Instead, turn it in to your IT security team or place it in a designated drop-off location if your organization has one. Security teams have isolated systems specifically designed to safely examine the contents of unknown devices without risking the corporate network. If you find a USB drive outside of work, the safest approach is to simply leave it or dispose of it -- the risk of plugging in an unknown device far outweighs the value of any files it might contain.</p>',
                    ],
                    [
                        'title' => 'Secure File Transfer Alternatives',
                        'slug' => 'secure-file-transfer-alternatives',
                        'duration_minutes' => 5,
                        'content' => '<h3>Secure File Transfer Alternatives</h3>
<p>Given the risks associated with USB drives and removable media, organizations provide secure alternatives for transferring files between systems, sharing data with external partners, and moving information between work and personal environments. Using these approved channels eliminates the malware risk of physical media while also providing audit trails and access controls that USB drives cannot offer.</p>

<h3>Corporate Cloud Storage</h3>
<p>Enterprise cloud storage solutions like SharePoint, Google Drive for Business, or Box provide secure, managed file sharing with encryption in transit and at rest, access controls that limit who can view or edit files, version history tracking changes over time, and DLP integration that prevents sensitive data from being shared inappropriately. Unlike a USB drive, files shared through corporate cloud storage remain under organizational control even after sharing, and access can be revoked at any time.</p>

<h3>Secure File Transfer Services</h3>
<ul>
<li><strong>SFTP (Secure File Transfer Protocol):</strong> Provides encrypted file transfers for large datasets or automated transfers between systems. Common in industries that regularly exchange large files with external partners</li>
<li><strong>Managed file transfer (MFT) platforms:</strong> Enterprise solutions that provide encrypted transfers with tracking, compliance reporting, and automated workflows. These replace ad-hoc USB transfers with auditable, repeatable processes</li>
<li><strong>Encrypted email attachments:</strong> For smaller files, your organization\'s email encryption tools protect attachments in transit and may require authentication before the recipient can open them</li>
</ul>

<h3>When You Think You Need a USB Drive</h3>
<p>If you encounter a situation where you feel a USB drive is the only option, stop and ask your IT team for an alternative. Common scenarios that seem to require USB drives often have better solutions: presenting at a conference can be done with cloud-based presentations accessed from any browser; sharing files with a vendor can be done through a secure file sharing link; moving files between your work and personal computer should go through approved cloud storage rather than a USB drive that bridges both environments.</p>

<h3>Encrypted USB Drives for Exceptions</h3>
<p>In cases where removable media is genuinely necessary, organizations should use hardware-encrypted USB drives with features like PIN authentication, automatic data wiping after failed login attempts, and remote management capabilities. These devices cost more than standard USB drives but provide critical protections including full-disk encryption, tamper-proof hardware, and compliance with security standards like FIPS 140-2.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'USB & Removable Media Security Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'You find a USB drive in the office parking lot. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Found USB drives may be part of a deliberate attack. Never plug one in -- turn it in to your IT security team for safe analysis.',
                            'answers' => [
                                ['answer' => 'Plug it into your computer to see who it belongs to so you can return it', 'is_correct' => false],
                                ['answer' => 'Turn it in to your IT security team without plugging it into any computer', 'is_correct' => true],
                                ['answer' => 'Plug it into a personal computer since only work computers are at risk', 'is_correct' => false],
                                ['answer' => 'Keep it for personal use after formatting it', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a "USB Rubber Ducky"?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A USB Rubber Ducky looks like a normal USB drive but is actually a programmable keyboard that types pre-programmed malicious commands at high speed.',
                            'answers' => [
                                ['answer' => 'A USB drive shaped like a rubber duck', 'is_correct' => false],
                                ['answer' => 'A device that looks like a USB drive but acts as a programmable keyboard to execute malicious commands', 'is_correct' => true],
                                ['answer' => 'A waterproof USB drive', 'is_correct' => false],
                                ['answer' => 'A type of antivirus software for USB drives', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why are corporate cloud storage solutions preferred over USB drives for file transfers?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Cloud storage provides encryption, access controls, audit trails, and the ability to revoke access -- none of which USB drives offer.',
                            'answers' => [
                                ['answer' => 'Cloud storage is always free while USB drives cost money', 'is_correct' => false],
                                ['answer' => 'They provide encryption, access controls, audit trails, and revocable access that USB drives cannot', 'is_correct' => true],
                                ['answer' => 'Cloud storage files cannot be infected with malware', 'is_correct' => false],
                                ['answer' => 'USB drives cannot store files larger than 1 GB', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'How was the Stuxnet worm delivered to its target on an air-gapped network?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Stuxnet was delivered via infected USB drives, demonstrating that even networks disconnected from the internet are vulnerable to USB-based attacks.',
                            'answers' => [
                                ['answer' => 'Through a phishing email', 'is_correct' => false],
                                ['answer' => 'By exploiting a Wi-Fi vulnerability', 'is_correct' => false],
                                ['answer' => 'Via infected USB drives that were physically carried to the target', 'is_correct' => true],
                                ['answer' => 'Through a compromised software update', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do if you believe a USB drive is the only way to complete a task?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Before resorting to USB drives, consult IT for a secure alternative. Most situations that seem to require USB have better solutions.',
                            'answers' => [
                                ['answer' => 'Use any available USB drive to get the job done quickly', 'is_correct' => false],
                                ['answer' => 'Buy a new USB drive from a store to ensure it is clean', 'is_correct' => false],
                                ['answer' => 'Ask your IT team for a secure alternative before using a USB drive', 'is_correct' => true],
                                ['answer' => 'Disable USB port restrictions temporarily on your computer', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Course 36: AI & Deepfake Threats ──
            [
                'title' => 'AI & Deepfake Threats',
                'slug' => 'ai-deepfake-threats',
                'description' => 'Explore how artificial intelligence is being weaponized for social engineering, including deepfake audio and video, and learn strategies to defend against AI-enhanced attacks.',
                'objectives' => [
                    'Understand how AI is used to enhance social engineering attacks',
                    'Recognize the capabilities and limitations of deepfake audio and video',
                    'Identify indicators that media or communications may be AI-generated',
                    'Apply defensive strategies to protect against AI-powered threats',
                ],
                'category' => 'Social Engineering',
                'difficulty' => 'advanced',
                'duration_minutes' => 25,
                'passing_score' => 75,
                'sort_order' => 36,
                'lessons' => [
                    [
                        'title' => 'AI-Powered Social Engineering',
                        'slug' => 'ai-powered-social-engineering',
                        'duration_minutes' => 8,
                        'content' => '<h3>AI-Powered Social Engineering</h3>
<p>Artificial intelligence has fundamentally changed the social engineering threat landscape. Where attackers once needed to manually research targets, craft personalized messages, and conduct attacks one at a time, AI tools now automate and scale these activities with unprecedented sophistication. Understanding how AI enhances social engineering is critical for recognizing and defending against these evolving threats.</p>

<h3>AI-Generated Phishing at Scale</h3>
<p>Large language models can generate grammatically perfect, contextually relevant phishing emails in any language, eliminating the spelling and grammar errors that were once reliable indicators of phishing. These AI tools can analyze a target\'s social media presence, published articles, and online activity, then craft messages that match the target\'s communication style and reference real topics they care about. What previously required hours of manual research and writing can now be produced in seconds, allowing attackers to launch highly personalized campaigns against thousands of targets simultaneously.</p>

<h3>Automated Reconnaissance</h3>
<p>AI-powered tools can scrape and analyze vast amounts of publicly available information to build detailed profiles of targets and organizations. These tools identify reporting structures, recent projects, technology stacks, vendor relationships, and personal details far faster than any human researcher. The resulting profiles enable attacks that are so well-informed they are nearly indistinguishable from legitimate communications.</p>

<h3>Conversational AI for Real-Time Manipulation</h3>
<ul>
<li><strong>Chatbot impersonation:</strong> AI chatbots can impersonate IT support staff, HR representatives, or other trusted roles in real-time text conversations, adapting their responses based on the victim\'s replies to maintain the illusion</li>
<li><strong>Voice synthesis in calls:</strong> AI can generate realistic voice responses during phone calls, allowing automated systems to conduct pretexting attacks that previously required a human operator</li>
<li><strong>Multi-language attacks:</strong> AI removes language barriers entirely, enabling attackers to target victims in any language with native-sounding fluency</li>
<li><strong>Sentiment adaptation:</strong> Advanced AI systems can detect the emotional state of a conversation and adjust their approach -- becoming more empathetic if the target seems suspicious, or more authoritative if the target seems hesitant</li>
</ul>

<h3>The Lowered Barrier to Entry</h3>
<p>Perhaps the most concerning aspect of AI-powered social engineering is that it dramatically lowers the skill threshold for attackers. Previously, sophisticated social engineering required extensive experience, psychological insight, and language skills. Now, readily available AI tools provide these capabilities to anyone, meaning the volume and quality of social engineering attacks are both increasing simultaneously.</p>',
                    ],
                    [
                        'title' => 'Deepfake Audio & Video Threats',
                        'slug' => 'deepfake-audio-video-threats',
                        'duration_minutes' => 9,
                        'content' => '<h3>Deepfake Audio and Video Threats</h3>
<p>Deepfakes are AI-generated or AI-manipulated media that convincingly replicate a real person\'s appearance, voice, or both. What began as a novelty has rapidly evolved into a serious cybersecurity threat, with deepfakes being used to authorize fraudulent wire transfers, manipulate stock prices, and impersonate executives in real-time video calls. The technology has reached a point where many deepfakes are indistinguishable from genuine media to the untrained eye.</p>

<h3>Audio Deepfakes</h3>
<p>AI voice cloning technology can now create a convincing replica of someone\'s voice from as little as three seconds of sample audio -- easily obtained from earnings calls, conference presentations, YouTube videos, or voicemail greetings. In 2019, criminals used AI-generated voice deepfakes to impersonate a CEO and instruct a subsidiary to transfer 220,000 euros to a fraudulent account. The employee complied because the voice sounded exactly like his boss, including the accent and speech patterns. Audio deepfakes are particularly dangerous because people inherently trust phone calls and voice messages more than text-based communications.</p>

<h3>Video Deepfakes</h3>
<p>Video deepfakes can superimpose a person\'s face onto another body in real-time, enabling attackers to impersonate someone during a live video conference. In early 2024, a finance worker at a multinational company was deceived into transferring 25 million dollars after attending a video conference where every participant -- including the CFO -- was a deepfake. Real-time video deepfakes can now be generated using consumer-grade hardware, making this attack vector accessible to a wide range of threat actors.</p>

<h3>Indicators of Deepfake Media</h3>
<ul>
<li><strong>Audio anomalies:</strong> Unnatural pauses, inconsistent background noise between sentences, slight metallic or robotic quality, breathing patterns that do not match speaking rhythm</li>
<li><strong>Video artifacts:</strong> Flickering around the edges of the face, inconsistent lighting or shadows, unnatural blinking patterns, slight lag between lip movements and audio</li>
<li><strong>Behavioral inconsistencies:</strong> Responses that feel slightly off-topic, an inability to handle unexpected questions or tangents, unusually formal or generic phrasing</li>
<li><strong>Contextual red flags:</strong> Unusual requests during the call, reluctance to switch to a different communication channel, insistence on immediate action without normal verification steps</li>
</ul>

<h3>The Rapid Evolution Challenge</h3>
<p>Deepfake technology improves faster than detection tools can keep pace. Artifacts and indicators that revealed deepfakes a year ago may no longer be present in current-generation fakes. This means that relying solely on spotting visual or audio flaws is increasingly unreliable, and process-based defenses -- such as verification callbacks and multi-person authorization -- become more important than ever.</p>',
                    ],
                    [
                        'title' => 'Defending Against AI-Enhanced Attacks',
                        'slug' => 'defending-against-ai-enhanced-attacks',
                        'duration_minutes' => 8,
                        'content' => '<h3>Defending Against AI-Enhanced Attacks</h3>
<p>Defending against AI-powered social engineering and deepfakes requires a fundamental shift in approach. Traditional advice about spotting poor grammar in phishing emails or visual artifacts in fake videos is no longer sufficient. Instead, organizations and individuals must rely on process-based defenses, multi-factor verification, and a healthy skepticism of any communication that requests sensitive actions, regardless of how legitimate it appears.</p>

<h3>Process-Based Defenses</h3>
<ul>
<li><strong>Out-of-band verification:</strong> For any high-value request -- financial transfers, credential changes, data access -- verify through a completely separate communication channel. If you receive a request via email, verify by phone. If you receive it on a video call, verify through a separate phone call to a known number</li>
<li><strong>Multi-person authorization:</strong> Require two or more people to approve significant actions like wire transfers, vendor payment changes, or access to sensitive systems. AI can fool one person, but fooling multiple people through different channels simultaneously is exponentially harder</li>
<li><strong>Code words and challenge phrases:</strong> Establish pre-shared verification phrases that team members can use to confirm identity during calls or video conferences. These phrases should be exchanged in person and changed regularly</li>
<li><strong>Callback procedures:</strong> Never act on instructions received through any channel without calling back on a pre-established number that you retrieved independently, not one provided in the message or call</li>
</ul>

<h3>Technical Controls</h3>
<p>Organizations should deploy AI-powered detection tools that analyze communications for signs of AI generation or manipulation. Email security platforms are increasingly incorporating AI detection capabilities that flag potentially generated content. Voice authentication systems that analyze not just what is said but the underlying acoustic properties can detect synthetic speech. Digital watermarking and content provenance technologies help verify the authenticity of media files.</p>

<h3>Building an AI-Skeptical Culture</h3>
<p>The most effective defense is a workforce that understands these threats exist and maintains appropriate skepticism. This does not mean distrusting every communication, but rather applying stronger verification to unusual or high-stakes requests. Train employees to ask themselves: "If this request were coming from an AI-generated deepfake instead of the real person, would my verification process catch it?" If the answer is no, the verification process needs to be strengthened.</p>

<h3>Reducing Your Deepfake Attack Surface</h3>
<ul>
<li><strong>Limit public audio and video:</strong> Minimize publicly available recordings of yourself, especially clear audio suitable for voice cloning</li>
<li><strong>Restrict high-resolution photos:</strong> High-quality face photos make video deepfakes easier to create. Consider the necessity before posting high-resolution images publicly</li>
<li><strong>Use authenticated communication channels:</strong> Prefer platforms with strong identity verification and end-to-end encryption for sensitive discussions</li>
</ul>',
                    ],
                ],
                'quiz' => [
                    'title' => 'AI & Deepfake Threats Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'How much audio sample is typically needed to create a convincing AI voice clone?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Modern voice cloning technology can create a convincing replica from as little as three seconds of sample audio.',
                            'answers' => [
                                ['answer' => 'At least one hour of clear recordings', 'is_correct' => false],
                                ['answer' => 'Approximately 30 minutes of varied speech', 'is_correct' => false],
                                ['answer' => 'As little as three seconds of sample audio', 'is_correct' => true],
                                ['answer' => 'Multiple hours of telephone conversations', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is traditional advice about spotting phishing through grammar errors becoming less reliable?',
                            'type' => 'multiple_choice',
                            'explanation' => 'AI language models can generate grammatically perfect, contextually relevant messages in any language, eliminating the errors that were once reliable phishing indicators.',
                            'answers' => [
                                ['answer' => 'Attackers have started hiring professional editors', 'is_correct' => false],
                                ['answer' => 'AI generates grammatically perfect and contextually relevant messages, removing the errors that were once telltale signs', 'is_correct' => true],
                                ['answer' => 'Email providers now autocorrect phishing emails', 'is_correct' => false],
                                ['answer' => 'Grammar checking tools are now available to everyone', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the most reliable defense against deepfake-based requests for financial transfers?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Process-based defenses like out-of-band verification and multi-person authorization are more reliable than trying to detect deepfakes visually or audibly.',
                            'answers' => [
                                ['answer' => 'Looking for visual artifacts in the video call', 'is_correct' => false],
                                ['answer' => 'Using deepfake detection software on every call', 'is_correct' => false],
                                ['answer' => 'Out-of-band verification through a separate channel and multi-person authorization', 'is_correct' => true],
                                ['answer' => 'Asking the person on the call to blink three times', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'In the 2024 incident, how were finance workers tricked into transferring 25 million dollars?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Attackers used real-time video deepfakes to impersonate multiple executives, including the CFO, on a live video conference call.',
                            'answers' => [
                                ['answer' => 'Through a phishing email with a fake invoice', 'is_correct' => false],
                                ['answer' => 'By hacking into the company bank account directly', 'is_correct' => false],
                                ['answer' => 'Via a video conference where every participant, including the CFO, was a real-time deepfake', 'is_correct' => true],
                                ['answer' => 'Through a compromised business email account', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a recommended way to reduce your personal deepfake attack surface?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Limiting publicly available audio and video recordings reduces the material attackers can use to create convincing deepfakes of you.',
                            'answers' => [
                                ['answer' => 'Delete all social media accounts entirely', 'is_correct' => false],
                                ['answer' => 'Minimize publicly available audio and video recordings of yourself', 'is_correct' => true],
                                ['answer' => 'Only use video calls through encrypted platforms', 'is_correct' => false],
                                ['answer' => 'Wear sunglasses in all online photos', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Course 37: Security Awareness for Leadership ──
            [
                'title' => 'Security Awareness for Leadership',
                'slug' => 'security-awareness-leadership',
                'description' => 'Equip organizational leaders with the cybersecurity knowledge needed to make informed risk decisions, build security-conscious culture, and govern technology investments effectively.',
                'objectives' => [
                    'Understand the unique cyber threats targeting executives and senior leadership',
                    'Learn how to foster a security-first culture across the organization',
                    'Make informed decisions about cybersecurity governance, budgets, and risk management',
                    'Recognize leadership\'s role in incident response and regulatory compliance',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'advanced',
                'duration_minutes' => 30,
                'passing_score' => 75,
                'sort_order' => 37,
                'lessons' => [
                    [
                        'title' => 'The Executive Threat Landscape',
                        'slug' => 'executive-threat-landscape',
                        'duration_minutes' => 10,
                        'content' => '<h3>The Executive Threat Landscape</h3>
<p>Senior leaders face a unique and elevated set of cybersecurity threats. As individuals with authority over financial decisions, strategic direction, and sensitive information, executives are high-value targets for cybercriminals, nation-state actors, and corporate espionage operations. Understanding these threats is not optional for modern leadership -- it is a core business competency.</p>

<h3>Why Executives Are Targeted</h3>
<p>Executives have several characteristics that make them attractive targets. They have the authority to approve large financial transactions without additional approvals. They have access to the most sensitive strategic, financial, and personnel information in the organization. Their public profiles -- speaking engagements, press interviews, board memberships, and social media presence -- provide attackers with detailed information for crafting convincing pretexts. And their busy schedules often mean they review and respond to communications quickly, with less scrutiny than other employees might apply.</p>

<h3>Business Email Compromise Targeting Leadership</h3>
<p>Business email compromise (BEC) attacks targeting executives have caused billions of dollars in losses globally. In a typical BEC attack, an attacker either compromises an executive\'s actual email account or creates a convincing impersonation, then sends instructions to employees in finance or accounting to process urgent wire transfers. The FBI estimates BEC losses exceeded 50 billion dollars globally between 2013 and 2023. These attacks succeed because employees are reluctant to question or delay requests that appear to come from senior leadership.</p>

<h3>Threats Beyond the Office</h3>
<ul>
<li><strong>Personal device compromise:</strong> Executives often access sensitive corporate data from personal phones, tablets, and home computers that may have weaker security than corporate-managed devices</li>
<li><strong>Travel risks:</strong> Hotel Wi-Fi networks, business center computers, and charging stations in airports can all be compromised. Nation-state actors have been known to target executive hotel rooms during international business trips</li>
<li><strong>Family targeting:</strong> Attackers may target executives\' family members as a vector to compromise the executive\'s personal accounts or devices, or to gather information useful for social engineering</li>
<li><strong>Board and M&A exposure:</strong> Board communications and merger-and-acquisition discussions represent extremely high-value targets for insider trading, competitive intelligence, and market manipulation</li>
</ul>

<h3>The Accountability Factor</h3>
<p>Regulatory frameworks increasingly hold executives personally accountable for cybersecurity failures. The SEC now requires public companies to disclose material cybersecurity incidents within four business days. Executives at several organizations have faced personal liability, termination, and even criminal charges following breaches that were attributed to negligent security practices. Cybersecurity is no longer something leaders can fully delegate -- it requires their direct engagement and informed oversight.</p>',
                    ],
                    [
                        'title' => 'Building a Security-First Culture',
                        'slug' => 'building-security-first-culture',
                        'duration_minutes' => 10,
                        'content' => '<h3>Building a Security-First Culture</h3>
<p>Technology alone cannot protect an organization from cyber threats. The most expensive security tools are rendered ineffective when employees bypass them for convenience, ignore security policies, or feel that security is "someone else\'s job." Building a security-first culture -- where every employee understands their role in protecting the organization and feels empowered to act on it -- is the single most impactful investment a leader can make in cybersecurity.</p>

<h3>Leading by Example</h3>
<p>Culture starts at the top. When senior leaders visibly prioritize security, the entire organization follows. This means executives should use multi-factor authentication on all their accounts, follow the same security policies as every other employee, participate in security awareness training rather than exempting themselves, openly discuss security incidents and near-misses as learning opportunities, and praise employees who report suspicious activity even when it turns out to be a false alarm.</p>

<h3>Creating Psychological Safety Around Security</h3>
<ul>
<li><strong>Encourage reporting without fear:</strong> Employees should feel safe reporting that they clicked a phishing link, lost a device, or shared a password without fear of punishment. A blame-free reporting culture dramatically reduces the time between a security incident and its detection, which directly limits damage</li>
<li><strong>Reward security-conscious behavior:</strong> Recognize employees who identify phishing attempts, report suspicious activity, or suggest security improvements. This reinforces that security is valued alongside productivity and revenue</li>
<li><strong>Frame security as enabling, not blocking:</strong> Position security practices as enabling the organization to move faster with confidence, not as barriers to productivity. When employees understand why a policy exists -- protecting customer data, preventing ransomware that could shut down operations for weeks -- compliance becomes voluntary rather than grudging</li>
</ul>

<h3>Effective Security Awareness Programs</h3>
<p>Compliance-focused, checkbox security training that employees click through once a year is largely ineffective. Effective security awareness programs use frequent, short training modules rather than annual marathons. They incorporate realistic simulated phishing exercises with immediate feedback. They tailor content to specific roles -- finance teams learn about BEC, developers learn about secure coding, and executives learn about whaling. They measure behavior change over time, not just quiz scores, tracking metrics like phishing simulation click rates, reporting rates, and time to report.</p>

<h3>Allocating Resources for Culture Change</h3>
<p>Building a security-first culture requires investment in dedicated security awareness staff, modern training platforms, realistic simulation tools, and time for employees to participate in training. Leaders who view security awareness spending as a cost center rather than a risk reduction investment will find their organizations repeatedly victimized by attacks that exploit human behavior -- the one vulnerability that no firewall or endpoint tool can fully address.</p>',
                    ],
                    [
                        'title' => 'Cybersecurity Governance & Investment',
                        'slug' => 'cybersecurity-governance-investment',
                        'duration_minutes' => 10,
                        'content' => '<h3>Cybersecurity Governance and Investment</h3>
<p>Effective cybersecurity governance ensures that an organization\'s security posture aligns with its business objectives, risk tolerance, and regulatory obligations. For leaders, this means understanding enough about cybersecurity to ask the right questions, allocate appropriate resources, and make informed decisions about risk -- without needing to become technical experts themselves.</p>

<h3>Key Questions Leaders Should Ask</h3>
<ul>
<li><strong>What are our crown jewels?</strong> Identify the organization\'s most critical assets -- customer data, intellectual property, financial systems, operational technology -- and ensure they receive proportionate protection</li>
<li><strong>What is our risk exposure?</strong> Understand the organization\'s attack surface, threat landscape, and the potential business impact of a breach. This should be quantified in business terms like potential revenue loss, regulatory fines, and reputational damage</li>
<li><strong>Are we meeting regulatory requirements?</strong> Ensure the organization complies with all applicable cybersecurity regulations and standards, and that compliance is documented and auditable</li>
<li><strong>How do we compare to peers?</strong> Benchmark your security posture against industry peers and recognized frameworks like NIST Cybersecurity Framework or ISO 27001</li>
<li><strong>What is our incident response readiness?</strong> Confirm that the organization has a tested incident response plan, that key decision-makers know their roles, and that the plan has been exercised through tabletop simulations</li>
</ul>

<h3>Budgeting for Cybersecurity</h3>
<p>Cybersecurity spending should be treated as risk management, not as an IT expense. Industry benchmarks suggest that organizations typically spend between 5 and 15 percent of their IT budget on security, though the appropriate amount varies significantly based on industry, regulatory requirements, and risk tolerance. When evaluating security investments, consider the cost of a breach -- averaging 4.45 million dollars in 2023 according to IBM -- against the cost of prevention. Effective security spending focuses on the highest-impact risks first rather than pursuing complete coverage of every possible threat.</p>

<h3>The Board\'s Role in Cybersecurity</h3>
<p>Boards of directors have a fiduciary responsibility to ensure adequate cybersecurity oversight. This means receiving regular security briefings in business-relevant language, reviewing and approving cybersecurity budgets and strategies, ensuring the organization has appropriate cyber insurance coverage, understanding the organization\'s incident response plan and their role in it, and holding management accountable for maintaining an acceptable security posture. Many boards are now adding directors with cybersecurity expertise or creating dedicated cybersecurity committees to ensure informed oversight.</p>

<h3>Measuring Security Effectiveness</h3>
<p>Leaders need metrics that demonstrate whether security investments are working. Useful metrics include mean time to detect and respond to incidents, percentage of employees falling for simulated phishing, patch compliance rates across systems, time to remediate known vulnerabilities, and the number and severity of security incidents over time. These metrics should be reported to leadership regularly and trended over time to show improvement or highlight areas needing attention.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Security Awareness for Leadership Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'Why is a blame-free reporting culture important for cybersecurity?',
                            'type' => 'multiple_choice',
                            'explanation' => 'When employees feel safe reporting mistakes, incidents are detected faster, which directly limits the damage an attacker can do.',
                            'answers' => [
                                ['answer' => 'It reduces the cost of security training programs', 'is_correct' => false],
                                ['answer' => 'It ensures no one is ever held accountable for security failures', 'is_correct' => false],
                                ['answer' => 'It reduces the time between a security incident and its detection, directly limiting damage', 'is_correct' => true],
                                ['answer' => 'It eliminates the need for incident response plans', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What percentage of IT budget do organizations typically spend on cybersecurity?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Industry benchmarks suggest organizations typically spend between 5 and 15 percent of their IT budget on security, varying by industry and risk.',
                            'answers' => [
                                ['answer' => 'Less than 1 percent', 'is_correct' => false],
                                ['answer' => 'Between 5 and 15 percent', 'is_correct' => true],
                                ['answer' => 'Between 30 and 50 percent', 'is_correct' => false],
                                ['answer' => 'Over 60 percent', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'According to the SEC, how quickly must public companies disclose material cybersecurity incidents?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The SEC requires public companies to disclose material cybersecurity incidents within four business days of determining materiality.',
                            'answers' => [
                                ['answer' => 'Within 24 hours', 'is_correct' => false],
                                ['answer' => 'Within four business days', 'is_correct' => true],
                                ['answer' => 'Within 30 calendar days', 'is_correct' => false],
                                ['answer' => 'At the next quarterly earnings report', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the most effective type of security awareness training?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Frequent, role-specific training with realistic simulations and behavior measurement is far more effective than annual compliance-focused sessions.',
                            'answers' => [
                                ['answer' => 'A single annual all-hands presentation covering general security topics', 'is_correct' => false],
                                ['answer' => 'Frequent short modules with realistic simulations, role-specific content, and behavior measurement over time', 'is_correct' => true],
                                ['answer' => 'Sending a monthly email newsletter about security news', 'is_correct' => false],
                                ['answer' => 'Requiring employees to pass a written exam every quarter', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'As a leader, which approach to cybersecurity investment is most effective?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Treating security as risk management and focusing on the highest-impact risks first provides the best return on security investment.',
                            'answers' => [
                                ['answer' => 'Spending as little as possible since breaches are unlikely', 'is_correct' => false],
                                ['answer' => 'Buying the most expensive security products available', 'is_correct' => false],
                                ['answer' => 'Treating security as risk management and focusing investment on the highest-impact risks first', 'is_correct' => true],
                                ['answer' => 'Delegating all security decisions entirely to the IT department', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
