<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h2 class="mb-1"><i class="bi bi-cash-coin me-2"></i>Timesheet Processing</h2>
        <p class="text-muted mb-0">Review → Payroll Preview → View Generated Payslip (Payroll calculates)</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-secondary" id="resetBtn" type="button"><i
                class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>
        <a class="btn btn-outline-primary" href="/payslip"><i class="bi bi-file-earmark-text me-1"></i>All Payslips</a>
    </div>
</div>

<hr class="my-4" />

<!-- Alerts -->
<div id="tsAlerts"></div>

<!-- Stepper -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4">
                <div class="p-3 rounded" id="step1" style="background: rgba(13,110,253,.08);">
                    <div class="fw-bold"><span class="me-2">1</span>Timesheet Review</div>
                    <div class="text-muted small" id="step1Sub">Select period to load details</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded" id="step2" style="background: rgba(255,193,7,.08);">
                    <div class="fw-bold"><span class="me-2">2</span>Payroll Preview</div>
                    <div class="text-muted small" id="step2Sub">Preview calculated breakdown</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 rounded" id="step3" style="background: rgba(25,135,84,.08);">
                    <div class="fw-bold"><span class="me-2">3</span>Generated Payslip</div>
                    <div class="text-muted small" id="step3Sub">View payslip if payroll generated</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Period Picker -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Select Pay Period</h5>
    </div>
    <div class="card-body">
        <form id="periodForm">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Start Date</label>
                    <input type="date" class="form-control" id="pay_period_start" required />
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">End Date</label>
                    <input type="date" class="form-control" id="pay_period_end" required />
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary w-50" type="submit" id="loadPeriodBtn"><i
                            class="bi bi-search me-1"></i>Load</button>
                    <button class="btn btn-outline-primary w-50" type="button" id="refreshPayslipBtn"><i
                            class="bi bi-arrow-repeat me-1"></i>Reload Payslip</button>
                </div>
            </div>
        </form>
        <p class="text-muted small mt-3 mb-0">
            Payroll requires approved timesheets to generate accurate payslips.
        </p>
    </div>
</div>

<!-- Step 1: Timesheet details -->
<div class="card shadow-sm mb-4" id="timesheetCard" style="display:none;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Timesheet Details</h5>
            <span class="badge" id="timesheetStatusBadge">—</span>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="fw-bold">Days Worked</div>
                <div id="ts_days_worked">0</div>
            </div>
            <div class="col-md-3">
                <div class="fw-bold">Regular Hours</div>
                <div id="ts_regular_hours">0</div>
            </div>
            <div class="col-md-3">
                <div class="fw-bold">Overtime Hours</div>
                <div id="ts_ot_hours">0</div>
            </div>
            <div class="col-md-3">
                <div class="fw-bold">Night Diff Hours</div>
                <div id="ts_night_diff">0</div>
            </div>
            <div class="col-md-4">
                <div class="fw-bold text-danger">Sick Leave (days)</div>
                <div id="ts_sick_leave">0</div>
            </div>
            <div class="col-md-4">
                <div class="fw-bold text-success">Vacation Leave (days)</div>
                <div id="ts_vac_leave">0</div>
            </div>
            <div class="col-md-4">
                <div class="fw-bold text-warning">Unpaid Leave (days)</div>
                <div id="ts_unpaid_leave">0</div>
            </div>
            <div class="col-md-12">
                <div class="fw-bold mb-1">Notes</div>
                <div id="ts_notes" class="bg-light p-3 rounded text-muted">—</div>
            </div>
        </div>

        <div class="alert alert-info mt-3 mb-0" id="readinessInfo">
            Timesheet review loaded. Continue to preview breakdown.
        </div>
    </div>
</div>

<!-- Step 2: Preview -->
<div class="card shadow-sm mb-4" id="previewCard" style="display:none;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-diagram-3 me-2"></i>Payroll Preview (No Save)</h5>
            <button class="btn btn-success" type="button" id="previewBtn">
                <i class="bi bi-play-fill me-1"></i>Run Preview
            </button>
        </div>
    </div>
    <div class="card-body">
        <div id="previewWarnings" class="mb-3"></div>

        <div class="row g-3">
            <div class="col-md-3">
                <div class="text-muted small">Gross Earnings</div>
                <div class="fs-5 fw-bold" id="pv_gross_earnings">₱0.00</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Total Deductions</div>
                <div class="fs-5 fw-bold" id="pv_total_deductions">₱0.00</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">Net Pay</div>
                <div class="fs-5 fw-bold text-success" id="pv_net_pay">₱0.00</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted small">YTD Net</div>
                <div class="fs-5 fw-bold" id="pv_ytd_net">₱0.00</div>
            </div>
        </div>

        <hr />

        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="mb-3">Earnings</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <tbody>
                            <tr>
                                <td>Basic Salary</td>
                                <td class="text-end" id="pv_basic_salary">₱0.00</td>
                            </tr>
                            <tr>
                                <td>Overtime Pay</td>
                                <td class="text-end" id="pv_overtime_pay">₱0.00</td>
                            </tr>
                            <tr>
                                <td>Night Diff Pay</td>
                                <td class="text-end" id="pv_night_diff_pay">₱0.00</td>
                            </tr>
                            <tr>
                                <td>Allowances</td>
                                <td class="text-end" id="pv_allowances">₱0.00</td>
                            </tr>
                            <tr>
                                <td>Bonuses</td>
                                <td class="text-end" id="pv_bonuses">₱0.00</td>
                            </tr>
                            <tr class="table-active">
                                <td class="fw-bold">Gross Earnings</td>
                                <td class="text-end fw-bold" id="pv_gross_earnings_2">₱0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="mb-3">Deductions</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <tbody>
                            <tr>
                                <td>SSS</td>
                                <td class="text-end" id="pv_sss_deduction">₱0.00</td>
                            </tr>
                            <tr>
                                <td>PhilHealth</td>
                                <td class="text-end" id="pv_philhealth_deduction">₱0.00</td>
                            </tr>
                            <tr>
                                <td>Pag-IBIG</td>
                                <td class="text-end" id="pv_pagibig_deduction">₱0.00</td>
                            </tr>
                            <tr>
                                <td>Withholding Tax</td>
                                <td class="text-end" id="pv_tax_deduction">₱0.00</td>
                            </tr>
                            <tr>
                                <td>Other Deductions</td>
                                <td class="text-end" id="pv_other_deductions">₱0.00</td>
                            </tr>
                            <tr class="table-danger">
                                <td class="fw-bold">Total Deductions</td>
                                <td class="text-end fw-bold" id="pv_total_deductions_2">₱0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card bg-light border-0">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Basic Salary (for preview math)</label>
                                <input type="number" min="0" step="0.01" class="form-control" id="basic_salary"
                                    value="0" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Allowances</label>
                                <input type="number" min="0" step="0.01" class="form-control" id="allowances"
                                    value="0" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Bonuses</label>
                                <input type="number" min="0" step="0.01" class="form-control" id="bonuses" value="0" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Other Deductions</label>
                                <input type="number" min="0" step="0.01" class="form-control" id="other_deductions"
                                    value="0" />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Remarks (optional)</label>
                                <input type="text" class="form-control" id="remarks"
                                    placeholder="e.g. adjustment, overtime correction..." />
                            </div>
                            <div class="col-md-12">
                                <div class="alert alert-warning mb-0">
                                    Preview uses the numbers you provide here. Final payslip values are computed by
                                    payroll.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Step 3: Generated payslip -->
<div class="card shadow-sm mb-4" id="payslipCard" style="display:none;">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Generated Payslip (Payroll)</h5>
            <span class="badge" id="generatedPayslipStatus">—</span>
        </div>
    </div>
    <div class="card-body">
        <div id="payslipMissing" class="text-muted py-3" style="display:none;">
            Payroll has not generated a payslip for the selected period yet.
        </div>

        <div id="payslipSummary" style="display:none;">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">Net Pay</div>
                    <div class="fs-5 fw-bold text-success" id="pl_net_pay">₱0.00</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Gross Earnings</div>
                    <div class="fs-5 fw-bold" id="pl_gross_earnings">₱0.00</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">Total Deductions</div>
                    <div class="fs-5 fw-bold" id="pl_total_deductions">₱0.00</div>
                </div>
            </div>

            <hr />

            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-outline-primary" type="button" id="viewPayslipBtn">
                    <i class="bi bi-eye me-1"></i>View Payslip
                </button>
                <button class="btn btn-outline-secondary" type="button" id="copyPayslipBtn">
                    <i class="bi bi-clipboard me-1"></i>Copy Payslip ID
                </button>
            </div>

            <div class="text-muted small mt-2" id="payslipIdText"></div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('extra_scripts') ?>
<script>
    const apiBase = '/api/v1/employee';

    function showAlert(type, message) {
        const container = document.getElementById('tsAlerts');
        const id = 'tsAlert_' + Date.now();
        container.innerHTML += `
      <div id="${id}" class="alert alert-${type} alert-dismissible fade show mt-3" role="alert">
        <i class="bi bi-${type === 'danger' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;
        setTimeout(() => {
            const el = document.getElementById(id);
            if (el) el.remove();
        }, 5000);
    }

    function money(v) {
        const n = parseFloat(v ?? 0);
        return '₱' + (isNaN(n) ? '0.00' : n.toFixed(2));
    }

    function statusBadge(status) {
        const map = {
            'pending': 'bg-warning text-dark',
            'approved': 'bg-success',
            'rejected': 'bg-danger',
            'draft': 'bg-secondary',
            'paid': 'bg-success',
            'approved_payslip': 'bg-info'
        };
        return map[status] || 'bg-secondary';
    }

    function statusText(status) {
        const map = {
            'pending': 'Pending',
            'approved': 'Approved',
            'rejected': 'Rejected',
            'draft': 'Draft',
            'paid': 'Paid'
        };
        return map[status] || status || '—';
    }

    function setStepper(step) {
        // highlight each step by subtle style changes
        const s1 = document.getElementById('step1Sub');
        const s2 = document.getElementById('step2Sub');
        const s3 = document.getElementById('step3Sub');

        if (step === 1) { s1.textContent = 'Timesheet loaded successfully'; s2.textContent = 'Preview ready'; s3.textContent = 'Await payroll generation'; }
        if (step === 2) { s2.textContent = 'Preview calculated'; }
        if (step === 3) { s3.textContent = 'Payslip loaded'; }
    }

    async function postJson(path, payload) {

        const res = await fetch(path, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });

        const rawText = await res.text();
        let result = null;
        try { result = rawText ? JSON.parse(rawText) : null; } catch (e) { }
        if (!res.ok) {
            throw new Error(result?.message || `HTTP error! Status: ${res.status}`);
        }
        return result;
    }

    const state = {
        pay_period_start: null,
        pay_period_end: null,
        timesheet: null,
        preview: null,
        payslip: null,
    };

    function populateTimesheet(t) {
        document.getElementById('timesheetCard').style.display = 'block';
        document.getElementById('ts_days_worked').textContent = t?.days_worked ?? 0;
        document.getElementById('ts_regular_hours').textContent = t?.regular_hours ?? 0;
        document.getElementById('ts_ot_hours').textContent = t?.ot_hours ?? 0;
        document.getElementById('ts_night_diff').textContent = t?.night_diff ?? 0;
        document.getElementById('ts_sick_leave').textContent = t?.sick_leave ?? 0;
        document.getElementById('ts_vac_leave').textContent = t?.vac_leave ?? 0;
        document.getElementById('ts_unpaid_leave').textContent = t?.unpaid_leave ?? 0;
        document.getElementById('ts_notes').textContent = t?.notes || '—';

        const badge = document.getElementById('timesheetStatusBadge');
        badge.className = 'badge ' + statusBadge(t?.status);
        badge.textContent = statusText(t?.status);

        const info = document.getElementById('readinessInfo');
        if (t?.status !== 'approved') {
            info.className = 'alert alert-warning mb-0';
            info.textContent = 'This timesheet is not approved yet. Preview may still work, but payroll may use last approved values.';
        } else {
            info.className = 'alert alert-info mb-0';
            info.textContent = 'Timesheet is approved. You can preview payroll computation.';
        }

        setStepper(1);
    }

    function populatePreview(pv, readiness) {
        console.log(pv);
        document.getElementById('previewCard').style.display = 'block';

        const warningsEl = document.getElementById('previewWarnings');
        warningsEl.innerHTML = '';
        if (readiness?.warnings?.length) {
            warningsEl.innerHTML = `<div class="alert alert-warning mb-0"><strong>Warnings:</strong><ul class="mb-0">${readiness.warnings.map(w => `<li>${w}</li>`).join('')}</ul></div>`;
        }

        document.getElementById('pv_basic_salary').textContent = money(pv.basic_salary);
        document.getElementById('pv_overtime_pay').textContent = money(pv.overtime_pay);
        // No explicit night diff pay field returned, but service calculates via night_diff and hourly rate.
        // We show it using night_diff hours premium only if present in returned structure.
        // Current service returns gross_earnings etc, but not night_diff_pay, so approximate by (gross - others) is not safe.
        // We'll show night_diff_pay as derived from gross minus basic/overtime/allow/bonus - sss/phil/tax etc is not possible.
        // Keep it simple: show 0 unless gross math includes it already; we don't have a dedicated field.
        document.getElementById('pv_night_diff_pay').textContent = money(pv.night_diff_pay ?? 0);

        document.getElementById('pv_allowances').textContent = money(pv.allowances);
        document.getElementById('pv_bonuses').textContent = money(pv.bonuses);

        document.getElementById('pv_gross_earnings').textContent = money(pv.gross_earnings);
        document.getElementById('pv_gross_earnings_2').textContent = money(pv.gross_earnings);

        document.getElementById('pv_sss_deduction').textContent = money(pv.sss_deduction);
        document.getElementById('pv_philhealth_deduction').textContent = money(pv.philhealth_deduction);
        document.getElementById('pv_pagibig_deduction').textContent = money(pv.pagibig_deduction);
        document.getElementById('pv_tax_deduction').textContent = money(pv.tax_deduction);
        document.getElementById('pv_other_deductions').textContent = money(pv.other_deductions);

        document.getElementById('pv_total_deductions').textContent = money(pv.total_deductions);
        document.getElementById('pv_total_deductions_2').textContent = money(pv.total_deductions);

        document.getElementById('pv_net_pay').textContent = money(pv.net_pay);
        document.getElementById('pv_ytd_net').textContent = money(pv.year_to_date_net);

        setStepper(2);
    }

    function populatePayslip(p) {
        document.getElementById('payslipCard').style.display = 'block';

        const badge = document.getElementById('generatedPayslipStatus');
        const missingEl = document.getElementById('payslipMissing');
        const summaryEl = document.getElementById('payslipSummary');

        if (!p) {
            missingEl.style.display = 'block';
            summaryEl.style.display = 'none';
            badge.className = 'badge bg-secondary';
            badge.textContent = 'Not generated';
            return;
        }

        missingEl.style.display = 'none';
        summaryEl.style.display = 'block';

        badge.className = 'badge ' + statusBadge(p.status);
        badge.textContent = statusText(p.status);

        document.getElementById('pl_net_pay').textContent = money(p.net_pay);
        document.getElementById('pl_gross_earnings').textContent = money(p.gross_earnings);
        document.getElementById('pl_total_deductions').textContent = money(p.total_deductions);

        document.getElementById('payslipIdText').textContent = `Payslip ID: ${p.id}`;

        const viewBtn = document.getElementById('viewPayslipBtn');
        viewBtn.onclick = () => {
            // reuse existing payslip UI modal by navigating to /payslip and opening it is not wired.
            // We'll navigate to /payslip which will list/popup.
            window.location.href = '/payslip';
        };

        document.getElementById('copyPayslipBtn').onclick = async () => {
            try {
                await navigator.clipboard.writeText(String(p.id));
                showAlert('success', 'Payslip ID copied to clipboard.');
            } catch (e) {
                showAlert('danger', 'Copy failed.');
            }
        };

        setStepper(3);
    }

    async function loadAllForPeriod() {
        const userId = localStorage.getItem('user_id');
        if (!userId) throw new Error('Session expired. Please log in again.');

        const start = state.pay_period_start;
        const end = state.pay_period_end;

        // 1) timesheet
        const tsRes = await postJson(apiBase + '/timesheet-processing/timesheet', {
            employee_id: userId,
            pay_period_start: start,
            pay_period_end: end
        });

        const ts = tsRes?.data?.timesheet ?? null;
        state.timesheet = ts;
        if (ts) populateTimesheet(ts);
        else {
            document.getElementById('timesheetCard').style.display = 'none';
            showAlert('warning', 'No timesheet found for this period. Payroll preview may be unavailable.');
        }

        // 2) preview card: just show shell; run preview separately
        document.getElementById('previewCard').style.display = 'block';

        // 3) payslip
        const plRes = await postJson(apiBase + '/timesheet-processing/payslip', {
            employee_id: userId,
            pay_period_start: start,
            pay_period_end: end
        });

        const pl = plRes?.data?.payslip ?? null;
        state.payslip = pl;
        populatePayslip(pl);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // default to last 15 days
        const now = new Date();
        const end = now.toISOString().slice(0, 10);
        const startDate = new Date(now.getTime() - 14 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);

        document.getElementById('pay_period_start').value = startDate;
        document.getElementById('pay_period_end').value = end;

        document.getElementById('periodForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            try {
                state.pay_period_start = document.getElementById('pay_period_start').value;
                state.pay_period_end = document.getElementById('pay_period_end').value;

                if (!state.pay_period_start || !state.pay_period_end) {
                    showAlert('warning', 'Select a valid period.');
                    return;
                }

                // reset UI
                document.getElementById('tsAlerts').innerHTML = '';
                document.getElementById('timesheetCard').style.display = 'none';
                document.getElementById('previewCard').style.display = 'none';
                document.getElementById('payslipCard').style.display = 'none';
                document.getElementById('previewWarnings').innerHTML = '';

                document.getElementById('loadPeriodBtn').disabled = true;
                document.getElementById('loadPeriodBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Loading...';

                await loadAllForPeriod();
                showAlert('success', 'Period loaded successfully.');

            } catch (err) {
                console.error(err);
                showAlert('danger', err.message || 'Failed to load period.');
            } finally {
                document.getElementById('loadPeriodBtn').disabled = false;
                document.getElementById('loadPeriodBtn').innerHTML = '<i class="bi bi-search me-1"></i>Load';
            }
        });

        document.getElementById('previewBtn').addEventListener('click', async () => {
            try {
                const userId = localStorage.getItem('user_id');
                const start = state.pay_period_start;
                const end = state.pay_period_end;
                const basic_salary = parseFloat(document.getElementById('basic_salary').value) || 0;

                if (!userId) throw new Error('Session expired. Please log in again.');
                if (!start || !end) throw new Error('Select a period first.');

                document.getElementById('previewWarnings').innerHTML = '';
                document.getElementById('previewBtn').disabled = true;
                const old = document.getElementById('previewBtn').innerHTML;
                document.getElementById('previewBtn').innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Running Preview...';

                const res = await postJson(apiBase + '/timesheet-processing/preview-payslip', {
                    employee_id: userId,
                    pay_period_start: start,
                    pay_period_end: end,
                    basic_salary: basic_salary,
                    allowances: parseFloat(document.getElementById('allowances').value) || 0,
                    bonuses: parseFloat(document.getElementById('bonuses').value) || 0,
                    other_deductions: parseFloat(document.getElementById('other_deductions').value) || 0,
                    remarks: document.getElementById('remarks').value || ''
                });

                const readiness = res?.data?.readiness;
                const preview = res?.data?.preview;
                state.preview = preview;

                populatePreview(preview, readiness);
                showAlert('success', 'Preview generated successfully.');

                document.getElementById('previewBtn').disabled = false;
                document.getElementById('previewBtn').innerHTML = old;

            } catch (err) {
                console.error(err);
                showAlert('danger', err.message || 'Preview failed.');
                document.getElementById('previewBtn').disabled = false;
            }
        });

        document.getElementById('refreshPayslipBtn').addEventListener('click', async () => {
            try {
                const userId = localStorage.getItem('user_id');
                if (!userId) throw new Error('Session expired. Please log in again.');
                if (!state.pay_period_start || !state.pay_period_end) {
                    showAlert('warning', 'Select a period first.');
                    return;
                }

                const plRes = await postJson(apiBase + '/timesheet-processing/payslip', {
                    employee_id: userId,
                    pay_period_start: state.pay_period_start,
                    pay_period_end: state.pay_period_end
                });
                const pl = plRes?.data?.payslip ?? null;
                state.payslip = pl;
                populatePayslip(pl);
                showAlert('success', 'Payslip reloaded.');
            } catch (e) {
                console.error(e);
                showAlert('danger', e.message || 'Failed to reload payslip.');
            }
        });

        document.getElementById('resetBtn').addEventListener('click', () => {
            document.getElementById('tsAlerts').innerHTML = '';
            document.getElementById('timesheetCard').style.display = 'none';
            document.getElementById('previewCard').style.display = 'none';
            document.getElementById('payslipCard').style.display = 'none';
            document.getElementById('previewWarnings').innerHTML = '';

            document.getElementById('basic_salary').value = 0;
            document.getElementById('allowances').value = 0;
            document.getElementById('bonuses').value = 0;
            document.getElementById('other_deductions').value = 0;
            document.getElementById('remarks').value = '';
        });
    });
</script>
<?= $this->endSection() ?>