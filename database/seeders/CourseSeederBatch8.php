<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Database\Seeder;

class CourseSeederBatch8 extends Seeder
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
            // ── Module 38: Cryptocurrency & Financial Scams ──
            [
                'title' => 'Cryptocurrency & Financial Scams',
                'slug' => 'cryptocurrency-financial-scams',
                'description' => 'Learn to identify and defend against cryptocurrency fraud, pig butchering scams, invoice manipulation, and other financial social engineering attacks targeting individuals and organizations.',
                'objectives' => [
                    'Recognize common financial scam techniques used against employees and organizations',
                    'Identify cryptocurrency fraud schemes including pig butchering and fake investment platforms',
                    'Detect invoice fraud and business email compromise targeting payment processes',
                    'Apply verification procedures to protect against financial manipulation',
                ],
                'category' => 'Phishing & Email Security',
                'difficulty' => 'intermediate',
                'duration_minutes' => 20,
                'passing_score' => 75,
                'sort_order' => 38,
                'lessons' => [
                    [
                        'title' => 'Common Financial Scam Techniques',
                        'slug' => 'common-financial-scam-techniques',
                        'duration_minutes' => 7,
                        'content' => '<h3>Common Financial Scam Techniques</h3>
<p>Financial scams have evolved far beyond the obvious Nigerian prince emails of decades past. Modern financial fraud is sophisticated, patient, and often indistinguishable from legitimate business communications. Attackers study their targets, build rapport over weeks or months, and use psychological manipulation to bypass the critical thinking that would otherwise protect their victims.</p>

<h3>Advance Fee Fraud</h3>
<p>In advance fee fraud, the attacker promises a large payout — an inheritance, a lottery winning, a business opportunity — but requires the victim to pay upfront fees for taxes, processing, or legal costs. Each payment leads to another required fee, and the promised payout never arrives. While this seems obvious in description, modern versions are highly targeted: a startup founder might receive a convincing offer from a fake venture capital firm that requires a due diligence fee, or an employee might be told they have won a company bonus that requires tax prepayment through gift cards.</p>

<h3>Romance and Trust-Based Scams</h3>
<p>Attackers build genuine-feeling relationships over dating apps, social media, or professional networks. Over weeks or months, they establish emotional trust before introducing a financial element — a business opportunity, a medical emergency, or an investment tip. These scams exploit the fact that people are far less skeptical of financial requests from someone they believe they know and trust. The emotional investment makes victims reluctant to accept they have been deceived, often leading to repeated losses.</p>

<h3>Authority and Urgency Manipulation</h3>
<ul>
<li><strong>Government impersonation:</strong> Scammers pose as tax authorities, law enforcement, or regulatory agencies threatening arrest or fines unless immediate payment is made</li>
<li><strong>Tech support scams:</strong> Fake alerts claim your computer is compromised, leading to remote access grants and payment for unnecessary services</li>
<li><strong>Prize and lottery scams:</strong> Notifications of winnings you never entered, requiring fees or personal information to claim</li>
<li><strong>Employment scams:</strong> Fake job offers requiring equipment purchases or advance payments for training materials</li>
</ul>

<p>The common thread across all financial scams is the manipulation of trust, authority, or emotion to override rational decision-making. Recognizing these psychological levers is the first step in defending against them.</p>',
                    ],
                    [
                        'title' => 'Cryptocurrency Fraud & Pig Butchering',
                        'slug' => 'cryptocurrency-fraud-pig-butchering',
                        'duration_minutes' => 7,
                        'content' => '<h3>Cryptocurrency Fraud and Pig Butchering</h3>
<p>Cryptocurrency has created entirely new categories of financial fraud. The combination of irreversible transactions, pseudonymous accounts, and widespread unfamiliarity with blockchain technology makes crypto an ideal medium for scammers. Understanding these schemes is essential whether or not you personally invest in cryptocurrency, because attackers increasingly target employees through workplace channels.</p>

<h3>What Is Pig Butchering?</h3>
<p>Pig butchering — named because the scammer "fattens" the victim before the "slaughter" — is a long-con investment scam that has caused billions of dollars in losses worldwide. The attacker contacts the victim through a dating app, messaging platform, or even a wrong-number text message. Over weeks or months, they build a relationship and casually mention their success with cryptocurrency investing. Eventually, they introduce the victim to a fake trading platform that shows fabricated profits. The victim deposits real money, sees impressive fake returns, and invests more. When they try to withdraw, the platform demands fees, taxes, or additional deposits, and eventually disappears entirely.</p>

<h3>Fake Investment Platforms and Exchanges</h3>
<ul>
<li><strong>Clone sites:</strong> Scammers create pixel-perfect copies of legitimate exchanges like Coinbase or Binance with slightly altered URLs, tricking users into entering credentials or depositing funds</li>
<li><strong>Fake DeFi protocols:</strong> Fraudulent decentralized finance platforms promise unrealistic yields of hundreds of percent, then drain connected wallets through malicious smart contracts</li>
<li><strong>Pump and dump schemes:</strong> Coordinated groups artificially inflate the price of a low-value token through social media hype, then sell their holdings when newcomers buy in, crashing the price</li>
<li><strong>Rug pulls:</strong> Developers create a new cryptocurrency project, attract investment, and then abandon the project and disappear with the funds</li>
</ul>

<h3>Red Flags for Cryptocurrency Scams</h3>
<p>Guaranteed returns are the single biggest warning sign — no legitimate investment can guarantee profits, and cryptocurrency is inherently volatile. Pressure to act quickly, requests to use unfamiliar platforms, unsolicited investment advice from online contacts, and requirements to pay in cryptocurrency for non-crypto services all indicate fraud. If someone you met online is eager to teach you about crypto investing, that is almost certainly a pig butchering setup regardless of how genuine the relationship feels.</p>

<p>Remember that cryptocurrency transactions are irreversible. Unlike credit card charges or bank transfers, there is no institution to file a dispute with and no way to reverse a completed transaction. Once crypto leaves your wallet, it is gone.</p>',
                    ],
                    [
                        'title' => 'Invoice & Payment Fraud Prevention',
                        'slug' => 'invoice-payment-fraud-prevention',
                        'duration_minutes' => 6,
                        'content' => '<h3>Invoice and Payment Fraud Prevention</h3>
<p>Invoice and payment fraud targets the financial processes of organizations, exploiting the routine nature of accounts payable workflows. Because companies process hundreds or thousands of invoices regularly, a single fraudulent invoice can easily slip through if proper controls are not in place. The FBI estimates that business email compromise, which includes invoice fraud, has caused over 50 billion dollars in global losses.</p>

<h3>Common Invoice Fraud Schemes</h3>
<ul>
<li><strong>Vendor impersonation:</strong> An attacker sends an email appearing to come from a known vendor, requesting that future payments be sent to new bank account details. The email may reference real invoice numbers and contract terms gathered through prior reconnaissance or email compromise</li>
<li><strong>Fake invoice injection:</strong> Fraudulent invoices for products or services never ordered are submitted, often for small amounts that fall below approval thresholds and get paid automatically</li>
<li><strong>Man-in-the-middle attacks:</strong> Attackers intercept email threads between your organization and a legitimate vendor, then insert themselves into the conversation to redirect payments</li>
<li><strong>Internal fraud:</strong> Employees create fictitious vendors and submit invoices to themselves, or manipulate existing vendor records to redirect payments</li>
</ul>

<h3>Verification Controls That Work</h3>
<p>The most effective defense against invoice fraud is a robust verification process. Any request to change vendor banking details should be verified through a phone call to a known contact number — never a number provided in the email requesting the change. Dual authorization should be required for payments above a defined threshold, ensuring no single person can approve a large payment without review. New vendors should go through a formal onboarding process that includes identity verification before any invoices are processed.</p>

<h3>What You Can Do</h3>
<ul>
<li><strong>Verify banking changes:</strong> Always call the vendor on a previously established phone number before updating payment details</li>
<li><strong>Question unexpected invoices:</strong> If you receive an invoice you did not expect, confirm the order with the department that supposedly placed it</li>
<li><strong>Check email headers:</strong> Look carefully at sender addresses on payment-related emails for subtle misspellings or domain changes</li>
<li><strong>Report anomalies:</strong> If anything about a payment request feels unusual, flag it to your finance team or manager before processing</li>
</ul>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Cryptocurrency & Financial Scams Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is a "pig butchering" scam?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Pig butchering is a long-con where the scammer builds a relationship over time, introduces the victim to a fake investment platform showing fabricated profits, and eventually steals all deposited funds.',
                            'answers' => [
                                ['answer' => 'A scam targeting agricultural businesses with fake livestock sales', 'is_correct' => false],
                                ['answer' => 'A long-con where the scammer builds trust over time, then lures the victim into a fake investment platform to steal their money', 'is_correct' => true],
                                ['answer' => 'A type of ransomware that encrypts files and demands cryptocurrency payment', 'is_correct' => false],
                                ['answer' => 'A phishing attack that targets restaurant and food service employees', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is cryptocurrency particularly attractive to scammers?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Cryptocurrency transactions are irreversible, pseudonymous, and cannot be disputed through a bank or credit card company, making stolen funds nearly impossible to recover.',
                            'answers' => [
                                ['answer' => 'Cryptocurrency is regulated by international law enforcement agencies', 'is_correct' => false],
                                ['answer' => 'Cryptocurrency transactions are slow, giving victims time to reverse them', 'is_correct' => false],
                                ['answer' => 'Transactions are irreversible and pseudonymous, making stolen funds very difficult to recover', 'is_correct' => true],
                                ['answer' => 'Cryptocurrency can only be purchased through verified identity checks', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'A vendor emails requesting that future payments be sent to a new bank account. What should you do first?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Banking detail change requests are a primary invoice fraud technique. Always verify by calling the vendor on a previously known phone number, not one provided in the email.',
                            'answers' => [
                                ['answer' => 'Update the bank details immediately to avoid payment delays', 'is_correct' => false],
                                ['answer' => 'Reply to the email asking them to confirm the new details', 'is_correct' => false],
                                ['answer' => 'Call the vendor on a previously established phone number to verify the change', 'is_correct' => true],
                                ['answer' => 'Forward the email to your personal account to review later', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of the following is the biggest red flag for a cryptocurrency investment scam?',
                            'type' => 'multiple_choice',
                            'explanation' => 'No legitimate investment can guarantee returns. Promises of guaranteed profits, especially in the highly volatile cryptocurrency market, are a definitive indicator of fraud.',
                            'answers' => [
                                ['answer' => 'The platform charges transaction fees', 'is_correct' => false],
                                ['answer' => 'The investment requires creating an account with identity verification', 'is_correct' => false],
                                ['answer' => 'The platform or person promises guaranteed high returns with no risk', 'is_correct' => true],
                                ['answer' => 'The platform is available as a mobile app', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What makes fake invoice fraud difficult to detect in large organizations?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Organizations process many invoices routinely, and small fraudulent invoices often fall below manual review thresholds, getting paid automatically without scrutiny.',
                            'answers' => [
                                ['answer' => 'Invoices are always paid in cash, leaving no paper trail', 'is_correct' => false],
                                ['answer' => 'High invoice volume means small fraudulent invoices can slip below approval thresholds and get paid automatically', 'is_correct' => true],
                                ['answer' => 'Organizations do not keep records of their vendor relationships', 'is_correct' => false],
                                ['answer' => 'Accounting software cannot read invoice documents', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 39: IoT Security in the Workplace ──
            [
                'title' => 'IoT Security in the Workplace',
                'slug' => 'iot-security-workplace',
                'description' => 'Understand the security risks posed by Internet of Things devices in office environments and learn practical steps to minimize the attack surface they create.',
                'objectives' => [
                    'Identify common IoT devices in workplace environments and their associated risks',
                    'Understand how compromised IoT devices can be used to attack organizational networks',
                    'Apply security best practices when using smart office equipment',
                    'Explain the concept of network segmentation and its role in IoT security',
                ],
                'category' => 'Mobile & Remote Work Security',
                'difficulty' => 'intermediate',
                'duration_minutes' => 20,
                'passing_score' => 70,
                'sort_order' => 39,
                'lessons' => [
                    [
                        'title' => 'IoT Devices & Their Risks',
                        'slug' => 'iot-devices-and-their-risks',
                        'duration_minutes' => 7,
                        'content' => '<h3>IoT Devices and Their Risks</h3>
<p>The Internet of Things (IoT) refers to the growing network of physical devices that connect to the internet and communicate with other systems. In the modern workplace, IoT devices are everywhere — smart thermostats, connected printers, security cameras, conference room displays, badge readers, smart lighting systems, and even connected coffee machines. While these devices improve convenience and efficiency, each one represents a potential entry point for attackers.</p>

<h3>Why IoT Devices Are Vulnerable</h3>
<p>IoT devices were historically designed with functionality as the priority and security as an afterthought. Many devices run minimal operating systems with limited capacity for security features like encryption, authentication, or automatic patching. Unlike laptops and servers that receive regular security updates, many IoT devices ship with firmware that is rarely or never updated after deployment. Some devices have hardcoded default passwords that cannot be changed, or they use unencrypted communication protocols that expose data to anyone monitoring the network.</p>

<h3>Common Workplace IoT Risks</h3>
<ul>
<li><strong>Default credentials:</strong> Many IoT devices ship with factory-set usernames and passwords such as admin/admin or admin/password. Lists of default credentials for thousands of device models are freely available online, making any device left at factory settings an open door</li>
<li><strong>Unpatched firmware:</strong> Manufacturers may stop releasing updates for older devices, leaving known vulnerabilities permanently unaddressed. Even when patches are available, many organizations lack a process for updating IoT firmware</li>
<li><strong>Insecure protocols:</strong> Some IoT devices transmit data using unencrypted protocols, meaning anyone on the same network can intercept communications including credentials and sensitive data</li>
<li><strong>Excessive permissions:</strong> Devices often request or receive more network access than they need for their function, expanding the potential damage if they are compromised</li>
</ul>

<h3>Real-World IoT Attacks</h3>
<p>In a widely reported incident, attackers breached a casino\'s network through an internet-connected fish tank thermometer in the lobby. The thermometer had network access to communicate temperature readings, and attackers used it as a pivot point to reach the casino\'s high-roller database. In another case, the Mirai botnet compromised hundreds of thousands of IoT devices — primarily security cameras and routers with default passwords — and used them to launch massive denial-of-service attacks that took down major websites including Twitter, Netflix, and Reddit.</p>',
                    ],
                    [
                        'title' => 'Securing Smart Office Equipment',
                        'slug' => 'securing-smart-office-equipment',
                        'duration_minutes' => 7,
                        'content' => '<h3>Securing Smart Office Equipment</h3>
<p>Smart office equipment enhances productivity but requires deliberate security practices to prevent it from becoming a liability. Every connected device in your workplace — from the printer down the hall to the smart display in the conference room — needs to be treated as part of your organization\'s security perimeter. As an employee, you play an important role in ensuring these devices do not become weak links.</p>

<h3>Printers and Multifunction Devices</h3>
<p>Network-connected printers are among the most overlooked security risks in any office. Modern multifunction printers store copies of every document scanned, printed, or faxed on internal hard drives. They have their own web-based administration interfaces, often accessible with default credentials. An attacker who compromises a printer can intercept sensitive documents, use the printer as a foothold into the network, or even modify documents in transit. Always ensure your organization\'s printers have default passwords changed, firmware updated, and print logs reviewed.</p>

<h3>Smart Displays and Conference Room Systems</h3>
<p>Conference room technology including smart TVs, video conferencing systems, and wireless presentation devices often connect to the corporate network and may have microphones and cameras. An unsecured conference room device could allow an attacker to eavesdrop on meetings, capture screen shares containing sensitive information, or access shared network resources. Ensure these devices are updated regularly and that wireless casting features require authentication.</p>

<h3>Practical Security Steps for Employees</h3>
<ul>
<li><strong>Report unfamiliar devices:</strong> If you notice a new device connected to the network or plugged into a network port that you do not recognize, report it to IT immediately. Rogue devices planted by attackers are a real threat</li>
<li><strong>Do not connect personal IoT devices:</strong> Avoid connecting personal smart devices — fitness trackers, smart speakers, personal hotspots — to the corporate network without IT approval. Each unauthorized device expands the attack surface</li>
<li><strong>Use secure printing:</strong> When printing sensitive documents, use pull-printing or secure print release features that require you to authenticate at the printer before your job is printed, preventing documents from sitting uncollected in the output tray</li>
<li><strong>Clear after meetings:</strong> After using a conference room, ensure any shared content is disconnected and that wireless casting sessions are properly ended</li>
</ul>

<p>Treat every connected device with the same caution you would give to any computer on the network. If it has an IP address, it can be targeted, and if it can be targeted, it needs to be secured.</p>',
                    ],
                    [
                        'title' => 'IoT Network Segmentation Basics',
                        'slug' => 'iot-network-segmentation-basics',
                        'duration_minutes' => 6,
                        'content' => '<h3>IoT Network Segmentation Basics</h3>
<p>Network segmentation is one of the most effective strategies for managing the security risks of IoT devices. The core idea is simple: instead of placing all devices on a single flat network where everything can communicate with everything else, you divide the network into separate segments with controlled access between them. This way, if an IoT device is compromised, the attacker\'s reach is limited to that segment rather than the entire network.</p>

<h3>How Segmentation Protects You</h3>
<p>Imagine your organization\'s network as a building. Without segmentation, every room is connected by open hallways — anyone who gets into the building through any entrance can walk to any room. With segmentation, each floor has locked doors that require specific credentials to pass through. An intruder who enters through a ground-floor window cannot reach the executive offices on the fifth floor without the right keys. Network segmentation works the same way: a compromised smart thermostat on the IoT segment cannot reach the finance servers on the business-critical segment.</p>

<h3>Common Segmentation Approaches</h3>
<ul>
<li><strong>Separate IoT VLAN:</strong> All IoT devices are placed on their own Virtual Local Area Network (VLAN), isolated from the corporate network where employees\' computers and business servers reside. IoT devices can reach the internet for updates and cloud services but cannot communicate with internal business systems</li>
<li><strong>Guest network for personal devices:</strong> Personal devices and visitor equipment connect to a guest network that provides internet access but no access to internal resources. This prevents personal IoT devices from interacting with corporate systems</li>
<li><strong>Micro-segmentation:</strong> For high-security environments, each IoT device or small group of devices gets its own segment with specific rules governing exactly what it can and cannot communicate with</li>
</ul>

<h3>What This Means for You</h3>
<p>As an employee, you may encounter network segmentation as different Wi-Fi networks for different purposes — a corporate network for your work laptop, a separate network for IoT and smart devices, and a guest network for visitors and personal devices. Always connect to the correct network for your device type. Connecting a personal smart device to the corporate network bypasses segmentation controls and creates exactly the risk that segmentation is designed to prevent.</p>

<ul>
<li><strong>Follow the network policy:</strong> Connect work devices to the corporate network and personal devices to the guest or designated network only</li>
<li><strong>Do not bridge networks:</strong> Never use your work laptop as a hotspot or bridge between the corporate network and other devices, as this creates an uncontrolled pathway between segments</li>
<li><strong>Report connectivity issues properly:</strong> If an IoT device cannot reach a resource it needs, submit an IT request rather than attempting to move it to a different network yourself</li>
</ul>',
                    ],
                ],
                'quiz' => [
                    'title' => 'IoT Security in the Workplace Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'Why are IoT devices particularly vulnerable to cyberattacks?',
                            'type' => 'multiple_choice',
                            'explanation' => 'IoT devices often have limited security features, ship with default credentials, use unencrypted protocols, and rarely receive firmware updates after deployment.',
                            'answers' => [
                                ['answer' => 'IoT devices are always connected to the public internet without any firewall', 'is_correct' => false],
                                ['answer' => 'They often have limited security features, default credentials, and rarely receive firmware updates', 'is_correct' => true],
                                ['answer' => 'IoT devices are designed to be hacked for testing purposes', 'is_correct' => false],
                                ['answer' => 'They use the same operating system as desktop computers but without antivirus', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is network segmentation in the context of IoT security?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Network segmentation divides a network into isolated segments so that a compromised IoT device cannot reach business-critical systems on other segments.',
                            'answers' => [
                                ['answer' => 'Disconnecting all IoT devices from the internet entirely', 'is_correct' => false],
                                ['answer' => 'Dividing the network into separate segments so IoT devices are isolated from critical business systems', 'is_correct' => true],
                                ['answer' => 'Encrypting all IoT device communications with the same key', 'is_correct' => false],
                                ['answer' => 'Physically separating IoT devices into different rooms in the office', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You find an unfamiliar small device plugged into a network port in a conference room. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Unknown devices connected to network ports could be rogue devices planted by attackers to gain persistent network access. Report them to IT immediately.',
                            'answers' => [
                                ['answer' => 'Unplug it and throw it away since it is probably broken', 'is_correct' => false],
                                ['answer' => 'Leave it alone — someone from IT probably installed it', 'is_correct' => false],
                                ['answer' => 'Report it to IT immediately without removing it, as it could be a rogue device', 'is_correct' => true],
                                ['answer' => 'Plug it into your laptop to see what it does', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should personal smart devices not be connected to the corporate Wi-Fi network?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Personal IoT devices may have unknown security vulnerabilities and bypass network segmentation controls, potentially giving attackers a pathway into corporate systems.',
                            'answers' => [
                                ['answer' => 'Personal devices use too much bandwidth and slow down the network', 'is_correct' => false],
                                ['answer' => 'They expand the attack surface and may bypass network segmentation designed to protect corporate systems', 'is_correct' => true],
                                ['answer' => 'Corporate Wi-Fi signals can damage personal device batteries', 'is_correct' => false],
                                ['answer' => 'It is illegal to connect personal devices to any workplace network', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'How did attackers breach a casino network through an IoT device?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Attackers compromised an internet-connected fish tank thermometer that had network access, then used it as a pivot point to reach the casino\'s internal database.',
                            'answers' => [
                                ['answer' => 'They hacked the casino\'s smart slot machines to gain admin access', 'is_correct' => false],
                                ['answer' => 'They compromised an internet-connected fish tank thermometer and used it to pivot to the internal database', 'is_correct' => true],
                                ['answer' => 'They installed a fake security camera that intercepted Wi-Fi traffic', 'is_correct' => false],
                                ['answer' => 'They exploited a vulnerability in the casino\'s smart lighting system', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 40: Zero Trust Security Principles ──
            [
                'title' => 'Zero Trust Security Principles',
                'slug' => 'zero-trust-security-principles',
                'description' => 'Understand the Zero Trust security model, its core principles, and how it changes everyday security behavior for all employees in an organization.',
                'objectives' => [
                    'Explain the Zero Trust security model and how it differs from traditional perimeter-based security',
                    'Understand the principle of "never trust, always verify" and its practical implications',
                    'Recognize how Zero Trust affects daily work activities like authentication and data access',
                    'Support organizational Zero Trust initiatives through compliant security behavior',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'advanced',
                'duration_minutes' => 25,
                'passing_score' => 75,
                'sort_order' => 40,
                'lessons' => [
                    [
                        'title' => 'What Is Zero Trust',
                        'slug' => 'what-is-zero-trust',
                        'duration_minutes' => 8,
                        'content' => '<h3>What Is Zero Trust?</h3>
<p>Zero Trust is a security model built on the principle that no user, device, or network connection should be automatically trusted, regardless of whether they are inside or outside the organization\'s network. Traditional security models operated like a castle with a moat — once you were inside the walls, you were trusted. Zero Trust eliminates that assumption and requires continuous verification of every access request.</p>

<h3>Why Traditional Security Models Failed</h3>
<p>The traditional perimeter-based approach assumed that threats existed primarily outside the network. Firewalls and VPNs formed a barrier, and anything inside was considered safe. This model broke down for several reasons. Cloud computing moved applications and data outside the traditional perimeter. Remote work meant employees accessed resources from home networks, coffee shops, and airports. Mobile devices blurred the line between personal and corporate systems. And critically, attackers who breached the perimeter — through phishing, compromised credentials, or insider threats — had unrestricted access to move laterally across the entire network.</p>

<h3>The Core Principles of Zero Trust</h3>
<ul>
<li><strong>Never trust, always verify:</strong> Every access request must be authenticated and authorized, regardless of where it originates. Being on the corporate network or VPN does not automatically grant trust</li>
<li><strong>Least privilege access:</strong> Users and systems receive only the minimum permissions necessary for their specific tasks. Access is granted just-in-time and revoked when no longer needed</li>
<li><strong>Assume breach:</strong> The security model is designed with the assumption that attackers may already be inside the network. Controls are placed around every resource, not just at the perimeter</li>
<li><strong>Verify explicitly:</strong> Access decisions consider multiple signals — user identity, device health, location, behavior patterns, and the sensitivity of the resource being accessed</li>
<li><strong>Micro-segmentation:</strong> The network is divided into small zones, and access between zones is strictly controlled, limiting an attacker\'s ability to move laterally</li>
</ul>

<p>Zero Trust is not a single product you can purchase. It is a strategic approach to security architecture that involves identity management, device security, network design, application security, and data protection working together as a unified system.</p>',
                    ],
                    [
                        'title' => 'Never Trust, Always Verify in Practice',
                        'slug' => 'never-trust-always-verify-in-practice',
                        'duration_minutes' => 9,
                        'content' => '<h3>Never Trust, Always Verify in Practice</h3>
<p>The "never trust, always verify" principle sounds straightforward, but implementing it changes how organizations handle authentication, authorization, and access control at every level. Understanding what this looks like in practice helps you appreciate why certain security measures exist and why they may sometimes feel more rigorous than what you have experienced before.</p>

<h3>Continuous Authentication</h3>
<p>In a Zero Trust environment, logging in once at the start of the day is not sufficient. The system continuously evaluates whether your access should continue based on changing conditions. If you log in from your usual office but then your account attempts to access a sensitive database from a different geographic location minutes later, the system recognizes this as anomalous and may require re-authentication or block the request entirely. This is known as adaptive or risk-based authentication — the level of verification required adjusts based on the risk level of each action.</p>

<h3>Device Trust and Health Checks</h3>
<p>Zero Trust extends verification beyond the user to the device they are using. Before granting access to corporate resources, the system may check whether your device has current security patches, an active and updated endpoint protection agent, disk encryption enabled, and a compliant configuration. A device that fails these health checks may be granted limited access — such as email only — or denied access entirely until it is brought into compliance. This is why keeping your device updated and compliant with security policies is not just good practice; it directly affects your ability to do your work.</p>

<h3>Context-Aware Access Decisions</h3>
<ul>
<li><strong>Location:</strong> Accessing payroll data from your usual office location may proceed normally, while the same request from an unfamiliar country triggers additional verification or is blocked</li>
<li><strong>Time:</strong> Accessing development systems during business hours is routine; the same access at 3 AM may require re-authentication and generate an alert</li>
<li><strong>Behavior:</strong> Downloading a few files from a project folder is normal; downloading the entire contents of a shared drive triggers a data loss prevention alert</li>
<li><strong>Resource sensitivity:</strong> Accessing a general knowledge base may require standard authentication, while accessing customer financial data requires MFA and approval from a data steward</li>
</ul>

<p>These contextual factors combine into a trust score for each access request. The higher the risk — due to unusual location, device issues, or sensitive data — the more verification is required. This dynamic approach replaces the binary "inside the network equals trusted" model with a nuanced, continuous evaluation.</p>',
                    ],
                    [
                        'title' => 'Zero Trust for Everyday Employees',
                        'slug' => 'zero-trust-for-everyday-employees',
                        'duration_minutes' => 8,
                        'content' => '<h3>Zero Trust for Everyday Employees</h3>
<p>If your organization is adopting a Zero Trust approach, you will notice changes in how you access systems and data. Some of these changes may feel like additional friction — more authentication prompts, access restrictions you did not have before, or new approval workflows. Understanding the reasoning behind these changes helps you work with the model rather than against it, and ultimately makes the entire organization more secure.</p>

<h3>What You May Experience</h3>
<ul>
<li><strong>More frequent MFA prompts:</strong> You may be asked to verify your identity more often, especially when accessing sensitive systems or when the context changes (new device, unusual location, or after a period of inactivity)</li>
<li><strong>Access request workflows:</strong> Instead of having standing access to all systems in your department, you may need to request access to specific resources for specific time periods. This is just-in-time access, and it ensures that unused access does not become a risk</li>
<li><strong>Device compliance requirements:</strong> Your device must meet security requirements to connect to corporate resources. If your laptop is missing a critical update, you may lose access until the update is installed</li>
<li><strong>Reduced access scope:</strong> You may notice that you can only access the systems and data directly relevant to your current projects rather than broad departmental resources</li>
</ul>

<h3>How to Support Zero Trust in Your Daily Work</h3>
<p>The most important thing you can do is comply with security controls rather than trying to work around them. When you encounter an MFA prompt, complete it rather than looking for a way to skip it. When access is restricted, use the proper request channel rather than asking a colleague to share their credentials or export data for you. Keep your devices updated and compliant, because device health directly determines your access level.</p>

<h3>Common Misconceptions</h3>
<ul>
<li><strong>"Zero Trust means the company does not trust me."</strong> It does not. Zero Trust is about verifying every digital access request, not about doubting employees\' integrity. It protects you as much as it protects the organization, because if your account is compromised, Zero Trust controls limit what an attacker can do with it</li>
<li><strong>"It is just more security theater."</strong> Zero Trust controls are designed to be proportional to risk. Routine, low-risk access should feel seamless. Extra verification appears only when the system detects elevated risk, which is precisely when you want stronger checks</li>
<li><strong>"I need broad access to do my job."</strong> If access controls are genuinely preventing legitimate work, that is a policy configuration issue, not a reason to bypass controls. Raise it with your manager or IT team so the policy can be adjusted properly</li>
</ul>

<p>Zero Trust works best when every person in the organization understands and supports it. Your willingness to comply with verification steps, keep devices secure, and use proper access channels directly strengthens the security posture that protects everyone\'s work and data.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Zero Trust Security Principles Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the fundamental difference between Zero Trust and traditional perimeter-based security?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Traditional security trusts everything inside the network perimeter. Zero Trust trusts nothing by default and requires continuous verification of every access request regardless of location.',
                            'answers' => [
                                ['answer' => 'Zero Trust uses stronger firewalls than traditional security', 'is_correct' => false],
                                ['answer' => 'Zero Trust never trusts any access request by default and requires continuous verification, while traditional security trusts everything inside the network', 'is_correct' => true],
                                ['answer' => 'Traditional security does not use passwords, while Zero Trust requires them', 'is_correct' => false],
                                ['answer' => 'Zero Trust only applies to cloud systems, not on-premises networks', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does "assume breach" mean in Zero Trust?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Assume breach means designing security controls with the assumption that attackers may already be inside the network, so protections are placed around every resource, not just the perimeter.',
                            'answers' => [
                                ['answer' => 'The organization has already been hacked and should not invest in further security', 'is_correct' => false],
                                ['answer' => 'Security is designed assuming attackers may already be inside the network, so every resource has its own protections', 'is_correct' => true],
                                ['answer' => 'All employees are assumed to be potential insider threats', 'is_correct' => false],
                                ['answer' => 'The organization should publicly disclose all past security breaches', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why might you be asked to re-authenticate when accessing a sensitive system even though you already logged in today?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Zero Trust uses continuous, context-aware authentication. Accessing sensitive resources, changed conditions, or elapsed time may trigger re-verification to ensure the request is still legitimate.',
                            'answers' => [
                                ['answer' => 'The system has a bug that keeps forgetting your login', 'is_correct' => false],
                                ['answer' => 'Zero Trust uses continuous authentication where access to sensitive resources requires re-verification based on risk context', 'is_correct' => true],
                                ['answer' => 'Your account has been flagged for suspicious activity', 'is_correct' => false],
                                ['answer' => 'MFA tokens expire every hour and must be refreshed', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Your laptop is missing a critical security update and you cannot access the project management system. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'In a Zero Trust environment, device health directly determines access. Installing the required update restores compliance and access, which is the designed behavior.',
                            'answers' => [
                                ['answer' => 'Use a colleague\'s computer to log in and access the system', 'is_correct' => false],
                                ['answer' => 'Install the required security update so your device meets compliance requirements and access is restored', 'is_correct' => true],
                                ['answer' => 'Connect through a VPN to bypass the device health check', 'is_correct' => false],
                                ['answer' => 'Contact the vendor of the project management system to whitelist your device', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is "least privilege access" in a Zero Trust model?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Least privilege means users receive only the minimum permissions needed for their specific tasks, reducing the potential damage if their account is compromised.',
                            'answers' => [
                                ['answer' => 'Giving all employees the same low level of system access', 'is_correct' => false],
                                ['answer' => 'Granting users only the minimum permissions needed for their specific tasks, revoked when no longer needed', 'is_correct' => true],
                                ['answer' => 'Restricting internet access to only approved websites', 'is_correct' => false],
                                ['answer' => 'Requiring all employees to use the least expensive security tools', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 41: Security Policy Fundamentals ──
            [
                'title' => 'Security Policy Fundamentals',
                'slug' => 'security-policy-fundamentals',
                'description' => 'Understand your organization\'s security policies, acceptable use requirements, and how to properly report security violations and concerns.',
                'objectives' => [
                    'Explain the purpose and importance of organizational security policies',
                    'Understand acceptable use policies and how they apply to daily work activities',
                    'Know the proper procedures for reporting security violations and incidents',
                    'Recognize your personal responsibilities under the organization\'s security framework',
                ],
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'beginner',
                'is_mandatory' => true,
                'duration_minutes' => 20,
                'passing_score' => 70,
                'sort_order' => 41,
                'lessons' => [
                    [
                        'title' => 'Understanding Your Organization\'s Security Policies',
                        'slug' => 'understanding-organization-security-policies',
                        'duration_minutes' => 7,
                        'content' => '<h3>Understanding Your Organization\'s Security Policies</h3>
<p>Security policies are the documented rules and guidelines that define how an organization protects its information, systems, and people. They exist because consistent, organization-wide security requires everyone to follow the same standards. Without clear policies, security depends on individual judgment, which varies widely and leaves dangerous gaps that attackers can exploit.</p>

<h3>Why Security Policies Matter</h3>
<p>Security policies serve multiple critical functions. They establish a baseline of expected behavior so that every employee knows what is required of them. They ensure the organization meets legal and regulatory obligations such as GDPR, HIPAA, PCI-DSS, or industry-specific requirements. They provide a framework for responding to incidents consistently rather than making decisions under pressure. And they protect individual employees by making expectations clear — you cannot be held accountable for rules that were never communicated.</p>

<h3>Common Types of Security Policies</h3>
<ul>
<li><strong>Information Security Policy:</strong> The overarching policy that defines the organization\'s approach to protecting information assets, including data classification, access control principles, and governance structure</li>
<li><strong>Acceptable Use Policy (AUP):</strong> Defines what employees can and cannot do with organizational IT resources including computers, networks, email, and internet access</li>
<li><strong>Password and Authentication Policy:</strong> Specifies requirements for password complexity, rotation, multi-factor authentication, and credential management</li>
<li><strong>Data Handling and Classification Policy:</strong> Defines categories of data sensitivity and the specific handling requirements for each level</li>
<li><strong>Remote Work and BYOD Policy:</strong> Establishes security requirements for working outside the office and using personal devices for work purposes</li>
<li><strong>Incident Response Policy:</strong> Outlines procedures for detecting, reporting, and responding to security incidents</li>
</ul>

<h3>Your Responsibilities</h3>
<p>As an employee, you are responsible for reading and understanding the security policies that apply to your role. Claiming you were unaware of a policy is generally not accepted as an excuse for non-compliance, especially after you have acknowledged the policies during onboarding or annual training. If a policy is unclear, ask your manager or IT security team for clarification rather than guessing or ignoring it. Policies are living documents that are updated regularly, so stay current with changes communicated through training, email, or your organization\'s intranet.</p>',
                    ],
                    [
                        'title' => 'Acceptable Use Policies',
                        'slug' => 'acceptable-use-policies',
                        'duration_minutes' => 7,
                        'content' => '<h3>Acceptable Use Policies</h3>
<p>An Acceptable Use Policy (AUP) is one of the most directly relevant security documents for everyday employees. It defines what you are permitted to do — and what is prohibited — when using your organization\'s computers, networks, email, internet access, software, and other IT resources. Think of it as the rules of the road for your digital workplace.</p>

<h3>What AUPs Typically Cover</h3>
<p>While every organization\'s AUP is different, most address the same core areas. Understanding these common elements helps you navigate your own organization\'s policy and recognize behaviors that could put you or your employer at risk.</p>

<ul>
<li><strong>Personal use of work resources:</strong> Most organizations permit limited personal use of email and internet during breaks, but prohibit activities that consume excessive bandwidth, create legal liability, or expose the network to risk. Streaming entertainment, downloading large personal files, or running a side business on company equipment typically violates the AUP</li>
<li><strong>Software installation:</strong> Installing unauthorized software can introduce vulnerabilities, licensing issues, or malware. AUPs typically require that all software be approved and installed through official channels such as an IT service desk or managed software catalog</li>
<li><strong>Email and communication:</strong> Using corporate email for personal business, forwarding work emails to personal accounts, or sending sensitive information to unauthorized recipients are common AUP violations. Corporate email is a business tool and is typically monitored and archived</li>
<li><strong>Social media:</strong> Policies may restrict what you share about work on personal social media, prohibit accessing social platforms on work devices, or require disclaimers when discussing work-related topics online</li>
<li><strong>Cloud services and shadow IT:</strong> Using unauthorized cloud services to store or share work data — even with good intentions — violates most AUPs because data leaves the organization\'s control and security perimeter</li>
</ul>

<h3>Consequences of AUP Violations</h3>
<p>Violating the acceptable use policy can result in consequences ranging from a verbal warning to termination, depending on the severity and intent. In some cases, AUP violations that result in data breaches or regulatory non-compliance can lead to legal liability for both the organization and the individual. Taking a few minutes to understand your AUP is a small investment that protects your career and your organization.</p>

<h3>When in Doubt, Ask</h3>
<p>If you are unsure whether a particular action is permitted under the AUP, ask before you act. Your IT team or manager would far rather answer a quick question than deal with the fallout from a policy violation. Most policies are designed to enable productive work while managing risk — if something feels like it might cross a line, it probably does.</p>',
                    ],
                    [
                        'title' => 'Reporting Security Violations',
                        'slug' => 'reporting-security-violations',
                        'duration_minutes' => 6,
                        'content' => '<h3>Reporting Security Violations</h3>
<p>Reporting security violations and suspicious activity is one of the most important contributions any employee can make to organizational security. Many significant breaches were detected not by automated tools but by observant employees who noticed something unusual and reported it. A culture where reporting is encouraged, easy, and free from retaliation is essential to effective security.</p>

<h3>What Should Be Reported</h3>
<ul>
<li><strong>Suspected phishing emails:</strong> Messages that ask for credentials, contain suspicious links, or create unusual urgency. Most organizations have a dedicated reporting button in the email client or a specific address to forward suspicious emails to</li>
<li><strong>Unauthorized access attempts:</strong> If you notice someone accessing systems they should not have access to, or if your own account shows login activity you do not recognize, report it immediately</li>
<li><strong>Policy violations by others:</strong> Sharing passwords, leaving sensitive documents exposed, propping open secure doors, or connecting unauthorized devices to the network are all reportable</li>
<li><strong>Lost or stolen devices:</strong> A lost laptop, phone, or USB drive containing work data is a security incident that must be reported immediately so the device can be remotely wiped and access credentials can be changed</li>
<li><strong>Unusual system behavior:</strong> If your computer is running slowly, displaying unexpected pop-ups, or behaving abnormally, it may be compromised. Report it to IT rather than trying to diagnose it yourself</li>
<li><strong>Social engineering attempts:</strong> Phone calls, in-person visits, or messages from people claiming to need access or information who cannot verify their identity</li>
</ul>

<h3>How to Report Effectively</h3>
<p>When reporting a security concern, include as much detail as possible: what you observed, when it happened, who was involved, and any evidence such as screenshots or email headers. Use your organization\'s official reporting channels — typically an IT service desk ticket, a dedicated security hotline, or a specific email address. For urgent issues like active data theft or an ongoing social engineering attempt, use the fastest channel available, such as a phone call to the security operations center.</p>

<h3>Overcoming Barriers to Reporting</h3>
<p>People often hesitate to report for several reasons: fear of being wrong and wasting someone\'s time, concern about getting a colleague in trouble, worry about retaliation, or simply not knowing how to report. Every organization should make clear that reporting is expected and appreciated, that good-faith reports will never result in punishment even if they turn out to be false alarms, and that retaliation against reporters is a serious offense. Your security team would always rather investigate ten false alarms than miss one real incident.</p>

<p>If you made a mistake — clicked a suspicious link, shared information you should not have, or violated a policy — report it immediately. Self-reporting a mistake allows the security team to contain potential damage quickly. The consequences of hiding a mistake that leads to a breach are always far worse than the consequences of promptly reporting one.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Security Policy Fundamentals Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the primary purpose of organizational security policies?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Security policies establish consistent, organization-wide standards for protecting information and systems, ensuring everyone follows the same rules rather than relying on individual judgment.',
                            'answers' => [
                                ['answer' => 'To make IT work more difficult and add bureaucracy', 'is_correct' => false],
                                ['answer' => 'To establish consistent rules and expectations for how the organization protects its information, systems, and people', 'is_correct' => true],
                                ['answer' => 'To give the legal department leverage over employees', 'is_correct' => false],
                                ['answer' => 'To prevent employees from using the internet at work', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of the following would typically violate an Acceptable Use Policy?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Installing unauthorized software can introduce security vulnerabilities, licensing issues, or malware. AUPs require software to be approved and installed through official IT channels.',
                            'answers' => [
                                ['answer' => 'Checking a personal email during your lunch break', 'is_correct' => false],
                                ['answer' => 'Installing unauthorized software you found online onto your work computer', 'is_correct' => true],
                                ['answer' => 'Using the company intranet to look up HR policies', 'is_correct' => false],
                                ['answer' => 'Submitting an IT support ticket for a slow computer', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'You accidentally clicked a link in a suspicious email. What should you do?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Self-reporting mistakes immediately allows the security team to contain potential damage quickly. Hiding a mistake that leads to a breach always has worse consequences than reporting it promptly.',
                            'answers' => [
                                ['answer' => 'Do nothing and hope no harm was done', 'is_correct' => false],
                                ['answer' => 'Try to fix the problem yourself by running antivirus software', 'is_correct' => false],
                                ['answer' => 'Report it to your security team immediately so they can assess and contain any potential damage', 'is_correct' => true],
                                ['answer' => 'Delete the email and clear your browser history', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you report a security concern even if you are not sure it is a real threat?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Security teams prefer to investigate false alarms rather than miss a real incident. Good-faith reports are always encouraged, and employees should never be punished for reporting something that turns out to be benign.',
                            'answers' => [
                                ['answer' => 'You are legally required to report every minor concern', 'is_correct' => false],
                                ['answer' => 'Security teams would rather investigate a false alarm than miss a real incident, and good-faith reports are never punished', 'is_correct' => true],
                                ['answer' => 'Reporting earns you credit toward your annual performance review', 'is_correct' => false],
                                ['answer' => 'You should not report unless you are absolutely certain it is a real threat', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do if you discover your work laptop has been lost or stolen?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A lost or stolen device is a security incident requiring immediate reporting so the device can be remotely wiped and compromised credentials can be changed before an attacker gains access.',
                            'answers' => [
                                ['answer' => 'Wait a day to see if you find it before reporting', 'is_correct' => false],
                                ['answer' => 'Buy a replacement laptop and transfer your work to the new device', 'is_correct' => false],
                                ['answer' => 'Report it immediately so the device can be remotely wiped and your access credentials can be changed', 'is_correct' => true],
                                ['answer' => 'Post about it on social media to see if anyone found it', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
