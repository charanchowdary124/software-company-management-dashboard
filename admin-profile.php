<?php
// admin-profile.php
require_once __DIR__ . '/includes/header.php';

// Profile default values
$defaultAdmin = [
    'name' => 'Charan Chowdary',
    'email' => 'charan.chowdary@archinexus.com',
    'phone' => '+91-9876500001',
    'role' => 'System Administrator',
    'department' => 'IT & Administration',
    'location' => 'Hyderabad, India',
    'joinDate' => '2020-03-15',
    'status' => 'online',
    'avatarUrl' => 'https://images.pexels.com/photos/220453/pexels-photo-220453.jpeg',
    'bio' => 'Experienced system administrator managing the ArchiNexus platform infrastructure, user access, and operational workflows for architecture and design firms.'
];

// Initialize session values if not present
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = $defaultAdmin['name'];
    $_SESSION['admin_email'] = $defaultAdmin['email'];
    $_SESSION['admin_phone'] = $defaultAdmin['phone'];
    $_SESSION['admin_location'] = $defaultAdmin['location'];
    $_SESSION['admin_bio'] = $defaultAdmin['bio'];
}

// Initialize notification preferences in session if not present
$defaultNotifs = [
    ['key' => 'email', 'label' => 'Email Notifications', 'enabled' => true, 'desc' => 'Receive system alerts via email'],
    ['key' => 'push', 'label' => 'Push Notifications', 'enabled' => true, 'desc' => 'Browser push notifications for urgent alerts'],
    ['key' => 'weekly', 'label' => 'Weekly Digest', 'enabled' => false, 'desc' => 'Summary of platform activity weekly'],
    ['key' => 'security', 'label' => 'Security Alerts', 'enabled' => true, 'desc' => 'Immediate alerts for security events'],
    ['key' => 'teams', 'label' => 'Team Updates', 'enabled' => true, 'desc' => 'Notifications for team changes'],
    ['key' => 'reminders', 'label' => 'Report Reminders', 'enabled' => false, 'desc' => 'Reminders for pending report submissions'],
];
if (!isset($_SESSION['notifications'])) {
    $_SESSION['notifications'] = $defaultNotifs;
}

// Handle Form Submission POST
$isEditing = isset($_GET['edit']) && $_GET['edit'] === 'true';
$successMessage = false;
$errorMessage = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'save_profile') {
        // CSRF Token Validation
        $submittedToken = $_POST['csrf_token'] ?? '';
        if (!verify_csrf_token($submittedToken)) {
            $errorMessage = 'Security validation failed (invalid CSRF token). Please try again.';
        } else {
            // Input Validation & Sanitization
            $name = trim(strip_tags($_POST['name'] ?? ''));
            if (!empty($name) && mb_strlen($name) <= 100) {
                $_SESSION['admin_name'] = $name;
            }

            $email = trim($_POST['email'] ?? '');
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && mb_strlen($email) <= 120) {
                $_SESSION['admin_email'] = $email;
            }

            $phone = trim($_POST['phone'] ?? '');
            if (!empty($phone) && mb_strlen($phone) <= 30 && preg_match('/^[\+0-9\-\(\)\s]+$/', $phone)) {
                $_SESSION['admin_phone'] = $phone;
            }

            $location = trim(strip_tags($_POST['location'] ?? ''));
            if (!empty($location) && mb_strlen($location) <= 100) {
                $_SESSION['admin_location'] = $location;
            }

            $bio = trim(strip_tags($_POST['bio'] ?? ''));
            if (!empty($bio) && mb_strlen($bio) <= 500) {
                $_SESSION['admin_bio'] = $bio;
            }

            $successMessage = 'Profile updated successfully!';
            // Redirect to view mode
            header('Location: admin-profile.php?success=1');
            exit;
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == '1') {
    $successMessage = 'Profile updated successfully!';
}

// Handle notification toggle request with CSRF verification
if (isset($_GET['toggle_notif'])) {
    $token = $_GET['csrf'] ?? '';
    if (verify_csrf_token($token)) {
        $key = $_GET['toggle_notif'];
        foreach ($_SESSION['notifications'] as &$notif) {
            if ($notif['key'] === $key) {
                $notif['enabled'] = !$notif['enabled'];
                break;
            }
        }
        unset($notif);
    }
    header('Location: admin-profile.php');
    exit;
}

$activityTimeline = [
    [ 'action' => 'Updated employee permissions', 'time' => '2 hours ago', 'type' => 'permission', 'icon' => 'shield' ],
    [ 'action' => 'Deployed system update v3.2.1', 'time' => '5 hours ago', 'type' => 'deploy', 'icon' => 'monitor' ],
    [ 'action' => 'Added new client: Skyline Developers', 'time' => '1 day ago', 'type' => 'client', 'icon' => 'building-2' ],
    [ 'action' => 'Generated monthly analytics report', 'time' => '2 days ago', 'type' => 'report', 'icon' => 'activity' ],
    [ 'action' => 'Reviewed team allocation for Q3', 'time' => '3 days ago', 'type' => 'review', 'icon' => 'check-circle' ],
    [ 'action' => 'Configured backup automation', 'time' => '5 days ago', 'type' => 'deploy', 'icon' => 'monitor' ],
    [ 'action' => 'Onboarded 3 new employees', 'time' => '1 week ago', 'type' => 'permission', 'icon' => 'shield' ],
];

$stats = [
    [ 'label' => 'Users Managed', 'value' => 47, 'icon' => 'users', 'color' => '#06b6d4' ],
    [ 'label' => 'Projects Overseen', 'value' => 12, 'icon' => 'briefcase', 'color' => '#9333ea' ],
    [ 'label' => 'System Updates', 'value' => 38, 'icon' => 'monitor', 'color' => '#14b8a6' ],
    [ 'label' => 'Reports Generated', 'value' => 156, 'icon' => 'activity', 'color' => '#ec4899' ],
];
?>

<div class="max-w-7xl mx-auto space-y-8 animate-fade-in">

    <?php if ($successMessage): ?>
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i> <?php echo htmlspecialchars($successMessage); ?>
        </div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm font-semibold flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4"></i> <?php echo htmlspecialchars($errorMessage); ?>
        </div>
    <?php endif; ?>

    <!-- Profile Cover Card Banner -->
    <div class="glass-premium rounded-2xl p-0 overflow-hidden relative border border-white/5">
        <div class="h-32 relative" style="background: linear-gradient(135deg, #0891b2 0%, #06b6d4 50%, #9333ea 100%)">
            <div class="absolute inset-0 opacity-15" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cpath d=\'M30 0L60 30L30 60L0 30z\' fill=\'none\' stroke=\'white\' stroke-width=\'0.5\'/%3E%3C/svg%3E'); background-size: 30px 30px;"></div>
            
            <!-- Edit Button Switch -->
            <div class="absolute top-4 right-4 z-10">
                <?php if (!$isEditing): ?>
                    <a href="admin-profile.php?edit=true" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-slate-100 text-xs font-bold transition-all border border-white/10">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit Profile
                    </a>
                <?php else: ?>
                    <div class="flex gap-2">
                        <button type="submit" form="profile-form" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-bold transition-all shadow-lg shadow-emerald-500/20">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i> Save
                        </button>
                        <a href="admin-profile.php" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition-all border border-white/5">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i> Cancel
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="px-8 pb-8 -mt-12 relative z-10 flex flex-col md:flex-row gap-6 items-start md:items-end">
            <!-- Profile Avatar with Status -->
            <div class="relative flex-shrink-0">
                <div class="w-24 h-24 rounded-2xl overflow-hidden border-4 border-slate-950 shadow-2xl bg-slate-800">
                    <img src="<?php echo htmlspecialchars($defaultAdmin['avatarUrl']); ?>" alt="Admin Profile" class="w-full h-full object-cover">
                </div>
                <div class="absolute -bottom-1 -right-1 online-dot border-4 border-slate-950" style="width: 18px; height: 18px;"></div>
            </div>
            
            <!-- Core titles -->
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold font-outfit text-slate-50 tracking-tight">
                    <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </h2>
                <div class="flex flex-wrap items-center gap-2.5 mt-1.5 text-xs text-slate-400 font-medium">
                    <span class="text-cyan-400 font-bold"><?php echo htmlspecialchars($defaultAdmin['role']); ?></span>
                    <span class="text-slate-700">|</span>
                    <span><?php echo htmlspecialchars($defaultAdmin['department']); ?></span>
                    <span class="text-slate-700">|</span>
                    <span class="flex items-center gap-1"><i data-lucide="map-pin" class="w-3.5 h-3.5 text-slate-500"></i> <?php echo htmlspecialchars($_SESSION['admin_location']); ?></span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed mt-3 max-w-2xl font-medium">
                    "<?php echo htmlspecialchars($_SESSION['admin_bio']); ?>"
                </p>
            </div>
        </div>
    </div>

    <!-- Main settings dashboard grid layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left details panel column (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Personal and Professional Settings Form block -->
            <form action="admin-profile.php" method="POST" id="profile-form">
                <input type="hidden" name="action" value="save_profile">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Column Card 1: Details -->
                    <div class="glass-dark rounded-2xl p-6 border border-white/5 space-y-4 shadow-xl">
                        <h3 class="text-base font-bold font-outfit text-slate-50 flex items-center gap-2">
                            <i data-lucide="user" class="w-4 h-4 text-cyan-400"></i> Account Information
                        </h3>
                        
                        <div class="space-y-4">
                            <?php if ($isEditing): ?>
                                <div>
                                    <label class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mb-1">Full Name</label>
                                    <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['admin_name']); ?>" class="w-full px-3 py-2 rounded-xl bg-slate-800/60 border border-slate-700/60 text-xs text-slate-100 focus:border-cyan-400">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mb-1">Email Address</label>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($_SESSION['admin_email']); ?>" class="w-full px-3 py-2 rounded-xl bg-slate-800/60 border border-slate-700/60 text-xs text-slate-100 focus:border-cyan-400">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mb-1">Contact Phone</label>
                                    <input type="text" name="phone" value="<?php echo htmlspecialchars($_SESSION['admin_phone']); ?>" class="w-full px-3 py-2 rounded-xl bg-slate-800/60 border border-slate-700/60 text-xs text-slate-100 focus:border-cyan-400">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mb-1">Office Location</label>
                                    <input type="text" name="location" value="<?php echo htmlspecialchars($_SESSION['admin_location']); ?>" class="w-full px-3 py-2 rounded-xl bg-slate-800/60 border border-slate-700/60 text-xs text-slate-100 focus:border-cyan-400">
                                </div>
                                <div>
                                    <label class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mb-1">Professional Bio</label>
                                    <textarea name="bio" rows="3" class="w-full px-3 py-2 rounded-xl bg-slate-800/60 border border-slate-700/60 text-xs text-slate-100 focus:border-cyan-400 resize-none"><?php echo htmlspecialchars($_SESSION['admin_bio']); ?></textarea>
                                </div>
                            <?php else: ?>
                                <div class="flex items-center gap-3">
                                    <i data-lucide="mail" class="w-4 h-4 text-slate-500"></i>
                                    <div>
                                        <p class="text-[9px] text-slate-500 uppercase tracking-wide">Email</p>
                                        <p class="text-xs text-slate-200 mt-0.5"><?php echo htmlspecialchars($_SESSION['admin_email']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i data-lucide="phone" class="w-4 h-4 text-slate-500"></i>
                                    <div>
                                        <p class="text-[9px] text-slate-500 uppercase tracking-wide">Phone</p>
                                        <p class="text-xs text-slate-200 mt-0.5"><?php echo htmlspecialchars($_SESSION['admin_phone']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i data-lucide="map-pin" class="w-4 h-4 text-slate-500"></i>
                                    <div>
                                        <p class="text-[9px] text-slate-500 uppercase tracking-wide">Location</p>
                                        <p class="text-xs text-slate-200 mt-0.5"><?php echo htmlspecialchars($_SESSION['admin_location']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i data-lucide="calendar" class="w-4 h-4 text-slate-500"></i>
                                    <div>
                                        <p class="text-[9px] text-slate-500 uppercase tracking-wide">Member Since</p>
                                        <p class="text-xs text-slate-200 mt-0.5"><?php echo (new DateTime($defaultAdmin['joinDate']))->format('d F Y'); ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Column Card 2: Professional System details -->
                    <div class="glass-dark rounded-2xl p-6 border border-white/5 space-y-4 shadow-xl">
                        <h3 class="text-base font-bold font-outfit text-slate-50 flex items-center gap-2">
                            <i data-lucide="briefcase" class="w-4 h-4 text-cyan-400"></i> Security & Metadata
                        </h3>
                        
                        <div class="space-y-4 text-xs">
                            <div class="flex items-center gap-3">
                                <i data-lucide="shield" class="w-4 h-4 text-slate-500"></i>
                                <div>
                                    <p class="text-[9px] text-slate-500 uppercase tracking-wide">System Role</p>
                                    <p class="text-xs text-slate-200 mt-0.5"><?php echo htmlspecialchars($defaultAdmin['role']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <i data-lucide="building-2" class="w-4 h-4 text-slate-500"></i>
                                <div>
                                    <p class="text-[9px] text-slate-500 uppercase tracking-wide">Department</p>
                                    <p class="text-xs text-slate-200 mt-0.5"><?php echo htmlspecialchars($defaultAdmin['department']); ?></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <i data-lucide="award" class="w-4 h-4 text-slate-500"></i>
                                <div>
                                    <p class="text-[9px] text-slate-500 uppercase tracking-wide">System Authorization</p>
                                    <p class="text-xs text-slate-200 mt-0.5">Full Administrator Account</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <i data-lucide="globe" class="w-4 h-4 text-slate-500"></i>
                                <div>
                                    <p class="text-[9px] text-slate-500 uppercase tracking-wide">Local Timezone</p>
                                    <p class="text-xs text-slate-200 mt-0.5">IST (UTC +05:30)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <i data-lucide="lock" class="w-4 h-4 text-slate-500"></i>
                                <div>
                                    <p class="text-[9px] text-slate-500 uppercase tracking-wide">Authentication Method</p>
                                    <p class="text-xs text-slate-200 mt-0.5">SSO Federation (2FA Configured)</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Activity Logs feed -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5 shadow-xl">
                <h3 class="text-base font-bold font-outfit text-slate-50 mb-4 flex items-center gap-2">
                    <i data-lucide="clock" class="w-4 h-4 text-cyan-400"></i> Activity Logs Feed
                </h3>
                
                <div class="space-y-4">
                    <?php foreach ($activityTimeline as $index => $item): 
                        $badgeColors = [
                            'permission' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                            'deploy' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                            'client' => 'bg-teal-500/10 text-teal-400 border-teal-500/20',
                            'review' => 'bg-pink-500/10 text-pink-400 border-pink-500/20',
                            'report' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                        ];
                        $col = $badgeColors[$item['type']] ?? 'bg-slate-800 text-slate-400';
                    ?>
                        <div class="flex gap-4 items-start pb-4 border-b border-white/5 last:border-0 last:pb-0">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-lg border flex items-center justify-center flex-shrink-0 <?php echo $col; ?>">
                                    <i data-lucide="<?php echo $item['icon']; ?>" class="w-4 h-4"></i>
                                </div>
                                <?php if ($index < count($activityTimeline) - 1): ?>
                                    <div class="w-px h-8 bg-slate-800/80 mt-2"></div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-slate-200"><?php echo htmlspecialchars($item['action']); ?></p>
                                <p class="text-[10px] text-slate-500 mt-1"><?php echo $item['time']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Notifications toggles card -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5 shadow-xl">
                <h3 class="text-base font-bold font-outfit text-slate-50 mb-4 flex items-center gap-2">
                    <i data-lucide="bell" class="w-4 h-4 text-cyan-400"></i> Platform Notification Preferences
                </h3>
                <div class="divide-y divide-white/5">
                    <?php foreach ($_SESSION['notifications'] as $notif): ?>
                        <div class="flex items-center justify-between py-3">
                            <div>
                                <p class="text-xs font-bold text-slate-200"><?php echo htmlspecialchars($notif['label']); ?></p>
                                <p class="text-[10px] text-slate-500 mt-0.5"><?php echo htmlspecialchars($notif['desc']); ?></p>
                            </div>
                            <a href="admin-profile.php?toggle_notif=<?php echo urlencode($notif['key']); ?>&csrf=<?php echo urlencode(generate_csrf_token()); ?>" class="focus:outline-none select-none">
                                <?php if ($notif['enabled']): ?>
                                    <i data-lucide="toggle-right" class="w-6 h-6 text-cyan-400"></i>
                                <?php else: ?>
                                    <i data-lucide="toggle-left" class="w-6 h-6 text-slate-600"></i>
                                <?php endif; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right statistics sidebar column (1/3 width) -->
        <div class="space-y-6">
            <!-- Numerical Statistics grid -->
            <div class="glass-premium rounded-2xl p-6 border border-white/5 shadow-xl">
                <h3 class="text-base font-bold font-outfit text-slate-50 mb-4 flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-4 h-4 text-cyan-400"></i> Operations Statistics
                </h3>
                <div class="grid grid-cols-2 gap-4">
                    <?php foreach ($stats as $st): ?>
                        <div class="p-3 bg-slate-900/50 rounded-xl border border-white/5">
                            <i data-lucide="<?php echo $st['icon']; ?>" class="w-4 h-4 mb-2" style="color: <?php echo $st['color']; ?>"></i>
                            <h4 class="text-2xl font-bold font-outfit text-slate-100 leading-none"><?php echo $st['value']; ?></h4>
                            <p class="text-[9px] text-slate-500 font-bold uppercase tracking-wider mt-1.5"><?php echo $st['label']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Server uptime dials (ApexCharts Dials) -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5 space-y-6 shadow-xl">
                <h3 class="text-base font-bold font-outfit text-slate-50 flex items-center gap-2">
                    <i data-lucide="activity" class="w-4 h-4 text-cyan-400"></i> System Metrics
                </h3>
                
                <div class="space-y-6">
                    <div class="flex flex-col items-center">
                        <div id="uptime-radial" class="w-full flex justify-center"></div>
                        <p class="text-[10px] text-slate-400 uppercase font-semibold mt-1">System Uptime</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div id="completion-radial" class="w-full flex justify-center"></div>
                        <p class="text-[10px] text-slate-400 uppercase font-semibold mt-1">Task Completion</p>
                    </div>
                    <div class="flex flex-col items-center">
                        <div id="response-radial" class="w-full flex justify-center"></div>
                        <p class="text-[10px] text-slate-400 uppercase font-semibold mt-1">Avg Response Time</p>
                    </div>
                </div>
            </div>

            <!-- Quick settings items list -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5 shadow-xl">
                <h3 class="text-base font-bold font-outfit text-slate-50 mb-3 flex items-center gap-2">
                    <i data-lucide="settings" class="w-4 h-4 text-cyan-400"></i> Preferences
                </h3>
                <div class="divide-y divide-white/5 text-xs text-slate-300">
                    <div class="flex items-center justify-between py-2.5">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="palette" class="w-4 h-4 text-slate-500"></i>
                            <span>Active Theme</span>
                        </div>
                        <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded border border-white/5 font-semibold">Dark Mode</span>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="globe" class="w-4 h-4 text-slate-500"></i>
                            <span>Locale Language</span>
                        </div>
                        <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded border border-white/5 font-semibold">English (IN)</span>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="monitor" class="w-4 h-4 text-slate-500"></i>
                            <span>Dashboard Panel</span>
                        </div>
                        <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded border border-white/5 font-semibold">Grid (Default)</span>
                    </div>
                    <div class="flex items-center justify-between py-2.5">
                        <div class="flex items-center gap-2.5">
                            <i data-lucide="lock" class="w-4 h-4 text-slate-500"></i>
                            <span>Session Autoclose</span>
                        </div>
                        <span class="text-[10px] bg-slate-800 text-slate-400 px-2 py-0.5 rounded border border-white/5 font-semibold">30 minutes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Radial indicators options
    const dialOptions = (val, color) => ({
        series: [val],
        chart: {
            type: 'radialBar',
            height: 120,
            sparkline: { enabled: true }
        },
        plotOptions: {
            radialBar: {
                hollow: { size: '60%' },
                dataLabels: {
                    name: { show: false },
                    value: {
                        show: true,
                        fontSize: '14px',
                        fontWeight: 'bold',
                        color: '#f8fafc',
                        offsetY: 4,
                        formatter: function (v) { return v + "%"; }
                    }
                }
            }
        },
        colors: [color],
        stroke: { lineCap: 'round' }
    });

    document.addEventListener("DOMContentLoaded", function() {
        new ApexCharts(document.querySelector("#uptime-radial"), dialOptions(99.7, '#14b8a6')).render();
        new ApexCharts(document.querySelector("#completion-radial"), dialOptions(94.0, '#06b6d4')).render();
        new ApexCharts(document.querySelector("#response-radial"), dialOptions(87.0, '#9333ea')).render();
    });
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
