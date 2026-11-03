# ASE 230 — Programming Assignment 4 Starter
## Sessions and Authentication

This project gives you a working Support Ticket application based on the previous assignment.
The ticket search, validation, upload, JSON persistence, and basic ticket functionality are already implemented.

Your work in PA4 focuses on **sessions, authentication, and authorization**.

## Test Accounts

Use these accounts while developing:

### Agent
- Email: `agent@example.com`
- Password: `Agent123!`
- Role: `agent`

### Manager
- Email: `manager@example.com`
- Password: `Manager123!`
- Role: `manager`

The passwords are NOT stored directly in `users.json`. Only password hashes are stored there.

## Start Here

1. Run the project through your PHP web server.
2. Open `session-lab.php`.
3. Search the project for `TODO`.
4. Complete the TODOs in numerical order when practical.

## Important Files

- `session-lab.php` — guided session warm-up
- `login.php` — authentication workflow
- `logout.php` — ending an authenticated session
- `includes/auth.php` — reusable session/authentication helpers
- `index.php` — protected ticket list
- `detail.php` — protected detail page and role-aware UI
- `update-status.php` — server-side authorization test
- `create.php` — demonstrates trusted identity from the session

## Core Ideas

By the end, you should be able to explain:

- why HTTP requests do not automatically remember earlier requests
- what `session_start()` does
- how a browser session cookie relates to server-side `$_SESSION` data
- why passwords are verified against hashes instead of decrypted
- why authentication and authorization are different
- why hiding a button is not sufficient authorization
- why authenticated identity should come from the session rather than a hidden form field
- how a flash message can survive one redirect and then disappear

## Test the System, Not Just the Happy Path

Try direct URLs and manually altered requests.

For example:
- visit `index.php` before signing in
- sign in as the agent and manually POST to `update-status.php`
- sign in as the manager and update a ticket
- sign out, then revisit a protected URL
- open another tab in the same browser
- compare behavior with a private/incognito window
