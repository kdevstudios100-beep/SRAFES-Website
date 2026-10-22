<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\Announcement;
use App\Models\Employee;
use App\Models\SiteSetting;
use App\Models\Student;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        SiteSetting::create(['settings' => [
            'name' => 'Shaheed Rajib Anmol Free Education System',
            'address' => 'Address goes here.',
            'email' => 'info@example.com',
            'phone' => '03009228297',
            'whatsapp' => '03009228297',
            'instagram_url' => 'https://instagram.com',
            'facebook_url' => 'https://fb.com',
            'x_url' => 'https://x.com',
            'google_maps_embed_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3571.5892795832815!2d67.79180677561992!3d26.46896507901934!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x394aeb168c814197%3A0x1cfb07131d7c8bd!2sShaheed%20Rajib%20Anmol%20Free%20Education%20System!5e0!3m2!1sen!2s!4v1730646668224!5m2!1sen!2s',
            'home_page_video_url' => 'https://www.youtube.com/embed/oRcNS5CCbnc',
        ]]);

        Employee::factory(9)->create();

        collect(['Class One', 'Class Two'])->each(function ($class) {
            $academicClass = AcademicClass::create(['name' => $class]);

            Student::factory(10)->for($academicClass)->create();
        });

        Announcement::create([
            'title' => 'Special announcement',
            'description' => 'Hey we are announcing an special announcement here',
            'is_recurring' => false,
            'start_date' => now(),
            'end_date' => now(),
        ]);

        Announcement::create([
            'title' => 'Quaiz Day',
            'description' => 'We are celeberating Quaiz Day today 🎉',
            'is_recurring' => true,
            'start_date' => '2022-12-25',
            'end_date' => '2022-12-25',
        ]);

        Announcement::create([
            'title' => 'Test announcemtn',
            'description' => 'This is an example announcement which is going to be shown for next 7 days.',
            'is_recurring' => false,
            'content' => <<<'HTML'
            <h2>Heading-1&nbsp;</h2><p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Veniam magnam architecto accusantium accusamus aliquid quibusdam maiores. Explicabo necessitatibus itaque cupiditate magni exercitationem soluta velit, praesentium a dolorem sunt minima eaque! Lorem ipsum dolor sit amet consectetur adipisicing elit. Veniam magnam architecto accusantium accusamus aliquid quibusdam maiores. Explicabo necessitatibus itaque cupiditate magni exercitationem soluta velit, praesentium a dolorem sunt minima eaque!&nbsp;</p><p><a href="https://google.com">Link Example</a></p><h3>Heading-1.1&nbsp;</h3><p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Veniam magnam architecto accusantium accusamus aliquid quibusdam maiores. Explicabo necessitatibus itaque cupiditate magni exercitationem soluta velit, praesentium a dolorem sunt minima eaque! Lorem ipsum dolor sit amet consectetur adipisicing elit.&nbsp;</p><h3>An example for render lists&nbsp;</h3><p><strong>Unordered list</strong></p><ul><li>List item - 1</li><li>List item - 2</li><li>List item - 3</li></ul><p><strong>Order LIst<br></strong><br></p><ol><li>List item -1</li><li>List item 2</li><li>List item 3</li></ol>
            HTML,
            'start_date' => now(),
            'end_date' => now()->addWeek(),
        ]);

        $this->call(JobPostSeeder::class);
    }
}
