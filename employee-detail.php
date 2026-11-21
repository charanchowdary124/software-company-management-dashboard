<?php
// employee-detail.php
require_once __DIR__ . '/includes/functions.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo '<p class="text-slate-400 text-sm">No employee ID specified.</p>';
    exit;
}

$employees = get_employees();
$employee = null;
foreach ($employees as $emp) {
    if ($emp['id'] === $id) {
        $employee = $emp;
        break;
    }
}

if (!$employee) {
    echo '<p class="text-slate-400 text-sm">Employee not found.</p>';
    exit;
}

$hireDateFormatted = (new DateTime($employee['hire_date']))->format('d M Y');
?>

<!-- Slide drawer content -->
<div class="space-y-6">
    <!-- Quick Profile Info Banner -->
    <div class="p-6 rounded-2xl bg-gradient-to-br from-slate-900 to-slate-800 border border-white/5 flex flex-col items-center text-center relative overflow-hidden">
        <!-- Accent circles -->
        <div class="absolute -top-12 -right-12 w-36 h-36 rounded-full bg-cyan-500/10 filter blur-xl"></div>
        <div class="absolute -bottom-12 -left-12 w-36 h-36 rounded-full bg-purple-500/10 filter blur-xl"></div>

        <div class="relative mb-4">
            <div class="w-24 h-24 rounded-2xl overflow-hidden border-2 border-cyan-500/30 shadow-lg relative">
                <?php if ($employee['profile_photo_url']): ?>
                    <img src="<?php echo htmlspecialchars($employee['profile_photo_url']); ?>" alt="Profile Photo" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full bg-gradient-to-br from-purple-500 to-cyan-500 flex items-center justify-center text-slate-100 font-bold text-3xl">
                        <?php echo $employee['first_name'][0] . $employee['last_name'][0]; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <h3 class="text-2xl font-bold font-outfit text-slate-50 leading-tight">
            <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
        </h3>
        <p class="text-sm text-cyan-400 font-medium mt-1"><?php echo htmlspecialchars($employee['designation']); ?></p>
        <p class="text-xs text-slate-400 mt-0.5"><?php echo htmlspecialchars($employee['department']['name'] ?? 'General Design'); ?></p>
        
        <div class="mt-4 flex items-center gap-2">
            <?php echo render_status_badge($employee['availability_status']); ?>
            <span class="text-xs text-slate-500 font-mono"><?php echo htmlspecialchars($employee['employee_id']); ?></span>
        </div>
    </div>

    <!-- Contact details card -->
    <div class="glass-dark rounded-xl p-5 border border-white/5 space-y-4">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
            <i data-lucide="user" class="w-4 h-4 text-cyan-400"></i> Contact Information
        </h4>
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <i data-lucide="mail" class="w-4 h-4 text-slate-500 flex-shrink-0"></i>
                <div class="min-w-0">
                    <p class="text-[10px] text-slate-500 leading-none">Email Address</p>
                    <p class="text-xs text-slate-200 truncate mt-1"><?php echo htmlspecialchars($employee['email']); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <i data-lucide="phone" class="w-4 h-4 text-slate-500 flex-shrink-0"></i>
                <div>
                    <p class="text-[10px] text-slate-500 leading-none">Phone Number</p>
                    <p class="text-xs text-slate-200 mt-1"><?php echo htmlspecialchars($employee['phone']); ?></p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <i data-lucide="calendar" class="w-4 h-4 text-slate-500 flex-shrink-0"></i>
                <div>
                    <p class="text-[10px] text-slate-500 leading-none">Joining Date</p>
                    <p class="text-xs text-slate-200 mt-1"><?php echo $hireDateFormatted; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Project assignments breakdown -->
    <div class="glass-dark rounded-xl p-5 border border-white/5 space-y-4">
        <div class="flex items-center justify-between">
            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="folder-kanban" class="w-4 h-4 text-cyan-400"></i> Active Allocations
            </h4>
            <span class="text-xs font-bold text-cyan-400"><?php echo $employee['workload_percentage']; ?>% Workload</span>
        </div>
        
        <div class="space-y-3">
            <?php 
            $asgCount = 0;
            foreach ($employee['assignments'] as $asg): 
                if ($asg['status'] !== 'active') continue;
                $asgCount++;
            ?>
                <div class="p-3.5 rounded-lg bg-slate-900/50 border border-white/5">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs font-bold text-slate-100"><?php echo htmlspecialchars($asg['project']['name'] ?? 'Direct Assignment'); ?></p>
                        <span class="text-[9px] font-bold text-purple-300 bg-purple-500/20 px-2 py-0.5 rounded border border-purple-500/30">
                            <?php echo htmlspecialchars($asg['role']); ?>
                        </span>
                    </div>
                    <p class="text-[10px] text-slate-400 leading-normal mb-3"><?php echo htmlspecialchars($asg['responsibilities']); ?></p>
                    <div class="flex items-center justify-between text-[10px]">
                        <span class="text-slate-500">Allocation: <?php echo $asg['allocation_percentage']; ?>%</span>
                        <span class="text-slate-500">Deadline: <?php echo (new DateTime($asg['project']['deadline']))->format('d M Y'); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if ($asgCount === 0): ?>
                <p class="text-xs text-slate-500 italic py-2">No active project assignments.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Skills list -->
    <div class="glass-dark rounded-xl p-5 border border-white/5 space-y-4">
        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-2">
            <i data-lucide="award" class="w-4 h-4 text-cyan-400"></i> Professional Skills
        </h4>
        <div class="flex flex-wrap gap-2">
            <?php 
            $skillsCount = 0;
            foreach ($employee['skills'] as $sk): 
                $skillsCount++;
                $profClass = $sk['proficiency_level'] === 'expert' ? 'border-teal-500/30 text-teal-400 bg-teal-500/10' : ($sk['proficiency_level'] === 'advanced' ? 'border-cyan-500/30 text-cyan-400 bg-cyan-500/10' : 'border-purple-500/30 text-purple-400 bg-purple-500/10');
            ?>
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full border text-xs font-medium <?php echo $profClass; ?>">
                    <?php echo htmlspecialchars($sk['skill']['name'] ?? 'Skill'); ?>
                    <?php if ($sk['certified']): ?>
                        <i data-lucide="check-circle" class="w-3 h-3 text-cyan-400" title="Certified"></i>
                    <?php endif; ?>
                </span>
            <?php endforeach; ?>
            <?php if ($skillsCount === 0): ?>
                <p class="text-xs text-slate-500 italic py-2">No professional skills listed.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Re-initialize Lucide Icons for AJAX loaded fragment
    lucide.createIcons();
</script>
