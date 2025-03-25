# Client Approval System Testing Guide

This guide provides instructions for testing the client approval functionality in the Hotel Management System.

## Setup Test Data

### Option 1: Complete Test Environment (with Reservations)

To set up a complete test environment with users, rooms, and reservations, run:

```bash
php artisan app:setup-client-approval-test --fresh
```

### Option 2: Simplified Test Environment (Users Only)

If you only need to test the client approval functionality without rooms and reservations, run:

```bash
php artisan app:setup-simple-client-approval --fresh
```

The `--fresh` flag will refresh the database. Remove this flag if you want to keep existing data.

## Test Accounts

After running the setup command, the following test accounts will be available:

### Admin
- Email: admin@example.com
- Password: password

### Receptionists
- Email: receptionist1@example.com
- Email: receptionist2@example.com
- Email: receptionist3@example.com
- Password: password

### Pending Clients
- Email: pending.client1@example.com
- Email: pending.client2@example.com
- Email: pending.client3@example.com
- Password: password

### Approved Clients
- Email: approved.client1@example.com
- Email: approved.client2@example.com
- Email: approved.client3@example.com
- Password: password

## Testing Workflow

1. **Login as a Receptionist**
   - Use one of the receptionist accounts to log in
   - Navigate to "Clients Pending Approval" page
   - You should see a list of pending clients

2. **Approve a Client**
   - Click the "Approve Client" button for one of the pending clients
   - The client should be moved to "My Approved Clients" list
   - Any pending reservations for this client should be automatically confirmed

3. **Reject a Client**
   - Click the "Reject Client" button for one of the pending clients
   - The client account should be deleted

4. **Check Client Status**
   - Use the command line tool to check client status:
   ```bash
   php artisan app:check-client-status
   ```
   - To check a specific client:
   ```bash
   php artisan app:check-client-status pending.client1@example.com
   ```

## Troubleshooting

If you encounter issues with the client approval functionality, you can use these commands to diagnose problems:

```bash
# Check for pending clients directly in the database
php artisan app:check-pending-clients

# Create a test client with pending status
php artisan app:create-test-client

# Check the status of all clients in the system
php artisan app:check-client-status

# Check a specific client by email
php artisan app:check-client-status pending.client1@example.com
```

### Common Issues and Solutions

1. **Empty Pending Clients List**
   - Make sure clients have `is_approved = 0` in the database
   - Check if the query is using `is_approved = 0` instead of `is_approved = false`
   - Verify that clients have the 'client' role assigned

2. **Client Approval Not Working**
   - Check if the transaction is completing successfully
   - Verify that the client's `is_approved` field is being updated to 1
   - Check if the client's `manager_id` is being set correctly

3. **Database Migration Issues**
   - If you encounter migration errors, check for syntax issues in migration files
   - Try running `php artisan migrate:status` to see the status of migrations
   - You can also try `php artisan migrate:fresh --seed` to start fresh

## Database Structure

The client approval system uses the following database fields:

- `users.is_approved` - Boolean field (0 = pending, 1 = approved)
- `users.manager_id` - Foreign key to the receptionist who approved the client
- `reservations.status` - Enum field ('pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled')
- `reservations.receptionist_id` - Foreign key to the receptionist who handled the reservation

## Expected Behavior

1. New clients register with `is_approved = 0` (pending)
2. Receptionists can see pending clients in the "Clients Pending Approval" page
3. When a receptionist approves a client:
   - The client's `is_approved` is set to 1
   - The receptionist becomes the client's manager
   - Any pending reservations are updated to "confirmed" status
4. When a receptionist rejects a client:
   - The client account is deleted
   - Any associated reservations are also deleted