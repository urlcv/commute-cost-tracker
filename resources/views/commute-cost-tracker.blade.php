{{--
  Commute Cost Tracker — fully client-side Alpine.js tool.
  No server round-trip. All computation happens in the browser.
  Supports multi-leg commutes (e.g. Drive → Train → Walk).
--}}
<div
    x-data="commuteCostTracker()"
    class="space-y-6"
>
    {{-- Comparison mode toggle --}}
    <div class="flex items-center justify-end gap-3">
        <span class="text-sm text-gray-600">Compare two roles</span>
        <button
            type="button"
            @click="comparison = !comparison"
            :class="comparison ? 'bg-primary-600' : 'bg-gray-300'"
            class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
            role="switch"
            :aria-checked="comparison"
        >
            <span
                :class="comparison ? 'translate-x-5' : 'translate-x-0'"
                class="pointer-events-none inline-block h-5 w-5 translate-y-0.5 ml-0.5 rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
            ></span>
        </button>
    </div>

    <div :class="comparison ? 'grid md:grid-cols-2 gap-6' : ''">
        @foreach(['a', 'b'] as $r)
        <div {!! $r === 'b' ? 'x-show="comparison" x-cloak' : '' !!} class="space-y-5">
            <h2 x-show="comparison" class="text-lg font-semibold text-gray-800">
                {{ $r === 'a' ? 'Role A — Office' : 'Role B — Remote / Hybrid' }}
            </h2>

            {{-- Job details --}}
            <fieldset class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                <legend class="text-sm font-semibold text-gray-700 px-1">Job details</legend>

                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Annual salary (&pound;)</label>
                    <input type="number" x-model.number="{{ $r }}.salary" min="0" step="500"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Office days / week</label>
                        <input type="number" x-model.number="{{ $r }}.officeDays" min="0" max="5" step="1"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Work weeks / year</label>
                        <input type="number" x-model.number="{{ $r }}.workWeeks" min="40" max="52" step="1"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                    </div>
                </div>
            </fieldset>

            {{-- Commute legs --}}
            <fieldset x-show="{{ $r }}.officeDays > 0" class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                <legend class="text-sm font-semibold text-gray-700 px-1">Commute legs</legend>

                <p class="text-xs text-gray-400">
                    Model your full door-to-door commute. Add legs for each stage (e.g. drive to station, train, walk to office).
                </p>

                {{-- Journey summary --}}
                <div x-show="{{ $r }}.legs.length > 1" class="rounded-lg bg-primary-50 border border-primary-100 px-3 py-2 text-sm text-primary-700 tabular-nums" x-text="legSummary({{ $r }})">
                </div>

                {{-- Leg cards --}}
                <template x-for="(leg, li) in {{ $r }}.legs" :key="li">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3">
                        {{-- Leg header --}}
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700" x-text="'Leg ' + (li + 1)"></span>
                            <button
                                type="button"
                                x-show="{{ $r }}.legs.length > 1"
                                @click="removeLeg({{ $r }}, li)"
                                class="text-xs text-red-500 hover:text-red-700 font-medium"
                            >Remove</button>
                        </div>

                        {{-- Mode selector --}}
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="m in modes" :key="m.value">
                                <button
                                    type="button"
                                    @click="leg.mode = m.value"
                                    :class="leg.mode === m.value
                                        ? 'bg-primary-600 text-white border-primary-600'
                                        : 'bg-white text-gray-700 border-gray-300 hover:border-primary-400'"
                                    class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors"
                                    x-text="m.label"
                                ></button>
                            </template>
                        </div>

                        {{-- Duration --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Minutes one way</label>
                            <input type="number" x-model.number="leg.minutes" min="0" max="240" step="5"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        {{-- Car fields --}}
                        <div x-show="leg.mode === 'car'" x-cloak class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Distance one way (miles)</label>
                                    <input type="number" x-model.number="leg.distanceMiles" min="0" step="1"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">MPG (UK)</label>
                                    <input type="number" x-model.number="leg.mpg" min="1" step="1"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Fuel cost (&pound;/litre)</label>
                                    <input type="number" x-model.number="leg.fuelCostPerLitre" min="0" step="0.01"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Parking (&pound;/day)</label>
                                    <input type="number" x-model.number="leg.parkingPerDay" min="0" step="0.50"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Tolls (&pound;/day)</label>
                                    <input type="number" x-model.number="leg.tollsPerDay" min="0" step="0.50"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Maintenance (&pound;/mile)</label>
                                    <input type="number" x-model.number="leg.maintenancePerMile" min="0" step="0.01"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Depreciation (&pound;/mile)</label>
                                    <input type="number" x-model.number="leg.depreciationPerMile" min="0" step="0.01"
                                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                                </div>
                                <div></div>
                            </div>
                        </div>

                        {{-- Train fields --}}
                        <div x-show="leg.mode === 'train'" x-cloak>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Ticket cost (&pound;/day)</label>
                            <input type="number" x-model.number="leg.ticketPerDay" min="0" step="0.50"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                            <p class="text-xs text-gray-400 mt-1">Daily return fare — or divide your season ticket by commute days.</p>
                        </div>

                        {{-- Bus fields --}}
                        <div x-show="leg.mode === 'bus'" x-cloak>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Ticket cost (&pound;/day)</label>
                            <input type="number" x-model.number="leg.busTicketPerDay" min="0" step="0.50"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                        </div>

                        {{-- Bike fields --}}
                        <div x-show="leg.mode === 'bike'" x-cloak>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Bike maintenance (&pound;/year)</label>
                            <input type="number" x-model.number="leg.bikeMaintenanceYear" min="0" step="10"
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                            <p class="text-xs text-gray-400 mt-1">Optional — tyres, servicing, etc.</p>
                        </div>

                        {{-- Walk --}}
                        <div x-show="leg.mode === 'walk'" x-cloak>
                            <p class="text-xs text-green-700">No cash cost for this leg — just time.</p>
                        </div>
                    </div>
                </template>

                {{-- Add leg button --}}
                <button
                    type="button"
                    @click="addLeg({{ $r }})"
                    x-show="{{ $r }}.legs.length < 5"
                    class="w-full py-2 text-sm font-medium text-primary-600 border border-dashed border-primary-300 rounded-lg hover:bg-primary-50 transition-colors"
                >+ Add another leg</button>
            </fieldset>

            {{-- Fully remote note --}}
            <div x-show="!{{ $r }}.officeDays || {{ $r }}.officeDays <= 0" x-cloak
                 class="rounded-xl p-4 text-sm bg-green-50 border border-green-200 text-green-800">
                Fully remote — no commute costs to calculate.
            </div>

            {{-- Results --}}
            <div class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-700"
                    x-text="comparison ? '{{ $r === 'a' ? 'Role A' : 'Role B' }} results' : 'Your commute costs'"></h3>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900 tabular-nums" x-text="fmt(calc({{ $r }}).cashCost)"></div>
                        <div class="text-xs text-gray-500 mt-1">Annual cash cost</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900 tabular-nums" x-text="calc({{ $r }}).commuteHours.toFixed(0) + ' hrs'"></div>
                        <div class="text-xs text-gray-500 mt-1">
                            Time per year
                            <span class="block text-gray-400" x-text="'&#8776; ' + calc({{ $r }}).commuteWorkdays.toFixed(1) + ' workdays'"></span>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900 tabular-nums" x-text="fmt(calc({{ $r }}).timeValueCost)"></div>
                        <div class="text-xs text-gray-500 mt-1">Time value cost</div>
                    </div>
                    <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
                        <div class="text-2xl font-bold tabular-nums"
                             :class="calc({{ $r }}).effectiveSalary < {{ $r }}.salary * 0.85 ? 'text-red-600' : 'text-gray-900'"
                             x-text="fmt(calc({{ $r }}).effectiveSalary)"></div>
                        <div class="text-xs text-gray-500 mt-1">Effective salary</div>
                    </div>
                </div>

                {{-- Perspective callout --}}
                <div class="rounded-xl p-4 text-sm bg-amber-50 border border-amber-200 text-amber-800">
                    <span class="font-semibold">Perspective:</span>
                    You spend roughly <span class="font-bold" x-text="calc({{ $r }}).lifeDays.toFixed(1)"></span> full waking days per year commuting
                    <span class="text-xs">(based on 16 waking hours/day)</span>.
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Comparison summary --}}
    <div x-show="comparison" x-cloak class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600"></th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Role A</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Role B</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Delta</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="px-4 py-3 text-gray-700">Gross salary</td>
                        <td class="px-4 py-3 text-right tabular-nums" x-text="fmt(a.salary)"></td>
                        <td class="px-4 py-3 text-right tabular-nums" x-text="fmt(b.salary)"></td>
                        <td class="px-4 py-3 text-right tabular-nums" :class="b.salary - a.salary >= 0 ? 'text-green-600' : 'text-red-600'"
                            x-text="fmtDelta(b.salary - a.salary)"></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-gray-700">Annual cash cost</td>
                        <td class="px-4 py-3 text-right tabular-nums" x-text="fmt(calc(a).cashCost)"></td>
                        <td class="px-4 py-3 text-right tabular-nums" x-text="fmt(calc(b).cashCost)"></td>
                        <td class="px-4 py-3 text-right tabular-nums" :class="calc(b).cashCost - calc(a).cashCost <= 0 ? 'text-green-600' : 'text-red-600'"
                            x-text="fmtDelta(calc(b).cashCost - calc(a).cashCost)"></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-gray-700">Commute hours / year</td>
                        <td class="px-4 py-3 text-right tabular-nums" x-text="calc(a).commuteHours.toFixed(0) + ' hrs'"></td>
                        <td class="px-4 py-3 text-right tabular-nums" x-text="calc(b).commuteHours.toFixed(0) + ' hrs'"></td>
                        <td class="px-4 py-3 text-right tabular-nums" :class="calc(b).commuteHours - calc(a).commuteHours <= 0 ? 'text-green-600' : 'text-red-600'"
                            x-text="(calc(b).commuteHours - calc(a).commuteHours >= 0 ? '+' : '') + (calc(b).commuteHours - calc(a).commuteHours).toFixed(0) + ' hrs'"></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 text-gray-700">Time value cost</td>
                        <td class="px-4 py-3 text-right tabular-nums" x-text="fmt(calc(a).timeValueCost)"></td>
                        <td class="px-4 py-3 text-right tabular-nums" x-text="fmt(calc(b).timeValueCost)"></td>
                        <td class="px-4 py-3 text-right tabular-nums" :class="calc(b).timeValueCost - calc(a).timeValueCost <= 0 ? 'text-green-600' : 'text-red-600'"
                            x-text="fmtDelta(calc(b).timeValueCost - calc(a).timeValueCost)"></td>
                    </tr>
                    <tr class="bg-gray-50 font-semibold">
                        <td class="px-4 py-3 text-gray-800">Effective salary</td>
                        <td class="px-4 py-3 text-right tabular-nums" x-text="fmt(calc(a).effectiveSalary)"></td>
                        <td class="px-4 py-3 text-right tabular-nums" x-text="fmt(calc(b).effectiveSalary)"></td>
                        <td class="px-4 py-3 text-right tabular-nums" :class="calc(b).effectiveSalary - calc(a).effectiveSalary >= 0 ? 'text-green-600' : 'text-red-600'"
                            x-text="fmtDelta(calc(b).effectiveSalary - calc(a).effectiveSalary)"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Share block --}}
        <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 space-y-3">
            <h3 class="text-sm font-semibold text-gray-700">Share this comparison</h3>
            <div class="relative">
                <pre
                    x-ref="shareText"
                    class="bg-white border border-gray-200 rounded-lg p-4 text-xs text-gray-700 whitespace-pre-wrap overflow-x-auto"
                    x-text="shareText()"
                ></pre>
                <button
                    type="button"
                    @click="copyShare()"
                    class="absolute top-2 right-2 px-3 py-1.5 bg-primary-600 text-white text-xs font-medium rounded-lg hover:bg-primary-700 transition-colors"
                    x-text="copied ? 'Copied!' : 'Copy'"
                ></button>
            </div>
        </div>
    </div>

    {{-- Pro tip --}}
    <div class="rounded-xl p-4 text-sm bg-blue-50 border border-blue-200 text-blue-800">
        <span class="font-semibold">Pro tip:</span>
        Many commutes involve multiple stages — drive to the station, catch a train, then walk. Add a leg for each stage to get an accurate picture of the real cost.
    </div>
</div>

@push('scripts')
<script>
function commuteCostTracker() {
    function makeLeg(mode, minutes) {
        return {
            mode: mode || 'car',
            minutes: minutes ?? 30,
            distanceMiles: 10,
            mpg: 40,
            fuelCostPerLitre: 1.45,
            parkingPerDay: 0,
            tollsPerDay: 0,
            maintenancePerMile: 0.10,
            depreciationPerMile: 0.15,
            ticketPerDay: 12,
            busTicketPerDay: 6,
            bikeMaintenanceYear: 0,
        };
    }

    return {
        a: { salary: 35000, officeDays: 5, workWeeks: 46, legs: [makeLeg('car', 30)] },
        b: { salary: 33000, officeDays: 0, workWeeks: 46, legs: [makeLeg('walk', 0)] },
        comparison: false,
        copied: false,

        modes: [
            { value: 'car',   label: 'Car' },
            { value: 'train', label: 'Train' },
            { value: 'bus',   label: 'Bus' },
            { value: 'bike',  label: 'Bike' },
            { value: 'walk',  label: 'Walk' },
        ],

        addLeg(role) {
            role.legs.push(makeLeg('walk', 10));
        },

        removeLeg(role, index) {
            if (role.legs.length > 1) {
                role.legs.splice(index, 1);
            }
        },

        totalMinutes(role) {
            return role.legs.reduce((sum, l) => sum + Math.max(0, Number(l.minutes) || 0), 0);
        },

        legSummary(role) {
            const parts = role.legs.map(l => {
                const label = this.modes.find(m => m.value === l.mode)?.label || l.mode;
                return label + ' ' + Math.max(0, Number(l.minutes) || 0) + ' min';
            });
            return parts.join('  \u2192  ') + '  =  ' + this.totalMinutes(role) + ' min one way';
        },

        clamp(v, min, max) {
            if (v === '' || v === null || isNaN(v)) return min;
            return Math.min(Math.max(Number(v), min), max);
        },

        calc(r) {
            const salary     = Math.max(0, Number(r.salary) || 0);
            const officeDays = this.clamp(r.officeDays, 0, 5);
            const workWeeks  = this.clamp(r.workWeeks, 40, 52);
            const tripsPerYear = officeDays * workWeeks;

            let totalMinutesOneWay = 0;
            let cashCost = 0;

            for (const leg of r.legs) {
                totalMinutesOneWay += Math.max(0, Number(leg.minutes) || 0);

                if (leg.mode === 'car') {
                    const dist  = Math.max(0, Number(leg.distanceMiles) || 0);
                    const mpg   = Math.max(1, Number(leg.mpg) || 40);
                    const fCpl  = Math.max(0, Number(leg.fuelCostPerLitre) || 0);
                    const park  = Math.max(0, Number(leg.parkingPerDay) || 0);
                    const tolls = Math.max(0, Number(leg.tollsPerDay) || 0);
                    const maint = Math.max(0, Number(leg.maintenancePerMile) || 0);
                    const depr  = Math.max(0, Number(leg.depreciationPerMile) || 0);

                    const milesYr   = dist * 2 * tripsPerYear;
                    const litresYr  = (milesYr / mpg) * 4.54609;
                    const fuelCost  = litresYr * fCpl;

                    cashCost += fuelCost
                        + (park * tripsPerYear)
                        + (tolls * tripsPerYear)
                        + (maint * milesYr)
                        + (depr * milesYr);
                } else if (leg.mode === 'train') {
                    cashCost += Math.max(0, Number(leg.ticketPerDay) || 0) * tripsPerYear;
                } else if (leg.mode === 'bus') {
                    cashCost += Math.max(0, Number(leg.busTicketPerDay) || 0) * tripsPerYear;
                } else if (leg.mode === 'bike') {
                    cashCost += Math.max(0, Number(leg.bikeMaintenanceYear) || 0);
                }
            }

            const commuteMinutes  = totalMinutesOneWay * 2 * tripsPerYear;
            const commuteHours    = commuteMinutes / 60;
            const commuteWorkdays = commuteHours / 8;
            const lifeDays        = commuteHours / 16;

            const hourlyRate    = workWeeks > 0 ? salary / (workWeeks * 40) : 0;
            const timeValueCost = commuteHours * hourlyRate;
            const effectiveSalary = salary - cashCost - timeValueCost;

            return { salary, cashCost, commuteHours, commuteWorkdays, lifeDays, hourlyRate, timeValueCost, effectiveSalary, tripsPerYear };
        },

        fmt(v) {
            return '\u00A3' + Math.round(v).toLocaleString('en-GB');
        },

        fmtDelta(v) {
            return (v >= 0 ? '+' : '') + '\u00A3' + Math.round(v).toLocaleString('en-GB');
        },

        shareText() {
            const ra = this.calc(this.a);
            const rb = this.calc(this.b);
            const delta = rb.effectiveSalary - ra.effectiveSalary;

            const lines = [
                'Commute Cost Comparison (via urlcv.com/tools/commute-cost-tracker)',
                '',
                'Role A: ' + this.legSummary(this.a),
                'Role B: ' + (this.b.officeDays > 0 ? this.legSummary(this.b) : 'Fully remote'),
                '',
                '                    Role A        Role B',
                'Gross salary        ' + this.fmt(this.a.salary).padStart(10) + '    ' + this.fmt(this.b.salary).padStart(10),
                'Cash cost           ' + this.fmt(ra.cashCost).padStart(10) + '    ' + this.fmt(rb.cashCost).padStart(10),
                'Time cost           ' + this.fmt(ra.timeValueCost).padStart(10) + '    ' + this.fmt(rb.timeValueCost).padStart(10),
                'Effective salary    ' + this.fmt(ra.effectiveSalary).padStart(10) + '    ' + this.fmt(rb.effectiveSalary).padStart(10),
                '',
                'Effective salary delta: ' + this.fmtDelta(delta) + '/year',
                '',
                'Calculate yours: https://urlcv.com/tools/commute-cost-tracker',
            ];

            return lines.join('\n');
        },

        copyShare() {
            navigator.clipboard.writeText(this.shareText()).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        },
    };
}
</script>
@endpush
