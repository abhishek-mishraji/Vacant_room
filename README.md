# Find Vacant Room (LAMP - PHP + MySQL)

## Quick setup

1. Ensure MySQL container is running and listening on host port 3306.
2. Put project folder content in your PHP container mount (we used ./find_vacant_room).
3. Configure DB credentials in `config/db.php` or via environment variables:
   - DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS
4. Start PHP container (your existing Docker setup).
5. Open: http://localhost:8081

## Notes

- Schedules table in DB expected to have day_of_week values: Mon, Tue, Wed, Thu, Fri.
- Slots 1..8 correspond to 09:00..17:00 hourly slots.
