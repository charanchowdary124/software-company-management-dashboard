<?php
// employees.php
require_once __DIR__ . '/includes/header.php';

// Retrieve filter query parameters
$searchQuery = $_GET['search'] ?? '';
$selectedDepartmentId = $_GET['department'] ?? '';
$availabilityFilter = $_GET['availability'] ?? '';
$sortBy = $_GET['sort'] ?? 'name';

// Load initial lists
$employees = get_employees();
$departments = get_departments();

// Filter employees list in server-side PHP
$filteredEmployees = array_filter($employees, function($emp) use ($searchQuery, $selectedDepartmentId, $availabilityFilter) {
    // 1. Search Query Match
    $matchesSearch = true;
    if ($searchQuery !== '') {
        $searchQueryLower = strtolower($searchQuery);
        $fullName = strtolower($emp['first_name'] . ' ' . $emp['last_name']);
        $idMatch = strtolower($emp['employee_id']);
        $desigMatch = strtolower($emp['designation']);
        
        $skillMatch = false;
        foreach ($emp['skills'] as $sk) {
            if (isset($sk['skill']['name']) && strpos(strtolower($sk['skill']['name']), $searchQueryLower) !== false) {
                $skillMatch = true;
                break;
            }
        }
        
        $matchesSearch = (
            strpos($fullName, $searchQueryLower) !== false ||
            strpos($idMatch, $searchQueryLower) !== false ||
            strpos($desigMatch, $searchQueryLower) !== false ||
            $skillMatch
        );
    }

    // 2. Department ID Match
    $matchesDepartment = true;
    if ($selectedDepartmentId !== '') {
        $matchesDepartment = ($emp['department_id'] === $selectedDepartmentId);
    }

    // 3. Availability Match
    $matchesAvailability = true;
    if ($availabilityFilter !== '') {
        $matchesAvailability = ($emp['availability_status'] === $availabilityFilter);
    }

    return $matchesSearch && $matchesDepartment && $matchesAvailability;
});

// Sort employees list in server-side PHP
usort($filteredEmployees, function($a, $b) use ($sortBy) {
    if ($sortBy === 'workload') {
        return $b['workload_percentage'] - $a['workload_percentage'];
    } elseif ($sortBy === 'rating') {
        // float comparison
        return ($b['performance_rating'] < $a['performance_rating']) ? -1 : 1;
    } else {
        // default: alphabetical name
        $nameA = $a['first_name'] . ' ' . $a['last_name'];
        $nameB = $b['first_name'] . ' ' . $b['last_name'];
        return strcmp($nameA, $nameB);
    }
});

// Count active filters
$activeFilterCount = 0;
if ($searchQuery !== '') $activeFilterCount++;
if ($selectedDepartmentId !== '') $activeFilterCount++;
if ($availabilityFilter !== '') $activeFilterCount++;

// Card outline colors helper
$cardColors = ['purple', 'cyan', 'pink', 'teal'];
function getCardColor($index) {
    global $cardColors;
    return $cardColors[$index % count($cardColors)];
}
?>

<div class="space-y-6">
    <!-- Header Controls Panel -->
    <div class="glass-dark rounded-2xl p-6 border border-white/5 shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-extrabold font-outfit text-slate-50 tracking-tight">Employees</h1>
                <p class="text-slate-400 text-xs mt-1">Manage allocations and view competencies of the studio team</p>
            </div>
            
            <?php if ($activeFilterCount > 0): ?>
                <div class="flex items-center gap-2 self-start md:self-auto px-4 py-1.5 rounded-full border border-cyan-500/30 bg-cyan-500/5">
                    <span class="text-[10px] font-bold text-slate-300 uppercase tracking-wider">Active Filters</span>
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gradient-to-r from-purple-500 to-cyan-400 text-[10px] font-extrabold text-white animate-pulse">
                        <?php echo $activeFilterCount; ?>
                    </span>
                    <a href="employees.php" class="text-[10px] text-cyan-400 font-bold hover:underline ml-1">Clear</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Filter and Search Form -->
        <form action="employees.php" method="GET" class="space-y-4" id="filters-form">
            <!-- Search bar -->
            <div class="relative group">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500 group-focus-within:text-cyan-400 transition-colors"></i>
                <input
                    type="text"
                    name="search"
                    placeholder="Search by employee name, designation, or skill..."
                    value="<?php echo htmlspecialchars($searchQuery); ?>"
                    class="w-full pl-12 pr-4 py-3 rounded-xl bg-slate-900/60 border border-white/5 focus:border-cyan-400/40 text-slate-50 text-sm placeholder:text-slate-500 transition-all focus:shadow-lg focus:shadow-cyan-400/10"
                />
            </div>

            <!-- Filters Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Department Selection -->
                <div class="relative">
                    <select
                        name="department"
                        onchange="document.getElementById('filters-form').submit()"
                        class="w-full pl-4 pr-10 py-2.5 rounded-lg bg-slate-900/80 border border-white/5 text-slate-300 text-sm cursor-pointer appearance-none focus:border-cyan-400/40 focus:ring-1 focus:ring-cyan-400/20 transition-all"
                    >
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo htmlspecialchars($dept['id']); ?>" <?php echo $selectedDepartmentId === $dept['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dept['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                </div>

                <!-- Availability Selection -->
                <div class="relative">
                    <select
                        name="availability"
                        onchange="document.getElementById('filters-form').submit()"
                        class="w-full pl-4 pr-10 py-2.5 rounded-lg bg-slate-900/80 border border-white/5 text-slate-300 text-sm cursor-pointer appearance-none focus:border-cyan-400/40 focus:ring-1 focus:ring-cyan-400/20 transition-all"
                    >
                        <option value="">All Availability</option>
                        <option value="available" <?php echo $availabilityFilter === 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="busy" <?php echo $availabilityFilter === 'busy' ? 'selected' : ''; ?>>Busy</option>
                        <option value="on_leave" <?php echo $availabilityFilter === 'on_leave' ? 'selected' : ''; ?>>On Leave</option>
                        <option value="offline" <?php echo $availabilityFilter === 'offline' ? 'selected' : ''; ?>>Offline</option>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                </div>

                <!-- Sorting Selection -->
                <div class="relative">
                    <select
                        name="sort"
                        onchange="document.getElementById('filters-form').submit()"
                        class="w-full pl-4 pr-10 py-2.5 rounded-lg bg-slate-900/80 border border-white/5 text-slate-300 text-sm cursor-pointer appearance-none focus:border-cyan-400/40 focus:ring-1 focus:ring-cyan-400/20 transition-all"
                    >
                        <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Sort by Name</option>
                        <option value="workload" <?php echo $sortBy === 'workload' ? 'selected' : ''; ?>>Sort by Workload</option>
                        <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Sort by Rating</option>
                    </select>
                    <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
                </div>
            </div>
        </form>
    </div>

    <!-- Employees Directory Grid -->
    <?php if (count($filteredEmployees) === 0): ?>
        <div class="text-center py-20 rounded-2xl glass-dark border border-white/5">
            <div class="w-16 h-16 rounded-full bg-slate-800/80 flex items-center justify-center mx-auto mb-4 border border-white/10">
                <i data-lucide="search" class="w-8 h-8 text-slate-500"></i>
            </div>
            <p class="text-slate-400 text-lg font-medium">No employees found</p>
            <p class="text-slate-500 text-sm mt-1">Try adjusting your search terms or filters</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php 
            $i = 0;
            foreach ($filteredEmployees as $employee): 
                $cardColor = getCardColor($i++);
                
                // Set availability pulse dot
                $pulseDot = 'bg-slate-500';
                if ($employee['availability_status'] === 'available') {
                    $pulseDot = 'bg-green-500 animate-pulse';
                } elseif ($employee['availability_status'] === 'busy') {
                    $pulseDot = 'bg-cyan-500 animate-pulse';
                } elseif ($employee['availability_status'] === 'on_leave') {
                    $pulseDot = 'bg-amber-500';
                }
            ?>
                <div 
                    class="glass-premium rounded-2xl overflow-hidden hover-lift hover-glow-<?php echo $cardColor; ?> cursor-pointer flex flex-col p-6 group transition-all duration-300 relative border border-white/5"
                    onclick="openEmployeeDetail('<?php echo htmlspecialchars($employee['id']); ?>')"
                >
                    <!-- Top header background image crop -->
                    <div class="relative -mx-6 -mt-6 mb-4 overflow-hidden h-36 rounded-t-2xl">
                        <?php if ($employee['profile_photo_url']): ?>
                            <img src="<?php echo htmlspecialchars($employee['profile_photo_url']); ?>" alt="Profile avatar" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-slate-950/80"></div>
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-purple-600 to-cyan-500 flex items-center justify-center text-slate-100 font-bold text-4xl">
                                <?php echo $employee['first_name'][0] . $employee['last_name'][0]; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Status header -->
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full <?php echo $pulseDot; ?>"></span>
                            <span class="text-[10px] font-semibold text-slate-500 tracking-wider font-mono"><?php echo htmlspecialchars($employee['employee_id']); ?></span>
                        </div>
                        <?php echo render_status_badge($employee['availability_status'], 'sm'); ?>
                    </div>

                    <!-- Name and Title -->
                    <h3 class="text-lg font-bold text-gradient-cyan group-hover:text-cyan-300 transition-colors leading-snug">
                        <?php echo htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']); ?>
                    </h3>
                    <p class="text-xs text-slate-300 mt-1"><?php echo htmlspecialchars($employee['designation']); ?></p>
                    <p class="text-[10px] text-slate-500 mt-0.5 font-medium"><?php echo htmlspecialchars($employee['department']['name'] ?? 'General Design'); ?></p>

                    <!-- Divider line -->
                    <div class="w-full h-px bg-gradient-to-r from-transparent via-slate-800 to-transparent my-4"></div>

                    <!-- Capacity bar -->
                    <div class="mb-4">
                        <div class="flex justify-between items-center text-xs mb-1.5">
                            <span class="text-slate-400 font-semibold text-[10px] uppercase tracking-wide">Workload</span>
                            <span class="font-bold text-cyan-400"><?php echo $employee['workload_percentage']; ?>%</span>
                        </div>
                        <?php echo render_workload_bar($employee['workload_percentage'], 'sm'); ?>
                    </div>

                    <!-- Rating stars -->
                    <div class="mb-4">
                        <span class="text-slate-400 font-semibold text-[10px] uppercase tracking-wide block mb-1">Performance</span>
                        <?php echo render_star_rating($employee['performance_rating']); ?>
                    </div>

                    <!-- Skills -->
                    <div class="mt-auto space-y-1">
                        <span class="text-slate-400 font-semibold text-[10px] uppercase tracking-wide block">Key Skills</span>
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            <?php 
                            $skills = array_slice($employee['skills'], 0, 2);
                            foreach ($skills as $sk): 
                            ?>
                                <span class="px-2 py-0.5 text-[9px] font-semibold bg-cyan-500/10 border border-cyan-500/20 text-cyan-300 rounded-full">
                                    <?php echo htmlspecialchars($sk['skill']['name'] ?? 'Skill'); ?>
                                </span>
                            <?php endforeach; ?>
                            <?php if (count($employee['skills']) > 2): ?>
                                <span class="px-2 py-0.5 text-[9px] font-semibold bg-slate-800 border border-white/5 text-slate-400 rounded-full">
                                    +<?php echo (count($employee['skills']) - 2); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (count($employee['skills']) === 0): ?>
                                <span class="text-[10px] text-slate-500 italic">No skills mapped</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- AJAX Side Detail Drawer Overlay -->
<div id="detail-backdrop" class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm hidden transition-opacity duration-300" onclick="closeEmployeeDetail()"></div>

<!-- Slide Drawer -->
<div id="detail-drawer" class="fixed right-0 top-0 h-screen w-full max-w-xl glass-dark border-l border-cyan-500/10 z-50 overflow-y-auto shadow-2xl translate-x-full transition-transform duration-300 flex flex-col">
    <!-- Header -->
    <div class="sticky top-0 z-20 border-b border-cyan-500/10 p-6 glass-dark flex items-start justify-between backdrop-blur-xl">
        <div>
            <h2 class="text-xl font-bold font-outfit text-gradient" id="drawer-title">Employee Details</h2>
            <p class="text-xs text-slate-400 mt-1" id="drawer-subtitle">Profile profile information & tasks</p>
        </div>
        <button onclick="closeEmployeeDetail()" class="p-2 rounded-lg hover:bg-white/10 transition-colors focus:outline-none group">
            <i data-lucide="x" class="w-6 h-6 text-slate-400 group-hover:text-slate-200"></i>
        </button>
    </div>

    <!-- AJAX content body container -->
    <div id="detail-drawer-content" class="p-6 flex-1">
        <!-- Content will be injected here -->
    </div>
</div>

<script>
    // AJAX Drawer functions
    function openEmployeeDetail(empId) {
        const backdrop = document.getElementById('detail-backdrop');
        const drawer = document.getElementById('detail-drawer');
        const content = document.getElementById('detail-drawer-content');
        
        // Show overlay backdrop and slide drawer open
        backdrop.classList.remove('hidden');
        drawer.classList.remove('translate-x-full');
        drawer.classList.add('translate-x-0');

        // Loading state
        content.innerHTML = `
            <div class="flex flex-col items-center justify-center h-64 space-y-4">
                <div class="relative w-12 h-12">
                    <div class="absolute inset-0 rounded-full border-2 border-slate-800 border-t-cyan-400 animate-spin"></div>
                    <div class="absolute inset-0 rounded-full border-2 border-transparent border-r-purple-500 animate-spin" style="animation-direction: reverse; animation-duration: 2s"></div>
                </div>
                <p class="text-xs text-slate-400 font-medium">Fetching details...</p>
            </div>
        `;

        // Fetch detail content
        fetch(`employee-detail.php?id=${encodeURIComponent(empId)}`)
            .then(res => res.text())
            .then(html => {
                content.innerHTML = html;
                
                // Extract employee name/id for drawer header
                const profileTitleEl = content.querySelector('h3');
                const profileIdEl = content.querySelector('.font-mono');
                if (profileTitleEl && profileIdEl) {
                    document.getElementById('drawer-title').textContent = profileTitleEl.textContent.trim();
                    document.getElementById('drawer-subtitle').textContent = profileIdEl.textContent.trim();
                }
            })
            .catch(err => {
                content.innerHTML = `
                    <div class="text-center py-12">
                        <i data-lucide="alert-circle" class="w-12 h-12 text-pink-500 mx-auto mb-3"></i>
                        <p class="text-sm font-semibold text-slate-200">Error loading details</p>
                        <p class="text-xs text-slate-500 mt-1">Please check connection or try again.</p>
                    </div>
                `;
                lucide.createIcons();
            });
    }

    function closeEmployeeDetail() {
        const backdrop = document.getElementById('detail-backdrop');
        const drawer = document.getElementById('detail-drawer');
        
        // Slide drawer closed and hide overlay backdrop
        drawer.classList.add('translate-x-full');
        drawer.classList.remove('translate-x-0');
        backdrop.classList.add('hidden');
    }
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
