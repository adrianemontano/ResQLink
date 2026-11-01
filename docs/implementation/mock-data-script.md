# TASK3 Mock Data Script

The standalone [`resqlink_mock_data.sql`](../../backend/database/mock-data/resqlink_mock_data.sql)
script creates 32 Cebu City incident scenarios for academic demonstration. It
reuses the seeded volunteer and dispatcher accounts, creates three verified demo
volunteer accounts only when absent, and inserts incident histories through the
current workflow status.

The dataset contains eight incidents in each status (`Reported`, `Received`,
`Dispatched`, and `Completed`), eight in each approved category (`Flood`,
`Earthquake`, `Landslide`, and `Fire`), and eight in each preliminary severity
(`Low`, `Moderate`, `High`, and `Critical`). Records are identified by the
`TASK3 mock/demo:` notes prefix, so repeated execution does not duplicate them.

## Workbench usage

1. Run the Laravel migrations and normal seeders first.
2. Open the SQL file in MySQL Workbench.
3. Select the development database in the schema navigator, or add `USE
   your_database_name;` before `START TRANSACTION`.
4. Execute the complete file. Review the verification result sets at the end.

The script targets MySQL 8.0+, does not drop or truncate tables, and has not been
runtime-tested against a local database in this change because database
credentials and a configured development connection were not available.
