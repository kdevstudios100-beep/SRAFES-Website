<?php

namespace Database\Seeders;

use App\Models\JobPost;
use Illuminate\Database\Seeder;

class JobPostSeeder extends Seeder
{
    public function run(): void
    {
        JobPost::create([
            'title' => 'Secondary School Teacher',
            'content' => <<<'HTML'
            <h2>About the Position</h2>
            <p>We are seeking a dedicated and passionate Secondary School Teacher to join our educational team. The ideal candidate will have a strong commitment to student development and academic excellence.</p>

            <h2>Requirements</h2>
            <ul>
                <li>Bachelor's degree in Education or relevant subject area</li>
                <li>Valid teaching certification/license</li>
                <li>Minimum 2 years of teaching experience</li>
                <li>Strong classroom management skills</li>
                <li>Excellent communication and interpersonal abilities</li>
            </ul>

            <h2>Qualifications</h2>
            <ul>
                <li>Master's degree in Education (preferred)</li>
                <li>Experience with modern teaching methodologies</li>
                <li>Proficiency in educational technology and digital learning tools</li>
                <li>Ability to work collaboratively with staff and parents</li>
                <li>Commitment to continuous professional development</li>
            </ul>

            <h2>Responsibilities</h2>
            <ol>
                <li>Plan and deliver engaging lessons aligned with curriculum standards</li>
                <li>Assess and monitor student progress through regular evaluations</li>
                <li>Maintain accurate records of student attendance and performance</li>
                <li>Participate in school activities and professional development sessions</li>
                <li>Communicate regularly with parents regarding student progress</li>
            </ol>

            <h2>Location</h2>
            <p><strong>Shaheed Rajib Anmol Free Education System</strong><br>Main Campus<br>Open to candidates from surrounding areas</p>
            HTML,
            'salary_range' => 'Competitive salary based on experience',
            'application_deadline' => now()->addMonth(),
            'is_active' => true,
        ]);

        JobPost::create([
            'title' => 'Administrative Assistant',
            'content' => <<<'HTML'
            <h2>About the Position</h2>
            <p>We are looking for a highly organized and detail-oriented Administrative Assistant to support our school operations. This role is essential in ensuring smooth daily operations and excellent service to students, staff, and parents.</p>

            <h2>Requirements</h2>
            <ul>
                <li>High school diploma or equivalent (Bachelor's degree preferred)</li>
                <li>Minimum 1-2 years of administrative experience</li>
                <li>Proficiency in Microsoft Office Suite and Google Workspace</li>
                <li>Strong written and verbal communication skills</li>
                <li>Ability to handle multiple tasks and prioritize effectively</li>
            </ul>

            <h2>Qualifications</h2>
            <ul>
                <li>Experience in educational administration (preferred)</li>
                <li>Knowledge of school management systems</li>
                <li>Excellent organizational and time management skills</li>
                <li>Professional demeanor and appearance</li>
                <li>Ability to maintain confidentiality</li>
            </ul>

            <h2>Key Responsibilities</h2>
            <ol>
                <li>Manage front desk operations and greet visitors</li>
                <li>Handle phone calls, emails, and correspondence</li>
                <li>Maintain student and staff records</li>
                <li>Coordinate meetings and school events</li>
                <li>Assist with document preparation and filing</li>
                <li>Support various administrative tasks as needed</li>
            </ol>

            <h2>Location</h2>
            <p><strong>Shaheed Rajib Anmol Free Education System</strong><br>Main Campus<br>Full-time position, Monday to Friday</p>

            <h2>Benefits</h2>
            <ul>
                <li>Professional development opportunities</li>
                <li>Supportive work environment</li>
                <li>Annual leave and benefits</li>
            </ul>
            HTML,
            'salary_range' => '$25,000 - $35,000',
            'application_deadline' => now()->addMonths(2),
            'is_active' => true,
        ]);
    }
}
