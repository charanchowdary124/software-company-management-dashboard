<?php
// index.php
require_once __DIR__ . '/includes/header.php';

// Fetch all dashboard data
$employees = get_employees();
$projects = get_projects();
$clients = get_clients();

// Calculate KPIs
$totalEmployees = count($employees);
$activeProjects = 0;
$uniqueClientIds = [];
$totalWorkload = 0;

foreach ($projects as $proj) {
    if ($proj['status'] === 'in_progress') {
        $activeProjects++;
    }
    if ($proj['client_id'] && !in_array($proj['client_id'], $uniqueClientIds)) {
        $uniqueClientIds[] = $proj['client_id'];
    }
}
$totalClients = count($uniqueClientIds);

foreach ($employees as $emp) {
    $totalWorkload += (int)$emp['workload_percentage'];
}
$avgWorkload = $totalEmployees > 0 ? Math_round($totalWorkload / $totalEmployees) : 0;

function Math_round($val) {
    return (int)round($val);
}

// Prepare Team Workload (top 8 sorted by capacity)
$sortedEmployees = $employees;
usort($sortedEmployees, function($a, $b) {
    return $b['workload_percentage'] - $a['workload_percentage'];
});
$topWorkloadEmployees = array_slice($sortedEmployees, 0, 8);

// Prepare Availability Chart Counts
$availabilityCounts = [
    'available' => 0,
    'busy' => 0,
    'on_leave' => 0,
    'offline' => 0
];
foreach ($employees as $emp) {
    $status = $emp['availability_status'];
    if (isset($availabilityCounts[$status])) {
        $availabilityCounts[$status]++;
    } else {
        $availabilityCounts['offline']++;
    }
}

// AI recommendations logic
$recommendations = [];

// Underutilized resources (< 30%)
$underutilized = [];
foreach ($employees as $emp) {
    if ((int)$emp['workload_percentage'] < 30) {
        $underutilized[] = $emp['first_name'];
    }
}
if (count($underutilized) > 0) {
    $recommendations[] = [
        'type' => 'cyan',
        'title' => count($underutilized) . ' Underutilized Resources',
        'description' => implode(', ', array_slice($underutilized, 0, 3)) . (count($underutilized) > 3 ? '...' : '') . (count($underutilized) === 1 ? ' is' : ' are') . ' available for allocation',
        'icon' => 'users'
    ];
}

// Overloaded resources (> 85%)
$overloadedCount = 0;
foreach ($employees as $emp) {
    if ((int)$emp['workload_percentage'] > 85) {
        $overloadedCount++;
    }
}
if ($overloadedCount > 0) {
    $recommendations[] = [
        'type' => 'pink',
        'title' => $overloadedCount . ' Overloaded Team Members',
        'description' => 'Consider redistributing work to prevent burnout and maintain quality',
        'icon' => 'alert-circle'
    ];
}

// Over budget projects (> 90% spent)
$overBudgetCount = 0;
$overBudgetName = '';
$overBudgetRatio = 0;
foreach ($projects as $p) {
    if ($p['budget'] > 0 && ($p['budget_spent'] / $p['budget']) > 0.9) {
        $overBudgetCount++;
        if (empty($overBudgetName)) {
            $overBudgetName = $p['name'];
            $overBudgetRatio = (int)round(($p['budget_spent'] / $p['budget']) * 100);
        }
    }
}
if ($overBudgetCount > 0) {
    $recommendations[] = [
        'type' => 'pink',
        'title' => $overBudgetCount . ' Projects Near Budget Limit',
        'description' => htmlspecialchars($overBudgetName) . ' is at ' . $overBudgetRatio . '% of budget',
        'icon' => 'alert-circle'
    ];
}

// Completed projects
$completedCount = 0;
foreach ($projects as $p) {
    if ($p['status'] === 'completed') {
        $completedCount++;
    }
}
if ($completedCount > 0) {
    $recommendations[] = [
        'type' => 'teal',
        'title' => 'Projects Completed',
        'description' => $completedCount . ' project(s) successfully delivered',
        'icon' => 'check-circle'
    ];
}

// Slicing recommendations to 4
$recommendations = array_slice($recommendations, 0, 4);

// Quick Stats Calculation
$totalBudget = 0;
$totalSpent = 0;
$onTimeProjects = 0;
foreach ($projects as $p) {
    $totalBudget += $p['budget'];
    $totalSpent += $p['budget_spent'];
    if (new DateTime($p['deadline']) > new DateTime()) {
        $onTimeProjects++;
    }
}
$budgetUtilization = $totalBudget > 0 ? (int)round(($totalSpent / $totalBudget) * 100) : 0;
$deadlineCompliance = count($projects) > 0 ? (int)round(($onTimeProjects / count($projects)) * 100) : 0;

$totalRating = 0;
foreach ($employees as $emp) {
    $totalRating += (float)$emp['performance_rating'];
}
$avgPerformance = $totalEmployees > 0 ? round($totalRating / $totalEmployees, 1) : 0.0;

$allocatedResourcesCount = 0;
foreach ($employees as $emp) {
    if ((int)$emp['workload_percentage'] > 0) {
        $allocatedResourcesCount++;
    }
}
$resourceEfficiency = $totalEmployees > 0 ? (int)round(($allocatedResourcesCount / $totalEmployees) * 100) : 0;

// SVG coordinates and parameters for Relationship Graph
$clientY = 60;
$projectY = 180;
$employeeY = 300;
$padding = 80;
$nodeRadius = 18;
$viewBoxWidth = 800;
$viewBoxHeight = 360;

// Gather nodes
$clientsNodeList = [];
$projectsNodeList = [];
$employeesNodeList = [];
$edgesList = [];

// Calculate client spacing
$clientsCount = count($clients);
$clientSpacing = $clientsCount > 1 ? ($viewBoxWidth - $padding * 2) / ($clientsCount - 1) : 0;
foreach ($clients as $i => $c) {
    $clientsNodeList[$c['id']] = [
        'id' => 'c-' . $c['id'],
        'name' => $c['company_name'],
        'initials' => strtoupper(substr($c['company_name'], 0, 2)),
        'x' => $clientsCount === 1 ? 400 : $padding + $i * $clientSpacing,
        'y' => $clientY,
        'type' => 'client'
    ];
}

// Calculate project spacing
$projectsCount = count($projects);
$projectSpacing = $projectsCount > 1 ? ($viewBoxWidth - $padding * 2) / ($projectsCount - 1) : 0;
foreach ($projects as $i => $p) {
    $projectsNodeList[$p['id']] = [
        'id' => 'p-' . $p['id'],
        'name' => $p['name'],
        'initials' => strtoupper(substr($p['name'], 0, 2)),
        'x' => $projectsCount === 1 ? 400 : $padding + $i * $projectSpacing,
        'y' => $projectY,
        'type' => 'project',
        'status' => $p['status']
    ];

    if ($p['client_id'] && isset($clientsNodeList[$p['client_id']])) {
        $edgesList[] = [
            'from' => 'c-' . $p['client_id'],
            'to' => 'p-' . $p['id'],
            'from_x' => $clientsNodeList[$p['client_id']]['x'],
            'from_y' => $clientY,
            'to_x' => $projectsNodeList[$p['id']]['x'],
            'to_y' => $projectY,
            'type' => 'client-project'
        ];
    }
}

// Calculate employee spacing
$employeesCount = count($employees);
$employeeSpacing = $employeesCount > 1 ? ($viewBoxWidth - $padding * 2) / ($employeesCount - 1) : 0;
foreach ($employees as $i => $emp) {
    $employeesNodeList[$emp['id']] = [
        'id' => 'e-' . $emp['id'],
        'name' => $emp['first_name'] . ' ' . $emp['last_name'],
        'initials' => strtoupper(substr($emp['first_name'], 0, 1) . substr($emp['last_name'], 0, 1)),
        'x' => $employeesCount === 1 ? 400 : $padding + $i * $employeeSpacing,
        'y' => $employeeY,
        'type' => 'employee',
        'status' => $emp['availability_status']
    ];

    // Gather active project assignments
    foreach ($emp['assignments'] as $asg) {
        if ($asg['status'] === 'active' && isset($projectsNodeList[$asg['project_id']])) {
            $edgesList[] = [
                'from' => 'p-' . $asg['project_id'],
                'to' => 'e-' . $emp['id'],
                'from_x' => $projectsNodeList[$asg['project_id']]['x'],
                'from_y' => $projectY,
                'to_x' => $employeesNodeList[$emp['id']]['x'],
                'to_y' => $employeeY,
                'type' => 'project-employee',
                'role' => $asg['role']
            ];
        }
    }
}

// Flatten all nodes to simplify rendering
$allNodes = array_merge(
    array_values($clientsNodeList),
    array_values($projectsNodeList),
    array_values($employeesNodeList)
);
?>

<div class="space-y-8">
    <!-- Welcome Header Banner -->
    <div class="relative px-6 py-8 rounded-2xl glass-dark border border-white/5 overflow-hidden flex flex-col md:flex-row justify-between items-start md:items-center">
        <!-- Pulse dots -->
        <div class="absolute inset-0 pointer-events-none opacity-20">
            <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-purple-500 rounded-full filter blur-3xl glow-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-72 h-72 bg-cyan-500 rounded-full filter blur-3xl glow-pulse" style="animation-delay:1s"></div>
        </div>
        
        <div class="relative z-10">
            <h1 class="text-3xl lg:text-4xl font-extrabold font-outfit text-slate-100 tracking-tight">
                Welcome back, <span class="text-gradient font-bold"><?php echo htmlspecialchars($adminUser['name']); ?></span>
            </h1>
            <p class="text-slate-400 text-sm mt-1">Manage your architecture studio efficiently with ArchiNexus.</p>
        </div>

        <div class="mt-4 md:mt-0 relative z-10 flex items-center gap-3 bg-slate-900/50 px-4 py-2 rounded-xl border border-white/10">
            <div class="w-9 h-9 rounded-lg overflow-hidden border border-cyan-500/30">
                <img src="<?php echo htmlspecialchars($adminUser['avatarUrl']); ?>" alt="Admin Profile" class="w-full h-full object-cover">
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-200"><?php echo htmlspecialchars($adminUser['name']); ?></p>
                <div class="flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span class="text-[10px] text-emerald-400 font-medium">Online</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium KPI Cards Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- KPI 1 -->
        <div class="glass-premium rounded-2xl p-6 relative overflow-hidden group hover:border-cyan-500/50 transition-all duration-300">
            <div class="absolute right-3 top-3 w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400 group-hover:scale-110 transition-transform">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Employees</p>
            <h3 class="text-4xl font-bold font-outfit text-slate-50 mt-2"><?php echo $totalEmployees; ?></h3>
            <p class="text-[10px] text-emerald-400 mt-2 font-medium flex items-center gap-1">
                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> +5% capacity growth
            </p>
        </div>

        <!-- KPI 2 -->
        <div class="glass-premium-cyan rounded-2xl p-6 relative overflow-hidden group hover:border-cyan-400/50 transition-all duration-300">
            <div class="absolute right-3 top-3 w-12 h-12 rounded-xl bg-cyan-500/10 flex items-center justify-center text-cyan-400 group-hover:scale-110 transition-transform">
                <i data-lucide="folder-kanban" class="w-6 h-6"></i>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active Projects</p>
            <h3 class="text-4xl font-bold font-outfit text-slate-50 mt-2"><?php echo $activeProjects; ?></h3>
            <p class="text-[10px] text-emerald-400 mt-2 font-medium flex items-center gap-1">
                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> +3 planning pipeline
            </p>
        </div>

        <!-- KPI 3 -->
        <div class="glass-premium-pink rounded-2xl p-6 relative overflow-hidden group hover:border-pink-500/50 transition-all duration-300">
            <div class="absolute right-3 top-3 w-12 h-12 rounded-xl bg-pink-500/10 flex items-center justify-center text-pink-400 group-hover:scale-110 transition-transform">
                <i data-lucide="building-2" class="w-6 h-6"></i>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Clients</p>
            <h3 class="text-4xl font-bold font-outfit text-slate-50 mt-2"><?php echo $totalClients; ?></h3>
            <p class="text-[10px] text-emerald-400 mt-2 font-medium flex items-center gap-1">
                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i> +8 prospective inquiries
            </p>
        </div>

        <!-- KPI 4 -->
        <div class="glass-premium-teal rounded-2xl p-6 relative overflow-hidden group hover:border-teal-500/50 transition-all duration-300">
            <div class="absolute right-3 top-3 w-12 h-12 rounded-xl bg-teal-500/10 flex items-center justify-center text-teal-400 group-hover:scale-110 transition-transform">
                <i data-lucide="zap" class="w-6 h-6"></i>
            </div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Avg Workload</p>
            <h3 class="text-4xl font-bold font-outfit text-slate-50 mt-2"><?php echo $avgWorkload; ?>%</h3>
            <p class="text-[10px] text-slate-400 mt-2 font-medium">
                % active studio capacity
            </p>
        </div>
    </div>

    <!-- Main Graphs Grid (Two columns) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Side Column (Active Projects and Workload Chart) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Active Projects Panel -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5">
                <h3 class="text-xl font-bold font-outfit text-slate-50 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-cyan-400 rounded-full"></span> Active Projects
                </h3>
                <div class="space-y-4">
                    <?php
                    $renderedProj = 0;
                    foreach ($projects as $project):
                        if ($project['status'] !== 'in_progress') continue;
                        $renderedProj++;
                        if ($renderedProj > 5) break;
                        
                        $isNearBudget = ($project['budget'] > 0 && ($project['budget_spent'] / $project['budget']) > 0.9);
                        $budgetRatioPercent = $project['budget'] > 0 ? (int)round(($project['budget_spent'] / $project['budget']) * 100) : 0;
                    ?>
                        <div class="pb-4 border-b border-white/5 last:border-b-0">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-200"><?php echo htmlspecialchars($project['name']); ?></h4>
                                    <p class="text-[10px] text-slate-400 mt-0.5"><?php echo htmlspecialchars($project['client']['company_name'] ?? 'No client assigned'); ?></p>
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-slate-400">Progress</span>
                                    <span class="font-bold text-slate-200"><?php echo $project['progress_percentage']; ?>%</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-800 rounded-full overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-cyan-400 to-purple-500 rounded-full" style="width: <?php echo $project['progress_percentage']; ?>%"></div>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="text-slate-400">
                                    Budget Spent: ₹<?php echo number_format($project['budget_spent'] / 100000, 1); ?>L / ₹<?php echo number_format($project['budget'] / 100000, 1); ?>L
                                </span>
                                <span class="font-bold <?php echo $isNearBudget ? 'text-pink-400 animate-pulse' : 'text-teal-400'; ?>">
                                    <?php echo $budgetRatioPercent; ?>% utilization
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if ($renderedProj === 0): ?>
                        <p class="text-center text-slate-500 py-6 text-sm">No active projects currently.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Team Workload Column Chart -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5">
                <h3 class="text-xl font-bold font-outfit text-slate-50 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-purple-500 rounded-full"></span> Team Workload
                </h3>
                <div id="workload-chart" class="w-full" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Right Side Column (Availability and AI Recommendations) -->
        <div class="space-y-6">
            <!-- Employee Availability Chart -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5 flex flex-col">
                <h3 class="text-xl font-bold font-outfit text-slate-50 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-pink-500 rounded-full"></span> Studio Availability
                </h3>
                <div id="availability-chart" class="w-full flex-1 flex items-center justify-center" style="min-height: 200px;"></div>
                
                <div class="mt-4 space-y-2 text-xs">
                    <div class="flex items-center justify-between py-1 border-b border-white/5">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-teal-500"></span>
                            <span class="text-slate-400">Available</span>
                        </div>
                        <span class="font-bold text-slate-200"><?php echo $availabilityCounts['available']; ?></span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-white/5">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            <span class="text-slate-400">Busy</span>
                        </div>
                        <span class="font-bold text-slate-200"><?php echo $availabilityCounts['busy']; ?></span>
                    </div>
                    <div class="flex items-center justify-between py-1 border-b border-white/5">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-pink-500"></span>
                            <span class="text-slate-400">On Leave</span>
                        </div>
                        <span class="font-bold text-slate-200"><?php echo $availabilityCounts['on_leave']; ?></span>
                    </div>
                    <div class="flex items-center justify-between py-1">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span>
                            <span class="text-slate-400">Offline</span>
                        </div>
                        <span class="font-bold text-slate-200"><?php echo $availabilityCounts['offline']; ?></span>
                    </div>
                </div>
            </div>

            <!-- AI Recommendations Card -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5">
                <h3 class="text-xl font-bold font-outfit text-slate-50 mb-4 flex items-center gap-2">
                    <span class="w-1 h-5 bg-teal-500 rounded-full"></span> AI Recommendations
                </h3>
                <div class="space-y-3">
                    <?php foreach ($recommendations as $rec): 
                        $glowColor = $rec['type'] === 'pink' ? 'text-pink-400 bg-pink-500/10 border-pink-500/20' : ($rec['type'] === 'cyan' ? 'text-cyan-400 bg-cyan-500/10 border-cyan-500/20' : 'text-teal-400 bg-teal-500/10 border-teal-500/20');
                    ?>
                        <div class="p-3.5 rounded-xl bg-slate-900/40 border border-white/5 flex gap-3 hover:border-slate-800 transition-colors">
                            <div class="p-2 rounded-lg flex-shrink-0 self-start <?php echo $glowColor; ?> border">
                                <i data-lucide="<?php echo $rec['icon']; ?>" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-200 text-xs leading-none mb-1"><?php echo htmlspecialchars($rec['title']); ?></h4>
                                <p class="text-[10px] text-slate-400 leading-tight"><?php echo htmlspecialchars($rec['description']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($recommendations)): ?>
                        <p class="text-center text-slate-500 py-6 text-sm">All metrics are optimal.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Relationship Graph SVG Component -->
    <div class="glass-dark rounded-2xl p-6 border border-white/5">
        <div class="mb-4">
            <h3 class="text-xl font-bold font-outfit text-slate-50 mb-1 flex items-center gap-2">
                <span class="w-1 h-5 bg-cyan-400 rounded-full"></span> Project Relationships
            </h3>
            <p class="text-slate-400 text-xs">Connections between Clients, Projects, and Active Employees</p>
        </div>

        <!-- Legend -->
        <div class="flex items-center gap-4 mb-4 text-[10px]">
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-amber-500/20 border border-amber-500 shadow-md shadow-amber-500/10" /></div>
                <span class="text-slate-300 font-medium">Client</span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-cyan-500/20 border border-cyan-500 shadow-md shadow-cyan-500/10" /></div>
                <span class="text-slate-300 font-medium">Project</span>
            </div>
            <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-emerald-500/20 border border-emerald-500 shadow-md shadow-emerald-500/10" /></div>
                <span class="text-slate-300 font-medium">Employee</span>
            </div>
        </div>

        <!-- SVG Container -->
        <div class="w-full overflow-x-auto">
            <svg viewBox="0 0 <?php echo $viewBoxWidth; ?> <?php echo $viewBoxHeight; ?>" class="w-full min-w-[700px] h-auto select-none" id="relationship-svg">
                <!-- Drop shadow filter -->
                <defs>
                    <filter id="node-shadow" x="-30%" y="-30%" width="160%" height="160%">
                        <feDropShadow dx="0" dy="2" stdDeviation="3" flood-color="#000" flood-opacity="0.5" />
                    </filter>
                </defs>

                <!-- Edges -->
                <?php foreach ($edgesList as $edge): 
                    $strokeColor = $edge['type'] === 'client-project' ? '#f59e0b' : '#06b6d4';
                    $dash = $edge['type'] === 'client-project' ? '4 3' : 'none';
                ?>
                    <g class="relationship-edge" data-from="<?php echo $edge['from']; ?>" data-to="<?php echo $edge['to']; ?>">
                        <line 
                            x1="<?php echo $edge['from_x']; ?>" y1="<?php echo $edge['from_y']; ?>" 
                            x2="<?php echo $edge['to_x']; ?>" y2="<?php echo $edge['to_y']; ?>" 
                            stroke="<?php echo $strokeColor; ?>" 
                            stroke-width="<?php echo $edge['type'] === 'client-project' ? 1.5 : 2; ?>" 
                            stroke-dasharray="<?php echo $dash; ?>" 
                            opacity="0.3" 
                            class="transition-all duration-300"
                        />
                        <?php if (isset($edge['role'])): ?>
                            <rect 
                                x="<?php echo ($edge['from_x'] + $edge['to_x'])/2 - 35; ?>" 
                                y="<?php echo ($edge['from_y'] + $edge['to_y'])/2 - 7; ?>" 
                                width="70" height="12" rx="4" 
                                fill="#0f172a" stroke="rgba(255,255,255,0.05)" stroke-width="1"
                                class="edge-label-bg" opacity="0"
                            />
                            <text 
                                x="<?php echo ($edge['from_x'] + $edge['to_x'])/2; ?>" 
                                y="<?php echo ($edge['from_y'] + $edge['to_y'])/2 + 2; ?>" 
                                text-anchor="middle" 
                                fill="#94a3b8" 
                                font-size="8px"
                                font-weight="500"
                                class="edge-label transition-all duration-300" 
                                opacity="0"
                            >
                                <?php echo htmlspecialchars($edge['role']); ?>
                            </text>
                        <?php endif; ?>
                    </g>
                <?php endforeach; ?>

                <!-- Nodes -->
                <?php foreach ($allNodes as $node): 
                    $fill = '#1e293b';
                    $stroke = '#64748b';
                    $shadowGlow = 'none';

                    if ($node['type'] === 'client') {
                        $fill = 'rgba(245, 158, 11, 0.15)';
                        $stroke = '#f59e0b';
                        $shadowGlow = 'rgba(245, 158, 11, 0.2)';
                    } elseif ($node['type'] === 'project') {
                        $fill = 'rgba(6, 182, 212, 0.15)';
                        $stroke = '#06b6d4';
                        $shadowGlow = 'rgba(6, 182, 212, 0.2)';
                    } elseif ($node['type'] === 'employee') {
                        $status = $node['status'] ?? 'available';
                        if ($status === 'available') {
                            $fill = 'rgba(34, 197, 94, 0.15)';
                            $stroke = '#22c55e';
                            $shadowGlow = 'rgba(34, 197, 94, 0.2)';
                        } elseif ($status === 'busy') {
                            $fill = 'rgba(147, 51, 234, 0.15)';
                            $stroke = '#9333ea';
                            $shadowGlow = 'rgba(147, 51, 234, 0.2)';
                        } elseif ($status === 'on_leave') {
                            $fill = 'rgba(236, 72, 153, 0.15)';
                            $stroke = '#ec4899';
                            $shadowGlow = 'rgba(236, 72, 153, 0.2)';
                        }
                    }
                ?>
                    <g 
                        class="relationship-node cursor-pointer transition-all duration-300" 
                        id="node-<?php echo $node['id']; ?>"
                        data-id="<?php echo $node['id']; ?>"
                        onmouseenter="handleNodeHover('<?php echo $node['id']; ?>', true)"
                        onmouseleave="handleNodeHover('<?php echo $node['id']; ?>', false)"
                    >
                        <circle 
                            cx="<?php echo $node['x']; ?>" 
                            cy="<?php echo $node['y']; ?>" 
                            r="<?php echo $nodeRadius; ?>" 
                            fill="<?php echo $fill; ?>" 
                            stroke="<?php echo $stroke; ?>" 
                            stroke-width="2" 
                            filter="url(#node-shadow)"
                            class="transition-all duration-300"
                        />
                        <text 
                            x="<?php echo $node['x']; ?>" 
                            y="<?php echo $node['y'] + 1; ?>" 
                            text-anchor="middle" 
                            dominant-baseline="central" 
                            fill="#f1f5f9" 
                            font-size="9px" 
                            font-weight="bold"
                            class="pointer-events-none"
                        >
                            <?php echo htmlspecialchars($node['initials']); ?>
                        </text>
                        <!-- Node label below node -->
                        <text 
                            x="<?php echo $node['x']; ?>" 
                            y="<?php echo $node['y'] + $nodeRadius + 12; ?>" 
                            text-anchor="middle" 
                            fill="#cbd5e1" 
                            font-size="7.5px"
                            font-weight="500"
                            class="pointer-events-none transition-colors duration-300 text-node-label"
                        >
                            <?php 
                            $trimmedName = strlen($node['name']) > 15 ? substr($node['name'], 0, 13) . '..' : $node['name'];
                            echo htmlspecialchars($trimmedName); 
                            ?>
                        </text>
                    </g>
                <?php endforeach; ?>
            </svg>
        </div>
    </div>

    <!-- Quick Stats Dial Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Radial 1 -->
        <div class="glass-premium rounded-2xl p-6 flex flex-col justify-between hover:border-purple-500/30 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Budget Utilization</p>
                <i data-lucide="target" class="w-5 h-5 text-purple-400"></i>
            </div>
            <div class="flex justify-center py-2">
                <?php echo render_progress_ring($budgetUtilization, 90, 'purple'); ?>
            </div>
        </div>

        <!-- Radial 2 -->
        <div class="glass-premium-cyan rounded-2xl p-6 flex flex-col justify-between hover:border-cyan-500/30 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Deadline Compliance</p>
                <i data-lucide="clock" class="w-5 h-5 text-cyan-400"></i>
            </div>
            <div class="flex justify-center py-2">
                <?php echo render_progress_ring($deadlineCompliance, 90, 'cyan'); ?>
            </div>
        </div>

        <!-- Metric 3 -->
        <div class="glass-premium-pink rounded-2xl p-6 flex flex-col justify-between hover:border-pink-500/30 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Avg Performance</p>
                <i data-lucide="check-circle" class="w-5 h-5 text-pink-400"></i>
            </div>
            <div class="flex flex-col items-center justify-center py-4 text-center">
                <span class="text-4xl font-extrabold text-slate-50 font-outfit"><?php echo $avgPerformance; ?></span>
                <span class="text-[10px] text-slate-400 mt-2">out of 5.0 rating</span>
            </div>
        </div>

        <!-- Radial 4 -->
        <div class="glass-premium-teal rounded-2xl p-6 flex flex-col justify-between hover:border-teal-500/30 transition-all duration-300">
            <div class="flex items-center justify-between mb-4">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Resource Efficiency</p>
                <i data-lucide="zap" class="w-5 h-5 text-teal-400"></i>
            </div>
            <div class="flex justify-center py-2">
                <?php echo render_progress_ring($resourceEfficiency, 90, 'teal'); ?>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Data mapping for Workload Chart (Top 8 highest capacity)
    const workloadCategories = <?php echo safe_json_encode(array_map(function($e){
        return htmlspecialchars($e['first_name'] . ' ' . substr($e['last_name'], 0, 1) . '.');
    }, $topWorkloadEmployees)); ?>;

    const workloadData = <?php echo safe_json_encode(array_map(function($e){
        return (int)$e['workload_percentage'];
    }, $topWorkloadEmployees)); ?>;

    const workloadOptions = {
        series: [{
            name: 'Workload',
            data: workloadData
        }],
        chart: {
            type: 'bar',
            height: 280,
            toolbar: { show: false },
            background: 'transparent'
        },
        plotOptions: {
            bar: {
                borderRadius: 6,
                columnWidth: '45%',
                distributed: true
            }
        },
        colors: ['#9333ea', '#06b6d4', '#ec4899', '#14b8a6', '#8b5cf6', '#3b82f6', '#f43f5e', '#10b981'],
        dataLabels: {
            enabled: false
        },
        legend: {
            show: false
        },
        theme: {
            mode: 'dark'
        },
        grid: {
            borderColor: 'rgba(255,255,255,0.06)',
            strokeDashArray: 3,
            yaxis: { lines: { show: true } }
        },
        xaxis: {
            categories: workloadCategories,
            labels: {
                style: {
                    colors: '#94a3b8',
                    fontSize: '11px'
                }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            max: 100,
            labels: {
                formatter: function (val) {
                    return val + "%";
                },
                style: {
                    colors: '#94a3b8'
                }
            }
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + "% Capacity";
                }
            }
        }
    };
    
    // 2. Data mapping for Availability Donut Chart
    const availabilityOptions = {
        series: [
            <?php echo $availabilityCounts['available']; ?>,
            <?php echo $availabilityCounts['busy']; ?>,
            <?php echo $availabilityCounts['on_leave']; ?>,
            <?php echo $availabilityCounts['offline']; ?>
        ],
        chart: {
            type: 'donut',
            height: 220,
            background: 'transparent'
        },
        labels: ['Available', 'Busy', 'On Leave', 'Offline'],
        colors: ['#14b8a6', '#9333ea', '#ec4899', '#64748b'],
        plotOptions: {
            pie: {
                donut: {
                    size: '72%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '12px',
                            color: '#94a3b8'
                        },
                        value: {
                            show: true,
                            fontSize: '20px',
                            fontWeight: 'bold',
                            color: '#f8fafc',
                            formatter: function (val) {
                                return val;
                            }
                        },
                        total: {
                            show: true,
                            label: 'Total Team',
                            color: '#94a3b8',
                            fontSize: '11px',
                            formatter: function (w) {
                                return <?php echo $totalEmployees; ?>;
                            }
                        }
                    }
                }
            }
        },
        dataLabels: {
            enabled: false
        },
        legend: {
            show: false
        },
        theme: {
            mode: 'dark'
        },
        stroke: {
            show: true,
            colors: ['#0f172a'],
            width: 3
        }
    };

    // Render Charts
    document.addEventListener("DOMContentLoaded", function() {
        const workloadChart = new ApexCharts(document.querySelector("#workload-chart"), workloadOptions);
        workloadChart.render();

        const availabilityChart = new ApexCharts(document.querySelector("#availability-chart"), availabilityOptions);
        availabilityChart.render();
    });

    // 3. Node hover effects in Relationship Graph SVG
    function handleNodeHover(nodeId, isHovered) {
        const svg = document.getElementById('relationship-svg');
        const edges = svg.querySelectorAll('.relationship-edge');
        const nodes = svg.querySelectorAll('.relationship-node');

        if (isHovered) {
            // Fade out unrelated elements and highlight related edges
            nodes.forEach(node => {
                const id = node.getAttribute('data-id');
                if (id !== nodeId) {
                    node.style.opacity = '0.25';
                }
            });

            edges.forEach(edge => {
                const from = edge.getAttribute('data-from');
                const to = edge.getAttribute('data-to');
                
                if (from === nodeId || to === nodeId) {
                    edge.querySelector('line').setAttribute('opacity', '1.0');
                    edge.querySelector('line').setAttribute('stroke-width', '3');
                    
                    // Show role labels on line hover
                    const textLabel = edge.querySelector('.edge-label');
                    const textBg = edge.querySelector('.edge-label-bg');
                    if (textLabel) textLabel.setAttribute('opacity', '1.0');
                    if (textBg) textBg.setAttribute('opacity', '0.9');
                } else {
                    edge.querySelector('line').setAttribute('opacity', '0.05');
                }
            });
        } else {
            // Restore default view states
            nodes.forEach(node => {
                node.style.opacity = '1.0';
            });
            edges.forEach(edge => {
                edge.querySelector('line').setAttribute('opacity', '0.3');
                edge.querySelector('line').setAttribute('stroke-width', edge.getAttribute('data-to').startsWith('e-') ? '2' : '1.5');
                
                const textLabel = edge.querySelector('.edge-label');
                const textBg = edge.querySelector('.edge-label-bg');
                if (textLabel) textLabel.setAttribute('opacity', '0');
                if (textBg) textBg.setAttribute('opacity', '0');
            });
        }
    }
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
