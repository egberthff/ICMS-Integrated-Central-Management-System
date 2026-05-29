# TODO

## Employee migration completion
- [ ] Inspect current employee migration `app/Database/Migrations/2026-05-29-023717_Employee.php`.
- [x] Update migration to create the `employeed` table.

- [x] Add required columns (based on payroll/employee needs) and constraints.

- [x] Ensure UUID primary identifier is `employee_id` (external source not needed for `id`).

- [x] Add FK to `users.user_id` where appropriate.

- [x] Add primary key and audit fields.

- [x] Validate the migration code compiles (no syntax errors).

- [x] Do not run migrations/commands (per instruction).


