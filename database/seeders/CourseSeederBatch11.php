<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Database\Seeder;

class CourseSeederBatch11 extends Seeder
{
    public function run(): void
    {
        $courses = $this->getCourses();

        foreach ($courses as $courseData) {
            $lessons = $courseData['lessons'];
            $quiz = $courseData['quiz'];
            unset($courseData['lessons'], $courseData['quiz']);

            $course = Course::updateOrCreate(
                ['slug' => $courseData['slug']],
                $courseData
            );

            foreach ($lessons as $lessonData) {
                $lessonData['course_id'] = $course->id;
                Lesson::updateOrCreate(
                    ['course_id' => $course->id, 'slug' => $lessonData['slug']],
                    $lessonData
                );
            }

            $quizModel = Quiz::updateOrCreate(
                ['course_id' => $course->id],
                [
                    'course_id' => $course->id,
                    'title' => $quiz['title'],
                    'max_attempts' => $quiz['max_attempts'],
                ]
            );

            foreach ($quiz['questions'] as $questionData) {
                $answers = $questionData['answers'];
                unset($questionData['answers']);

                $question = QuizQuestion::updateOrCreate(
                    ['quiz_id' => $quizModel->id, 'question' => $questionData['question']],
                    array_merge($questionData, ['quiz_id' => $quizModel->id])
                );

                foreach ($answers as $answerData) {
                    QuizAnswer::updateOrCreate(
                        ['quiz_question_id' => $question->id, 'answer' => $answerData['answer']],
                        array_merge($answerData, ['quiz_question_id' => $question->id])
                    );
                }
            }
        }
    }

    private function getCourses(): array
    {
        return [
            // Course 1: Supply Chain Attack Awareness
            [
                'title' => 'Supply Chain Attack Awareness',
                'slug' => 'supply-chain-attack-awareness',
                'description' => 'Learn how attackers compromise trusted vendors and software suppliers to infiltrate downstream targets. This advanced course covers real-world supply chain incidents, detection strategies, and organizational defenses against one of the most sophisticated modern threat vectors.',
                'category' => 'Malware & Ransomware',
                'difficulty' => 'advanced',
                'duration_minutes' => 60,
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 34,
                'lessons' => [
                    [
                        'title' => 'Understanding Supply Chain Attacks',
                        'slug' => 'understanding-supply-chain-attacks',
                        'sort_order' => 0,
                        'duration_minutes' => 20,
                        'content' => '<h2>What Are Supply Chain Attacks?</h2>
<p>A supply chain attack occurs when a threat actor compromises a trusted third-party vendor, software provider, or service partner to gain access to their customers\' systems and data. Rather than attacking the target organization directly, adversaries infiltrate through the trusted relationships and dependencies that every modern business relies upon. These attacks exploit the inherent trust placed in suppliers, making them exceptionally difficult to detect.</p>

<h3>The Anatomy of a Supply Chain Compromise</h3>
<p>Supply chain attacks typically follow a multi-stage process. First, the attacker identifies a supplier with privileged access to many downstream targets. They then compromise that supplier\'s development environment, build systems, or update mechanisms. Finally, malicious code is distributed to all of the supplier\'s customers through legitimate-looking software updates or patches. Because the malicious payload arrives through a trusted channel, traditional perimeter defenses are often bypassed entirely.</p>

<blockquote><strong>Key Insight:</strong> The SolarWinds attack of 2020 compromised over 18,000 organizations, including multiple U.S. government agencies, by inserting malicious code into a routine software update. The attackers had access for months before detection.</blockquote>

<h3>Types of Supply Chain Attacks</h3>
<ul>
<li><strong>Software supply chain:</strong> Compromising source code repositories, build pipelines, or code-signing certificates to inject malware into legitimate software</li>
<li><strong>Hardware supply chain:</strong> Tampering with physical components during manufacturing, shipping, or installation to embed backdoors or surveillance capabilities</li>
<li><strong>Service provider attacks:</strong> Targeting managed service providers (MSPs) or cloud service providers to gain access to their entire customer base</li>
<li><strong>Open-source dependency attacks:</strong> Poisoning widely-used open-source libraries or packages that thousands of applications depend upon</li>
</ul>

<h3>Why Supply Chain Attacks Are Increasing</h3>
<p>Modern organizations rely on increasingly complex webs of vendors, software libraries, and cloud services. The average enterprise uses hundreds of third-party applications and thousands of open-source components. Each dependency represents a potential entry point for attackers. As organizations improve their direct defenses, adversaries naturally pivot to these less-protected indirect pathways. The return on investment for attackers is enormous: compromising a single widely-used supplier can yield access to thousands of targets simultaneously.</p>

<p>Understanding these attack vectors is the first step toward building meaningful defenses. In the following lessons, we will examine detection techniques and concrete organizational strategies for mitigating supply chain risk.</p>',
                    ],
                    [
                        'title' => 'Detecting Supply Chain Compromises',
                        'slug' => 'detecting-supply-chain-compromises',
                        'sort_order' => 1,
                        'duration_minutes' => 20,
                        'content' => '<h2>Identifying Signs of Supply Chain Compromise</h2>
<p>Detecting supply chain attacks is inherently challenging because the malicious activity originates from trusted sources. Traditional signature-based security tools often miss these threats because the malware is delivered through legitimate, digitally-signed software. Organizations must adopt a multi-layered detection strategy that combines behavioral analysis, integrity verification, and continuous monitoring of vendor relationships.</p>

<h3>Behavioral Indicators to Watch For</h3>
<p>Even the most sophisticated supply chain attacks leave traces that alert defenders can identify. The key is knowing what anomalous behavior looks like in the context of trusted software and vendor access. Security teams should establish baselines for normal vendor activity and investigate any deviations promptly.</p>
<ul>
<li><strong>Unexpected network connections:</strong> Trusted software suddenly communicating with unfamiliar external servers or unusual geographic regions</li>
<li><strong>Anomalous process behavior:</strong> Legitimate applications spawning unexpected child processes, accessing unusual files, or escalating privileges</li>
<li><strong>Irregular update patterns:</strong> Software updates arriving outside of normal schedules, with unusual file sizes, or without corresponding release notes</li>
<li><strong>Lateral movement from vendor systems:</strong> Service accounts or VPN connections used by vendors accessing resources beyond their normal scope</li>
</ul>

<h3>Technical Detection Methods</h3>
<ol>
<li><strong>Software Bill of Materials (SBOM):</strong> Maintain a detailed inventory of all software components, libraries, and dependencies. Compare each update against the known SBOM to identify unauthorized additions or modifications.</li>
<li><strong>Binary analysis and hash verification:</strong> Verify the cryptographic hashes of software binaries against vendor-published values before deployment. Automated tools can flag any discrepancies.</li>
<li><strong>Network traffic analysis:</strong> Deploy network detection and response (NDR) solutions that baseline normal application traffic patterns and alert on anomalies, even from signed and trusted applications.</li>
<li><strong>Endpoint detection and response (EDR):</strong> Use advanced EDR tools that monitor process behavior, memory operations, and system calls to detect post-exploitation activity regardless of the delivery mechanism.</li>
</ol>

<blockquote><strong>Best Practice:</strong> Implement a zero-trust approach even for trusted vendors. Verify every connection, validate every update, and monitor every access — trust must be continuously earned, not assumed based on a prior relationship.</blockquote>

<p>Early detection significantly reduces the impact of supply chain compromises. Organizations that invest in proactive monitoring capabilities can identify and contain breaches before attackers achieve their objectives, limiting data exposure and operational disruption.</p>',
                    ],
                    [
                        'title' => 'Organizational Defenses Against Supply Chain Threats',
                        'slug' => 'organizational-defenses-supply-chain-threats',
                        'sort_order' => 2,
                        'duration_minutes' => 20,
                        'content' => '<h2>Building a Resilient Supply Chain Security Program</h2>
<p>Defending against supply chain attacks requires a comprehensive organizational strategy that spans procurement, vendor management, technical controls, and incident response planning. No single technology or policy can eliminate supply chain risk, but a layered defense-in-depth approach can dramatically reduce exposure and improve detection and recovery capabilities.</p>

<h3>Vendor Risk Management</h3>
<p>Effective supply chain security begins long before a vendor\'s software or services are deployed. Organizations must establish rigorous vendor assessment and ongoing monitoring programs that evaluate security posture throughout the entire relationship lifecycle.</p>
<ul>
<li>Require vendors to demonstrate compliance with recognized security frameworks such as SOC 2 Type II, ISO 27001, or NIST CSF</li>
<li>Include specific security requirements and breach notification obligations in all vendor contracts</li>
<li>Conduct regular security assessments and audits of critical vendors, including penetration testing and code review where applicable</li>
<li>Maintain a tiered vendor classification system based on the level of access and criticality to your operations</li>
</ul>

<h3>Technical Hardening Strategies</h3>
<ol>
<li><strong>Network segmentation:</strong> Isolate vendor-accessed systems from critical assets. Ensure that a compromise of one vendor cannot provide unrestricted access across the environment.</li>
<li><strong>Least privilege access:</strong> Grant vendors only the minimum access necessary for their function. Review and revoke access regularly, and require multi-factor authentication for all vendor connections.</li>
<li><strong>Update staging and testing:</strong> Never deploy vendor updates directly to production. Test all updates in an isolated environment and monitor for anomalous behavior before rolling out to the broader network.</li>
<li><strong>Code signing and integrity checks:</strong> Verify digital signatures on all software and enforce policies that prevent unsigned or tampered code from executing.</li>
</ol>

<blockquote><strong>Remember:</strong> Your organization\'s security is only as strong as the weakest link in your supply chain. A vendor with poor security practices can become the backdoor through which attackers reach your most sensitive systems.</blockquote>

<h3>Incident Response for Supply Chain Events</h3>
<p>Supply chain incidents demand a specialized response playbook. When a vendor is compromised, the affected organization must rapidly assess the scope of exposure, determine what data or systems may be impacted, and coordinate with the vendor and potentially with law enforcement. Pre-established communication channels and response procedures with key vendors are essential for minimizing response time.</p>

<p>By combining rigorous vendor management, technical controls, and prepared incident response capabilities, organizations can build meaningful resilience against supply chain threats. Regular tabletop exercises that simulate supply chain compromise scenarios help validate these defenses and identify gaps before real incidents occur.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Supply Chain Attack Awareness Quiz',
                    'max_attempts' => 3,
                    'questions' => [
                        [
                            'question' => 'What is the primary characteristic that makes supply chain attacks particularly dangerous?',
                            'answers' => [
                                ['answer' => 'They exploit the trust placed in legitimate vendors and software providers', 'is_correct' => true],
                                ['answer' => 'They only target small businesses', 'is_correct' => false],
                                ['answer' => 'They require physical access to the target network', 'is_correct' => false],
                                ['answer' => 'They are easily detected by standard antivirus software', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of the following is an example of an open-source supply chain attack?',
                            'answers' => [
                                ['answer' => 'Sending phishing emails to employees', 'is_correct' => false],
                                ['answer' => 'Poisoning a widely-used open-source library that many applications depend on', 'is_correct' => true],
                                ['answer' => 'Brute-forcing administrator passwords', 'is_correct' => false],
                                ['answer' => 'Physically tampering with a company server', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is a Software Bill of Materials (SBOM) used for?',
                            'answers' => [
                                ['answer' => 'Tracking employee training completion', 'is_correct' => false],
                                ['answer' => 'Managing hardware inventory in the data center', 'is_correct' => false],
                                ['answer' => 'Maintaining a detailed inventory of all software components and dependencies for integrity verification', 'is_correct' => true],
                                ['answer' => 'Generating invoices for software licenses', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the recommended approach for deploying vendor software updates?',
                            'answers' => [
                                ['answer' => 'Install updates immediately upon release to patch vulnerabilities as fast as possible', 'is_correct' => false],
                                ['answer' => 'Delay updates for at least one year to allow others to find issues', 'is_correct' => false],
                                ['answer' => 'Test updates in an isolated staging environment and monitor for anomalies before production deployment', 'is_correct' => true],
                                ['answer' => 'Only install updates if the vendor sends a follow-up reminder', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which vendor risk management practice is most effective for ongoing supply chain security?',
                            'answers' => [
                                ['answer' => 'Conducting a single security assessment at the start of the vendor relationship', 'is_correct' => false],
                                ['answer' => 'Trusting vendor self-reported security questionnaires without verification', 'is_correct' => false],
                                ['answer' => 'Granting vendors full network access to simplify support workflows', 'is_correct' => false],
                                ['answer' => 'Regular security assessments, contractual breach notification obligations, and least-privilege access controls', 'is_correct' => true],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 2: Secure Software Development Basics
            [
                'title' => 'Secure Software Development Basics',
                'slug' => 'secure-software-development-basics',
                'description' => 'Explore the fundamentals of building security into the software development lifecycle from the very beginning. This course covers secure coding principles, common vulnerability patterns, and practical techniques for writing code that resists attack.',
                'category' => 'Data Protection & Privacy',
                'difficulty' => 'intermediate',
                'duration_minutes' => 50,
                'is_mandatory' => false,
                'is_active' => true,
                'sort_order' => 35,
                'lessons' => [
                    [
                        'title' => 'Secure Development Lifecycle Fundamentals',
                        'slug' => 'secure-development-lifecycle-fundamentals',
                        'sort_order' => 0,
                        'duration_minutes' => 17,
                        'content' => '<h2>Integrating Security Into the Development Process</h2>
<p>The Secure Software Development Lifecycle (SSDLC) is a framework for incorporating security practices into every phase of software development — from initial requirements gathering through design, implementation, testing, deployment, and maintenance. Rather than treating security as an afterthought or a final gate before release, the SSDLC ensures that security considerations inform decisions at every stage.</p>

<h3>Why Shift Security Left?</h3>
<p>The concept of "shifting left" means addressing security concerns as early as possible in the development process. Research consistently shows that vulnerabilities discovered during the design phase cost a fraction of what they cost to remediate after deployment. A SQL injection vulnerability caught during code review takes minutes to fix; the same vulnerability discovered after a data breach can cost millions in incident response, regulatory fines, and reputational damage.</p>

<blockquote><strong>Industry Data:</strong> According to NIST, fixing a security defect after release can cost 30 to 100 times more than addressing it during the design phase. Early investment in security yields significant long-term savings.</blockquote>

<h3>Key Phases of the SSDLC</h3>
<ul>
<li><strong>Requirements:</strong> Define security requirements alongside functional requirements. Identify sensitive data, compliance obligations, and threat scenarios specific to the application.</li>
<li><strong>Design:</strong> Conduct threat modeling to identify potential attack surfaces. Select secure architecture patterns and establish trust boundaries between components.</li>
<li><strong>Implementation:</strong> Follow secure coding standards, use approved libraries and frameworks, and perform peer code reviews with a security lens.</li>
<li><strong>Testing:</strong> Execute static application security testing (SAST), dynamic application security testing (DAST), and manual penetration testing to validate security controls.</li>
<li><strong>Deployment:</strong> Harden configurations, implement monitoring, and verify that security controls function correctly in the production environment.</li>
<li><strong>Maintenance:</strong> Monitor for new vulnerabilities in dependencies, apply patches promptly, and conduct periodic security assessments.</li>
</ul>

<p>Every team member plays a role in the SSDLC. Developers write secure code, architects design resilient systems, testers validate security controls, and operations teams maintain secure environments. Security is a shared responsibility, not the sole domain of a dedicated security team.</p>',
                    ],
                    [
                        'title' => 'Common Vulnerabilities and Secure Coding Practices',
                        'slug' => 'common-vulnerabilities-secure-coding-practices',
                        'sort_order' => 1,
                        'duration_minutes' => 17,
                        'content' => '<h2>Understanding the Most Common Software Vulnerabilities</h2>
<p>The OWASP Top Ten provides a widely-recognized list of the most critical web application security risks. Understanding these vulnerabilities is essential for any developer, as they represent the attack vectors most frequently exploited in real-world breaches. By learning to recognize and prevent these patterns, developers can eliminate the majority of common security flaws from their code.</p>

<h3>Critical Vulnerability Categories</h3>
<ol>
<li><strong>Injection Attacks (SQL, NoSQL, Command):</strong> Occur when untrusted data is sent to an interpreter as part of a command or query. Always use parameterized queries and prepared statements — never concatenate user input directly into queries.</li>
<li><strong>Broken Authentication:</strong> Weak session management, credential storage, or authentication logic that allows attackers to compromise passwords, keys, or session tokens. Implement multi-factor authentication and use proven authentication frameworks.</li>
<li><strong>Cross-Site Scripting (XSS):</strong> Happens when an application includes untrusted data in a web page without proper validation or escaping. Always encode output and use Content Security Policy headers to mitigate XSS risks.</li>
<li><strong>Insecure Direct Object References:</strong> Exposing internal implementation objects such as database keys or file paths that allow attackers to access unauthorized resources. Implement proper access control checks on every request.</li>
</ol>

<h3>Secure Coding Principles</h3>
<ul>
<li><strong>Input validation:</strong> Validate all input on the server side. Use allowlists rather than blocklists, and reject any input that does not conform to expected patterns.</li>
<li><strong>Output encoding:</strong> Encode all output based on the context where it will be rendered — HTML, JavaScript, URL, or CSS encoding as appropriate.</li>
<li><strong>Least privilege:</strong> Applications should run with the minimum permissions necessary. Database connections should use accounts with restricted access rather than administrative credentials.</li>
<li><strong>Defense in depth:</strong> Layer multiple security controls so that the failure of any single control does not result in a complete compromise.</li>
</ul>

<blockquote><strong>Developer Rule of Thumb:</strong> Never trust user input. Every piece of data that crosses a trust boundary — from form fields to API parameters to HTTP headers — must be treated as potentially malicious and validated before use.</blockquote>

<p>Secure coding is a skill that improves with practice. Regular security training, code review with security checklists, and participation in capture-the-flag exercises help developers build the instincts needed to write resilient code consistently.</p>',
                    ],
                    [
                        'title' => 'Security Testing and Code Review',
                        'slug' => 'security-testing-code-review',
                        'sort_order' => 2,
                        'duration_minutes' => 16,
                        'content' => '<h2>Validating Security Through Testing</h2>
<p>Security testing is the practice of evaluating software to identify vulnerabilities, weaknesses, and potential attack vectors before they can be exploited. Unlike functional testing, which verifies that software does what it should, security testing verifies that software does not do what it should not — it cannot be tricked into leaking data, escalating privileges, or executing arbitrary code.</p>

<h3>Types of Security Testing</h3>
<ul>
<li><strong>Static Application Security Testing (SAST):</strong> Analyzes source code, bytecode, or binaries without executing the application. SAST tools identify potential vulnerabilities such as buffer overflows, SQL injection, and hard-coded credentials early in the development cycle. They integrate directly into IDEs and CI/CD pipelines for immediate developer feedback.</li>
<li><strong>Dynamic Application Security Testing (DAST):</strong> Tests the running application by simulating attacks from the outside. DAST tools crawl the application, submit malicious inputs, and analyze responses to identify runtime vulnerabilities such as XSS, authentication flaws, and server misconfigurations.</li>
<li><strong>Software Composition Analysis (SCA):</strong> Scans third-party libraries and open-source dependencies for known vulnerabilities. Given that modern applications often consist of 80% or more third-party code, SCA is essential for maintaining a secure software supply chain.</li>
<li><strong>Penetration Testing:</strong> Skilled security professionals attempt to exploit vulnerabilities in a controlled manner, simulating real-world attack scenarios. Penetration tests uncover complex, multi-step attack chains that automated tools often miss.</li>
</ul>

<h3>Effective Security Code Review</h3>
<p>Security-focused code reviews complement automated testing by applying human judgment to identify logic flaws, design weaknesses, and subtle vulnerabilities that tools cannot detect. Reviewers should focus on authentication and authorization logic, data validation boundaries, cryptographic implementations, and error handling that might leak sensitive information.</p>

<blockquote><strong>Review Checklist:</strong> Does the code validate all inputs? Are database queries parameterized? Is sensitive data encrypted at rest and in transit? Are error messages free of internal system details? Are access controls enforced on every endpoint?</blockquote>

<h3>Integrating Security Into CI/CD</h3>
<ol>
<li>Run SAST scans on every pull request and block merges that introduce high-severity findings</li>
<li>Execute DAST scans against staging environments before each release</li>
<li>Automate SCA scans to flag vulnerable dependencies and enforce update policies</li>
<li>Track security findings alongside functional defects in the team\'s issue tracker</li>
</ol>

<p>Security testing is not a one-time event but an ongoing discipline. By embedding security checks into the development workflow, teams build confidence that their applications can withstand real-world attacks while maintaining development velocity.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Secure Software Development Basics Quiz',
                    'max_attempts' => 3,
                    'questions' => [
                        [
                            'question' => 'What does "shifting left" mean in the context of secure software development?',
                            'answers' => [
                                ['answer' => 'Moving security responsibilities to a separate team', 'is_correct' => false],
                                ['answer' => 'Addressing security concerns as early as possible in the development lifecycle', 'is_correct' => true],
                                ['answer' => 'Deploying software updates more frequently', 'is_correct' => false],
                                ['answer' => 'Reducing the number of developers on the team', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the most effective defense against SQL injection attacks?',
                            'answers' => [
                                ['answer' => 'Using a web application firewall as the sole defense', 'is_correct' => false],
                                ['answer' => 'Filtering out common SQL keywords from user input', 'is_correct' => false],
                                ['answer' => 'Using parameterized queries and prepared statements', 'is_correct' => true],
                                ['answer' => 'Limiting the length of user input fields', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the primary purpose of Static Application Security Testing (SAST)?',
                            'answers' => [
                                ['answer' => 'Testing the running application by simulating external attacks', 'is_correct' => false],
                                ['answer' => 'Analyzing source code to identify potential vulnerabilities without executing the application', 'is_correct' => true],
                                ['answer' => 'Scanning third-party libraries for known CVEs', 'is_correct' => false],
                                ['answer' => 'Performing manual penetration testing', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'When validating user input, which approach is more secure?',
                            'answers' => [
                                ['answer' => 'Client-side validation only, for faster user feedback', 'is_correct' => false],
                                ['answer' => 'Blocklists that reject known malicious patterns', 'is_correct' => false],
                                ['answer' => 'Server-side validation using allowlists that accept only expected patterns', 'is_correct' => true],
                                ['answer' => 'No validation, since the database will reject invalid data', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is Software Composition Analysis (SCA) important for modern applications?',
                            'answers' => [
                                ['answer' => 'It replaces the need for code reviews', 'is_correct' => false],
                                ['answer' => 'Modern applications rely heavily on third-party and open-source components that may contain known vulnerabilities', 'is_correct' => true],
                                ['answer' => 'It guarantees that the application is free of all security flaws', 'is_correct' => false],
                                ['answer' => 'It is only required for applications written in JavaScript', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 3: Business Email Compromise Prevention
            [
                'title' => 'Business Email Compromise Prevention',
                'slug' => 'business-email-compromise-prevention',
                'description' => 'Understand how attackers impersonate executives, vendors, and partners to manipulate employees into transferring funds or sharing sensitive data. This course teaches you to recognize BEC tactics, verify requests through secure channels, and protect your organization from financial fraud.',
                'category' => 'Phishing & Email Security',
                'difficulty' => 'intermediate',
                'duration_minutes' => 45,
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 36,
                'lessons' => [
                    [
                        'title' => 'How Business Email Compromise Works',
                        'slug' => 'how-business-email-compromise-works',
                        'sort_order' => 0,
                        'duration_minutes' => 15,
                        'content' => '<h2>The Mechanics of Business Email Compromise</h2>
<p>Business Email Compromise (BEC) is a sophisticated form of cybercrime in which attackers use email to impersonate trusted individuals — typically executives, vendors, or business partners — to trick employees into transferring money, sharing sensitive information, or taking other harmful actions. The FBI has identified BEC as one of the most financially damaging forms of cybercrime, with global losses exceeding $50 billion since 2013.</p>

<h3>Common BEC Scenarios</h3>
<p>BEC attacks come in several well-documented forms, each targeting different roles and business processes within an organization. Understanding these patterns is critical for recognizing and stopping attacks before damage occurs.</p>
<ul>
<li><strong>CEO Fraud:</strong> The attacker impersonates a senior executive and sends an urgent email to a finance team member requesting an immediate wire transfer. The message typically stresses confidentiality and urgency to prevent the recipient from seeking verification.</li>
<li><strong>Vendor Invoice Manipulation:</strong> Attackers compromise or spoof a vendor\'s email account and send modified invoices with updated bank account details, redirecting legitimate payments to attacker-controlled accounts.</li>
<li><strong>Attorney Impersonation:</strong> Posing as a lawyer handling a confidential matter, the attacker pressures employees into acting quickly and discreetly, often near the end of a business day or before a holiday.</li>
<li><strong>Payroll Diversion:</strong> Attackers impersonate employees and contact HR or payroll departments to request changes to direct deposit information, redirecting salary payments to fraudulent accounts.</li>
</ul>

<blockquote><strong>Real-World Impact:</strong> In 2019, a major European film company lost approximately $21 million to a BEC attack where criminals impersonated the CEO and convinced the CFO to transfer funds for a fabricated acquisition deal.</blockquote>

<h3>How Attackers Prepare</h3>
<p>BEC attackers invest significant time in reconnaissance before launching their attacks. They study organizational hierarchies, monitor email communication patterns, learn business terminology, and identify the individuals who authorize payments. Many attackers gain this intelligence by compromising legitimate email accounts through phishing, giving them direct visibility into ongoing business conversations, invoice formats, and approval workflows.</p>

<p>The sophistication of BEC attacks continues to increase. Attackers now use lookalike domains, display name spoofing, and even AI-generated voice deepfakes to lend credibility to their impersonation. Employees at every level must understand these techniques to serve as an effective last line of defense.</p>',
                    ],
                    [
                        'title' => 'Recognizing and Verifying Suspicious Requests',
                        'slug' => 'recognizing-verifying-suspicious-requests',
                        'sort_order' => 1,
                        'duration_minutes' => 15,
                        'content' => '<h2>Red Flags That Signal a BEC Attack</h2>
<p>While BEC messages are designed to look legitimate, they frequently exhibit patterns that an alert employee can recognize. The key is to slow down, resist pressure to act immediately, and evaluate the request critically before taking any action. Attackers rely on urgency and authority to bypass careful thinking — recognizing this dynamic is itself a powerful defense.</p>

<h3>Warning Signs in Email Requests</h3>
<ul>
<li><strong>Unusual urgency:</strong> Phrases like "this must be done today," "do not discuss this with anyone," or "I need this handled before end of day" are designed to override normal verification procedures</li>
<li><strong>Changes to payment details:</strong> Any request to update bank account information, wire transfer destinations, or payment methods should trigger immediate verification</li>
<li><strong>Slight email address variations:</strong> Attackers use domains like "company-inc.com" instead of "companyinc.com" or swap similar-looking characters (l vs 1, rn vs m)</li>
<li><strong>Unusual sender behavior:</strong> An executive who normally does not email you directly, communication outside normal business hours, or requests that bypass standard approval workflows</li>
<li><strong>Requests for secrecy:</strong> Legitimate business transactions rarely require hiding them from colleagues or management</li>
</ul>

<h3>Verification Procedures</h3>
<p>When you receive a suspicious request, verification through an independent channel is essential. Never use contact information provided in the suspicious email itself — the attacker may have included a phone number that routes to their own line.</p>
<ol>
<li><strong>Call the requestor directly:</strong> Use a known phone number from your contacts or the company directory — not a number from the email</li>
<li><strong>Verify in person:</strong> If the requestor is in the same location, walk over and confirm the request face-to-face</li>
<li><strong>Use a separate communication channel:</strong> Confirm via your company\'s instant messaging platform, a video call, or through the requestor\'s known personal mobile number</li>
<li><strong>Check with your manager:</strong> If you are uncertain, escalate to your supervisor before acting on the request</li>
</ol>

<blockquote><strong>Golden Rule:</strong> Any request involving money, credentials, or sensitive data that deviates from normal processes deserves a verification call — even if it comes from the CEO. Legitimate executives will appreciate the caution.</blockquote>

<p>Building a culture where verification is expected and encouraged — rather than seen as distrustful or slow — is fundamental to preventing BEC losses. Organizations should make it clear that employees will never be penalized for taking time to verify a request, regardless of who it appears to come from.</p>',
                    ],
                    [
                        'title' => 'Organizational Controls Against BEC',
                        'slug' => 'organizational-controls-against-bec',
                        'sort_order' => 2,
                        'duration_minutes' => 15,
                        'content' => '<h2>Building Organizational Defenses Against BEC</h2>
<p>While individual awareness is essential, organizations must also implement systematic controls that make BEC attacks harder to execute and easier to detect. A single employee\'s mistake should not be sufficient to cause a significant financial loss. Layered controls ensure that multiple checkpoints exist between a fraudulent request and actual harm.</p>

<h3>Financial Controls</h3>
<ul>
<li><strong>Dual authorization:</strong> Require two independent approvals for all wire transfers, payment changes, and transactions above a defined threshold</li>
<li><strong>Callback verification:</strong> Mandate phone verification using pre-registered numbers for any changes to vendor payment details or new payment requests above a set amount</li>
<li><strong>Payment change cooling period:</strong> Implement a waiting period (e.g., 48 hours) before new or changed bank account details become active for payments</li>
<li><strong>Segregation of duties:</strong> Ensure that the person who approves a payment is different from the person who initiates it</li>
</ul>

<h3>Email Security Technical Controls</h3>
<ol>
<li><strong>DMARC, DKIM, and SPF:</strong> Implement and enforce email authentication protocols that prevent domain spoofing and verify that incoming emails genuinely originate from the claimed sender domain</li>
<li><strong>External email banners:</strong> Display prominent warnings on emails originating from outside the organization, alerting recipients that the message came from an external source</li>
<li><strong>Lookalike domain monitoring:</strong> Use services that monitor for newly-registered domains that closely resemble your organization\'s domain and could be used in spoofing attacks</li>
<li><strong>Advanced email filtering:</strong> Deploy AI-powered email security solutions that analyze communication patterns and flag anomalies such as unusual sender behavior or first-time contacts requesting financial actions</li>
</ol>

<blockquote><strong>Key Principle:</strong> The best BEC defenses combine technical controls with human awareness. Technology catches the obvious fakes; trained people catch the sophisticated ones that slip through.</blockquote>

<h3>Incident Response for BEC</h3>
<p>If a BEC attack succeeds, speed is critical. Organizations should have a documented response plan that includes immediately contacting the financial institution to attempt to recall the transfer, notifying law enforcement (including filing a complaint with the FBI\'s IC3), preserving all email evidence for investigation, and conducting a root cause analysis to determine how the attack bypassed existing controls. Funds recalled within 24 hours have a significantly higher recovery rate than those reported later.</p>

<p>Regular BEC simulation exercises, similar to phishing simulations, help test organizational readiness and reinforce employee awareness. These exercises should include realistic scenarios that target finance, HR, and executive assistant roles — the positions most commonly targeted in real BEC attacks.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Business Email Compromise Prevention Quiz',
                    'max_attempts' => 3,
                    'questions' => [
                        [
                            'question' => 'What is "CEO Fraud" in the context of BEC?',
                            'answers' => [
                                ['answer' => 'When a CEO commits financial fraud within their own company', 'is_correct' => false],
                                ['answer' => 'An attacker impersonating a senior executive to trick employees into transferring money or sharing data', 'is_correct' => true],
                                ['answer' => 'A compliance violation by a company executive', 'is_correct' => false],
                                ['answer' => 'An insider threat where the CEO\'s credentials are compromised', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'When verifying a suspicious email request, what is the safest approach?',
                            'answers' => [
                                ['answer' => 'Reply to the email asking for confirmation', 'is_correct' => false],
                                ['answer' => 'Call the phone number provided in the suspicious email', 'is_correct' => false],
                                ['answer' => 'Contact the requestor using a known phone number from the company directory or your own contacts', 'is_correct' => true],
                                ['answer' => 'Forward the email to a colleague for their opinion', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which financial control is most effective at preventing unauthorized wire transfers?',
                            'answers' => [
                                ['answer' => 'Allowing any manager to independently approve transfers of any amount', 'is_correct' => false],
                                ['answer' => 'Requiring dual authorization with two independent approvals for transfers above a threshold', 'is_correct' => true],
                                ['answer' => 'Processing all transfer requests within one hour to maintain efficiency', 'is_correct' => false],
                                ['answer' => 'Using email confirmation as the sole approval method', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What do DMARC, DKIM, and SPF protect against?',
                            'answers' => [
                                ['answer' => 'Malware infections on employee workstations', 'is_correct' => false],
                                ['answer' => 'Physical theft of company documents', 'is_correct' => false],
                                ['answer' => 'Email domain spoofing by verifying that emails genuinely come from the claimed sender domain', 'is_correct' => true],
                                ['answer' => 'Brute-force attacks against email passwords', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'If a BEC attack succeeds and funds are transferred, what should be the first response action?',
                            'answers' => [
                                ['answer' => 'Wait until the next business day to investigate', 'is_correct' => false],
                                ['answer' => 'Immediately contact the financial institution to attempt to recall the transfer', 'is_correct' => true],
                                ['answer' => 'Delete all evidence to prevent further exposure', 'is_correct' => false],
                                ['answer' => 'Send a company-wide email about the incident', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 4: IoT & Smart Device Security
            [
                'title' => 'IoT & Smart Device Security',
                'slug' => 'iot-smart-device-security',
                'description' => 'Discover the unique security challenges posed by Internet of Things devices in workplace and home environments. Learn how to secure smart devices, understand their risks, and implement practical safeguards to protect your network from IoT-based threats.',
                'category' => 'Mobile & Remote Work Security',
                'difficulty' => 'beginner',
                'duration_minutes' => 40,
                'is_mandatory' => false,
                'is_active' => true,
                'sort_order' => 37,
                'lessons' => [
                    [
                        'title' => 'Introduction to IoT Security Risks',
                        'slug' => 'introduction-iot-security-risks',
                        'sort_order' => 0,
                        'duration_minutes' => 13,
                        'content' => '<h2>The Expanding World of IoT Devices</h2>
<p>The Internet of Things (IoT) refers to the vast and growing network of physical devices — from smart thermostats and security cameras to industrial sensors and connected medical equipment — that connect to the internet to collect and share data. While these devices bring convenience and efficiency, they also introduce significant security risks that many users and organizations overlook.</p>

<h3>Why IoT Devices Are Vulnerable</h3>
<p>IoT devices are often designed with functionality and cost as primary concerns, with security receiving far less attention. Many devices ship with minimal processing power, making it difficult to run traditional security software. This creates a landscape where billions of internet-connected devices operate with weak or no security protections.</p>
<ul>
<li><strong>Default credentials:</strong> Many IoT devices ship with factory-set usernames and passwords (such as admin/admin) that users never change, and some devices do not allow credential changes at all</li>
<li><strong>Infrequent updates:</strong> Manufacturers may provide limited firmware updates, and many devices lack automatic update mechanisms, leaving known vulnerabilities unpatched indefinitely</li>
<li><strong>Limited encryption:</strong> Data transmitted by IoT devices is often unencrypted or uses weak encryption protocols, allowing attackers to intercept sensitive information</li>
<li><strong>Lack of visibility:</strong> Organizations often do not maintain accurate inventories of IoT devices on their networks, creating blind spots that attackers can exploit</li>
</ul>

<blockquote><strong>Scale of the Problem:</strong> By 2025, an estimated 75 billion IoT devices were connected globally. The Mirai botnet demonstrated the risk in 2016 when it compromised hundreds of thousands of IoT devices to launch massive distributed denial-of-service (DDoS) attacks that disrupted major internet services.</blockquote>

<h3>IoT in the Workplace</h3>
<p>Modern offices are filled with IoT devices that employees may not even recognize as security risks: smart TVs in conference rooms, connected printers, building access control systems, HVAC controllers, and even smart coffee machines. Each of these devices represents a potential entry point into the corporate network. An attacker who compromises a poorly-secured smart device can use it as a foothold to move laterally into more sensitive systems.</p>

<p>Understanding that every connected device is a potential attack vector is the first step toward effective IoT security. In the following lessons, we will explore specific strategies for securing these devices in both personal and professional environments.</p>',
                    ],
                    [
                        'title' => 'Securing IoT Devices at Home and Work',
                        'slug' => 'securing-iot-devices-home-work',
                        'sort_order' => 1,
                        'duration_minutes' => 13,
                        'content' => '<h2>Practical Steps for IoT Device Security</h2>
<p>Securing IoT devices requires a combination of good habits, proper configuration, and network-level protections. While individual IoT devices may have limited security features, you can significantly reduce risk by following established best practices. These steps apply whether you are securing devices in your home, your remote work environment, or your organization\'s office.</p>

<h3>Device-Level Security</h3>
<ol>
<li><strong>Change default credentials immediately:</strong> The very first step when setting up any IoT device is to change the default username and password. Use a strong, unique password for each device. If a device does not allow you to change its credentials, seriously consider whether the risk of using it is acceptable.</li>
<li><strong>Update firmware regularly:</strong> Check for and install firmware updates from the manufacturer. Enable automatic updates when available. If a device is no longer receiving security updates from its manufacturer, plan to replace it.</li>
<li><strong>Disable unnecessary features:</strong> Turn off features you do not use, such as remote access, voice assistants, or Universal Plug and Play (UPnP). Each active feature is a potential attack surface.</li>
<li><strong>Review privacy settings:</strong> Many IoT devices collect and transmit data by default. Review the device\'s privacy settings and disable data sharing features that are not required for the device\'s intended function.</li>
</ol>

<h3>Network-Level Protections</h3>
<ul>
<li><strong>Network segmentation:</strong> Place IoT devices on a separate network (VLAN or guest network) isolated from your primary network where computers, phones, and sensitive data reside. This limits the damage if an IoT device is compromised.</li>
<li><strong>Router security:</strong> Ensure your router firmware is up to date, change its default admin password, use WPA3 encryption for Wi-Fi, and disable WPS (Wi-Fi Protected Setup) which is known to have vulnerabilities.</li>
<li><strong>Monitor network traffic:</strong> Use your router\'s monitoring features or a dedicated network monitoring tool to identify unusual traffic patterns from IoT devices, such as connections to unknown servers or data transfers at unexpected times.</li>
</ul>

<blockquote><strong>Home Office Tip:</strong> If you work remotely, your home IoT devices share the same network as your work computer. Segmenting IoT devices onto a separate guest network is one of the most effective steps you can take to protect your work data from IoT-related threats.</blockquote>

<p>Remember that IoT security is an ongoing process, not a one-time setup. Regularly review your connected devices, remove ones you no longer use, and stay informed about newly discovered vulnerabilities that may affect your equipment.</p>',
                    ],
                    [
                        'title' => 'IoT Security Policies and Incident Response',
                        'slug' => 'iot-security-policies-incident-response',
                        'sort_order' => 2,
                        'duration_minutes' => 14,
                        'content' => '<h2>Organizational IoT Security Governance</h2>
<p>For organizations, managing IoT security goes beyond individual device configuration. It requires formal policies, asset management processes, and incident response procedures specifically designed to address the unique challenges of IoT environments. Without these frameworks, IoT devices proliferate unmanaged across the network, creating an ever-expanding attack surface.</p>

<h3>Essential IoT Security Policies</h3>
<ul>
<li><strong>Device approval process:</strong> Establish a review and approval workflow before any new IoT device is connected to the corporate network. Evaluate each device\'s security features, update mechanisms, and data handling practices.</li>
<li><strong>Asset inventory:</strong> Maintain a comprehensive, up-to-date inventory of all IoT devices on the network, including their location, firmware version, owner, and purpose. You cannot secure what you do not know exists.</li>
<li><strong>End-of-life management:</strong> Define procedures for decommissioning IoT devices that have reached end-of-life or are no longer supported by the manufacturer. Unsupported devices must be removed from the network or placed in strict isolation.</li>
<li><strong>Vendor security requirements:</strong> Include minimum security standards in procurement requirements for IoT devices, such as encrypted communications, regular firmware updates, and the ability to change default credentials.</li>
</ul>

<h3>Responding to IoT Security Incidents</h3>
<p>When an IoT device is suspected of being compromised, the response process differs from traditional endpoint incidents. IoT devices often lack logging capabilities, cannot run forensic tools, and may need to be physically reset or replaced rather than cleaned.</p>
<ol>
<li><strong>Isolate the device:</strong> Immediately disconnect the suspected device from the network to prevent lateral movement or data exfiltration</li>
<li><strong>Assess the scope:</strong> Determine what network segments the device had access to and whether other devices or systems may have been affected</li>
<li><strong>Capture network logs:</strong> Since IoT devices rarely maintain their own logs, network traffic captures and firewall logs are often the primary evidence sources</li>
<li><strong>Factory reset and update:</strong> After investigation, factory reset the device, apply the latest firmware, change all credentials, and reconfigure security settings before reconnecting</li>
</ol>

<blockquote><strong>Policy Reminder:</strong> Employees should never connect personal IoT devices to the corporate network without explicit IT approval. Even a seemingly harmless device like a smart photo frame or a connected fitness tracker can serve as an entry point for attackers.</blockquote>

<p>As IoT adoption continues to accelerate, organizations that invest in governance frameworks today will be better positioned to manage the security challenges of an increasingly connected future. Regular policy reviews and IoT-specific security assessments should be part of every organization\'s ongoing security program.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'IoT & Smart Device Security Quiz',
                    'max_attempts' => 3,
                    'questions' => [
                        [
                            'question' => 'Why are IoT devices particularly vulnerable to cyberattacks?',
                            'answers' => [
                                ['answer' => 'They are always connected to high-speed internet', 'is_correct' => false],
                                ['answer' => 'They often have default credentials, limited processing power for security software, and infrequent firmware updates', 'is_correct' => true],
                                ['answer' => 'They are only used in industrial environments', 'is_correct' => false],
                                ['answer' => 'They use the same operating system as desktop computers', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the most important first step when setting up a new IoT device?',
                            'answers' => [
                                ['answer' => 'Connect it to your primary Wi-Fi network', 'is_correct' => false],
                                ['answer' => 'Enable all available features for full functionality', 'is_correct' => false],
                                ['answer' => 'Change the default username and password immediately', 'is_correct' => true],
                                ['answer' => 'Register it with the manufacturer for warranty purposes', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the recommended network strategy for IoT devices?',
                            'answers' => [
                                ['answer' => 'Connect them to the same network as all other devices for simplicity', 'is_correct' => false],
                                ['answer' => 'Place them on a separate network segment isolated from computers and sensitive data', 'is_correct' => true],
                                ['answer' => 'Disconnect them from the network when not actively in use', 'is_correct' => false],
                                ['answer' => 'Use only wired connections for all IoT devices', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do when an IoT device is no longer receiving firmware updates from its manufacturer?',
                            'answers' => [
                                ['answer' => 'Continue using it since it still works properly', 'is_correct' => false],
                                ['answer' => 'Install third-party security software on the device', 'is_correct' => false],
                                ['answer' => 'Plan to replace it or place it in strict network isolation', 'is_correct' => true],
                                ['answer' => 'Share the device password with IT so they can monitor it', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'When responding to a suspected IoT device compromise, what is the first action to take?',
                            'answers' => [
                                ['answer' => 'Update the device firmware to the latest version', 'is_correct' => false],
                                ['answer' => 'Immediately disconnect the device from the network', 'is_correct' => true],
                                ['answer' => 'Run an antivirus scan on the device', 'is_correct' => false],
                                ['answer' => 'Contact the device manufacturer for support', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 5: Deepfake & AI-Powered Social Engineering
            [
                'title' => 'Deepfake & AI-Powered Social Engineering',
                'slug' => 'deepfake-ai-powered-social-engineering',
                'description' => 'Examine how artificial intelligence is being weaponized to create convincing deepfake audio, video, and text for social engineering attacks. This advanced course prepares you to identify AI-generated deceptions and implement verification protocols against this rapidly evolving threat.',
                'category' => 'Social Engineering',
                'difficulty' => 'advanced',
                'duration_minutes' => 55,
                'is_mandatory' => true,
                'is_active' => true,
                'sort_order' => 38,
                'lessons' => [
                    [
                        'title' => 'The Rise of AI-Powered Deception',
                        'slug' => 'rise-of-ai-powered-deception',
                        'sort_order' => 0,
                        'duration_minutes' => 18,
                        'content' => '<h2>How AI Is Transforming Social Engineering</h2>
<p>Artificial intelligence has fundamentally changed the landscape of social engineering attacks. Threat actors now use AI to generate highly convincing fake audio, video, images, and text that can impersonate real people with alarming accuracy. These AI-powered tools lower the barrier to entry for sophisticated attacks that previously required specialized skills and significant resources.</p>

<h3>Types of AI-Generated Deceptions</h3>
<ul>
<li><strong>Voice deepfakes:</strong> AI models trained on just a few minutes of a person\'s recorded speech can generate real-time voice clones capable of conducting convincing phone conversations. Attackers have used this technology to impersonate CEOs and authorize fraudulent wire transfers.</li>
<li><strong>Video deepfakes:</strong> AI-generated video can place a target\'s face onto another person\'s body in real-time video calls, making it appear that a trusted colleague or executive is participating in a live meeting.</li>
<li><strong>AI-generated text:</strong> Large language models can craft highly personalized phishing emails, social media messages, and chat communications that mimic a specific person\'s writing style, tone, and vocabulary.</li>
<li><strong>Synthetic images:</strong> AI-generated profile photos, forged documents, and fabricated evidence can support false identities and fraudulent narratives that are difficult to distinguish from authentic materials.</li>
</ul>

<blockquote><strong>Case Study:</strong> In 2024, a multinational corporation lost $25 million when an employee was deceived by a video conference call in which deepfake technology was used to impersonate the company\'s CFO and other senior executives. The employee believed they were in a live meeting with trusted colleagues.</blockquote>

<h3>Why AI Makes Social Engineering More Dangerous</h3>
<p>Traditional social engineering attacks often failed because of telltale signs — grammatical errors in phishing emails, unnatural speech patterns in vishing calls, or inconsistencies in fake identities. AI eliminates many of these red flags. AI-generated phishing emails are grammatically flawless, culturally appropriate, and can be personalized at scale. Voice clones sound natural and respond dynamically in conversation. The volume and quality of attacks are increasing simultaneously.</p>

<h3>The Democratization of Attack Tools</h3>
<p>Perhaps most concerning is the accessibility of these technologies. Open-source voice cloning tools, commercial deepfake applications, and powerful language models are readily available. Creating a convincing voice clone no longer requires a well-funded state intelligence agency — it can be done by anyone with a laptop and a few sample recordings. This democratization means organizations must prepare for a future where AI-powered deception is the norm rather than the exception.</p>',
                    ],
                    [
                        'title' => 'Detecting Deepfakes and AI-Generated Content',
                        'slug' => 'detecting-deepfakes-ai-generated-content',
                        'sort_order' => 1,
                        'duration_minutes' => 18,
                        'content' => '<h2>How to Spot AI-Generated Deceptions</h2>
<p>While AI-generated content is becoming increasingly sophisticated, there are still observable artifacts and techniques that can help identify fakes. Detection requires a combination of technological tools, observational skills, and healthy skepticism. As AI capabilities improve, detection methods must continuously evolve as well.</p>

<h3>Visual and Audio Indicators</h3>
<p>Deepfake video and audio may exhibit subtle artifacts that a trained observer can identify, particularly in real-time or live scenarios where processing limitations introduce inconsistencies.</p>
<ul>
<li><strong>Facial inconsistencies:</strong> Watch for unnatural blinking patterns, misalignment at the jawline or hairline, inconsistent skin texture, or lighting that does not match the environment</li>
<li><strong>Audio anomalies:</strong> Listen for unusual pauses, metallic or robotic tonal shifts, inconsistent background noise, or subtle echo patterns that differ from normal phone or video call audio</li>
<li><strong>Lip sync issues:</strong> In video deepfakes, the movement of the lips may not perfectly synchronize with the spoken words, especially during rapid speech or unusual phonemes</li>
<li><strong>Emotional inconsistency:</strong> AI-generated faces may display emotions that do not match the tone of voice or the context of the conversation</li>
</ul>

<h3>Textual Detection Strategies</h3>
<ol>
<li><strong>Style analysis:</strong> Compare the writing style of a suspicious message against known authentic communications from the purported sender. AI-generated text may be technically proficient but lack the personal quirks, abbreviations, or formatting habits of the real person.</li>
<li><strong>Content verification:</strong> Check specific claims, references, or details mentioned in the message against independent sources. AI-generated text may contain plausible-sounding but fabricated details.</li>
<li><strong>Contextual anomalies:</strong> Be alert to messages that reference events, projects, or relationships with slight inaccuracies, or that demonstrate knowledge the sender should not have — or conversely, lack knowledge they should.</li>
</ol>

<blockquote><strong>Critical Mindset:</strong> The best defense against AI-generated deception is not any single detection technique but a verification-first approach. When the stakes are high — financial transactions, credential sharing, sensitive data — always verify through an independent channel, regardless of how convincing the communication appears.</blockquote>

<h3>Technology-Assisted Detection</h3>
<p>Organizations are deploying AI-powered detection tools that analyze media for signs of manipulation. These tools examine pixel-level patterns, audio spectrograms, and metadata inconsistencies that are invisible to the human eye and ear. While no detection tool is perfect, they add a valuable layer of defense when integrated into communication and content verification workflows. Staying current with these tools is important, as the arms race between deepfake generation and detection continues to accelerate.</p>',
                    ],
                    [
                        'title' => 'Organizational Defenses Against AI-Powered Attacks',
                        'slug' => 'organizational-defenses-ai-powered-attacks',
                        'sort_order' => 2,
                        'duration_minutes' => 19,
                        'content' => '<h2>Building Resilience Against AI-Enhanced Threats</h2>
<p>Defending against AI-powered social engineering requires organizations to rethink their verification protocols, communication procedures, and security awareness programs. Traditional defenses that relied on detecting grammatical errors or unnatural speech are no longer sufficient. Organizations must implement systematic verification processes that do not depend on the ability to distinguish real from fake.</p>

<h3>Updated Verification Protocols</h3>
<ul>
<li><strong>Multi-channel verification:</strong> For any high-impact request — financial transactions, credential changes, data access — require verification through at least two independent communication channels. A voice call confirming an email request, or a video call confirming a text message.</li>
<li><strong>Code words and challenge phrases:</strong> Establish pre-arranged code words or challenge-response phrases that can be used to verify identity during phone or video calls. These phrases should be changed regularly and shared only through secure, in-person channels.</li>
<li><strong>Callback procedures:</strong> Always initiate verification calls yourself using known contact information. Never accept an inbound call or callback as proof of identity, as deepfake voice technology can operate in real time.</li>
<li><strong>Time-delayed approvals:</strong> For sensitive actions, implement mandatory waiting periods that allow time for verification and reduce the effectiveness of urgency-based attacks.</li>
</ul>

<h3>Security Awareness for the AI Era</h3>
<p>Security awareness training must evolve to address AI-powered threats specifically. Employees need to understand that they can no longer trust their senses alone — a familiar voice on the phone or a colleague\'s face on a video call may be synthetically generated. Training should include live demonstrations of deepfake technology so employees can experience firsthand how convincing these fabrications can be.</p>

<blockquote><strong>Training Recommendation:</strong> Conduct regular exercises where employees encounter simulated deepfake scenarios — a fake voice message from their manager, a manipulated video call request, or an AI-crafted phishing email. These exercises build the reflexive skepticism needed to resist real attacks.</blockquote>

<h3>Implementing an AI Threat Response Framework</h3>
<ol>
<li><strong>Establish reporting channels:</strong> Create clear, easy-to-use reporting mechanisms for employees who suspect they have encountered a deepfake or AI-generated deception</li>
<li><strong>Develop specific playbooks:</strong> Create incident response procedures tailored to AI-powered attacks, including evidence preservation for synthetic media and coordination with specialized forensic services</li>
<li><strong>Monitor the threat landscape:</strong> Assign responsibility for tracking developments in AI-powered attack techniques and updating defenses accordingly</li>
<li><strong>Collaborate across the industry:</strong> Participate in information-sharing organizations and industry groups focused on AI threats to stay ahead of emerging attack methods</li>
</ol>

<p>The organizations that will be most resilient against AI-powered social engineering are those that combine robust verification procedures with a culture of healthy skepticism. When every employee understands that any communication could potentially be fabricated, and acts accordingly by verifying before trusting, the effectiveness of even the most sophisticated AI-generated attacks is dramatically reduced.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Deepfake & AI-Powered Social Engineering Quiz',
                    'max_attempts' => 3,
                    'questions' => [
                        [
                            'question' => 'How much audio is typically needed to create a convincing AI voice clone?',
                            'answers' => [
                                ['answer' => 'Several hours of high-quality recordings', 'is_correct' => false],
                                ['answer' => 'Just a few minutes of recorded speech', 'is_correct' => true],
                                ['answer' => 'An entire day of continuous conversation', 'is_correct' => false],
                                ['answer' => 'Voice cloning requires direct access to the person', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which visual artifact is most commonly associated with deepfake video?',
                            'answers' => [
                                ['answer' => 'The video always appears in black and white', 'is_correct' => false],
                                ['answer' => 'The background is always blurred', 'is_correct' => false],
                                ['answer' => 'Unnatural blinking, facial boundary artifacts, or lip sync issues', 'is_correct' => true],
                                ['answer' => 'The person in the video always wears sunglasses', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the most reliable defense against a deepfake voice call requesting a financial transfer?',
                            'answers' => [
                                ['answer' => 'Listening carefully to determine if the voice sounds natural', 'is_correct' => false],
                                ['answer' => 'Asking the caller personal questions to verify their identity', 'is_correct' => false],
                                ['answer' => 'Verifying the request through an independent channel using a known contact number', 'is_correct' => true],
                                ['answer' => 'Recording the call for later analysis', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the purpose of pre-arranged code words or challenge phrases?',
                            'answers' => [
                                ['answer' => 'To encrypt email communications', 'is_correct' => false],
                                ['answer' => 'To verify identity during calls or meetings when deepfakes may be used', 'is_correct' => true],
                                ['answer' => 'To replace passwords for system access', 'is_correct' => false],
                                ['answer' => 'To report security incidents to management', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is the democratization of AI deepfake tools a concern for organizations?',
                            'answers' => [
                                ['answer' => 'It makes AI tools more expensive for businesses to use', 'is_correct' => false],
                                ['answer' => 'It means only large companies are at risk', 'is_correct' => false],
                                ['answer' => 'Sophisticated attacks no longer require specialized skills or resources, increasing the number of potential attackers', 'is_correct' => true],
                                ['answer' => 'It reduces the quality of deepfake content', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // Course 6: Regulatory Compliance Essentials
            [
                'title' => 'Regulatory Compliance Essentials',
                'slug' => 'regulatory-compliance-essentials',
                'description' => 'Gain a foundational understanding of the major regulatory frameworks that govern data protection and cybersecurity. This beginner-friendly course covers GDPR, HIPAA, PCI DSS, and other key regulations, helping you understand your compliance obligations and how to fulfill them.',
                'category' => 'Incident Response & Compliance',
                'difficulty' => 'beginner',
                'duration_minutes' => 45,
                'is_mandatory' => false,
                'is_active' => true,
                'sort_order' => 39,
                'lessons' => [
                    [
                        'title' => 'Understanding Key Regulatory Frameworks',
                        'slug' => 'understanding-key-regulatory-frameworks',
                        'sort_order' => 0,
                        'duration_minutes' => 15,
                        'content' => '<h2>The Regulatory Landscape for Cybersecurity</h2>
<p>Organizations today operate under a complex web of regulations designed to protect personal data, ensure cybersecurity standards, and maintain consumer trust. Understanding which regulations apply to your organization and what they require is essential for every employee, not just the compliance team. Non-compliance can result in substantial fines, legal liability, and lasting reputational damage.</p>

<h3>Major Regulatory Frameworks</h3>
<ul>
<li><strong>GDPR (General Data Protection Regulation):</strong> The European Union\'s comprehensive data protection law that governs how organizations collect, process, store, and share personal data of EU residents. It applies to any organization worldwide that handles EU residents\' data, with fines up to 4% of annual global revenue or 20 million euros.</li>
<li><strong>HIPAA (Health Insurance Portability and Accountability Act):</strong> U.S. legislation that sets standards for protecting sensitive patient health information. It applies to healthcare providers, health plans, healthcare clearinghouses, and their business associates.</li>
<li><strong>PCI DSS (Payment Card Industry Data Security Standard):</strong> A set of security standards designed to ensure that all companies that accept, process, store, or transmit credit card information maintain a secure environment. Non-compliance can result in fines and loss of the ability to process card payments.</li>
<li><strong>SOX (Sarbanes-Oxley Act):</strong> U.S. legislation requiring publicly traded companies to maintain accurate financial records and implement internal controls to prevent fraud, with significant implications for IT systems that handle financial data.</li>
</ul>

<blockquote><strong>Key Principle:</strong> Compliance is not the same as security, but the two are deeply intertwined. A compliant organization is not necessarily secure, and a secure organization is not necessarily compliant. Both are required — compliance sets the minimum baseline, and good security practices go beyond it.</blockquote>

<h3>Why Compliance Matters to Every Employee</h3>
<p>Regulatory compliance is not solely the responsibility of the legal or IT departments. Every employee who handles data — from customer service representatives accessing client records to marketing teams managing email lists — plays a role in maintaining compliance. A single employee mishandling personal data can trigger a breach notification obligation, regulatory investigation, and significant organizational consequences.</p>

<p>Understanding the basics of applicable regulations helps employees make better daily decisions about how they handle data, respond to requests, and report potential issues. The following lessons will explore your practical compliance obligations and how to fulfill them in your day-to-day work.</p>',
                    ],
                    [
                        'title' => 'Your Compliance Obligations in Practice',
                        'slug' => 'compliance-obligations-in-practice',
                        'sort_order' => 1,
                        'duration_minutes' => 15,
                        'content' => '<h2>Translating Regulations Into Daily Actions</h2>
<p>While regulatory frameworks can seem abstract and complex, they translate into concrete daily actions and decisions for every employee. Understanding your practical obligations helps you handle data responsibly and avoid actions that could put the organization at risk of non-compliance. This lesson covers the most common compliance-related scenarios you may encounter in your work.</p>

<h3>Data Handling Best Practices</h3>
<ol>
<li><strong>Data minimization:</strong> Collect and retain only the personal data that is genuinely necessary for the stated purpose. Do not gather extra information "just in case" — under GDPR and similar regulations, excessive data collection is itself a violation.</li>
<li><strong>Purpose limitation:</strong> Use personal data only for the purpose for which it was collected. Customer data gathered for order fulfillment should not be repurposed for marketing without separate consent.</li>
<li><strong>Storage and retention:</strong> Store personal data securely and only for as long as it is needed. Follow your organization\'s data retention policies and securely delete data when it is no longer required.</li>
<li><strong>Access controls:</strong> Only access personal data that you need for your specific job function. Browsing records out of curiosity — even without sharing them — can constitute a compliance violation.</li>
</ol>

<h3>Responding to Data Subject Requests</h3>
<p>Under regulations like GDPR, individuals have specific rights regarding their personal data. Employees may receive requests from customers or other individuals exercising these rights, and it is important to know how to handle them.</p>
<ul>
<li><strong>Right of access:</strong> Individuals can request a copy of all personal data an organization holds about them</li>
<li><strong>Right to rectification:</strong> Individuals can request correction of inaccurate or incomplete personal data</li>
<li><strong>Right to erasure:</strong> Also known as the "right to be forgotten," individuals can request deletion of their personal data under certain circumstances</li>
<li><strong>Right to data portability:</strong> Individuals can request their data in a structured, machine-readable format for transfer to another service</li>
</ul>

<blockquote><strong>What To Do:</strong> If you receive a data subject request, do not attempt to fulfill it yourself unless your role specifically requires it. Forward the request immediately to your organization\'s designated privacy officer or data protection team, and document when and how the request was received.</blockquote>

<p>Compliance is an ongoing practice, not a one-time achievement. Regulations evolve, new frameworks are introduced, and organizational practices must adapt continuously. Staying informed through regular training and updates from your compliance team is an essential part of your professional responsibility.</p>',
                    ],
                    [
                        'title' => 'Breach Notification and Audit Readiness',
                        'slug' => 'breach-notification-audit-readiness',
                        'sort_order' => 2,
                        'duration_minutes' => 15,
                        'content' => '<h2>What Happens When Things Go Wrong</h2>
<p>Despite best efforts, data breaches and compliance incidents do occur. How an organization responds to a breach — particularly the speed and transparency of its notification process — can significantly affect the legal, financial, and reputational consequences. Most regulatory frameworks impose strict breach notification requirements with specific timelines that organizations must meet.</p>

<h3>Breach Notification Requirements</h3>
<ul>
<li><strong>GDPR:</strong> Requires notification to the relevant supervisory authority within 72 hours of becoming aware of a personal data breach that is likely to result in a risk to individuals\' rights and freedoms. Affected individuals must also be notified without undue delay if the breach poses a high risk.</li>
<li><strong>HIPAA:</strong> Requires notification to affected individuals within 60 days of discovery of a breach involving unsecured protected health information. Breaches affecting 500 or more individuals must also be reported to the Department of Health and Human Services and prominent media outlets.</li>
<li><strong>PCI DSS:</strong> Requires organizations to notify the relevant payment card brands and acquiring banks immediately upon discovering a breach involving cardholder data. Forensic investigation by a qualified security assessor is typically required.</li>
<li><strong>State breach notification laws:</strong> In the U.S., all 50 states have their own breach notification laws with varying requirements regarding timing, content, and the types of data that trigger notification obligations.</li>
</ul>

<h3>Your Role in Breach Response</h3>
<p>Every employee plays a critical role in breach detection and response. The faster a potential breach is identified and reported internally, the better the organization can manage its notification obligations and limit the damage.</p>
<ol>
<li><strong>Report immediately:</strong> If you suspect a data breach — lost device, unauthorized access, accidental data exposure, or suspicious system activity — report it to your IT security team or through the designated incident reporting channel immediately. Do not wait to investigate on your own.</li>
<li><strong>Preserve evidence:</strong> Do not attempt to fix the problem yourself, delete logs, or alter affected systems. Preservation of evidence is essential for both the investigation and any subsequent legal or regulatory proceedings.</li>
<li><strong>Document what you know:</strong> Record what happened, when you noticed it, what data may be affected, and any actions you took. Provide this information to the incident response team.</li>
</ol>

<blockquote><strong>Audit Readiness:</strong> Regulatory audits can occur at any time. Maintain organized records, follow documented procedures, and ensure that your team\'s data handling practices align with your organization\'s compliance policies. The best time to prepare for an audit is before you know one is coming.</blockquote>

<p>Understanding breach notification requirements and maintaining audit readiness are not just compliance obligations — they are fundamental components of organizational trust. Customers, partners, and regulators all evaluate organizations based on their ability to handle incidents transparently and responsibly. Your awareness and preparedness contribute directly to that organizational capability.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Regulatory Compliance Essentials Quiz',
                    'max_attempts' => 3,
                    'questions' => [
                        [
                            'question' => 'Which regulation applies to any organization worldwide that handles personal data of EU residents?',
                            'answers' => [
                                ['answer' => 'HIPAA', 'is_correct' => false],
                                ['answer' => 'PCI DSS', 'is_correct' => false],
                                ['answer' => 'GDPR', 'is_correct' => true],
                                ['answer' => 'SOX', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Under GDPR, how quickly must an organization notify the supervisory authority of a qualifying data breach?',
                            'answers' => [
                                ['answer' => 'Within 24 hours', 'is_correct' => false],
                                ['answer' => 'Within 72 hours', 'is_correct' => true],
                                ['answer' => 'Within 30 days', 'is_correct' => false],
                                ['answer' => 'Within 60 days', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does the principle of "data minimization" require?',
                            'answers' => [
                                ['answer' => 'Encrypting all data at rest', 'is_correct' => false],
                                ['answer' => 'Collecting and retaining only the personal data genuinely necessary for the stated purpose', 'is_correct' => true],
                                ['answer' => 'Storing data in the smallest possible file format', 'is_correct' => false],
                                ['answer' => 'Limiting the number of employees who can view reports', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'If you receive a data subject access request from a customer, what should you do?',
                            'answers' => [
                                ['answer' => 'Ignore it since only the legal team can respond', 'is_correct' => false],
                                ['answer' => 'Fulfill the request immediately by sending all available data', 'is_correct' => false],
                                ['answer' => 'Forward it immediately to your organization\'s designated privacy officer or data protection team', 'is_correct' => true],
                                ['answer' => 'Ask the customer to submit the request through a social media channel', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'If you suspect a data breach has occurred, what is the correct first action?',
                            'answers' => [
                                ['answer' => 'Try to fix the problem yourself before reporting it', 'is_correct' => false],
                                ['answer' => 'Delete any affected logs to limit exposure', 'is_correct' => false],
                                ['answer' => 'Wait to see if anyone else notices before raising an alarm', 'is_correct' => false],
                                ['answer' => 'Report it immediately to your IT security team through the designated incident reporting channel', 'is_correct' => true],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
