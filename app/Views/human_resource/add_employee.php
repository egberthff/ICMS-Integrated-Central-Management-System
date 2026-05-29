<?= $this->extend('layout') ?>

<?= $this->section('content') ?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><?= $title ?></h4>
                </div>
                <div class="card-body">
                    <form action="/employee/store" method="POST" class="needs-validation" novalidate>

                        <!-- User ID Link -->
                        <div class="mb-3">
                            <label for="user_id" class="form-label">User ID (Auth Link)</label>
                            <input type="text" class="form-control" id="user_id" name="user_id" maxlength="255"
                                required>
                            <div class="invalid-feedback">Please enter the associated User ID.</div>
                        </div>

                        <!-- Employee Number -->
                        <div class="mb-3">
                            <label for="employee_number" class="form-label">Employee Number</label>
                            <input type="text" class="form-control" id="employee_number" name="employee_number"
                                maxlength="50" required>
                            <div class="invalid-feedback">Please enter a unique employee number.</div>
                        </div>

                        <!-- Name Section (Grid Layout) -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name"
                                    maxlength="100" required>
                                <div class="invalid-feedback">First name is required.</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" maxlength="100"
                                    required>
                                <div class="invalid-feedback">Last name is required.</div>
                            </div>
                        </div>

                        <!-- Position Title -->
                        <div class="mb-3">
                            <label for="position_title" class="form-label">Position Title (Optional)</label>
                            <input type="text" class="form-control" id="position_title" name="position_title"
                                maxlength="150">
                        </div>

                        <!-- Employment Status -->
                        <div class="mb-4">
                            <label for="employment_status" class="form-label">Employment Status</label>
                            <select class="form-select" id="employment_status" name="employment_status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                            <div class="invalid-feedback">Please select a status.</div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-end gap-2">
                            <button type="reset" class="btn btn-secondary">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Employee</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>