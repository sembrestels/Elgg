This plugin overrides the Elgg's default password storing and hashes user's passwords
twice: first in *md5* (default behaviour) and then in *sha-512*, a better algorithm.

**Warning:** This plugin may cause problems with other autentication related Elgg plugins.
Be careful before put together in production.<br>
**Warning:** While this plugin is desactivated, users with new hashed password can't access
to their accounts, unless they reset the password.

### Steps to test this plugin

1. Try to log in with an old user.
2. Create a new user and log in.
3. Request a new password.
4. Change password from settings panel.
5. Change password to other user from settings panel (as admin).