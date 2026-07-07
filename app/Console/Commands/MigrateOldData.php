<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateOldData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:migrate-old-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from legacy tables to new Laravel structures';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting legacy data migration...');

        // 1. Migrate Users
        $this->migrateUsers();

        // 2. Migrate Schools
        $this->migrateSchools();

        // 3. Migrate Courses
        $this->migrateCourses();

        // 4. Migrate Banners
        $this->migrateBanners();

        $this->info('Migration completed successfully!');
        return Command::SUCCESS;
    }

    private function migrateUsers()
    {
        $this->info('Migrating users...');
        $legacyUsers = DB::table('legacy_users')->get();
        $count = 0;

        foreach ($legacyUsers as $user) {
            $email = trim($user->email ?: '');
            if ($email === '') {
                continue;
            }

            // Map role
            $role = ($user->role === 'admin') ? 'admin' : 'viewer';

            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    // Preserve the user's original ID if not already taken
                    'id' => $user->id,
                    'name' => $user->username ?: trim($user->fname . ' ' . $user->lname),
                    'password' => $user->password_hash ?: '!external_api_login!',
                    'role' => $role,
                    'logo' => null,
                    'email_verified_at' => now(),
                    'created_at' => $user->created_at ?: now(),
                    'updated_at' => $user->last_login_at ?: now(),
                ]
            );
            $count++;
        }
        $this->info("Migrated $count users.");
    }

    private function migrateSchools()
    {
        $this->info('Migrating schools...');
        $legacySchools = DB::table('system_school')->get();
        $count = 0;

        foreach ($legacySchools as $school) {
            // Build a readable address from legacy fields
            $addressParts = [];
            if (!empty($school->muti)) {
                $addressParts[] = 'หมู่ ' . trim($school->muti);
            }
            if (!empty($school->road)) {
                $addressParts[] = 'ถนน' . trim($school->road);
            }
            if (!empty($school->muban)) {
                $addressParts[] = 'หมู่บ้าน' . trim($school->muban);
            }
            if (!empty($school->tambon)) {
                $addressParts[] = 'ต.' . trim($school->tambon);
            }
            if (!empty($school->amper)) {
                $addressParts[] = 'อ.' . trim($school->amper);
            }
            if (!empty($school->province)) {
                $addressParts[] = 'จ.' . trim($school->province);
            }
            if (!empty($school->postcode)) {
                $addressParts[] = trim($school->postcode);
            }
            $address = implode(' ', $addressParts);

            $groupName = null;
            if (!empty($school->schoolgroup)) {
                $group = DB::table('system_group')->where('code', $school->schoolgroup)->first();
                if ($group) {
                    $groupName = $group->name;
                }
            }

            DB::table('network_schools')->updateOrInsert(
                ['id' => $school->id],
                [
                    'name' => $school->schoolname ?: $school->schoolname_eng,
                    'logo' => null,
                    'district' => $school->amper ?: 'ชุมพร',
                    'school_group' => $groupName,
                    'address' => $address ?: null,
                    'website' => $school->website ?: null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            $count++;
        }
        $this->info("Migrated $count schools.");
    }

    private function migrateCourses()
    {
        $this->info('Migrating courses...');
        $legacyCourses = DB::table('lms_courses')->get();
        $count = 0;

        foreach ($legacyCourses as $course) {
            // Strip HTML tags for objectives or store as text
            $objectives = strip_tags($course->description);

            // Clean cover image path: e.g. /1/uploads/lms/covers/... -> uploads/lms/covers/...
            $coverImage = $course->cover_url;
            if (str_starts_with($coverImage, '/1/')) {
                $coverImage = substr($coverImage, 3);
            }

            // Set academic year in BE format, default 2569 (for 2026)
            $year = '2569';
            if ($course->created_at) {
                $year = (int)date('Y', strtotime($course->created_at)) + 543;
            }

            DB::table('courses')->updateOrInsert(
                ['id' => $course->id],
                [
                    'title' => $course->title,
                    'cover_image' => $coverImage ?: null,
                    'objectives' => $objectives ?: null,
                    'hours' => '20', // Default hours
                    'academic_year' => $year,
                    'registration_link' => null,
                    'target_group' => 'ครูและบุคลากรทางการศึกษา',
                    'location' => 'ออนไลน์ (LMS)',
                    'status' => ($course->status === 'published') ? 'published' : 'upcoming',
                    'sort_order' => 0,
                    'duration_text' => '20 ชั่วโมง',
                    'created_at' => $course->created_at ?: now(),
                    'updated_at' => $course->updated_at ?: now(),
                ]
            );
            $count++;
        }
        $this->info("Migrated $count courses.");
    }

    private function migrateBanners()
    {
        $this->info('Migrating banners to slides...');
        $legacyBanners = DB::table('banners')->get();
        $count = 0;

        foreach ($legacyBanners as $banner) {
            DB::table('slides')->updateOrInsert(
                ['id' => $banner->id],
                [
                    'badge' => 'ประชาสัมพันธ์',
                    'title' => $banner->title ?: 'ศูนย์พัฒนาครูและบุคลากรทางการศึกษา',
                    'highlight' => null,
                    'slogan' => null,
                    'image' => $banner->image_path,
                    'link' => $banner->link_url ?: '#',
                    'btn_text' => 'ดูรายละเอียด',
                    'sort_order' => $banner->sort_order ?: 0,
                    'created_at' => $banner->created_at ?: now(),
                    'updated_at' => now(),
                ]
            );
            $count++;
        }
        $this->info("Migrated $count banners.");
    }
}
