<?php

namespace Database\Seeders;

use App\Models\ProjectList;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $list1 = ProjectList::where('name', 'Website Redesign Project')->first();
        $list2 = ProjectList::where('name', 'Mobile App MVP')->first();
        $list3 = ProjectList::where('name', 'Brand & Design System')->first();

        if ($list1) {
            Task::create([
                'list_id' => $list1->id,
                'title' => 'Wireframing Landing Page',
                'description' => 'Create low fidelity wireframes for desktop & mobile view.',
                'priority' => 'high',
                'deadline' => Carbon::today()->addDays(2),
                'status' => 'completed',
            ]);

            Task::create([
                'list_id' => $list1->id,
                'title' => 'Setup Tailwind & Blade Components',
                'description' => 'Install Tailwind CSS v4 and configure component layout.',
                'priority' => 'medium',
                'deadline' => Carbon::today()->addDays(1),
                'status' => 'in_progress',
            ]);

            Task::create([
                'list_id' => $list1->id,
                'title' => 'Integrate Contact Form API',
                'description' => 'Endpoint to send email notifications via Mailgun.',
                'priority' => 'low',
                'deadline' => Carbon::today()->addDays(5),
                'status' => 'pending',
            ]);

            Task::create([
                'list_id' => $list1->id,
                'title' => 'SEO Audit & Meta Tags',
                'description' => 'Ensure Lighthouse SEO score is above 90.',
                'priority' => 'medium',
                'deadline' => Carbon::today()->addDays(7),
                'status' => 'pending',
            ]);
        }

        if ($list2) {
            Task::create([
                'list_id' => $list2->id,
                'title' => 'Setup Auth Screen Flow',
                'description' => 'Login, register, and password reset screens.',
                'priority' => 'high',
                'deadline' => Carbon::today()->addDays(3),
                'status' => 'in_progress',
            ]);

            Task::create([
                'list_id' => $list2->id,
                'title' => 'Push Notification Service',
                'description' => 'Firebase Cloud Messaging integration.',
                'priority' => 'medium',
                'deadline' => Carbon::today()->addDays(10),
                'status' => 'pending',
            ]);
        }

        if ($list3) {
            Task::create([
                'list_id' => $list3->id,
                'title' => 'Color Palette & Accessibility Contrast Check',
                'description' => 'Check WCAG AA compliance for primary buttons.',
                'priority' => 'high',
                'deadline' => Carbon::today()->addDays(1),
                'status' => 'completed',
            ]);
        }
    }
}
