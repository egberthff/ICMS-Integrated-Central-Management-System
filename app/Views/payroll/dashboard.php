<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="row">
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <svg xmlns="http://w3.org" width="24" height="24" fill="currentColor"
                    class="bi bi-exclamation-triangle-fill me-2" viewBox="0 0 16 16">
                    <path
                        d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
                </svg>
                <div>
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Total Gross Pay Card -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-cash-stack display-1 text-primary"></i>
                <h5 class="card-title mt-2">Total Gross Earnings</h5>
                <p class="card-text display-6 fw-bold text-truncate" id="totalGross">₱ 0.00</p>
            </div>
        </div>
    </div>

    <!-- Total Deductions Card -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-dash-circle display-1 text-danger"></i>
                <h5 class="card-title mt-2">Total Deductions</h5>
                <p class="card-text display-6 fw-bold text-truncate" id="totalDeductions">₱ 0.00</p>
            </div>
        </div>
    </div>

    <!-- Total Net Pay Card -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-wallet2 display-1 text-success"></i>
                <h5 class="card-title mt-2">Total Net Payout</h5>
                <p class="card-text display-6 fw-bold text-truncate" id="totalNet">₱ 0.00</p>
            </div>
        </div>
    </div>

    <!-- Pending Approvals Card -->
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-clock-history display-1 text-warning"></i>
                <h5 class="card-title mt-2">Pending Approvals</h5>
                <p class="card-text display-4 fw-bold" id="pendingPayslips">0</p>
            </div>
        </div>
    </div>
</div>

<!-- Payroll Quick Actions -->
<div class="row mt-5">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Payroll Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="/payroll/payslips/create" class="btn btn-primary me-2"><i class="bi bi-plus-circle"></i> Create
                    New Payslip</a>
                <a href="/payroll/payslips" class="btn btn-success me-2"><i class="bi bi-file-earmark-spreadsheet"></i>
                    Manage Payslips</a>
                <a href="/payroll/reports" class="btn btn-warning"><i class="bi bi-graph-up"></i> Financial Reports</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Formatter utility for Philippine Peso or preferred currency format
    const currencyFormatter = new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP'
    });

    async function loadDashboardStats() {
        try {
            console.log('Payroll view initializing...');

            // Fetch payslips data from the API
            const response = await apiCall('/api/v1/payroll/payslips', 'GET');

            if (response.ok && response.data && response.data.data && response.data.data.payslips) {
                const payslips = response.data.data.payslips;

                let grossSum = 0;
                let deductionSum = 0;
                let netSum = 0;
                let pendingCount = 0;

                // Loop through payslips and aggregate calculations
                payslips.forEach(slip => {
                    // Force variables to numerical format safely
                    const gross = parseFloat(slip.gross_earnings) || 0;
                    const deductions = parseFloat(slip.total_deductions) || 0;
                    const net = parseFloat(slip.net_pay) || 0;

                    grossSum += gross;
                    deductionSum += deductions;
                    netSum += net;

                    if (slip.status === 'pending') {
                        pendingCount++;
                    }
                });

                // Update UI elements with formatted values
                document.getElementById('totalGross').textContent = currencyFormatter.format(grossSum);
                document.getElementById('totalDeductions').textContent = currencyFormatter.format(deductionSum);
                document.getElementById('totalNet').textContent = currencyFormatter.format(netSum);
                document.getElementById('pendingPayslips').textContent = pendingCount;
            }
        } catch (error) {
            console.error('Error loading payroll dashboard stats:', error);
        }
    }

    window.addEventListener('load', loadDashboardStats);
</script>

<?= $this->endSection() ?>