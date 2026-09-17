<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizAnswer;
use Illuminate\Database\Seeder;

class CourseSeederBatch6 extends Seeder
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
            // ── Module 28: GDPR & Data Privacy Regulations ──
            [
                'title' => 'GDPR & Data Privacy Regulations',
                'slug' => 'gdpr-data-privacy-regulations',
                'description' => 'Understand the General Data Protection Regulation and other major privacy frameworks, including individual rights, lawful processing, and cross-border data transfer rules.',
                'objectives' => [
                    'Explain the core principles and individual rights established by the GDPR',
                    'Identify the six lawful bases for processing personal data',
                    'Understand the rules governing cross-border data transfers outside the EEA',
                    'Apply GDPR requirements to everyday business activities involving personal data',
                ],
                'category' => 'Data Protection & Privacy',
                'difficulty' => 'intermediate',
                'duration_minutes' => 25,
                'passing_score' => 75,
                'sort_order' => 28,
                'lessons' => [
                    [
                        'title' => 'GDPR Principles & Rights',
                        'slug' => 'gdpr-principles-rights',
                        'duration_minutes' => 8,
                        'content' => '<h3>GDPR Principles & Rights</h3>
<p>The General Data Protection Regulation (GDPR) is the European Union\'s comprehensive data privacy law that took effect in May 2018. It applies to any organization that processes personal data of individuals located in the European Economic Area (EEA), regardless of where the organization itself is based. This means a company headquartered in the United States that serves European customers must comply with GDPR just as a company in Berlin would.</p>

<h3>The Seven Core Principles</h3>
<p>GDPR is built on seven foundational principles that guide all data processing activities. These are not optional guidelines — they are legally binding requirements that organizations must demonstrate compliance with at all times.</p>
<ul>
<li><strong>Lawfulness, fairness, and transparency:</strong> Data must be processed legally, in ways the individual would reasonably expect, and with clear communication about how their data is used</li>
<li><strong>Purpose limitation:</strong> Data must be collected for specified, explicit, and legitimate purposes and not further processed in ways incompatible with those purposes</li>
<li><strong>Data minimisation:</strong> Only the minimum amount of personal data necessary for the stated purpose should be collected and retained</li>
<li><strong>Accuracy:</strong> Personal data must be kept accurate and up to date, with reasonable steps taken to correct or delete inaccurate data promptly</li>
<li><strong>Storage limitation:</strong> Data should be kept in identifiable form only for as long as necessary to fulfil the original purpose</li>
<li><strong>Integrity and confidentiality:</strong> Data must be protected against unauthorized access, accidental loss, or destruction through appropriate technical and organizational measures</li>
<li><strong>Accountability:</strong> The data controller must be able to demonstrate compliance with all of the above principles</li>
</ul>

<h3>Individual Rights Under GDPR</h3>
<p>GDPR grants individuals eight specific rights over their personal data. The right of access allows individuals to obtain a copy of all personal data an organization holds about them. The right to rectification lets them correct inaccurate data. The right to erasure — sometimes called the "right to be forgotten" — allows individuals to request deletion of their data when it is no longer needed. The right to data portability means individuals can receive their data in a machine-readable format and transfer it to another service. Organizations must be prepared to respond to these requests within one calendar month.</p>

<p>Understanding these principles and rights is not just a legal obligation — it is essential for building trust with customers and partners who entrust you with their personal information. Every employee who handles personal data plays a role in maintaining GDPR compliance.</p>',
                    ],
                    [
                        'title' => 'Lawful Basis for Processing',
                        'slug' => 'lawful-basis-for-processing',
                        'duration_minutes' => 9,
                        'content' => '<h3>Lawful Basis for Processing</h3>
<p>Under GDPR, every instance of processing personal data must be justified by one of six lawful bases. You cannot collect or use personal data simply because it would be useful or convenient — there must be a legally recognized reason. Choosing the correct lawful basis is critical because it determines what rights individuals have and what obligations the organization must meet.</p>

<h3>The Six Lawful Bases</h3>
<ul>
<li><strong>Consent:</strong> The individual has given clear, informed, and freely given agreement to the processing of their data for a specific purpose. Consent must be as easy to withdraw as it was to give, and pre-ticked boxes or bundled consent do not qualify</li>
<li><strong>Contract:</strong> Processing is necessary to fulfil a contract with the individual or to take steps at their request before entering into a contract. For example, processing a shipping address to deliver a product someone purchased</li>
<li><strong>Legal obligation:</strong> Processing is necessary to comply with a law the organization is subject to, such as retaining financial records for tax purposes or reporting certain transactions to regulators</li>
<li><strong>Vital interests:</strong> Processing is necessary to protect someone\'s life. This basis is narrow and typically applies only in genuine life-or-death emergencies, such as sharing medical information with a hospital during a crisis</li>
<li><strong>Public task:</strong> Processing is necessary for a task carried out in the public interest or under official authority, primarily applicable to government bodies and organizations performing public functions</li>
<li><strong>Legitimate interests:</strong> Processing is necessary for the legitimate interests of the organization or a third party, provided those interests are not overridden by the individual\'s rights and freedoms. This requires a documented balancing test weighing the organization\'s need against the individual\'s privacy</li>
</ul>

<h3>Choosing the Right Basis</h3>
<p>The lawful basis must be determined before processing begins and documented in the organization\'s Records of Processing Activities (ROPA). Different bases carry different obligations. Consent, for instance, requires mechanisms for individuals to withdraw it at any time. Legitimate interests requires a formal Legitimate Interest Assessment (LIA) documenting why the processing is necessary and proportionate. Choosing the wrong basis — or failing to document one — can result in fines of up to 20 million euros or 4% of global annual turnover, whichever is higher.</p>

<p>In practice, most employee data processing relies on the contract and legal obligation bases, while marketing activities often require consent. When evaluating which basis applies, consult your organization\'s Data Protection Officer or legal team rather than making assumptions.</p>',
                    ],
                    [
                        'title' => 'Cross-Border Data Transfers',
                        'slug' => 'cross-border-data-transfers',
                        'duration_minutes' => 8,
                        'content' => '<h3>Cross-Border Data Transfers</h3>
<p>One of GDPR\'s most impactful provisions restricts the transfer of personal data outside the European Economic Area (EEA). The regulation requires that personal data transferred to a third country receives a level of protection "essentially equivalent" to that provided within the EEA. This has significant implications for multinational organizations, cloud computing, and any business that uses services hosted outside Europe.</p>

<h3>Adequacy Decisions</h3>
<p>The European Commission can determine that a country outside the EEA provides an adequate level of data protection through an "adequacy decision." Countries with adequacy decisions — including Canada, Japan, South Korea, the United Kingdom, and others — can receive personal data from the EEA without additional safeguards. The EU-US Data Privacy Framework, adopted in 2023, provides a mechanism for certified US organizations to receive EEA personal data, replacing the earlier Privacy Shield that was invalidated by the Court of Justice of the EU in 2020.</p>

<h3>Transfer Mechanisms When No Adequacy Decision Exists</h3>
<ul>
<li><strong>Standard Contractual Clauses (SCCs):</strong> Pre-approved contract terms issued by the European Commission that the data exporter and importer sign, committing the importer to protect the data to EEA standards. SCCs must be supplemented with a Transfer Impact Assessment evaluating the legal framework in the destination country</li>
<li><strong>Binding Corporate Rules (BCRs):</strong> Internal policies approved by a supervisory authority that allow multinational corporations to transfer personal data within their group of companies globally. BCRs are comprehensive but expensive and time-consuming to implement</li>
<li><strong>Derogations:</strong> In limited situations, transfers can proceed based on explicit consent, necessity for a contract, or important reasons of public interest. These are exceptions, not routine transfer mechanisms</li>
</ul>

<h3>Practical Implications for Employees</h3>
<p>When selecting a new cloud service, SaaS vendor, or external partner, always consider where personal data will be stored and processed. A tool that stores data on servers in a country without an adequacy decision may require SCCs or other safeguards before personal data can flow to it. Before uploading personal data to any new platform, check with your Data Protection Officer to ensure the appropriate transfer mechanism is in place. Failure to secure lawful transfers is one of the most common GDPR violations and has resulted in substantial fines for organizations of all sizes.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'GDPR & Data Privacy Regulations Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'Which GDPR principle requires that only the minimum necessary personal data be collected?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The data minimisation principle states that only the personal data necessary for the specified purpose should be collected and processed.',
                            'answers' => [
                                ['answer' => 'Purpose limitation', 'is_correct' => false],
                                ['answer' => 'Data minimisation', 'is_correct' => true],
                                ['answer' => 'Storage limitation', 'is_correct' => false],
                                ['answer' => 'Integrity and confidentiality', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'An employee wants a copy of all personal data your organization holds about them. Which GDPR right are they exercising?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The right of access (also called a Subject Access Request) allows individuals to obtain a copy of all personal data held about them.',
                            'answers' => [
                                ['answer' => 'Right to erasure', 'is_correct' => false],
                                ['answer' => 'Right to data portability', 'is_correct' => false],
                                ['answer' => 'Right of access', 'is_correct' => true],
                                ['answer' => 'Right to rectification', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which lawful basis requires a documented balancing test weighing the organization\'s need against the individual\'s privacy?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Legitimate interests requires a formal Legitimate Interest Assessment (LIA) that documents why the processing is necessary and proportionate.',
                            'answers' => [
                                ['answer' => 'Consent', 'is_correct' => false],
                                ['answer' => 'Contract', 'is_correct' => false],
                                ['answer' => 'Legal obligation', 'is_correct' => false],
                                ['answer' => 'Legitimate interests', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question' => 'What are Standard Contractual Clauses (SCCs) used for?',
                            'type' => 'multiple_choice',
                            'explanation' => 'SCCs are pre-approved contract terms that enable lawful transfer of personal data to countries outside the EEA that lack an adequacy decision.',
                            'answers' => [
                                ['answer' => 'Encrypting personal data stored on local servers', 'is_correct' => false],
                                ['answer' => 'Enabling lawful transfer of personal data to countries outside the EEA without an adequacy decision', 'is_correct' => true],
                                ['answer' => 'Granting employees access to their own personnel files', 'is_correct' => false],
                                ['answer' => 'Defining an organization\'s internal password policy', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the maximum fine for serious GDPR violations?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The most serious GDPR violations can result in fines of up to 20 million euros or 4% of global annual turnover, whichever is higher.',
                            'answers' => [
                                ['answer' => '1 million euros or 1% of turnover', 'is_correct' => false],
                                ['answer' => '10 million euros or 2% of turnover', 'is_correct' => false],
                                ['answer' => '20 million euros or 4% of global annual turnover, whichever is higher', 'is_correct' => true],
                                ['answer' => '50 million euros flat penalty', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 29: Data Classification & Handling ──
            [
                'title' => 'Data Classification & Handling',
                'slug' => 'data-classification-handling',
                'description' => 'Learn how to classify data by sensitivity level and apply appropriate handling, storage, and sharing procedures to protect organizational information.',
                'objectives' => [
                    'Explain common data classification levels and their criteria',
                    'Apply proper handling procedures for each classification level',
                    'Follow secure practices when sharing data internally and externally',
                    'Recognize violations of data handling policies and respond appropriately',
                ],
                'category' => 'Data Protection & Privacy',
                'difficulty' => 'beginner',
                'is_mandatory' => true,
                'duration_minutes' => 20,
                'passing_score' => 70,
                'sort_order' => 29,
                'lessons' => [
                    [
                        'title' => 'Data Classification Levels',
                        'slug' => 'data-classification-levels',
                        'duration_minutes' => 7,
                        'content' => '<h3>Data Classification Levels</h3>
<p>Data classification is the process of organizing information into categories based on its sensitivity and the impact that unauthorized disclosure would have on the organization. Without classification, employees have no clear guidance on how to handle different types of information, leading to either over-protection that hampers productivity or under-protection that creates security risks.</p>

<h3>Common Classification Tiers</h3>
<p>While specific labels vary between organizations, most classification schemes use four tiers that map to increasing levels of sensitivity and control.</p>
<ul>
<li><strong>Public:</strong> Information intended for open distribution with no impact if disclosed. Examples include marketing brochures, published press releases, job postings, and public-facing website content. No special handling is required</li>
<li><strong>Internal:</strong> Information meant for use within the organization that is not intended for public release. Disclosure would cause minimal harm but could be embarrassing or give competitors minor advantages. Examples include internal newsletters, organizational charts, non-sensitive meeting notes, and general policies</li>
<li><strong>Confidential:</strong> Sensitive business information whose unauthorized disclosure could cause significant harm to the organization, its employees, or its partners. Examples include financial reports, employee personal data, customer lists, contracts, strategic plans, and proprietary processes</li>
<li><strong>Restricted:</strong> The most sensitive data whose compromise could cause severe damage including regulatory penalties, major financial loss, or harm to individuals. Examples include Social Security numbers, credit card data, health records, trade secrets, encryption keys, and authentication credentials</li>
</ul>

<h3>Who Classifies Data?</h3>
<p>Data classification is typically the responsibility of the data owner — the person or team that creates or is accountable for the information. When you create a document, spreadsheet, or database, you should assign it a classification based on the most sensitive element it contains. A report that is mostly internal information but includes one column of employee Social Security numbers should be classified as Restricted, not Internal. When in doubt, classify at the higher level and consult your manager or data protection team for guidance.</p>

<p>Classification is not a one-time activity. Data sensitivity can change over time — a product roadmap is Confidential before launch but may become Internal or Public afterward. Regular reviews ensure classifications remain accurate and appropriate controls stay in place.</p>',
                    ],
                    [
                        'title' => 'Handling Sensitive Information',
                        'slug' => 'handling-sensitive-information',
                        'duration_minutes' => 7,
                        'content' => '<h3>Handling Sensitive Information</h3>
<p>Each classification level comes with specific handling requirements that govern how data is stored, transmitted, accessed, and disposed of. Following these procedures consistently is what transforms a classification label from a meaningless tag into actual protection for sensitive information.</p>

<h3>Storage Requirements</h3>
<ul>
<li><strong>Public and Internal data</strong> can be stored on standard company systems including shared drives, collaboration platforms, and approved cloud services. No additional encryption beyond the platform defaults is typically required</li>
<li><strong>Confidential data</strong> must be stored on access-controlled systems where permissions limit access to authorized personnel. Encryption at rest is recommended, and the data should not be stored on personal devices without IT approval and device encryption</li>
<li><strong>Restricted data</strong> requires encryption at rest and in transit, strict access controls with logging, and storage only on systems specifically approved for that classification level. Restricted data must never be stored on personal devices, removable media, or unapproved cloud services</li>
</ul>

<h3>Access Controls</h3>
<p>Access to sensitive data should follow the principle of least privilege — each person should have access only to the data they need for their specific role. When an employee changes roles or leaves the organization, their access to Confidential and Restricted data must be reviewed and adjusted promptly. Shared folders and team drives should be audited regularly to ensure that access permissions have not expanded beyond what is appropriate through accumulated sharing over time.</p>

<h3>Physical Handling</h3>
<p>Printed documents containing Confidential or Restricted information should never be left unattended on desks, printers, or in common areas. Use secure printing features that require badge authentication at the printer before documents are released. When no longer needed, shred printed Confidential and Restricted documents using a cross-cut shredder — standard recycling bins are not appropriate. Whiteboards containing sensitive information should be erased after meetings, especially in rooms used by multiple teams or accessible to visitors.</p>

<p>Laptops and mobile devices containing sensitive data must be encrypted and protected with strong authentication. Enable remote wipe capabilities so that data can be erased if a device is lost or stolen. Lock your screen every time you step away from your workstation, even for a moment.</p>',
                    ],
                    [
                        'title' => 'Secure Data Sharing Practices',
                        'slug' => 'secure-data-sharing-practices',
                        'duration_minutes' => 6,
                        'content' => '<h3>Secure Data Sharing Practices</h3>
<p>Sharing data is a normal part of business operations, but how you share it matters enormously. A file shared through the wrong channel or with overly broad permissions can turn a routine collaboration into a data breach. The goal is not to avoid sharing entirely — it is to share through approved channels with appropriate controls.</p>

<h3>Internal Sharing Guidelines</h3>
<ul>
<li><strong>Use approved platforms:</strong> Share files through your organization\'s approved collaboration tools — company SharePoint, Google Workspace, or sanctioned project management systems. These platforms provide access controls, audit trails, and encryption that email attachments do not</li>
<li><strong>Set appropriate permissions:</strong> When sharing a document, grant the minimum necessary access. Use "view only" when recipients do not need to edit, and share with specific named individuals rather than broad groups or "anyone with the link"</li>
<li><strong>Review sharing regularly:</strong> Periodically audit who has access to your shared files and revoke permissions for people who no longer need them. Shared links accumulate over time and can provide access long after the original need has passed</li>
<li><strong>Label clearly:</strong> Include the classification level in the document header, footer, or filename so recipients understand how to handle it</li>
</ul>

<h3>External Sharing Guidelines</h3>
<p>Sharing data outside the organization introduces additional risk because you lose direct control over how the data is handled afterward. Before sharing Confidential or Restricted data externally, verify that a Non-Disclosure Agreement (NDA) or Data Processing Agreement (DPA) is in place with the receiving party. Use your organization\'s secure file transfer service rather than attaching sensitive files to email. If encrypted email is available, use it for any external communication containing personal data or business-sensitive information.</p>

<h3>What Never to Do</h3>
<ul>
<li>Never send Restricted data via unencrypted email, personal messaging apps, or SMS</li>
<li>Never upload Confidential or Restricted data to personal cloud storage accounts</li>
<li>Never share credentials alongside the data they protect — send access credentials through a separate channel</li>
<li>Never share more data than the recipient actually needs — extract only the relevant rows, columns, or sections</li>
</ul>

<p>If you are unsure whether a sharing method is appropriate for the data\'s classification level, pause and check with your security team. It is always better to ask first than to explain a data breach afterward.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Data Classification & Handling Quiz',
                    'instructions' => 'Answer all questions. You need 70% to pass.',
                    'questions' => [
                        [
                            'question' => 'A report contains mostly internal information but includes one column of employee Social Security numbers. How should it be classified?',
                            'type' => 'multiple_choice',
                            'explanation' => 'A document should be classified based on its most sensitive element. Social Security numbers are Restricted data, so the entire report is Restricted.',
                            'answers' => [
                                ['answer' => 'Internal, since most of the data is routine', 'is_correct' => false],
                                ['answer' => 'Confidential, as a compromise between the levels', 'is_correct' => false],
                                ['answer' => 'Restricted, based on the most sensitive data element it contains', 'is_correct' => true],
                                ['answer' => 'Public, since it is an internal report', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the principle of least privilege in the context of data access?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Least privilege means each person should have access only to the data they need for their specific role — no more, no less.',
                            'answers' => [
                                ['answer' => 'Everyone should have access to all data so they can do their jobs flexibly', 'is_correct' => false],
                                ['answer' => 'Only senior management should have access to any sensitive data', 'is_correct' => false],
                                ['answer' => 'Each person should have access only to the data they need for their specific role', 'is_correct' => true],
                                ['answer' => 'Data access should be rotated weekly among team members', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which of the following is an appropriate way to share Confidential data with an external partner?',
                            'type' => 'multiple_choice',
                            'explanation' => 'External sharing of Confidential data requires an NDA or DPA in place and should use the organization\'s secure file transfer service.',
                            'answers' => [
                                ['answer' => 'Attach it to a regular unencrypted email', 'is_correct' => false],
                                ['answer' => 'Upload it to your personal Dropbox and share the link', 'is_correct' => false],
                                ['answer' => 'Use the organization\'s secure file transfer service after confirming an NDA is in place', 'is_correct' => true],
                                ['answer' => 'Send it via a personal messaging app for speed', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should you do with printed documents containing Restricted information when they are no longer needed?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Restricted documents must be destroyed using a cross-cut shredder. Standard recycling or regular trash bins are not secure disposal methods.',
                            'answers' => [
                                ['answer' => 'Place them in the standard recycling bin', 'is_correct' => false],
                                ['answer' => 'Leave them on your desk for later disposal', 'is_correct' => false],
                                ['answer' => 'Destroy them using a cross-cut shredder', 'is_correct' => true],
                                ['answer' => 'Tear them in half and place in the trash', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'When sharing a document internally, what permission level should you default to?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Default to the minimum necessary access — use "view only" when recipients do not need to edit, and share with specific individuals rather than broad groups.',
                            'answers' => [
                                ['answer' => 'Full edit access for everyone in the organization', 'is_correct' => false],
                                ['answer' => 'The minimum necessary access — view only when editing is not needed, shared with specific individuals', 'is_correct' => true],
                                ['answer' => 'Anyone with the link can edit, for maximum convenience', 'is_correct' => false],
                                ['answer' => 'No access for anyone until they individually request it', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 30: Cloud Security Essentials ──
            [
                'title' => 'Cloud Security Essentials',
                'slug' => 'cloud-security-essentials',
                'description' => 'Understand the security considerations of cloud computing, including service models, shared responsibility, and best practices for securing cloud-hosted data and applications.',
                'objectives' => [
                    'Describe the major cloud service models and their associated security risks',
                    'Apply best practices for securing data stored in cloud environments',
                    'Explain the shared responsibility model and identify your organization\'s obligations',
                    'Recognize common cloud security misconfigurations and how to prevent them',
                ],
                'category' => 'Data Protection & Privacy',
                'difficulty' => 'intermediate',
                'duration_minutes' => 25,
                'passing_score' => 75,
                'sort_order' => 30,
                'lessons' => [
                    [
                        'title' => 'Cloud Service Models & Risks',
                        'slug' => 'cloud-service-models-risks',
                        'duration_minutes' => 8,
                        'content' => '<h3>Cloud Service Models & Risks</h3>
<p>Cloud computing has transformed how organizations store data, run applications, and deliver services. Instead of maintaining physical servers in on-premises data centers, organizations can rent computing resources from cloud providers like Amazon Web Services (AWS), Microsoft Azure, and Google Cloud Platform (GCP). While cloud computing offers significant benefits in scalability, cost, and flexibility, it also introduces security considerations that every employee should understand.</p>

<h3>The Three Service Models</h3>
<ul>
<li><strong>Infrastructure as a Service (IaaS):</strong> The provider supplies virtual machines, storage, and networking. Your organization manages the operating system, applications, and data. Examples include AWS EC2, Azure Virtual Machines, and Google Compute Engine. IaaS gives the most control but also the most security responsibility</li>
<li><strong>Platform as a Service (PaaS):</strong> The provider manages the infrastructure and operating system, while your organization manages the applications and data running on the platform. Examples include AWS Elastic Beanstalk, Azure App Service, and Google App Engine. PaaS reduces infrastructure management burden but still requires securing applications and data</li>
<li><strong>Software as a Service (SaaS):</strong> The provider manages everything — infrastructure, platform, and application. Your organization manages user access and data configuration. Examples include Microsoft 365, Salesforce, Slack, and Google Workspace. SaaS is the simplest to consume but still requires proper configuration and access management</li>
</ul>

<h3>Common Cloud Security Risks</h3>
<p>The most frequent cloud security incidents are not caused by sophisticated attacks on cloud infrastructure — they result from misconfiguration by the customer. Publicly exposed storage buckets containing sensitive data have been responsible for some of the largest data breaches in recent years. Overly permissive access policies, unencrypted data stores, and failure to enable logging are mistakes that organizations, not cloud providers, make. Other risks include shadow IT — employees adopting cloud services without IT approval — and data sovereignty issues when data is stored in regions with different legal requirements.</p>

<p>Understanding which cloud services your organization uses, where your data resides, and who has access to it is the foundation of cloud security awareness. If you are uncertain whether a cloud service is approved for use with company data, check with your IT department before uploading anything sensitive.</p>',
                    ],
                    [
                        'title' => 'Securing Cloud Storage',
                        'slug' => 'securing-cloud-storage',
                        'duration_minutes' => 9,
                        'content' => '<h3>Securing Cloud Storage</h3>
<p>Cloud storage services — from enterprise solutions like AWS S3 and Azure Blob Storage to collaboration tools like Google Drive and OneDrive — are where much of an organization\'s sensitive data lives. Securing cloud storage requires proper configuration, access management, and ongoing monitoring to prevent unauthorized access or accidental exposure.</p>

<h3>Access Control Best Practices</h3>
<ul>
<li><strong>Apply least-privilege access:</strong> Grant users and applications only the minimum permissions needed. Avoid using wildcard policies that grant broad access across all resources</li>
<li><strong>Use role-based access control (RBAC):</strong> Assign permissions based on job roles rather than individual users. When someone changes roles, their access automatically adjusts with the role assignment</li>
<li><strong>Disable public access by default:</strong> Cloud storage buckets and containers should be private by default. Public access should be explicitly enabled only for resources genuinely intended to be public, such as static website assets</li>
<li><strong>Require multi-factor authentication:</strong> All administrative and privileged access to cloud storage should require MFA, protecting against credential theft</li>
</ul>

<h3>Encryption and Data Protection</h3>
<p>Enable encryption at rest for all cloud storage. Most major cloud providers offer this as a default or easy-to-enable option. For highly sensitive data, consider using customer-managed encryption keys (CMEK) rather than provider-managed keys, giving your organization sole control over the encryption. Enable encryption in transit by ensuring all connections use TLS/HTTPS — never access cloud storage over unencrypted HTTP connections.</p>

<h3>Monitoring and Auditing</h3>
<p>Enable access logging on all cloud storage resources. These logs record who accessed what data, when, and from where. Without logging, you have no way to detect unauthorized access or investigate an incident. Set up alerts for unusual access patterns — large data downloads, access from unexpected geographic locations, or access attempts outside business hours. Regularly review access permissions and revoke any that are no longer needed. Many breaches persist for months because no one is reviewing who has access to what.</p>

<p>Cloud storage misconfigurations have been behind some of the most high-profile data breaches in recent years, exposing millions of customer records. A few minutes spent verifying your storage configuration can prevent months of incident response and reputational damage.</p>',
                    ],
                    [
                        'title' => 'Shared Responsibility Model',
                        'slug' => 'shared-responsibility-model',
                        'duration_minutes' => 8,
                        'content' => '<h3>Shared Responsibility Model</h3>
<p>The shared responsibility model is the foundational concept in cloud security. It defines which security tasks the cloud provider handles and which remain the customer\'s responsibility. Misunderstanding this division is one of the most common causes of cloud security failures — organizations assume the provider is handling something that is actually their own responsibility.</p>

<h3>What the Cloud Provider Manages</h3>
<p>Cloud providers are responsible for security "of" the cloud — the physical infrastructure, including data center buildings, hardware, networking equipment, cooling, and power. They manage the hypervisor layer that creates and isolates virtual machines, the global network backbone, and the foundational services. Major providers invest billions of dollars in physical security, redundancy, and infrastructure hardening that most organizations could never replicate on their own. They undergo rigorous third-party audits and maintain certifications like SOC 2, ISO 27001, and FedRAMP.</p>

<h3>What the Customer Manages</h3>
<p>Customers are responsible for security "in" the cloud — everything they build, configure, and store on the provider\'s infrastructure. This includes:</p>
<ul>
<li><strong>Identity and access management:</strong> Creating user accounts, assigning permissions, enforcing MFA, and managing who can access what</li>
<li><strong>Data protection:</strong> Classifying data, enabling encryption, configuring access controls on storage, and managing encryption keys</li>
<li><strong>Application security:</strong> Securing applications deployed on cloud infrastructure, patching software, and managing application-level vulnerabilities</li>
<li><strong>Network configuration:</strong> Setting up firewalls, security groups, network segmentation, and VPN connections</li>
<li><strong>Operating system patches:</strong> In IaaS environments, the customer is responsible for keeping operating systems updated and patched</li>
</ul>

<h3>The Responsibility Shifts by Service Model</h3>
<p>The division of responsibility shifts depending on the service model. With IaaS, the customer manages almost everything above the hardware. With PaaS, the provider takes on operating system and runtime management. With SaaS, the customer manages only user access and data configuration. However, one responsibility never transfers to the provider: your data. Regardless of the service model, the customer is always responsible for the security and classification of their own data, for controlling who has access to it, and for complying with applicable regulations.</p>

<p>Think of it this way: the cloud provider builds a secure building and provides locks, alarms, and cameras. But you are responsible for choosing strong lock combinations, setting the alarm, checking who has keys, and deciding what to store inside.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Cloud Security Essentials Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is the most common cause of cloud security incidents?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Most cloud security incidents result from customer misconfiguration — publicly exposed storage, overly permissive access, unencrypted data — not attacks on cloud infrastructure.',
                            'answers' => [
                                ['answer' => 'Sophisticated attacks on the cloud provider\'s infrastructure', 'is_correct' => false],
                                ['answer' => 'Customer misconfiguration such as publicly exposed storage or overly permissive access', 'is_correct' => true],
                                ['answer' => 'Physical break-ins at cloud data centers', 'is_correct' => false],
                                ['answer' => 'Outdated encryption algorithms used by cloud providers', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'In the shared responsibility model, who is always responsible for data security regardless of service model?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Regardless of whether you use IaaS, PaaS, or SaaS, the customer is always responsible for the security and classification of their own data.',
                            'answers' => [
                                ['answer' => 'The cloud provider handles all data security', 'is_correct' => false],
                                ['answer' => 'The customer is always responsible for their own data', 'is_correct' => true],
                                ['answer' => 'A third-party auditor manages data security', 'is_correct' => false],
                                ['answer' => 'Responsibility depends on which compliance framework applies', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which cloud service model gives the customer the MOST security responsibility?',
                            'type' => 'multiple_choice',
                            'explanation' => 'IaaS gives the customer the most control and therefore the most security responsibility, including OS patching, network configuration, and application security.',
                            'answers' => [
                                ['answer' => 'Software as a Service (SaaS)', 'is_correct' => false],
                                ['answer' => 'Platform as a Service (PaaS)', 'is_correct' => false],
                                ['answer' => 'Infrastructure as a Service (IaaS)', 'is_correct' => true],
                                ['answer' => 'All models have identical customer responsibility', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What should be the default access setting for cloud storage buckets?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Cloud storage should be private by default. Public access should be explicitly enabled only for resources genuinely intended to be public.',
                            'answers' => [
                                ['answer' => 'Public read access so team members can easily find files', 'is_correct' => false],
                                ['answer' => 'Private by default, with public access enabled only when explicitly needed', 'is_correct' => true],
                                ['answer' => 'Open to anyone within the same cloud region', 'is_correct' => false],
                                ['answer' => 'Read-write access for all authenticated cloud users', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why is enabling access logging on cloud storage important?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Access logs record who accessed what data, when, and from where, enabling detection of unauthorized access and supporting incident investigation.',
                            'answers' => [
                                ['answer' => 'It automatically blocks all unauthorized access attempts', 'is_correct' => false],
                                ['answer' => 'It reduces cloud storage costs by optimizing access patterns', 'is_correct' => false],
                                ['answer' => 'It records who accessed data, when, and from where, enabling detection and investigation of unauthorized access', 'is_correct' => true],
                                ['answer' => 'It is only required for compliance and has no practical security benefit', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 31: Ransomware Defense & Recovery ──
            [
                'title' => 'Ransomware Defense & Recovery',
                'slug' => 'ransomware-defense-recovery',
                'description' => 'Learn how ransomware attacks work, how to prevent them, and how to respond effectively if your organization is targeted.',
                'objectives' => [
                    'Explain how modern ransomware attacks operate, including double extortion tactics',
                    'Implement prevention strategies that reduce ransomware risk',
                    'Follow the correct response procedures during an active ransomware incident',
                    'Understand the role of backups, segmentation, and incident plans in recovery',
                ],
                'category' => 'Malware & Ransomware',
                'difficulty' => 'intermediate',
                'is_mandatory' => true,
                'duration_minutes' => 25,
                'passing_score' => 75,
                'sort_order' => 31,
                'lessons' => [
                    [
                        'title' => 'How Ransomware Works',
                        'slug' => 'how-ransomware-works',
                        'duration_minutes' => 8,
                        'content' => '<h3>How Ransomware Works</h3>
<p>Ransomware is malicious software that encrypts a victim\'s files, rendering them inaccessible, and demands payment — typically in cryptocurrency — in exchange for a decryption key. What began as simple screen-locking malware in the late 2000s has evolved into a multi-billion-dollar criminal industry with sophisticated operators, affiliate programs, and even customer support desks for victims negotiating payments.</p>

<h3>The Attack Lifecycle</h3>
<p>Modern ransomware attacks follow a methodical sequence. Initial access is typically gained through phishing emails with malicious attachments, exploitation of unpatched vulnerabilities in internet-facing systems (such as VPN gateways or remote desktop services), or compromised credentials purchased from other criminals. Once inside, attackers do not immediately deploy ransomware. Instead, they spend days or weeks performing reconnaissance — mapping the network, identifying critical systems, locating backups, and escalating their privileges to domain administrator level.</p>

<p>This dwell time is critical to the attacker\'s strategy. They identify and disable or delete backup systems first, ensuring the victim cannot simply restore from backups. They exfiltrate sensitive data for use in double extortion. Only after thorough preparation do they deploy the ransomware payload across as many systems as possible simultaneously, often during off-hours when IT staff are less likely to notice and respond quickly.</p>

<h3>Double and Triple Extortion</h3>
<ul>
<li><strong>Single extortion:</strong> The traditional model — encrypt files and demand payment for the decryption key</li>
<li><strong>Double extortion:</strong> Encrypt files and also exfiltrate sensitive data, threatening to publish it on leak sites if the ransom is not paid. This means even organizations with good backups face pressure to pay</li>
<li><strong>Triple extortion:</strong> In addition to encryption and data theft, attackers contact the victim\'s customers, partners, or patients directly, threatening to expose their personal data unless additional payments are made</li>
</ul>

<h3>Ransomware-as-a-Service</h3>
<p>The ransomware ecosystem now operates like a franchise business. Ransomware developers create the malware and infrastructure, then recruit affiliates who carry out the actual attacks in exchange for a percentage of ransom payments — typically 70-80% for the affiliate. This model has dramatically lowered the barrier to entry, allowing less technically skilled criminals to conduct devastating attacks using professional-grade tools and playbooks.</p>',
                    ],
                    [
                        'title' => 'Prevention Strategies',
                        'slug' => 'ransomware-prevention-strategies',
                        'duration_minutes' => 9,
                        'content' => '<h3>Prevention Strategies</h3>
<p>Preventing ransomware requires a layered defense approach — no single measure is sufficient on its own, but multiple overlapping controls create a resilient posture that makes successful attacks significantly harder and less damaging.</p>

<h3>Patch Management</h3>
<p>Unpatched vulnerabilities in internet-facing systems are one of the most common initial access vectors for ransomware. VPN appliances, remote desktop gateways, email servers, and web applications must be patched promptly when security updates are released. Attackers actively scan for and exploit known vulnerabilities, sometimes within hours of a patch being published. Prioritize patching critical and internet-facing systems, and establish a regular patching cadence for all other systems.</p>

<h3>Email and Endpoint Protection</h3>
<ul>
<li><strong>Email filtering:</strong> Deploy advanced email security that scans attachments in sandboxes, blocks known malicious senders, and strips or quarantines suspicious file types before they reach user inboxes</li>
<li><strong>Endpoint detection and response (EDR):</strong> Modern EDR tools monitor endpoint behavior in real time, detecting and blocking ransomware based on its actions — rapid file encryption, shadow copy deletion, or attempts to disable security tools — rather than relying solely on known malware signatures</li>
<li><strong>Application whitelisting:</strong> Allow only approved applications to run on endpoints. This prevents unknown executables, including ransomware, from executing even if they reach a user\'s system</li>
</ul>

<h3>Network Segmentation</h3>
<p>Divide your network into isolated segments so that if ransomware compromises one area, it cannot spread freely to others. Critical systems like backup infrastructure, domain controllers, and databases should be in separate, heavily restricted segments. Lateral movement between segments should require explicit firewall rules and authentication, not be freely available to any device on the network.</p>

<h3>Backup Strategy — The 3-2-1 Rule</h3>
<p>Maintain at least three copies of critical data, on two different types of media, with one copy stored offline or in an immutable storage system that cannot be altered or deleted by ransomware. Test backups regularly by performing actual restoration drills — a backup that has never been tested is not a backup you can rely on. Ensure backup systems use separate credentials from the production environment so that compromised domain admin credentials cannot be used to delete backups.</p>

<p>As an individual employee, you contribute to prevention by staying alert to phishing attempts, keeping your devices updated, reporting anything suspicious promptly, and following your organization\'s security policies consistently.</p>',
                    ],
                    [
                        'title' => 'Responding to a Ransomware Attack',
                        'slug' => 'responding-to-ransomware-attack',
                        'duration_minutes' => 8,
                        'content' => '<h3>Responding to a Ransomware Attack</h3>
<p>If you suspect your system or your organization is experiencing a ransomware attack, your immediate actions can significantly affect the outcome. Speed matters — every minute that passes gives the ransomware more time to encrypt files and spread to additional systems.</p>

<h3>Immediate Actions for Employees</h3>
<ul>
<li><strong>Do not panic, but act quickly:</strong> If you see a ransom note on your screen, files with strange extensions, or are unable to open your documents, do not attempt to fix it yourself</li>
<li><strong>Disconnect from the network immediately:</strong> Unplug your Ethernet cable and disable Wi-Fi. This is the single most important action you can take — it prevents the ransomware from spreading to shared drives and other systems on the network</li>
<li><strong>Do not turn off your computer:</strong> Leave it powered on. Forensic investigators may be able to recover encryption keys from memory, which are lost if the system is shut down</li>
<li><strong>Report it immediately:</strong> Contact your IT security team or help desk through your phone — not through email or messaging on the potentially compromised network. Provide your name, location, what you observed, and when you noticed it</li>
<li><strong>Do not pay the ransom:</strong> Individual employees should never engage with ransom demands. This decision involves legal, business, and law enforcement considerations that must be handled at the organizational level</li>
</ul>

<h3>Organizational Response Steps</h3>
<p>The incident response team will work to contain the attack by isolating affected network segments, identifying the ransomware variant, and assessing the scope of encryption and data exfiltration. They will engage law enforcement — the FBI and CISA in the US encourage reporting all ransomware incidents and may be able to provide decryption tools or intelligence. Legal counsel will assess regulatory notification obligations, as many jurisdictions require timely breach notification when personal data is compromised.</p>

<h3>Recovery</h3>
<p>Recovery begins with restoring systems from clean backups after ensuring the attacker\'s access has been fully eliminated. Rebuilding from backups before removing the attacker\'s foothold risks immediate re-encryption. The organization will reset all credentials — especially privileged accounts — patch the vulnerabilities that enabled initial access, and implement additional monitoring before bringing systems back online. Full recovery from a major ransomware attack typically takes weeks to months, underscoring why prevention and preparation are far preferable to response.</p>

<p>Every employee plays a role in ransomware defense. Your vigilance in reporting suspicious activity early can be the difference between an isolated incident and an organization-wide catastrophe.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Ransomware Defense & Recovery Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What is "double extortion" in the context of ransomware?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Double extortion combines file encryption with data theft — attackers threaten to publish stolen data on leak sites if the ransom is not paid.',
                            'answers' => [
                                ['answer' => 'Encrypting files twice with different algorithms', 'is_correct' => false],
                                ['answer' => 'Encrypting files and also stealing data, threatening to publish it if the ransom is not paid', 'is_correct' => true],
                                ['answer' => 'Demanding ransom from both the victim and their insurance company', 'is_correct' => false],
                                ['answer' => 'Attacking two organizations simultaneously', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What is the FIRST thing you should do if you see a ransom note on your screen?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Disconnecting from the network immediately is the most critical action — it prevents the ransomware from spreading to shared drives and other systems.',
                            'answers' => [
                                ['answer' => 'Turn off your computer immediately to stop the encryption', 'is_correct' => false],
                                ['answer' => 'Try to delete the ransom note and scan for viruses', 'is_correct' => false],
                                ['answer' => 'Disconnect from the network by unplugging Ethernet and disabling Wi-Fi', 'is_correct' => true],
                                ['answer' => 'Read the ransom note carefully and follow its instructions', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What does the 3-2-1 backup rule recommend?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The 3-2-1 rule: three copies of data, on two different media types, with one copy offline or immutable to protect against ransomware.',
                            'answers' => [
                                ['answer' => 'Back up three times a day, two times a week, and once a month', 'is_correct' => false],
                                ['answer' => 'Three copies of data, on two different media types, with one copy offline or immutable', 'is_correct' => true],
                                ['answer' => 'Use three different cloud providers, two data centers, and one local server', 'is_correct' => false],
                                ['answer' => 'Retain backups for three years, two months, and one week', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why do modern ransomware attackers spend days or weeks inside a network before deploying ransomware?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Attackers use dwell time to map the network, identify and disable backups, exfiltrate data, and escalate privileges to maximize damage.',
                            'answers' => [
                                ['answer' => 'They are waiting for the optimal time when cryptocurrency prices are highest', 'is_correct' => false],
                                ['answer' => 'The ransomware takes that long to install on each computer', 'is_correct' => false],
                                ['answer' => 'To map the network, disable backups, steal data, and escalate privileges for maximum impact', 'is_correct' => true],
                                ['answer' => 'Legal requirements force them to give notice before attacking', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Why should you NOT turn off your computer during a ransomware attack?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Forensic investigators may be able to recover encryption keys from the computer\'s memory (RAM), which are lost when the system is powered off.',
                            'answers' => [
                                ['answer' => 'The ransom amount increases if you restart', 'is_correct' => false],
                                ['answer' => 'Forensic investigators may recover encryption keys from memory, which are lost on shutdown', 'is_correct' => true],
                                ['answer' => 'Turning off the computer deletes all your files permanently', 'is_correct' => false],
                                ['answer' => 'The ransomware will spread faster when the computer restarts', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],

            // ── Module 32: Advanced Malware Threats ──
            [
                'title' => 'Advanced Malware Threats',
                'slug' => 'advanced-malware-threats',
                'description' => 'Explore sophisticated malware techniques including fileless attacks, supply chain compromises, and persistent threats that evade traditional security tools.',
                'objectives' => [
                    'Explain how fileless malware and living-off-the-land techniques evade detection',
                    'Describe how supply chain attacks compromise trusted software and updates',
                    'Understand rootkits and other persistence mechanisms used by advanced threats',
                    'Apply defensive strategies appropriate for advanced malware threats',
                ],
                'category' => 'Malware & Ransomware',
                'difficulty' => 'advanced',
                'duration_minutes' => 30,
                'passing_score' => 75,
                'sort_order' => 32,
                'lessons' => [
                    [
                        'title' => 'Fileless Malware & Living-off-the-Land',
                        'slug' => 'fileless-malware-living-off-the-land',
                        'duration_minutes' => 10,
                        'content' => '<h3>Fileless Malware & Living-off-the-Land</h3>
<p>Traditional malware operates by writing malicious executable files to disk, which antivirus software can scan, detect, and quarantine. Fileless malware takes a fundamentally different approach — it operates entirely in memory, leveraging legitimate system tools and processes to carry out malicious actions without ever writing a traditional malware file to the hard drive. This makes it significantly harder to detect using conventional file-based scanning.</p>

<h3>How Fileless Attacks Work</h3>
<p>A typical fileless attack begins with an initial hook — often a phishing email containing a malicious document or link. When the victim opens the document or clicks the link, a script executes that does not drop a traditional executable. Instead, it invokes built-in system tools like PowerShell, Windows Management Instrumentation (WMI), or the Windows Script Host to download and execute malicious code directly in memory. The malware may also inject itself into the memory space of a legitimate running process, hiding inside a trusted application.</p>

<h3>Living-off-the-Land Binaries (LOLBins)</h3>
<p>Living-off-the-Land refers to the technique of using legitimate, pre-installed system tools to perform malicious actions. Attackers prefer this approach because security tools are unlikely to flag the use of standard system utilities. Common LOLBins include:</p>
<ul>
<li><strong>PowerShell:</strong> A powerful scripting environment built into Windows that can download files, execute code in memory, interact with APIs, and manage systems remotely. Attackers use encoded PowerShell commands to obscure their activities</li>
<li><strong>WMI (Windows Management Instrumentation):</strong> A management framework that attackers use for reconnaissance, lateral movement, and persistence by creating event subscriptions that execute code when triggered</li>
<li><strong>Certutil:</strong> A legitimate certificate management tool that attackers repurpose to download malicious payloads because it can fetch files from URLs and decode Base64 content</li>
<li><strong>Mshta and Regsvr32:</strong> Built-in Windows tools that can execute scripts and load DLLs, allowing attackers to bypass application whitelisting controls</li>
</ul>

<h3>Why Fileless Malware Is Dangerous</h3>
<p>Because fileless malware uses legitimate tools and operates in memory, it leaves minimal forensic artifacts. Traditional antivirus that scans files on disk may find nothing to detect. The malware disappears from memory when the system restarts, making forensic analysis more difficult. However, this also means fileless malware must re-establish itself after a reboot, often through persistence mechanisms stored in the Windows Registry or scheduled tasks — subtle entries that do not look obviously malicious.</p>

<p>Detection requires behavioral analysis rather than signature matching. Modern endpoint detection and response (EDR) solutions monitor process behavior, command-line arguments, and parent-child process relationships to identify suspicious activity even when no malicious file exists on disk.</p>',
                    ],
                    [
                        'title' => 'Supply Chain Attacks',
                        'slug' => 'supply-chain-attacks',
                        'duration_minutes' => 10,
                        'content' => '<h3>Supply Chain Attacks</h3>
<p>A supply chain attack compromises an organization not by attacking it directly but by targeting a trusted third party — a software vendor, a managed service provider, or an open-source library — that the organization depends on. Because the malicious code arrives through a trusted channel, it bypasses many security controls that would block a direct attack. Supply chain attacks are among the most dangerous and difficult-to-detect threats in modern cybersecurity.</p>

<h3>The SolarWinds Attack</h3>
<p>The 2020 SolarWinds attack is the most prominent example of a software supply chain compromise. Attackers infiltrated SolarWinds\' build environment and inserted a backdoor into the Orion network management platform. When SolarWinds distributed a routine software update, approximately 18,000 organizations — including Fortune 500 companies and US government agencies — installed the compromised update through their normal patch management processes. The attackers then selectively activated the backdoor in high-value targets to conduct espionage. The attack went undetected for months because the malicious code was delivered through a trusted, digitally signed software update.</p>

<h3>Types of Supply Chain Attacks</h3>
<ul>
<li><strong>Software update compromise:</strong> Attackers inject malicious code into legitimate software updates, as in the SolarWinds case. Victims receive the malware through their normal update processes</li>
<li><strong>Open-source dependency poisoning:</strong> Attackers publish malicious packages to public repositories (npm, PyPI, RubyGems) with names similar to popular legitimate packages (typosquatting), or compromise maintainer accounts to push malicious updates to widely-used libraries</li>
<li><strong>Managed service provider (MSP) compromise:</strong> Attackers target MSPs that have privileged access to multiple client networks. Compromising one MSP can provide access to hundreds of downstream organizations</li>
<li><strong>Hardware supply chain:</strong> Malicious components or firmware are inserted during the manufacturing or distribution process. While rarer, hardware supply chain attacks are extremely difficult to detect and remediate</li>
</ul>

<h3>Defending Against Supply Chain Attacks</h3>
<p>Supply chain attacks are difficult to prevent entirely because they exploit trust relationships that organizations need to function. However, several strategies reduce risk. Maintain a software bill of materials (SBOM) that inventories all software components and dependencies in your environment. Monitor vendor security practices and require security certifications from critical suppliers. Implement the principle of least privilege for all third-party software and services — vendor tools should have access only to what they need. Use network segmentation to limit the blast radius if a trusted tool is compromised, and deploy behavioral monitoring that can detect unusual activity even from trusted software.</p>

<p>As an employee, be aware that not all threats come from suspicious emails or unknown websites. Malicious code can arrive through the very tools and updates you trust most, which is why defense-in-depth and monitoring are essential.</p>',
                    ],
                    [
                        'title' => 'Rootkits & Persistent Threats',
                        'slug' => 'rootkits-persistent-threats',
                        'duration_minutes' => 10,
                        'content' => '<h3>Rootkits & Persistent Threats</h3>
<p>A rootkit is a type of malware specifically designed to hide its presence — and the presence of other malware — from users and security tools. The name comes from "root," the highest privilege level on Unix systems. Rootkits operate at deep levels of the operating system, modifying system calls, hiding files and processes, and intercepting security scans to report clean results even while malicious code runs underneath. They represent some of the most technically sophisticated malware in existence.</p>

<h3>Types of Rootkits</h3>
<ul>
<li><strong>User-mode rootkits:</strong> Operate at the application level, intercepting API calls to hide malicious processes, files, and registry entries from task managers and file explorers. They are the easiest to develop but also the easiest to detect with kernel-level security tools</li>
<li><strong>Kernel-mode rootkits:</strong> Operate within the operating system kernel itself, modifying core system functions. Because they run at the same privilege level as the OS, they can hide virtually anything from security software. Detection requires specialized tools that examine raw disk and memory content</li>
<li><strong>Bootkits:</strong> Infect the master boot record (MBR) or volume boot record (VBR), loading before the operating system itself starts. This allows them to subvert the entire OS from the moment the computer turns on. Modern UEFI Secure Boot was designed partly to counter this threat</li>
<li><strong>Firmware rootkits:</strong> Infect device firmware such as BIOS/UEFI, network card firmware, or hard drive controller firmware. These survive operating system reinstallation and even hard drive replacement, making them extremely persistent and difficult to eradicate</li>
</ul>

<h3>Persistence Mechanisms</h3>
<p>Advanced threats use various techniques to survive system reboots and remain active on compromised systems for extended periods. Registry run keys and startup folder entries ensure malware launches when the user logs in. Scheduled tasks and Windows services can execute malware at defined intervals. DLL search order hijacking places malicious libraries where legitimate applications will load them. WMI event subscriptions trigger malicious code when specific system events occur. These mechanisms are designed to be subtle — they use innocuous-sounding names and blend in with legitimate system configuration.</p>

<h3>Detection and Defense</h3>
<p>Detecting rootkits and persistent threats requires looking beneath the surface. Integrity monitoring tools compare current system files and configurations against known-good baselines to detect unauthorized modifications. Memory forensics examines the actual contents of RAM rather than relying on what the operating system reports. Behavioral monitoring watches for suspicious patterns — a legitimate system process making unusual network connections, an application loading unexpected DLLs, or a service account authenticating at unusual hours.</p>

<p>For employees, the most important defense against advanced persistent threats is maintaining good security hygiene: keeping systems updated, using approved software, reporting unusual system behavior (unexpected slowdowns, strange pop-ups, applications behaving differently), and never disabling security tools even if they seem to interfere with normal work. These everyday practices make the attacker\'s job significantly harder at every stage.</p>',
                    ],
                ],
                'quiz' => [
                    'title' => 'Advanced Malware Threats Quiz',
                    'instructions' => 'Answer all questions. You need 75% to pass.',
                    'questions' => [
                        [
                            'question' => 'What makes fileless malware difficult for traditional antivirus to detect?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Fileless malware operates entirely in memory using legitimate system tools, so there are no malicious files on disk for traditional file-based antivirus to scan.',
                            'answers' => [
                                ['answer' => 'It encrypts itself with military-grade encryption', 'is_correct' => false],
                                ['answer' => 'It operates in memory using legitimate system tools, leaving no malicious files on disk to scan', 'is_correct' => true],
                                ['answer' => 'It disables the antivirus software before installing', 'is_correct' => false],
                                ['answer' => 'It is too small for antivirus signatures to match', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'What was significant about the SolarWinds supply chain attack?',
                            'type' => 'multiple_choice',
                            'explanation' => 'The SolarWinds attack distributed malicious code through a legitimate, digitally signed software update, reaching approximately 18,000 organizations through trusted channels.',
                            'answers' => [
                                ['answer' => 'It targeted personal home computers rather than businesses', 'is_correct' => false],
                                ['answer' => 'Malicious code was distributed through a trusted, digitally signed software update to approximately 18,000 organizations', 'is_correct' => true],
                                ['answer' => 'It was the first ransomware attack in history', 'is_correct' => false],
                                ['answer' => 'It only affected organizations that had not updated their software', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'Which type of rootkit is the most persistent and can survive operating system reinstallation?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Firmware rootkits infect device firmware (BIOS/UEFI) and survive OS reinstallation and even hard drive replacement.',
                            'answers' => [
                                ['answer' => 'User-mode rootkits', 'is_correct' => false],
                                ['answer' => 'Kernel-mode rootkits', 'is_correct' => false],
                                ['answer' => 'Bootkits', 'is_correct' => false],
                                ['answer' => 'Firmware rootkits', 'is_correct' => true],
                            ],
                        ],
                        [
                            'question' => 'What is "living-off-the-land" in the context of cyber attacks?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Living-off-the-land means using legitimate, pre-installed system tools (PowerShell, WMI, certutil) to perform malicious actions, avoiding detection.',
                            'answers' => [
                                ['answer' => 'Attackers using only the victim\'s internet bandwidth for their operations', 'is_correct' => false],
                                ['answer' => 'Using legitimate, pre-installed system tools to perform malicious actions instead of custom malware', 'is_correct' => true],
                                ['answer' => 'Operating exclusively from the victim\'s physical location', 'is_correct' => false],
                                ['answer' => 'A type of social engineering that targets rural organizations', 'is_correct' => false],
                            ],
                        ],
                        [
                            'question' => 'How can organizations reduce the risk of supply chain attacks?',
                            'type' => 'multiple_choice',
                            'explanation' => 'Maintaining an SBOM, monitoring vendor security, applying least privilege to third-party software, and using behavioral monitoring all help reduce supply chain risk.',
                            'answers' => [
                                ['answer' => 'Never install software updates from any vendor', 'is_correct' => false],
                                ['answer' => 'Only use software developed entirely in-house', 'is_correct' => false],
                                ['answer' => 'Maintain a software inventory, apply least privilege to vendor tools, segment the network, and monitor behavior', 'is_correct' => true],
                                ['answer' => 'Require all vendors to share their complete source code', 'is_correct' => false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
