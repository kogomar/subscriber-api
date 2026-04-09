<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Client\GitHubClient;
use App\Database;
use App\Service\EmailNotifierService;

echo "Starting background scanner...\n";

$db = Database::getConnection();
$github = new GitHubClient();
$notifier = new EmailNotifierService();

while (true) {
    echo "Running check at " . date('Y-m-d H:i:s') . "\n";
    
    try {
        $stmt = $db->query("SELECT * FROM repositories");
        $repositories = $stmt->fetchAll();

        foreach ($repositories as $repo) {
            $fullName = $repo['owner'] . '/' . $repo['repo'];
            echo "Checking $fullName...\n";

            try {
                $latestTag = $github->getLatestRelease($fullName);
                
                if ($latestTag && $latestTag !== $repo['last_seen_tag']) {
                    echo "Found new release: $latestTag (was {$repo['last_seen_tag']})\n";
                    
                    // fetch subscribers
                    $subStmt = $db->prepare("SELECT email FROM subscriptions WHERE repository_id = ?");
                    $subStmt->execute([$repo['id']]);
                    $subscribers = $subStmt->fetchAll(PDO::FETCH_COLUMN);

                    foreach ($subscribers as $email) {
                        echo "Notifying $email...\n";
                        $notifier->notify($email, $fullName, $latestTag);
                    }

                    // update last seen tag
                    $updateStmt = $db->prepare("UPDATE repositories SET last_seen_tag = ? WHERE id = ?");
                    $updateStmt->execute([$latestTag, $repo['id']]);
                }
            } catch (\Exception $e) {
                echo "Error checking $fullName: " . $e->getMessage() . "\n";
                if ($e->getCode() === 429) {
                    echo "Rate limit exceeded. Waiting for longer before next loop.\n";
                    sleep(60);
                }
            }
        }
    } catch (\Exception $e) {
        echo "Database error: " . $e->getMessage() . "\n";
    }

    echo "Sleeping for 60 seconds...\n";
    sleep(60);
}
