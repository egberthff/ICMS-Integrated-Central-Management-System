<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<!-- Payslip Header -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2><i class="bi bi-file-earmark-text me-2"></i>My Payslips</h2>
            <button class="btn btn-outline-primary" id="filterBtn">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
        </div>
    </div>
</div>

<!-- Filter Card -->
<div class="row mb-4" id="filterCard" style="display: none;">
    <div class="col-md-12">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-funnel me-1"></i> Filter Payslips</h5>
            </div>
            <div class="card-body">
                <form id="payslipFilterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="filter_start_date" class="form-label">From Date</label>
                            <input type="date" class="form-control" id="filter_start_date">
                        </div>
                        <div class="col-md-3">
                            <label for="filter_end_date" class="form-label">To Date</label>
                            <input type="date" class="form-control" id="filter_end_date">
                        </div>
                        <div class="col-md-3">
                            <label for="filter_status" class="form-label">Status</label>
                            <select class="form-select" id="filter_status">
                                <option value="">All Statuses</option>
                                <option value="draft">Draft</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="paid">Paid</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Apply Filter</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payslip Alerts -->
<div id="payslipAlerts"></div>

<!-- Payslip Table -->
<div class="table-responsive">
    <table class="table table-hover align-middle" id="payslipTable">
        <thead class="table-light">
            <tr>
                <th>Pay Period</th>
                <th>Date Issued</th>
                <th class="text-end">Gross Earnings</th>
                <th class="text-end">Total Deductions</th>
                <th class="text-end">Net Pay</th>
                <th class="text-center">Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <!-- Payslip rows will be loaded here via JavaScript -->
            <tr>
                <td colspan="7" class="text-center text-muted">Loading payslips...</td>
            </tr>
        </tbody>
    </table>
</div>

<!-- No Payslips Message -->
<div class="text-center py-5" id="noPayslipsMessage" style="display: none;">
    <i class="bi bi-file-earmark-text display-4 text-muted mb-3"></i>
    <h5 class="text-muted">No payslips found</h5>
    <p class="text-muted">You don't have any payslips available yet. Payslips are typically generated after each pay period.</p>
</div>

<!-- Payslip Detail Modal -->
<div class="modal fade" id="payslipDetailModal" tabindex="-1" aria-labelledby="payslipDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="payslipDetailModalLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>Payslip Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="payslipDetailBody">
                <!-- Payslip detail content will be loaded here -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3">Loading payslip details...</p>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="printPayslipBtn"><i class="bi bi-printer me-1"></i> Print</button>
                <button type="button" class="btn btn-success" id="downloadPayslipBtn"><i class="bi bi-download me-1"></i> Download PDF</button>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('extra_scripts') ?>
<script>
    // Payslip Management
    class PayslipManager {
        constructor() {
            this.apiBase = '/api/v1/payroll';
            this.currentPayslipId = null;
            this.init();
        }
        
        init() {
            this.loadPayslips();
            this.bindEvents();
        }
        
        bindEvents() {
            // Filter button
            document.getElementById('filterBtn').addEventListener('click', () => {
                const filterCard = document.getElementById('filterCard');
                filterCard.style.display = filterCard.style.display === 'none' ? 'block' : 'none';
            });
            
            // Filter form submission
            document.getElementById('payslipFilterForm').addEventListener('submit', (e) => {
                e.preventDefault();
                this.loadPayslips(true); // Apply filters
            });
            
            // Print button
            document.getElementById('printPayslipBtn').addEventListener('click', () => {
                window.print();
            });
            
            // Download PDF button
            document.getElementById('downloadPayslipBtn').addEventListener('click', () => {
                this.downloadPayslipPdf();
            });
        }
        
        async loadPayslips(applyFilters = false) {
            try {
                // Show loading state
                document.querySelector('#payslipTable tbody').innerHTML = `
                    <tr><td colspan="7" class="text-center text-muted">Loading payslips...</td></tr>
                `;
                
                // Build query parameters
                const params = new URLSearchParams();
                params.append('limit', 50);
                
                if (applyFilters) {
                    const startDate = document.getElementById('filter_start_date').value;
                    const endDate = document.getElementById('filter_end_date').value;
                    const status = document.getElementById('filter_status').value;
                    
                    if (startDate) params.append('start_date', startDate);
                    if (endDate) params.append('end_date', endDate);
                    if (status) params.append('status', status);
                }
                
                // Fetch payslips
                const response = await fetch(`${this.apiBase}/payslips?${params.toString()}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const result = await response.json();
                
                if (result.payslips && result.payslips.length > 0) {
                    this.renderPayslipTable(result.payslips);
                    document.getElementById('noPayslipsMessage').style.display = 'none';
                } else {
                    document.querySelector('#payslipTable tbody').innerHTML = `
                        <tr><td colspan="7" class="text-center text-muted">No payslips found</td></tr>
                    `;
                    document.getElementById('noPayslipsMessage').style.display = 'block';
                }
                
            } catch (error) {
                console.error('Error loading payslips:', error);
                this.showAlert('danger', 'Failed to load payslips. Please try again later.');
                document.querySelector('#payslipTable tbody').innerHTML = `
                    <tr><td colspan="7" class="text-center text-danger">Error loading payslips</td></tr>
                `;
            }
        }
        
        renderPayslipTable(payslips) {
            const tbody = document.querySelector('#payslipTable tbody');
            
            tbody.innerHTML = payslips.map(payslip => {
                const statusBadge = this.getStatusBadge(payslip.status);
                const formattedDate = new Date(payslip.date_issued).toLocaleDateString();
                const period = `${new Date(payslip.pay_period_start).toLocaleDateString()} - ${new Date(payslip.pay_period_end).toLocaleDateString()}`;
                
                return `
                    <tr>
                        <td>${period}</td>
                        <td>${formattedDate}</td>
                        <td class="text-end">₱${parseFloat(payslip.gross_earnings || 0).toFixed(2)}</td>
                        <td class="text-end">₱${parseFloat(payslip.total_deductions || 0).toFixed(2)}</td>
                        <td class="text-end">₱${parseFloat(payslip.net_pay || 0).toFixed(2)}</td>
                        <td class="text-center">${statusBadge}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary me-1" onclick="payslipManager.viewPayslip(${payslip.id})" title="View Details">
                                <i class="bi bi-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-success" onclick="payslipManager.downloadPayslip(${payslip.id})" title="Download PDF">
                                <i class="bi bi-download"></i>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        
        getStatusBadge(status) {
            const statusMap = {
                'draft': 'bg-secondary',
                'pending': 'bg-warning text-dark',
                'approved': 'bg-info text-white',
                'paid': 'bg-success',
                'rejected': 'bg-danger'
            };
            
            const statusText = {
                'draft': 'Draft',
                'pending': 'Pending',
                'approved': 'Approved',
                'paid': 'Paid',
                'rejected': 'Rejected'
            };
            
            return `<span class="badge ${statusMap[status] || 'bg-secondary'}">${statusText[status] || status}</span>`;
        }
        
        async viewPayslip(id) {
            try {
                this.currentPayslipId = id;
                
                const response = await fetch(`${this.apiBase}/payslip/${id}`, {
                    headers: {
                        'Authorization': `Bearer ${localStorage.getItem('authToken')}`
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                
                const result = await response.json();
                this.renderPayslipDetail(result.payslip);
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('payslipDetailModal'));
                modal.show();
                
            } catch (error) {
                console.error('Error loading payslip detail:', error);
                this.showAlert('danger', 'Failed to load payslip details.');
            }
        }
        
        renderPayslipDetail(payslip) {
            const body = document.getElementById('payslipDetailBody');
            
            // Format dates
            const issuedDate = new Date(payslip.date_issued).toLocaleDateString();
            const periodStart = new Date(payslip.pay_period_start).toLocaleDateString();
            const periodEnd = new Date(payslip.pay_period_end).toLocaleDateString();
            const paymentDate = payslip.payment_date ? new Date(payslip.payment_date).toLocaleDateString() : 'Not set';
            
            body.innerHTML = `
                <!-- Company Info -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="mb-1">ICMS Company</h5>
                            <p class="mb-0">123 Business Ave, Manila, Philippines</p>
                        </div>
                        <div class="text-end">
                            <h5 class="mb-1">PAYSLIP</h5>
                            <p class="mb-0">No: ${payslip.id.toString().padStart(8, '0')}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Employee Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Employee Information</h6>
                        <p><strong>Name:</strong> [Employee Name]</p>
                        <p><strong>Employee ID:</strong> ${payslip.employee_id}</p>
                        <p><strong>Position:</strong> [Position]</p>
                        <p><strong>Department:</strong> [Department]</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Pay Period Information</h6>
                        <p><strong>Pay Period:</strong> ${periodStart} to ${periodEnd}</p>
                        <p><strong>Date Issued:</strong> ${issuedDate}</p>
                        <p><strong>Payment Date:</strong> ${paymentDate}</p>
                        <p><strong>Payment Method:</strong> ${payslip.payment_method.replace('_', ' ').toUpperCase()}</p>
                    </div>
                </div>
                
                <!-- Earnings -->
                <div class="mb-4">
                    <h6><i class="bi bi-plus-circle me-2"></i> Earnings</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start">Description</th>
                                    <th class="text-end">Amount (₱)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-start">Basic Salary</td>
                                    <td class="text-end">${parseFloat(payslip.basic_salary || 0).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-start">Overtime Pay</td>
                                    <td class="text-end">${parseFloat(payslip.overtime_pay || 0).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-start">Allowances</td>
                                    <td class="text-end">${parseFloat(payslip.allowances || 0).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-start">Bonuses</td>
                                    <td class="text-end">${parseFloat(payslip.bonuses || 0).toFixed(2)}</td>
                                </tr>
                                <tr class="table-active">
                                    <td class="text-start fw-bold">Gross Earnings</td>
                                    <td class="text-end fw-bold">₱${parseFloat(payslip.gross_earnings || 0).toFixed(2)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Deductions -->
                <div class="mb-4">
                    <h6><i class="bi bi-dash-circle me-2 text-danger"></i> Deductions</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start">Description</th>
                                    <th class="text-end">Amount (₱)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-start">SSS Contribution</td>
                                    <td class="text-end">${parseFloat(payslip.sss_deduction || 0).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-start">PhilHealth Contribution</td>
                                    <td class="text-end">${parseFloat(payslip.philhealth_deduction || 0).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-start">Pag-IBIG Contribution</td>
                                    <td class="text-end">${parseFloat(payslip.pagibig_deduction || 0).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-start">Withholding Tax</td>
                                    <td class="text-end">${parseFloat(payslip.tax_deduction || 0).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-start">Other Deductions</td>
                                    <td class="text-end">${parseFloat(payslip.other_deductions || 0).toFixed(2)}</td>
                                </tr>
                                <tr class="table-danger">
                                    <td class="text-start fw-bold">Total Deductions</td>
                                    <td class="text-end fw-bold">₱${parseFloat(payslip.total_deductions || 0).toFixed(2)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Net Pay -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6><i class="bi bi-cash-coin me-2"></i> Net Pay</h6>
                        </div>
                        <div>
                            <h5 class="fw-success">₱${parseFloat(payslip.net_pay || 0).toFixed(2)}</h5>
                        </div>
                    </div>
                </div>
                
                <!-- Year-to-Date -->
                <div class="mb-4">
                    <h6><i class="bi bi-graph-up me-2"></i> Year-to-Date Summary</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start">Description</th>
                                    <th class="text-end">Amount (₱)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-start">YTD Earnings</td>
                                    <td class="text-end">${parseFloat(payslip.year_to_date_earnings || 0).toFixed(2)}</td>
                                </tr>
                                <tr>
                                    <td class="text-start">YTD Deductions</td>
                                    <td class="text-end">${parseFloat(payslip.year_to_date_deductions || 0).toFixed(2)}</td>
                                </tr>
                                <tr class="table-active">
                                    <td class="text-start fw-bold">YTD Net Pay</td>
                                    <td class="text-end fw-bold">₱${parseFloat(payslip.year_to_date_net || 0).toFixed(2)}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Leave Credits -->
                <div class="mb-4">
                    <h6><i class="bi bi-calendar-check me-2"></i> Leave Credits</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Vacation Leave:</strong> ${parseFloat(payslip.leave_credits_vacation || 0).toFixed(2)} days</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Sick Leave:</strong> ${parseFloat(payslip.leave_credits_sick || 0).toFixed(2)} days</p>
                        </div>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="mb-4">
                    <h6>Status</h6>
                    <span class="badge ${this.getStatusClass(payslip.status)} fw-bold">${this.getStatusText(payslip.status)}</span>
                </div>
                
                <!-- Remarks -->
                ${payslip.remarks ? `
                <div class="mb-4">
                    <h6>Remarks</h6>
                    <p class="bg-light p-3 rounded">${payslip.remarks}</p>
                </div>
                ` : ''}
            `;
        }
        
        getStatusClass(status) {
            const statusMap = {
                'draft': 'bg-secondary',
                'pending': 'bg-warning text-dark',
                'approved': 'bg-info text-white',
                'paid': 'bg-success',
                'rejected': 'bg-danger'
            };
            return statusMap[status] || 'bg-secondary';
        }
        
        getStatusText(status) {
            const statusMap = {
                'draft': 'Draft',
                'pending': 'Pending',
                'approved': 'Approved',
                'paid': 'Paid',
                'rejected': 'Rejected'
            };
            return statusMap[status] || status;
        }
        
        async downloadPayslip(id) {
            try {
                // In a real implementation, this would generate and download a PDF
                // For now, we'll show a message
                this.showAlert('info', 'PDF download feature coming soon!');
                
                // Alternative: Open print view
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <html>
                    <head>
                        <title>Payslip #${id}</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 40px; }
                            .header { text-align: center; margin-bottom: 30px; }
                            .section { margin-bottom: 20px; }
                            table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f2f2f2; }
                            .total { font-weight: bold; background-color: #e6f3ff; }
                            .net-pay { font-size: 1.2em; color: #28a745; }
                        </style>
                    </head>
                    <body onfocus="this.close();">
                        <!-- Printable payslip content would go here -->
                        <div class="header">
                            <h2>ICMS Company</h2>
                            <h3>PAYSLIP</h3>
                            <p>Payslip ID: ${id}</p>
                        </div>
                        <p>This is a printable version of the payslip. The actual PDF generation feature is under development.</p>
                        <p><small>Generated on ${new Date().toLocaleString()}</small></p>
                    </body>
                    </html>
                `);
                printWindow.document.close();
                printWindow.focus();
                printWindow.print();
                
            } catch (error) {
                console.error('Error downloading payslip:', error);
                this.showAlert('danger', 'Failed to download payslip.');
            }
        }
        
        downloadPayslipPdf() {
            this.downloadPayslip(this.currentPayslipId);
        }
        
        showAlert(type, message) {
            const alertsContainer = document.getElementById('payslipAlerts');
            const alertId = `alert_${Date.now()}`;
            
            alertsContainer.innerHTML += `
                <div id="${alertId}" class="alert alert-${type} alert-dismissible fade show mt-3" role="alert">
                    <i class="bi bi-${type === 'danger' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                const alert = document.getElementById(alertId);
                if (alert) {
                    alert.remove();
                }
            }, 5000);
        }
    }
    
    // Initialize payslip manager when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        window.payslipManager = new PayslipManager();
    });
</script>
<?= $this->endSection() ?>