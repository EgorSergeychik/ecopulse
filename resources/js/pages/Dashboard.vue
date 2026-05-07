<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { Bar, Doughnut } from 'vue-chartjs';
import {
    ArcElement,
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Title,
    Tooltip,
} from 'chart.js';
import { Activity, BatteryWarning, Bot, TriangleAlert } from 'lucide-vue-next';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard } from '@/routes';
import { show as zoneShow } from '@/routes/zones';

ChartJS.register(CategoryScale, LinearScale, BarElement, ArcElement, Title, Tooltip, Legend);

interface Stats {
    total_robots: number;
    active_robots: number;
    problem_robots: number;
    unresolved_incidents: number;
}

interface ProblemRobot {
    id: number;
    name: string;
    status: string;
    status_label: string;
    battery_pct: number;
    zone_id: number | null;
    zone_name: string | null;
}

interface RecentIncident {
    id: number;
    type: string;
    type_label: string;
    severity: 'warning' | 'critical' | 'fatal';
    severity_label: string;
    description: string;
    robot_name: string | null;
    zone_id: number | null;
    zone_name: string | null;
    created_at: string;
}

interface TrendRow {
    date: string;
    severity: string;
    count: number;
}

const props = defineProps<{
    stats: Stats;
    problem_robots: ProblemRobot[];
    recent_incidents: RecentIncident[];
    incident_trend: TrendRow[];
    severity_counts: Record<string, number>;
}>();

const { t, locale } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'pages.dashboard.title', href: dashboard() }],
    },
});

function severityClass(severity: string): string {
    if (severity === 'fatal') return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
    if (severity === 'critical') return 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300';
    return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300';
}

function statusClass(status: string): string {
    if (status === 'error') return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
    return 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300';
}

const last7Days = computed(() =>
    Array.from({ length: 7 }, (_, i) => {
        const d = new Date();
        d.setDate(d.getDate() - (6 - i));
        return d.toISOString().split('T')[0];
    }),
);

const trendChartData = computed(() => {
    const grouped: Record<string, Record<string, number>> = {};
    for (const row of props.incident_trend) {
        if (!grouped[row.date]) grouped[row.date] = {};
        grouped[row.date][row.severity] = row.count;
    }

    const severities = ['warning', 'critical', 'fatal'] as const;
    const colors = { warning: '#eab308', critical: '#f97316', fatal: '#ef4444' };
    const dateLocale = locale.value === 'uk' ? 'uk-UA' : 'en-US';

    return {
        labels: last7Days.value.map((d) =>
            new Date(d + 'T00:00:00').toLocaleDateString(dateLocale, { month: 'short', day: 'numeric' }),
        ),
        datasets: severities.map((sev) => ({
            label: t(`common.severity.${sev}`),
            data: last7Days.value.map((d) => grouped[d]?.[sev] ?? 0),
            backgroundColor: colors[sev],
        })),
    };
});

const severityChartData = computed(() => ({
    labels: (['warning', 'critical', 'fatal'] as const).map((s) => t(`common.severity.${s}`)),
    datasets: [
        {
            data: ['warning', 'critical', 'fatal'].map((s) => props.severity_counts[s] ?? 0),
            backgroundColor: ['#eab308', '#f97316', '#ef4444'],
            borderWidth: 0,
        },
    ],
}));

const barOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' as const } },
    scales: {
        x: { stacked: true },
        y: { stacked: true, beginAtZero: true, ticks: { stepSize: 1 } },
    },
};

const doughnutOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { position: 'bottom' as const } },
};
</script>

<template>
    <Head :title="t('pages.dashboard.title')" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <!-- Stat cards -->
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ t('pages.dashboard.stats.total_robots') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-bold">{{ stats.total_robots }}</span>
                        <Bot class="mb-1 size-5 text-muted-foreground" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ t('pages.dashboard.stats.active_robots') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-end gap-2">
                        <span class="text-3xl font-bold text-green-600 dark:text-green-400">{{ stats.active_robots }}</span>
                        <Activity class="mb-1 size-5 text-green-600 dark:text-green-400" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ t('pages.dashboard.stats.problem_robots') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-end gap-2">
                        <span
                            class="text-3xl font-bold"
                            :class="stats.problem_robots > 0 ? 'text-orange-500' : ''"
                        >{{ stats.problem_robots }}</span>
                        <BatteryWarning class="mb-1 size-5 text-muted-foreground" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium text-muted-foreground">
                        {{ t('pages.dashboard.stats.unresolved_incidents') }}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex items-end gap-2">
                        <span
                            class="text-3xl font-bold"
                            :class="stats.unresolved_incidents > 0 ? 'text-red-500' : ''"
                        >{{ stats.unresolved_incidents }}</span>
                        <TriangleAlert class="mb-1 size-5 text-muted-foreground" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Operational row -->
        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('pages.dashboard.problem_robots.title') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p v-if="problem_robots.length === 0" class="text-sm text-muted-foreground">
                        {{ t('pages.dashboard.problem_robots.empty') }}
                    </p>
                    <ul v-else class="divide-y">
                        <li
                            v-for="robot in problem_robots"
                            :key="robot.id"
                            class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ robot.name }}</p>
                                <p v-if="robot.zone_name && robot.zone_id" class="text-xs text-muted-foreground">
                                    <Link :href="zoneShow(robot.zone_id).url" class="hover:underline">
                                        {{ robot.zone_name }}
                                    </Link>
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <span class="text-xs text-muted-foreground">{{ robot.battery_pct }}%</span>
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="statusClass(robot.status)"
                                >{{ robot.status_label }}</span>
                            </div>
                        </li>
                    </ul>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('pages.dashboard.recent_incidents.title') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <p v-if="recent_incidents.length === 0" class="text-sm text-muted-foreground">
                        {{ t('pages.dashboard.recent_incidents.empty') }}
                    </p>
                    <ul v-else class="divide-y">
                        <li
                            v-for="incident in recent_incidents"
                            :key="incident.id"
                            class="flex items-start justify-between gap-3 py-3 first:pt-0 last:pb-0"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ incident.type_label }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ incident.robot_name
                                    }}<template v-if="incident.zone_name && incident.zone_id">
                                        ·
                                        <Link :href="zoneShow(incident.zone_id).url" class="hover:underline">{{
                                            incident.zone_name
                                        }}</Link>
                                    </template>
                                </p>
                            </div>
                            <span
                                class="mt-0.5 inline-flex shrink-0 items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="severityClass(incident.severity)"
                            >{{ incident.severity_label }}</span>
                        </li>
                    </ul>
                </CardContent>
            </Card>
        </div>

        <!-- Charts row -->
        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('pages.dashboard.charts.incident_trend') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="relative h-52">
                        <Bar :data="trendChartData" :options="barOptions" />
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('pages.dashboard.charts.severity_breakdown') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <div v-if="stats.unresolved_incidents === 0" class="flex h-52 items-center justify-center">
                        <p class="text-sm text-muted-foreground">
                            {{ t('pages.dashboard.recent_incidents.empty') }}
                        </p>
                    </div>
                    <div v-else class="relative h-52">
                        <Doughnut :data="severityChartData" :options="doughnutOptions" />
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
