<?php
session_start();

if (!isset($_SESSION['userid'])) {
    die("Please login first");
}

echo "<h1>🛠️ Setup Folders</h1>";
echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
    .result { background: white; padding: 20px; margin: 10px 0; border-radius: 10px; }
    .success { color: green; }
    .error { color: red; }
</style>";

$folders = [
    'uploads',
    'uploads/products'
];

echo "<div class='result'>";
echo "<h2>Creating folders...</h2>";

foreach ($folders as $folder) {
    echo "<p>";
    if (file_exists($folder)) {
        echo "📁 <strong>$folder</strong> - <span class='success'>✅ Already exists</span>";
        
        // Check permissions
        $perms = substr(sprintf('%o', fileperms($folder)), -4);
        echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;Permissions: $perms";
        
        if (is_writable($folder)) {
            echo " <span class='success'>(Writable)</span>";
        } else {
            echo " <span class='error'>(Not writable - need to fix!)</span>";
            @chmod($folder, 0777);
            if (is_writable($folder)) {
                echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;<span class='success'>✅ Fixed permissions</span>";
            } else {
                echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;<span class='error'>❌ Cannot fix automatically. Run: chmod 777 $folder</span>";
            }
        }
    } else {
        if (mkdir($folder, 0777, true)) {
            echo "📁 <strong>$folder</strong> - <span class='success'>✅ Created successfully</span>";
        } else {
            echo "📁 <strong>$folder</strong> - <span class='error'>❌ Failed to create</span>";
        }
    }
    echo "</p>";
}

// Test file write
echo "<h2>Testing file write...</h2>";
$testFile = 'uploads/products/test.txt';
if (file_put_contents($testFile, 'Test content')) {
    echo "<p class='success'>✅ Successfully wrote test file: $testFile</p>";
    unlink($testFile);
    echo "<p class='success'>✅ Successfully deleted test file</p>";
} else {
    echo "<p class='error'>❌ Cannot write to uploads/products/ folder</p>";
    echo "<p><strong>Solution:</strong> Run this command in terminal:</p>";
    echo "<pre>chmod -R 777 uploads/</pre>";
}

echo "</div>";

echo "<div class='result'>";
echo "<h2>✅ Next Steps:</h2>";
echo "<ol>";
echo "<li>Go to <a href='admin_products.php'>Product Management</a></li>";
echo "<li>Edit any product and re-upload its image</li>";
echo "<li>Or run <a href='check_images.php'>Check Images</a> to see which products need fixing</li>";
echo "</ol>";
echo "</div>";
?>