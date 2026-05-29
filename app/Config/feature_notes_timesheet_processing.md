Feature Notes - Timesheet Processing → Payslips

1) Employee can:
- Select pay period
- View the individual timesheet record for that period
- Run a PAYROLL PREVIEW (no save) to see calculated earnings/deductions/net
- View the generated payslip if payroll already generated it

2) Employee cannot:
- Generate/recalculate payslips (RBAC payroll-only)

3) Payroll can:
- Generate/recalculate payslips via existing endpoint

4) Implementation approach:
- Add endpoints (employee-accessible) for:
  - timesheet by period
  - payslip preview (calculation-only)
  - payslip lookup by period/status
- Upgrade /timesheet UI with a stepper: Review -> Preview -> (View generated payslip)

