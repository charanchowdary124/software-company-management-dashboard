<?php
// projects.php
require_once __DIR__ . '/includes/header.php';

// Retrieve filter query parameters
$searchTerm = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? 'all';
$typeFilter = $_GET['type'] ?? 'all';

// Load initial lists
$projects = get_projects();

// Filter projects list in server-side PHP
$filteredProjects = array_filter($projects, function($p) use ($searchTerm, $statusFilter, $typeFilter) {
    // 1. Search Query Match
    $matchesSearch = true;
    if ($searchTerm !== '') {
        $searchLower = strtolower($searchTerm);
        $nameMatch = strtolower($p['name']);
        $locMatch = strtolower($p['location'] ?? '');
        $matchesSearch = (strpos($nameMatch, $searchLower) !== false || strpos($locMatch, $searchLower) !== false);
    }

    // 2. Status Match
    $matchesStatus = true;
    if ($statusFilter !== 'all') {
        $matchesStatus = ($p['status'] === $statusFilter);
    }

    // 3. Type Match
    $matchesType = true;
    if ($typeFilter !== 'all') {
        $matchesType = ($p['project_type'] === $typeFilter);
    }

    return $matchesSearch && $matchesStatus && $matchesType;
});

// Convert array keys to sequential index
$filteredProjects = array_values($filteredProjects);

// Color helpers
$cardColors = ['purple', 'cyan', 'pink', 'teal'];
function getCardColor($index) {
    global $cardColors;
    return $cardColors[$index % count($cardColors)];
}

function getProjectTypeColor($type) {
    switch ($type) {
        case 'architecture':
            return 'bg-purple-500';
        case 'interior_design':
            return 'bg-cyan-500';
        case 'urban_planning':
            return 'bg-pink-500';
        case 'landscape':
            return 'bg-teal-500';
        case 'renovation':
            return 'bg-orange-500';
        case 'mixed':
            return 'bg-indigo-500';
        default:
            return 'bg-slate-500';
    }
}

function getDaysRemaining($deadline) {
    $today = new DateTime();
    $deadlineDate = new DateTime($deadline);
    $interval = $today->diff($deadlineDate);
    $days = (int)$interval->format('%r%a');
    return $days;
}
?>

<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-4xl font-extrabold font-outfit text-slate-50 tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-purple-400 via-pink-400 to-cyan-400">
                Projects
            </h1>
            <p class="text-slate-300 text-xs mt-1">Manage and track all architecture studio projects</p>
        </div>
    </div>

    <!-- Controls Panel -->
    <div class="glass-dark rounded-2xl p-6 backdrop-blur-md border border-slate-700/50 shadow-xl">
        <form action="projects.php" method="GET" id="projects-form" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
            <!-- Search field -->
            <div class="col-span-1 md:col-span-5 relative group">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within:text-purple-400 transition-colors"></i>
                <input
                    type="text"
                    name="search"
                    placeholder="Search projects by name or location..."
                    value="<?php echo htmlspecialchars($searchTerm); ?>"
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-800/50 border border-slate-700/60 rounded-xl focus:outline-none focus:border-purple-500 text-slate-50 placeholder-slate-500 transition-all focus:ring-2 focus:ring-purple-500/20"
                />
            </div>

            <!-- Status filter -->
            <div class="col-span-1 md:col-span-3 relative">
                <select
                    name="status"
                    onchange="document.getElementById('projects-form').submit()"
                    class="w-full pl-4 pr-10 py-2.5 bg-slate-800/50 border border-slate-700/60 rounded-xl text-slate-200 text-sm cursor-pointer appearance-none focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                >
                    <option value="all">All Status</option>
                    <option value="planning" <?php echo $statusFilter === 'planning' ? 'selected' : ''; ?>>Planning</option>
                    <option value="in_progress" <?php echo $statusFilter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="on_hold" <?php echo $statusFilter === 'on_hold' ? 'selected' : ''; ?>>On Hold</option>
                    <option value="review" <?php echo $statusFilter === 'review' ? 'selected' : ''; ?>>Review</option>
                    <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    <option value="cancelled" <?php echo $statusFilter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                </select>
                <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
            </div>

            <!-- Type filter -->
            <div class="col-span-1 md:col-span-3 relative">
                <select
                    name="type"
                    onchange="document.getElementById('projects-form').submit()"
                    class="w-full pl-4 pr-10 py-2.5 bg-slate-800/50 border border-slate-700/60 rounded-xl text-slate-200 text-sm cursor-pointer appearance-none focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20"
                >
                    <option value="all">All Types</option>
                    <option value="architecture" <?php echo $typeFilter === 'architecture' ? 'selected' : ''; ?>>Architecture</option>
                    <option value="interior_design" <?php echo $typeFilter === 'interior_design' ? 'selected' : ''; ?>>Interior Design</option>
                    <option value="urban_planning" <?php echo $typeFilter === 'urban_planning' ? 'selected' : ''; ?>>Urban Planning</option>
                    <option value="landscape" <?php echo $typeFilter === 'landscape' ? 'selected' : ''; ?>>Landscape</option>
                    <option value="renovation" <?php echo $typeFilter === 'renovation' ? 'selected' : ''; ?>>Renovation</option>
                    <option value="mixed" <?php echo $typeFilter === 'mixed' ? 'selected' : ''; ?>>Mixed</option>
                </select>
                <i data-lucide="chevron-down" class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"></i>
            </div>

            <!-- Layout toggle -->
            <div class="col-span-1 md:col-span-1 flex gap-2 h-[42px]">
                <button
                    type="button"
                    onclick="setViewMode('grid')"
                    id="grid-mode-btn"
                    class="flex-1 flex items-center justify-center p-2 rounded-xl transition-all"
                >
                    <i data-lucide="grid-3x3" class="w-5 h-5"></i>
                </button>
                <button
                    type="button"
                    onclick="setViewMode('list')"
                    id="list-mode-btn"
                    class="flex-1 flex items-center justify-center p-2 rounded-xl transition-all"
                >
                    <i data-lucide="list" class="w-5 h-5"></i>
                </button>
            </div>
        </form>

        <p class="text-xs text-slate-400 mt-4">
            Showing <span class="text-slate-100 font-semibold"><?php echo count($filteredProjects); ?></span> of <span class="text-slate-100 font-semibold"><?php echo count($projects); ?></span> projects
        </p>
    </div>

    <!-- Grid View Layout Container -->
    <div id="projects-grid-view" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php foreach ($filteredProjects as $index => $project): 
            $cardColor = getCardColor($index);
            $typeColor = getProjectTypeColor($project['project_type']);
            $daysLeft = getDaysRemaining($project['deadline']);
            $assignments = get_project_assignments($project['id']);
            $budgetSpentPercent = $project['budget'] > 0 ? (int)round(($project['budget_spent'] / $project['budget']) * 100) : 0;
        ?>
            <div 
                class="glass-premium rounded-2xl overflow-hidden hover-glow-<?php echo $cardColor; ?> transition-all duration-300 relative border border-white/5 flex flex-col p-6 group"
                id="grid-card-<?php echo $project['id']; ?>"
            >
                <!-- Color Strip Accent -->
                <div class="<?php echo $typeColor; ?> h-2 w-full -mx-6 -mt-6 rounded-t-2xl mb-4"></div>

                <!-- Card Header -->
                <div class="flex items-start justify-between mb-4 cursor-pointer" onclick="toggleProjectExpand('<?php echo htmlspecialchars($project['id']); ?>')">
                    <div class="flex-1 pr-4">
                        <h3 class="text-lg font-bold text-slate-50 group-hover:text-purple-400 transition-colors leading-tight mb-2">
                            <?php echo htmlspecialchars($project['name']); ?>
                        </h3>
                        <?php echo render_status_badge($project['status']); ?>
                    </div>
                    <button class="text-slate-500 hover:text-slate-200 mt-1 focus:outline-none">
                        <i data-lucide="chevron-down" class="w-5 h-5 toggle-arrow transition-transform duration-300" id="arrow-<?php echo $project['id']; ?>"></i>
                    </button>
                </div>

                <!-- Quick info block -->
                <div class="space-y-3 mb-4 flex-1">
                    <div class="flex items-center gap-2 text-xs text-slate-300">
                        <i data-lucide="building-2" class="w-4 h-4 text-purple-400 flex-shrink-0"></i>
                        <span class="font-medium text-slate-200"><?php echo htmlspecialchars($project['client']['company_name'] ?? 'No client assigned'); ?></span>
                    </div>

                    <div class="flex items-center gap-2 text-xs text-slate-300">
                        <i data-lucide="map-pin" class="w-4 h-4 text-cyan-400 flex-shrink-0"></i>
                        <span class="font-medium text-slate-200"><?php echo htmlspecialchars($project['location'] ?? 'Location N/A'); ?></span>
                    </div>

                    <!-- Progress dials -->
                    <div class="flex items-center gap-6 py-2">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-semibold tracking-wider mb-1">Progress</p>
                            <?php echo render_progress_ring($project['progress_percentage'], 60, $cardColor); ?>
                        </div>
                        <div class="flex-1">
                            <p class="text-[10px] text-slate-500 uppercase font-semibold tracking-wider mb-1">Budget Spent</p>
                            <div class="mb-1">
                                <?php echo render_workload_bar($budgetSpentPercent); ?>
                            </div>
                            <p class="text-[10px] text-slate-400">
                                ₹<?php echo number_format($project['budget_spent'] / 100000, 1); ?>L / ₹<?php echo number_format($project['budget'] / 100000, 1); ?>L (<?php echo $budgetSpentPercent; ?>%)
                            </p>
                        </div>
                    </div>

                    <!-- Timelines / Team statistics -->
                    <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-800/80">
                        <div class="flex items-center gap-2">
                            <i data-lucide="calendar" class="w-4 h-4 text-pink-400"></i>
                            <div class="text-[10px]">
                                <p class="text-slate-500 leading-none">Days Left</p>
                                <p class="font-bold text-slate-200 mt-1"><?php echo $daysLeft > 0 ? $daysLeft . 'd' : ($daysLeft === 0 ? 'Deadline Today' : 'Overdue ' . abs($daysLeft) . 'd'); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4 text-teal-400"></i>
                            <div class="text-[10px]">
                                <p class="text-slate-500 leading-none">Team Assigned</p>
                                <p class="font-bold text-slate-200 mt-1"><?php echo count($assignments); ?> members</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collapsible details accordion panel -->
                <div class="hidden mt-6 pt-6 border-t border-slate-800/80 space-y-6 animate-slide-up relative" id="details-<?php echo $project['id']; ?>">
                    <!-- Project description -->
                    <div>
                        <p class="text-xs text-slate-300 leading-relaxed"><?php echo htmlspecialchars($project['description']); ?></p>
                    </div>

                    <!-- Team assignments detail lists -->
                    <div>
                        <h4 class="text-xs font-bold text-slate-100 uppercase tracking-wider mb-3">Project Team Members</h4>
                        <div class="space-y-2.5">
                            <?php foreach ($assignments as $asg): ?>
                                <div class="p-3 bg-slate-900/40 rounded-xl border border-white/5">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="text-xs font-bold text-slate-200"><?php echo htmlspecialchars($asg['employee']['first_name'] . ' ' . $asg['employee']['last_name']); ?></p>
                                        <span class="text-[9px] font-bold text-purple-300 bg-purple-500/10 px-2 py-0.5 rounded border border-purple-500/20">
                                            <?php echo htmlspecialchars($asg['role']); ?>
                                        </span>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mb-2 leading-relaxed"><?php echo htmlspecialchars($asg['responsibilities']); ?></p>
                                    <div class="flex items-center justify-between text-[9px]">
                                        <span class="text-slate-500">Allocation: <?php echo $asg['allocation_percentage']; ?>%</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <?php if (count($assignments) === 0): ?>
                                <p class="text-xs text-slate-500 italic">No team assigned to this project currently.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Timeline details -->
                    <div class="pt-4 border-t border-slate-800/80 grid grid-cols-2 gap-4 text-[10px]">
                        <div>
                            <p class="text-slate-500">Start Date</p>
                            <p class="font-bold text-slate-200 mt-1"><?php echo (new DateTime($project['start_date']))->format('d M Y'); ?></p>
                        </div>
                        <div>
                            <p class="text-slate-500">Deadline Target</p>
                            <p class="font-bold text-slate-200 mt-1"><?php echo (new DateTime($project['deadline']))->format('d M Y'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- List View Table Container -->
    <div id="projects-list-view" class="hidden glass-dark rounded-2xl overflow-hidden border border-slate-700/50 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-900/60 text-slate-300 text-xs font-semibold">
                        <th class="px-6 py-4">Project</th>
                        <th class="px-6 py-4">Client</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Progress</th>
                        <th class="px-6 py-4 text-right">Deadline</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-white/5">
                    <?php foreach ($filteredProjects as $index => $project): 
                        $daysLeft = getDaysRemaining($project['deadline']);
                        $cardColor = getCardColor($index);
                    ?>
                        <!-- Main row -->
                        <tr 
                            class="hover:bg-white/5 cursor-pointer transition-colors"
                            onclick="toggleProjectExpand('<?php echo htmlspecialchars($project['id']); ?>')"
                        >
                            <td class="px-6 py-4 font-bold text-slate-50">
                                <p class="leading-tight"><?php echo htmlspecialchars($project['name']); ?></p>
                                <span class="text-[10px] font-normal text-slate-500 mt-0.5 inline-block"><?php echo htmlspecialchars($project['location'] ?? 'India'); ?></span>
                            </td>
                            <td class="px-6 py-4 text-slate-300">
                                <?php echo htmlspecialchars($project['client']['company_name'] ?? 'N/A'); ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full border border-slate-700/50 bg-slate-800 text-[10px] text-slate-300 uppercase font-semibold">
                                    <?php echo htmlspecialchars(str_replace('_', ' ', $project['project_type'])); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php echo render_status_badge($project['status'], 'sm'); ?>
                            </td>
                            <td class="px-6 py-4 flex items-center justify-center">
                                <?php echo render_progress_ring($project['progress_percentage'], 40, $cardColor); ?>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-300">
                                <?php echo $daysLeft > 0 ? $daysLeft . 'd left' : ($daysLeft === 0 ? 'Today' : 'Overdue ' . abs($daysLeft) . 'd'); ?>
                            </td>
                        </tr>

                        <!-- Accordion row matching layout -->
                        <tr id="list-details-row-<?php echo $project['id']; ?>" class="hidden bg-slate-950/40">
                            <td colspan="6" class="px-6 py-5">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-slide-up">
                                    <div class="md:col-span-2 space-y-4">
                                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider leading-none">Project Overview</p>
                                        <p class="text-xs text-slate-300 leading-relaxed"><?php echo htmlspecialchars($project['description']); ?></p>
                                        
                                        <!-- Timeline stats -->
                                        <div class="flex gap-8 text-[10px] pt-2">
                                            <div>
                                                <span class="text-slate-500">Start Date:</span>
                                                <span class="font-bold text-slate-300 ml-1"><?php echo (new DateTime($project['start_date']))->format('d M Y'); ?></span>
                                            </div>
                                            <div>
                                                <span class="text-slate-500">Deadline:</span>
                                                <span class="font-bold text-slate-300 ml-1"><?php echo (new DateTime($project['deadline']))->format('d M Y'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider leading-none mb-3">Allocated Team</p>
                                        <div class="space-y-2.5">
                                            <?php 
                                            $assignments = get_project_assignments($project['id']);
                                            foreach ($assignments as $asg): 
                                            ?>
                                                <div class="p-2.5 rounded-lg bg-slate-900/60 border border-white/5 text-[10px]">
                                                    <div class="flex items-center justify-between mb-1 font-bold text-slate-200">
                                                        <span><?php echo htmlspecialchars($asg['employee']['first_name'] . ' ' . $asg['employee']['last_name']); ?></span>
                                                        <span class="text-cyan-400"><?php echo $asg['allocation_percentage']; ?>%</span>
                                                    </div>
                                                    <p class="text-slate-400 leading-normal text-[9px]"><?php echo htmlspecialchars($asg['role']); ?></p>
                                                </div>
                                            <?php endforeach; ?>
                                            <?php if (count($assignments) === 0): ?>
                                                <p class="text-[10px] text-slate-500 italic">No team assigned.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Empty state -->
    <?php if (count($filteredProjects) === 0): ?>
        <div class="text-center py-20 rounded-2xl glass-dark border border-white/5">
            <p class="text-slate-400 text-lg font-medium">No projects found</p>
            <p class="text-slate-500 text-sm mt-1">Try adjusting your filters or search terms</p>
        </div>
    <?php endif; ?>
</div>

<script>
    // 1. Grid/List View Toggler scripts
    let currentView = localStorage.getItem('projects-view-mode') || 'grid';

    function applyViewMode(mode) {
        const gridView = document.getElementById('projects-grid-view');
        const listView = document.getElementById('projects-list-view');
        const gridBtn = document.getElementById('grid-mode-btn');
        const listBtn = document.getElementById('list-mode-btn');

        if (mode === 'list') {
            gridView.classList.add('hidden');
            listView.classList.remove('hidden');
            
            listBtn.className = "flex-1 flex items-center justify-center p-2 rounded-xl transition-all bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg shadow-purple-500/30";
            gridBtn.className = "flex-1 flex items-center justify-center p-2 rounded-xl transition-all bg-slate-800/50 text-slate-400 hover:text-slate-50 hover:bg-slate-700/50";
        } else {
            listView.classList.add('hidden');
            gridView.classList.remove('hidden');
            
            gridBtn.className = "flex-1 flex items-center justify-center p-2 rounded-xl transition-all bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg shadow-purple-500/30";
            listBtn.className = "flex-1 flex items-center justify-center p-2 rounded-xl transition-all bg-slate-800/50 text-slate-400 hover:text-slate-50 hover:bg-slate-700/50";
        }
        currentView = mode;
        localStorage.setItem('projects-view-mode', mode);
        
        // Hide all expanded items when switching views
        document.querySelectorAll('.toggle-arrow').forEach(arr => arr.style.transform = 'rotate(0deg)');
        document.querySelectorAll('[id^="details-"]').forEach(panel => panel.classList.add('hidden'));
        document.querySelectorAll('[id^="list-details-row-"]').forEach(row => row.classList.add('hidden'));
    }

    function setViewMode(mode) {
        applyViewMode(mode);
    }

    // Initialize layout toggle on load
    applyViewMode(currentView);

    // 2. Expand/Collapse Accordion scripts
    function toggleProjectExpand(projId) {
        if (currentView === 'list') {
            // Table row details accordion
            const row = document.getElementById(`list-details-row-${projId}`);
            if (row) {
                row.classList.toggle('hidden');
            }
        } else {
            // Card details accordion
            const panel = document.getElementById(`details-${projId}`);
            const arrow = document.getElementById(`arrow-${projId}`);
            if (panel && arrow) {
                const isOpen = !panel.classList.contains('hidden');
                if (isOpen) {
                    panel.classList.add('hidden');
                    arrow.style.transform = 'rotate(0deg)';
                } else {
                    panel.classList.remove('hidden');
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        }
    }
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
