<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-calendar-check display-1 text-primary"></i>
                <h5 class="card-title mt-2">Timesheet Status</h5>
                <p class="card-text">Submitted for this week</p>
                <button class="btn btn-primary">View Details</button>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-body text-center">
                <i class="bi bi-cash display-1 text-success"></i>
                <h5 class="card-title mt-2">Payroll Summary</h5>
                <p class="card-text">Next payday: June 15, 2026</p>
                <button class="btn btn-success">View Payslip</button>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Employee Quick Actions</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-primary me-2"><i class="bi bi-clock"></i> Submit Timesheet</button>
                <button class="btn btn-info me-2"><i class="bi bi-person"></i> Update Profile</button>
                <button class="btn btn-warning"><i class="bi bi-question-circle"></i> Request Support</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Employee-specific dashboard logic can go here
    window.addEventListener('load', function() {
        // Load employee-specific data
        console.log('Employee dashboard loaded');
    });
</script>

<?= $this->endSection() ?>