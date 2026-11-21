<?php
// reports.php
require_once __DIR__ . '/includes/header.php';

$employees = get_employees();
$projects = get_projects();

// 1. Calculate key metrics
$totalBudget = 0;
$totalSpent = 0;
$activeProjectsCount = 0;
$totalProgressSum = 0;
$projectsCount = count($projects);

foreach ($projects as $p) {
    $totalBudget += $p['budget'];
    $totalSpent += $p['budget_spent'];
    $totalProgressSum += $p['progress_percentage'];
    if ($p['status'] === 'in_progress') {
        $activeProjectsCount++;
    }
}
$avgProgress = $projectsCount > 0 ? (int)round($totalProgressSum / $projectsCount) : 0;

$totalWorkload = 0;
foreach ($employees as $e) {
    $totalWorkload += $e['workload_percentage'];
}
$teamEfficiency = count($employees) > 0 ? (int)round($totalWorkload / count($employees)) : 0;

// 2. Prepare Budget Overview columns data
$budgetChartProjects = [];
$budgetChartTotals = [];
$budgetChartSpents = [];

foreach (array_slice($projects, 0, 8) as $p) {
    $budgetChartProjects[] = strlen($p['name']) > 15 ? substr($p['name'], 0, 15) . '..' : $p['name'];
    $budgetChartTotals[] = (int)($p['budget'] / 1000); // in thousands
    $budgetChartSpents[] = (int)($p['budget_spent'] / 1000); // in thousands
}

// 3. Prepare Top 5 active projects progress
$activeProjectsProgress = [];
$activeCount = 0;
foreach ($projects as $p) {
    if ($p['status'] !== 'completed' && $p['status'] !== 'cancelled') {
        $activeProjectsProgress[] = $p;
        $activeCount++;
        if ($activeCount >= 5) break;
    }
}

// 4. Prepare Department Performance area chart data (avg workload)
$deptMap = [];
foreach ($employees as $emp) {
    $deptName = $emp['department']['name'] ?? 'General';
    if (!isset($deptMap[$deptName])) {
        $deptMap[$deptName] = ['sum' => 0, 'count' => 0];
    }
    $deptMap[$deptName]['sum'] += $emp['workload_percentage'];
    $deptMap[$deptName]['count']++;
}
$deptNames = [];
$deptWorkloads = [];
foreach ($deptMap as $name => $data) {
    $deptNames[] = $name;
    $deptWorkloads[] = (int)round($data['sum'] / $data['count']);
}

// 5. Project Status Donut counts
$statusCounts = [
    'planning' => 0,
    'in_progress' => 0,
    'review' => 0,
    'completed' => 0,
    'on_hold' => 0,
    'cancelled' => 0
];
foreach ($projects as $p) {
    $st = $p['status'];
    if (isset($statusCounts[$st])) {
        $statusCounts[$st]++;
    }
}
$statusLabels = ['Planning', 'In Progress', 'Review', 'Completed', 'On Hold', 'Cancelled'];
$statusSeries = [
    $statusCounts['planning'],
    $statusCounts['in_progress'],
    $statusCounts['review'],
    $statusCounts['completed'],
    $statusCounts['on_hold'],
    $statusCounts['cancelled']
];

// 6. Availability Donut counts
$availabilityCounts = [
    'available' => 0,
    'busy' => 0,
    'on_leave' => 0,
    'offline' => 0
];
foreach ($employees as $e) {
    $av = $e['availability_status'];
    if (isset($availabilityCounts[$av])) {
        $availabilityCounts[$av]++;
    }
}
$availabilityLabels = ['Available', 'Busy', 'On Leave', 'Offline'];
$availabilitySeries = [
    $availabilityCounts['available'],
    $availabilityCounts['busy'],
    $availabilityCounts['on_leave'],
    $availabilityCounts['offline']
];
?>

<div class="space-y-8 animate-fade-in">
    <!-- Header Banner -->
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-purple-500 to-cyan-500 flex items-center justify-center shadow-lg shadow-purple-500/20">
            <i data-lucide="bar-chart-3" class="text-white w-6 h-6"></i>
        </div>
        <div>
            <h1 class="text-4xl font-extrabold font-outfit text-slate-50 tracking-tight">Analytics Dashboard</h1>
            <p class="text-slate-400 text-xs mt-1">Real-time analytical graphs and financial utilization sheets</p>
        </div>
    </div>

    <!-- Key Metrics strips -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="rounded-xl p-5 border border-white/5 bg-gradient-to-br from-purple-900/10 to-purple-800/5 hover:border-slate-700 transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <i data-lucide="trending-up" class="w-5 h-5 text-purple-400"></i>
                <span class="text-xs font-bold text-slate-500">+</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Budget</p>
            <h3 class="text-2xl font-bold font-outfit text-slate-50 mt-1">₹<?php echo number_format($totalBudget / 100000, 1); ?>L</h3>
        </div>

        <div class="rounded-xl p-5 border border-white/5 bg-gradient-to-br from-cyan-900/10 to-cyan-800/5 hover:border-slate-700 transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <i data-lucide="target" class="w-5 h-5 text-cyan-400"></i>
                <span class="text-xs font-bold text-slate-500">+</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Avg Progress</p>
            <h3 class="text-2xl font-bold font-outfit text-slate-50 mt-1"><?php echo $avgProgress; ?>%</h3>
        </div>

        <div class="rounded-xl p-5 border border-white/5 bg-gradient-to-br from-pink-900/10 to-pink-800/5 hover:border-slate-700 transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <i data-lucide="zap" class="w-5 h-5 text-pink-400"></i>
                <span class="text-xs font-bold text-slate-500">+</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Active Projects</p>
            <h3 class="text-2xl font-bold font-outfit text-slate-50 mt-1"><?php echo $activeProjectsCount; ?></h3>
        </div>

        <div class="rounded-xl p-5 border border-white/5 bg-gradient-to-br from-teal-900/10 to-teal-800/5 hover:border-slate-700 transition-all duration-300">
            <div class="flex items-center justify-between mb-2">
                <i data-lucide="users" class="w-5 h-5 text-teal-400"></i>
                <span class="text-xs font-bold text-slate-500">+</span>
            </div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Team Efficiency</p>
            <h3 class="text-2xl font-bold font-outfit text-slate-50 mt-1"><?php echo $teamEfficiency; ?>%</h3>
        </div>
    </div>

    <!-- Graphs Columns Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Side Column (2/3 width) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Budget Overview Bar Chart -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5">
                <div class="mb-4">
                    <h3 class="text-lg font-bold font-outfit text-slate-50 flex items-center gap-2">
                        <span class="w-1.5 h-5 bg-purple-500 rounded-full"></span> Budget Overview
                    </h3>
                    <p class="text-slate-400 text-xs mt-1">Comparison between total allocation and actual spent budgets (in ₹ Thousands)</p>
                </div>
                <div id="budget-overview-chart" class="w-full" style="height: 300px;"></div>
            </div>

            <!-- Project Progress bar listings -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5">
                <div class="mb-5">
                    <h3 class="text-lg font-bold font-outfit text-slate-50 flex items-center gap-2">
                        <span class="w-1.5 h-5 bg-cyan-400 rounded-full"></span> Project Progress
                    </h3>
                    <p class="text-slate-400 text-xs mt-1">Current milestone progress ratio of top 5 active projects</p>
                </div>
                
                <div class="space-y-4">
                    <?php foreach ($activeProjectsProgress as $proj): ?>
                        <div class="group">
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-xs font-bold text-slate-300 group-hover:text-slate-100 transition-colors"><?php echo htmlspecialchars($proj['name']); ?></span>
                                <span class="text-[10px] font-bold text-cyan-400 bg-cyan-500/10 px-2 py-0.5 rounded border border-cyan-500/20">
                                    <?php echo $proj['progress_percentage']; ?>%
                                </span>
                            </div>
                            <div class="w-full h-2 bg-slate-900/60 rounded-full overflow-hidden border border-white/5">
                                <div class="h-full bg-gradient-to-r from-purple-500 to-cyan-500 rounded-full shadow-lg shadow-cyan-500/20" style="width: <?php echo $proj['progress_percentage']; ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (count($activeProjectsProgress) === 0): ?>
                        <p class="text-xs text-slate-500 italic">No active projects currently.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Department Workload Area Chart -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5">
                <div class="mb-4">
                    <h3 class="text-lg font-bold font-outfit text-slate-50 flex items-center gap-2">
                        <span class="w-1.5 h-5 bg-pink-500 rounded-full"></span> Department Workload
                    </h3>
                    <p class="text-slate-400 text-xs mt-1">Average capacity workloads recorded within departments</p>
                </div>
                <div id="dept-workload-chart" class="w-full" style="height: 300px;"></div>
            </div>
        </div>

        <!-- Right Side Column (1/3 width) -->
        <div class="space-y-6">
            <!-- Project Status Distribution Donut -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5">
                <h3 class="text-base font-bold font-outfit text-slate-50 mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-purple-500 rounded-full"></span> Project Status
                </h3>
                <div id="status-donut-chart" class="w-full" style="min-height: 220px;"></div>
                
                <div class="mt-4 space-y-2 text-xs">
                    <?php 
                    $colors = ['#9333ea', '#06b6d4', '#ec4899', '#14b8a6', '#f59e0b', '#64748b'];
                    foreach ($statusLabels as $i => $label): 
                        $val = $statusSeries[$i];
                        if ($val === 0) continue;
                    ?>
                        <div class="flex items-center justify-between py-1 border-b border-white/5 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?php echo $colors[$i]; ?>"></span>
                                <span class="text-slate-400"><?php echo $label; ?></span>
                            </div>
                            <span class="font-bold text-slate-200"><?php echo $val; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Team Availability Donut -->
            <div class="glass-dark rounded-2xl p-6 border border-white/5">
                <h3 class="text-base font-bold font-outfit text-slate-50 mb-4 flex items-center gap-2">
                    <span class="w-1.5 h-5 bg-teal-500 rounded-full"></span> Team Availability
                </h3>
                <div id="avail-donut-chart" class="w-full" style="min-height: 220px;"></div>
                
                <div class="mt-4 space-y-2 text-xs">
                    <?php 
                    $colors = ['#14b8a6', '#9333ea', '#ec4899', '#64748b'];
                    foreach ($availabilityLabels as $i => $label): 
                        $val = $availabilitySeries[$i];
                    ?>
                        <div class="flex items-center justify-between py-1 border-b border-white/5 last:border-0">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: <?php echo $colors[$i]; ?>"></span>
                                <span class="text-slate-400"><?php echo $label; ?></span>
                            </div>
                            <span class="font-bold text-slate-200"><?php echo $val; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Budget Overview Data
    const budgetCategories = <?php echo safe_json_encode($budgetChartProjects); ?>;
    const budgetTotals = <?php echo safe_json_encode($budgetChartTotals); ?>;
    const budgetSpents = <?php echo safe_json_encode($budgetChartSpents); ?>;

    const budgetOptions = {
        series: [{
            name: 'Total Budget',
            data: budgetTotals
        }, {
            name: 'Budget Spent',
            data: budgetSpents
        }],
        chart: {
            type: 'bar',
            height: 280,
            toolbar: { show: false },
            background: 'transparent'
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
                borderRadius: 4
            },
        },
        colors: ['#9333ea', '#06b6d4'],
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        theme: { mode: 'dark' },
        grid: {
            borderColor: 'rgba(255,255,255,0.06)',
            strokeDashArray: 3,
            yaxis: { lines: { show: true } }
        },
        xaxis: {
            categories: budgetCategories,
            labels: { style: { colors: '#94a3b8', fontSize: '10px' } }
        },
        yaxis: {
            labels: {
                formatter: function (value) {
                    return "₹" + value + "k";
                },
                style: { colors: '#94a3b8' }
            }
        },
        legend: {
            position: 'top',
            horizontalAlign: 'center',
            labels: { colors: '#94a3b8' }
        },
        fill: { opacity: 0.85 }
    };

    // 2. Department Workload Area Data
    const deptCategories = <?php echo safe_json_encode($deptNames); ?>;
    const deptWorkloads = <?php echo safe_json_encode($deptWorkloads); ?>;

    const deptOptions = {
        series: [{
            name: 'Average Workload',
            data: deptWorkloads
        }],
        chart: {
            type: 'area',
            height: 280,
            toolbar: { show: false },
            background: 'transparent'
        },
        colors: ['#ec4899'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        theme: { mode: 'dark' },
        grid: {
            borderColor: 'rgba(255,255,255,0.06)',
            strokeDashArray: 3
        },
        xaxis: {
            categories: deptCategories,
            labels: { style: { colors: '#94a3b8', fontSize: '10px' } }
        },
        yaxis: {
            max: 100,
            labels: {
                formatter: function(val) { return val + "%"; },
                style: { colors: '#94a3b8' }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.45,
                opacityTo: 0.05,
                stops: [0, 100]
            }
        }
    };

    // 3. Project Status Donut Data
    const statusOptions = {
        series: <?php echo safe_json_encode($statusSeries); ?>,
        chart: { type: 'donut', height: 200, background: 'transparent' },
        labels: <?php echo safe_json_encode($statusLabels); ?>,
        colors: ['#9333ea', '#06b6d4', '#ec4899', '#14b8a6', '#f59e0b', '#64748b'],
        dataLabels: { enabled: false },
        legend: { show: false },
        theme: { mode: 'dark' },
        stroke: { show: true, colors: ['#0f172a'], width: 2 }
    };

    // 4. Team Availability Donut Data
    const availOptions = {
        series: <?php echo safe_json_encode($availabilitySeries); ?>,
        chart: { type: 'donut', height: 200, background: 'transparent' },
        labels: <?php echo safe_json_encode($availabilityLabels); ?>,
        colors: ['#14b8a6', '#9333ea', '#ec4899', '#64748b'],
        dataLabels: { enabled: false },
        legend: { show: false },
        theme: { mode: 'dark' },
        stroke: { show: true, colors: ['#0f172a'], width: 2 }
    };

    // Render Charts
    document.addEventListener("DOMContentLoaded", function() {
        new ApexCharts(document.querySelector("#budget-overview-chart"), budgetOptions).render();
        new ApexCharts(document.querySelector("#dept-workload-chart"), deptOptions).render();
        new ApexCharts(document.querySelector("#status-donut-chart"), statusOptions).render();
        new ApexCharts(document.querySelector("#avail-donut-chart"), availOptions).render();
    });
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
