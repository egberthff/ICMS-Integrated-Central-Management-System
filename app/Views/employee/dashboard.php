<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<!-- Dashboard Metrics Row -->
<div class="row">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check display-1 text-primary"></i>
                <h5 class="card-title mt-2">Timesheet Status</h5>
                <p class="card-text">Submitted for this week</p>
                <a class="btn btn-primary" href="/timesheet">View Details</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <i class="bi bi-cash display-1 text-success"></i>
                <h5 class="card-title mt-2">Payroll Summary</h5>
                <p class="card-text">Next payday: June 15, 2026</p>
                <!-- TRIGGER BUTTON FOR PAYSLIP MODAL -->
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#payslipModal">
                    View Payslip
                </button>
                <button class="btn btn-outline-warning" type="button" id="calculatePayslipBtn">
                    <i class="bi bi-arrow-repeat me-1"></i> Generate Latest Payslip
                </button>
            </div>
        </div>
    </div>
</div>

<!-- PAYSLIP MODAL POP-UP -->
<div class="modal fade" id="payslipModal" tabindex="-1" aria-labelledby="payslipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="payslipModalLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>Recent Payslips
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading your recent payslips...</p>
                </div> -->
                <div id="payslipList">
                    <!-- Payslip items will be loaded here -->
                    <div class="text-center py-4">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3">Loading your recent payslips...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <a href="/payslip" class="btn btn-success">
                    <i class="bi bi-file-earmark-text me-1"></i> View All Payslips
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Employee Quick Actions Card -->
<div class="row mt-5">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Employee Quick Actions</h5>
            </div>
            <div class="card-body">
                <!-- TRIGGER BUTTON FOR MODAL -->
                <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#timesheetModal">
                    <i class="bi bi-clock"></i> Submit Timesheet
                </button>
                <button class="btn btn-info me-2"><i class="bi bi-person"></i> Update Profile</button>
                <button class="btn btn-warning"><i class="bi bi-question-circle"></i> Request Support</button>
            </div>
        </div>
    </div>
</div>

<!-- TIMESHEET ENTRY MODAL POP-UP -->
<div class="modal fade" id="timesheetModal" tabindex="-1" aria-labelledby="timesheetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="timesheetModalLabel">
                    <i class="bi bi-calendar3 me-2"></i>Timesheet Entry Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <form id="timesheetForm" onsubmit="submitTimesheet(event)">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Pay Period / Date Range -->
                        <div class="col-md-6">
                            <label for="ts_start_date" class="form-label fw-bold">Period Start Date</label>
                            <input type="date" class="form-control" id="ts_start_date" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ts_end_date" class="form-label fw-bold">Period End Date</label>
                            <input type="date" class="form-control" id="ts_end_date" required>
                        </div>

                        <!-- Work Hours Breakdown -->
                        <div class="col-md-3">
                            <label for="ts_days_worked" class="form-label fw-bold">Days Worked</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="ts_days_worked" min="0" step="0.5"
                                    value="0">
                                <span class="input-group-text">days</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="ts_reg_hours" class="form-label fw-bold">Regular Hours</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="ts_reg_hours" min="0" step="0.1"
                                    value="0">
                                <span class="input-group-text">hrs</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="ts_ot_hours" class="form-label fw-bold">Total Overtime</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="ts_ot_hours" min="0" step="0.1" value="0">
                                <span class="input-group-text">hrs</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="ts_night_differential" class="form-label fw-bold">Night Diff</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="ts_night_differential" min="0" step="0.1"
                                    value="0">
                                <span class="input-group-text">hrs</span>
                            </div>
                        </div>

                        <!-- Absences and Leaves -->
                        <div class="col-md-4">
                            <label for="ts_sick_leave" class="form-label fw-bold text-danger">Sick Leave</label>
                            <div class="input-group">
                                <input type="number" class="form-control border-danger-subtle" id="ts_sick_leave"
                                    min="0" step="0.5" value="0">
                                <span
                                    class="input-group-text bg-danger-subtle text-danger border-danger-subtle">days</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="ts_vacation_leave" class="form-label fw-bold text-success">Vacation
                                Leave</label>
                            <div class="input-group">
                                <input type="number" class="form-control border-success-subtle" id="ts_vacation_leave"
                                    min="0" step="0.5" value="0">
                                <span
                                    class="input-group-text bg-success-subtle text-success border-success-subtle">days</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="ts_unpaid_leave" class="form-label fw-bold text-warning">Unpaid Leave</label>
                            <div class="input-group">
                                <input type="number" class="form-control border-warning-subtle" id="ts_unpaid_leave"
                                    min="0" step="0.5" value="0">
                                <span
                                    class="input-group-text bg-warning-subtle text-warning border-warning-subtle">days</span>
                            </div>
                        </div>

                        <!-- Notes / Comments -->
                        <div class="col-md-12">
                            <label for="ts_notes" class="form-label fw-bold">Submission Notes / Project Codes</label>
                            <textarea class="form-control" id="ts_notes" rows="2"
                                placeholder="Describe tasks, projects worked on, or reasons for leave/overtime..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"
                            aria-hidden="true"></span>
                        Submit Timesheet Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    async function submitTimesheet(event) {
        // Stop form from reloading the browser page
        event.preventDefault();

        // 1. Session Validation
        const authToken = localStorage.getItem('authToken');
        const userId = localStorage.getItem('user_id');
        const username = localStorage.getItem('username');
        const activeRole = localStorage.getItem('activeRole');

        if (!authToken || !userId) {
            alert("Session expired. Please log in again.");
            return;
        }

        // UI Loading Feedback Setup
        const submitBtn = document.getElementById('submitBtn');
        const submitSpinner = document.getElementById('submitSpinner');
        submitBtn.disabled = true;
        submitSpinner.classList.remove('d-none');

        // 2. Data Gathering Payload Creation
        const payload = {
            user_id: userId,
            username: username,
            activeRole: activeRole,
            start_date: document.getElementById('ts_start_date').value,
            end_date: document.getElementById('ts_end_date').value,
            days_worked: parseFloat(document.getElementById('ts_days_worked').value) || 0,
            regular_hours: parseFloat(document.getElementById('ts_reg_hours').value) || 0,
            ot_hours: parseFloat(document.getElementById('ts_ot_hours').value) || 0,
            night_diff: parseFloat(document.getElementById('ts_night_differential').value) || 0,
            sick_leave: parseFloat(document.getElementById('ts_sick_leave').value) || 0,
            vac_leave: parseFloat(document.getElementById('ts_vacation_leave').value) || 0,
            unpaid_leave: parseFloat(document.getElementById('ts_unpaid_leave').value) || 0,
            notes: document.getElementById('ts_notes').value
        };

        // 3. API Dispatch Network Block
        try {
            const response = await fetch("api/v1/employee/submit-timesheet", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const result = await response.json();
            alert("Timesheet submitted successfully!");

            // Close the modal upon successful transaction completion
            const modalElement = document.getElementById('timesheetModal');
            const modalInstance = bootstrap.Modal.getInstance(modalElement);
            modalInstance.hide();

            // Optional: Reset form fields back to original values
            document.getElementById('timesheetForm').reset();

        } catch (error) {
            console.error("Submission failed:", error);
            alert("An error occurred while submitting your timesheet. Please try again.");
        } finally {
            // Restore button UI states
            submitBtn.disabled = false;
            submitSpinner.classList.add('d-none');
        }
    }

    // Payslip Modal Handler
    document.addEventListener('DOMContentLoaded', function () {
        const payslipModal = document.getElementById('payslipModal');
        if (payslipModal) {
            payslipModal.addEventListener('show.bs.modal', function () {
                loadRecentPayslips();
            });
        }

        const calculateBtn = document.getElementById('calculatePayslipBtn');
        if (calculateBtn) {
            calculateBtn.addEventListener('click', async () => {
                const payslipList = document.getElementById('payslipList');
                if (payslipList) {
                    payslipList.innerHTML = `
                        <div class="text-center py-4">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Generating...</span>
                            </div>
                            <p class="mt-3 mb-0">Generating payslip...</p>
                        </div>
                    `;
                }

                try {
                    // Payroll calculation should be payroll/prerogative. For now, keep only the refresh UX.
                    await loadRecentPayslips();
                } catch (e) {
                    if (payslipList) {
                        payslipList.innerHTML = `
                            <div class="text-center py-4 text-danger">
                                <i class="bi bi-exclamation-triangle mb-3"></i>
                                <p class="mb-0">Failed to generate payslip.</p>
                            </div>
                        `;
                    }
                }
            });
        }
    });


    async function generateLatestPayslip() {
        try {
            const userId = localStorage.getItem('user_id');
            if (!userId) {
                throw new Error('Session expired. Please log in again.');
            }

            // Use the most recent payslip period if available; otherwise default to the last 15 days.
            // NOTE: this is a simple UX default; you can wire this to a real pay period picker later.
            const now = new Date();
            const end = now.toISOString().slice(0, 10);
            const startDate = new Date(now.getTime() - 14 * 24 * 60 * 60 * 1000).toISOString().slice(0, 10);

            const basicSalary = 0; // backend will still calculate using your provided basic_salary math

            const response = await fetch('/api/v1/payroll/calculate-payslip', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: "same-origin",
                body: JSON.stringify({
                    employee_id: userId,
                    pay_period_start: startDate,
                    pay_period_end: end,
                    basic_salary: basicSalary,
                    payment_method: 'bank_transfer'
                })
            });

            const rawText = await response.text();
            let result;
            try { result = rawText ? JSON.parse(rawText) : null; } catch (e) { /* ignore */ }

            if (!response.ok) {
                throw new Error(result?.message || `HTTP error! Status: ${response.status}`);
            }

            await loadRecentPayslips();
            return result;
        } catch (err) {
            console.error('generateLatestPayslip failed:', err);
            throw err;
        }
    }

    async function loadRecentPayslips() {
        try {
            const payslipList = document.getElementById('payslipList');
            payslipList.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading your recent payslips...</p>
                </div>
            `;

            const response = await fetch("/api/v1/payroll/latest-payslip", {
                credentials: "same-origin"
            });

            // Helpful debug logs if it fails (RBAC/401/403/etc)
            const rawText = await response.text();
            let result;
            try {
                result = rawText ? JSON.parse(rawText) : null;
            } catch (e) {
                console.error('Non-JSON response from latest-payslip:', rawText);
            }

            if (!result) {
                throw new Error('Empty/invalid JSON response from latest-payslip');
            }

            if (result.data.payslip) {
                const payslip = result.data.payslip;
                const formattedDate = new Date(payslip.date_issued).toLocaleDateString();
                const period = `${new Date(payslip.pay_period_start).toLocaleDateString()} - ${new Date(payslip.pay_period_end).toLocaleDateString()}`;
                payslipList.innerHTML = '';
                payslipList.innerHTML = `
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h6 class="mb-0">${period}</h6>
                                <span class="badge bg-${getStatusBadgeClass(payslip.status)}">${getStatusBadgeText(payslip.status)}</span>
                            </div>
                            <div class="d-flex justify-content-between mt-2">
                                <span>Net Pay:</span>
                                <strong>₱${parseFloat(payslip.net_pay || 0).toFixed(2)}</strong>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                payslipList.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-file-earmark-text display-6 text-muted mb-3"></i>
                        <p class="text-muted">No recent payslips available</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading recent payslip:', error);
            document.getElementById('payslipList').innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle mb-3"></i>
                    <p>Failed to load payslip information</p>
                </div>
            `;
        }
    }

    function getStatusBadgeClass(status) {
        const statusMap = {
            'draft': 'secondary',
            'pending': 'warning',
            'approved': 'info',
            'paid': 'success',
            'rejected': 'danger'
        };
        return statusMap[status] || 'secondary';
    }

    function getStatusBadgeText(status) {
        const statusMap = {
            'draft': 'Draft',
            'pending': 'Pending',
            'approved': 'Approved',
            'paid': 'Paid',
            'rejected': 'Rejected'
        };
        return statusMap[status] || status;
    }
</script>

<?= $this->endSection() ?>


<!-- <script>
   async function submitTimesheet(){
        // Validation: Ensure required localStorage items exist before making the API call
        const authToken = localStorage.getItem('authToken');
        const userId = localStorage.getItem('user_id');
        const username = localStorage.getItem('username');
        const activeRole = localStorage.getItem('activeRole');

        if (!authToken || !userId) {
            console.error("Missing authentication or user data in localStorage.");
            alert("Session expired. Please log in again.");
            return;
        }

        try {
            // base_url() ensures the fetch hits the correct origin regardless of your current sub-url route
            const response = await fetch("api/v1/employee/get-timesheet", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${authToken}`
                },
                // FIXED: Wrapped payload fields inside an object literal {}
                body: JSON.stringify({
                    userId: userId,
                    username: username,
                    activeRole: activeRole
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            const result = await response.json();
            document.cookie = "authToken=" + result.token + "; path=/; max-age=86400"; // 24 hours
            window.location.href = '/timesheet';
            // console.log("Success:", result);
            // alert("Timesheet submitted successfully!");
            
        } catch(error) {
            // FIXED: Do not leave catch blocks empty so you can diagnose connection or server crashes
            console.error("Submission failed:", error);
            alert("An error occurred while submitting your timesheet.");
        }
    }
</script>  -->