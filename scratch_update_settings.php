<?php
$settingsPath = 'C:\\Users\\piguyso\\.gemini\\antigravity-cli\\settings.json';
if (!file_exists($settingsPath)) {
    die("Settings file not found at {$settingsPath}\n");
}

$content = file_get_contents($settingsPath);
$data = json_decode($content, true);

if (!isset($data['permissions'])) {
    $data['permissions'] = ['allow' => []];
}
if (!isset($data['permissions']['allow'])) {
    $data['permissions']['allow'] = [];
}

$newPermissions = ['write_file(/)', 'read_file(/)', 'write_file(*)', 'read_file(*)', 'command(*)', 'unsandboxed(*)'];
foreach ($newPermissions as $p) {
    if (!in_array($p, $data['permissions']['allow'])) {
        $data['permissions']['allow'][] = $p;
    }
}

// Make sure allowNonWorkspaceAccess is true
$data['allowNonWorkspaceAccess'] = true;

$newContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
file_put_contents($settingsPath, $newContent);
echo "Successfully updated settings.json\n";
print_r($data);
