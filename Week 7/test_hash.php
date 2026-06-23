<?php

/**
 * test_hash.php - Understanding password hashing
 * Run this file to see how hashing works
 */

echo "<h2>Password Hashing Demo</h2>";

// Step 1: Original password
$password = "Password123";
echo "<p><strong>Original Password:</strong> " . $password . "</p>";

// Step 2: Hash the password
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "<p><strong>Hashed Password:</strong> " . $hash . "</p>";
echo "<p><em>Note: The hash contains the salt and algorithm info</em></p>";

// Step 3: Verify correct password
$verify = password_verify($password, $hash);
echo "<p><strong>Verify correct password:</strong> " . ($verify ? "✅ TRUE" : "❌ FALSE") . "</p>";

// Step 4: Verify wrong password
$wrongPassword = "WrongPassword123";
$wrongVerify = password_verify($wrongPassword, $hash);
echo "<p><strong>Verify wrong password:</strong> " . ($wrongVerify ? "✅ TRUE" : "❌ FALSE") . "</p>";

// Step 5: Show that same password produces different hashes
$hash2 = password_hash($password, PASSWORD_DEFAULT);
echo "<p><strong>Second hash of same password:</strong> " . $hash2 . "</p>";
echo "<p><em>Note: Both hashes are different because of random salt!</em></p>";

// Step 6: Verify with different hash
$verify2 = password_verify($password, $hash2);
echo "<p><strong>Verify with second hash:</strong> " . ($verify2 ? "✅ TRUE" : "❌ FALSE") . "</p>";
